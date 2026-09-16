<?php

namespace Tests\Feature;

use App\Models\AiReview;
use App\Models\PatientEncounter;
use App\Models\User;
use App\Services\AiReviewService;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Tests\TestCase;

class AiReviewTest extends TestCase
{
    use LazilyRefreshDatabase;

    private function enable(): void
    {
        config(['clinobserve.ai_enabled' => true, 'clinobserve.ai_provider' => 'openai', 'clinobserve.ai_key' => 'fake-test-key', 'clinobserve.ai_model' => 'test-model']);
        Http::preventStrayRequests();
    }

    private function payload(PatientEncounter $entry): array
    {
        $service = app(AiReviewService::class);

        return ['request_key' => (string) Str::uuid(), 'privacy_confirmed' => 1, 'input_hash' => $service->hash($service->payload($entry))];
    }

    private function educationalResult(): array
    {
        return ['summary_feedback' => 'Review the documentation with faculty.', 'missing_information' => ['Clarify the symptom timeline.'], 'questions_to_consider' => [], 'documentation_improvements' => [], 'learning_topics' => ['History taking'], 'clinical_considerations' => [], 'safety_note' => 'Education only.'];
    }

    private function fakeSuccess(): void
    {
        Http::fake(['https://api.openai.com/v1/responses' => Http::response(['status' => 'completed', 'output' => [['type' => 'message', 'content' => [['type' => 'output_text', 'text' => json_encode($this->educationalResult())]]]]])]);
    }

    public function test_disabled_ai_keeps_app_usable_and_sends_nothing(): void
    {
        Http::preventStrayRequests();
        $entry = PatientEncounter::factory()->create();
        $this->actingAs($entry->student)->get(route('ai-reviews.preview', $entry))->assertSee('AI is disabled');
        $this->post(route('ai-reviews.store', $entry), $this->payload($entry))->assertSessionHasErrors('ai');
        $this->assertDatabaseCount('ai_reviews', 0);
        $this->assertNull($entry->fresh()->locked_at);
        Http::assertNothingSent();
    }

    public function test_other_student_cannot_request_or_read_private_review(): void
    {
        $this->enable();
        $entry = PatientEncounter::factory()->create();
        $review = AiReview::factory()->create(['encounter_id' => $entry->id]);
        $other = User::factory()->create();
        $this->actingAs($other)->post(route('ai-reviews.store', $entry), $this->payload($entry))->assertForbidden();
        $this->get(route('ai-reviews.show', $review))->assertForbidden();
        Http::assertNothingSent();
    }

    public function test_successful_text_only_review_preserves_history_and_deduplicates_reposts(): void
    {
        $this->enable();
        $this->fakeSuccess();
        $entry = PatientEncounter::factory()->create();
        $payload = $this->payload($entry);
        $this->actingAs($entry->student)->post(route('ai-reviews.store', $entry), $payload)->assertSessionHasNoErrors()->assertRedirect();
        $review = AiReview::firstOrFail();
        $this->assertSame('succeeded', $review->status);
        $expected = $this->educationalResult();
        $actual = $review->structured_response;
        ksort($expected);
        ksort($actual);
        $this->assertSame($expected, $actual);
        $this->assertNotNull($entry->fresh()->locked_at);
        Http::assertSent(function ($request) use ($entry): bool {
            $input = json_decode($request['input'], true);

            return $request['store'] === false && ! isset($input['display_name']) && ! str_contains($request['input'], $entry->patient->display_name) && ! str_contains($request['input'], $entry->student->email) && ! isset($input['images']);
        });
        $this->post(route('ai-reviews.store', $entry), $payload)->assertRedirect();
        $this->assertDatabaseCount('ai_reviews', 1);
        Http::assertSentCount(1);
        $this->get(route('ai-reviews.show', $review))->assertSee('AI-generated educational feedback')->assertSee('Review the documentation with faculty.');
    }

    public function test_intentional_new_request_creates_another_review(): void
    {
        $this->enable();
        $this->fakeSuccess();
        $entry = PatientEncounter::factory()->create();
        $this->actingAs($entry->student)->post(route('ai-reviews.store', $entry), $this->payload($entry))->assertRedirect();
        $this->post(route('ai-reviews.store', $entry), $this->payload($entry))->assertRedirect();
        $this->assertDatabaseCount('ai_reviews', 2);
        Http::assertSentCount(2);
    }

    public function test_bad_provider_output_becomes_safe_failure(): void
    {
        $this->enable();
        Http::fake(['https://api.openai.com/v1/responses' => Http::response(['status' => 'completed', 'output' => [['content' => [['type' => 'output_text', 'text' => 'not json']]]]])]);
        $entry = PatientEncounter::factory()->create();
        $this->actingAs($entry->student)->post(route('ai-reviews.store', $entry), $this->payload($entry))->assertRedirect();
        $this->assertDatabaseHas('ai_reviews', ['status' => 'failed', 'error_code' => 'review_unavailable']);
        $this->assertNull(AiReview::first()->structured_response);
        Http::assertSentCount(1);
    }

    public function test_connection_failure_preserves_observation(): void
    {
        $this->enable();
        Http::fake(['https://api.openai.com/v1/responses' => Http::failedConnection()]);
        $entry = PatientEncounter::factory()->create();
        $this->actingAs($entry->student)->post(route('ai-reviews.store', $entry), $this->payload($entry))->assertRedirect();
        $this->assertDatabaseHas('ai_reviews', ['status' => 'failed']);
        $this->assertModelExists($entry);
        Http::assertSentCount(1);
    }

    public function test_identifiers_and_stale_preview_are_rejected_before_network_request(): void
    {
        $this->enable();
        $entry = PatientEncounter::factory()->create();
        $payload = $this->payload($entry);
        $entry->summary = 'Contact patient@example.test for private information.';
        $entry->save();
        $this->actingAs($entry->student)->post(route('ai-reviews.store', $entry), $payload)->assertSessionHasErrors('ai');
        $this->post(route('ai-reviews.store', $entry), $this->payload($entry->fresh()))->assertSessionHasErrors('ai');
        $this->assertDatabaseCount('ai_reviews',0);
        Http::assertNothingSent();
    }
}
