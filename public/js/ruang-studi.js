// Ruang Studi public site: mobile menu toggle and catalog filter collapse.
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
})();
