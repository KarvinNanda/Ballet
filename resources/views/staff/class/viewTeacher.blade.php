@extends('layouts.app')

@section('title', 'Add teacher')

@section('content')
    <x-page-header :title="'Add teacher to '.$class_label">
        <x-slot:actions>
            <a href="{{ staff_route('class.show', ['class' => $class_id, 'tab' => 'teachers']) }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left" aria-hidden="true"></i> Back to class</a>
        </x-slot:actions>
    </x-page-header>

    <div class="card">
        <div class="card-body">
            @if ($teachers->isEmpty())
                <x-empty-state icon="person-badge" title="Every teacher is already in this class" />
            @else
                <table class="table table-hover">
                    <thead><tr><th scope="col">Name</th><th scope="col" class="d-none d-md-table-cell">Email</th><th scope="col">Phone</th><th scope="col"><span class="visually-hidden">Actions</span></th></tr></thead>
                    <tbody>
                    @foreach ($teachers as $teacher)
                        <tr>
                            <td>{{ $teacher->name }}</td>
                            <td class="d-none d-md-table-cell">{{ $teacher->email }}</td>
                            <td>{{ $teacher->phone }}</td>
                            <td class="text-end">
                                <form action="{{ staff_route('class.teacher.store') }}" method="post">
                                    @csrf
                                    <input type="hidden" value="{{ $teacher->id }}" name="teacherId">
                                    <input type="hidden" value="{{ $class_id }}" name="classId">
                                    <button type="submit" class="btn btn-sm btn-primary">Add</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
                <div class="mt-3">{{ $teachers->links() }}</div>
            @endif
        </div>
    </div>
@endsection
