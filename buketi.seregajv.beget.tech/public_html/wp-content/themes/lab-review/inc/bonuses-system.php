<?php
/**
 * bonuses-system.php
 * - product meta: _lr_bonus_points (points per 1 qty)
 * - award points on paid orders (processing/completed)
 * - keep user balance + ledger
 * - render "Bonuses & Promocodes" endpoint content
 */

if (!defined('ABSPATH')) exit;

define('LR_BONUS_META_KEY', '_lr_bonus_points');
define('LR_BONUS_USER_BALANCE_KEY', 'lr_bonus_balance');
define('LR_BONUS_USER_LEDGER_KEY', 'lr_bonus_ledger');

/**
 * -----------------------------
 * 1) (Опционально) Поле в админке товара
 * -----------------------------
 * Если ты заполняешь всё только массово — можно оставить, не мешает.
 */
add_action('woocommerce_product_options_general_product_data', function () {
  woocommerce_wp_text_input([
    'id' => LR_BONUS_META_KEY,
    'label' => 'Бонусы за покупку (за 1 шт.)',
    'desc_tip' => true,
    'description' => 'Сколько бонусов начислять за 1 единицу товара.',
    'type' => 'number',
    'custom_attributes' => [
      'min' => '0',
      'step' => '1',
    ],
  ]);
});

add_action('woocommerce_admin_process_product_object', function ($product) {
  if (!isset($_POST[LR_BONUS_META_KEY])) return;
  $val = (int) wp_unslash($_POST[LR_BONUS_META_KEY]);
  if ($val < 0) $val = 0;
  $product->update_meta_data(LR_BONUS_META_KEY, $val);
});

/**
 * -----------------------------
 * 2) Подсчёт бонусов по заказу
 * -----------------------------
 */
function lr_bonus_calc_points_for_order($order) {
  if (!$order || !is_a($order, 'WC_Order')) return 0;

  $total = 0;

  foreach ($order->get_items('line_item') as $item) {
    $product = $item->get_product();
    if (!$product) continue;

    $per_one = (int) $product->get_meta(LR_BONUS_META_KEY);
    if ($per_one <= 0) continue;

    $qty = (int) $item->get_quantity();
    if ($qty <= 0) $qty = 1;

    $total += ($per_one * $qty);
  }

  return max(0, (int) $total);
}

/**
 * -----------------------------
 * 3) Баланс/история в user_meta
 * -----------------------------
 */
function lr_bonus_get_balance($user_id) {
  return max(0, (int) get_user_meta($user_id, LR_BONUS_USER_BALANCE_KEY, true));
}

function lr_bonus_set_balance($user_id, $balance) {
  update_user_meta($user_id, LR_BONUS_USER_BALANCE_KEY, max(0, (int) $balance));
}

function lr_bonus_add_ledger($user_id, array $row) {
  $ledger = get_user_meta($user_id, LR_BONUS_USER_LEDGER_KEY, true);
  if (!is_array($ledger)) $ledger = [];
  $ledger[] = $row;

  // ограничим размер истории, чтобы не разрасталась бесконечно
  if (count($ledger) > 200) {
    $ledger = array_slice($ledger, -200);
  }

  update_user_meta($user_id, LR_BONUS_USER_LEDGER_KEY, $ledger);
}

/**
 * -----------------------------
 * 4) Начисление бонусов после оплаты
 * -----------------------------
 * Лучшее место — когда заказ становится processing/completed.
 * Чтобы не начислить дважды — order meta флаг.
 */
function lr_bonus_award_for_order($order_id) {
  $order = wc_get_order($order_id);
  if (!$order) return;

  $user_id = (int) $order->get_user_id();
  if ($user_id <= 0) return; // если гость — бонусы некуда привязать

  // уже начисляли?
  if ((string) $order->get_meta('_lr_bonus_awarded_done') === '1') return;

  // начисляем только если заказ реально оплачен
  if (!$order->is_paid()) return;

  $points = lr_bonus_calc_points_for_order($order);
  if ($points <= 0) {
    // даже если 0 — ставим флаг, чтобы не гонять хук
    $order->update_meta_data('_lr_bonus_awarded_done', '1');
    $order->update_meta_data('_lr_bonus_awarded', 0);
    $order->save();
    return;
  }

  $balance = lr_bonus_get_balance($user_id);
  $new_balance = $balance + $points;

  lr_bonus_set_balance($user_id, $new_balance);

  lr_bonus_add_ledger($user_id, [
    'ts' => time(),
    'type' => 'earn',
    'points' => $points,
    'order_id' => (int) $order->get_id(),
    'note' => 'Начисление за заказ',
    'balance_after' => $new_balance,
  ]);

  $order->update_meta_data('_lr_bonus_awarded_done', '1');
  $order->update_meta_data('_lr_bonus_awarded', $points);
  $order->save();
}

add_action('woocommerce_order_status_processing', 'lr_bonus_award_for_order', 20);
add_action('woocommerce_order_status_completed',  'lr_bonus_award_for_order', 20);

/**
 * -----------------------------
 * 5) Списание при отмене/возврате
 * -----------------------------
 * Упростим: если заказ cancelled/refunded и бонусы начислялись — списываем их целиком.
 * (Частичный refund можно добавить позже, если понадобится.)
 */
