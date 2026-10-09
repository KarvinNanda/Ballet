@extends('layouts.app')

@section('title', 'Add schedule · '.$class_label)

@section('content')
    <x-page-header title="Add schedule" :subtitle="$class_label">
        <x-slot:actions>
            <a href="{{ staff_route('schedule.index', $class->id) }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left" aria-hidden="true"></i> Back to schedule</a>
        </x-slot:actions>
    </x-page-header>

    <form class="card" method="post" action="{{ staff_route('schedule.store', $class->id) }}">
        @csrf
        <div class="card-body">
            <x-form.error-summary />
            <x-form.section title="Session">
                <x-form.field name="dateTime" label="Date and time" type="datetime-local" required />
            </x-form.section>
            <div class="save-bar">
                <button type="submit" class="btn btn-primary">Create schedule</button>
            </div>
        </div>
    </form>
@endsection
