@extends('layouts.app')

@section('title', 'Frozen classes')

@section('content')
    <x-page-header title="Frozen classes" />

    <x-filter-bar :action="staff_route('class.freeze.index')">
        <label for="freeze-keyword" class="visually-hidden">Search frozen classes</label>
        <input id="freeze-keyword" class="form-control" type="search" name="keyword" value="{{ request('keyword') }}" placeholder="Search course, teacher or student…">
    </x-filter-bar>

    <div class="card">
        <div class="card-body">
            @if ($classes->isEmpty())
                <x-empty-state icon="snow" title="No frozen classes found" />
            @else
                <table class="table table-hover">
                    <thead><tr><th scope="col">Class</th><th scope="col">Teacher</th><th scope="col">Price</th><th scope="col"><span class="visually-hidden">Actions</span></th></tr></thead>
                    <tbody>
                    @foreach ($classes as $class)
                        <tr>
                            <td>{{ $class->Type?->class_name ?? '-' }}</td>
                            <td>{{ $class->mapping->first()?->getUser?->name ?? '-' }}</td>
                            <td>Rp{{ number_format($class->class_transaction_price) }}</td>
                            <td class="text-end text-nowrap">
                                <a href="{{ staff_route('class.freeze.show', $class) }}" class="btn btn-sm btn-outline-secondary">Detail</a>
                                @can('class.freeze-price')
                                    <a href="{{ staff_route('class.freeze.edit', $class) }}" class="btn btn-sm btn-outline-secondary">Update price</a>
                                @endcan
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
                <div class="mt-3">{{ $classes->links() }}</div>
            @endif
        </div>
    </div>
@endsection
