@extends('layouts.app')

@section('title', 'Add weekly schedules · '.$course)

@section('content')
    <x-page-header title="Add weekly schedules" :subtitle="$course">
        <x-slot:actions>
            <a href="{{ route('viewScheduleClassTeacher', $classId) }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left" aria-hidden="true"></i> Back to schedule</a>
        </x-slot:actions>
    </x-page-header>

    <form class="card" method="post" action="{{ route('addMultipleScheduleClassTeacher') }}">
        @csrf
        <input type="hidden" value="{{ $classId }}" name="classId">
        <div class="card-body">
            <x-form.error-summary />
            <x-form.section title="Weekly sessions">
                <x-form.field name="dateTime" label="First session" type="datetime-local" :min="now('Asia/Jakarta')->format('Y-m-d\TH:i')" required />
                <x-form.field name="ScheduleLoop" label="Number of weeks" type="number" min="1" max="52" required
                              help="Creates one schedule per week, starting from the first session." />
            </x-form.section>
            <div class="save-bar">
                <button type="submit" class="btn btn-primary">Create schedules</button>
            </div>
        </div>
    </form>
@endsection
