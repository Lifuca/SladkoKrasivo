/* =====================================================
   combo-modal.js — FULL (UI flow like donor)
   - open modal
   - choose in category -> auto return to main view
   - replace CTA row with picked product row (img/name/price + replace/remove)
   - totals: base + picked, discount 2.5% per picked category
   - add-to-cart adds base + picked via WP AJAX (lr_combo_add_to_cart),
     refresh fragments, close modal
   ===================================================== */

(function () {
  function qs(sel, root = document) { return root.querySelector(sel); }
  function qsa(sel, root = document) { return Array.from(root.querySelectorAll(sel)); }
  function getModal() { return qs('[data-lr-combo-modal]'); }

  const fmtRub = (n) => {
    const v = Math.round(Number(n) || 0);
    try { return new Intl.NumberFormat('ru-RU').format(v); }
    catch (e) { return String(v); }
  };

  function showDefaultLeft(modal) {
    const leftDefault = qs('[data-lr-combo-left-default]', modal);
    const pickWrap = qs('[data-lr-combo-pick]', modal);
    const panels = qsa('[data-lr-combo-panel]', modal);

    if (pickWrap) pickWrap.hidden = true;
    if (leftDefault) leftDefault.hidden = false;
    panels.forEach(p => p.hidden = true);
  }

  function showPicker(modal, key) {
    const leftDefault = qs('[data-lr-combo-left-default]', modal);
    const pickWrap = qs('[data-lr-combo-pick]', modal);
    const pickTitle = qs('[data-lr-combo-pick-title]', modal);
    const panels = qsa('[data-lr-combo-panel]', modal);

    if (!pickWrap || !leftDefault) return;

    leftDefault.hidden = true;
    pickWrap.hidden = false;

    panels.forEach(p => {
      p.hidden = p.getAttribute('data-lr-combo-panel') !== key;
    });

    if (pickTitle) {
      pickTitle.textContent =
        key === 'flowers' ? 'Выберите букет' :
        key === 'berry' ? 'Выберите клубнику' :
        key === 'gifts' ? 'Выберите подарок' : 'Выберите';
    }
  }

  function closeModal(modal) {
    modal.hidden = true;
    document.body.classList.remove('lr-combo-open');
    showDefaultLeft(modal);
  }

  function openModal(modal) {
    modal.hidden = false;
    document.body.classList.add('lr-combo-open');
    showDefaultLeft(modal);
    recalc(modal);
  }

  // ---------- pick storage (source of truth = hidden inputs) ----------
  function getPickInput(modal, key) {
    return qs(`[data-lr-combo-picked="${key}"]`, modal);
  }

  function setPicked(modal, key, data) {
    // data: {id, price, name, img, priceHtml}
    const inp = getPickInput(modal, key);
    if (inp) inp.value = data && data.id ? String(data.id) : '';

    const slot = qs(`[data-lr-slot="${key}"]`, modal);
    if (!slot) return;

    const def = qs('[data-lr-slot-default]', slot);
    const picked = qs('[data-lr-slot-picked]', slot);

    if (!data || !data.id) {
      if (picked) picked.hidden = true;
      if (def) def.hidden = false;
      if (picked) {
        const im = qs('[data-lr-picked-img]', picked);
        const nm = qs('[data-lr-picked-name]', picked);
        const pr = qs('[data-lr-picked-price]', picked);
        if (im) { im.removeAttribute('src'); im.alt = ''; }
        if (nm) nm.textContent = '';
        if (pr) pr.innerHTML = '';
      }
      return;
    }

    if (def) def.hidden = true;
    if (picked) picked.hidden = false;

    const im = qs('[data-lr-picked-img]', picked);
    const nm = qs('[data-lr-picked-name]', picked);
    const pr = qs('[data-lr-picked-price]', picked);

    if (im) { im.src = data.img || ''; im.alt = data.name || ''; }
    if (nm) nm.textContent = data.name || '';
    if (pr) pr.innerHTML = data.priceHtml || (fmtRub(data.price) + ' ₽');

    // cache price for totals
    picked.dataset.price = String(Number(data.price || 0));
  }

  function getPickedIds(modal) {
    return ['flowers', 'berry', 'gifts']
      .map(k => (getPickInput(modal, k)?.value || '').trim())
      .filter(Boolean);
  }

  function getPickedSum(modal) {
    let sum = 0;
    ['flowers', 'berry', 'gifts'].forEach(k => {
      const slot = qs(`[data-lr-slot="${k}"] [data-lr-slot-picked]`, modal);
      if (!slot || slot.hidden) return;
      const p = parseFloat(slot.dataset.price || '0') || 0;
      sum += p;
    });
    return sum;
  }

  function pickedCount(modal) {
    return getPickedIds(modal).length;
  }

  // ---------- totals ----------
  function recalc(modal) {
    const basePrice = parseFloat(modal.getAttribute('data-base-price') || '0') || 0;
    const cnt = pickedCount(modal);
    const pct = cnt * 2.5;

    const sum = basePrice + getPickedSum(modal);
    const discount = sum * (pct / 100);
    const total = sum - discount;

    const elPct = qs('[data-lr-combo-total-discount]', modal);
    const elAmt = qs('[data-lr-combo-discount-amount]', modal);
    const elTot = qs('[data-lr-combo-total]', modal);

    if (elPct) elPct.textContent = pct ? String(pct).replace('.0', '') : '0';
    if (elAmt) elAmt.textContent = fmtRub(discount);
    if (elTot) elTot.textContent = fmtRub(total);
  }

  // ---------- Woo fragments ----------
  function applyFragments(data) {
    if (!data) return;
    const fragments = data.fragments || data.data?.fragments;
    if (!fragments) return;

    Object.keys(fragments).forEach((key) => {
      const el = document.querySelector(key);
      if (el) el.outerHTML = fragments[key];
    });
  }

  async function refreshFragments() {
    const ajaxUrl =
      (window.wc_add_to_cart_params && window.wc_add_to_cart_params.wc_ajax_url)
        ? window.wc_add_to_cart_params.wc_ajax_url.replace('%%endpoint%%', 'get_refreshed_fragments')
        : '/?wc-ajax=get_refreshed_fragments';

    const res = await fetch(ajaxUrl, { method: 'POST', credentials: 'same-origin' });
    return await res.json().catch(() => null);
  }

  // ---------- NEW: Combo add-to-cart via WP AJAX ----------
  async function comboAddToCartAjax(modal) {
    const baseId = (modal.getAttribute('data-base-product-id') || '').trim();
    if (!baseId) return null;

    const picked = getPickedIds(modal);
    const cnt = picked.length;
    const pct = cnt * 2.5;

    const ajaxUrl = modal.getAttribute('data-ajax-url') || (window.ajaxurl || '/wp-admin/admin-ajax.php');
    const nonce = modal.getAttribute('data-nonce') || '';

    const body = new URLSearchParams();
    body.set('action', 'lr_combo_add_to_cart');
    body.set('nonce', nonce);
    body.set('base_id', baseId);
    body.set('pct', String(pct));
    picked.forEach((id) => body.append('picked_ids[]', String(id)));

    const res = await fetch(ajaxUrl, {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' },
      credentials: 'same-origin',
      body: body.toString()
    });

    return await res.json().catch(() => null);
  }

  // ESC close
  document.addEventListener('keydown', (e) => {
    if (e.key !== 'Escape') return;
    const modal = getModal();
    if (!modal || modal.hidden) return;
    closeModal(modal);
  });

  // ---------- Delegated clicks ----------
  document.addEventListener('click', async (e) => {
    const modal = getModal();

    // open MODAL (ВАЖНО: это твой рабочий селектор)
    const opener = e.target.closest('[data-lr-combo]');
    if (opener) {
      e.preventDefault();
      if (modal) openModal(modal);
      return;
    }

    if (!modal || modal.hidden) return;

    // close
    if (e.target.closest('[data-lr-combo-close]')) {
      e.preventDefault();
      closeModal(modal);
      return;
    }

    // open picker
    const pickOpen = e.target.closest('[data-lr-combo-open]');
    if (pickOpen) {
      e.preventDefault();
      const key = pickOpen.getAttribute('data-lr-combo-open');
      if (key) showPicker(modal, key);
      return;
    }

    // replace from picked row
    const repl = e.target.closest('[data-lr-picked-replace]');
    if (repl) {
      e.preventDefault();
      const key = repl.getAttribute('data-lr-picked-replace');
      if (key) showPicker(modal, key);
      return;
    }

    // remove picked
    const rm = e.target.closest('[data-lr-picked-remove]');
    if (rm) {
      e.preventDefault();
      const key = rm.getAttribute('data-lr-picked-remove');
      if (key) {
        setPicked(modal, key, null);
        recalc(modal);
      }
      return;
    }

    // back
    if (e.target.closest('[data-lr-combo-back]')) {
      e.preventDefault();
      showDefaultLeft(modal);
      return;
    }

    // select product -> set picked -> return to main view
    const sel = e.target.closest('[data-lr-combo-select]');
    if (sel) {
      e.preventDefault();

      const key = sel.getAttribute('data-lr-combo-select');  // flowers|berry|gifts
      const pid = sel.getAttribute('data-product-id');
      const priceNum = parseFloat(sel.getAttribute('data-price') || '0') || 0;

      if (!key || !pid) return;

      const card = sel.closest('[data-lr-combo-card]') || sel.closest('.lr-combo-card');
      const name = qs('.lr-combo-card__name', card)?.textContent?.trim() || '';
      const img = qs('.lr-combo-card__img img', card)?.getAttribute('src') || '';
      const priceHtml = qs('.lr-combo-card__price', card)?.innerHTML || (fmtRub(priceNum) + ' ₽');

      setPicked(modal, key, {
        id: pid,
        price: priceNum,
        name,
        img,
        priceHtml
      });

      showDefaultLeft(modal);
      recalc(modal);
      return;
    }

    // ADD TO CART (NEW logic)
    const addBtn = e.target.closest('[data-lr-combo-addtocart]');
    if (addBtn) {
      e.preventDefault();
      addBtn.disabled = true;

      try {
        const json = await comboAddToCartAjax(modal);

        // если не настроен nonce/endpoint — json может быть null
        // но модалка при этом НЕ должна ломаться (просто ничего не добавится)
        if (json && json.success === false && json.data && json.data.redirect) {
          window.location.href = json.data.redirect;
          return;
        }

        // обновляем мини-корзину / фрагменты Woo
        const fr = await refreshFragments();
        applyFragments(fr);

        if (window.jQuery) {
          window.jQuery(document.body).trigger('wc_fragment_refresh');
        }

        closeModal(modal);

      } catch (err) {
        // console.error(err);
      } finally {
        addBtn.disabled = false;
      }

      return;
    }
  });

})();