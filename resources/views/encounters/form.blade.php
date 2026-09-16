@extends('layouts.app')
@section('title','Record observation')
@section('content')
<div class="page-heading"><div><span class="eyebrow">{{ $patient->display_name }}</span><h1>{{ $encounter->exists?'Edit observation':'Record an encounter' }}</h1><p class="text-muted">Capture your observations and what you learned.</p></div></div>
<form class="content-card" enctype="multipart/form-data" method="post" action="{{ $encounter->exists?route('encounters.update',$encounter):route('encounters.store',$patient) }}">@csrf @if($encounter->exists) @method('PATCH') @endif
<x-field name="attended_at" label="Attendance date and time" type="datetime-local" :value="$encounter->attended_at?->timezone(config('clinobserve.timezone'))->format('Y-m-d\TH:i')??now(config('clinobserve.timezone'))->format('Y-m-d\TH:i')" required/>
<p class="small text-muted">Times use {{ config('clinobserve.timezone') }}.</p>
@foreach(\App\Models\PatientEncounter::FIELDS as $field)<x-field :name="$field" type="textarea" :value="$encounter->$field" :required="$field==='summary'" maxlength="5000"/>@endforeach
<label class="form-label" for="images">Encounter images (optional)</label><input class="form-control mb-2" id="images" type="file" name="images[]" accept="image/png,image/jpeg,image/webp" multiple>
<p class="small text-muted">Up to five images per encounter. JPEG, PNG or WebP, max 5 MiB each and 4000 × 4000 pixels. Remove identifying content before upload.</p>
<button class="btn btn-primary">Save observation</button><a class="btn btn-light" href="{{ route('patients.show',$patient) }}">Cancel</a></form>
@endsection


