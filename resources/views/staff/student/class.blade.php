@extends('layouts.app')

@section('title', 'Add class')

@section('content')
    @php
        // Only classes that have at least one schedule can be joined.
        $available = collect($data)->filter(fn ($class) => in_array($class->id, $schedules));
    @endphp

    <x-page-header :title="'Add class for '.$student->LongName">
        <x-slot:actions>
            <a href="{{ staff_route('student.show', ['student' => $student->id, 'tab' => 'classes']) }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left" aria-hidden="true"></i> Back to student
            </a>
        </x-slot:actions>
    </x-page-header>

    <div class="card">
        <div class="card-body">
            @if ($available->isEmpty())
                <x-empty-state icon="calendar-x" title="No class with a schedule is available" />
            @else
                <table class="table table-hover">
                    <thead>
                    <tr>
                        <th scope="col">Class</th>
                        <th scope="col">Teacher</th>
                        <th scope="col">Students</th>
                        <th scope="col"><span class="visually-hidden">Actions</span></th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach ($available as $class)
                        <tr>
                            <td>{{ $class->class_name }}</td>
                            <td>{{ $class->user }}</td>
                            <td>{{ $class->students }}</td>
                            <td class="text-end">
                                <form action="{{ staff_route('student.class.store', ['class' => $class->id, 'student' => $student->id]) }}" method="post">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-primary">Add</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    </div>
@endsection
