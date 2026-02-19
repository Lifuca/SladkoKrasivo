(function () {
  const sliders = document.querySelectorAll('[data-lr-card-slider]');
  if (!sliders.length) return;

  sliders.forEach((root) => {
    const track = root.querySelector('.lr-card__track');
    const dotsWrap = root.querySelector('.lr-card__dots');
    const linkEl = root.querySelector('.lr-card__link');
    const href = linkEl ? linkEl.getAttribute('href') : null;

    if (!track) return;

    const dots = dotsWrap ? Array.from(dotsWrap.querySelectorAll('[data-dot]')) : [];

    let isDown = false;
    let startX = 0;
    let startScrollLeft = 0;
    let dragged = false;

    const setActiveDot = (idx) => {
      if (!dots.length) return;
      dots.forEach((d) => d.classList.toggle('is-active', Number(d.dataset.dot) === idx));
    };

    const getIndex = () => {
      const w = track.clientWidth || 1;
      const idx = Math.round(track.scrollLeft / w);
      if (!dots.length) return idx;
      return Math.max(0, Math.min(dots.length - 1, idx));
    };

    const snapTo = (idx, behavior = 'smooth') => {
      const w = track.clientWidth || 1;
      track.scrollTo({ left: idx * w, behavior });
      setActiveDot(idx);
    };

    // dots click
    dots.forEach((d) => {
      d.addEventListener('click', (e) => {
        e.preventDefault();
        e.stopPropagation();
        snapTo(Number(d.dataset.dot));
      });
    });

    // scroll -> active dot
    let raf = 0;
    track.addEventListener(
      'scroll',
      () => {
        if (!dots.length) return;
        cancelAnimationFrame(raf);
        raf = requestAnimationFrame(() => setActiveDot(getIndex()));
      },
      { passive: true }
    );

    // drag/swipe pointer
    track.addEventListener('pointerdown', (e) => {
      isDown = true;
      dragged = false;
      startX = e.clientX;
      startScrollLeft = track.scrollLeft;
      track.setPointerCapture(e.pointerId);
    });

    track.addEventListener('pointermove', (e) => {
      if (!isDown) return;
      const dx = e.clientX - startX;
      if (Math.abs(dx) > 6) dragged = true;
      track.scrollLeft = startScrollLeft - dx;
    });

    const endDrag = (e) => {
      if (!isDown) return;
      isDown = false;

      // snap to nearest slide
      if (dots.length) snapTo(getIndex());
      else track.scrollTo({ left: getIndex() * (track.clientWidth || 1), behavior: 'smooth' });

      try {
        track.releasePointerCapture(e.pointerId);
      } catch (_) {}
    };

    track.addEventListener('pointerup', endDrag);
    track.addEventListener('pointercancel', endDrag);

    // click on image area -> open product (but not after drag)
    root.addEventListener('click', (e) => {
      if (!href) return;
      if (e.target.closest('.lr-card__dots') || e.target.closest('button')) return;
      if (dragged) return;
      window.location.href = href;
    });

    // on resize keep dots in sync
    window.addEventListener('resize', () => {
      if (!dots.length) return;
      snapTo(getIndex(), 'auto');
    });
  });
})();


(function () {
  const sliders = document.querySelectorAll('[data-lr-card-slider]');
  if (!sliders.length) return;

  sliders.forEach((root) => {
    const track = root.querySelector('.lr-card__track');
    const dotsWrap = root.querySelector('.lr-card__dots');
    const linkEl = root.querySelector('.lr-card__link');
    const href = linkEl ? linkEl.getAttribute('href') : null;

    if (!track) return;

    const dots = dotsWrap ? Array.from(dotsWrap.querySelectorAll('[data-dot]')) : [];

    let isDown = false;
    let startX = 0;
    let startScrollLeft = 0;
    let dragged = false;

    const setActiveDot = (idx) => {
      if (!dots.length) return;
      dots.forEach((d) => d.classList.toggle('is-active', Number(d.dataset.dot) === idx));
    };

    const getIndex = () => {
      const w = track.clientWidth || 1;
      const idx = Math.round(track.scrollLeft / w);
      if (!dots.length) return idx;
      return Math.max(0, Math.min(dots.length - 1, idx));
    };

    const snapTo = (idx, behavior = 'smooth') => {
      const w = track.clientWidth || 1;
      track.scrollTo({ left: idx * w, behavior });
      setActiveDot(idx);
    };

    // dots click
    dots.forEach((d) => {
      d.addEventListener('click', (e) => {
        e.preventDefault();
        e.stopPropagation();
        snapTo(Number(d.dataset.dot));
      });
    });

    // scroll -> active dot
    let raf = 0;
    track.addEventListener('scroll', () => {
      if (!dots.length) return;
      cancelAnimationFrame(raf);
      raf = requestAnimationFrame(() => setActiveDot(getIndex()));
    }, { passive: true });

    // pointer drag
    track.addEventListener('pointerdown', (e) => {
      isDown = true;
      dragged = false;
      startX = e.clientX;
      startScrollLeft = track.scrollLeft;
      track.setPointerCapture(e.pointerId);
    });

    track.addEventListener('pointermove', (e) => {
      if (!isDown) return;

      const dx = e.clientX - startX;

      // threshold: после 4px считаем это drag
      if (Math.abs(dx) > 4) {
        dragged = true;
        e.preventDefault();
        e.stopPropagation();
      }

      track.scrollLeft = startScrollLeft - dx;
    });

    const endDrag = (e) => {
      if (!isDown) return;
      isDown = false;

      // snap
      if (dots.length) snapTo(getIndex());
      else {
        const w = track.clientWidth || 1;
        track.scrollTo({ left: Math.round(track.scrollLeft / w) * w, behavior: 'smooth' });
      }

      try { track.releasePointerCapture(e.pointerId); } catch (_) {}
    };

    track.addEventListener('pointerup', endDrag);
    track.addEventListener('pointercancel', endDrag);

    // click on image area -> open product (only if not dragged)
    root.addEventListener('click', (e) => {
      if (!href) return;
      if (e.target.closest('.lr-card__dots') || e.target.closest('button')) return;
      if (dragged) return;
      window.location.href = href;
    });

    // keep snap on resize
    window.addEventListener('resize', () => {
      if (!dots.length) return;
      snapTo(getIndex(), 'auto');
    });
  });
})();
