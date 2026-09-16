@props(['encounters'])
<div class="table-responsive"><table class="table align-middle"><thead><tr><th>Patient case</th><th>Student</th><th>Observed</th><th>Summary</th><th></th></tr></thead><tbody>
@forelse($encounters as $entry)<tr><td><a class="fw-semibold" href="{{ route('patients.show',$entry->patient) }}">{{ $entry->patient->display_name }}</a><small class="d-block text-muted">{{ str($entry->patient->case_number)->limit(20) }}</small></td><td>{{ $entry->student->name }}</td><td class="text-nowrap">{{ $entry->attended_at->timezone(config('clinobserve.timezone'))->format('d M Y') }}<small class="d-block text-muted">{{ $entry->attended_at->format('h:i A') }}</small></td><td>{{ str($entry->summary)->limit(80) }}</td><td><a class="btn btn-sm btn-light" href="{{ route('encounters.show',$entry) }}">View →</a></td></tr>
@empty<tr><td colspan="5"><div class="empty-state">No observations yet. Open a patient case to record your first encounter.</div></td></tr>@endforelse
</tbody></table></div>


