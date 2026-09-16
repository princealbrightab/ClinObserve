@extends('layouts.app')
@section('title','Observations')
@section('content')
<div class="page-heading"><div><span class="eyebrow">CLINICAL ENCOUNTERS</span><h1>{{ auth()->user()->isHod()?'Student observations':'My observations' }}</h1><p class="text-muted">A record of attendance, clinical documentation and reflection.</p></div><a class="btn btn-primary" href="{{ route('patients.index') }}">Find a case →</a></div><div class="content-card"><x-encounter-table :encounters="$encounters"/>{{ $encounters->links() }}</div>
@endsection


