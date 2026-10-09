@extends('layouts.app')

@section('title', 'Update transaction · '.$row->LongName)

@section('content')
    {{-- old() is the failed save's own input, so it is checked like any return_url before it reaches an href. --}}
    @php
        $back = own_url(old('return_url', $return_url), url('/'));
    @endphp
    @php
        $discount = filled($transaction->discount) ? $transaction->discount : '0';
        // Crafted array input must never reach Discount::total() or be echoed.
        $oldPrice = old('inputPrice', $transaction->price);
        $oldDisc = old('inputDisc', $discount);
        $total = (is_array($oldPrice) || is_array($oldDisc)) ? null : \App\Support\Discount::total($oldPrice, $oldDisc);
        $className = $row->class_name ?? 'this class';
    @endphp

    <x-page-header :title="'Update transaction'" :subtitle="$row->LongName.' · '.($row->class_name ?? 'No class')">
        <x-slot:actions>
            <a href="{{ staff_route('transaction.show', $transaction->id) }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left" aria-hidden="true"></i> Back</a>
        </x-slot:actions>
    </x-page-header>

    <form class="card" method="post" action="{{ staff_route('transaction.update', $transaction) }}" data-transaction-form>
        @csrf
        <input type="hidden" name="return_url" value="{{ $back }}">
        <div class="card-body">
            <x-form.error-summary />

            <x-form.section title="Billing">
                <x-form.field name="inputJatuhTempo" label="Due date" type="date" :value="$transaction->transaction_date" required />
                <x-form.field name="inputPrice" label="Price" type="number" min="0" :value="$transaction->price" required data-price-input />
                <x-form.field name="inputDisc" label="Discount" :value="$discount" required data-discount-input help="A Rupiah amount (e.g. 50000) or a percentage (e.g. 10%). Use 0 for none." />
                <div class="form-field">
                    <span class="form-label d-block">Total</span>
                    <output class="form-static fw-semibold" for="field-inputPrice field-inputDisc" data-total-output aria-live="polite">{{ $total === null ? 'Invalid discount' : 'Rp'.number_format($total) }}</output>
                </div>
                <x-form.field name="inputQuota" label="Quota" type="number" min="1" max="24" :value="$transaction->transaction_quota" required />
                <x-form.field name="inputDesc" label="Description" :value="$transaction->desc" />
            </x-form.section>

            <x-form.section title="Payment">
                <x-form.field name="inputStatus" label="Status" type="select" :value="$transaction->payment_status" required
                              :options="['Unpaid' => 'Unpaid', 'Paid' => 'Paid']" />
                <x-form.field name="inputTanggalBayar" label="Payment date" type="date" :value="$transaction->transaction_payment" help="Required when Status is Paid; leave empty when Status is Unpaid." />
                <x-form.field name="Type" label="Payment type" :value="$transaction->transaction_type" />
                <x-form.field name="inputBankName" label="Bank name" :value="$data?->Bank?->bank_name" />
                <x-form.field name="inputSenderName" label="Sender name" :value="($data?->nama_pengirim === '-') ? '' : $data?->nama_pengirim" />

                <div class="form-field form-field-wide">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="all_transaction" value="1" id="field-all_transaction" data-apply-all
                               data-confirm-message="{{ 'Overwrite every other transaction of '.$row->LongName.' in '.$className.' with these values and mark them Paid?' }}"
                               aria-describedby="field-all_transaction-help" @checked(old('all_transaction'))>
                        <label class="form-check-label" for="field-all_transaction">Also apply these values to every other transaction of {{ $row->LongName }} in {{ $className }} and mark them Paid</label>
                    </div>
                    <div id="field-all_transaction-help" class="form-text">Only applies when a payment date is filled in. It overwrites due date, price, discount, quota, description and payment type on those transactions.</div>
                </div>
            </x-form.section>

            <div class="save-bar">
                <button type="submit" class="btn btn-primary">Save changes</button>
            </div>
        </div>
    </form>
@endsection

@push('scripts')
    <script src="{{ asset('assets/js/pages/transaction-form.js') }}" defer></script>
@endpush
