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


add_filter('template_include', function ($tpl) {
  if (!current_user_can('manage_options')) return $tpl;

  add_action('wp_head', function () use ($tpl) {
    global $wp_filter;
    echo "\n<!-- LR TEMPLATE picked: " . esc_html($tpl) . " -->\n";

    if (!empty($wp_filter['template_include'])) {
      echo "<!-- template_include callbacks:\n";
      foreach ($wp_filter['template_include']->callbacks as $prio => $cbs) {
        foreach ($cbs as $cb) {
          $fn = $cb['function'];
          if (is_string($fn)) {
            echo " - [$prio] $fn\n";
          } elseif (is_array($fn)) {
            $cls = is_object($fn[0]) ? get_class($fn[0]) : (string)$fn[0];
            echo " - [$prio] {$cls}::{$fn[1]}\n";
          } else {
            echo " - [$prio] (closure)\n";
          }
        }
      }
      echo "-->\n";
    }
  }, 999999);

  return $tpl;
}, 1);

add_action('wp', function () {
  if (!is_singular('product')) return;

  // Убираем стандартный loader Woo, который подменяет шаблон и может увести в index.php
  remove_filter('template_include', [WC_Template_Loader::class, 'template_loader'], 10);

  // Если плагин WooCommerce Brands тоже лезет — убираем и его
  if (has_filter('template_include', ['WC_Brands', 'template_loader'])) {
    remove_filter('template_include', ['WC_Brands', 'template_loader'], 10);
  }
}, 0);

add_filter('template_include', function ($template) {
  if (is_singular('product')) {
    $t = get_stylesheet_directory() . '/single-product.php';
    if (file_exists($t)) return $t;
  }
  return $template;
}, 999999);

add_filter('template_include', function ($template) {

  // только для админа — чтобы не светить это всем
  $is_admin = current_user_can('manage_options');

  // условия (проверяем и WP, и Woo)
  $is_product_wp  = is_singular('product');
  $is_product_wc  = function_exists('is_product') ? is_product() : false;

  $root_single = get_stylesheet_directory() . '/single-product.php';
  $wc_single   = get_stylesheet_directory() . '/woocommerce/single-product.php';

  if ($is_admin) {
    add_action('wp_head', function () use ($template, $is_product_wp, $is_product_wc, $root_single, $wc_single) {
      echo "\n<!-- LR DEBUG: is_singular(product)=".($is_product_wp?'YES':'NO').
           " | is_product()=".($is_product_wc?'YES':'NO').
           " | root_single_exists=".(file_exists($root_single)?'YES':'NO').
           " | root_single_readable=".(is_readable($root_single)?'YES':'NO').
           " | wc_single_exists=".(file_exists($wc_single)?'YES':'NO').
           " | wc_single_readable=".(is_readable($wc_single)?'YES':'NO').
           " | incoming_template=".$template.
           " -->\n";
    }, 999999);
  }



  return $template;
}, 999999);
add_action('wp_head', function () {
  if (!current_user_can('manage_options')) return;
  global $wp_query;
  echo "\n<!-- LR Q: is_404=". (is_404()?'YES':'NO')
    ." | post_type=". esc_html(get_post_type() ?: '—')
    ." | is_page=". (is_page()?'YES':'NO')
    ." | is_single=". (is_single()?'YES':'NO')
    ." | is_singular=". (is_singular()?'YES':'NO')
    ." -->\n";
}, 999999);

