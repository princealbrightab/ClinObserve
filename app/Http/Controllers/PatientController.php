<?php

namespace App\Http\Controllers;

use App\Http\Requests\PatientRequest;
use App\Models\Patient;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class PatientController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate(['q' => ['nullable', 'string', 'max:120'], 'gender' => ['nullable', 'in:female,male,other,unknown,undisclosed'], 'condition' => ['nullable', 'string', 'max:150']]);
        $visibleEncounters = fn ($query) => $query->visibleToProfessor($request->user());
        $patients = Patient::query()->when($request->user()->isProfessor(), fn ($query) => $query->whereHas('encounters', $visibleEncounters))
            ->withCount(['encounters' => $visibleEncounters])->withMax(['encounters' => $visibleEncounters], 'attended_at')
            ->when($filters['q'] ?? null, fn ($q, $term) => $q->where(fn ($q) => $q->where('display_name', 'like', '%'.$term.'%')->orWhere('case_number', 'like', '%'.$term.'%')))
            ->when($filters['gender'] ?? null, fn ($q, $v) => $q->where('gender', $v))->when($filters['condition'] ?? null, fn ($q, $v) => $q->where('condition', 'like', '%'.$v.'%'))->latest('id')->paginate(12)->withQueryString();

        return view('patients.index', compact('patients'));
    }

    public function create(): View
    {
        Gate::authorize('create', Patient::class);

        return view('patients.form', ['patient' => new Patient]);
    }

    public function store(PatientRequest $request): RedirectResponse
    {
        $patient = new Patient($request->safe()->except('privacy_confirmed'));
        $patient->created_by = $request->user()->id;
        $patient->updated_by = $request->user()->id;
        $patient->save();

        return redirect()->route('patients.show', $patient)->with('success', 'Case created. You can now record an observation.');
    }

    public function show(Request $request, Patient $patient): View
    {
        Gate::authorize('view', $patient);
        $encounters = $patient->encounters()->visibleToProfessor($request->user())->with(['student:id,name', 'images'])->latest('attended_at')->latest('id')->paginate(10);

        return view('patients.show', compact('patient', 'encounters'));
    }

    public function edit(Patient $patient): View
    {
        Gate::authorize('update', $patient);

        return view('patients.form', compact('patient'));
    }

    public function update(PatientRequest $request, Patient $patient): RedirectResponse
    {
        DB::transaction(function () use ($request, $patient): void {
            $locked = Patient::whereKey($patient->id)->lockForUpdate()->firstOrFail();
            Gate::authorize('update', $locked);
            $locked->fill($request->safe()->except('privacy_confirmed'));
            $locked->updated_by = $request->user()->id;
            $locked->save();
        });

        return redirect()->route('patients.show', $patient)->with('success', 'Case updated.');
    }
}
