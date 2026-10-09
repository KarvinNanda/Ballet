@extends('layouts.app')

@section('title', 'Students')

@section('content')
    @php
        $keyword = request('keyword');
        $status = request('status', 'all');
        $filters = array_filter(['keyword' => $keyword, 'status' => $status === 'all' ? null : $status]);
        $statusTabs = ['all' => 'All', 'aktif' => 'Active', 'non-aktif' => 'Inactive', 'trial' => 'Trial'];
        // Stored status => the value ToggleStudentStatusRequest expects.
        $statusActions = ['aktif' => 'Active', 'non-aktif' => 'Inactive', 'trial' => 'Trial'];
        $sortUrl = fn (string $column) => staff_route('student.sort', array_merge(['column' => $column, 'direction' => $sort], $filters));
    @endphp

    <x-page-header title="Students">
        <x-slot:actions>
            <a href="{{ staff_route('student.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg" aria-hidden="true"></i> Add student</a>
        </x-slot:actions>
    </x-page-header>

    <x-filter-bar :action="staff_route('student.index')">
        <label for="student-keyword" class="visually-hidden">Search students</label>
        <input id="student-keyword" class="form-control" type="search" name="keyword" value="{{ $keyword }}" placeholder="Search name, phone, parent, account…">
        <input type="hidden" name="status" value="{{ $status }}">
        <nav class="status-filter" aria-label="Filter by status">
            @foreach ($statusTabs as $value => $label)
                <a href="{{ staff_route('student.index', array_filter(['keyword' => $keyword, 'status' => $value === 'all' ? null : $value])) }}"
                   class="status-filter-link{{ $status === $value ? ' active' : '' }}" @if ($status === $value) aria-current="page"@endif>{{ $label }}</a>
            @endforeach
        </nav>
    </x-filter-bar>

    <div class="card">
        <div class="card-body">
            @if ($students->isEmpty())
                <x-empty-state icon="people" title="No students found">
                    <x-slot:action>
                        <a href="{{ staff_route('student.index') }}" class="btn btn-outline-secondary">Reset filters</a>
                    </x-slot:action>
                </x-empty-state>
            @else
                <table class="table table-hover">
                    <thead>
                    <tr>
                        <th scope="col"><a href="{{ $sortUrl('name') }}">Name</a></th>
                        <th scope="col"><a href="{{ $sortUrl('dob') }}">Birthday</a></th>
                        <th scope="col">Parent</th>
                        <th scope="col" class="d-none d-lg-table-cell">Phone</th>
                        <th scope="col">Status</th>
                        <th scope="col"><span class="visually-hidden">Actions</span></th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach ($students as $student)
                        <tr>
                            <td>
                                {{ $student->name }}
                                @if ($student->nis)
                                    <div class="row-note">NIS {{ $student->nis }}</div>
                                @endif
                            </td>
                            <td>
                                @if ($student->dob)
                                    {{ \Carbon\Carbon::parse($student->dob)->format('d M') }} ({{ $student->age }})
                                @else
                                    -
                                @endif
                            </td>
                            <td>{{ $student->ortu }}</td>
                            <td class="d-none d-lg-table-cell">{{ $student->phone }}</td>
                            <td><x-status-badge :status="$student->status" /></td>
                            <td class="text-end text-nowrap">
                                <a href="{{ staff_route('student.show', $student->id) }}" class="btn btn-sm btn-outline-secondary">Detail</a>
                                <x-row-menu :label="'More actions for '.$student->name">
                                    @foreach ($statusActions as $stored => $label)
                                        @continue($student->status === $stored)
                                        <li>
                                            <x-confirm-form :action="staff_route('student.status', $student->id)" :message="'Set '.$student->name.' to '.$label.'?'">
                                                <button type="submit" name="stats" value="{{ $label }}" class="dropdown-item">Set {{ $label }}</button>
                                            </x-confirm-form>
                                        </li>
                                    @endforeach
                                    <li><hr class="dropdown-divider"></li>
                                    <li>
                                        <x-confirm-form :action="staff_route('student.destroy', $student->id)" :message="'Delete '.$student->name.'? This cannot be undone.'">
                                            <button type="submit" class="dropdown-item text-danger">Delete…</button>
                                        </x-confirm-form>
                                    </li>
                                </x-row-menu>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
                <div class="mt-3">{{ $students->links() }}</div>
            @endif
        </div>
    </div>
@endsection