function lr_bonus_reverse_for_order($order_id) {
  $order = wc_get_order($order_id);
  if (!$order) return;

  $user_id = (int) $order->get_user_id();
  if ($user_id <= 0) return;

  if ((string) $order->get_meta('_lr_bonus_awarded_done') !== '1') return; // нечего списывать
  if ((string) $order->get_meta('_lr_bonus_reversed_done') === '1') return; // уже списали

  $awarded = (int) $order->get_meta('_lr_bonus_awarded');
  if ($awarded <= 0) {
    $order->update_meta_data('_lr_bonus_reversed_done', '1');
    $order->save();
    return;
  }

  $balance = lr_bonus_get_balance($user_id);
  $new_balance = max(0, $balance - $awarded);

  lr_bonus_set_balance($user_id, $new_balance);

  lr_bonus_add_ledger($user_id, [
    'ts' => time(),
    'type' => 'spend',
    'points' => $awarded,
    'order_id' => (int) $order->get_id(),
    'note' => 'Списание из-за отмены/возврата заказа',
    'balance_after' => $new_balance,
  ]);

  $order->update_meta_data('_lr_bonus_reversed_done', '1');
  $order->save();
}

add_action('woocommerce_order_status_cancelled', 'lr_bonus_reverse_for_order', 20);
add_action('woocommerce_order_status_refunded',  'lr_bonus_reverse_for_order', 20);

/**
 * -----------------------------
 * 6) Рендер раздела "Бонусы и промокоды" в ЛК
 * -----------------------------
 * Эту функцию вызовем из твоего endpoint'а.
 */
function lr_render_bonuses_promocodes_endpoint() {
  if (!is_user_logged_in()) {
    echo '<p>Войдите в аккаунт, чтобы увидеть бонусы.</p>';
    return;
  }

  $user_id = get_current_user_id();
  $balance = lr_bonus_get_balance($user_id);

  echo '<h3>Бонусы и промокоды</h3>';

  echo '<div style="margin:12px 0; padding:14px; border:1px solid rgba(0,0,0,.10); border-radius:12px;">';
  echo '<div style="font-size:14px; opacity:.8; margin-bottom:6px;">Ваш бонусный баланс</div>';
  echo '<div style="font-size:28px; font-weight:800; line-height:1;">' . esc_html($balance) . '</div>';
  echo '</div>';

  // История
  $ledger = get_user_meta($user_id, LR_BONUS_USER_LEDGER_KEY, true);
  if (!is_array($ledger)) $ledger = [];
  $ledger = array_reverse($ledger);
  $ledger = array_slice($ledger, 0, 20);

  echo '<h4 style="margin-top:18px;">История операций</h4>';

  if (empty($ledger)) {
    echo '<p>Пока нет начислений или списаний.</p>';
  } else {
    echo '<div style="overflow:auto;">';
    echo '<table style="width:100%; border-collapse:collapse;">';
    echo '<thead><tr>';
    echo '<th style="text-align:left; padding:10px 8px; border-bottom:1px solid rgba(0,0,0,.10);">Дата</th>';
    echo '<th style="text-align:left; padding:10px 8px; border-bottom:1px solid rgba(0,0,0,.10);">Операция</th>';
    echo '<th style="text-align:left; padding:10px 8px; border-bottom:1px solid rgba(0,0,0,.10);">Бонусы</th>';
    echo '<th style="text-align:left; padding:10px 8px; border-bottom:1px solid rgba(0,0,0,.10);">Заказ</th>';
    echo '</tr></thead><tbody>';

    foreach ($ledger as $row) {
      $ts = isset($row['ts']) ? (int) $row['ts'] : 0;
      $date = $ts ? date_i18n('d.m.Y H:i', $ts) : '—';
      $type = isset($row['type']) ? (string) $row['type'] : '';
      $note = isset($row['note']) ? (string) $row['note'] : '';
      $points = isset($row['points']) ? (int) $row['points'] : 0;
      $order_id = isset($row['order_id']) ? (int) $row['order_id'] : 0;

      $sign = ($type === 'spend') ? '-' : '+';

      echo '<tr>';
      echo '<td style="padding:10px 8px; border-bottom:1px solid rgba(0,0,0,.06); white-space:nowrap;">' . esc_html($date) . '</td>';
      echo '<td style="padding:10px 8px; border-bottom:1px solid rgba(0,0,0,.06);">' . esc_html($note ?: ($type === 'spend' ? 'Списание' : 'Начисление')) . '</td>';
      echo '<td style="padding:10px 8px; border-bottom:1px solid rgba(0,0,0,.06); font-weight:700;">' . esc_html($sign . $points) . '</td>';
      echo '<td style="padding:10px 8px; border-bottom:1px solid rgba(0,0,0,.06);">' . ($order_id ? esc_html('#' . $order_id) : '—') . '</td>';
      echo '</tr>';
    }

    echo '</tbody></table>';
    echo '</div>';
  }

  // Промокоды (опционально). Можно убрать полностью, если не нужно.
  $coupons = get_posts([
    'post_type'      => 'shop_coupon',
    'post_status'    => 'publish',
    'posts_per_page' => 10,
    'orderby'        => 'date',
    'order'          => 'DESC',
  ]);

  echo '<h4 style="margin-top:18px;">Промокоды</h4>';
  if (!empty($coupons)) {
    echo '<ul style="margin:10px 0 0; padding-left:18px;">';
    foreach ($coupons as $c) {
      echo '<li><code>' . esc_html($c->post_title) . '</code></li>';
    }
    echo '</ul>';
  } else {
    echo '<p>Сейчас нет активных промокодов.</p>';
  }
}
