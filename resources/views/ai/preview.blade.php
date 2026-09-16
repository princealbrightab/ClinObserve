@extends('layouts.app')
@section('title','Review AI input')
@section('content')
<div class="page-heading"><div><span class="eyebrow">REVIEW BEFORE SENDING</span><h1>AI educational review</h1><p class="text-muted">Text only. No images, case labels, student details or internal IDs are sent.</p></div></div>
<div class="content-card"><h2>Information that will be sent</h2><p>Read every field below. Remove names, contact details and any other identifying information from your observation before proceeding.</p><pre class="payload-preview">{{ json_encode($payload,JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE) }}</pre>
<p class="small text-muted">{{ config('clinobserve.disclaimer') }}</p>
@if($enabled)<form method="post" action="{{ route('ai-reviews.store',$encounter) }}">@csrf<input type="hidden" name="request_key" value="{{ $requestKey }}"><input type="hidden" name="input_hash" value="{{ $hash }}"><label class="form-check mb-3"><input class="form-check-input" type="checkbox" name="privacy_confirmed" value="1" required><span class="form-check-label">I have checked this preview. It is synthetic or de-identified, and I agree to send this text to the configured external AI provider.</span></label><p class="text-muted">Requesting a review locks this observation and its images, including if the request fails. Faculty review remains necessary.</p><button class="btn btn-primary">Send for educational review</button></form>@else<div class="alert alert-light border">AI is disabled or not configured. Your observation has not been sent anywhere.</div>@endif
<a class="d-block mt-4" href="{{ route('encounters.show',$encounter) }}">← Return to observation</a></div>
@endsection


