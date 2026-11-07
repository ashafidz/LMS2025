/*
 * reCAPTCHA v3 for the auth forms (login, register, forgot password).
 * On submit a fresh token is requested, written to the hidden "recaptcha-response" input, and the form is sent again.
 * The token is cleared after each send, so a second try (after an error) asks for a new one (v3 tokens last 2 minutes).
 * If Google's script is missing or fails, a short message is shown inside the form (no native dialog).
 */
(function () {
    'use strict';

    function status(form, text) {
        var el = form.querySelector('[data-recaptcha-status]');
        if (!el) { return; }
        el.textContent = text || '';
        el.hidden = !text;
    }

    function setPending(form, on) {
        var buttons = form.querySelectorAll('button[type=submit]');
        Array.prototype.forEach.call(buttons, function (b) { b.disabled = on; });
        if (on) { form.setAttribute('aria-busy', 'true'); } else { form.removeAttribute('aria-busy'); }
    }

    function fail(form) {
        delete form.dataset.recaptchaPending;
        setPending(form, false);
        status(form, 'Pemeriksaan keamanan belum siap. Muat ulang halaman lalu coba lagi.');
    }

    Array.prototype.forEach.call(document.querySelectorAll('form[data-recaptcha-site-key][data-recaptcha-action]'), function (form) {
        form.addEventListener('submit', function (event) {
            if (form.dataset.recaptchaReady === '1') {
                // Second pass: the token is in place, let this submit go through, then forget the token.
                delete form.dataset.recaptchaReady;
                // Lock the form while the page loads (register can be slow): a second click must not send it again.
                form.dataset.recaptchaSent = '1';
                setTimeout(function () { setPending(form, true); }, 0);
                return;
            }
            event.preventDefault();
            if (form.dataset.recaptchaSent === '1') { return; }
            if (form.dataset.recaptchaPending === '1') { return; }
            status(form, '');
            if (typeof grecaptcha === 'undefined' || !grecaptcha.ready) { fail(form); return; }

            form.dataset.recaptchaPending = '1';
            setPending(form, true);
            var submitter = event.submitter;
            var siteKey = form.getAttribute('data-recaptcha-site-key');
            var action = form.getAttribute('data-recaptcha-action');

            // With a wrong key Google never answers; give up after a while so the form does not stay stuck.
            var timer = setTimeout(function () { if (form.dataset.recaptchaPending === '1') { fail(form); } }, 8000);

            grecaptcha.ready(function () {
                grecaptcha.execute(siteKey, { action: action }).then(function (token) {
                    clearTimeout(timer);
                    if (form.dataset.recaptchaPending !== '1') { return; }
                    var input = form.querySelector('input[name="recaptcha-response"]');
                    if (!input) { fail(form); return; }
                    input.value = token;
                    form.dataset.recaptchaReady = '1';
                    delete form.dataset.recaptchaPending;
                    // Buttons must be enabled for the browser to treat them as submitters; they are locked again right after the send.
                    setPending(form, false);
                    // Deferred: if the token arrives while the first submit event is still being dispatched, requestSubmit() would be ignored.
                    setTimeout(function () {
                        if (form.requestSubmit) {
                            try { form.requestSubmit(submitter && submitter.form === form ? submitter : undefined); } catch (e) { form.submit(); }
                        } else {
                            form.submit();
                        }
                    }, 0);
                }, function () { clearTimeout(timer); fail(form); });
            });
        });

        // Back/forward cache: a restored page must not keep a stale pending state.
        window.addEventListener('pageshow', function (e) {
            if (e.persisted) {
                delete form.dataset.recaptchaPending;
                delete form.dataset.recaptchaReady;
                delete form.dataset.recaptchaSent;
                setPending(form, false);
            }
        });
    });
})();
