<?php

namespace App\Http\Controllers;

use App\Models\PatientEncounter;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Database\Query\Expression;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function __invoke(Request $request): View
    {
        $data = $request->validate(['from' => ['nullable', 'date_format:Y-m-d'], 'to' => ['nullable', 'date_format:Y-m-d'], 'student_id' => ['nullable', 'integer', Rule::exists('users', 'id')->where('role', 'student')]]);
        $tz = config('clinobserve.timezone');
        $from = CarbonImmutable::parse($data['from'] ?? now($tz)->startOfYear()->toDateString(), $tz);
        $to = CarbonImmutable::parse($data['to'] ?? now($tz)->toDateString(), $tz)->addDay();
        if ($from->gte($to) || $from->startOfMonth()->addMonths(120)->lt($to)) {
            throw ValidationException::withMessages(['to' => 'Choose an end date after the start date and a range of at most ten years.']);
        }
        $scope = fn ($q) => $q->where('attended_at', '>=', $from->utc())->where('attended_at', '<', $to->utc());
        $students = User::where('role', 'student')->when($data['student_id'] ?? null, fn ($q, $id) => $q->whereKey($id))
            ->withCount(['encounters' => $scope])->withAggregate(['encounters as patients_count' => $scope], new Expression('DISTINCT patient_id'), 'count')
            ->withMax(['encounters' => $scope], 'attended_at')->orderBy('name')->paginate(20)->withQueryString();
        $monthly = [];
        for ($month = $from->startOfMonth(); $month->lt($to); $month = $month->addMonth()) {
            $start = $month->max($from);
            $end = $month->addMonth()->min($to);
            $monthly[$month->format('M Y')] = PatientEncounter::where('attended_at', '>=', $start->utc())->where('attended_at', '<', $end->utc())->when($data['student_id'] ?? null, fn ($q, $id) => $q->where('student_id', $id))->count();
        }

        return view('reports',compact('students','monthly'));
    }
}
