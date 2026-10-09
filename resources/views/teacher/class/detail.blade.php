@extends('layouts.app')

@section('title', 'Students · '.$course)

@section('content')
    @php
        // Same rule as the staff class detail: trial → 2; no class quota set → the course default.
        $quotaPay = match ($course) {
            'Pointe Class' => 4,
            'Intensive Kids', 'Intensive Class' => 12,
            default => 3,
        };
    @endphp

    <x-page-header :title="'Students · '.$course">
        <x-slot:actions>
            <a href="{{ route('viewClass') }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left" aria-hidden="true"></i> Back to my classes</a>
        </x-slot:actions>
    </x-page-header>

    <div class="card">
        <div class="card-body">
            @if ($students->isEmpty())
                <x-empty-state icon="people" title="No students in this class yet" />
            @else
                <table class="table table-hover">
                    <thead>
                    <tr>
                        <th scope="col">Name</th>
                        <th scope="col">Age</th>
                        <th scope="col">Quota</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach ($students as $student)
                        <tr>
                            <td>{{ $student->name }}</td>
                            <td><x-age :dob="$student->dob" /></td>
                            <td>{{ (int) $student->quota }} / {{ $student->status === 'trial' ? 2 : ((int) $student->max_quota === 0 ? $quotaPay : $student->max_quota) }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    </div>
@endsection
