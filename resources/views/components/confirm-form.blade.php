@props(['action', 'message'])
{{-- The confirmation itself is the submit listener in public/assets/js/app.js. --}}
<form method="POST" action="{{ $action }}" data-confirm="{{ $message }}" {{ $attributes }}>
    @csrf
    {{ $slot }}
</form>
