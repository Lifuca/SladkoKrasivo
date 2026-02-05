<?php
if (!defined('ABSPATH')) exit;

$theme_dir = get_template_directory();

$includes = [
  '/inc/assets.php',
  '/inc/woocommerce.php',
  '/inc/shortcodes.php',

  // твои модули (если ты реально их перенёс в inc/)
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
