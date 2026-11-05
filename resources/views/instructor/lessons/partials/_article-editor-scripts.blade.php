<link href="{{ asset('vendor/jodit/jodit.min.css') }}" rel="stylesheet">
<script src="{{ asset('vendor/jodit/jodit.min.js') }}"></script>
<script>
    (function () {
        var textarea = document.getElementById('article-content');
        if (!textarea || typeof Jodit === 'undefined') {
            return;
        }
        var editor = Jodit.make(textarea, {
            language: 'id',
            height: 480,
            toolbarAdaptive: false,
            buttons: ['undo', 'redo', '|', 'paragraph', '|', 'bold', 'italic', 'underline', '|',
                'left', 'center', 'right', '|', 'outdent', 'indent', '|', 'ul', 'ol', '|', 'table', 'image', 'link'],
            showCharsCounter: false,
            showWordsCounter: false,
            showXPathInStatusbar: false,
            askBeforePasteHTML: false,
            askBeforePasteFromWord: false,
            defaultActionOnPaste: 'insert_clear_html',
            uploader: { insertImageAsBase64URI: true, imagesExtensions: ['jpg', 'jpeg', 'png', 'gif'] }
        });

        var form = textarea.closest('form');
        if (!form) {
            return;
        }
        // HtmlSanitizer only keeps data URI images of these types; other types are dropped silently on save.
        var allowedImage = /^data:image\/(png|jpe?g|gif);base64,/i;
        var maxImageBytes = 2 * 1024 * 1024;
        var maxTotalBytes = 6 * 1024 * 1024; // below the 8 MB PHP post_max_size

        var block = function (e, message) {
            e.preventDefault();
            Swal.fire({
                icon: 'warning',
                title: 'Peringatan',
                text: message,
                confirmButtonText: 'Tutup',
                allowOutsideClick: true
            });
            form.dispatchEvent(new CustomEvent('article:blocked'));
        };

        form.addEventListener('submit', function (e) {
            var text = editor.editor.textContent.replace(/\u00a0/g, ' ').trim();
            var hasMedia = editor.editor.querySelector('img, table, iframe') !== null;
            if (text === '' && !hasMedia) {
                block(e, 'Konten artikel tidak boleh kosong!');
                return;
            }

            var html = editor.value;
            var holder = document.createElement('div');
            holder.innerHTML = html;
            var images = holder.querySelectorAll('img[src^="data:"]');
            for (var i = 0; i < images.length; i += 1) {
                var src = images[i].getAttribute('src');
                if (!allowedImage.test(src)) {
                    block(e, 'Ada gambar dengan format yang belum didukung (misalnya WebP). Simpan gambar itu sebagai JPG atau PNG, hapus dari artikel, lalu sisipkan lagi.');
                    return;
                }
                if ((src.length - src.indexOf(',') - 1) * 0.75 > maxImageBytes) {
                    block(e, 'Ada gambar yang terlalu besar (lebih dari 2 MB). Perkecil gambar itu, hapus dari artikel, lalu sisipkan lagi.');
                    return;
                }
            }
            if (html.length > maxTotalBytes) {
                block(e, 'Ukuran artikel terlalu besar karena gambarnya. Kurangi atau perkecil gambar, lalu simpan lagi.');
            }
        });
    })();
</script>
