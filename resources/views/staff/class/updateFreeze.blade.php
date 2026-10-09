@extends('layouts.app')

@section('title', 'Update price')

@section('content')
    {{-- old() is the failed save's own input, so it is checked like any return_url before it reaches an href. --}}
    @php
        $back = own_url(old('return_url', $return_url), url('/'));
    @endphp
    <x-page-header :title="'Update price · '.$class_label" />

    <form class="card" method="post" action="{{ staff_route('class.freeze.update', $class_id) }}">
        @csrf
        <input type="hidden" name="return_url" value="{{ $back }}">
        <div class="card-body">
            <x-form.error-summary />
            <x-form.section title="Price">
                <x-form.field name="inputPrice" label="Price" type="number" min="0" :value="$class->class_transaction_price" required />
            </x-form.section>
            <div class="save-bar">
                <a href="{{ $back }}" class="btn btn-outline-secondary">Cancel</a>
                <button type="submit" class="btn btn-primary">Save price</button>
            </div>
        </div>
    </form>
@endsection
