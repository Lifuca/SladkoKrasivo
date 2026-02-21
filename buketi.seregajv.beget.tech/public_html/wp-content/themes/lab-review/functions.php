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



// 1) Хук: применяем скидку к позициям корзины, помеченным как combo
add_action('woocommerce_before_calculate_totals', function ($cart) {
  if (is_admin() && !defined('DOING_AJAX')) return;
  if (!$cart) return;

  foreach ($cart->get_cart() as $cart_item_key => &$item) {
    if (empty($item['lr_combo_pct'])) continue;

    $pct = (float) $item['lr_combo_pct'];
    if ($pct <= 0) continue;

    // запоминаем исходную цену один раз
    if (!isset($item['lr_combo_orig_price'])) {
      $item['lr_combo_orig_price'] = (float) $item['data']->get_price();
    }

    $orig = (float) $item['lr_combo_orig_price'];
    $new  = $orig * (1 - $pct / 100);

    // Woo сам округлит по настройкам валюты, но лучше отдать с 2 знаками
    $item['data']->set_price(round($new, 2));
  }
}, 20, 1);


// 2) AJAX: добавить base + picked как combo-набор
add_action('wp_ajax_lr_combo_add_to_cart', 'lr_combo_add_to_cart_ajax');
add_action('wp_ajax_nopriv_lr_combo_add_to_cart', 'lr_combo_add_to_cart_ajax');

function lr_combo_add_to_cart_ajax() {
  if (!function_exists('WC')) {
    wp_send_json_error(['message' => 'WooCommerce not loaded']);
  }

  $nonce = isset($_POST['nonce']) ? sanitize_text_field($_POST['nonce']) : '';
  if (!wp_verify_nonce($nonce, 'lr_combo_nonce')) {
    wp_send_json_error(['message' => 'Bad nonce']);
  }

  $base_id = isset($_POST['base_id']) ? absint($_POST['base_id']) : 0;
  $picked  = isset($_POST['picked_ids']) ? (array) $_POST['picked_ids'] : [];
  $picked  = array_values(array_filter(array_map('absint', $picked)));

  $pct = isset($_POST['pct']) ? (float) $_POST['pct'] : 0.0;
  if ($pct < 0) $pct = 0;
  if ($pct > 50) $pct = 50; // защита от мусора

  if (!$base_id) {
    wp_send_json_error(['message' => 'No base product']);
  }

  $ids = array_values(array_unique(array_filter(array_merge([$base_id], $picked))));
  $group = 'lrcombo_' . wp_generate_uuid4();

  // убедимся что корзина инициализирована
  if (!WC()->cart) wc_load_cart();

  $added = [];

  foreach ($ids as $pid) {
    $product = wc_get_product($pid);
    if (!$product) continue;

    // если вдруг попался variable — лучше редиректить на карточку (как стандартный wc-ajax)
    if ($product->is_type('variable')) {
      wp_send_json_error([
        'redirect' => get_permalink($pid),
        'message'  => 'Variable product requires selection'
      ]);
    }

    $cart_item_data = [
      'lr_combo_pct'   => $pct,
      'lr_combo_group' => $group,
      // чтобы не склеивалось с обычным добавлением
      'unique_key'     => md5($group . '|' . $pid . '|' . microtime(true)),
    ];

    $key = WC()->cart->add_to_cart($pid, 1, 0, [], $cart_item_data);
    if ($key) $added[] = $pid;
  }

  wp_send_json_success([
    'added' => $added,
    'pct'   => $pct,
  ]);
}