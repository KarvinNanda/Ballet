// Rule editor (head rules, Add and Update): CKEditor 5 on textarea#content.
// Loaded by head/rule/_editor.blade.php as <script type="module">; the import is relative to this file, so no importmap.
// The server sanitizes whatever this sends (App\Support\HtmlSanitizer).
import {
    ClassicEditor, Essentials, Paragraph, Heading, Bold, Italic, Underline,
    List, Link, BlockQuote, GeneralHtmlSupport,
} from '../../vendor/ckeditor5/ckeditor5.js';

const element = document.querySelector('#content');

if (element) {
    ClassicEditor.create(element, {
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
}
