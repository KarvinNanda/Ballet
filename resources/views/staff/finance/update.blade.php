@extends('layouts.app')

@section('title', 'Update finance account · '.$user->name)

@section('content')
    <x-page-header title="Update finance account" :subtitle="$user->name">
        <x-slot:actions>
            <a href="{{ staff_route('finance.index') }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left" aria-hidden="true"></i> Back to finance accounts</a>
        </x-slot:actions>
    </x-page-header>

    <form class="card" method="post" action="{{ staff_route('finance.update', $user) }}">
        @csrf
        <input type="hidden" name="return_url" value="{{ $return_url }}">
        <div class="card-body">
            <x-form.error-summary />
            <x-form.account-fields :account="$user">
                <x-form.field name="inputBonus" label="Bonus %" type="number" min="0" max="100" :value="$user->percent ?? 0" required
                              help="Stored on the account; not used by any report." />
            </x-form.account-fields>
            <div class="save-bar">
                <button type="submit" class="btn btn-primary">Save changes</button>
            </div>
        </div>
    </form>
@endsection
