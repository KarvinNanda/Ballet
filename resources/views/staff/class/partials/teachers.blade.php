{{-- Teachers of a class. $actions: Remove (not on frozen classes). --}}
@if ($teachers->isEmpty())
    <x-empty-state icon="person-badge" title="No teacher in this class yet" />
@else
    <table class="table table-hover">
        <thead>
        <tr>
            <th scope="col">Name</th>
            <th scope="col" class="d-none d-md-table-cell">Email</th>
            <th scope="col">Phone</th>
            @if ($actions)
                <th scope="col"><span class="visually-hidden">Actions</span></th>
            @endif
        </tr>
        </thead>
        <tbody>
        @foreach ($teachers as $teacher)
            <tr>
                <td>{{ $teacher->teacherName }}</td>
                <td class="d-none d-md-table-cell">{{ $teacher->teacherEmail }}</td>
                <td>{{ $teacher->teacherPhone }}</td>
                @if ($actions)
                    <td class="text-end">
                        <x-confirm-form :action="staff_route('class.teacher.destroy', ['teacher' => $teacher->id, 'class' => $class_id])" :message="'Remove '.$teacher->teacherName.' from this class?'">
                            <button type="submit" class="btn btn-sm btn-outline-danger">Remove…</button>
                        </x-confirm-form>
                    </td>
                @endif
            </tr>
        @endforeach
        </tbody>
    </table>
    <div class="mt-3">{{ $teachers->links() }}</div>
@endif
