<link href="{{ asset('vendor/jodit/jodit.min.css') }}" rel="stylesheet">
<script src="{{ asset('vendor/jodit/jodit.min.js') }}"></script>
<script src="{{ asset('js/rs-image-shrink.js') }}"></script>
<script>
    (function () {
        if (typeof Jodit === 'undefined') {
            return;
        }

        var dataImageMessage = 'Gambar harus diunggah lewat tombol gambar di editor, bukan ditempel langsung.';
        var editors = []; // every Jodit instance of this form: {kind, textarea, instance}

        var baseConfig = {
            language: 'id',
            toolbarAdaptive: false,
            showCharsCounter: false,
            showWordsCounter: false,
            showXPathInStatusbar: false,
            askBeforePasteHTML: false,
            askBeforePasteFromWord: false,
            defaultActionOnPaste: 'insert_clear_html',
            placeholder: 'Ketik di sini...',
            uploader: {
                url: '{{ route('instructor.question-bank.images.store') }}',
                insertImageAsBase64URI: false,
                imagesExtensions: ['jpg', 'jpeg', 'png', 'gif', 'webp'],
                filesVariableName: function (i) { return 'files[' + i + ']'; },
                buildData: function (data) { return shrinkImages(data); },
                // Own upload instead of Jodit's Ajax: Jodit shows only the bare HTTP status text on a failed request,
                // and a blank second notice when buildData fails. Every outcome here becomes one readable message.
                customUploadFunction: function (data) { return uploadImage(data); },
                isSuccess: function (resp) { return !!(resp && resp.success); },
                getMessage: function (resp) {
                    return resp && resp.data && Array.isArray(resp.data.messages) && resp.data.messages.length
                        ? resp.data.messages[0]
                        : 'Gambar gagal diunggah. Coba lagi.';
                }
            }
        };

        // Phone photos are often 3-6 MB, above the 2 MB server limit. The shrinking itself lives in the shared
        // public/js/rs-image-shrink.js (also used by the course thumbnail and badge icon forms).
        var MAX_UPLOAD_BYTES = 2 * 1024 * 1024;
        var SHRINK_OPTIONS = {
            maxBytes: MAX_UPLOAD_BYTES,
            maxSide: 1600,
            alphaType: 'image/png',
            accept: ['image/jpeg', 'image/png', 'image/gif']
        };
        var TOO_BIG_MESSAGE = 'Ukuran gambar maksimal 2 MB. Perkecil gambar itu, lalu unggah lagi.';

        function failure(message) {
            return { success: false, data: { messages: [message] } };
        }

        function uploadImage(data) {
            if (data && data.rsError) {
                return Promise.resolve(failure(data.rsError));
            }
            return fetch('{{ route('instructor.question-bank.images.store') }}', {
                method: 'POST',
                body: data,
                credentials: 'same-origin',
                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' }
            }).then(function (response) {
                if (response.status === 413) {
                    return failure(TOO_BIG_MESSAGE);
                }
                if (response.status === 419) {
                    return failure('Sesi sudah berakhir. Muat ulang halaman, lalu unggah gambar lagi.');
                }
                return response.json().catch(function () {
                    return failure('Gambar gagal diunggah. Coba lagi.');
                });
            }).catch(function () {
                return failure('Gambar gagal diunggah. Periksa koneksi internet, lalu coba lagi.');
            });
        }

        // Resolves to the FormData to send, or to {rsError} when the image is still too big (an unshrunk GIF).
        function shrinkImages(data) {
            if (typeof FormData === 'undefined' || !(data instanceof FormData) || typeof createImageBitmap === 'undefined' || typeof RsImageShrink === 'undefined') {
                return data;
            }
            var entries = [];
            data.forEach(function (value, key) { entries.push([key, value]); });
            return Promise.all(entries.map(function (entry) {
                return entry[1] instanceof File
                    ? RsImageShrink.shrink(entry[1], SHRINK_OPTIONS).then(function (f) { return [entry[0], f]; })
                    : entry;
            })).then(function (done) {
                var tooBig = done.some(function (entry) {
                    return entry[1] instanceof File && entry[1].size > MAX_UPLOAD_BYTES;
                });
                if (tooBig) {
                    return { rsError: TOO_BIG_MESSAGE };
                }
                var out = new FormData();
                done.forEach(function (entry) {
                    if (entry[1] instanceof File) {
                        out.append(entry[0], entry[1], entry[1].name);
                    } else {
                        out.append(entry[0], entry[1]);
                    }
                });
                return out;
            });
        }

        var mainButtons = ['undo', 'redo', '|', 'bold', 'italic', 'underline', 'subscript', 'superscript', '|',
            'left', 'center', 'right', '|', 'ul', 'ol', '|', 'table', 'image', 'link'];
        var optionButtons = ['bold', 'italic', 'underline', 'subscript', 'superscript', '|', 'image'];

        function make(textarea, kind, height, buttons) {
            var config = Object.assign({}, baseConfig, { height: height, buttons: buttons });
            var instance = Jodit.make(textarea, config);
            editors.push({ kind: kind, textarea: textarea, instance: instance });
            return instance;
        }

        function isEmpty(instance) {
            var text = instance.editor.textContent.replace(/\u00a0/g, ' ').trim();
            return text === '' && instance.editor.querySelector('img, table') === null;
        }

        document.querySelectorAll('textarea[data-rich="question"]').forEach(function (el) {
            make(el, 'question', 260, mainButtons);
        });
        document.querySelectorAll('textarea[data-rich="explanation"]').forEach(function (el) {
            make(el, 'explanation', 180, mainButtons);
        });

        // Option editors are made one by one: server-rendered ones now, new rows by the form script.
        window.rsMakeOptionEditor = function (textarea) {
            return make(textarea, 'option', 120, optionButtons);
        };
        document.querySelectorAll('textarea[data-rich="option"]').forEach(function (el) {
            window.rsMakeOptionEditor(el);
        });

        window.rsDestroyOptionEditor = function (rowEl) {
            editors = editors.filter(function (item) {
                if (item.kind === 'option' && rowEl.contains(item.textarea)) {
                    item.instance.destruct();
                    return false;
                }
                return true;
            });
        };

        var first = document.querySelector('textarea[data-rich="question"]');
        var form = first ? first.closest('form') : null;
        if (!form) {
            return;
        }

        var block = function (e, message) {
            e.preventDefault();
            Swal.fire({
                icon: 'warning',
                title: 'Peringatan',
                text: message,
                confirmButtonText: 'Tutup',
                allowOutsideClick: true
            });
            form.dispatchEvent(new CustomEvent('question:blocked'));
        };

        form.addEventListener('submit', function (e) {
            // Jodit copies the editor into the textarea after a short debounce (synchronizeValues() does not flush it),
            // so copy it now. Otherwise the last typed characters are lost when Save is clicked right away.
            editors.forEach(function (item) {
                item.textarea.value = item.instance.getEditorValue();
            });

            for (var i = 0; i < editors.length; i += 1) {
                var item = editors[i];
                if (item.kind === 'question' && isEmpty(item.instance)) {
                    block(e, 'Teks pertanyaan tidak boleh kosong!');
                    return;
                }
            }
            for (var j = 0; j < editors.length; j += 1) {
                if (editors[j].kind === 'option' && isEmpty(editors[j].instance)) {
                    block(e, 'Setiap pilihan jawaban harus diisi!');
                    return;
                }
            }
            for (var k = 0; k < editors.length; k += 1) {
                if (editors[k].instance.editor.querySelector('img[src^="data:"]') !== null) {
                    block(e, dataImageMessage);
                    return;
                }
            }
        });
    })();
</script>
