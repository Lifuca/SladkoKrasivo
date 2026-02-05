(function () {
  const html = document.documentElement;

  // ===== Offcanvas menu =====
  const offcanvas = document.getElementById('lr-offcanvas');
  const burger = document.querySelector('.lr-burger');

  const openMenu = () => {
    if (!offcanvas || !burger) return;
    offcanvas.classList.add('is-open');
    offcanvas.setAttribute('aria-hidden', 'false');
    burger.setAttribute('aria-expanded', 'true');
    html.style.overflow = 'hidden';
  };

  const closeMenu = () => {
    if (!offcanvas || !burger) return;
    offcanvas.classList.remove('is-open');
    offcanvas.setAttribute('aria-hidden', 'true');
    burger.setAttribute('aria-expanded', 'false');
    html.style.overflow = '';
  };

  if (burger && offcanvas) {
    burger.addEventListener('click', openMenu);
    offcanvas.addEventListener('click', (e) => {
      if (e.target && e.target.hasAttribute('data-lr-close')) closeMenu();
    });
  }

  // ===== Search panel =====
  const searchPanel = document.getElementById('lr-search');
  const searchOpen = document.querySelector('[data-lr-search-open]');

  const openSearch = () => {
    if (!searchPanel || !searchOpen) return;
    searchPanel.classList.add('is-open');
    searchPanel.setAttribute('aria-hidden', 'false');
    searchOpen.setAttribute('aria-expanded', 'true');
    html.style.overflow = 'hidden';

    const input = searchPanel.querySelector('input[type="search"]');
    if (input) setTimeout(() => input.focus(), 50);
  };

  const closeSearch = () => {
    if (!searchPanel || !searchOpen) return;
    searchPanel.classList.remove('is-open');
    searchPanel.setAttribute('aria-hidden', 'true');
    searchOpen.setAttribute('aria-expanded', 'false');
    html.style.overflow = '';
  };

  if (searchOpen && searchPanel) {
    searchOpen.addEventListener('click', openSearch);
    searchPanel.addEventListener('click', (e) => {
      if (e.target && e.target.hasAttribute('data-lr-search-close')) closeSearch();
    });
  }

  // ===== ESC closes all =====
  document.addEventListener('keydown', (e) => {
    if (e.key !== 'Escape') return;
    closeMenu();
    closeSearch();
  });
})();
