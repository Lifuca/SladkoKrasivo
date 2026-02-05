<?php
/**
 * woodmart-child/checkout-fulfillment.php
 * Требования:
 * - Только Имя + Телефон (Email и Фамилия убраны)
 * - Телефон строго: +7 9XX XXX-XX-XX (с пробелами и тире), серверная валидация
 * - Тумблер "Доставка / Самовывоз" влияет на обязательность адреса:
 *   - Доставка: адрес виден и обязателен (звёздочка)
 *   - Самовывоз: адрес скрыт, required снят, вместо поля показывается текст адреса самовывоза
 * - Одно поле: "Текст для открытки" (необязательное, без слова "необязательно")
 * - Примечание к заказу (order notes) полностью отключено
 * - Выбор тумблера дублируется в скрытое поле lr_fulfillment_mode (не зависит от shipping_method)
 */

if (!defined('ABSPATH')) exit;

/** Адрес самовывоза — позже замените на ваш */
function lr_pickup_address_text() {
  return 'Адрес самовывоза: ___';
}

/**
 * 1) Поля checkout: только имя + телефон. Email/фамилия/примечания убираем.
 */
add_filter('woocommerce_checkout_fields', function ($fields) {

  // Имя — обязательно
  if (isset($fields['billing']['billing_first_name'])) {
    $fields['billing']['billing_first_name']['required'] = true;
  }

  // Фамилию убираем
  if (isset($fields['billing']['billing_last_name'])) {
    unset($fields['billing']['billing_last_name']);
  }

  // Email убираем полностью
  if (isset($fields['billing']['billing_email'])) {
    unset($fields['billing']['billing_email']);
  }

  // Телефон — обязателен + атрибуты под маску
  if (isset($fields['billing']['billing_phone'])) {
    $fields['billing']['billing_phone']['required'] = true;
    $fields['billing']['billing_phone']['type'] = 'tel';
    $fields['billing']['billing_phone']['placeholder'] = '+7 9__ ___-__-__';
    $fields['billing']['billing_phone']['input_class'] = ['lr-phone-ru'];

    $fields['billing']['billing_phone']['custom_attributes'] = [
      'inputmode' => 'tel',
      'maxlength' => '16',
      'pattern'   => '^\+7 9\d{2} \d{3}-\d{2}-\d{2}$',
    ];
  }

  // Убираем стандартные адресные billing-поля (адрес будет только в нашем поле ниже)
  $remove = [
    'billing_company','billing_country','billing_state','billing_city',
    'billing_address_1','billing_address_2','billing_postcode'
  ];
  foreach ($remove as $k) {
    if (isset($fields['billing'][$k])) unset($fields['billing'][$k]);
  }

  // Убираем shipping-блок целиком (чтобы не было второго адреса)
  if (isset($fields['shipping'])) {
    foreach ($fields['shipping'] as $k => $v) unset($fields['shipping'][$k]);
  }

  // Убираем "Примечание к заказу"
  if (isset($fields['order']['order_comments'])) {
    unset($fields['order']['order_comments']);
  }

  return $fields;
}, 20);

/** На всякий: отключаем notes-field полностью */
add_filter('woocommerce_enable_order_notes_field', '__return_false');

/**
 * 2) Рендерим:
 * - тумблер "Доставка / Самовывоз"
 * - скрытое поле lr_fulfillment_mode (для PHP-валидации)
 * - блок адреса самовывоза (текст)
 * - поле адреса доставки (required=true -> звёздочка, без "необязательно")
 * - поле "Текст для открытки" (необязательное, без "необязательно")
 */
