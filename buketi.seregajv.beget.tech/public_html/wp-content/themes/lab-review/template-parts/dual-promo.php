<?php
/**
 * Template Part: Dual Promo
 */
if (!defined('ABSPATH')) exit;

$u = wp_get_upload_dir();
$uploads = trailingslashit($u['baseurl']);
?>

<section class="ui-dual">
  <div class="ui-dual__grid">

    <!-- LEFT COLUMN -->
    <div class="ui-col">
      <h2 class="ui-title">Наши соц сети</h2>
      <p class="ui-text">
        Подписывайтесь, чтобы первыми узнавать о самых выгодных акциях, специальных предложениях и новых поступлениях.
      </p>

      <div class="ui-actions">
        <a href="#" class="ui-btn"><span>Instagram</span></a>
        <a href="#" class="ui-btn"><span>Telegram</span></a>
      </div>

      <div class="ui-gallery">
        <img src="<?php echo esc_url($uploads . '2026/01/soc1.webp'); ?>" alt="">
        <img src="<?php echo esc_url($uploads . '2026/01/soc2-scaled.webp'); ?>" alt="">
        <img src="<?php echo esc_url($uploads . '2026/01/soc3-scaled.webp'); ?>" alt="">
        <img src="<?php echo esc_url($uploads . '2026/01/soc4.webp'); ?>" alt="">
      </div>
    </div>

    <!-- RIGHT COLUMN -->
    <div class="ui-col">
      <h2 class="ui-title">С нами выгодно</h2>
      <p class="ui-text">
        Бонусная программа лояльности для всех покупателей! Скидки и специальные условия для корпоративных клиентов
      </p>

      <div class="ui-actions">
        <a href="#" class="ui-btn"><span>Узнать о бонусах</span></a>
        <a href="#" class="ui-btn"><span>Для бизнеса</span></a>
      </div>

      <div class="ui-image">
        <img src="<?php echo esc_url($uploads . '2026/01/vigoda.webp'); ?>" alt="">
      </div>
    </div>

  </div>
</section>
