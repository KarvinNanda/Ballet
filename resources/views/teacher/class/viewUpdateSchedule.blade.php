@extends('layouts.app')

@section('title', 'Update schedule · '.$course)

@section('content')
    @php
        // datetime-local accepts only Y-m-d\TH:i; old() (a failed save) still wins inside x-form.field.
        $value = \Carbon\Carbon::parse($schedule->date)->format('Y-m-d\TH:i');
    @endphp

    <x-page-header title="Update schedule" :subtitle="$course">
        <x-slot:actions>
            <a href="{{ route('viewScheduleClassTeacher', $schedule->class_id) }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left" aria-hidden="true"></i> Back to schedule</a>
        </x-slot:actions>
    </x-page-header>

    <form class="card" method="post" action="{{ route('updateScheduleClassTeacher') }}">
        @csrf
        <input type="hidden" value="{{ $schedule->id }}" name="scheduleId">
        <div class="card-body">
            <x-form.error-summary />
            <x-form.section title="Session">
                <x-form.field name="dateTime" label="Date and time" type="datetime-local" :value="$value" :min="now('Asia/Jakarta')->format('Y-m-d\TH:i')" required />
            </x-form.section>
            <div class="save-bar">
                <button type="submit" class="btn btn-primary">Save changes</button>
            </div>
        </div>
    </form>
@endsection
