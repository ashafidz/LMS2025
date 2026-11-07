<link href="{{ asset('vendor/jodit/jodit.min.css') }}" rel="stylesheet">
<script src="{{ asset('vendor/jodit/jodit.min.js') }}"></script>
<script>
    (function () {
        var textarea = document.querySelector('textarea[name="description"]');
        if (typeof Jodit === 'undefined' || !textarea) {
            return;
        }

        var editor = Jodit.make(textarea, {
            language: 'id',
            toolbarAdaptive: false,
            showCharsCounter: false,
            showWordsCounter: false,
            showXPathInStatusbar: false,
            askBeforePasteHTML: false,
            askBeforePasteFromWord: false,
            defaultActionOnPaste: 'insert_clear_html',
            placeholder: 'Ceritakan isi kursus ini...',
            minHeight: 200,
            buttons: ['bold', 'italic', 'underline', '|', 'ul', 'ol', '|', 'link'],
            buttonsMD: ['bold', 'italic', 'underline', '|', 'ul', 'ol', '|', 'link'],
            buttonsSM: ['bold', 'italic', 'underline', '|', 'ul', 'ol', '|', 'link'],
            buttonsXS: ['bold', 'italic', 'underline', '|', 'ul', 'ol', '|', 'link']
        });

        // Jodit copies the editor into the textarea after a short debounce, so copy it now.
        // Otherwise the last typed characters are lost when Save is clicked right away.
        var form = textarea.closest('form');
        if (form) {
            form.addEventListener('submit', function () {
                textarea.value = editor.getEditorValue();
            });
        }
    })();
</script>
