<?php
if (!defined('ABSPATH')) exit;

$theme_dir = get_stylesheet_directory();

$includes = [
  '/inc/assets.php',
  '/inc/shortcodes.php',
  '/inc/woocommerce.php',

  '/inc/myaccount-menu.php',
  '/inc/bonuses-system.php',
  '/inc/checkout-fulfillment.php',
  '/inc/checkout-required-fields.php',
];

foreach ($includes as $rel) {
  $path = $theme_dir . $rel;
  if (file_exists($path)) {
    require_once $path;
  }
}

add_filter('woocommerce_breadcrumb_defaults', function ($d) {
  $d['wrap_before'] = '<nav class="lr-breadcrumbs" aria-label="Breadcrumbs">';
  $d['wrap_after']  = '</nav>';
  return $d;
});


