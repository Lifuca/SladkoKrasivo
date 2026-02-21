(function () {
  function initSingleProductGallery() {
    const root = document.querySelector('[data-lr-sp]');
    if (!root) return;

    const track = root.querySelector('[data-lr-sp-track]');
    if (!track) return;

    const slides = Array.from(root.querySelectorAll('[data-lr-sp-slide]'));
    const thumbs = Array.from(root.querySelectorAll('[data-lr-sp-thumb]'));
    const prev = root.querySelector('[data-lr-sp-prev]');
    const next = root.querySelector('[data-lr-sp-next]');

    const clamp = (n, a, b) => Math.max(a, Math.min(b, n));

    const getIndex = () => {
      const w = track.clientWidth || 1;
      const idx = Math.round(track.scrollLeft / w);
      return clamp(idx, 0, Math.max(0, slides.length - 1));
    };

    const setActive = (idx) => {
      if (!thumbs.length) return;
      thumbs.forEach((t) => t.classList.toggle('is-active', Number(t.dataset.idx) === idx));
    };

    const go = (idx, behavior = 'smooth') => {
      const w = track.clientWidth || 1;
      const safe = clamp(idx, 0, Math.max(0, slides.length - 1));
      track.scrollTo({ left: safe * w, behavior });
      setActive(safe);
    };

    prev?.addEventListener('click', () => go(getIndex() - 1));
    next?.addEventListener('click', () => go(getIndex() + 1));

    thumbs.forEach((t) => t.addEventListener('click', () => go(Number(t.dataset.idx))));

    let raf = 0;
    track.addEventListener(
      'scroll',
      () => {
        cancelAnimationFrame(raf);
        raf = requestAnimationFrame(() => setActive(getIndex()));
      },
      { passive: true }
    );

    // drag/swipe
    let isDown = false;
    let startX = 0;
    let startScrollLeft = 0;

    track.style.cursor = 'grab';

    track.addEventListener('pointerdown', (e) => {
      isDown = true;
      startX = e.clientX;
      startScrollLeft = track.scrollLeft;
      track.style.cursor = 'grabbing';
      try { track.setPointerCapture(e.pointerId); } catch (_) {}
    });

    track.addEventListener('pointermove', (e) => {
      if (!isDown) return;
      const dx = e.clientX - startX;
      track.scrollLeft = startScrollLeft - dx;
    });

    const end = (e) => {
      if (!isDown) return;
      isDown = false;
      track.style.cursor = 'grab';
      go(getIndex());
      try { track.releasePointerCapture(e.pointerId); } catch (_) {}
    };

    track.addEventListener('pointerup', end);
    track.addEventListener('pointercancel', end);

    window.addEventListener('resize', () => go(getIndex(), 'auto'));

    setActive(0);
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initSingleProductGallery);
  } else {
    initSingleProductGallery();
  }
})();
