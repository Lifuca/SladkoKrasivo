(function () {
  const html = document.documentElement;

  const calcMenuTop = () => {
    const ticker = document.querySelector('.lr-header__ticker');
    const mid = document.querySelector('.lr-header__mid');

    // если тикер скрыт (sticky), его height будет 0
    const top = (ticker?.offsetHeight || 0) + (mid?.offsetHeight || 0);

    html.style.setProperty('--lr-menu-top', `${top}px`);
  };

  // ===== Sticky header toggler =====
  const header = document.getElementById('lr-header');

  const onScroll = () => {
    if (!header) return;

    if (window.scrollY > 10) {
      document.body.classList.add('lr-sticky');
      header.classList.add('is-sticky');
    } else {
      document.body.classList.remove('lr-sticky');
      header.classList.remove('is-sticky');
    }

    // всегда держим корректный top для меню
    calcMenuTop();
  };

  window.addEventListener('scroll', onScroll, { passive: true });
  window.addEventListener('resize', () => {
    calcMenuTop();
  });

  // ===== Fullscreen Menu =====
  const menu = document.getElementById('lr-menu');
  const burger = document.querySelector('.lr-burger');

  const openMenu = () => {
    if (!menu || !burger) return;

    calcMenuTop();

    menu.classList.add('is-open');
    menu.setAttribute('aria-hidden', 'false');

    burger.setAttribute('aria-expanded', 'true');
    burger.classList.add('is-active');
    burger.setAttribute('aria-label', 'Закрыть меню');

    document.body.classList.add('lr-menu-open');
  };

  const closeMenu = () => {
    if (!menu || !burger) return;

    menu.classList.remove('is-open');
    menu.setAttribute('aria-hidden', 'true');

    burger.setAttribute('aria-expanded', 'false');
    burger.classList.remove('is-active');
    burger.setAttribute('aria-label', 'Открыть меню');

    document.body.classList.remove('lr-menu-open');
  };

  if (burger && menu) {
    burger.addEventListener('click', () => {
      menu.classList.contains('is-open') ? closeMenu() : openMenu();
    });

    // клик по backdrop закрывает
    menu.addEventListener('click', (e) => {
      if (e.target && e.target.hasAttribute('data-lr-menu-close')) closeMenu();
    });
  }

  // ===== Search panel =====
  const searchPanel = document.getElementById('lr-search');
  const searchOpen = document.querySelector('[data-lr-search-open]');

  const openSearch = () => {
    if (!searchPanel || !searchOpen) return;
    if (menu && menu.classList.contains('is-open')) closeMenu();

    searchPanel.classList.add('is-open');
    searchPanel.setAttribute('aria-hidden', 'false');
    searchOpen.setAttribute('aria-expanded', 'true');

    html.style.overflow = 'hidden';
    const input = searchPanel.querySelector('input[type="search"]');
    if (input) setTimeout(() => input.focus(), 60);
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

  // ===== ESC =====
  document.addEventListener('keydown', (e) => {
    if (e.key !== 'Escape') return;
    closeMenu();
    closeSearch();
  });

  // ===== init =====
  onScroll();
  calcMenuTop();
})();
