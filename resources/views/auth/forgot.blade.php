@extends('layouts.app')
@section('title','Recover access')
@section('content')
<div class="auth-card"><h1>Recover access</h1>@if(config('clinobserve.password_recovery'))<p class="text-muted">Enter your account email to request a reset link.</p><form method="post" action="{{ route('password.email') }}">@csrf<x-field name="email" type="email" required/><button class="btn btn-primary">Send reset link</button></form>@else<p class="text-muted">Email recovery is not enabled for this installation. Contact your HOD to receive a new temporary password.</p>@endif<a class="d-block mt-4" href="{{ route('login') }}">← Back to sign in</a></div>
@endsection


