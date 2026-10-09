@extends('layouts.app')

@section('title', 'Sell · '.$stock->name)

@section('content')
    {{-- old() is the failed save's own input, so it is checked like any return_url before it reaches an href. --}}
    @php
        $back = own_url(old('return_url', $return_url), url('/'));
    @endphp
    <x-page-header :title="'Sell · '.$stock->name">
        <x-slot:actions>
            <a href="{{ route('buyer') }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left" aria-hidden="true"></i> Back to items</a>
        </x-slot:actions>
    </x-page-header>

    <div class="card mb-4">
        <div class="card-body">
            <dl class="detail-grid">
                <div><dt>Name</dt><dd>{{ $stock->name }}</dd></div>
                <div><dt>Size</dt><dd>{{ $stock->size ?: '-' }}</dd></div>
                <div><dt>In stock</dt><dd>{{ (int) $stock->quantity }}</dd></div>
            </dl>
        </div>
    </div>

    @if ((int) $stock->quantity < 1)
        {{-- A refused sale's qty error would otherwise vanish: the form that shows it is not rendered when sold out. --}}
        @if ($errors->has('qty'))
            <div class="alert alert-danger" role="alert">{{ $errors->first('qty') }}</div>
        @endif
        <div class="card">
            <div class="card-body">
                <x-empty-state icon="bag-x" title="Sold out" />
            </div>
        </div>
    @else
        {{-- max is the stock when the page loaded; the server re-checks it and answers on the qty field. --}}
        <form class="card" method="post" action="{{ route('buying', $stock->id) }}">
            @csrf
            <input type="hidden" name="return_url" value="{{ $back }}">
            <div class="card-body">
                <x-form.error-summary />
                <x-form.section title="Sale">
                    <x-form.field name="name" label="Buyer name" required />
                    <x-form.field name="qty" label="Quantity" type="number" min="1" :max="(int) $stock->quantity" required />
                </x-form.section>
                <div class="save-bar">
                    <button type="submit" class="btn btn-primary">Record sale</button>
                </div>
            </div>
        </form>
    @endif
@endsection
