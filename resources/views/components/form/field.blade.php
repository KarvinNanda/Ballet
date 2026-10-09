@props(['name', 'label', 'type' => 'text', 'value' => null, 'required' => false, 'help' => null, 'options' => [], 'wide' => false])
@php
    // $errors is shared by the web middleware; a component rendered outside a request (tests) may not have it.
    $errorBag = $errors ?? new \Illuminate\Support\ViewErrorBag;
    $id = 'field-'.$name;
    $error = $errorBag->first($name);
    $current = old($name, $value);
    if (is_array($current)) {
        // Crafted array input (name[]=x): never echo an array.
        $current = null;
    }
    if ($type === 'date' && $current instanceof \DateTimeInterface) {
        // An already-valid object (an Eloquent date cast): format it, no parsing involved.
        $current = $current->format('Y-m-d');
    } elseif ($type === 'date' && is_string($current) && preg_match('/^(\d{4}-\d{2}-\d{2})[T ]/', $current, $dateMatch)) {
        // A date input only accepts Y-m-d, so a datetime keeps just its date part. Never parse: Carbon turns
        // rejected input such as '20166-03-04' into a different valid date. Anything else renders as typed.
        $current = $dateMatch[1];
    }
    $describedBy = trim(($help ? $id.'-help ' : '').($error ? $id.'-error' : ''));
    $controlClass = ($type === 'select' ? 'form-select' : 'form-control').($error ? ' is-invalid' : '');
@endphp
<div class="form-field{{ $wide ? ' form-field-wide' : '' }}">
    <label for="{{ $id }}" class="form-label">{{ $label }}@if ($required)<span class="required-mark" aria-hidden="true">*</span>@endif</label>
    @if ($type === 'select')
        <select id="{{ $id }}" name="{{ $name }}" {{ $attributes->class([$controlClass]) }} @required($required) @if ($describedBy) aria-describedby="{{ $describedBy }}" @endif @if ($error) aria-invalid="true" @endif>
            @if (blank($current) && ! array_key_exists('', $options))
                <option value="" selected>Select…</option>
            @elseif (! array_key_exists((string) $current, $options))
                <option value="{{ $current }}" selected>{{ $current }}</option>
            @endif
            @foreach ($options as $optionValue => $optionLabel)
                <option value="{{ $optionValue }}" @selected((string) $current === (string) $optionValue)>{{ $optionLabel }}</option>
            @endforeach
        </select>
    @elseif ($type === 'textarea')
        <textarea id="{{ $id }}" name="{{ $name }}" {{ $attributes->class([$controlClass]) }} rows="3" @required($required) @if ($describedBy) aria-describedby="{{ $describedBy }}" @endif @if ($error) aria-invalid="true" @endif>{{ $current }}</textarea>
    @else
        <input type="{{ $type }}" id="{{ $id }}" name="{{ $name }}" value="{{ $current }}" {{ $attributes->class([$controlClass]) }} @required($required) @if ($describedBy) aria-describedby="{{ $describedBy }}" @endif @if ($error) aria-invalid="true" @endif>
    @endif
    @if ($help)
        <div id="{{ $id }}-help" class="form-text">{{ $help }}</div>
    @endif
    @if ($error)
        <div id="{{ $id }}-error" class="invalid-feedback d-block">{{ $error }}</div>
    @endif
</div>
