@props(['action', 'reset' => null])
<form method="GET" action="{{ $action }}" role="search" aria-label="Filter" {{ $attributes->class('card filter-bar') }}>
    {{ $slot }}
    <div class="filter-bar-buttons">
        <button type="submit" class="btn btn-primary">Apply</button>
        <a href="{{ $reset ?? $action }}" class="btn btn-outline-secondary">Reset</a>
    </div>
</form>
