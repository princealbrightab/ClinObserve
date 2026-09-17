@extends('layouts.app')
@section('title', 'Professors')
@section('content')
<div class="page-heading"><div><span class="eyebrow">DEPARTMENT DIRECTORY</span><h1>Professors</h1></div><a class="btn btn-primary" href="{{ route('hod.professors.create') }}">+ Add professor</a></div>
<div class="content-card">
    <form class="d-flex gap-2 mb-4"><input class="form-control" name="q" aria-label="Search professors" placeholder="Search by name or email" value="{{ $q }}"><button class="btn btn-outline-primary">Search</button></form>
    <div class="table-responsive"><table class="table align-middle"><thead><tr><th>Professor</th><th>Assigned students</th><th>Access</th><th></th></tr></thead><tbody>
    @forelse($professors as $professor)
        <tr><td><strong>{{ $professor->name }}</strong><small class="d-block text-muted">{{ $professor->email }}</small></td><td>{{ $professor->assigned_students_count }}</td><td><span class="badge {{ $professor->is_active ? 'text-bg-success' : 'text-bg-secondary' }}">{{ $professor->is_active ? 'Active' : 'Inactive' }}</span></td><td><a href="{{ route('hod.professors.show', $professor) }}">Profile</a></td></tr>
    @empty
        <tr><td colspan="4" class="empty-state">No professors found.</td></tr>
    @endforelse
    </tbody></table></div>{{ $professors->links() }}
</div>
@endsection
