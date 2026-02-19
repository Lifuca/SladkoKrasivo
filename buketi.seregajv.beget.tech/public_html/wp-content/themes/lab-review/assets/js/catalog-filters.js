document.addEventListener('DOMContentLoaded', () => {
  const root = document.getElementById('lr-filters');
  if (!root) return;

  // Accordion:
  // - price section (lr-filter--price) stays OPEN by default
  // - all others CLOSED by default
  root.querySelectorAll('.lr-filter').forEach((sec) => {
    const head = sec.querySelector('.lr-filter__head');
    const body = sec.querySelector('.lr-filter__body');
    if (!head || !body) return;

    const isPrice = sec.classList.contains('lr-filter--price');

    if (isPrice) {
      sec.classList.add('is-open');
      body.hidden = false;
      head.setAttribute('aria-expanded', 'true');
    } else {
      sec.classList.remove('is-open');
      body.hidden = true;
      head.setAttribute('aria-expanded', 'false');
    }

    head.addEventListener('click', () => {
      const willOpen = !sec.classList.contains('is-open');
      sec.classList.toggle('is-open', willOpen);
      body.hidden = !willOpen;
      head.setAttribute('aria-expanded', willOpen ? 'true' : 'false');
    });
  });

  // Price sync: visible -> hidden inputs (if Woo put them)
  const minVisible = root.querySelector('[data-role="min-visible"]');
  const maxVisible = root.querySelector('[data-role="max-visible"]');

  // Hidden inputs are usually inside widget form
  const minHidden = root.querySelector('input[name="min_price"]');
  const maxHidden = root.querySelector('input[name="max_price"]');

  const toIntOrEmpty = (v) => {
    const n = parseInt(String(v ?? '').replace(/[^\d]/g, ''), 10);
    return Number.isFinite(n) ? String(n) : '';
  };

  const syncToHidden = () => {
    const min = toIntOrEmpty(minVisible?.value);
    const max = toIntOrEmpty(maxVisible?.value);

    if (minHidden) minHidden.value = min;
    if (maxHidden) maxHidden.value = max;

    return { min, max };
  };

  // Populate visible from URL (more reliable than reading hidden)
  const urlNow = new URL(window.location.href);
  const minFromUrl = urlNow.searchParams.get('min_price');
  const maxFromUrl = urlNow.searchParams.get('max_price');
  if (minVisible && minFromUrl) minVisible.value = minFromUrl;
  if (maxVisible && maxFromUrl) maxVisible.value = maxFromUrl;

  minVisible?.addEventListener('input', syncToHidden);
  maxVisible?.addEventListener('input', syncToHidden);

  const apply = () => {
    const { min, max } = syncToHidden();
    const url = new URL(window.location.href);

    if (min) url.searchParams.set('min_price', min);
    else url.searchParams.delete('min_price');

    if (max) url.searchParams.set('max_price', max);
    else url.searchParams.delete('max_price');

    window.location.href = url.toString();
  };

  // Enter = apply
  minVisible?.addEventListener('keydown', (e) => { if (e.key === 'Enter') apply(); });
  maxVisible?.addEventListener('keydown', (e) => { if (e.key === 'Enter') apply(); });

  // "Показать" applies price too
  root.querySelector('.lr-filter-apply')?.addEventListener('click', apply);
});
