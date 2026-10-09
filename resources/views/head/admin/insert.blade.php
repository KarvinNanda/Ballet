@extends('layouts.app')

@section('title', 'Add admin')

@section('content')
    <x-page-header title="Add admin">
        <x-slot:actions>
            <a href="{{ route('headAdminPage') }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left" aria-hidden="true"></i> Back to admin accounts</a>
        </x-slot:actions>
    </x-page-header>

    <form class="card" method="post" action="{{ route('AdminAdd') }}">
        @csrf
        <div class="card-body">
            <x-form.error-summary />
            <x-form.account-fields />
            <div class="save-bar">
                <button type="submit" class="btn btn-primary">Create admin</button>
            </div>
        </div>
    </form>
@endsection
