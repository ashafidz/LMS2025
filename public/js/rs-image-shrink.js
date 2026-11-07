/*
 * Shrinks large images in the browser before a form is sent.
 *
 * Phone photos are often 3-6 MB, above the server limits. Large images (and types the server does not accept but the
 * browser can read, such as WebP) are scaled down and saved again. Re-encoding also drops EXIF data such as the GPS
 * location. GIF is left as is, because redrawing it would lose the animation.
 *
 * Used by the question editor (window.RsImageShrink.shrink) and by file inputs marked with data-rs-shrink (bindInput).
 * Without createImageBitmap or DataTransfer nothing happens; the server still validates every upload.
 */
(function (global) {
    'use strict';

    var DEFAULT_QUALITY = 0.85;
    var EXTENSIONS = { 'image/jpeg': '.jpg', 'image/png': '.png', 'image/webp': '.webp' };
    var TYPE_LABELS = { 'image/jpeg': 'JPG', 'image/png': 'PNG', 'image/gif': 'GIF', 'image/webp': 'WebP' };

    function isAccepted(type, accept) {
        return !accept || !accept.length || accept.indexOf(type) !== -1;
    }

    function needsShrink(file, o) {
        if (!(file instanceof File) || file.type === 'image/gif' || !/^image\//.test(file.type)) {
            return false;
        }
        if (file.size > o.maxBytes) {
            return true;
        }
        // A type the server does not accept (for example WebP for a thumbnail) is converted when the browser can read it.
        if (o.accept && o.accept.length) {
            return o.accept.indexOf(file.type) === -1;
        }
        return file.type === 'image/webp';
    }

    function toBlob(canvas, type, quality) {
        return new Promise(function (resolve) { canvas.toBlob(resolve, type, quality); });
    }

    // PNG sources try the alpha type first; WebP sources only when the alpha type is WebP too.
    function triesAlphaType(file, o) {
        if (!o.alphaType) {
            return false;
        }
        return file.type === 'image/png' || (file.type === 'image/webp' && o.alphaType === 'image/webp');
    }

    function shrinkFile(file, o) {
        return createImageBitmap(file, { imageOrientation: 'from-image' }).then(function (bitmap) {
            var scale = Math.min(1, o.maxSide / Math.max(bitmap.width, bitmap.height));
            var canvas = document.createElement('canvas');
            canvas.width = Math.round(bitmap.width * scale);
            canvas.height = Math.round(bitmap.height * scale);
            var ctx = canvas.getContext('2d');
            ctx.drawImage(bitmap, 0, 0, canvas.width, canvas.height);
            bitmap.close();

            var base = file.name.replace(/\.[^.]+$/, '') || 'gambar';
            // Keep transparency (PNG or WebP) when it fits, otherwise JPEG.
            var first = triesAlphaType(file, o) ? toBlob(canvas, o.alphaType) : Promise.resolve(null);
            return first.then(function (alpha) {
                // A browser that cannot encode the type hands back a PNG; only keep what the server accepts.
                if (alpha && alpha.size <= o.maxBytes && isAccepted(alpha.type, o.accept)) {
                    return new File([alpha], base + (EXTENSIONS[alpha.type] || ''), { type: alpha.type });
                }
                // JPEG has no transparency: paint white behind the image, otherwise transparent areas turn black.
                // Done only now, so the alpha attempt above kept the transparency.
                ctx.globalCompositeOperation = 'destination-over';
                ctx.fillStyle = '#ffffff';
                ctx.fillRect(0, 0, canvas.width, canvas.height);
                ctx.globalCompositeOperation = 'source-over';
                return toBlob(canvas, 'image/jpeg', o.jpegQuality).then(function (jpg) {
                    return jpg ? new File([jpg], base + '.jpg', { type: 'image/jpeg' }) : file;
                });
            });
        }).catch(function () {
            return file; // the browser cannot read it (for example HEIC); the caller reports it
        });
    }

    /**
     * shrink(file, { maxBytes, maxSide, alphaType, jpegQuality, accept }) -> Promise<File>
     * Resolves the original file when nothing needs to change or the browser cannot read it.
     */
    function shrink(file, opts) {
        var o = {
            maxBytes: opts.maxBytes,
            maxSide: opts.maxSide,
            alphaType: opts.alphaType || null,
            jpegQuality: opts.jpegQuality || DEFAULT_QUALITY,
            accept: opts.accept || null
        };
        if (typeof createImageBitmap === 'undefined' || !needsShrink(file, o)) {
            return Promise.resolve(file);
        }
        return shrinkFile(file, o);
    }

    function formatSize(bytes) {
        if (bytes >= 1024 * 1024) {
            return (bytes / (1024 * 1024)).toFixed(1).replace('.', ',') + ' MB';
        }
        return Math.round(bytes / 1024) + ' KB';
    }

    function limitLabel(bytes) {
        var mb = bytes / (1024 * 1024);
        return (mb === Math.floor(mb) ? String(mb) : mb.toFixed(1).replace('.', ',')) + ' MB';
    }

    function formatMessage(accept) {
        var names = (accept || []).map(function (type) { return TYPE_LABELS[type] || type; });
        var list = names.length > 1
            ? names.slice(0, -1).join(', ') + ', atau ' + names[names.length - 1]
            : names.join('');
        return 'Format gambar harus ' + list + '.';
    }

    function readOptions(input) {
        var accept = (input.getAttribute('data-accept') || '').split(',').map(function (t) { return t.trim(); }).filter(Boolean);
        return {
            maxBytes: parseInt(input.getAttribute('data-max-bytes'), 10),
            maxSide: parseInt(input.getAttribute('data-max-side'), 10),
            alphaType: input.getAttribute('data-alpha-type') || null,
            accept: accept,
            label: input.getAttribute('data-label') || ''
        };
    }

    function canDecode(file) {
        return createImageBitmap(file).then(function (bitmap) {
            bitmap.close();
            return true;
        }).catch(function () {
            return false;
        });
    }

    function submitButtons(form) {
        return form ? Array.prototype.slice.call(form.querySelectorAll('button[type=submit], input[type=submit], button:not([type])')) : [];
    }

    function bindInput(input) {
        if (input.rsShrinkBound || typeof createImageBitmap === 'undefined' || typeof DataTransfer === 'undefined') {
            return;
        }
        input.rsShrinkBound = true;

        var note = null;
        var runId = 0;

        function showNote(text, className) {
            if (!note) {
                note = document.createElement('small');
                note.className = 'form-text rs-shrink-note';
                input.insertAdjacentElement('afterend', note);
            }
            note.className = 'form-text rs-shrink-note ' + className;
            note.textContent = text;
        }

        function clearNote() {
            if (note) {
                note.parentNode.removeChild(note);
                note = null;
            }
        }

        function reject(message) {
            input.value = '';
            showNote(message, 'text-danger');
        }

        function announce(file) {
            input.dispatchEvent(new CustomEvent('rs-image-ready', { bubbles: true, detail: { file: file, label: readOptions(input).label } }));
        }

        // Unlock the submit buttons again, with the state they had before the first lock.
        function restoreButtons() {
            if (!input.rsLocked) {
                return;
            }
            submitButtons(input.form).forEach(function (b, i) { b.disabled = input.rsWasDisabled[i]; });
            input.rsLocked = false;
        }

        input.addEventListener('change', function () {
            var original = input.files && input.files[0];
            if (!original) {
                runId++; // cancel a running shrink, so it does not put its result back into the cleared input
                clearNote();
                restoreButtons();
                return;
            }

            var opts = readOptions(input);
            var myRun = ++runId;
            var buttons = submitButtons(input.form);
            // Remember the original state once; a second pick while the first is still running must not save "disabled".
            if (!input.rsLocked) {
                input.rsLocked = true;
                input.rsWasDisabled = buttons.map(function (b) { return b.disabled; });
            }
            buttons.forEach(function (b) { b.disabled = true; });
            showNote('Menyiapkan gambar...', 'text-muted');

            shrink(original, opts).then(function (result) {
                if (myRun !== runId) {
                    return null; // the user picked another file meanwhile
                }
                if (!isAccepted(result.type, opts.accept)) {
                    reject(formatMessage(opts.accept));
                    return null;
                }
                if (result.size > opts.maxBytes) {
                    reject('Ukuran gambar maksimal ' + limitLabel(opts.maxBytes) + '. Perkecil gambar itu, lalu pilih lagi.');
                    return null;
                }
                return canDecode(result).then(function (ok) {
                    if (myRun !== runId) {
                        return null;
                    }
                    if (!ok) {
                        reject(formatMessage(opts.accept));
                        return null;
                    }
                    if (result !== original) {
                        var dt = new DataTransfer();
                        dt.items.add(result);
                        input.files = dt.files;
                        showNote(result.size < original.size
                            ? 'Gambar diperkecil otomatis dari ' + formatSize(original.size) + ' menjadi ' + formatSize(result.size) + '.'
                            : 'Gambar diubah otomatis ke format ' + (TYPE_LABELS[result.type] || result.type) + ' (' + formatSize(result.size) + ').', 'text-muted');
                    } else {
                        clearNote();
                    }
                    return result;
                });
            }).catch(function () {
                clearNote(); // never block the form: the server still validates
                return original;
            }).then(function () {
                if (myRun === runId) {
                    restoreButtons();
                    announce(input.files && input.files[0] ? input.files[0] : null);
                }
            });
        });
    }

    function bindAll() {
        Array.prototype.forEach.call(document.querySelectorAll('input[type=file][data-rs-shrink]'), bindInput);
    }

    global.RsImageShrink = { shrink: shrink, bindInput: bindInput };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', bindAll);
    } else {
        bindAll();
    }
})(window);
