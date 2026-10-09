@extends('layouts.app')

@section('title', 'Add stock item')

@section('content')
    <x-page-header title="Add stock item">
        <x-slot:actions>
            <a href="{{ staff_route('stock.index') }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left" aria-hidden="true"></i> Back to stock</a>
        </x-slot:actions>
    </x-page-header>

    <form class="card" method="post" action="{{ staff_route('stock.store') }}">
        @csrf
        <div class="card-body">
            <x-form.error-summary />
            <x-form.section title="Item">
                <x-form.field name="inputName" label="Name" required />
                <x-form.field name="inputSize" label="Size" required />
                <x-form.field name="inputQty" label="Quantity" type="number" min="0" required />
            </x-form.section>
            <div class="save-bar">
                <button type="submit" class="btn btn-primary">Create stock item</button>
            </div>
        </div>
    </form>
@endsection
