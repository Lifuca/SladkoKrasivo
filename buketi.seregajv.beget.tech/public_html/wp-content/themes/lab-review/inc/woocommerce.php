<?php
if (!defined('ABSPATH')) exit;

/**
 * WooCommerce integration for Lab Review theme.
 */
add_action('after_setup_theme', function () {
  add_theme_support('woocommerce');
  add_theme_support('wc-product-gallery-zoom');
  add_theme_support('wc-product-gallery-lightbox');
  add_theme_support('wc-product-gallery-slider');
});

/**
 * Better defaults for catalog grid.
 */
add_filter('loop_shop_columns', function () {
  return 4;
}, 20);

add_filter('loop_shop_per_page', function () {
  return 12;
}, 20);

/**
 * Remove sidebar on Woo pages for full-width layout.
 */
add_action('wp', function () {
  if (!function_exists('is_woocommerce')) {
    return;
  }

  if (is_woocommerce() || is_cart() || is_checkout() || is_account_page()) {
    remove_action('woocommerce_sidebar', 'woocommerce_get_sidebar', 10);
  }
});