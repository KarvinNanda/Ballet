@extends('layouts.app')

@section('title', 'My profile')

@section('content')
    <x-page-header title="My profile" />

    <form class="card" method="post" action="{{ route('change-profile') }}">
        @csrf
        <div class="card-body">
            <x-form.error-summary />
            <x-form.section title="Account">
                {{-- Name and date of birth are not editable here: shown as text, not posted. --}}
                <div class="form-field">
                    <span class="form-label d-block">Name</span>
                    <p class="form-static">{{ $user->name ?: '-' }}</p>
                </div>
                <div class="form-field">
                    <span class="form-label d-block">Date of birth</span>
                    <p class="form-static">{{ $user->dob ? \Carbon\Carbon::parse($user->dob)->format('d M Y') : '-' }}</p>
                </div>
                <x-form.field name="address" label="Address" type="textarea" :value="$user->address" required wide autocomplete="street-address" />
                <x-form.field name="phone" label="Phone" type="tel" :value="$user->phone" required autocomplete="tel" help="10–12 digits" />
                <x-form.field name="email" label="Email" type="email" :value="$user->email" required autocomplete="email" />
            </x-form.section>
            <div class="save-bar">
                <button type="submit" class="btn btn-primary">Save changes</button>
            </div>
        </div>
    </form>
@endsection
