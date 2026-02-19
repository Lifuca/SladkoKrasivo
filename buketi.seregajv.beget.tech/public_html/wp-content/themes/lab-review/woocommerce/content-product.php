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

$bonus = (int) $product->get_meta('_lr_bonus_points'); // из bonuses-system.php

// Картинки: главная + галерея
$img_ids = [];
$main_id = (int) $product->get_image_id();
if ($main_id) $img_ids[] = $main_id;

$gallery_ids = $product->get_gallery_image_ids();
if (is_array($gallery_ids) && !empty($gallery_ids)) {
  // добавляем, но без дубля главной
  foreach ($gallery_ids as $gid) {
    $gid = (int) $gid;
    if ($gid && $gid !== $main_id) $img_ids[] = $gid;
  }
}

if (empty($img_ids)) {
  // fallback если нет картинок
  $img_ids = [0];
}

$is_on_sale = $product->is_on_sale();
$is_instock = $product->is_in_stock();
$slides_cnt = count($img_ids);
?>

<li <?php wc_product_class('lr-card', $product); ?>>

  <div class="lr-card__media lr-card__slider" data-lr-card-slider>
    <div class="lr-card__track" aria-label="<?php echo esc_attr($title); ?>" role="group">
      <?php foreach ($img_ids as $i => $aid): ?>
        <div class="lr-card__slide" data-slide="<?php echo (int)$i; ?>">
          <?php
          if ($aid) {
            echo wp_get_attachment_image($aid, 'woocommerce_thumbnail', false, [
              'class'   => 'lr-card__img',
              'loading' => $i === 0 ? 'eager' : 'lazy',
              'alt'     => esc_attr($title),
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
          <button class="lr-card__dot <?php echo $i === 0 ? 'is-active' : ''; ?>" type="button" data-dot="<?php echo (int)$i; ?>" tabindex="-1"></button>
        <?php endfor; ?>
      </div>
    <?php endif; ?>

    <?php if ($bonus > 0): ?>
      <span class="lr-card__badge lr-card__badge--bonus">
        +<?php echo (int)$bonus; ?> бонусов
      </span>
    <?php endif; ?>

    <?php if ($is_on_sale): ?>
      <span class="lr-card__badge lr-card__badge--sale">
        <?php esc_html_e('Скидка', 'lab-review'); ?>
      </span>
    <?php endif; ?>

    <?php if (!$is_instock): ?>
      <span class="lr-card__badge lr-card__badge--out">
        <?php esc_html_e('Нет в наличии', 'lab-review'); ?>
      </span>
    <?php endif; ?>

    <!-- кликабельная область на товар (поверх слайдера) -->
    <a class="lr-card__link" href="<?php echo esc_url($link); ?>" aria-label="<?php echo esc_attr($title); ?>"></a>
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

