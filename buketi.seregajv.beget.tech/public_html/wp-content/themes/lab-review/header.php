<?php
/**
 * Header (Lab-Review) — 3 уровня + sticky + fullscreen menu + inline SVG icons
 */
if (!defined('ABSPATH')) exit;

/* =========================
   Inline SVG helper
   assets/icons/*.svg
   ========================= */
if (!function_exists('lr_inline_svg')) {
  function lr_inline_svg($relative_path) {
    $file = get_stylesheet_directory() . '/' . ltrim($relative_path, '/');
    if (!file_exists($file)) return '';
    $svg = file_get_contents($file);
    return $svg ?: '';
  }
}

/* =========================
   Data
   ========================= */
$phone_raw  = '+7 (900) 000-00-00';
$phone_tel  = '+79000000000';
$work_hours = 'Режим работы: с 08:00 до 21:00';

$ticker_text = '1000 бонусных рублей при первом заказе на сайте · Доставка от 60 мин · Кешбэк с заказа до 15% · Авторские букеты и клубника в шоколаде';

$account_url = function_exists('wc_get_page_permalink') ? wc_get_page_permalink('myaccount') : wp_login_url();
$cart_url    = function_exists('wc_get_cart_url') ? wc_get_cart_url() : home_url('/');
$cart_count  = (function_exists('WC') && WC()->cart) ? WC()->cart->get_cart_contents_count() : 0;

$cats = [];
if (taxonomy_exists('product_cat')) {
  $cats = get_terms([
    'taxonomy'   => 'product_cat',
    'hide_empty' => true,
    'parent'     => 0,
    'orderby'    => 'menu_order',
    'order'      => 'ASC',
  ]);
}

$u = wp_get_upload_dir();
$uploads = trailingslashit($u['baseurl']);

/* Картинка для бургер-меню справа (опционально) */
$menu_img_rel = '2026/01/menu-flower.webp';
$menu_img_url = $uploads . $menu_img_rel;
$menu_img_fs  = trailingslashit($u['basedir']) . $menu_img_rel;
$menu_img     = file_exists($menu_img_fs) ? $menu_img_url : '';
?><!doctype html>
<html <?php language_attributes(); ?>>
<head>
  <meta charset="<?php bloginfo('charset'); ?>">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<header class="lr-header" id="lr-header">

  <!-- =====================
       1) TOP TICKER
       ===================== -->
  <div class="lr-header__ticker" role="region" aria-label="Уведомления">
    <div class="lr-ticker" aria-hidden="true">
      <div class="lr-ticker__track">
        <span class="lr-ticker__item"><?php echo esc_html($ticker_text); ?></span>
        <span class="lr-ticker__item"><?php echo esc_html($ticker_text); ?></span>
      </div>
    </div>
  </div>

  <!-- =====================
       2) MID ROW
       ===================== -->
  <div class="lr-header__mid">
    <div class="lr-container lr-header__mid-inner">

      <!-- LEFT -->
      <div class="lr-hleft">
        <button class="lr-burger" type="button"
          aria-label="Открыть меню"
          aria-controls="lr-menu"
          aria-expanded="false">
          <span></span><span></span><span></span>
        </button>

        <div class="lr-phone">
          <a class="lr-phone__number" href="tel:<?php echo esc_attr($phone_tel); ?>">
            <?php echo esc_html($phone_raw); ?>
          </a>
          <div class="lr-phone__hours"><?php echo esc_html($work_hours); ?></div>
        </div>

        <div class="lr-mswrap" aria-label="Мессенджеры">
          <a class="lr-ms lr-ms--wa" href="https://wa.me/" target="_blank" rel="noopener" aria-label="WhatsApp">
            <?php echo lr_inline_svg('assets/icons/whatsapp.svg'); ?>
          </a>

          <a class="lr-ms lr-ms--tg" href="https://t.me/" target="_blank" rel="noopener" aria-label="Telegram">
            <?php echo lr_inline_svg('assets/icons/telegram.svg'); ?>
          </a>
        </div>
      </div>

      <!-- CENTER -->
      <div class="lr-hcenter">
        <a class="lr-logo" href="<?php echo esc_url(home_url('/')); ?>">
          <?php echo esc_html(get_bloginfo('name')); ?>
        </a>

        <!-- Slot for sticky переноса навигации (JS) -->
        <div class="lr-midnav-slot" aria-label="Навигация (sticky)"></div>
      </div>

      <!-- RIGHT -->
      <div class="lr-hright">
        <button class="lr-iconbtn" type="button" aria-label="Поиск" aria-controls="lr-search" aria-expanded="false" data-lr-search-open>
          <?php echo lr_inline_svg('assets/icons/search.svg'); ?>
        </button>

        <a class="lr-iconbtn lr-accountbtn" href="<?php echo esc_url($account_url); ?>" aria-label="Личный кабинет">
          <?php echo lr_inline_svg('assets/icons/profile.svg'); ?>
        </a>

        <a class="lr-iconbtn lr-cartbtn" href="<?php echo esc_url($cart_url); ?>" aria-label="Корзина">
          <?php echo lr_inline_svg('assets/icons/cart.svg'); ?>
          <span class="lr-badge" aria-label="Товаров в корзине"><?php echo (int)$cart_count; ?></span>
        </a>
      </div>

    </div>
  </div>

  <!-- =====================
       3) CATEGORIES ROW
       ===================== -->
  <div class="lr-header__cats">
    <div class="lr-container">
      <nav class="lr-cats lr-cats--row" aria-label="Категории каталога">
        <?php if (!empty($cats) && !is_wp_error($cats)): ?>
          <?php foreach ($cats as $cat): ?>
            <a class="lr-cats__link" href="<?php echo esc_url(get_term_link($cat)); ?>">
              <?php echo esc_html($cat->name); ?>
            </a>
          <?php endforeach; ?>
        <?php else: ?>
          <span class="lr-cats__empty">Категории пока не настроены</span>
        <?php endif; ?>
      </nav>
    </div>
  </div>

