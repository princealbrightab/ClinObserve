@extends('layouts.app')
@section('title','Sign in')
@section('content')
<div class="auth-card"><a class="brand text-dark mb-5" href="{{ route('home') }}"><span class="brand-icon">C+</span> ClinObserve</a><span class="eyebrow">YOUR LEARNING JOURNEY</span><h1>Welcome back</h1><p class="text-muted mb-4">Sign in to your clinical learning workspace.</p><form method="post" action="{{ route('login.store') }}">@csrf<x-field name="email" type="email" label="College email" required autocomplete="username"/><x-field name="password" type="password" required autocomplete="current-password"/><div class="d-flex justify-content-between mb-4"><label><input type="checkbox" name="remember" value="1"> Remember me</label><a href="{{ route('password.request') }}">Forgot password?</a></div><button class="btn btn-primary w-100 py-2">Sign in →</button></form><p class="small text-muted mt-4 mb-0">Students receive access from their HOD. Public registration is not available.</p></div>
@endsection


