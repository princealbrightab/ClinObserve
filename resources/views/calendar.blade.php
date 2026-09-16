@extends('layouts.app')
@section('title','Learning calendar')
@section('content')
<div class="page-heading"><div><span class="eyebrow">ATTENDANCE & OBSERVATIONS</span><h1>Learning calendar</h1><p class="text-muted">Explore the cases observed on each day. Times: {{ config('clinobserve.timezone') }}.</p></div></div>
<div class="content-card"><form class="row g-3 mb-4" method="get"><div class="col-md-4"><x-field name="month" type="month" :value="$month->format('Y-m')" label="Month"/></div>@can('hod')<div class="col-md-5"><label for="student_id" class="form-label">Student</label><select class="form-select" name="student_id" id="student_id"><option value="">All students</option>@foreach($students as $student)<option value="{{ $student->id }}" @selected((string)$studentId===(string)$student->id)>{{ $student->name }}</option>@endforeach</select></div>@endcan<div class="col-md-3 d-flex align-items-center"><button class="btn btn-primary">Show calendar</button></div></form>
<div class="section-heading"><a href="{{ route(request()->route()->getName(),['month'=>$month->subMonth()->format('Y-m'),'student_id'=>auth()->user()->isHod()?$studentId:null]) }}">← Previous</a><h2>{{ $month->format('F Y') }}</h2><a href="{{ route(request()->route()->getName(),['month'=>$month->addMonth()->format('Y-m'),'student_id'=>auth()->user()->isHod()?$studentId:null]) }}">Next →</a></div>
<div class="calendar-grid">@foreach(['Mon','Tue','Wed','Thu','Fri','Sat','Sun'] as $day)<div class="calendar-label">{{ $day }}</div>@endforeach
@foreach($days as $day)<a class="calendar-day {{ $day['date']->month!==$month->month?'outside':'' }} {{ $selected===$day['date']->toDateString()?'selected':'' }}" href="{{ route(request()->route()->getName(),['month'=>$month->format('Y-m'),'date'=>$day['date']->toDateString(),'student_id'=>auth()->user()->isHod()?$studentId:null]) }}"><strong>{{ $day['date']->day }}</strong>@if($day['count'])<span>{{ $day['count'] }} <span class="d-none d-sm-inline">encounters</span></span>@endif</a>@endforeach</div></div>
<div class="content-card mt-4"><div class="section-heading"><h2>{{ \Carbon\Carbon::parse($selected)->format('d F Y') }}</h2><span class="badge topic-badge">{{ $entries->unique('patient_id')->count() }} patients · {{ $entries->count() }} encounters</span></div><x-encounter-table :encounters="$entries"/></div>
@endsection


