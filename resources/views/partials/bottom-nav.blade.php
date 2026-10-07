@php
    $bottomItems = collect($navigation)->flatMap(fn ($group) => $group['items'])->where('bottom', true)->take(3);
@endphp
<nav class="bottom-nav d-lg-none" aria-label="Navigasi cepat">
    @foreach ($bottomItems as $item)
        <a href="{{ $item['url'] }}" class="bottom-nav-link{{ $item['active'] ? ' active' : '' }}"
           @if ($item['active']) aria-current="page" @endif>
            <i class="bi {{ $item['icon'] }}" aria-hidden="true"></i>
            <span>{{ $item['label'] }}</span>
        </a>
    @endforeach
    <button type="button" class="bottom-nav-link" data-bs-toggle="offcanvas" data-bs-target="#appSidebar" aria-controls="appSidebar">
        <i class="bi bi-list" aria-hidden="true"></i>
        <span>Menu</span>
    </button>
</nav>
