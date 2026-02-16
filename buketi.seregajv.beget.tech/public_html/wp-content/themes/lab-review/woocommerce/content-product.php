<?php
/**
 * Product Card (Loop)
 * Path: /woocommerce/content-product.php
 */
echo '<div style="position:fixed;top:90px;right:10px;z-index:999999;background:#16a34a;color:#fff;padding:8px 10px;border-radius:10px;font:12px Arial">LR content-product.php ACTIVE</div>';

if (!defined('ABSPATH')) exit;

global $product;

if (empty($product) || !$product->is_visible()) return;

$product_id = $product->get_id();
$link       = get_permalink($product_id);
$title      = get_the_title($product_id);
$img_id     = $product->get_image_id();
$img_html   = $img_id ? wp_get_attachment_image($img_id, 'woocommerce_thumbnail', false, [
  'class'   => 'lr-card__img',
  'loading' => 'lazy',
  'alt'     => esc_attr($title),
]) : wc_placeholder_img('woocommerce_thumbnail');

$is_on_sale = $product->is_on_sale();
$is_instock = $product->is_in_stock();
?>

<li <?php wc_product_class('lr-card', $product); ?>>

  <a class="lr-card__media" href="<?php echo esc_url($link); ?>">
    <?php echo $img_html; ?>

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
  </a>

  <div class="lr-card__body">
    <a class="lr-card__title" href="<?php echo esc_url($link); ?>">
      <?php echo esc_html($title); ?>
    </a>

    <div class="lr-card__price">
      <?php echo wp_kses_post($product->get_price_html()); ?>
    </div>

    <div class="lr-card__actions">
      <?php
      // стандартная woocommerce кнопка add_to_cart с правильными data-атрибутами
      woocommerce_template_loop_add_to_cart();
      ?>
    </div>
  </div>

</li>
