@extends('layouts.app')

@section('title', 'Add finance account')

@section('content')
    <x-page-header title="Add finance account">
        <x-slot:actions>
            <a href="{{ staff_route('finance.index') }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left" aria-hidden="true"></i> Back to finance accounts</a>
        </x-slot:actions>
    </x-page-header>

    <form class="card" method="post" action="{{ staff_route('finance.store') }}">
        @csrf
        <div class="card-body">
            <x-form.error-summary />
            <x-form.account-fields />
            <div class="save-bar">
                <button type="submit" class="btn btn-primary">Create finance account</button>
            </div>
        </div>
    </form>
@endsection
