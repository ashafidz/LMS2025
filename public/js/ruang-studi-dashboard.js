// Ruang Studi dashboard shell: small-screen menu and the account menu.
(function () {
  var body = document.body;
  var button = document.querySelector('.rs-dash-menu-btn');
  var side = document.getElementById('rs-dash-side');

  if (button && side) {
    var setOpen = function (open) {
      body.classList.toggle('is-menu-open', open);
      button.setAttribute('aria-expanded', open ? 'true' : 'false');
    };
    button.addEventListener('click', function (event) {
      event.stopPropagation();
      setOpen(!body.classList.contains('is-menu-open'));
    });
    document.addEventListener('click', function (event) {
      if (body.classList.contains('is-menu-open') && !side.contains(event.target)) {
        setOpen(false);
      }
    });
    document.addEventListener('keydown', function (event) {
      if (event.key === 'Escape' && body.classList.contains('is-menu-open')) {
        setOpen(false);
        button.focus();
      }
    });
  }

  // Close the account menu (<details>) when clicking elsewhere.
  var user = document.querySelector('.rs-dash-user');
  if (user) {
    document.addEventListener('click', function (event) {
      if (user.open && !user.contains(event.target)) {
        user.open = false;
      }
    });
  }

  // Auto-initialize searchable Select2 dropdowns with search bar
  if (typeof jQuery !== 'undefined') {
    var initSearchableDropdowns = function (context) {
      if (!jQuery.fn || !jQuery.fn.select2) {
        return;
      }

      var root = context ? jQuery(context) : jQuery(document);
      root.find('select.form-control, select.rs-select2, select[data-searchable="true"]').each(function () {
        var $select = jQuery(this);

        if ($select.hasClass('no-select2') || $select.attr('data-no-select2') !== undefined || $select.closest('.drag-and-drop-container').length) {
          return;
        }

        if ($select.hasClass('select2-hidden-accessible')) {
          return;
        }

        var modal = $select.closest('.modal');
        var config = {
          language: 'id',
          width: '100%'
        };

        if (modal.length) {
          config.dropdownParent = modal;
        }

        $select.select2(config);

        // Forward select2 selection as native change event so inline onchange attributes fire reliably
        $select.on('select2:select select2:unselect select2:clear', function () {
          this.dispatchEvent(new Event('change', { bubbles: true }));
        });
      });
    };

    jQuery(function () {
      initSearchableDropdowns();
    });

    jQuery(document).on('shown.bs.modal', function () {
      initSearchableDropdowns(this);
    });

    // Automatically focus search input when select2 dropdown opens
    jQuery(document).on('select2:open', function () {
      var searchField = document.querySelector('.select2-container--open .select2-search__field');
      if (searchField) {
        searchField.focus();
      }
    });
  }
})();
