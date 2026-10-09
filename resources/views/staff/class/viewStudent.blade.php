@extends('layouts.app')

@section('title', 'Add student')

@section('content')
    <x-page-header :title="'Add student to '.$class_label">
        <x-slot:actions>
            <a href="{{ staff_route('class.show', $class_id) }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left" aria-hidden="true"></i> Back to class</a>
        </x-slot:actions>
    </x-page-header>

    <x-filter-bar :action="staff_route('class.student.create', $class_id)">
        <label for="student-keyword" class="visually-hidden">Search students</label>
        <input id="student-keyword" class="form-control" type="search" name="keyword" value="{{ request('keyword') }}" placeholder="Search student name…">
    </x-filter-bar>

    <div class="card">
        <div class="card-body">
            @if ($students->isEmpty())
                <x-empty-state icon="people" title="No student to add" />
            @else
                <table class="table table-hover">
                    <thead><tr><th scope="col">Name</th><th scope="col">Age</th><th scope="col" class="d-none d-md-table-cell">Phone</th><th scope="col"><span class="visually-hidden">Actions</span></th></tr></thead>
                    <tbody>
                    @foreach ($students as $student)
                        <tr>
                            <td>{{ $student->LongName }}</td>
                            <td>{{ $student->Dob ? \Carbon\Carbon::parse($student->Dob)->age : '-' }}</td>
                            <td class="d-none d-md-table-cell">{{ $student->Phone1 }}</td>
                            <td class="text-end">
                                <form action="{{ staff_route('class.student.store') }}" method="post">
                                    @csrf
                                    <input type="hidden" value="{{ $student->id }}" name="studentId">
                                    <input type="hidden" value="{{ $class_id }}" name="classId">
                                    <button type="submit" class="btn btn-sm btn-primary">Add</button>
                                </form>
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
