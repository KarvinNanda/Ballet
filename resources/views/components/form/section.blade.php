@props(['title'])
<fieldset {{ $attributes->class('form-section') }}>
    <legend class="form-section-title">{{ $title }}</legend>
    <div class="form-grid">
        {{ $slot }}
    </div>
</fieldset>
