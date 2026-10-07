<aside id="appSidebar" class="app-sidebar offcanvas-lg offcanvas-start" tabindex="-1" aria-label="Menu utama">
    <div class="offcanvas-header d-lg-none">
        <span class="app-brand">En Pointe</span>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" data-bs-target="#appSidebar" aria-label="Tutup menu"></button>
    </div>
    <nav class="offcanvas-body">
        @foreach ($navigation as $group)
            @if ($group['title'])
                @php $groupId = 'nav-group-'.\Illuminate\Support\Str::slug($group['title']); @endphp
                <button type="button" class="nav-group-toggle {{ $group['open'] ? '' : 'collapsed' }}"
                        data-bs-toggle="collapse" data-bs-target="#{{ $groupId }}" aria-expanded="{{ $group['open'] ? 'true' : 'false' }}"
                        aria-controls="{{ $groupId }}">
                    <span>{{ $group['title'] }}</span>
                    <i class="bi bi-chevron-down" aria-hidden="true"></i>
                </button>
                <div id="{{ $groupId }}" class="collapse{{ $group['open'] ? ' show' : '' }}">
            @endif

            @foreach ($group['items'] as $item)
                <a href="{{ $item['url'] }}" class="app-nav-link{{ $item['active'] ? ' active' : '' }}"
                   @if ($item['active']) aria-current="page" @endif>
                    <i class="bi {{ $item['icon'] }}" aria-hidden="true"></i>
                    <span>{{ $item['label'] }}</span>
                </a>
            @endforeach

            @if ($group['title'])
                </div>
            @endif
        @endforeach
    </nav>
</aside>
