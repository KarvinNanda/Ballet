@extends('layouts.app')

@section('title', 'Replace '.$teacher->name)

@section('content')
    @php
        $search = request('search');
        $n = $classCount;
        $classes = $n.' '.\Illuminate\Support\Str::plural('class', $n);
        $f = $frozenClassCount;
        $confirm = fn ($candidate) => $n > 0
            ? "Move {$classes} from {$teacher->name} to {$candidate->name} and delete {$teacher->name}'s account?"
            : "Delete {$teacher->name}'s account? It has no classes to move.";
    @endphp

    <x-page-header :title="'Replace '.$teacher->name">
        <x-slot:actions>
            <a href="{{ staff_route('teacher.index') }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left" aria-hidden="true"></i> Back to teachers</a>
        </x-slot:actions>
    </x-page-header>

    <div class="alert alert-warning" role="alert">
        @if ($n > 0)
            The {{ $classes }} of {{ $teacher->name }}{{ $f > 0 ? " (including {$f} frozen)" : '' }} {{ $n === 1 ? 'moves' : 'move' }} to the teacher you choose, then {{ $teacher->name }}'s account is deleted. This cannot be undone from the app.
        @else
            {{ $teacher->name }} has no classes. Choosing a teacher below only deletes {{ $teacher->name }}'s account. This cannot be undone from the app.
        @endif
    </div>

    <x-filter-bar :action="staff_route('teacher.switch', $teacher)">
        <label for="replacement-search" class="visually-hidden">Search by teacher name</label>
        <input id="replacement-search" class="form-control" type="search" name="search" value="{{ $search }}" placeholder="Search teacher name…">
    </x-filter-bar>

    <div class="card">
        <div class="card-body">
            @if ($teachers->isEmpty())
                <x-empty-state icon="person-badge" title="No other teachers found">
                    <x-slot:action>
                        <a href="{{ staff_route('teacher.switch', $teacher) }}" class="btn btn-outline-secondary">Reset search</a>
                    </x-slot:action>
                </x-empty-state>
            @else
                <table class="table table-hover">
                    <thead>
                    <tr>
                        <th scope="col">Name</th>
                        <th scope="col">Reward %</th>
                        <th scope="col">Phone</th>
                        <th scope="col">Email</th>
                        <th scope="col"><span class="visually-hidden">Actions</span></th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach ($teachers as $candidate)
                        <tr>
                            <td>{{ $candidate->name }}</td>
                            <td>{{ $candidate->percent ?? 0 }}%</td>
                            <td>{{ $candidate->phone ?? '-' }}</td>
                            <td>{{ $candidate->email }}</td>
                            <td class="text-end text-nowrap">
                                <x-confirm-form :action="staff_route('teacher.replace', ['teacher' => $teacher, 'replacement' => $candidate->id])" :message="$confirm($candidate)">
                                    <button type="submit" class="btn btn-sm btn-outline-danger">Replace with this teacher…</button>
                                </x-confirm-form>
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
