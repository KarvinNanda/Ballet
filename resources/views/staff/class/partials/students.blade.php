{{-- Students of a class. $actions: row menu (not on frozen classes). --}}
@php
    $quotaPay = match ($class_name) {
        'Pointe Class' => 4,
        'Intensive Kids', 'Intensive Class' => 12,
        default => 3,
    };
@endphp
@if ($students->isEmpty())
    <x-empty-state icon="people" title="No students in this class yet" />
@else
    <table class="table table-hover">
        <thead>
        <tr>
            <th scope="col">Name</th>
            <th scope="col">Age</th>
            <th scope="col" class="d-none d-md-table-cell">Phone</th>
            <th scope="col">Quota</th>
            @if ($actions)
                <th scope="col"><span class="visually-hidden">Actions</span></th>
            @endif
        </tr>
        </thead>
        <tbody>
        @foreach ($students as $student)
            <tr>
                <td><a href="{{ staff_route('student.show', $student->id) }}">{{ $student->studentName }}</a></td>
                <td>{{ $student->studentDOB ? \Carbon\Carbon::parse($student->studentDOB)->age : '-' }}</td>
                <td class="d-none d-md-table-cell">{{ $student->studentPhone }}</td>
                <td>{{ $student->studentQuota }} / {{ $student->studentStatus === 'trial' ? 2 : ($student->studentMaxQuota == 0 ? $quotaPay : $student->studentMaxQuota) }}</td>
                @if ($actions)
                    <td class="text-end">
                        <x-row-menu :label="'More actions for '.$student->studentName">
                            <li>
                                <x-confirm-form :action="staff_route('class.student.generate-transaction', ['student' => $student->id, 'class' => $class_id])" :message="'Generate the transactions of '.$student->studentName.' for this class?'">
                                    <button type="submit" class="dropdown-item">Generate transaction…</button>
                                </x-confirm-form>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <x-confirm-form :action="staff_route('class.student.destroy', ['student' => $student->id, 'class' => $class_id])" :message="'Remove '.$student->studentName.' from this class?'">
                                    <button type="submit" class="dropdown-item text-danger">Remove from class…</button>
                                </x-confirm-form>
                            </li>
                        </x-row-menu>
                    </td>
                @endif
            </tr>
        @endforeach
        </tbody>
    </table>
    <div class="mt-3">{{ $students->links() }}</div>
@endif