</header>

<!-- =====================
     SEARCH PANEL
     ===================== -->
<div id="lr-search" class="lr-searchpanel" aria-hidden="true">
  <div class="lr-searchpanel__backdrop" data-lr-search-close></div>

  <div class="lr-searchpanel__box" role="dialog" aria-modal="true" aria-label="Поиск">
    <div class="lr-searchpanel__top">
      <div class="lr-searchpanel__title">Поиск</div>
      <button class="lr-searchpanel__close" type="button" aria-label="Закрыть поиск" data-lr-search-close>×</button>
    </div>

    <div class="lr-searchpanel__content">
      <?php
      if (function_exists('get_product_search_form')) {
        get_product_search_form();
      } else {
        get_search_form();
      }
      ?>
    </div>
  </div>
</div>

<!-- =====================
     FULLSCREEN MENU
     Не перекрывает шапку: top = --lr-menu-top
     ===================== -->
<div id="lr-menu" class="lr-menu" aria-hidden="true">
  <div class="lr-menu__backdrop" data-lr-menu-close></div>

  <div class="lr-menu__panel" role="dialog" aria-modal="true" aria-label="Меню">
    <div class="lr-container lr-menu__inner">

      <nav class="lr-menu__nav" aria-label="Основная навигация">
        <?php
        wp_nav_menu([
          'theme_location' => 'primary',
          'container'      => false,
          'fallback_cb'    => '__return_empty_string',
          'menu_class'     => 'lr-menu__list',
        ]);
        ?>
      </nav>

      <div class="lr-menu__side" aria-label="Блок справа">
        <div class="lr-menu__image">
          <?php if (!empty($menu_img)): ?>
            <img src="<?php echo esc_url($menu_img); ?>" alt="" loading="lazy">
          <?php endif; ?>
        </div>
      </div>

    </div>
  </div>
</div>
