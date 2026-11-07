/*
 * Site dialogs (SweetAlert2) instead of browser pop-ups.
 *   rsConfirm({ title, text, confirmText, cancelText, danger }) -> Promise<boolean>
 *   rsAlert({ title, text, icon }) -> Promise
 *   rsToast(text, icon) -> Promise (settles when the toast closes)
 * A <form data-confirm="..."> asks first. Options: data-confirm-title, data-confirm-ok, data-confirm-danger.
 * Titles go in titleText (plain text): Swal renders 'title' as HTML and the login toast contains the user's name.
 * Fallback to window.confirm / window.alert only when Swal did not load (CDN blocked).
 */
(function () {
    'use strict';

    function images() { return window.rsDialogImages || null; }

    function imageFor(icon) {
        var img = images();
        if (!img) { return null; }
        if (icon === 'success') { return img.ok || null; }
        if (icon === 'error' || icon === 'warning') { return img.fail || null; }
        return null;
    }

    window.rsConfirm = function (opts) {
        if (typeof opts === 'string') { opts = { text: opts }; }
        opts = opts || {};
        var danger = !!opts.danger;
        if (!window.Swal) {
            return Promise.resolve(window.confirm(opts.text || opts.title || 'Yakin?'));
        }
        return window.Swal.fire({
            icon: danger ? 'warning' : 'question',
            titleText: opts.title || 'Yakin?',
            text: opts.text || '',
            showCancelButton: true,
            reverseButtons: true,
            focusCancel: danger,
            confirmButtonText: opts.confirmText || 'Ya, lanjutkan',
            cancelButtonText: opts.cancelText || 'Batal',
            customClass: { confirmButton: danger ? 'rs-swal-danger' : '' }
        }).then(function (r) { return r.isConfirmed; });
    };

    window.rsAlert = function (opts) {
        if (typeof opts === 'string') { opts = { text: opts }; }
        opts = opts || {};
        var icon = opts.icon || 'info';
        if (!window.Swal) {
            window.alert(opts.text || opts.title || '');
            return Promise.resolve();
        }
        var config = {
            titleText: opts.title || '',
            text: opts.text || '',
            confirmButtonText: 'Mengerti'
        };
        var url = imageFor(icon);
        if (url) {
            config.imageUrl = url;
            config.imageWidth = 120;
            config.imageHeight = 120;
            config.imageAlt = '';
        } else {
            config.icon = icon;
        }
        return window.Swal.fire(config);
    };

    window.rsToast = function (text, icon) {
        icon = icon || 'success';
        if (!window.Swal) { return Promise.resolve(); }
        var config = { titleText: text };
        var url = imageFor(icon);
        if (url) {
            config.icon = icon;
            config.iconHtml = '<img src="' + url + '" alt="" width="44" height="44">';
            config.customClass = { icon: 'rs-swal-img' };
        } else {
            config.icon = icon;
        }
        return window.Swal.mixin({
            toast: true,
            position: 'top-end',
            timer: Math.min(9000, Math.max(3500, String(text).length * 60)),
            timerProgressBar: true,
            showConfirmButton: false,
            didOpen: function (t) {
                t.addEventListener('mouseenter', window.Swal.stopTimer);
                t.addEventListener('mouseleave', window.Swal.resumeTimer);
            }
        }).fire(config);
    };

    document.addEventListener('submit', function (e) {
        var form = e.target;
        if (!form || !form.matches || !form.matches('form[data-confirm]')) { return; }
        if (form.dataset.rsConfirmed === '1') {
            delete form.dataset.rsConfirmed;
            return;
        }
        e.preventDefault();
        var submitter = e.submitter || null;
        window.rsConfirm({
            title: form.getAttribute('data-confirm-title') || undefined,
            text: form.getAttribute('data-confirm') || '',
            confirmText: form.getAttribute('data-confirm-ok') || undefined,
            danger: form.hasAttribute('data-confirm-danger')
        }).then(function (yes) {
            if (!yes) { return; }
            form.dataset.rsConfirmed = '1';
            if (typeof form.requestSubmit === 'function') {
                form.requestSubmit(submitter);
            } else {
                form.submit();
            }
        });
    });
}());
