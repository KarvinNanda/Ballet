@extends('layouts.app')

@section('title', 'Freeze class')

@section('content')
    {{-- old() is the failed save's own input, so it is checked like any return_url before it reaches an href. --}}
    @php
        $back = own_url(old('return_url', $return_url), url('/'));
    @endphp
    <x-page-header :title="'Freeze class '.$class_name">
        <x-slot:actions>
            <a href="{{ $back }}" class="btn btn-outline-secondary">Cancel</a>
        </x-slot:actions>
    </x-page-header>

    <div class="alert alert-warning" role="alert">
        Freezing moves this class out of the active list and locks its price: later course price changes will not apply to it.
        This cannot be undone from the app.
    </div>

    <div class="card">
        <div class="card-body">
            <h2 class="form-section-title">Students in this class</h2>
            @if ($students->isEmpty())
                <x-empty-state icon="people" title="No students in this class" />
            @else
                <table class="table">
                    <thead><tr><th scope="col">Name</th><th scope="col">Age</th><th scope="col">Email</th></tr></thead>
                    <tbody>
                    @foreach ($students as $student)
                        <tr>
                            <td>{{ $student->studentName }}</td>
                            <td>{{ $student->studentDOB ? \Carbon\Carbon::parse($student->studentDOB)->age : '-' }}</td>
                            <td>{{ $student->studentEmail }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            @endif

            <x-confirm-form :action="staff_route('class.level.store')" :message="'Freeze '.$class_name.'? This cannot be undone.'" class="save-bar">
                <input type="hidden" name="classId" value="{{ $class_id }}">
                <input type="hidden" name="return_url" value="{{ $back }}">
                <button type="submit" class="btn btn-danger">Freeze class</button>
            </x-confirm-form>
        </div>
    </div>
@endsection
