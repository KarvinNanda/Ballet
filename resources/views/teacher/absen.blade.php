@extends('layouts.app')

@section('title', 'Attendance · '.$class_label)

@section('content')
    {{-- old() is the failed save's own input, so it is checked like any return_url before it reaches an href. --}}
    @php
        $back = own_url(old('return_url', $return_url), route('teacher'));
    @endphp
    {{-- A block @php, not @php(...): Blade pairs an inline @php( with the next @endphp and breaks the file. --}}
    @php
        $when = \Carbon\Carbon::parse($schedule->date);
    @endphp

    <x-page-header :title="'Attendance · '.$class_label.' · '.$when->format('D d M Y, H:i')">
        <x-slot:actions>
            <a href="{{ $back }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left" aria-hidden="true"></i> Back</a>
        </x-slot:actions>
    </x-page-header>

    @if ($recorded)
        <div class="alert alert-info" role="status">Recorded. Ask the head to correct it.</div>
        <div class="card">
            <div class="card-body">
                @if ($records->isEmpty())
                    <x-empty-state icon="people" title="No students were recorded" />
                @else
                    <table class="table table-hover">
                        <thead>
                        <tr>
                            <th scope="col">Student</th>
                            <th scope="col">Status</th>
                            <th scope="col">Notes</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach ($records as $r)
                            @php
                                // Stored values: Attend / Absent / Permission / Sick (the app) and Sakit / Izin (older rows, the seed).
                                $status = [
                                    'Attend' => ['Present', 'success'],
                                    'Absent' => ['Absent', 'warning'],
                                    'Permission' => ['Permission', 'neutral'],
                                    'Izin' => ['Permission', 'neutral'],
                                    'Sick' => ['Sick', 'neutral'],
                                    'Sakit' => ['Sick', 'neutral'],
                                ][$r->Description ?? ''] ?? null;
                            @endphp
                            <tr>
                                <td>
                                    {{ $r->nama }}
                                    @if (filled($r->nis))
                                        <div class="row-note">NIS {{ $r->nis }}</div>
                                    @endif
                                </td>
                                <td>
                                    @if ($status)
                                        <span class="status-badge status-badge-{{ $status[1] }}">{{ $status[0] }}</span>
                                    @else
                                        {{ filled($r->Description) ? $r->Description : '–' }}
                                    @endif
                                </td>
                                <td>{{ $r->Notes }}</td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                @endif
            </div>
        </div>
    @else
        <x-confirm-form :action="route('getAbsen', $schedule->id)" message="Attendance cannot be changed after saving. Save now?" class="card">
            <input type="hidden" name="return_url" value="{{ $back }}">
            <div class="card-body">
                <x-form.error-summary />
                @if ($students->isEmpty())
                    <x-empty-state icon="people" title="No active students in this class" />
                @else
                    @include('partials.attendance-form-table', ['students' => $students, 'details' => $details, 'mustPay' => $mustPay])
                    <div class="save-bar">
                        <button type="submit" class="btn btn-primary">Save attendance</button>
                    </div>
                @endif
            </div>
        </x-confirm-form>
    @endif
@endsection
