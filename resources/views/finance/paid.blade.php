@extends('layouts.app')

@section('title', 'Record payment · '.$studentName)

@section('content')
    {{-- old() is the failed save's own input, so it is checked like any return_url before it reaches an href. --}}
    @php
        $back = own_url(old('return_url', $return_url), url('/'));
    @endphp
    @php
        $price = (int) $transaction->price;
        $total = \App\Support\Discount::total($price, $transaction->discount);
        $discountLabel = \App\Support\Discount::label($transaction->discount);
        $date = fn ($value) => $value ? \Carbon\Carbon::parse($value)->format('d M Y') : '-';
    @endphp

    <x-page-header :title="'Record payment · '.$studentName">
        <x-slot:actions>
            <a href="{{ route('financeTransaction') }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left" aria-hidden="true"></i> Back to transactions</a>
        </x-slot:actions>
    </x-page-header>

    <div class="card mb-4">
        <div class="card-body">
            <section class="form-section">
                <h2 class="form-section-title">Billing</h2>
                <dl class="detail-grid">
                    <div><dt>Class</dt><dd>{{ $className ?? 'No class' }}</dd></div>
                    <div><dt>Due date</dt><dd>{{ $date($transaction->transaction_date) }}</dd></div>
                    <div><dt>Price</dt><dd>Rp{{ number_format($price) }}</dd></div>
                    <div><dt>Discount</dt><dd>{{ $discountLabel !== '' ? $discountLabel : 'None' }}</dd></div>
                    <div>
                        <dt>Total</dt>
                        <dd>
                            Rp{{ number_format($total ?? $price) }}
                            @if ($total === null)
                                <span class="status-badge status-badge-warning">Invalid discount</span>
                            @endif
                        </dd>
                    </div>
                    <div><dt>Quota</dt><dd>{{ $transaction->transaction_quota ?? 0 }}</dd></div>
                    <div><dt>Account number</dt><dd>{{ $student?->bank_rek ?: '-' }}</dd></div>
                    <div><dt>Description</dt><dd>{{ $transaction->desc ?: '-' }}</dd></div>
                </dl>
            </section>
        </div>
    </div>

    @if ($transaction->payment_status !== 'Unpaid')
        <div class="alert alert-info" role="status">This transaction is already settled.</div>
    @else
        <x-confirm-form :action="route('doPaidTransaction', $transaction->id)" :message="'Mark Rp'.number_format($total ?? $price).' from '.$studentName.' as paid?'" class="card">
            <input type="hidden" name="return_url" value="{{ $back }}">
            <div class="card-body">
                <x-form.error-summary />
                <x-form.section title="Payment">
                    <x-form.field name="datePaid" label="Payment date" type="date" :value="$transaction->transaction_payment" required />
                    <x-form.field name="Type" label="Payment type" :value="$transaction->transaction_type" required />
                    <x-form.field name="inputBankName" label="Bank name" :value="$data?->Bank?->bank_name" required />
                    <x-form.field name="inputSenderName" label="Sender name" :value="($data?->nama_pengirim === '-') ? '' : $data?->nama_pengirim" required />
                    <x-form.field name="inputQuota" label="Quota" type="number" min="1" max="24" :value="$transaction->transaction_quota" required />
                </x-form.section>
                <div class="save-bar">
                    <button type="submit" class="btn btn-primary">Mark as paid</button>
                </div>
            </div>
        </x-confirm-form>
    @endif
@endsection
