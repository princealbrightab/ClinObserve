@extends('layouts.app')
@section('title','Faculty feedback')
@section('content')
<div class="page-heading"><div><span class="eyebrow">PRIVATE FACULTY REVIEW</span><h1>{{ $hodReview->encounter->patient->display_name }}</h1><p class="text-muted">{{ $hodReview->hod->name }} · Created {{ $hodReview->created_at->format('d M Y, H:i') }} · Updated {{ $hodReview->updated_at->format('d M Y, H:i') }}</p></div></div><div class="content-card"><p class="preserve">{{ $hodReview->comment }}</p>@can('update',$hodReview)<hr><form method="post" action="{{ route('hod-reviews.update',$hodReview) }}">@csrf @method('PATCH')<x-field name="comment" label="Edit your feedback" type="textarea" :value="$hodReview->comment" required/><button class="btn btn-primary">Save feedback</button></form>@endcan<a class="d-block mt-4" href="{{ route('encounters.show',$hodReview->encounter) }}">← Observation</a></div>
@endsection


