<?php

namespace App\Http\Controllers;

use App\Http\Requests\EncounterRequest;
use App\Models\Patient;
use App\Models\PatientEncounter;
use App\Services\EncounterService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class EncounterController extends Controller
{
    public function index(Request $request): View
    {
        $encounters = PatientEncounter::with(['patient', 'student'])->forWorkspace($request->user())->latest('attended_at')->latest('id')->paginate(15);

        return view('encounters.index', compact('encounters'));
    }

    public function create(Patient $patient): View
    {
        Gate::authorize('create', PatientEncounter::class);

        return view('encounters.form', ['patient' => $patient, 'encounter' => new PatientEncounter]);
    }

    public function store(EncounterRequest $request, Patient $patient, EncounterService $service): RedirectResponse
    {
        $encounter = $service->save($request->user(), $patient, $request->validated(), $request->file('images', []));

        return redirect()->route('encounters.show', $encounter)->with('success', 'Observation recorded.');
    }

    public function show(Request $request, PatientEncounter $encounter): View
    {
        Gate::authorize('view', $encounter);
        $encounter->load(['patient', 'student', 'images']);
        $private = $request->user()->can('privateFeedback', $encounter);
        if ($private) {
            $encounter->load(['hodReviews.hod', 'aiReviews' => fn ($q) => $q->latest()]);
        }

        return view('encounters.show', compact('encounter', 'private'));
    }

    public function edit(PatientEncounter $encounter): View
    {
        Gate::authorize('update', $encounter);

        return view('encounters.form', ['encounter' => $encounter, 'patient' => $encounter->patient]);
    }

    public function update(EncounterRequest $request, PatientEncounter $encounter, EncounterService $service): RedirectResponse
    {
        $service->save($request->user(), $encounter->patient, $request->validated(), $request->file('images', []), $encounter);

        return redirect()->route('encounters.show', $encounter)->with('success', 'Observation updated.');
    }
}
