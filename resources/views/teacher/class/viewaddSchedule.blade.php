@extends('layouts.app')

@section('title', 'Add schedule · '.$course)

@section('content')
    <x-page-header title="Add schedule" :subtitle="$course">
        <x-slot:actions>
            <a href="{{ route('viewScheduleClassTeacher', $classId) }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left" aria-hidden="true"></i> Back to schedule</a>
        </x-slot:actions>
    </x-page-header>

    <form class="card" method="post" action="{{ route('addScheduleClassTeacher', ['id' => $classId]) }}">
        @csrf
        {{-- Kept for the field contract; addSchedule() uses the authorized route id, never this value. --}}
        <input type="hidden" value="{{ $classId }}" name="classId">
        <div class="card-body">
            <x-form.error-summary />
            <x-form.section title="Session">
                <x-form.field name="dateTime" label="Date and time" type="datetime-local" :min="now('Asia/Jakarta')->format('Y-m-d\TH:i')" required />
            </x-form.section>
            <div class="save-bar">
                <button type="submit" class="btn btn-primary">Create schedule</button>
            </div>
        </div>
    </form>
@endsection
