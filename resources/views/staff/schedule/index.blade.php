@extends('layouts.app')

@section('title', 'Schedule · '.$class_label)

@section('content')
    @php
        $classUrl = (int) $class->is_freeze === 1 ? staff_route('class.freeze.show', $class->id) : staff_route('class.show', $class->id);
    @endphp

    <x-page-header :title="'Schedule · '.$class_label">
        <x-slot:actions>
            <a href="{{ $classUrl }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left" aria-hidden="true"></i> Back to class</a>
            <a href="{{ staff_route('schedule.multiple.create', $class->id) }}" class="btn btn-outline-secondary">Add weekly schedules</a>
            <a href="{{ staff_route('schedule.create', $class->id) }}" class="btn btn-primary"><i class="bi bi-plus-lg" aria-hidden="true"></i> Add schedule</a>
        </x-slot:actions>
    </x-page-header>

    <div class="card">
        <div class="card-body">
            @if ($schedules->isEmpty())
                <x-empty-state icon="calendar3" title="No schedules yet">
                    <x-slot:action>
                        <a href="{{ staff_route('schedule.create', $class->id) }}" class="btn btn-primary">Add schedule</a>
                    </x-slot:action>
                </x-empty-state>
            @else
                <table class="table table-hover">
                    <thead>
                    <tr>
                        <th scope="col">Day</th>
                        <th scope="col">Date</th>
                        <th scope="col">Time</th>
                        <th scope="col"><span class="visually-hidden">Actions</span></th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach ($schedules as $s)
                        @php($when = \Carbon\Carbon::parse($s->date))
                        <tr>
                            <td>{{ $when->format('l') }}</td>
                            <td>{{ $when->format('d M Y') }}</td>
                            <td>{{ $when->format('H:i') }}</td>
                            <td class="text-end text-nowrap">
                                <a href="{{ staff_route('schedule.edit', $s->id) }}" class="btn btn-sm btn-outline-secondary">Update</a>
                                <x-row-menu :label="'More actions for the session of '.$when->format('d M Y, H:i')">
                                    @can('attendance.record')
                                        <li><a href="{{ staff_route('attendance.edit', $s->id) }}" class="dropdown-item">Attendance</a></li>
                                        <li><hr class="dropdown-divider"></li>
                                    @endcan
                                    <li>
                                        <x-confirm-form :action="staff_route('schedule.destroy', $s->id)" :message="'Delete the session of '.$when->format('d M Y, H:i').'? This cannot be undone.'">
                                            <button type="submit" class="dropdown-item text-danger">Delete…</button>
                                        </x-confirm-form>
                                    </li>
                                </x-row-menu>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
                <div class="mt-3">{{ $schedules->links() }}</div>
            @endif
        </div>
    </div>
@endsection
