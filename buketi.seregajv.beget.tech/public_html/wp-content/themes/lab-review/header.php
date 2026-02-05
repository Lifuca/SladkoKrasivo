<?php
/**
 * Header (Lab-Review) — 3 уровня + sticky one-line + fullscreen menu
 */
if (!defined('ABSPATH')) exit;

$phone_raw  = '+7 (845) 239-77-23';
$phone_tel  = '+78452397723';
$work_hours = 'Режим работы: с 08:00 до 21:00';

$ticker_text = '1000 бонусных рублей при первом заказе на сайте · Доставка от 60 мин · Гарантия на цветы 3 дня · Кешбэк с заказа до 15% · Клубника в шоколаде и авторские букеты';

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

// картинка для меню (поставь реальный файл, если нужно)
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

  <!-- ===== LEVEL 1: TICKER ===== -->
  <div class="lr-header__ticker" role="region" aria-label="Уведомления">
    <div class="lr-ticker" aria-hidden="true">
      <div class="lr-ticker__track">
        <span class="lr-ticker__item"><?php echo esc_html($ticker_text); ?></span>
        <span class="lr-ticker__item"><?php echo esc_html($ticker_text); ?></span>
      </div>
    </div>
  </div>

  <!-- ===== LEVEL 2 ===== -->
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

        <!-- эти блоки скрываются в sticky, но в обычном режиме остаются -->
        <div class="lr-phone">
          <a class="lr-phone__number" href="tel:<?php echo esc_attr($phone_tel); ?>">
            <?php echo esc_html($phone_raw); ?>
          </a>
          <div class="lr-phone__hours"><?php echo esc_html($work_hours); ?></div>
        </div>

        <div class="lr-mswrap" aria-label="Мессенджеры">
          <a class="lr-ms lr-ms--wa" href="https://wa.me/" target="_blank" rel="noopener" aria-label="WhatsApp">
            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M20.52 3.48A11.9 11.9 0 0 0 12.03 0C5.4 0 .03 5.37.03 12c0 2.12.55 4.19 1.6 6.02L0 24l6.18-1.6a11.93 11.93 0 0 0 5.85 1.49h.01c6.63 0 12-5.37 12-12 0-3.2-1.25-6.2-3.52-8.41zM12.04 21.9h-.01a9.9 9.9 0 0 1-5.04-1.38l-.36-.21-3.66.95.98-3.56-.23-.37A9.86 9.86 0 0 1 2.1 12c0-5.46 4.45-9.9 9.92-9.9 2.65 0 5.14 1.03 7.02 2.9a9.86 9.86 0 0 1 2.9 7c0 5.46-4.45 9.9-9.9 9.9zm5.44-7.41c-.3-.15-1.77-.87-2.05-.97-.28-.1-.48-.15-.68.15-.2.3-.78.97-.96 1.17-.18.2-.35.22-.65.07-.3-.15-1.25-.46-2.38-1.48-.88-.79-1.47-1.77-1.64-2.07-.17-.3-.02-.46.13-.61.13-.13.3-.35.45-.53.15-.18.2-.3.3-.5.1-.2.05-.37-.03-.52-.08-.15-.68-1.64-.93-2.25-.24-.58-.48-.5-.68-.5h-.58c-.2 0-.52.07-.8.37-.28.3-1.05 1.03-1.05 2.52 0 1.49 1.08 2.93 1.23 3.13.15.2 2.12 3.24 5.14 4.55.72.31 1.28.5 1.72.64.72.23 1.38.2 1.9.12.58-.09 1.77-.72 2.02-1.42.25-.7.25-1.3.18-1.42-.07-.12-.27-.2-.57-.35z"/></svg>
          </a>

          <a class="lr-ms lr-ms--tg" href="https://t.me/" target="_blank" rel="noopener" aria-label="Telegram">
            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M9.95 15.53 9.7 19.1c.36 0 .52-.15.72-.34l1.73-1.65 3.59 2.63c.66.36 1.13.17 1.3-.61l2.36-11.1c.2-.93-.34-1.3-.98-1.06L3.25 10.1c-.9.36-.88.86-.15 1.09l4.22 1.32 9.79-6.17c.46-.28.88-.13.54.15l-7.7 7.04z"/></svg>
          </a>
        </div>
      </div>

      <!-- CENTER: в обычном режиме логотип, в sticky — меню категорий -->
      <div class="lr-hcenter">
        <a class="lr-logo" href="<?php echo esc_url(home_url('/')); ?>">
          <?php echo esc_html(get_bloginfo('name')); ?>
        </a>

       
        <div class="lr-midnav-slot" aria-label="Навигация (sticky)"></div>
      </div>


      <!-- RIGHT: в sticky оставляем ЛУПУ + АККАУНТ + КОРЗИНУ -->
      <div class="lr-hright">
        <button class="lr-iconbtn" type="button" aria-label="Поиск" aria-controls="lr-search" aria-expanded="false" data-lr-search-open>
          <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M10 2a8 8 0 1 1 5.29 13.99l4.36 4.36-1.41 1.41-4.36-4.36A8 8 0 0 1 10 2Zm0 2a6 6 0 1 0 .01 12.01A6 6 0 0 0 10 4Z"/></svg>
        </button>

        <a class="lr-iconbtn lr-accountbtn" href="<?php echo esc_url($account_url); ?>" aria-label="Личный кабинет">
          <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 12a5 5 0 1 0-5-5 5 5 0 0 0 5 5Zm0 2c-4.42 0-8 2.24-8 5v1h16v-1c0-2.76-3.58-5-8-5Z"/></svg>
        </a>

        <a class="lr-iconbtn lr-cartbtn" href="<?php echo esc_url($cart_url); ?>" aria-label="Корзина">
          <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M7 4h-2l-1 2v2h2l3.6 7.59-1.35 2.44A2 2 0 0 0 10 22h10v-2H10l1.1-2H18a2 2 0 0 0 1.8-1.1l3.2-6.4A1 1 0 0 0 22 9H7.42L6.27 6H22V4H7Zm3 18a2 2 0 1 0-2-2 2 2 0 0 0 2 2Zm8 0a2 2 0 1 0-2-2 2 2 0 0 0 2 2Z"/></svg>
          <span class="lr-badge" aria-label="Товаров в корзине"><?php echo (int)$cart_count; ?></span>
        </a>
      </div>

    </div>
  </div>

  <!-- ===== LEVEL 3: CATEGORIES (обычный режим) ===== -->
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

<!-- ===== SEARCH PANEL ===== -->
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

<!-- ===== FULLSCREEN MENU (не перекрывает шапку) ===== -->
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
