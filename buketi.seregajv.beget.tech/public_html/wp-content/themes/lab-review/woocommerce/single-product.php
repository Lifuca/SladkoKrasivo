<?php
if (!defined('ABSPATH')) exit;
echo '<div style="position:fixed;top:50px;right:10px;z-index:999999;background:#22c55e;color:#fff;padding:8px 10px;border-radius:10px;font:12px Arial">LR single-product.php ACTIVE</div>';

/**
 * Single Product
 */
if (!defined('ABSPATH')) exit;

do_action('woocommerce_before_main_content');

while (have_posts()) {
  the_post();
  wc_get_template_part('content', 'single-product');
}

do_action('woocommerce_after_main_content');
