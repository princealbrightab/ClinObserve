@extends('layouts.app')
@section('title', $professor->name)
@section('content')
<div class="page-heading"><div><span class="eyebrow">PROFESSOR PROFILE</span><h1>{{ $professor->name }}</h1><p class="text-muted">{{ $professor->email }}</p></div><a class="btn btn-primary" href="{{ route('hod.professors.edit', $professor) }}">Edit professor</a></div>
<div class="content-card mb-4"><div class="section-heading"><h2>Assigned students</h2><a href="{{ route('hod.students.index') }}">Manage student assignments</a></div>
    <div class="table-responsive"><table class="table align-middle"><thead><tr><th>Student</th><th>Roll number</th><th>Encounters</th><th></th></tr></thead><tbody>
    @forelse($students as $student)<tr><td>{{ $student->name }}</td><td>{{ $student->studentProfile?->roll_number }}</td><td>{{ $student->encounters_count }}</td><td><a href="{{ route('hod.students.edit', $student) }}">Edit assignment</a></td></tr>
    @empty<tr><td colspan="4" class="empty-state">No students assigned.</td></tr>@endforelse
    </tbody></table></div>{{ $students->links() }}
</div>
<div class="content-card mb-4"><h2>Assign a student</h2>@if($availableStudents->isEmpty())<p class="empty-state mb-0">No unassigned students are available for this professor.</p>@else<form method="post" action="{{ route('hod.professors.assign-students', $professor) }}">@csrf<label class="form-label" for="student_id">Select student</label><select class="form-select mb-3" name="student_id" id="student_id" required><option value="">Choose a student</option>@foreach($availableStudents as $student)<option value="{{ $student->id }}">{{ $student->name }}{{ $student->studentProfile?->roll_number ? ' — '.$student->studentProfile->roll_number : '' }}</option>@endforeach</select><button class="btn btn-primary">Assign student</button></form>@endif</div>
<div class="content-card"><h2>Account access</h2><p><span class="badge {{ $professor->is_active ? 'text-bg-success' : 'text-bg-secondary' }}">{{ $professor->is_active ? 'Active' : 'Inactive' }}</span></p>
    <form method="post" action="{{ route('hod.professors.status.update', $professor) }}" data-confirm="Change this professor's access? Existing sessions will end.">@csrf @method('PATCH')<input type="hidden" name="is_active" value="{{ $professor->is_active ? 0 : 1 }}"><button class="btn btn-outline-secondary">{{ $professor->is_active ? 'Deactivate' : 'Activate' }} account</button></form>
    <hr><h3>Reset credentials</h3><form method="post" action="{{ route('hod.professors.credentials.update', $professor) }}" data-confirm="Reset the password and revoke existing sessions?">@csrf @method('PUT')<x-field name="password" label="Temporary password" type="password" required minlength="12" autocomplete="new-password"/><x-field name="password_confirmation" label="Confirm password" type="password" required autocomplete="new-password"/><button class="btn btn-outline-primary">Reset password</button></form>
</div>
@endsection
