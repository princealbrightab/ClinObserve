<?php

namespace App\Services;

use App\Contracts\AiSuggestionServiceInterface;
use App\Models\AiReview;
use App\Models\Patient;
use App\Models\PatientEncounter;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;
use Throwable;

class AiReviewService
{
    public function __construct(private AiSuggestionServiceInterface $provider) {}

    public function enabled(): bool
    {
        return config('clinobserve.ai_enabled') && config('clinobserve.ai_provider') === 'openai' && filled(config('clinobserve.ai_key')) && filled(config('clinobserve.ai_model'));
    }

    public function payload(PatientEncounter $encounter): array
    {
        $patient = $encounter->patient;
        $age = (int) (floor($patient->age_years / 10) * 10);

        return ['age_band' => $age.'–'.($age + 9), 'case_context' => $patient->only(['chief_complaint', 'presenting_symptoms', 'history_of_present_illness', 'examination_findings']), 'observation' => $encounter->only(PatientEncounter::FIELDS)];
    }

    public function hash(array $payload): string
    {
        return hash('sha256', json_encode($payload, JSON_THROW_ON_ERROR));
    }

    public function generate(PatientEncounter $encounter, User $student, array $data): AiReview
    {
        Gate::authorize('requestAi', $encounter);
        if (! $this->enabled()) {
            throw ValidationException::withMessages(['ai' => 'AI review is unavailable. All other features remain available.']);
        }
        $review = DB::transaction(function () use ($encounter, $student, $data): AiReview {
            Patient::whereKey($encounter->patient_id)->lockForUpdate()->firstOrFail();
            $locked = PatientEncounter::whereKey($encounter->id)->lockForUpdate()->firstOrFail();
            Gate::authorize('requestAi', $locked);
            $existing = AiReview::where('request_key', $data['request_key'])->first();
            if ($existing) {
                abort_unless($existing->requested_by === $student->id && $existing->encounter_id === $locked->id, 403);

                return $existing;
            }
            $payload = $this->payload($locked);
            $hash = $this->hash($payload);
            if (! hash_equals($hash, $data['input_hash'])) {
                throw ValidationException::withMessages(['ai' => 'The observation changed. Review the updated preview before sending.']);
            }
            $text = json_encode($payload, JSON_THROW_ON_ERROR);
            if (strlen($text) > 24000 || preg_match('/[A-Z0-9._%+-]+@[A-Z0-9.-]+\\.[A-Z]{2,}|(?:\\+?\\d[\\s().-]*){10,}/i', $text)) {
                throw ValidationException::withMessages(['ai' => 'Remove contact identifiers or shorten the notes before requesting AI review.']);
            }
            $locked->aiReviews()->where('status', 'pending')->where('created_at', '<', now()->subMinute())->update(['status' => 'failed', 'error_code' => 'request_expired', 'completed_at' => now()]);
            if ($locked->aiReviews()->where('status', 'pending')->exists()) {
                throw ValidationException::withMessages(['ai' => 'A review is already in progress. Please check again shortly.']);
            }
            $locked->locked_at ??= now();
            $locked->save();
            $review = $locked->aiReviews()->make(['request_key' => $data['request_key'], 'provider' => config('clinobserve.ai_provider'), 'model' => config('clinobserve.ai_model'), 'prompt_version' => 'education-v1', 'status' => 'pending', 'input_snapshot' => $payload, 'input_hash' => $hash, 'privacy_confirmed_at' => now()]);
            $review->requested_by = $student->id;
            $review->save();

            return $review;
        });
        if (! $review->wasRecentlyCreated) {
            return $review;
        }
        try {
            $result = $this->provider->review($review->input_snapshot);
            AiReview::whereKey($review->id)->where('status', 'pending')->update(['status' => 'succeeded', 'structured_response' => json_encode($result, JSON_THROW_ON_ERROR), 'completed_at' => now()]);
        } catch (Throwable $e) {
            AiReview::whereKey($review->id)->where('status', 'pending')->update(['status' => 'failed', 'error_code' => 'review_unavailable', 'completed_at' => now()]);
        }

        return $review->refresh();
    }
}