add_action('woocommerce_after_checkout_billing_form', function ($checkout) {

  echo '<div id="lr-fulfillment" style="margin:14px 0 10px;">';
  echo '  <div class="lr-fulfillment-title" style="font-weight:700;margin:0 0 8px;">Способ получения</div>';
  echo '  <div class="lr-toggle">';
  echo '    <input type="radio" name="lr_fulfillment" id="lr_f_delivery" value="delivery" checked>';
  echo '    <input type="radio" name="lr_fulfillment" id="lr_f_pickup" value="pickup">';
  echo '    <div class="lr-toggle-ui" role="tablist" aria-label="Способ получения">';
  echo '      <label for="lr_f_delivery" class="lr-opt">Доставка</label>';
  echo '      <label for="lr_f_pickup" class="lr-opt">Самовывоз</label>';
  echo '      <span class="lr-knob" aria-hidden="true"></span>';
  echo '    </div>';
  echo '  </div>';
  echo '</div>';

  // Скрытое поле — истинный источник режима для PHP
  echo '<input type="hidden" name="lr_fulfillment_mode" id="lr_fulfillment_mode" value="delivery">';

  echo '<div id="pickup-address-box" class="lr-pickup-box" style="display:none; margin:10px 0; padding:12px; border:1px solid rgba(0,0,0,.10); border-radius:10px;">';
  echo '<strong>Адрес самовывоза</strong><br>' . esc_html(lr_pickup_address_text());
  echo '</div>';

  woocommerce_form_field('delivery_address', [
    'type'        => 'text',
    'class'       => ['form-row-wide'],
    'label'       => 'Адрес доставки',
    'required'    => true, // ✅ звездочка, нет "необязательно"
    'placeholder' => 'Город, улица, дом, подъезд, квартира'
  ], $checkout->get_value('delivery_address'));

  woocommerce_form_field('card_text', [
    'type'        => 'textarea',
    'class'       => ['form-row-wide'],
    'label'       => 'Текст для открытки',
    'required'    => false,
    'placeholder' => ''
  ], $checkout->get_value('card_text'));
});

/**
 * 2.1) Убираем "необязательно" только у card_text
 */
add_filter('woocommerce_form_field', function ($field, $key, $args, $value) {
  if ($key === 'card_text') {
    $field = preg_replace('~<span class="optional">.*?</span>~s', '', $field);
  }
  return $field;
}, 10, 4);

/**
 * 3) Валидация:
 * - Телефон строго +7 9XX XXX-XX-XX
 * - Адрес обязателен только при режиме delivery (ползунок)
 */
add_action('woocommerce_checkout_process', function () {

  // Телефон
  $phone_raw = isset($_POST['billing_phone']) ? (string) wp_unslash($_POST['billing_phone']) : '';
  if (!preg_match('/^\+7 9\d{2} \d{3}-\d{2}-\d{2}$/', $phone_raw)) {
    wc_add_notice('Введите телефон в формате +7 9XX XXX-XX-XX.', 'error');
  }

  // Режим получения (истина — из скрытого поля)
  $mode = isset($_POST['lr_fulfillment_mode']) ? sanitize_text_field(wp_unslash($_POST['lr_fulfillment_mode'])) : 'delivery';
  $is_pickup = ($mode === 'pickup');

  if (!$is_pickup) {
    $addr = isset($_POST['delivery_address']) ? trim(wp_unslash($_POST['delivery_address'])) : '';
    if ($addr === '') {
      wc_add_notice('Введите адрес доставки.', 'error');
    }
  }
});

/**
 * 4) Сохраняем meta заказа
 */
add_action('woocommerce_checkout_create_order', function ($order, $data) {

  if (isset($_POST['lr_fulfillment_mode'])) {
    $order->update_meta_data('_lr_fulfillment_mode', sanitize_text_field(wp_unslash($_POST['lr_fulfillment_mode'])));
  }
  if (isset($_POST['delivery_address'])) {
    $order->update_meta_data('_delivery_address', sanitize_text_field(wp_unslash($_POST['delivery_address'])));
  }
  if (isset($_POST['card_text'])) {
    $order->update_meta_data('_card_text', sanitize_textarea_field(wp_unslash($_POST['card_text'])));
  }

}, 10, 2);

/**
 * 5) Админка заказа
 */
add_action('woocommerce_admin_order_data_after_billing_address', function ($order) {

  $mode = (string) $order->get_meta('_lr_fulfillment_mode');
  $addr = (string) $order->get_meta('_delivery_address');
  $card = (string) $order->get_meta('_card_text');

  if ($mode) echo '<p><strong>Способ получения:</strong> ' . esc_html($mode === 'pickup' ? 'Самовывоз' : 'Доставка') . '</p>';
  if ($addr) echo '<p><strong>Адрес доставки:</strong> ' . esc_html($addr) . '</p>';
  if ($card) echo '<p><strong>Текст для открытки:</strong><br>' . nl2br(esc_html($card)) . '</p>';
});

/**
 * 6) CSS + JS:
 * - тумблер переключает поля и записывает lr_fulfillment_mode
 * - телефон маска +7 9XX XXX-XX-XX (показывает ввод по мере набора)
 */
