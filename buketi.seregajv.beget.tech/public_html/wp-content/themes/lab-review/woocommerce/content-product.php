<?php
defined('ABSPATH') || exit;

global $product;
?>

<li <?php wc_product_class('custom-product-card'); ?>>

  <a href="<?php the_permalink(); ?>" class="product-image">
    <?php woocommerce_template_loop_product_thumbnail(); ?>
  </a>

  <div class="product-body">

    <h3 class="product-title">
      <?php the_title(); ?>
    </h3>

    <div class="product-price">
      <?php woocommerce_template_loop_price(); ?>
    </div>

    <div class="product-actions">
      <?php woocommerce_template_loop_add_to_cart(); ?>
    </div>

  </div>

</li>
