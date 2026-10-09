@extends('layouts.app')

@section('title', 'Items')

@section('content')
    @php
        $search = request('search');
        $sortUrl = fn (string $column) => route('buyerSorting', array_filter(['value' => $column, 'type' => $sort, 'search' => $search], fn ($v) => $v !== null && $v !== ''));
        $sortedBy = request()->route('value');
        $sortedDirection = request()->route('type');
    @endphp

    <x-page-header title="Items" />

    <x-filter-bar :action="url()->current()" :reset="route('buyer')">
        <label for="item-search" class="visually-hidden">Search by item name</label>
        <input id="item-search" class="form-control" type="search" name="search" value="{{ $search }}" placeholder="Search item name…">
    </x-filter-bar>

    <div class="card">
        <div class="card-body">
            @if ($stocks->isEmpty())
                <x-empty-state icon="box-seam" title="No items found">
                    @if (filled($search))
                        <x-slot:action>
                            <a href="{{ route('buyer') }}" class="btn btn-outline-secondary">Reset search</a>
                        </x-slot:action>
                    @endif
                </x-empty-state>
            @else
                <table class="table table-hover">
                    <thead>
                    <tr>
                        <x-sort-th :href="$sortUrl('name')" :active="$sortedBy === 'name'" :direction="$sortedDirection">Name</x-sort-th>
                        <x-sort-th :href="$sortUrl('size')" :active="$sortedBy === 'size'" :direction="$sortedDirection">Size</x-sort-th>
                        <x-sort-th :href="$sortUrl('quantity')" :active="$sortedBy === 'quantity'" :direction="$sortedDirection">Quantity</x-sort-th>
                        <th scope="col"><span class="visually-hidden">Actions</span></th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach ($stocks as $stock)
                        <tr>
                            <td>{{ $stock->name }}</td>
                            <td>{{ $stock->size }}</td>
                            <td>{{ (int) $stock->quantity }}</td>
                            <td class="text-end text-nowrap">
                                @if ((int) $stock->quantity > 0)
                                    <a href="{{ route('buyingItem', $stock->id) }}" class="btn btn-sm btn-primary">Sell</a>
                                @else
                                    <span class="status-badge status-badge-neutral">Sold out</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
                <div class="mt-3">{{ $stocks->links() }}</div>
            @endif
        </div>
    </div>
@endsection