add_action('wp_enqueue_scripts', function () {

  if (!function_exists('is_checkout') || !is_checkout()) return;

  wp_enqueue_script('wc-checkout');

  // CSS тумблера
  wp_register_style('lr-fulfillment-style', false);
  wp_enqueue_style('lr-fulfillment-style');

  $css = '
  .lr-toggle{position:relative; display:inline-block;}
  .lr-toggle input{position:absolute; left:-9999px;}
  .lr-toggle-ui{
    position:relative; display:flex;
    width:260px; max-width:100%;
    background:rgba(0,0,0,.06);
    border-radius:999px; padding:4px;
    user-select:none;
  }
  .lr-toggle-ui .lr-opt{
    position:relative; z-index:2;
    flex:1; text-align:center;
    padding:10px 12px;
    cursor:pointer;
    font-weight:600;
    border-radius:999px;
  }
  .lr-toggle-ui .lr-knob{
    position:absolute; top:4px; bottom:4px; left:4px;
    width:calc(50% - 4px);
    border-radius:999px;
    background:#fff;
    box-shadow:0 6px 18px rgba(0,0,0,.10);
    transition:transform .22s ease;
    z-index:1;
  }
  #lr_f_pickup:checked ~ .lr-toggle-ui .lr-knob{ transform:translateX(100%); }
  ';
  wp_add_inline_style('lr-fulfillment-style', $css);

  $js = <<<JS
(function($){

  function setMode(mode){
    $('#lr_fulfillment_mode').val(mode);
  }

  function showForDelivery(){
    setMode('delivery');
    $('#pickup-address-box').hide();
    $('#delivery_address_field').show();
    $('#delivery_address').prop('required', true).attr('aria-required','true');
  }

  function showForPickup(){
    setMode('pickup');
    $('#delivery_address_field').hide();
    $('#pickup-address-box').show();
    $('#delivery_address').prop('required', false).attr('aria-required','false');
  }

  // При клике на тумблер — переключаем поля
  $(document).on('change', 'input[name="lr_fulfillment"]', function(){
    if ($(this).val() === 'pickup') showForPickup();
    else showForDelivery();
  });

  // Инициализация по умолчанию
  function init(){
    if ($('#lr_f_pickup').is(':checked')) showForPickup();
    else showForDelivery();
  }

  // ---- Телефон: маска +7 9XX XXX-XX-XX (по мере ввода) ----
  function digits(s){ return String(s||'').replace(/\\D+/g,''); }

  function formatRuPhone(val){
    var d = digits(val);

    // 8XXXXXXXXXX -> 7XXXXXXXXXX
    if (d.length === 11 && d[0] === '8') d = '7' + d.slice(1);

    // 9XXXXXXXXX (10 цифр) -> 7 + 9XXXXXXXXX
    if (d.length === 10 && d[0] === '9') d = '7' + d;

    // гарантируем старт с 7
    if (d.length > 0 && d[0] !== '7') d = '7' + d.replace(/^7+/, '');

    // гарантируем вторую цифру 9 (моб. РФ), если есть вторая цифра
    if (d.length >= 2 && d[1] !== '9') d = '7' + '9' + d.slice(2);

    d = d.slice(0, 11);

    var out = '+7';

    if (d.length >= 2) {
      var partA = d.slice(1, Math.min(4, d.length)); // "9", "90", "900"
      out += ' ' + partA;
    }

    if (d.length >= 5) {
      var partB = d.slice(4, Math.min(7, d.length)); // "1", "12", "123"
      out += ' ' + partB;
    }

    if (d.length >= 8) {
      var partC = d.slice(7, Math.min(9, d.length)); // "4", "45"
      out += '-' + partC;
    }

    if (d.length >= 10) {
      var partD = d.slice(9, Math.min(11, d.length)); // "6", "67"
      out += '-' + partD;
    }

    return out;
  }

  $(document).on('input', 'input.lr-phone-ru', function(){
    $(this).val(formatRuPhone($(this).val()));
  });

  // WooCommerce иногда перерисовывает checkout — переинициализируем
  $(document.body).on('updated_checkout', init);

  $(function(){
    init();
    setTimeout(init, 300);
  });

})(jQuery);
JS;

  wp_add_inline_script('wc-checkout', $js);
}, 20);
