<?php
/**
 * Checkout: доставка/самовывоз + обязательные поля
 * Файл: wp-content/themes/woodmart-child/checkout-required-fields.php
 */

if (!defined('ABSPATH')) exit;

/** Получить выбранный метод доставки (flat_rate:1 / local_pickup:3 и т.п.) */
function lr_get_chosen_shipping_method() {
  // Во время AJAX обновления checkout метод приходит через POST
  if (!empty($_POST['shipping_method']) && is_array($_POST['shipping_method'])) {
    return (string) $_POST['shipping_method'][0];
  }

  // Иначе берём из сессии
  $chosen = (function_exists('WC') && WC()->session) ? WC()->session->get('chosen_shipping_methods') : [];
  return (!empty($chosen) && is_array($chosen)) ? (string) $chosen[0] : '';
}

/**
 * 1) Настройка стандартных полей checkout:
 * - Оставляем имя + телефон (email по желанию)
 * - Убираем стандартные адресные поля WooCommerce
 */
add_filter('woocommerce_checkout_fields', function ($fields) {

  // Имя — обязательно
  if (isset($fields['billing']['billing_first_name'])) {
    $fields['billing']['billing_first_name']['required'] = true;
  }

  // Телефон — обязательно
  if (isset($fields['billing']['billing_phone'])) {
    $fields['billing']['billing_phone']['required'] = true;
  }

  // Email: по умолчанию WooCommerce считает его важным (чеки/уведомления).
  // Если хотите оставить — ничего не трогаем.
  // Если хотите сделать необязательным — раскомментируйте:
  // if (isset($fields['billing']['billing_email'])) {
  //   $fields['billing']['billing_email']['required'] = false;
  // }

  // Убираем стандартные billing-адресные поля (чтобы адрес был только в нашем поле)
  $remove = [
    'billing_company','billing_country','billing_state','billing_city',
    'billing_address_1','billing_address_2','billing_postcode'
  ];
  foreach ($remove as $k) {
    if (isset($fields['billing'][$k])) unset($fields['billing'][$k]);
  }

  // Убираем shipping-блок полностью (чтобы WooCommerce не рисовал второй адрес)
  if (isset($fields['shipping'])) {
    foreach ($fields['shipping'] as $k => $v) unset($fields['shipping'][$k]);
  }

  return $fields;
}, 20);

/**
 * 2) Рисуем:
 * - Текст адреса самовывоза (показывается только при local_pickup)
 * - Поле "Адрес доставки" (обязательное только при доставке)
 * - Поле "Комментарий" (необязательное)
 */
add_action('woocommerce_after_checkout_billing_form', function ($checkout) {

  // TODO: замените на ваш адрес самовывоза
  $pickup_text = 'Самовывоз: г. ___, ул. ___, дом ___, вход ___, время ___';

  echo '<div id="pickup-address-box" style="margin:12px 0; padding:12px; border:1px solid rgba(0,0,0,.10); border-radius:10px; display:none;">';
  echo '<strong>Адрес самовывоза</strong><br>' . esc_html($pickup_text);
  echo '</div>';

  // Поле адреса доставки
  woocommerce_form_field('delivery_address', [
    'type'        => 'text',
    'class'       => ['form-row-wide'],
    'label'       => 'Адрес доставки',
    'required'    => false, // обязательность зададим валидатором + JS
    'placeholder' => 'Город, улица, дом, подъезд, квартира'
  ], $checkout->get_value('delivery_address'));

  // Поле комментария
  woocommerce_form_field('delivery_comment', [
    'type'        => 'textarea',
    'class'       => ['form-row-wide'],
    'label'       => 'Комментарий к заказу (по желанию)',
    'required'    => false,
    'placeholder' => 'Например: домофон, ориентир, пожелания по времени'
  ], $checkout->get_value('delivery_comment'));
});

/**
 * 3) Валидация:
 * Если НЕ самовывоз — адрес доставки обязателен
 */
add_action('woocommerce_checkout_process', function () {

  $method = lr_get_chosen_shipping_method();
  $is_pickup = (strpos($method, 'local_pickup') === 0);

  if (!$is_pickup) {
    $addr = isset($_POST['delivery_address']) ? trim(wp_unslash($_POST['delivery_address'])) : '';
    if ($addr === '') {
      wc_add_notice('Введите адрес доставки.', 'error');
    }
  }
});

/**
 * 4) Сохраняем поля в meta заказа
 */
add_action('woocommerce_checkout_create_order', function ($order, $data) {

  if (isset($_POST['delivery_address'])) {
    $order->update_meta_data('_delivery_address', sanitize_text_field(wp_unslash($_POST['delivery_address'])));
  }
  if (isset($_POST['delivery_comment'])) {
    $order->update_meta_data('_delivery_comment', sanitize_textarea_field(wp_unslash($_POST['delivery_comment'])));
  }

}, 10, 2);

/**
 * 5) Показываем в админке заказа
 */
add_action('woocommerce_admin_order_data_after_billing_address', function ($order) {

  $addr = $order->get_meta('_delivery_address');
  $comm = $order->get_meta('_delivery_comment');

  if ($addr) echo '<p><strong>Адрес доставки:</strong> ' . esc_html($addr) . '</p>';
  if ($comm) echo '<p><strong>Комментарий:</strong> ' . nl2br(esc_html($comm)) . '</p>';
});

/**
 * 6) JS: переключаем отображение поля/текста на checkout
 * (встроенный JS, чтобы всё было в одном файле)
 */
add_action('wp_enqueue_scripts', function () {
  if (!function_exists('is_checkout') || !is_checkout()) return;

  // Важно: jQuery в WooCommerce обычно есть на checkout, но гарантируем зависимость
  wp_enqueue_script('jquery');

  $js = <<<JS
(function($){
  function lrToggle(){
    // Берём выбранный способ доставки (local_pickup:* или что-то другое)
    var method = $('input[name^="shipping_method"]:checked').val() || '';
    var isPickup = method.indexOf('local_pickup') === 0;

    var \$addrRow = $('#delivery_address_field');
    var \$addrInp = $('#delivery_address');
    var \$pickupBox = $('#pickup-address-box');

    if(isPickup){
      \$addrRow.hide();
      \$pickupBox.show();
      \$addrInp.prop('required', false).attr('aria-required','false');
    } else {
      \$addrRow.show();
      \$pickupBox.hide();
      \$addrInp.prop('required', true).attr('aria-required','true');
    }
  }

  // WooCommerce часто перерисовывает checkout через AJAX
  $(document.body).on('updated_checkout', lrToggle);
  $(document).on('change', 'input[name^="shipping_method"]', lrToggle);
  $(function(){ lrToggle(); });
})(jQuery);
JS;

  wp_add_inline_script('jquery', $js);
}, 20);
