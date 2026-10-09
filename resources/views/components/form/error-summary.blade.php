@php
    $fields = count(($errors ?? new \Illuminate\Support\ViewErrorBag)->keys());
@endphp
@if ($fields > 0)
    <div class="alert alert-danger" role="alert">Please fix the {{ $fields === 1 ? 'field' : $fields.' fields' }} marked below.</div>
@endif
