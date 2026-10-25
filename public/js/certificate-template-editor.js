/*
 * Editor template sertifikat (admin dan superadmin).
 * Tanpa build step dan tanpa dependensi baru: memakai jQuery UI (draggable, resizable) yang sudah dimuat layout.
 * Semua posisi disimpan dalam persen halaman supaya sama di editor, pratinjau, dan PDF.
 */
(function () {
    'use strict';

    var form = document.getElementById('template-form');
    var $ = window.jQuery;
    if (!form || !$) {
        return;
    }

    var PAGE_WIDTH_PT = { landscape: 841.89, portrait: 595.28 };
    var MAX_UPLOAD_BYTES = 5 * 1024 * 1024;

    var config = {
        previewUrl: form.dataset.previewUrl,
        csrf: form.querySelector('input[name="_token"]').value,
        fonts: JSON.parse(form.dataset.fonts),
        boldFonts: JSON.parse(form.dataset.boldFonts),
        italicFonts: JSON.parse(form.dataset.italicFonts),
        labels: JSON.parse(form.dataset.labels)
    };

    var DEFAULTS = {
        student_name: { x: 50, y: 50, width: 70, align: 'center', size: 36, bold: true, shrink_to_fit: true },
        course_title: { x: 50, y: 40, width: 70, align: 'center', size: 24, shrink_to_fit: true },
        instructor_name: { x: 50, y: 75, width: 40, align: 'center', size: 14 },
        certificate_code: { x: 85, y: 8, width: 25, align: 'right', size: 10 },
        issued_date: { x: 50, y: 85, width: 40, align: 'center', size: 14, date_format: 'long' },
        custom_text: { x: 50, y: 25, width: 60, align: 'center', size: 18, text: 'Teks baru' }
    };

    var state = {
        orientation: form.dataset.orientation === 'portrait' ? 'portrait' : 'landscape',
        elements: [],
        selectedId: null,
        dirty: false,
        nextId: 1,
        hasBackground: !!form.dataset.backgroundUrl
    };

    var canvas = document.getElementById('cert-canvas');
    var bgImage = document.getElementById('cert-bg');
    var emptyNote = document.getElementById('cert-empty');
    var guideV = document.getElementById('guide-v');
    var guideH = document.getElementById('guide-h');
    var fileInput = document.getElementById('background');
    var panel = document.getElementById('element-settings');

    var controls = {
        text: document.getElementById('ctl-text'),
        dateFormat: document.getElementById('ctl-date-format'),
        font: document.getElementById('ctl-font'),
        size: document.getElementById('ctl-size'),
        color: document.getElementById('ctl-color'),
        bold: document.getElementById('ctl-bold'),
        italic: document.getElementById('ctl-italic'),
        shrink: document.getElementById('ctl-shrink'),
        x: document.getElementById('ctl-x'),
        y: document.getElementById('ctl-y')
    };

    function round2(value) {
        return Math.round(value * 100) / 100;
    }

    function clamp(value, min, max) {
        return Math.min(max, Math.max(min, value));
    }

    function toBool(value) {
        return value === true || value === '1' || value === 1 || value === 'true' || value === 'on';
    }

    function markDirty() {
        state.dirty = true;
    }

    function labelFor(el) {
        return config.labels[el.key] || el.key;
    }

    function supportsBold(fontKey) {
        return config.boldFonts.indexOf(fontKey) !== -1;
    }

    function supportsItalic(fontKey) {
        return config.italicFonts.indexOf(fontKey) !== -1;
    }

    function createElement(key, overrides) {
        var base = Object.assign({
            align: 'center', font: 'helvetica', size: 24, color: '#000000',
            bold: false, italic: false, shrink_to_fit: false, text: '', date_format: 'long'
        }, DEFAULTS[key] || {}, overrides || {});

        return Object.assign(base, { id: state.nextId++, key: key });
    }

    function normalize(raw) {
        var key = raw.key in DEFAULTS ? raw.key : 'custom_text';
        var el = createElement(key, {
            x: parseFloat(raw.x), y: parseFloat(raw.y), width: parseFloat(raw.width),
            align: ['left', 'center', 'right'].indexOf(raw.align) !== -1 ? raw.align : 'center',
            font: config.fonts[raw.font] ? raw.font : 'helvetica',
            size: parseInt(raw.size, 10) || 24,
            color: /^#[0-9a-fA-F]{6}$/.test(raw.color || '') ? raw.color : '#000000',
            bold: toBool(raw.bold), italic: toBool(raw.italic), shrink_to_fit: toBool(raw.shrink_to_fit),
            text: raw.text || (key === 'custom_text' ? 'Teks baru' : ''),
            date_format: raw.date_format === 'short' ? 'short' : 'long'
        });
        ['x', 'y', 'width'].forEach(function (field) {
            if (isNaN(el[field])) {
                el[field] = DEFAULTS[key][field];
            }
        });
        if (!supportsBold(el.font)) { el.bold = false; }
        if (!supportsItalic(el.font)) { el.italic = false; }

        return el;
    }

    function sampleText(el) {
        switch (el.key) {
            case 'student_name': return 'Nama Siswa Contoh Yang Cukup Panjang';
            case 'course_title': return 'Judul Kursus Contoh';
            case 'instructor_name': return 'Nama Instruktur';
            case 'certificate_code': return 'LMS-0-0-0000000000';
            case 'issued_date':
                var now = new Date();
                if (el.date_format === 'short') {
                    return String(now.getDate()).padStart(2, '0') + '/' + String(now.getMonth() + 1).padStart(2, '0') + '/' + now.getFullYear();
                }
                return now.toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' });
            default: return el.text || 'Teks baru';
        }
    }

    function findElement(id) {
        for (var i = 0; i < state.elements.length; i++) {
            if (state.elements[i].id === id) { return state.elements[i]; }
        }
        return null;
    }

    // ---------- Kanvas ----------

    function setOrientation(orientation) {
        state.orientation = orientation;
        canvas.style.aspectRatio = orientation === 'portrait' ? '210 / 297' : '297 / 210';
        document.getElementById('orientation-input').value = orientation;
        document.getElementById('orient-landscape').checked = orientation === 'landscape';
        document.getElementById('orient-portrait').checked = orientation === 'portrait';
        renderCanvas();
    }

    // Meniru "kecilkan otomatis" di server: ukuran turun satu per satu sampai teks muat satu baris (minimal 60%)
    var measureContext = document.createElement('canvas').getContext('2d');

    function effectiveSize(el, text) {
        if (!el.shrink_to_fit) { return el.size; }

        var widthPt = el.width * PAGE_WIDTH_PT[state.orientation] / 100;
        var min = Math.floor(el.size * 0.6);
        var prefix = (el.italic ? 'italic ' : '') + (el.bold ? 'bold ' : '');

        for (var size = el.size; size >= min; size--) {
            measureContext.font = prefix + size + "px '" + config.fonts[el.font] + "', sans-serif";
            if (measureContext.measureText(text).width <= widthPt) { return size; }
        }

        return min;
    }

    function applyBoxStyle(el, box) {
        var cw = canvas.clientWidth;
        var ch = canvas.clientHeight;
        var widthPx = el.width * cw / 100;
        var anchorPx = el.x * cw / 100;
        var leftPx = el.align === 'right' ? anchorPx - widthPx : (el.align === 'center' ? anchorPx - widthPx / 2 : anchorPx);

        box.style.left = leftPx + 'px';
        box.style.top = (el.y * ch / 100) + 'px';
        box.style.width = widthPx + 'px';
        box.style.textAlign = el.align;
        box.style.fontFamily = "'" + config.fonts[el.font] + "', sans-serif";
        box.style.fontSize = (effectiveSize(el, box.textContent) * cw / PAGE_WIDTH_PT[state.orientation]) + 'px';
        box.style.color = el.color;
        box.style.fontWeight = el.bold ? 'bold' : 'normal';
        box.style.fontStyle = el.italic ? 'italic' : 'normal';
    }

    function readBoxIntoState(el, box) {
        var cw = canvas.clientWidth;
        var ch = canvas.clientHeight;
        var width = box.offsetWidth;
        var anchorPx = el.align === 'right' ? box.offsetLeft + width : (el.align === 'center' ? box.offsetLeft + width / 2 : box.offsetLeft);

        el.x = clamp(round2(anchorPx / cw * 100), 0, 100);
        el.y = clamp(round2(box.offsetTop / ch * 100), 0, 100);
        el.width = clamp(round2(width / cw * 100), 5, 100);
    }

    function showGuides(el) {
        guideV.style.display = Math.abs(el.x - 50) < 1 ? 'block' : 'none';
        guideH.style.display = Math.abs(el.y - 50) < 1 ? 'block' : 'none';
    }

    function hideGuides() {
        guideV.style.display = 'none';
        guideH.style.display = 'none';
    }

    function buildBox(el) {
        var box = document.createElement('div');
        box.className = 'cert-el' + (el.id === state.selectedId ? ' selected' : '');
        box.dataset.id = el.id;
        box.textContent = sampleText(el);
        applyBoxStyle(el, box);
        canvas.appendChild(box);

        $(box).draggable({
            containment: 'parent',
            start: function () { selectElement(el.id); },
            drag: function () {
                readBoxIntoState(el, box);
                showGuides(el);
            },
            stop: function () {
                readBoxIntoState(el, box);
                // Tempel ke tengah halaman jika sudah sangat dekat
                if (Math.abs(el.x - 50) < 1) { el.x = 50; }
                hideGuides();
                applyBoxStyle(el, box);
                fillPanel();
                markDirty();
            }
        }).resizable({
            handles: 'e',
            containment: 'parent',
            stop: function () {
                readBoxIntoState(el, box);
                applyBoxStyle(el, box);
                fillPanel();
                markDirty();
            }
        });

        box.addEventListener('mousedown', function () { selectElement(el.id); });
        bridgeTouch(box);

        return box;
    }

    // jQuery UI hanya mengenal mouse. Sentuhan jari di tablet diteruskan sebagai kejadian mouse supaya
    // kotak bisa diseret dan diubah lebarnya.
    function bridgeTouch(box) {
        var active = false;

        function simulate(type, touchEvent) {
            var touch = touchEvent.changedTouches[0];
            touch.target.dispatchEvent(new MouseEvent(type, {
                bubbles: true, cancelable: true, view: window, button: 0,
                clientX: touch.clientX, clientY: touch.clientY, screenX: touch.screenX, screenY: touch.screenY
            }));
        }

        box.addEventListener('touchstart', function (event) {
            if (event.touches.length !== 1) { return; }
            active = true;
            simulate('mouseover', event);
            simulate('mousemove', event);
            simulate('mousedown', event);
        }, { passive: false });

        box.addEventListener('touchmove', function (event) {
            if (!active) { return; }
            event.preventDefault();
            simulate('mousemove', event);
        }, { passive: false });

        box.addEventListener('touchend', function (event) {
            if (!active) { return; }
            active = false;
            simulate('mouseup', event);
        });
    }

    function renderCanvas() {
        Array.prototype.slice.call(canvas.querySelectorAll('.cert-el')).forEach(function (node) {
            $(node).draggable('destroy').resizable('destroy');
            node.remove();
        });
        state.elements.forEach(buildBox);
        emptyNote.style.display = state.hasBackground ? 'none' : 'flex';
        bgImage.style.display = state.hasBackground ? 'block' : 'none';
    }

    function refreshBox(el) {
        var box = canvas.querySelector('.cert-el[data-id="' + el.id + '"]');
        if (box) {
            box.textContent = sampleText(el);
            applyBoxStyle(el, box);
        }
    }

    // ---------- Daftar dan panel ----------

    function renderElementList() {
        var list = document.getElementById('element-list');
        list.innerHTML = '';
        state.elements.forEach(function (el) {
            var button = document.createElement('button');
            button.type = 'button';
            button.className = 'btn btn-sm btn-block ' + (el.id === state.selectedId ? 'btn-primary' : 'btn-outline-secondary');
            var snippet = el.key === 'custom_text' ? ': ' + (el.text || '').slice(0, 24) : '';
            button.textContent = labelFor(el) + snippet;
            button.addEventListener('click', function () { selectElement(el.id); });
            list.appendChild(button);
        });

        Array.prototype.slice.call(document.querySelectorAll('.js-add-element')).forEach(function (checkbox) {
            checkbox.checked = state.elements.some(function (el) { return el.key === checkbox.dataset.key; });
        });
    }

    function selectElement(id) {
        if (state.selectedId === id) { return; }
        state.selectedId = id;
        Array.prototype.slice.call(canvas.querySelectorAll('.cert-el')).forEach(function (node) {
            node.classList.toggle('selected', parseInt(node.dataset.id, 10) === id);
        });
        renderElementList();
        fillPanel();
    }

    function fillPanel() {
        var el = findElement(state.selectedId);
        panel.style.display = el ? 'block' : 'none';
        if (!el) { return; }

        document.getElementById('settings-title').textContent = 'Pengaturan: ' + labelFor(el);
        document.getElementById('group-text').style.display = el.key === 'custom_text' ? 'block' : 'none';
        document.getElementById('group-date-format').style.display = el.key === 'issued_date' ? 'block' : 'none';
        document.getElementById('ctl-delete').style.display = el.key === 'student_name' ? 'none' : 'inline-block';

        controls.text.value = el.text;
        controls.dateFormat.value = el.date_format;
        controls.font.value = el.font;
        controls.size.value = el.size;
        controls.color.value = el.color;
        controls.bold.checked = el.bold;
        controls.italic.checked = el.italic;
        controls.shrink.checked = el.shrink_to_fit;
        controls.x.value = el.x;
        controls.y.value = el.y;

        var canBold = supportsBold(el.font);
        var canItalic = supportsItalic(el.font);
        controls.bold.disabled = !canBold;
        controls.italic.disabled = !canItalic;
        document.getElementById('style-hint').style.display = (canBold && canItalic) ? 'none' : 'block';

        Array.prototype.slice.call(document.querySelectorAll('.js-align')).forEach(function (button) {
            button.classList.toggle('active', button.dataset.align === el.align);
        });
    }

    function onControlChange(apply) {
        return function () {
            var el = findElement(state.selectedId);
            if (!el) { return; }
            apply(el);
            refreshBox(el);
            markDirty();
        };
    }

    controls.text.addEventListener('input', onControlChange(function (el) {
        el.text = controls.text.value;
        renderElementList();
    }));
    controls.dateFormat.addEventListener('change', onControlChange(function (el) { el.date_format = controls.dateFormat.value; }));
    controls.font.addEventListener('change', onControlChange(function (el) {
        el.font = controls.font.value;
        if (document.fonts && document.fonts.load) { document.fonts.load("16px '" + config.fonts[el.font] + "'"); }
        if (!supportsBold(el.font)) { el.bold = false; }
        if (!supportsItalic(el.font)) { el.italic = false; }
        fillPanel();
    }));
    controls.size.addEventListener('input', onControlChange(function (el) {
        el.size = clamp(parseInt(controls.size.value, 10) || 6, 6, 96);
    }));
    controls.color.addEventListener('input', onControlChange(function (el) { el.color = controls.color.value; }));
    controls.bold.addEventListener('change', onControlChange(function (el) { el.bold = controls.bold.checked; }));
    controls.italic.addEventListener('change', onControlChange(function (el) { el.italic = controls.italic.checked; }));
    controls.shrink.addEventListener('change', onControlChange(function (el) { el.shrink_to_fit = controls.shrink.checked; }));
    controls.x.addEventListener('input', onControlChange(function (el) { el.x = clamp(parseFloat(controls.x.value) || 0, 0, 100); }));
    controls.y.addEventListener('input', onControlChange(function (el) { el.y = clamp(parseFloat(controls.y.value) || 0, 0, 100); }));

    Array.prototype.slice.call(document.querySelectorAll('.js-align')).forEach(function (button) {
        button.addEventListener('click', onControlChange(function (el) {
            el.align = button.dataset.align;
            fillPanel();
        }));
    });

    document.getElementById('ctl-delete').addEventListener('click', function () {
        var el = findElement(state.selectedId);
        if (el && el.key !== 'student_name') {
            removeElement(el);
        }
    });

    function removeElement(el) {
        state.elements = state.elements.filter(function (item) { return item.id !== el.id; });
        if (state.selectedId === el.id) { state.selectedId = null; }
        markDirty();
        renderCanvas();
        renderElementList();
        fillPanel();
    }

    function addElement(key) {
        var el = createElement(key);
        state.elements.push(el);
        state.selectedId = el.id;
        markDirty();
        renderCanvas();
        renderElementList();
        fillPanel();
    }

    Array.prototype.slice.call(document.querySelectorAll('.js-add-element')).forEach(function (checkbox) {
        checkbox.addEventListener('change', function () {
            var key = checkbox.dataset.key;
            if (checkbox.checked) {
                addElement(key);
                return;
            }
            state.elements.filter(function (el) { return el.key === key; }).forEach(removeElement);
        });
    });

    document.getElementById('add-custom-text').addEventListener('click', function () { addElement('custom_text'); });

    canvas.addEventListener('mousedown', function (event) {
        if (event.target === canvas || event.target === bgImage || event.target === emptyNote) {
            state.selectedId = null;
            Array.prototype.slice.call(canvas.querySelectorAll('.cert-el')).forEach(function (node) { node.classList.remove('selected'); });
            renderElementList();
            fillPanel();
        }
    });

    // ---------- Gambar latar ----------

    fileInput.addEventListener('change', function () {
        var file = fileInput.files[0];
        if (!file) { return; }

        if (['image/png', 'image/jpeg'].indexOf(file.type) === -1) {
            fileInput.value = '';
            Swal.fire({ icon: 'warning', title: 'File tidak cocok', text: 'Gambar latar harus berupa file PNG atau JPG.' });
            return;
        }
        if (file.size > MAX_UPLOAD_BYTES) {
            fileInput.value = '';
            Swal.fire({ icon: 'warning', title: 'File terlalu besar', text: 'Ukuran gambar latar maksimal 5 MB.' });
            return;
        }

        var url = URL.createObjectURL(file);
        var probe = new Image();
        probe.onload = function () {
            if (Math.max(probe.naturalWidth, probe.naturalHeight) < 1000) {
                fileInput.value = '';
                URL.revokeObjectURL(url);
                Swal.fire({ icon: 'warning', title: 'Gambar terlalu kecil', text: 'Sisi terpanjang gambar minimal 1000 piksel agar hasil cetak tetap tajam.' });
                return;
            }
            bgImage.src = url;
            state.hasBackground = true;
            setOrientation(probe.naturalWidth >= probe.naturalHeight ? 'landscape' : 'portrait');
            checkRatio(probe.naturalWidth, probe.naturalHeight);
            markDirty();
        };
        probe.src = url;
    });

    function checkRatio(width, height) {
        var target = state.orientation === 'portrait' ? 210 / 297 : 297 / 210;
        document.getElementById('ratio-warning').style.display = Math.abs((width / height) / target - 1) > 0.05 ? 'block' : 'none';
    }

    Array.prototype.slice.call(document.querySelectorAll('input[name="orientation_choice"]')).forEach(function (radio) {
        radio.addEventListener('change', function () {
            setOrientation(radio.value);
            markDirty();
        });
    });

    // ---------- Kirim form dan pratinjau ----------

    function syncInputs() {
        var holder = document.getElementById('elements-inputs');
        holder.innerHTML = '';

        function add(index, name, value) {
            var input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'elements[' + index + '][' + name + ']';
            input.value = value;
            holder.appendChild(input);
        }

        state.elements.forEach(function (el, index) {
            ['key', 'x', 'y', 'width', 'align', 'font', 'size', 'color'].forEach(function (field) { add(index, field, el[field]); });
            add(index, 'bold', el.bold ? '1' : '0');
            add(index, 'italic', el.italic ? '1' : '0');
            add(index, 'shrink_to_fit', el.shrink_to_fit ? '1' : '0');
            if (el.key === 'custom_text') { add(index, 'text', el.text); }
            if (el.key === 'issued_date') { add(index, 'date_format', el.date_format); }
        });
    }

    function hasBackgroundSource() {
        return fileInput.files.length > 0 || !!form.querySelector('input[name="based_on_id"]');
    }

    function needBackground() {
        Swal.fire({ icon: 'warning', title: 'Pilih gambar latar dulu', text: 'Unggah gambar PNG atau JPG di Langkah 1.' });
    }

    form.addEventListener('submit', function (event) {
        if (!hasBackgroundSource()) {
            event.preventDefault();
            needBackground();
            return;
        }
        syncInputs();
        state.dirty = false;
        document.getElementById('btn-save').disabled = true;
    });

    document.getElementById('btn-preview').addEventListener('click', function () {
        if (!hasBackgroundSource()) {
            needBackground();
            return;
        }
        syncInputs();

        var button = this;
        var original = button.innerHTML;
        button.disabled = true;
        button.textContent = 'Membuat pratinjau...';
        var tab = window.open('', '_blank');

        fetch(config.previewUrl, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': config.csrf, 'Accept': 'application/json' },
            body: new FormData(form)
        }).then(function (response) {
            if (response.ok) {
                return response.blob().then(function (blob) {
                    var url = URL.createObjectURL(blob);
                    if (tab) { tab.location = url; } else { window.open(url, '_blank'); }
                });
            }
            if (tab) { tab.close(); }
            if (response.status === 422) {
                return response.json().then(function (body) {
                    var messages = [];
                    Object.keys(body.errors || {}).forEach(function (key) { messages = messages.concat(body.errors[key]); });
                    Swal.fire({ icon: 'warning', title: 'Pratinjau belum bisa dibuat', html: '<ul style="text-align:left">' + messages.map(function (m) {
                        var li = document.createElement('li'); li.textContent = m; return li.outerHTML;
                    }).join('') + '</ul>' });
                });
            }
            var text = response.status === 429 ? 'Terlalu sering membuat pratinjau. Tunggu sebentar lalu coba lagi.' : 'Pratinjau gagal dibuat. Coba lagi.';
            Swal.fire({ icon: 'error', title: 'Gagal', text: text });
        }).catch(function () {
            if (tab) { tab.close(); }
            Swal.fire({ icon: 'error', title: 'Gagal', text: 'Pratinjau gagal dibuat. Periksa koneksi internet Anda.' });
        }).then(function () {
            button.disabled = false;
            button.innerHTML = original;
        });
    });

    window.addEventListener('beforeunload', function (event) {
        if (state.dirty) {
            event.preventDefault();
            event.returnValue = '';
        }
    });

    // Ukuran teks yang menyusut bergantung pada lebar huruf, jadi hitung ulang setelah font selesai dimuat
    if (document.fonts && document.fonts.addEventListener) {
        document.fonts.addEventListener('loadingdone', function () { renderCanvas(); });
    }

    var resizeTimer = null;
    window.addEventListener('resize', function () {
        clearTimeout(resizeTimer);
        resizeTimer = setTimeout(renderCanvas, 150);
    });

    // ---------- Mulai ----------

    (function init() {
        var initial = [];
        try {
            initial = JSON.parse(form.dataset.elements) || [];
        } catch (e) {
            initial = [];
        }
        if (initial && !Array.isArray(initial)) {
            initial = Object.keys(initial).map(function (k) { return initial[k]; });
        }

        state.elements = initial.map(normalize);
        if (!state.elements.some(function (el) { return el.key === 'student_name'; })) {
            state.elements.unshift(createElement('student_name'));
        }

        if (form.dataset.backgroundUrl) {
            bgImage.src = form.dataset.backgroundUrl;
            bgImage.addEventListener('load', function () { checkRatio(bgImage.naturalWidth, bgImage.naturalHeight); });
        }

        setOrientation(state.orientation);
        renderElementList();
        fillPanel();
    })();
})();
