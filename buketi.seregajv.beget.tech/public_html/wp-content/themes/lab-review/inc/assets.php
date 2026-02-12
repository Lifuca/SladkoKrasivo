<?php
if (!defined('ABSPATH')) exit;

add_action('wp_enqueue_scripts', function () {
  $v   = wp_get_theme()->get('Version');
  $uri = get_stylesheet_directory_uri();
  $dir = get_stylesheet_directory();

  // TOKENS (fonts + colors) — ПЕРВЫМ
  if (file_exists($dir . '/assets/css/tokens.css')) {
    wp_enqueue_style('lr-tokens', $uri . '/assets/css/tokens.css', [], $v);
  }

  // MAIN — зависит от tokens
  if (file_exists($dir . '/assets/css/main.css')) {
    wp_enqueue_style('lr-main', $uri . '/assets/css/main.css', ['lr-tokens'], $v);
  }

  // HEADER — зависит от main (а значит и от tokens)
  if (file_exists($dir . '/assets/css/header.css')) {
    wp_enqueue_style('lr-header', $uri . '/assets/css/header.css', ['lr-main'], $v);
  }
  if (file_exists($dir . '/assets/js/header.js')) {
    wp_enqueue_script('lr-header', $uri . '/assets/js/header.js', [], $v, true);
  }

  // FOOTER — зависит от main
  if (file_exists($dir . '/assets/css/site-footer.css')) {
    wp_enqueue_style('lr-site-footer', $uri . '/assets/css/site-footer.css', ['lr-main'], $v);
  }

  if (is_front_page()) {

    // HERO — зависит от main
    if (file_exists($dir . '/assets/css/hero.css')) {
      wp_enqueue_style('lr-hero', $uri . '/assets/css/hero.css', ['lr-main'], $v);
    }

    // Swiper
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
  }

}, 20);
