<?php

namespace App\Http\Controllers;

use App\Http\Requests\CalendarRequest;
use App\Models\PatientEncounter;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\View\View;

class CalendarController extends Controller
{
    public function __invoke(CalendarRequest $request): View
    {
        $tz = config('clinobserve.timezone');
        $month = CarbonImmutable::parse(($request->validated('month') ?? now($tz)->format('Y-m')).'-01', $tz)->startOfMonth();
        $selected = $request->validated('date') ?? $month->toDateString();
        $studentId = $request->user()->isHod() ? $request->validated('student_id') : $request->user()->id;
        $query = PatientEncounter::with(['patient', 'student'])->when($studentId, fn ($q) => $q->where('student_id', $studentId));
        $monthEntries = (clone $query)->where('attended_at', '>=', $month->utc())->where('attended_at', '<', $month->addMonth()->utc())->get()->groupBy(fn ($e) => $e->attended_at->timezone($tz)->toDateString());
        $day = CarbonImmutable::parse($selected, $tz);
        $entries = (clone $query)->where('attended_at', '>=', $day->utc())->where('attended_at', '<', $day->addDay()->utc())->orderBy('attended_at')->get();
        $days = [];
        for ($date = $month->startOfWeek(); $date->lt($month->addMonth()->startOfWeek()->addWeek()); $date = $date->addDay()) {
            $days[] = ['date' => $date, 'count' => $monthEntries->get($date->toDateString(), collect())->count()];
        }
        $students = $request->user()->isHod() ? User::where('role', 'student')->orderBy('name')->get(['id', 'name']) : collect();

        return view('calendar', compact('month', 'selected', 'studentId', 'entries', 'days', 'students'));
    }
}
