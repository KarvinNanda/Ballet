@extends('layouts.app')

@section('title', 'Update teacher · '.$teacher->name)

@section('content')
    <x-page-header title="Update teacher" :subtitle="$teacher->name">
        <x-slot:actions>
            <a href="{{ staff_route('teacher.index') }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left" aria-hidden="true"></i> Back to teachers</a>
        </x-slot:actions>
    </x-page-header>

    <form class="card" method="post" action="{{ staff_route('teacher.update', $teacher) }}">
        @csrf
        <input type="hidden" name="return_url" value="{{ $return_url }}">
        <div class="card-body">
            <x-form.error-summary />
            <x-form.account-fields :account="$teacher">
                {{-- percent is nullable; a blank value would fail "required" on an unchanged save. --}}
                <x-form.field name="inputBonus" label="Reward %" type="number" min="0" max="100" :value="$teacher->percent ?? 0" required
                              help="Share of class fees used in the teacher reward report." />
            </x-form.account-fields>
            <div class="save-bar">
                <button type="submit" class="btn btn-primary">Save changes</button>
            </div>
        </div>
    </form>
@endsection
