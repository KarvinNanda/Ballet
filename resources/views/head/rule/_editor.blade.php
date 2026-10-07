{{-- CKEditor 5 (local, GPL) for the rule textarea #content. The server sanitizes whatever it sends. --}}
@push('head')
    <link rel="stylesheet" href="{{ asset('assets/vendor/ckeditor5/ckeditor5.css') }}">
@endpush

@push('scripts')
    <script type="importmap">
        { "imports": { "ckeditor5": "{{ asset('assets/vendor/ckeditor5/ckeditor5.js') }}" } }
    </script>
    <script type="module">
        import {
            ClassicEditor, Essentials, Paragraph, Heading, Bold, Italic, Underline,
            List, Link, BlockQuote, GeneralHtmlSupport,
        } from 'ckeditor5';

        ClassicEditor.create(document.querySelector('#content'), {
            licenseKey: 'GPL',
            plugins: [Essentials, Paragraph, Heading, Bold, Italic, Underline, List, Link, BlockQuote, GeneralHtmlSupport],
            toolbar: ['heading', '|', 'bold', 'italic', 'underline', '|', 'link', 'bulletedList', 'numberedList', 'blockQuote', '|', 'undo', 'redo'],
            // Keep the accordion markup existing rules use; same allowlist as App\Support\HtmlSanitizer.
            htmlSupport: {
                allow: [
                    { name: /^(div|h2|span)$/, classes: true, attributes: { id: true, 'aria-labelledby': true, 'data-bs-parent': true } },
                    { name: 'button', classes: true, attributes: { type: /^button$/, 'aria-expanded': true, 'aria-controls': true, 'data-bs-toggle': /^collapse$/, 'data-bs-target': true } },
                    { name: 'img', attributes: { src: true, alt: true, width: true, height: true }, classes: true },
                ],
            },
        }).catch((error) => console.error(error));
    </script>
@endpush
