@extends('layouts.app')
@section('title','My profile')
@section('content')
<div class="page-heading"><div><span class="eyebrow">ACCOUNT</span><h1>My profile</h1><p class="text-muted">{{ $user->name }} · {{ $user->email }}</p></div><a class="btn btn-outline-primary" href="{{ route('password.edit') }}">Change password</a></div>
@if($user->studentProfile)<form class="content-card" method="post" enctype="multipart/form-data" action="{{ route('profile.update') }}">@csrf @method('PATCH')<h2>Personal details</h2><p class="text-muted">Contact your HOD to change your name, email or academic records.</p>@if($user->studentProfile->avatar_path)<img class="profile-photo mb-4" src="{{ route('students.avatar',$user) }}" alt="Your profile image">@endif
<div class="row"><div class="col-md-6"><x-field name="phone" :value="$user->studentProfile->phone"/></div><div class="col-md-6"><x-field name="date_of_birth" type="date" :value="$user->studentProfile->date_of_birth?->format('Y-m-d')"/></div></div><x-select name="gender" label="Gender" :value="$user->studentProfile->gender" :options="['female'=>'Female','male'=>'Male','other'=>'Other','undisclosed'=>'Undisclosed']"/>
<x-field name="address" type="textarea" :value="$user->studentProfile->address"/><x-field name="bio" label="About me" type="textarea" :value="$user->studentProfile->bio"/>
<label for="avatar" class="form-label">Profile photo (JPEG, PNG or WebP; max 2 MiB)</label><input class="form-control mb-4" name="avatar" id="avatar" type="file" accept="image/jpeg,image/png,image/webp"><button class="btn btn-primary">Save profile</button></form>@else<div class="content-card"><h2>{{ $user->isHod()?'Head of department':'Academic profile' }}</h2><p class="text-muted">{{ $user->isHod()?'Your faculty account can manage students and review academic observations.':'Ask your HOD to complete your academic profile.' }}</p></div>@endif
@endsection


