<?php

namespace App\Http\Controllers;

use App\Http\Requests\AiReviewRequest;
use App\Models\AiReview;
use App\Models\PatientEncounter;
use App\Services\AiReviewService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AiReviewController extends Controller
{
    public function index(Request $request, AiReviewService $service): View
    {
        $reviews = AiReview::with(['encounter.patient', 'requester'])->when(! $request->user()->isHod(), fn ($q) => $q->where('requested_by', $request->user()->id))->latest()->paginate(15);

        return view('ai.index', ['reviews' => $reviews, 'enabled' => $service->enabled()]);
    }

    public function preview(PatientEncounter $encounter, AiReviewService $service): View
    {
        Gate::authorize('requestAi', $encounter);
        $payload = $service->payload($encounter);

        return view('ai.preview', ['encounter' => $encounter, 'payload' => $payload, 'hash' => $service->hash($payload), 'requestKey' => (string) Str::uuid(), 'enabled' => $service->enabled()]);
    }

    public function store(AiReviewRequest $request, PatientEncounter $encounter, AiReviewService $service): RedirectResponse
    {
        $review = $service->generate($encounter, $request->user(), $request->validated());

        return redirect()->route('ai-reviews.show', $review);
    }

    public function show(AiReview $aiReview): View
    {
        Gate::authorize('view', $aiReview);
        $aiReview->load('encounter.patient');

        return view('ai.show', compact('aiReview'));
    }
}
