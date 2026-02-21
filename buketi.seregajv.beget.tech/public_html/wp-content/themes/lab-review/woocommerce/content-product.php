<?php
/**
 * Product Card (Loop)
 * Path: /woocommerce/content-product.php
 */
if (!defined('ABSPATH')) exit;

global $product;
if (empty($product) || !$product->is_visible()) return;

$product_id = $product->get_id();
$link       = get_permalink($product_id);
$title      = get_the_title($product_id);

// бонусы из bonuses-system.php: meta _lr_bonus_points
$bonus = (int) $product->get_meta('_lr_bonus_points');

// Картинки: главная + галерея
$img_ids = [];
$main_id = (int) $product->get_image_id();
if ($main_id) $img_ids[] = $main_id;

$gallery_ids = $product->get_gallery_image_ids();
if (is_array($gallery_ids) && !empty($gallery_ids)) {
  foreach ($gallery_ids as $gid) {
    $gid = (int) $gid;
    if ($gid && $gid !== $main_id) $img_ids[] = $gid;
  }
}

if (empty($img_ids)) $img_ids = [0];

$slides_cnt = count($img_ids);
?>

<li <?php wc_product_class('lr-card', $product); ?>>

  <div class="lr-card__media lr-card__slider"
       data-lr-card-slider
       data-href="<?php echo esc_url($link); ?>"
       aria-label="<?php echo esc_attr($title); ?>">

    <div class="lr-card__track" role="group" aria-label="<?php echo esc_attr($title); ?>">
      <?php foreach ($img_ids as $i => $aid): ?>
        <div class="lr-card__slide" data-slide="<?php echo (int)$i; ?>">
          <?php
          if ($aid) {
            echo wp_get_attachment_image($aid, 'woocommerce_thumbnail', false, [
              'class'     => 'lr-card__img',
              'loading'   => $i === 0 ? 'eager' : 'lazy',
              'alt'       => esc_attr($title),
              'draggable' => 'false',
            ]);
          } else {
            echo wc_placeholder_img('woocommerce_thumbnail');
          }
          ?>
        </div>
      <?php endforeach; ?>
    </div>

    <?php if ($slides_cnt > 1): ?>
      <div class="lr-card__dots" aria-hidden="true">
        <?php for ($i = 0; $i < $slides_cnt; $i++): ?>
          <button class="lr-card__dot <?php echo $i === 0 ? 'is-active' : ''; ?>"
                  type="button"
                  data-dot="<?php echo (int)$i; ?>"
                  tabindex="-1"
                  aria-hidden="true"></button>
        <?php endfor; ?>
      </div>
    <?php endif; ?>

    <?php
    $bonus_raw = $product->get_meta('_lr_bonus_points');
    $bonus_val = is_numeric($bonus_raw) ? (float)$bonus_raw : 0;
    $bonus_txt = $bonus_val > 0 ? rtrim(rtrim(number_format($bonus_val, 2, '.', ''), '0'), '.') : '';
    ?>
    <?php if ($bonus_txt !== ''): ?>
      <span class="lr-card__badge lr-card__badge--bonus" aria-label="Bonus points">
        <span class="lr-card__badge-plus">+</span>
        <span class="lr-card__badge-val"><?php echo esc_html($bonus_txt); ?></span>
        <span class="lr-card__badge-ico" aria-hidden="true">
          <!-- gift icon -->
          <svg viewBox="0 0 24 24" width="14" height="14" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M20 12v8a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2v-8" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/>
            <path d="M2 7h20v5H2V7Z" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round"/>
            <path d="M12 7v15" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/>
            <path d="M12 7H8.8c-1.6 0-2.8-1.1-2.8-2.4C6 3 7.3 2 8.8 2c1.8 0 3.2 2.1 3.2 5Z" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round"/>
            <path d="M12 7h3.2c1.6 0 2.8-1.1 2.8-2.4C18 3 16.7 2 15.2 2 13.4 2 12 4.1 12 7Z" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round"/>
          </svg>
        </span>
      </span>
    <?php endif; ?>

  </div>

  <div class="lr-card__body">
    <a class="lr-card__title" href="<?php echo esc_url($link); ?>">
      <?php echo esc_html($title); ?>
    </a>

    <div class="lr-card__price">
      <?php echo wp_kses_post($product->get_price_html()); ?>
    </div>

    <div class="lr-card__actions">
      <?php woocommerce_template_loop_add_to_cart(); ?>
    </div>
  </div>

