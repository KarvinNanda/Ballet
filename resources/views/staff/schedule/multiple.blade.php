@extends('layouts.app')

@section('title', 'Add weekly schedules')

@section('content')
    <x-page-header title="Add weekly schedules" :subtitle="$class_label">
        <x-slot:actions>
            @if ($class)
                <a href="{{ staff_route('schedule.index', $class->id) }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left" aria-hidden="true"></i> Back to schedule</a>
            @else
                <a href="{{ staff_route('class.index') }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left" aria-hidden="true"></i> Back to classes</a>
            @endif
        </x-slot:actions>
    </x-page-header>

    <form class="card" method="post" action="{{ staff_route('schedule.multiple.store') }}">
        @csrf
        @if ($class)
            <input type="hidden" value="{{ $class->id }}" name="classId">
        @endif
        <div class="card-body">
            <x-form.error-summary />
            <x-form.section title="Weekly sessions">
                @unless ($class)
                    <x-form.field name="classId" label="Class" type="select" required
                                  :options="$classes->mapWithKeys(fn ($c) => [$c->id => $c->class_name.' (#'.$c->id.')'])->all()" />
                @endunless
                <x-form.field name="dateTime" label="First session" type="datetime-local" required />
                <x-form.field name="ScheduleLoop" label="Number of weeks" type="number" min="1" max="52" required
                              help="Creates one schedule per week, starting from the first session." />
            </x-form.section>
            <div class="save-bar">
                <button type="submit" class="btn btn-primary">Create schedules</button>
            </div>
        </div>
    </form>
@endsection
