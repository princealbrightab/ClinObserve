@extends('layouts.app')
@section('title','Change password')
@section('content')
<div class="content-card narrow"><h1>Set your password</h1><p class="text-muted">{{ auth()->user()->must_change_password?'Choose a personal password before entering the workspace.':'Keep your account secure with a unique password.' }}</p><form method="post" action="{{ route('password.update') }}">@csrf @method('PUT')<x-field name="current_password" type="password" required autocomplete="current-password"/><x-field name="password" label="New password" type="password" required minlength="12" autocomplete="new-password"/><x-field name="password_confirmation" label="Confirm new password" type="password" required autocomplete="new-password"/><p class="small text-muted">At least 12 characters, including letters and numbers.</p><button class="btn btn-primary">Update password</button></form></div>
@endsection


