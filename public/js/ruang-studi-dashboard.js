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
})();
