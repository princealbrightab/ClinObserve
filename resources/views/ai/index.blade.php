@extends('layouts.app')
@section('title','AI reviews')
@section('content')
<div class="page-heading"><div><span class="eyebrow">OPTIONAL LEARNING SUPPORT</span><h1>AI educational reviews</h1><p class="text-muted">Your review history, ready for reflection with qualified faculty.</p></div></div>
@if(!$enabled)<div class="alert alert-light border">AI is not configured for this installation. Cases, observations and faculty feedback work normally.</div>@endif
<div class="content-card"><div class="table-responsive"><table class="table"><thead><tr><th>Case</th><th>Requested by</th><th>Status</th><th>Date</th><th></th></tr></thead><tbody>@forelse($reviews as $review)<tr><td>{{ $review->encounter->patient->display_name }}</td><td>{{ $review->requester->name }}</td><td>{{ ucfirst($review->status) }}</td><td>{{ $review->created_at->format('d M Y') }}</td><td><a href="{{ route('ai-reviews.show',$review) }}">View →</a></td></tr>@empty<tr><td colspan="5" class="empty-state">No reviews yet. Request an educational review from your own observation page.</td></tr>@endforelse</tbody></table></div>{{ $reviews->links() }}</div>
@endsection


