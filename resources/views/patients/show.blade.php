@extends('layouts.app')
@section('title',$patient->display_name)
@section('content')
<div class="page-heading"><div><span class="eyebrow">{{ $patient->case_number }}</span><h1>{{ $patient->display_name }}</h1><p class="text-muted">{{ $patient->age_years }} years · {{ ucfirst($patient->gender) }} · Admitted {{ $patient->admission_date->format('d M Y') }} <span class="badge text-bg-light">{{ ucfirst($patient->data_classification) }}</span></p></div><div>@can('update',$patient)<a class="btn btn-light" href="{{ route('patients.edit',$patient) }}">Edit case</a>@endcan @can('create',\App\Models\PatientEncounter::class)<a class="btn btn-primary" href="{{ route('encounters.create',$patient) }}">+ Record encounter</a>@endcan</div></div>
<div class="content-card"><h2>Case context</h2>@if($patient->condition)<span class="badge rounded-pill topic-badge mb-3">{{ $patient->condition }}</span>@endif<div class="row">@foreach(\App\Models\Patient::CLINICAL_FIELDS as $field)@if($patient->$field)<div class="col-md-6 mb-3"><h3>{{ str($field)->replace('_',' ')->title() }}</h3><p class="preserve">{{ $patient->$field }}</p></div>@endif
@endforeach</div></div>
<div class="content-card mt-4"><div class="section-heading"><div><h2>Observation timeline</h2><p class="text-muted mb-0">Each encounter preserves a student's own observations.</p></div></div>
@forelse($encounters as $entry)<article class="timeline-entry"><div class="d-flex justify-content-between gap-3"><h3>{{ $entry->student->name }}</h3><small class="text-muted">{{ $entry->attended_at->timezone(config('clinobserve.timezone'))->format('d M Y · h:i A') }}</small></div><p class="preserve">{{ $entry->summary }}</p>@if($entry->images->isNotEmpty())<div class="image-grid mb-3">@foreach($entry->images as $image)<a href="{{ route('encounter-images.show',$image) }}"><img alt="De-identified encounter image" src="{{ route('encounter-images.show',$image) }}" loading="lazy"></a>@endforeach</div>@endif<a href="{{ route('encounters.show',$entry) }}">View observation →</a></article>@empty<div class="empty-state">This case has no observations yet.</div>@endforelse
{{ $encounters->links() }}</div>
@endsection


