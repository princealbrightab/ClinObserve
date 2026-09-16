@extends('layouts.app')
@section('title','Welcome')
@section('content')
<section class="welcome-card"><div class="brand text-dark"><span class="brand-icon">C+</span> ClinObserve</div><span class="eyebrow">OBSERVE. REFLECT. GROW.</span><h1>Every observation.<br>A step toward understanding.</h1><p class="lead text-muted">Your clinical learning journey, thoughtfully connected. Document patient cases, reflect on encounters and learn with faculty feedback.</p><a href="{{ route(auth()->check()?'dashboard':'login') }}" class="btn btn-primary btn-lg">Enter your workspace →</a><div class="mt-5 d-flex gap-4 flex-wrap"><span>✓ Student observations</span><span>✓ Faculty guidance</span><span>✓ Learning calendar</span></div><p class="small text-muted mt-4 mb-0">Use synthetic or de-identified cases. Educational support only.</p></section>
@endsection


