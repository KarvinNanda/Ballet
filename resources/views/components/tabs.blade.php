@props(['tabs', 'active'])
<ul class="nav nav-tabs" role="tablist">
    @foreach ($tabs as $key => $label)
        <li class="nav-item" role="presentation">
            <button class="nav-link{{ $active === $key ? ' active' : '' }}" id="tab-{{ $key }}" type="button" role="tab"
                    data-bs-toggle="tab" data-bs-target="#pane-{{ $key }}" aria-controls="pane-{{ $key }}"
                    aria-selected="{{ $active === $key ? 'true' : 'false' }}">{{ $label }}</button>
        </li>
    @endforeach
</ul>
