@props(['href', 'active' => false, 'direction' => null])
@php($descending = strtolower((string) $direction) === 'desc') {{-- the controllers accept DESC too --}}
<th scope="col" @if ($active) aria-sort="{{ $descending ? 'descending' : 'ascending' }}" @endif {{ $attributes }}>
    <a href="{{ $href }}" class="sort-link">{{ $slot }}@if ($active) <i class="bi bi-arrow-{{ $descending ? 'down' : 'up' }}" aria-hidden="true"></i>@endif</a>
</th>
