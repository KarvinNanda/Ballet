@extends('layouts.app')

@section('title', 'Attendance · '.$class_label)

@section('content')
    @php
        $when = \Carbon\Carbon::parse($schedule->date);
        // "Attend" is not a reason: a present student is the ticked box. An empty reason is saved as Absent (AttendanceRecorder).
        $reasons = ['Absent', 'Permission', 'Sick'];
    @endphp

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
                <table class="table table-hover">
                    <thead>
                    <tr>
                        <th scope="col">Student</th>
                        <th scope="col">Present</th>
                        <th scope="col">Reason if absent</th>
                        <th scope="col">Notes</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach ($students as $c)
                        @php
                            $i = $loop->index;
                            $d = $details->get($c->id);
                            $stored = $d?->Description;
                            // Older rows (and the seed) store Indonesian values; show them as the reason the app writes today.
                            $description = ['Sakit' => 'Sick', 'Izin' => 'Permission'][$stored] ?? $stored;
                            $legacy = $d && filled($stored) && $description !== 'Attend' && ! in_array($description, $reasons, true);
                        @endphp
                        <tr>
                            <td>
                                {{ $c->nama }}
                                @if (filled($c->nis))
                                    <div class="row-note">NIS {{ $c->nis }}</div>
                                @endif
                            </td>
                            @if ($mustPay->has($c->id))
                                <td colspan="3"><span class="status-badge status-badge-warning">Payment required</span></td>
                            @else
                                <td>
                                    <input type="hidden" value="{{ $c->id }}" name="student_id[{{ $i }}]">
                                    <input type="hidden" name="check[{{ $i }}]" value="off">
                                    <input type="checkbox" id="present-{{ $i }}" name="check[{{ $i }}]" class="form-check-input" value="on" @checked(! $d || $description === 'Attend')>
                                    <label class="visually-hidden" for="present-{{ $i }}">{{ $c->nama }} is present</label>
                                </td>
                                <td>
                                    <label class="visually-hidden" for="reason-{{ $i }}">Reason if {{ $c->nama }} is absent</label>
                                    <select id="reason-{{ $i }}" name="keterangan[{{ $i }}]" class="form-select">
                                        <option value="" @selected(! in_array($description, $reasons, true))>No reason</option>
                                        @foreach ($reasons as $reason)
                                            <option value="{{ $reason }}" @selected($description === $reason)>{{ $reason }}</option>
                                        @endforeach
                                    </select>
                                    @if ($legacy)
                                        <div class="row-note">Saved as “{{ $stored }}”</div>
                                    @endif
                                </td>
                                <td>
                                    <label class="visually-hidden" for="notes-{{ $i }}">Notes for {{ $c->nama }}</label>
                                    <input type="text" id="notes-{{ $i }}" name="notes[{{ $i }}]" class="form-control" maxlength="255" value="{{ $d?->Notes }}">
                                </td>
                            @endif
                        </tr>
                    @endforeach
                    </tbody>
                </table>
                <div class="save-bar">
                    <button type="submit" class="btn btn-primary">Save attendance</button>
                </div>
            @endif
        </div>
    </form>
@endsection
