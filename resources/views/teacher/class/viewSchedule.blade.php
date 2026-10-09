@extends('layouts.app')

@section('title', 'Schedule · '.$course)

@section('content')
    @php
        $badges = [
            \App\Support\AttendanceWindow::RECORDED => ['Recorded', 'success'],
            \App\Support\AttendanceWindow::OPEN => ['Open', 'warning'],
            \App\Support\AttendanceWindow::NOT_STARTED => ['Upcoming', 'neutral'],
            \App\Support\AttendanceWindow::MISSED => ['Missed', 'warning'],
        ];
    @endphp

    <x-page-header :title="'Schedule · '.$course">
        <x-slot:actions>
            <a href="{{ route('viewAllScheduleTeacher', auth()->id()) }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left" aria-hidden="true"></i> Back to schedules</a>
            <a href="{{ route('viewaddMultipleScheduleClass', $classId) }}" class="btn btn-outline-secondary">Add weekly schedules</a>
            <a href="{{ route('viewaddScheduleClass', $classId) }}" class="btn btn-primary"><i class="bi bi-plus-lg" aria-hidden="true"></i> Add schedule</a>
        </x-slot:actions>
    </x-page-header>

    <div class="card">
        <div class="card-body">
            @if ($schedules->isEmpty())
                <x-empty-state icon="calendar3" title="No schedules yet">
                    <x-slot:action>
                        <a href="{{ route('viewaddScheduleClass', $classId) }}" class="btn btn-primary">Add schedule</a>
                    </x-slot:action>
                </x-empty-state>
            @else
                <table class="table table-hover">
                    <thead>
                    <tr>
                        <th scope="col">Day</th>
                        <th scope="col">Date</th>
                        <th scope="col">Time</th>
                        <th scope="col">Status</th>
                        <th scope="col"><span class="visually-hidden">Actions</span></th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach ($schedules as $s)
                        @php
                            $when = \Carbon\Carbon::parse($s->date);
                            [$label, $tone] = $badges[$s->status];
                        @endphp
                        <tr>
                            <td>{{ $when->format('l') }}</td>
                            <td>{{ $when->format('d M Y') }}</td>
                            <td>{{ $when->format('H:i') }}</td>
                            <td><span class="status-badge status-badge-{{ $tone }}">{{ $label }}</span></td>
                            <td class="text-end text-nowrap">
                                {{-- A recorded session cannot be changed, and one that has started can only be moved by the head (the controller refuses both too). Deleting is not a window bypass. --}}
                                @unless ($s->recorded)
                                    @if ($s->status === \App\Support\AttendanceWindow::NOT_STARTED)
                                        <a href="{{ route('viewUpdateScheduleClassTeacher', ['scheduleId' => $s->id]) }}" class="btn btn-sm btn-outline-secondary">Update</a>
                                    @endif
                                    <x-confirm-form :action="route('deleteScheduleTeacher', ['id' => $s->id, 'classId' => $classId])" :message="'Delete the session on '.$when->format('d M Y, H:i').'? This cannot be undone.'" class="d-inline">
                                        <button type="submit" class="btn btn-sm btn-outline-danger">Delete…</button>
                                    </x-confirm-form>
                                @endunless
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    </div>
@endsection
