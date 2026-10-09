@extends('layouts.app')

@section('title', 'Stock')

@section('content')
    @php
        $search = request('search');
        $sortUrl = fn (string $column) => staff_route('stock.sort', array_filter(['column' => $column, 'direction' => $sort, 'search' => $search], fn ($v) => $v !== null && $v !== ''));
        $sortedBy = request()->route('column');
        $sortedDirection = request()->route('direction');
    @endphp

    <x-page-header title="Stock">
        <x-slot:actions>
            @can('stock.manage')
                <a href="{{ staff_route('stock.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg" aria-hidden="true"></i> Add stock</a>
            @endcan
        </x-slot:actions>
    </x-page-header>

    <x-filter-bar :action="url()->current()" :reset="staff_route('stock.index')">
        <label for="stock-search" class="visually-hidden">Search by item name</label>
        <input id="stock-search" class="form-control" type="search" name="search" value="{{ $search }}" placeholder="Search item name…">
    </x-filter-bar>

    <div class="card">
        <div class="card-body">
            @if ($stocks->isEmpty())
                <x-empty-state icon="box-seam" title="No stock items found">
                    @if (filled($search))
                        <x-slot:action>
                            <a href="{{ staff_route('stock.index') }}" class="btn btn-outline-secondary">Reset search</a>
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
                        @can('stock.manage')
                            <th scope="col"><span class="visually-hidden">Actions</span></th>
                        @endcan
                    </tr>
                    </thead>
                    <tbody>
                    @foreach ($stocks as $stock)
                        <tr>
                            <td>{{ $stock->name }}</td>
                            <td>{{ $stock->size }}</td>
                            <td>{{ $stock->quantity }}</td>
                            @can('stock.manage')
                                <td class="text-end text-nowrap">
                                    <a href="{{ staff_route('stock.edit', $stock) }}" class="btn btn-sm btn-outline-secondary">Update</a>
                                    <x-row-menu :label="'More actions for '.$stock->name">
                                        <li>
                                            <x-confirm-form :action="staff_route('stock.destroy', $stock)" :message="'Delete '.$stock->name.' ('.$stock->size.')? This cannot be undone.'">
                                                <button type="submit" class="dropdown-item text-danger">Delete…</button>
                                            </x-confirm-form>
                                        </li>
                                    </x-row-menu>
                                </td>
                            @endcan
                        </tr>
                    @endforeach
                    </tbody>
                </table>
                <div class="mt-3">{{ $stocks->links() }}</div>
            @endif
        </div>
    </div>
@endsection
