@props(['label'])
{{-- Row actions. strategy "fixed" keeps the menu from being clipped by the scrolling table card. --}}
<div class="dropdown d-inline-block">
    <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-toggle="dropdown" aria-expanded="false"
            data-bs-popper-config='{"strategy":"fixed"}' aria-label="{{ $label }}">
        <i class="bi bi-three-dots" aria-hidden="true"></i>
    </button>
    <ul class="dropdown-menu dropdown-menu-end">
        {{ $slot }}
    </ul>
</div>
