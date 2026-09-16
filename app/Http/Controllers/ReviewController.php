<?php

namespace App\Http\Controllers;

use App\Http\Requests\ReviewRequest;
use App\Models\HodReview;
use App\Models\PatientEncounter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class ReviewController extends Controller
{
    public function store(ReviewRequest $request, PatientEncounter $encounter): RedirectResponse
    {
        DB::transaction(function () use ($request, $encounter): void {
            $locked = PatientEncounter::whereKey($encounter->id)->lockForUpdate()->firstOrFail();
            $locked->locked_at ??= now();
            $locked->save();
            $review = $locked->hodReviews()->make($request->validated());
            $review->hod_id = $request->user()->id;
            $review->save();
        });

        return back()->with('success', 'Faculty feedback added. The observation is now locked.');
    }

    public function show(HodReview $hodReview): View
    {
        Gate::authorize('view', $hodReview);
        $hodReview->load(['hod', 'encounter.patient']);

        return view('reviews.show', compact('hodReview'));
    }

    public function update(ReviewRequest $request, HodReview $hodReview): RedirectResponse
    {
        $hodReview->update($request->validated());

        return back()->with('success', 'Feedback updated.');
    }
}