</li>

<?php
// ==============================
// Inline slider script (ONCE)
// ==============================
static $lr_card_slider_inited = false;
if (!$lr_card_slider_inited) :
  $lr_card_slider_inited = true;
?>
<script>
(function () {
  function clamp(n, min, max){ return Math.max(min, Math.min(max, n)); }

  function initCardSlider(root){
    const track = root.querySelector('.lr-card__track');
    const dots  = root.querySelectorAll('.lr-card__dot');
    const slidesCount = root.querySelectorAll('.lr-card__slide').length;
    const href = root.getAttribute('data-href') || '';
    if (!track || slidesCount <= 0) return;

    let index = 0;
    let width = 0;

    let isDown = false;
    let startX = 0;
    let startTranslate = 0;
    let currentTranslate = 0;
    let moved = false;

    function measure(){
      width = root.clientWidth || 1;
    }

    function setActiveDot(i){
      if (!dots || !dots.length) return;
      dots.forEach((d, k) => d.classList.toggle('is-active', k === i));
    }

    function apply(animate){
      if (animate) track.classList.remove('is-dragging');
      else track.classList.add('is-dragging');

      currentTranslate = -index * width;
      track.style.transform = 'translate3d(' + currentTranslate + 'px,0,0)';
      setActiveDot(index);
    }

    function goTo(i, animate=true){
      index = clamp(i, 0, slidesCount - 1);
      apply(animate);
    }

    function onDown(e){
      if (slidesCount <= 1) return;

      measure();
      isDown = true;
      moved = false;
      root.classList.add('is-grabbing');

      const p = (e.touches && e.touches[0]) ? e.touches[0] : e;
      startX = p.clientX;
      startTranslate = -index * width;
      track.classList.add('is-dragging');

      // чтобы не выделялся текст/не было ghost-drag картинки
      e.preventDefault?.();
    }

    function onMove(e){
      if (!isDown) return;

      const p = (e.touches && e.touches[0]) ? e.touches[0] : e;
      const dx = p.clientX - startX;
      if (Math.abs(dx) > 6) moved = true;

      // немного сопротивления на краях
      let next = startTranslate + dx;
      const min = -(slidesCount - 1) * width;
      const max = 0;
      if (next > max) next = max + (next - max) * 0.25;
      if (next < min) next = min + (next - min) * 0.25;

      track.style.transform = 'translate3d(' + next + 'px,0,0)';
    }

    function onUp(e){
      if (!isDown) return;
      isDown = false;
      root.classList.remove('is-grabbing');
      track.classList.remove('is-dragging');

      const p = (e.changedTouches && e.changedTouches[0]) ? e.changedTouches[0] : e;
      const dx = p.clientX - startX;

      if (Math.abs(dx) > Math.max(40, width * 0.12)) {
        if (dx < 0) index++;
        else index--;
      }
      index = clamp(index, 0, slidesCount - 1);
      apply(true);

      // если это был не drag — открываем товар кликом по картинке
      if (!moved && href) window.location.href = href;
    }

    // dots
    if (dots && dots.length) {
      dots.forEach(btn => {
        btn.addEventListener('click', (ev) => {
          ev.preventDefault();
          const i = parseInt(btn.getAttribute('data-dot') || '0', 10);
          measure();
          goTo(i, true);
        });
      });
    }

    // mouse
    root.addEventListener('pointerdown', onDown, {passive:false});
    window.addEventListener('pointermove', onMove, {passive:true});
    window.addEventListener('pointerup', onUp, {passive:true});
    window.addEventListener('pointercancel', onUp, {passive:true});

    // initial
    measure();
    goTo(0, true);

    // resize
    let t = null;
    window.addEventListener('resize', () => {
      clearTimeout(t);
      t = setTimeout(() => {
        measure();
        apply(true);
      }, 60);
    });
  }

  document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('[data-lr-card-slider]').forEach(initCardSlider);
  });
})();
</script>
<?php endif; ?>