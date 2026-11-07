// Ruang Studi dashboard shell: sidebar show/hide (slide-in on small screens, hide/show on large screens) and the account menu.
(function () {
  var body = document.body;
  var button = document.querySelector('.rs-dash-menu-btn');
  var side = document.getElementById('rs-dash-side');

  var closeBtn = document.querySelector('.rs-dash-side-close');

  if (button && side) {
    var wide = window.matchMedia('(min-width: 992px)');
    var setOpen = function (open) {
      body.classList.toggle('is-menu-open', open);
      button.setAttribute('aria-expanded', open ? 'true' : 'false');
    };
    // Large screens: the sidebar is part of the page. The << button in the sidebar hides it, the Menu button in the
    // top bar (only shown while it is hidden) brings it back, and the choice is remembered.
    var setSideClosed = function (closed) {
      body.classList.toggle('is-side-closed', closed);
      button.setAttribute('aria-expanded', closed ? 'false' : 'true');
      if (closeBtn) {
        closeBtn.setAttribute('aria-expanded', closed ? 'false' : 'true');
      }
      try {
        localStorage.setItem('rs-dash-side', closed ? 'closed' : 'open');
      } catch (e) {}
    };
    var syncExpanded = function () {
      var expanded = wide.matches ? !body.classList.contains('is-side-closed') : body.classList.contains('is-menu-open');
      button.setAttribute('aria-expanded', expanded ? 'true' : 'false');
      if (closeBtn) {
        closeBtn.setAttribute('aria-expanded', expanded ? 'true' : 'false');
      }
    };
    syncExpanded();
    if (wide.addEventListener) {
      wide.addEventListener('change', syncExpanded);
    }
    button.addEventListener('click', function (event) {
      event.stopPropagation();
      if (wide.matches) {
        setSideClosed(false);
        if (closeBtn) {
          closeBtn.focus();
        }
        return;
      }
      setOpen(!body.classList.contains('is-menu-open'));
    });
    if (closeBtn) {
      closeBtn.addEventListener('click', function (event) {
        event.stopPropagation();
        if (wide.matches) {
          setSideClosed(true);
        } else {
          setOpen(false);
        }
        button.focus();
      });
    }
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

// Sticky top bar: a soft shadow once the page has scrolled, so the bar reads as a layer above the content.
(function () {
    var bar = document.querySelector('.rs-dash-top');
    if (!bar) {
        return;
    }
    var update = function () {
        bar.classList.toggle('is-scrolled', window.scrollY > 4);
    };
    window.addEventListener('scroll', update, { passive: true });
    update();
})();

// Lists on phones: label every cell of a simple table with its column header, so CSS can show each row as a card
// (see ".rs-table-stack" in ruang-studi-dashboard.css). Skipped: tables with merged header cells, DataTables,
// ".rs-table-cards" (labelled in Blade) and tables marked ".rs-table-keep".
(function () {
    var NUM = /^(#|no\.?|nomor)$/i;
    var ACTIONS = /^(aksi|action|actions|opsi|tindakan)$/i;
    var TITLE = /(nama|judul|kursus|topik|pertanyaan|soal|title|name|siswa|pengguna|kupon|lencana|badge|kategori|pesan)/i;

    var onlyControls = function (td) {
        var text = td.textContent.replace(/\s+/g, '');
        return td.querySelector('a, button, form') !== null && text.length <= 40;
    };

    document.querySelectorAll('.rs-dash-content table').forEach(function (table) {
        if (table.classList.contains('rs-table-cards') || table.classList.contains('rs-table-keep')
            || table.closest('.dataTables_wrapper') || !table.tHead || table.tHead.rows.length !== 1
            || table.tHead.querySelector('[colspan], [rowspan]') || !table.tBodies.length) {
            return;
        }
        var headers = Array.prototype.map.call(table.tHead.rows[0].cells, function (th) {
            return th.textContent.replace(/\s+/g, ' ').trim();
        });
        if (headers.length < 2) {
            return;
        }
        var titleIndex = headers.findIndex(function (h) { return !NUM.test(h) && TITLE.test(h); });
        var lastIsActions = headers.length > 1 && (ACTIONS.test(headers[headers.length - 1]) || headers[headers.length - 1] === '');

        Array.prototype.forEach.call(table.tBodies, function (tbody) {
            Array.prototype.forEach.call(tbody.rows, function (tr) {
                Array.prototype.forEach.call(tr.cells, function (td, i) {
                    if (td.colSpan > 1) {
                        td.classList.add('rs-td-full');
                        return;
                    }
                    var header = headers[i] || '';
                    if (!td.hasAttribute('data-label')) {
                        td.setAttribute('data-label', header);
                    }
                    if (NUM.test(header)) {
                        td.classList.add('rs-td-num');
                    } else if (ACTIONS.test(header) || (i === headers.length - 1 && lastIsActions && onlyControls(td))) {
                        td.classList.add('rs-td-actions');
                    } else if (i === titleIndex) {
                        td.classList.add('rs-td-title');
                    }
                });
            });
        });
        table.classList.add('rs-table-stack');
    });
})();
