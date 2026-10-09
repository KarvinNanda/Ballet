{{-- CKEditor 5 (local, GPL) for the rule textarea #content; setup in public/assets/js/pages/rule-editor.js. The server sanitizes whatever it sends. --}}
@push('head')
    <link rel="stylesheet" href="{{ asset('assets/vendor/ckeditor5/ckeditor5.css') }}">
    {{-- Starts the large vendor download early; the module imports this same URL. --}}
    <link rel="modulepreload" href="{{ asset('assets/vendor/ckeditor5/ckeditor5.js') }}">
@endpush

@push('scripts')
    <script type="module" src="{{ asset('assets/js/pages/rule-editor.js') }}"></script>
@endpush
