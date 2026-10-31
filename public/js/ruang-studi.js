// Ruang Studi public site: menu, filter, dialogs, password toggle, sign-in helpers, cart coupons, contact form.
(function () {
  var header = document.querySelector('.rs-header');
  var button = document.querySelector('.rs-menu-btn');

  if (header && button) {
    button.addEventListener('click', function () {
      var open = header.classList.toggle('is-open');
      button.setAttribute('aria-expanded', open ? 'true' : 'false');
    });
  }

  // On small screens, collapse the category filter unless a category is already selected.
  var filter = document.querySelector('.rs-filter');
  if (filter && window.matchMedia('(max-width: 991.98px)').matches) {
    if (!filter.querySelector('input[type="checkbox"]:checked')) {
      filter.removeAttribute('open');
    }
  }

  // Open a native <dialog> from a button with data-rs-dialog="<dialog id>".
  document.querySelectorAll('[data-rs-dialog]').forEach(function (opener) {
    opener.addEventListener('click', function () {
      var dialog = document.getElementById(opener.getAttribute('data-rs-dialog'));
      if (dialog && typeof dialog.showModal === 'function') {
        dialog.showModal();
      }
    });
  });

  // Show or hide a password field: <button data-rs-toggle-password="<input id>">.
  document.querySelectorAll('[data-rs-toggle-password]').forEach(function (toggle) {
    toggle.addEventListener('click', function () {
      var input = document.getElementById(toggle.getAttribute('data-rs-toggle-password'));
      if (!input) {
        return;
      }
      var show = input.type === 'password';
      input.type = show ? 'text' : 'password';
      toggle.setAttribute('aria-pressed', show ? 'true' : 'false');
      toggle.setAttribute('aria-label', show ? 'Sembunyikan kata sandi' : 'Tampilkan kata sandi');
      var icon = toggle.querySelector('i');
      if (icon) {
        icon.className = show ? 'bi bi-eye-slash' : 'bi bi-eye';
      }
    });
  });

  // Register: the instructor-only fields follow the selected role.
  var instructorFields = document.getElementById('instructor-fields');
  if (instructorFields) {
    var syncRole = function () {
      var checked = document.querySelector('input[name="role"]:checked');
      var isInstructor = Boolean(checked && checked.value === 'instructor');
      instructorFields.hidden = !isInstructor;
      instructorFields.querySelectorAll('input').forEach(function (input) {
        input.required = isInstructor;
      });
    };
    document.querySelectorAll('input[name="role"]').forEach(function (radio) {
      radio.addEventListener('change', syncRole);
    });
    syncRole();
  }

  // Login: send the browser timezone with the form.
  var timezoneInput = document.getElementById('user_timezone');
  if (timezoneInput) {
    try {
      timezoneInput.value = Intl.DateTimeFormat().resolvedOptions().timeZone || '';
    } catch (e) {
      timezoneInput.value = '';
    }
  }

  // Cart: "Gunakan" on a public coupon fills the code and submits the coupon form.
  var couponForm = document.getElementById('coupon-form');
  var couponInput = document.getElementById('coupon_code');
  document.querySelectorAll('.use-coupon-btn').forEach(function (couponButton) {
    couponButton.addEventListener('click', function () {
      if (couponForm && couponInput) {
        couponInput.value = couponButton.getAttribute('data-code');
        couponForm.submit();
      }
    });
  });

  // Contact form: send with fetch and show a plain-language status.
  var contactForm = document.querySelector('form[data-rs-contact]');
  if (contactForm) {
    var statusBox = function (name) {
      return contactForm.querySelector('[data-rs-status="' + name + '"]');
    };
    var setStatus = function (name, message) {
      ['wait', 'error', 'ok'].forEach(function (key) {
        var box = statusBox(key);
        if (box) {
          box.classList.toggle('is-shown', key === name);
        }
      });
      if (name === 'error' && message && statusBox('error')) {
        statusBox('error').textContent = message;
      }
    };
    var messageFor = function (status, body) {
      if (status === 422) {
        try {
          var data = JSON.parse(body);
          var first = data.errors ? Object.values(data.errors)[0] : null;
          if (first && first[0]) {
            return first[0];
          }
        } catch (e) {
          // Not JSON: use the generic text below.
        }
        return 'Periksa kembali isian formulir.';
      }
      if (status === 419) {
        return 'Halaman sudah terlalu lama dibuka. Muat ulang halaman lalu kirim lagi.';
      }
      if (status === 429) {
        return 'Terlalu banyak percobaan. Tunggu beberapa menit lalu coba lagi.';
      }
      // ContactMessageController answers validation and spam problems as short plain text with status 200.
      if ((status === 200 || status === 403) && body && body.length < 300 && body.charAt(0) !== '{' && body.charAt(0) !== '<') {
        return body;
      }
      return 'Pesan belum terkirim. Coba lagi beberapa saat lagi.';
    };
    var send = function (token) {
      var data = new FormData(contactForm);
      if (token) {
        data.set('recaptcha-response', token);
      }
      fetch(contactForm.getAttribute('action'), {
        method: 'POST',
        body: data,
        headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
      })
        .then(function (response) {
          return response.text().then(function (body) {
            if (response.ok && body.trim() === 'OK') {
              setStatus('ok');
              contactForm.reset();
            } else {
              setStatus('error', messageFor(response.status, body));
            }
          });
        })
        .catch(function () {
          setStatus('error', 'Pesan belum terkirim. Periksa koneksi internet lalu coba lagi.');
        });
    };
    contactForm.addEventListener('submit', function (event) {
      event.preventDefault();
      setStatus('wait');
      var siteKey = contactForm.getAttribute('data-recaptcha-site-key');
      if (!siteKey) {
        send(null);
        return;
      }
      if (typeof grecaptcha === 'undefined') {
        setStatus('error', 'Pemeriksaan keamanan belum siap. Muat ulang halaman lalu coba lagi.');
        return;
      }
      grecaptcha.ready(function () {
        grecaptcha.execute(siteKey, { action: 'php_email_form_submit' }).then(send, function () {
          setStatus('error', 'Pemeriksaan keamanan gagal. Muat ulang halaman lalu coba lagi.');
        });
      });
    });
  }
})();
