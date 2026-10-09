@props(['status' => null])
@php
    // Stored values: students.Status (aktif / non-aktif / trial) and transactions.payment_status (Paid / Unpaid).
    [$label, $tone] = [
        'aktif' => ['Active', 'success'],
        'trial' => ['Trial', 'warning'],
        'non-aktif' => ['Inactive', 'neutral'],
        'Paid' => ['Paid', 'success'],
        'Unpaid' => ['Unpaid', 'warning'],
    ][$status] ?? [filled($status) ? $status : 'Unknown', 'neutral'];
@endphp
<span {{ $attributes->class(['status-badge', 'status-badge-'.$tone]) }}>{{ $label }}</span>
