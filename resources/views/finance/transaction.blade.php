@extends('layouts.app')

@section('title', 'Transactions')

@section('content')
    @php
        $search = request('search');
        // Unpaid is the default status, so its link (and the sort links while on it) leave status out.
        $statusParam = $status === 'Unpaid' ? null : $status;
        $statusTabs = ['Unpaid' => 'Unpaid', 'Paid' => 'Paid', 'all' => 'All'];
        $sortUrl = fn (string $column) => route('financeTransactionSorting', array_filter(['column' => $column, 'search' => $search, 'status' => $statusParam], fn ($v) => $v !== null && $v !== ''));
        // On the sort page the filter bar and the status links stay on that page, so the sort survives.
        $here = fn (array $query) => url()->current().($query ? '?'.\Illuminate\Support\Arr::query($query) : '');
        $sortedBy = request()->route('column');
        $isFiltered = filled($search) || $status !== 'Unpaid';
    @endphp

    <x-page-header title="Transactions" />

    <x-filter-bar :action="url()->current()" :reset="route('financeTransaction')">
        <label for="transaction-search" class="visually-hidden">Search by student name</label>
        <input id="transaction-search" class="form-control" type="search" name="search" value="{{ $search }}" placeholder="Search student name…">
        <input type="hidden" name="status" value="{{ $status }}">
        <nav class="status-filter" aria-label="Filter by status">
            @foreach ($statusTabs as $value => $label)
                <a href="{{ $here(array_filter(['search' => $search, 'status' => $value === 'Unpaid' ? null : $value], fn ($v) => $v !== null && $v !== '')) }}"
                   class="status-filter-link{{ $status === $value ? ' active' : '' }}" @if ($status === $value) aria-current="page"@endif>{{ $label }}</a>
            @endforeach
        </nav>
    </x-filter-bar>

    <div class="card">
        <div class="card-body">
            @if ($transactions->isEmpty())
                <x-empty-state icon="receipt" title="No transactions found">
                    @if ($isFiltered)
                        <x-slot:action>
                            <a href="{{ route('financeTransaction') }}" class="btn btn-outline-secondary">Reset filters</a>
                        </x-slot:action>
                    @endif
                </x-empty-state>
            @else
                <table class="table table-hover">
                    <thead>
                    <tr>
                        <th scope="col">Student</th>
                        <th scope="col">Class</th>
                        <th scope="col" class="d-none d-lg-table-cell">Due date</th>
                        <x-sort-th :href="$sortUrl('price')" :active="$sortedBy === 'price'" direction="asc">Total</x-sort-th>
                        <th scope="col" class="d-none d-lg-table-cell">Paid on</th>
                        <x-sort-th :href="$sortUrl('payment_status')" :active="$sortedBy === 'payment_status'" direction="asc">Status</x-sort-th>
                        <th scope="col"><span class="visually-hidden">Actions</span></th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach ($transactions as $transaction)
                        @php
                            $price = (int) $transaction->price;
                            $total = \App\Support\Discount::total($price, $transaction->discount);
                            $discountLabel = \App\Support\Discount::label($transaction->discount);
                        @endphp
                        <tr>
                            <td>{{ $transaction->LongName }}</td>
                            <td>{{ $transaction->class_name ?? '-' }}</td>
                            <td class="d-none d-lg-table-cell">{{ $transaction->transaction_date ? \Carbon\Carbon::parse($transaction->transaction_date)->format('d M Y') : '-' }}</td>
                            <td>
                                Rp{{ number_format($total ?? $price) }}
                                @if ($total === null)
                                    <span class="status-badge status-badge-warning">Invalid discount</span>
                                @elseif ($discountLabel !== '')
                                    <div class="row-note">Rp{{ number_format($price) }} − {{ $discountLabel }}</div>
                                @endif
                            </td>
                            <td class="d-none d-lg-table-cell">{{ $transaction->transaction_payment ? \Carbon\Carbon::parse($transaction->transaction_payment)->format('d M Y') : 'Waiting' }}</td>
                            <td><x-status-badge :status="$transaction->payment_status" /></td>
                            <td class="text-end text-nowrap">
                                @if ($transaction->payment_status === 'Unpaid')
                                    <a href="{{ route('paidTransaction', $transaction->id) }}" class="btn btn-sm btn-primary">Record payment</a>
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
