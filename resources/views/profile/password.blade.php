@extends('layouts.app')

@section('title', 'Change password')

@section('content')
    <x-page-header title="Change password" />

    <form class="card" method="post" action="{{ route('change-password') }}">
        @csrf
        <div class="card-body">
            <x-form.error-summary />
            <x-form.section title="New password">
                <x-form.field name="new_password" label="New password" type="password" required autocomplete="new-password" help="At least 8 characters." />
                <x-form.field name="confirm_password" label="Confirm new password" type="password" required autocomplete="new-password" />
            </x-form.section>
            <div class="save-bar">
                <button type="submit" class="btn btn-primary">Change password</button>
            </div>
        </div>
    </form>
@endsection
