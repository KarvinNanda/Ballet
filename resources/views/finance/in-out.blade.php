@extends('layouts.app')

@section('title', 'Record stock in · '.$stock->name)

@section('content')
    {{-- old() is the failed save's own input, so it is checked like any return_url before it reaches an href. --}}
    @php
        $back = own_url(old('return_url', $return_url), url('/'));
    @endphp
    <x-page-header :title="'Record stock in · '.$stock->name">
        <x-slot:actions>
            <a href="{{ route('finance') }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left" aria-hidden="true"></i> Back to stock</a>
        </x-slot:actions>
    </x-page-header>

    <div class="card mb-4">
        <div class="card-body">
            <dl class="detail-grid">
                <div><dt>Name</dt><dd>{{ $stock->name }}</dd></div>
                <div><dt>Size</dt><dd>{{ $stock->size ?: '-' }}</dd></div>
                <div><dt>Quantity</dt><dd>{{ (int) $stock->quantity }}</dd></div>
            </dl>
        </div>
    </div>

    <form class="card" method="post" action="{{ route('makeReport', [$stock, $type]) }}">
        @csrf
        <input type="hidden" name="return_url" value="{{ $back }}">
        <div class="card-body">
            <x-form.error-summary />
            <x-form.section title="Stock in">
                <x-form.field name="in_out" label="Quantity in" type="number" min="1" required />
            </x-form.section>
            <div class="save-bar">
                <button type="submit" class="btn btn-primary">Record stock in</button>
            </div>
        </div>
    </form>

    <section class="card mt-4" aria-labelledby="purchase-history-title">
        <div class="card-body">
            <h2 id="purchase-history-title" class="h5">Purchase history</h2>
            @if ($buyer->isEmpty())
                <x-empty-state icon="bag" title="No purchases yet" />
            @else
                <table class="table table-hover">
                    <thead>
                    <tr>
                        <th scope="col">Buyer</th>
                        <th scope="col">Qty</th>
                        <th scope="col">Date</th>
                        <th scope="col">Served by</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach ($buyer as $purchase)
                        <tr>
                            <td>{{ $purchase->name }}</td>
                            <td>{{ $purchase->qty }}</td>
                            <td>{{ $purchase->created_at ? \Carbon\Carbon::parse($purchase->created_at)->format('d M Y') : '-' }}</td>
                            <td>{{ $purchase->served_by ?? '-' }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
                <div class="mt-3">{{ $buyer->links() }}</div>
            @endif
        </div>
    </section>
@endsection
