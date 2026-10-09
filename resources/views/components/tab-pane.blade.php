@props(['name', 'active' => false])
<div class="tab-pane fade{{ $active ? ' show active' : '' }}" id="pane-{{ $name }}" role="tabpanel" aria-labelledby="tab-{{ $name }}" tabindex="0">
    {{ $slot }}
</div>
