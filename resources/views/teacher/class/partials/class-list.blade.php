{{-- The teacher's classes, shared by My classes (viewClass) and Schedules (viewAllScheduleTeacher). Rows: id, class_name, students. --}}
<div class="card">
    <div class="card-body">
        @if ($classes->isEmpty())
            <x-empty-state icon="easel" title="No classes yet" />
        @else
            <table class="table table-hover">
                <thead>
                <tr>
                    <th scope="col">Class</th>
                    <th scope="col">Students</th>
                    <th scope="col"><span class="visually-hidden">Actions</span></th>
                </tr>
                </thead>
                <tbody>
                @foreach ($classes as $class)
                    <tr>
                        <td>{{ $class->class_name ?? 'Class' }}</td>
                        <td>{{ $class->students }}</td>
                        <td class="text-end text-nowrap">
                            <form method="POST" action="{{ route('viewDetailTeacher', $class->id) }}" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-outline-secondary">Students</button>
                            </form>
                            <a href="{{ route('viewScheduleClassTeacher', $class->id) }}" class="btn btn-sm btn-primary">Schedule</a>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        @endif
    </div>
</div>
