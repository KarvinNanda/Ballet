@extends('layouts.app')

@section('title', 'Transaction · '.$detail->LongName)

@section('content')
    @php
        $total = \App\Support\Discount::total($detail->price, $detail->discount);
        $discountLabel = \App\Support\Discount::label($detail->discount);
        $date = fn ($value) => $value ? \Carbon\Carbon::parse($value)->format('d M Y') : '-';
    @endphp

    <div class="card summary-card">
        <div class="summary-head">
            <div>
                <h1 class="page-title summary-name">
                    <a href="{{ staff_route('student.show', ['student' => $detail->students_id, 'tab' => 'transactions']) }}">{{ $detail->LongName }}</a>
                </h1>
                <p class="summary-meta">{{ $detail->class_name ?? 'No class' }} · Due {{ $date($detail->transaction_date) }}</p>
            </div>
            <div class="page-actions">
                <x-status-badge :status="$detail->payment_status" />
                <a href="{{ staff_route('transaction.index') }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left" aria-hidden="true"></i> Back to transactions</a>
                @can('transaction.edit-paid', $transaction)
                    <a href="{{ staff_route('transaction.edit', $transaction->id) }}" class="btn btn-outline-secondary">Update</a>
                @endcan
                @can('transaction.delete')
                    <x-confirm-form :action="staff_route('transaction.destroy', $transaction->id)" :message="'Delete this transaction of '.$detail->LongName.'? This cannot be undone.'">
                        <input type="hidden" name="return_url" value="{{ staff_route('transaction.index') }}">
                        <button type="submit" class="btn btn-outline-danger">Delete…</button>
                    </x-confirm-form>
                @endcan
            </div>
        </div>
        <div class="stat-grid">
            <div class="stat"><span class="stat-label">Total</span><span class="stat-value">Rp{{ number_format($total ?? $detail->price) }}</span></div>
            <div class="stat"><span class="stat-label">Quota</span><span class="stat-value">{{ $detail->transaction_quota ?? 0 }}</span></div>
            <div class="stat"><span class="stat-label">Paid on</span><span class="stat-value">{{ $detail->transaction_payment ? $date($detail->transaction_payment) : 'Waiting' }}</span></div>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <section class="form-section">
                <h2 class="form-section-title">Billing</h2>
                <dl class="detail-grid">
                    <div><dt>Due date</dt><dd>{{ $date($detail->transaction_date) }}</dd></div>
                    <div><dt>Price</dt><dd>Rp{{ number_format($detail->price) }}</dd></div>
                    <div><dt>Discount</dt><dd>{{ $discountLabel !== '' ? $discountLabel : 'None' }}</dd></div>
                    <div>
                        <dt>Total</dt>
                        <dd>
                            Rp{{ number_format($total ?? $detail->price) }}
                            @if ($total === null)
                                <span class="status-badge status-badge-warning">Invalid discount</span>
                            @endif
                        </dd>
                    </div>
                    <div><dt>Quota</dt><dd>{{ $detail->transaction_quota ?? 0 }}</dd></div>
                    <div><dt>Description</dt><dd>{{ $detail->desc ?: '-' }}</dd></div>
                </dl>
            </section>

            <section class="form-section">
                <h2 class="form-section-title">Payment</h2>
                <dl class="detail-grid">
                    <div><dt>Paid on</dt><dd>{{ $detail->transaction_payment ? $date($detail->transaction_payment) : 'Waiting for payment' }}</dd></div>
                    <div><dt>Payment type</dt><dd>{{ $detail->transaction_type ?: '-' }}</dd></div>
                    <div><dt>Bank</dt><dd>{{ $data?->Bank?->bank_name ?? '-' }}</dd></div>
                    <div><dt>Sender</dt><dd>{{ $data?->nama_pengirim ?: '-' }}</dd></div>
                    <div><dt>Account number</dt><dd>{{ $detail->bank_rek ?: '-' }}</dd></div>
                </dl>
            </section>
        </div>
    </div>
@endsection
