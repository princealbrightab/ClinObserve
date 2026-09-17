@extends('layouts.app')
@section('title', $professor->exists ? 'Edit professor' : 'Add professor')
@section('content')
<div class="page-heading"><div><span class="eyebrow">FACULTY MANAGEMENT</span><h1>{{ $professor->exists ? 'Edit professor' : 'Add professor' }}</h1></div></div>
<form class="content-card" method="post" action="{{ $professor->exists ? route('hod.professors.update', $professor) : route('hod.professors.store') }}">
    @csrf @if($professor->exists) @method('PATCH') @endif
    <div class="row"><div class="col-md-6"><x-field name="name" label="Full name" :value="$professor->name" required/></div><div class="col-md-6"><x-field name="email" label="Login email" type="email" :value="$professor->email" required/></div></div>
    @if(!$professor->exists)<x-field name="password" label="Temporary password" type="password" required minlength="12" autocomplete="new-password"/>@endif
    <button class="btn btn-primary">Save professor</button><a class="btn btn-light" href="{{ route('hod.professors.index') }}">Cancel</a>
</form>
@endsection
