@props(['icon' => 'inbox', 'title'])
<div {{ $attributes->class('empty-state') }}>
    <i class="bi bi-{{ $icon }}" aria-hidden="true"></i>
    <p class="empty-state-title">{{ $title }}</p>
    @isset($action)
        {{ $action }}
    @endisset
</div>
