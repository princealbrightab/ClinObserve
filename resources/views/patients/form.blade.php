@extends('layouts.app')
@section('title',$patient->exists?'Edit case':'Add patient case')
@section('content')
<div class="page-heading"><div><span class="eyebrow">CASE DOCUMENTATION</span><h1>{{ $patient->exists?'Edit patient case':'Add a patient case' }}</h1><p class="text-muted">Use a synthetic name or de-identified alias. Do not enter personal identifiers.</p></div></div>
<form class="content-card" method="post" action="{{ $patient->exists?route('patients.update',$patient):route('patients.store') }}">@csrf @if($patient->exists) @method('PATCH') @endif
<h2>Case essentials</h2><div class="row"><div class="col-md-6"><x-field name="display_name" label="De-identified display name" :value="$patient->display_name" required maxlength="120"/></div><div class="col-md-6"><x-select name="data_classification" label="Data type" :value="$patient->data_classification??'synthetic'" :options="['synthetic'=>'Synthetic / fictional','deidentified'=>'De-identified educational case']" required/></div><div class="col-md-4"><x-field name="age_years" label="Age at admission (years)" type="number" :value="$patient->age_years" required min="0" max="130"/></div><div class="col-md-4"><x-select name="gender" label="Gender" :value="$patient->gender" :options="['female'=>'Female','male'=>'Male','other'=>'Other','unknown'=>'Unknown','undisclosed'=>'Undisclosed']" required/></div><div class="col-md-4"><x-field name="admission_date" type="date" :value="$patient->admission_date?->format('Y-m-d')??now()->toDateString()" required/></div></div>
<x-field name="condition" label="Condition / learning topic" :value="$patient->condition"/>
<h2 class="mt-4">Clinical context <span class="text-muted fs-6">(optional)</span></h2><div class="row">@foreach(\App\Models\Patient::CLINICAL_FIELDS as $field)<div class="col-md-6"><x-field :name="$field" type="textarea" :value="$patient->$field" maxlength="5000"/></div>@endforeach</div>
<label class="form-check mb-4"><input class="form-check-input" type="checkbox" name="privacy_confirmed" value="1" required><span class="form-check-label">I confirm this case is synthetic or de-identified and contains no direct patient identifiers.</span></label>
<button class="btn btn-primary">Save case</button><a class="btn btn-light" href="{{ route('patients.index') }}">Cancel</a></form>
@endsection


