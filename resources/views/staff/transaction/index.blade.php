@extends('layouts.app')

@section('title', 'Transactions')

@section('content')
    @php
        $search = request('search');
        $status = request('status', 'all');
        $filters = array_filter(['search' => $search, 'status' => $status === 'all' ? null : $status]);
        $statusTabs = ['all' => 'All', 'Unpaid' => 'Unpaid', 'Paid' => 'Paid'];
        $sortUrl = fn (string $column) => staff_route('transaction.sort', array_merge(['column' => $column, 'direction' => $sort], $filters));
    @endphp

    <x-page-header title="Transactions">
        <x-slot:actions>
            <a href="{{ staff_route('transaction.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg" aria-hidden="true"></i> Add transaction</a>
        </x-slot:actions>
    </x-page-header>

    <x-filter-bar :action="staff_route('transaction.index')">
        <label for="transaction-search" class="visually-hidden">Search by student name</label>
        <input id="transaction-search" class="form-control" type="search" name="search" value="{{ $search }}" placeholder="Search student name…">
        <input type="hidden" name="status" value="{{ $status }}">
        <nav class="status-filter" aria-label="Filter by status">
            @foreach ($statusTabs as $value => $label)
                <a href="{{ staff_route('transaction.index', array_filter(['search' => $search, 'status' => $value === 'all' ? null : $value])) }}"
                   class="status-filter-link{{ $status === $value ? ' active' : '' }}" @if ($status === $value) aria-current="page"@endif>{{ $label }}</a>
            @endforeach
        </nav>
    </x-filter-bar>

    <div class="card">
        <div class="card-body">
            @if ($transactions->isEmpty())
                <x-empty-state icon="receipt" title="No transactions found">
                    <x-slot:action>
                        <a href="{{ staff_route('transaction.index') }}" class="btn btn-outline-secondary">Reset filters</a>
                    </x-slot:action>
                </x-empty-state>
            @else
                <table class="table table-hover">
                    <thead>
                    <tr>
                        <th scope="col">Student</th>
                        <th scope="col">Class</th>
                        <th scope="col" class="d-none d-lg-table-cell">Due date</th>
                        <th scope="col"><a href="{{ $sortUrl('price') }}">Total</a></th>
                        <th scope="col" class="d-none d-lg-table-cell">Paid on</th>
                        <th scope="col"><a href="{{ $sortUrl('payment_status') }}">Status</a></th>
                        <th scope="col"><span class="visually-hidden">Actions</span></th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach ($transactions as $transaction)
                        @php
                            $total = \App\Support\Discount::total($transaction->price, $transaction->discount);
                            $discountLabel = \App\Support\Discount::label($transaction->discount);
                            $canEdit = auth()->user()->can('transaction.edit-paid', $transaction);
                            $canDelete = auth()->user()->can('transaction.delete');
                        @endphp
                        <tr>
                            <td>{{ $transaction->LongName }}</td>
                            <td>{{ $transaction->class_name ?? '-' }}</td>
                            <td class="d-none d-lg-table-cell">{{ $transaction->transaction_date ? \Carbon\Carbon::parse($transaction->transaction_date)->format('d M Y') : '-' }}</td>
                            <td>
                                Rp{{ number_format($total ?? $transaction->price) }}
                                @if ($total === null)
                                    <span class="status-badge status-badge-warning">Invalid discount</span>
                                @elseif ($discountLabel !== '')
                                    <div class="row-note">Rp{{ number_format($transaction->price) }} − {{ $discountLabel }}</div>
                                @endif
                            </td>
                            <td class="d-none d-lg-table-cell">{{ $transaction->transaction_payment ? \Carbon\Carbon::parse($transaction->transaction_payment)->format('d M Y') : 'Waiting' }}</td>
                            <td><x-status-badge :status="$transaction->payment_status" /></td>
                            <td class="text-end text-nowrap">
                                <a href="{{ staff_route('transaction.show', $transaction->id) }}" class="btn btn-sm btn-outline-secondary">Detail</a>
                                @if ($canEdit || $canDelete)
                                    <x-row-menu :label="'More actions for the transaction of '.$transaction->LongName">
                                        @if ($canEdit)
                                            <li><a href="{{ staff_route('transaction.edit', $transaction->id) }}" class="dropdown-item">Update</a></li>
                                        @endif
                                        @if ($canDelete)
                                            @if ($canEdit)<li><hr class="dropdown-divider"></li>@endif
                                            <li>
                                                <x-confirm-form :action="staff_route('transaction.destroy', $transaction->id)" :message="'Delete this transaction of '.$transaction->LongName.'? This cannot be undone.'">
                                                    <button type="submit" class="dropdown-item text-danger">Delete…</button>
                                                </x-confirm-form>
                                            </li>
                                        @endif
                                    </x-row-menu>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
                <div class="mt-3">{{ $transactions->links() }}</div>
            @endif
        </div>
    </div>
@endsection
