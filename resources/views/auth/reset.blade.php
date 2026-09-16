@extends('layouts.app')
@section('title','Reset password')
@section('content')
<div class="auth-card"><h1>Reset password</h1><form method="post" action="{{ route('password.store') }}">@csrf<input type="hidden" name="token" value="{{ $token }}"><x-field name="email" type="email" :value="$email" required/><x-field name="password" type="password" required minlength="12"/><x-field name="password_confirmation" type="password" required/><button class="btn btn-primary">Reset password</button></form></div>
@endsection


