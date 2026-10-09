@extends('layouts.app')

@section('title', 'Add teacher')

@section('content')
    <x-page-header title="Add teacher">
        <x-slot:actions>
            <a href="{{ staff_route('teacher.index') }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left" aria-hidden="true"></i> Back to teachers</a>
        </x-slot:actions>
    </x-page-header>

    <form class="card" method="post" action="{{ staff_route('teacher.store') }}">
        @csrf
        <div class="card-body">
            <x-form.error-summary />
            <x-form.account-fields />
            <div class="save-bar">
                <button type="submit" class="btn btn-primary">Create teacher</button>
            </div>
        </div>
    </form>
@endsection
