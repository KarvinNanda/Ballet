@extends('layouts.app')

@section('title', 'Update price')

@section('content')
    <x-page-header :title="'Update price · '.$class_label" />

    <form class="card" method="post" action="{{ staff_route('class.freeze.update', $class_id) }}">
        @csrf
        <input type="hidden" name="return_url" value="{{ $return_url }}">
        <div class="card-body">
            <x-form.error-summary />
            <x-form.section title="Price">
                <x-form.field name="inputPrice" label="Price" type="number" min="0" :value="$class->class_transaction_price" required />
            </x-form.section>
            <div class="save-bar">
                <a href="{{ $return_url }}" class="btn btn-outline-secondary">Cancel</a>
                <button type="submit" class="btn btn-primary">Save price</button>
            </div>
        </div>
    </form>
@endsection
