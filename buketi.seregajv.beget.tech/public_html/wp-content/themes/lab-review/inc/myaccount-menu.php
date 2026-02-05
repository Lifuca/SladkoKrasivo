<?php
/**
 * woodmart-child/myaccount-menu.php
 * Оставить: Заказы / Бонусы и промокоды / Выйти
 * + корректный endpoint для WooCommerce
 */

if (!defined('ABSPATH')) exit;

/**
 * 1) Регистрируем endpoint в WordPress
 */
add_action('init', function () {
  add_rewrite_endpoint('bonuses-promocodes', EP_ROOT | EP_PAGES);
}, 0);

/**
 * 2) Регистрируем endpoint в WooCommerce (ВАЖНО!)
 * Иначе WC()->query->get_current_endpoint() будет пустым,
 * а хук woocommerce_account_bonuses-promocodes_endpoint не сработает.
 */
add_filter('woocommerce_get_query_vars', function ($vars) {
  $vars['bonuses-promocodes'] = 'bonuses-promocodes';
  return $vars;
});

/**
 * 3) Авто-flush rewrite 1 раз (чтобы не ловить 404)
 */
add_action('admin_init', function () {
  if (!current_user_can('manage_options')) return;

  $done = get_option('lr_flush_rewrite_bonuses_promocodes_done', '');
  if ($done === '1') return;

  flush_rewrite_rules(false);
  update_option('lr_flush_rewrite_bonuses_promocodes_done', '1');
});

/**
 * 4) Меню "Мой аккаунт": только нужные пункты
 */
add_filter('woocommerce_account_menu_items', function ($items) {

  $new = [];
  $new['orders'] = $items['orders'] ?? 'Заказы';
  $new['bonuses-promocodes'] = 'Бонусы и промокоды';
  $new['customer-logout'] = $items['customer-logout'] ?? 'Выйти';

  return $new;
}, 999);

/**
 * 5) Контент вкладки "Бонусы и промокоды"
 */
add_action('woocommerce_account_bonuses-promocodes_endpoint', function () {

  if (function_exists('lr_render_bonuses_promocodes_endpoint')) {
    lr_render_bonuses_promocodes_endpoint();
    return;
  }

  echo '<h3>Бонусы и промокоды</h3>';
  echo '<p>Функция <code>lr_render_bonuses_promocodes_endpoint()</code> не найдена. Проверь подключение bonuses-system.php.</p>';
});

/**
 * 6) Редирект: разрешаем только нужные вкладки.
 * ВАЖНО: корректно определяем endpoint, включая наш.
 */
add_action('template_redirect', function () {

  if (!function_exists('is_account_page') || !is_account_page()) return;
  if (!is_user_logged_in()) return;

  $allowed = ['orders', 'bonuses-promocodes', 'customer-logout'];

  // 1) Пробуем получить endpoint через WooCommerce
  $endpoint = '';
  if (function_exists('WC') && WC()->query) {
    $endpoint = (string) WC()->query->get_current_endpoint();
  }

  // 2) Если по какой-то причине пусто — пробуем определить по query_vars WP
  // (это спасает, когда WC ещё не успел распознать)
  if ($endpoint === '') {
    global $wp;
    if (!empty($wp->query_vars) && array_key_exists('bonuses-promocodes', $wp->query_vars)) {
      $endpoint = 'bonuses-promocodes';
    }
  }

  // dashboard = пустой endpoint
  if ($endpoint === '') {
    wp_safe_redirect(wc_get_account_endpoint_url('orders'));
    exit;
  }

  if (!in_array($endpoint, $allowed, true)) {
    wp_safe_redirect(wc_get_account_endpoint_url('orders'));
    exit;
  }

}, 20);
