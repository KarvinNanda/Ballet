@extends('layouts.app')

@section('title', 'Attendance · '.$class_label)

@section('content')
    @php($when = \Carbon\Carbon::parse($schedule->date))

    <x-page-header :title="'Attendance · '.$class_label.' · '.$when->format('D d M Y, H:i')">
        <x-slot:actions>
            <a href="{{ staff_route('schedule.index', $schedule->class_id) }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left" aria-hidden="true"></i> Back to schedule</a>
        </x-slot:actions>
    </x-page-header>

    <form class="card" method="post" action="{{ staff_route('attendance.update', $schedule->id) }}">
        @csrf
        <div class="card-body">
            <x-form.error-summary />
            @if ($students->isEmpty())
                <x-empty-state icon="people" title="No active students in this class" />
            @else
                @include('partials.attendance-form-table', ['students' => $students, 'details' => $details, 'mustPay' => $mustPay, 'hasHeader' => $hasHeader])
                <div class="save-bar">
                    <button type="submit" class="btn btn-primary">Save attendance</button>
                </div>
            @endif
        </div>
    </form>
@endsection
