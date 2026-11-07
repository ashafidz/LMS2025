/*
 * Busy state for forms: after a form is sent, the submit button shows a spinner and "Memproses...",
 * every submit button of that form is disabled, and the form cannot be sent twice.
 * Opt out with <form data-no-busy>. Custom text: data-busy-text on the button.
 * Restored on back/forward cache (pageshow) and after 12 seconds (file downloads do not leave the page);
 * multipart (upload) forms get no 12-second restore.
 * window.rsFormIdle(form) restores one form by hand.
 */
(function () {
    'use strict';

    var SAFETY_MS = 12000;
    var busy = []; // { form, buttons: [{ el, disabled }], main, original, minWidth, timer }

    function submitButtons(form) {
        var list = Array.prototype.slice.call(form.querySelectorAll('button[type=submit], button:not([type]), input[type=submit]'));
        if (form.id) {
            var outside = document.querySelectorAll('[form="' + form.id.replace(/"/g, '\\"') + '"]');
            Array.prototype.forEach.call(outside, function (el) {
                var tag = el.tagName.toLowerCase();
                var type = (el.getAttribute('type') || '').toLowerCase();
                var isSubmit = (tag === 'button' && (type === 'submit' || type === '')) || (tag === 'input' && type === 'submit');
                if (isSubmit && list.indexOf(el) === -1) { list.push(el); }
            });
        }
        return list;
    }

    function find(form) {
        for (var i = 0; i < busy.length; i++) {
            if (busy[i].form === form) { return i; }
        }
        return -1;
    }

    function restore(entry) {
        clearTimeout(entry.timer);
        var form = entry.form;
        if (entry.main) {
            if (entry.main.tagName.toLowerCase() === 'input') {
                entry.main.value = entry.original;
            } else {
                entry.main.innerHTML = entry.original;
            }
            entry.main.style.minWidth = entry.minWidth;
        }
        entry.buttons.forEach(function (b) { b.el.disabled = b.disabled; });
        delete form.dataset.rsBusy;
        form.removeAttribute('aria-busy');
    }

    window.rsFormIdle = function (form) {
        var i = find(form);
        if (i === -1) { return; }
        var entry = busy.splice(i, 1)[0];
        restore(entry);
    };

    function restoreAll() {
        while (busy.length) { restore(busy.pop()); }
    }

    function makeBusy(form, submitter) {
        var buttons = submitButtons(form);
        var main = submitter && buttons.indexOf(submitter) !== -1 ? submitter : (buttons[0] || null);
        var label = (main && main.getAttribute('data-busy-text')) || 'Memproses...';
        var entry = { form: form, buttons: [], main: main, original: '', minWidth: '', timer: 0 };

        form.dataset.rsBusy = '1';
        form.setAttribute('aria-busy', 'true');

        if (main) {
            entry.minWidth = main.style.minWidth;
            main.style.minWidth = main.offsetWidth + 'px';
            if (main.tagName.toLowerCase() === 'input') {
                entry.original = main.value;
                main.value = label;
            } else {
                entry.original = main.innerHTML;
                while (main.firstChild) { main.removeChild(main.firstChild); }
                var spin = document.createElement('span');
                spin.className = 'rs-busy-spin';
                spin.setAttribute('aria-hidden', 'true');
                main.appendChild(spin);
                main.appendChild(document.createTextNode(label));
            }
        }
        buttons.forEach(function (el) {
            entry.buttons.push({ el: el, disabled: el.disabled });
            el.disabled = true;
        });
        // Uploads (video up to 100 MB) can take minutes: no timed restore, or a second click would send it again.
        if (String(form.enctype).toLowerCase() !== 'multipart/form-data') {
            entry.timer = setTimeout(function () { window.rsFormIdle(form); }, SAFETY_MS);
        }
        busy.push(entry);
    }

    // Capture phase: block a second submit while busy.
    document.addEventListener('submit', function (e) {
        var form = e.target;
        if (form && form.dataset && form.dataset.rsBusy === '1') {
            e.preventDefault();
            e.stopImmediatePropagation();
        }
    }, true);

    // Bubble phase: other handlers (data-confirm, AJAX forms) have run by the time the timeout fires.
    document.addEventListener('submit', function (e) {
        var form = e.target;
        var submitter = e.submitter || null;
        if (!form || !form.matches || !form.matches('form')) { return; }
        setTimeout(function () {
            if (e.defaultPrevented) { return; }
            if (form.dataset.rsBusy === '1') { return; }
            if (String(form.method).toLowerCase() === 'get') { return; }
            if (form.target) { return; }
            if (form.hasAttribute('data-no-busy')) { return; }
            makeBusy(form, submitter);
        }, 0);
    });

    window.addEventListener('pageshow', function (e) {
        if (e.persisted) { restoreAll(); }
    });
}());
