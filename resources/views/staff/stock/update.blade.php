@extends('layouts.app')

@section('title', 'Update stock item · '.$stock->name)

@section('content')
    <x-page-header title="Update stock item" :subtitle="$stock->name.' · '.$stock->size">
        <x-slot:actions>
            <a href="{{ staff_route('stock.index') }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left" aria-hidden="true"></i> Back to stock</a>
        </x-slot:actions>
    </x-page-header>

    <form class="card" method="post" action="{{ staff_route('stock.update', $stock) }}">
        @csrf
        <input type="hidden" name="return_url" value="{{ $return_url }}">
        <div class="card-body">
            <x-form.error-summary />
            <x-form.section title="Item">
                <x-form.field name="inputName" label="Name" :value="$stock->name" required />
                <x-form.field name="inputSize" label="Size" :value="$stock->size" required />
                <x-form.field name="inputQty" label="Quantity" type="number" min="0" :value="$stock->quantity" required />
            </x-form.section>
            <div class="save-bar">
                <button type="submit" class="btn btn-primary">Save changes</button>
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
