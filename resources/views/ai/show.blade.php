@extends('layouts.app')
@section('title','Educational feedback')
@section('content')
<div class="page-heading"><div><span class="eyebrow">AI REVIEW #{{ $aiReview->id }}</span><h1>Educational feedback</h1><p class="text-muted">{{ $aiReview->encounter->patient->display_name }} · {{ $aiReview->created_at->format('d M Y, H:i') }} · {{ ucfirst($aiReview->status) }}</p></div></div>
<div class="alert alert-warning">{{ config('clinobserve.disclaimer') }}</div><div class="content-card">
@if($aiReview->status==='succeeded')@foreach($aiReview->structured_response as $field=>$value)<h2>{{ str($field)->replace('_',' ')->title() }}</h2>@if(is_array($value))<ul>@forelse($value as $item)<li>{{ $item }}</li>@empty<li>No additional items supplied.</li>@endforelse</ul>@else<p class="preserve">{{ $value }}</p>@endif @endforeach
@elseif($aiReview->status==='failed')<h2>Review could not be completed</h2><p>The provider was unavailable or returned an unusable result. Your observation and previous reviews are safe. You may request a new review later.</p>
@else<h2>Review in progress</h2><p>Reload this page shortly. If the request was interrupted, return to the observation and request a new review after one minute.</p>@endif
<details class="mt-4"><summary>Review provenance</summary><p class="small text-muted mt-2">Provider: {{ $aiReview->provider }} · Model: {{ $aiReview->model }} · Prompt: {{ $aiReview->prompt_version }}</p><pre class="payload-preview">{{ json_encode($aiReview->input_snapshot,JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE) }}</pre></details><a class="d-block mt-4" href="{{ route('encounters.show',$aiReview->encounter) }}">← Observation and faculty feedback</a></div>
@endsection


