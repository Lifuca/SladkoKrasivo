<?php
if (!defined('ABSPATH')) exit;

add_action('wp_enqueue_scripts', function () {

  $v   = wp_get_theme()->get('Version');
  $uri = get_stylesheet_directory_uri();
  $dir = get_stylesheet_directory();

  /* =========================
     BASE: tokens -> main -> header/footer
     ========================= */

  // TOKENS (fonts + colors) — FIRST
  if (file_exists($dir . '/assets/css/tokens.css')) {
    wp_enqueue_style('lr-tokens', $uri . '/assets/css/tokens.css', [], $v);
  }

  // MAIN — depends on tokens
  if (file_exists($dir . '/assets/css/main.css')) {
    wp_enqueue_style('lr-main', $uri . '/assets/css/main.css', ['lr-tokens'], $v);
  }

  // HEADER
  if (file_exists($dir . '/assets/css/header.css')) {
    wp_enqueue_style('lr-header', $uri . '/assets/css/header.css', ['lr-main'], $v);
  }
  if (file_exists($dir . '/assets/js/header.js')) {
    wp_enqueue_script('lr-header', $uri . '/assets/js/header.js', [], $v, true);
  }

  // FOOTER
  if (file_exists($dir . '/assets/css/site-footer.css')) {
    wp_enqueue_style('lr-site-footer', $uri . '/assets/css/site-footer.css', ['lr-main'], $v);
  }

  /* =========================
     PRODUCT CARD (нужно и на главной, и в каталоге)
     ========================= */
  if (file_exists($dir . '/assets/css/product-card.css')) {
    wp_enqueue_style('lr-product-card', $uri . '/assets/css/product-card.css', ['lr-main'], $v);
  }

  if (file_exists($dir . '/assets/js/product-card.js')) {
  wp_enqueue_script('lr-product-card', $uri . '/assets/js/product-card.js', [], $v, true);
  }

  /* =========================
     FRONT PAGE ONLY
     ========================= */
  if (is_front_page()) {

    // HERO
    if (file_exists($dir . '/assets/css/hero.css')) {
      wp_enqueue_style('lr-hero', $uri . '/assets/css/hero.css', ['lr-main'], $v);
    }

    // Swiper (CDN)
    wp_enqueue_style('lr-swiper', 'https://cdn.jsdelivr.net/npm/swiper@8/swiper-bundle.min.css', [], '8');
    wp_enqueue_script('lr-swiper', 'https://cdn.jsdelivr.net/npm/swiper@8/swiper-bundle.min.js', [], '8', true);

    if (file_exists($dir . '/assets/js/hero.js')) {
      wp_enqueue_script('lr-hero', $uri . '/assets/js/hero.js', ['lr-swiper'], $v, true);
    }

    // Dual promo
    if (file_exists($dir . '/assets/css/dual-promo.css')) {
      wp_enqueue_style('lr-dual-promo', $uri . '/assets/css/dual-promo.css', ['lr-main'], $v);
    }

    // Yandex reviews
    if (file_exists($dir . '/assets/css/yandex-reviews.css')) {
      wp_enqueue_style('lr-yandex-reviews', $uri . '/assets/css/yandex-reviews.css', ['lr-main'], $v);
    }

    // FAQ
    if (file_exists($dir . '/assets/css/faq.css')) {
      wp_enqueue_style('lr-faq', $uri . '/assets/css/faq.css', ['lr-main'], $v);
    }
    if (file_exists($dir . '/assets/js/faq.js')) {
      wp_enqueue_script('lr-faq', $uri . '/assets/js/faq.js', [], $v, true);
    }

    // Products by categories block
    if (file_exists($dir . '/assets/css/products-by-categories.css')) {
      wp_enqueue_style('lr-products-by-categories', $uri . '/assets/css/products-by-categories.css', ['lr-main'], $v);
    }
  }

  /* =========================
     CATALOG (shop/category/tag/product)
     ========================= */
  $is_shop_context = function_exists('is_woocommerce') && (
    is_woocommerce() ||
    is_shop() ||
    is_product_category() ||
    is_product_tag() ||
    is_product_taxonomy() ||
    is_tax('product_cat') ||
    is_tax('product_tag') ||
    is_post_type_archive('product') ||
    is_singular('product')
  );

  if ($is_shop_context) {
    if (file_exists($dir . '/assets/css/catalog.css')) {
      wp_enqueue_style('lr-catalog', $uri . '/assets/css/catalog.css', ['lr-main', 'lr-product-card'], $v);
      wp_enqueue_script('lr-catalog-filters', $uri . '/assets/js/catalog-filters.js', [], $v, true);
    }
  }



}, 20);

/**
 * Inline SVG helper
 * Usage: echo lr_inline_svg('assets/icons/search.svg');
 */
function lr_inline_svg($relative_path) {
  $file = get_stylesheet_directory() . '/' . ltrim($relative_path, '/');
  if (!file_exists($file)) return '';
  $svg = file_get_contents($file);
  return $svg ?: '';
}
