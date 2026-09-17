<!doctype html>
<html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>@yield('title','Workspace') · ClinObserve</title><link rel="stylesheet" href="{{ asset('vendor/bootstrap/bootstrap.min.css') }}"><link rel="stylesheet" href="{{ asset('css/clinobserve.css') }}"></head>
<body>
@auth
<aside class="sidebar" id="sidebar"><a class="brand" href="{{ route('dashboard') }}"><span class="brand-icon">C<span>+</span></span> ClinObserve</a><div class="workspace-label">CLINICAL LEARNING WORKSPACE</div>
<nav aria-label="Main navigation">
<a class="{{ request()->routeIs('dashboard','hod.dashboard')?'active':'' }}" href="{{ route(auth()->user()->isHod()?'hod.dashboard':'dashboard') }}">▦ <span>Overview</span></a>
@can('faculty')<a class="{{ request()->routeIs('hod.students.*','professor.students.*')?'active':'' }}" href="{{ route(auth()->user()->isHod()?'hod.students.index':'professor.students.index') }}">♙ <span>Students</span></a>@endcan
<a class="{{ request()->routeIs('patients.*')?'active':'' }}" href="{{ route('patients.index') }}">▤ <span>Patient cases</span></a>
<a class="{{ request()->routeIs('encounters.*')?'active':'' }}" href="{{ route('encounters.index') }}">▧ <span>{{ auth()->user()->isFaculty()?'Observations':'My observations' }}</span></a>
<a class="{{ request()->routeIs('*calendar*')?'active':'' }}" href="{{ route(auth()->user()->isHod()?'hod.calendar.index':'calendar.index') }}">▦ <span>Calendar</span></a>
<a class="{{ request()->routeIs('*ai-reviews*')?'active':'' }}" href="{{ route(auth()->user()->isHod()?'hod.ai-reviews.index':'ai-reviews.index') }}">✧ <span>AI reviews</span></a>
@can('hod')<a class="{{ request()->routeIs('hod.reports.*')?'active':'' }}" href="{{ route('hod.reports.index') }}">▥ <span>Reports</span></a>@endcan
@can('hod')<a class="{{ request()->routeIs('hod.professors.*')?'active':'' }}" href="{{ route('hod.professors.index') }}"><span>Professors</span></a>@endcan
</nav><div class="sidebar-note"><span class="badge text-bg-light">EDUCATION ONLY</span><p>A place to observe, reflect<br>and learn together.</p></div></aside>
<div class="workspace"><header class="topbar"><div class="d-flex align-items-center gap-3"><button class="btn btn-light d-lg-none" id="menu-toggle" aria-controls="sidebar" aria-expanded="false" aria-label="Toggle navigation">☰</button><span class="text-muted">Department workspace</span></div><div class="d-flex align-items-center gap-3"><a href="{{ route('profile.edit') }}" class="user-link"><span class="avatar">{{ mb_substr(auth()->user()->name,0,1) }}</span><span>{{ auth()->user()->name }}<small>{{ auth()->user()->isHod()?'Head of department':(auth()->user()->isProfessor()?'Professor':'Student') }}</small></span></a><form method="post" action="{{ route('logout') }}">@csrf<button class="btn btn-sm btn-outline-secondary">Sign out</button></form></div></header>
<main class="page">
@else
<main class="guest-page">
@endauth
@if(session('success'))<div class="alert alert-success" role="status">{{ session('success') }}</div>@endif
@if($errors->any())<div class="alert alert-danger" role="alert"><strong>Please check the following:</strong><ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
@yield('content')
<footer class="page-footer">ClinObserve · Academic medical education only. Not for diagnosis, treatment, prescribing or emergency decisions.</footer>
</main>@auth</div>@endauth
<script src="{{ asset('vendor/bootstrap/bootstrap.bundle.min.js') }}" defer></script><script src="{{ asset('js/clinobserve.js') }}" defer></script></body></html>


