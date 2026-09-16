@extends('layouts.app')
@section('title',$student->exists?'Edit student':'Add student')
@section('content')
<div class="page-heading"><div><span class="eyebrow">STUDENT MANAGEMENT</span><h1>{{ $student->exists?'Edit student':'Welcome a new student' }}</h1><p class="text-muted">Create their account and academic profile in one step.</p></div></div><form class="content-card" method="post" action="{{ $student->exists?route('hod.students.update',$student):route('hod.students.store') }}">@csrf @if($student->exists) @method('PATCH') @endif
<div class="row"><div class="col-md-6"><x-field name="name" label="Full name" :value="$student->name" required/></div><div class="col-md-6"><x-field name="email" label="Login email" type="email" :value="$student->email" required/></div></div>
@if(!$student->exists)<x-field name="password" label="Initial password" type="password" required minlength="12" autocomplete="new-password"/><p class="small text-muted">Use at least 12 characters, with letters and numbers. Share it securely. The student must change it on first login.</p>@endif
<h2 class="mt-4">Academic details</h2><div class="row">@foreach(['roll_number','registration_number','batch','academic_year','college','course','department','joining_year'] as $field)<div class="col-md-6"><x-field :name="$field" :value="$student->studentProfile?->$field" :required="in_array($field,['roll_number','batch','academic_year'])" :type="$field==='joining_year'?'number':'text'"/></div>@endforeach</div><button class="btn btn-primary">Save student</button><a class="btn btn-light" href="{{ route('hod.students.index') }}">Cancel</a></form>
@endsection


