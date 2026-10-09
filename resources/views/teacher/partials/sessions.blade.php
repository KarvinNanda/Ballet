{{-- Home page sessions. Rows: id, date, class_name, students, status (an AttendanceWindow constant). --}}
@php
    $badges = [
        \App\Support\AttendanceWindow::RECORDED => ['Recorded', 'success'],
        \App\Support\AttendanceWindow::OPEN => ['Open', 'warning'],
        \App\Support\AttendanceWindow::NOT_STARTED => ['Not started', 'neutral'],
        \App\Support\AttendanceWindow::MISSED => ['Missed', 'warning'],
    ];
@endphp
<table class="table table-hover">
    <thead>
    <tr>
        <th scope="col">Class</th>
        <th scope="col">Time</th>
        <th scope="col">Students</th>
        <th scope="col">Status</th>
        <th scope="col"><span class="visually-hidden">Actions</span></th>
    </tr>
    </thead>
    <tbody>
    @foreach ($sessions as $s)
        @php
            [$label, $tone] = $badges[$s->status];
        @endphp
        <tr>
            <td>{{ $s->class_name ?? 'Class' }}</td>
            <td>{{ \Carbon\Carbon::parse($s->date)->format('H:i') }}</td>
            <td>{{ $s->students }}</td>
            <td><span class="status-badge status-badge-{{ $tone }}">{{ $label }}</span></td>
            <td class="text-end">
                @if ($s->status === \App\Support\AttendanceWindow::OPEN)
                    <form method="POST" action="{{ route('viewAbsen', $s->id) }}">
                        @csrf
                        <button type="submit" class="btn btn-primary">Take attendance</button>
                    </form>
                @elseif ($s->status === \App\Support\AttendanceWindow::RECORDED)
                    <form method="POST" action="{{ route('viewAbsen', $s->id) }}">
                        @csrf
                        <button type="submit" class="btn btn-outline-secondary">View attendance</button>
                    </form>
                @endif
            </td>
        </tr>
    @endforeach
    </tbody>
</table>
