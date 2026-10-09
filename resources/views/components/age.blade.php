@props(['dob' => null])
@php
    // Whole years since a stored date of birth; a dash when it is missing, unreadable or in the future.
    $years = null;
    if (filled($dob)) {
        try {
            $years = \Carbon\Carbon::parse($dob)->age;
        } catch (\Throwable) {
            $years = null;
        }
    }
@endphp
{{ $years !== null && $years >= 0 ? $years : '–' }}
