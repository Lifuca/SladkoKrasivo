<?php
/**
 * Site Footer Block
 * Shortcode + enqueue + HTML
 */

if ( ! defined('ABSPATH') ) {
  exit;
}

add_shortcode('site_footer', 'render_site_footer');

function render_site_footer() {

  wp_enqueue_style(
    'site-footer-css',
    get_stylesheet_directory_uri() . '/assets/css/site-footer.css',
    [],
    '1.2'
  );

  ob_start();
  ?>

  <footer class="site-footer">

    <div class="footer-container">

      <!-- LEFT / WIDGET -->
      <div class="footer-widget-col">

        <!-- YANDEX MAPS REVIEWS -->
        <div class="footer-rating-widget">
          <iframe
            src="https://yandex.ru/maps-reviews-widget/180594864669?comments"
            loading="lazy"
            referrerpolicy="no-referrer-when-downgrade">
          </iframe>

          <a
            href="https://yandex.ru/maps/org/ptichka_nevelichka/180594864669/"
            target="_blank"
            rel="noopener">
            Читать отзывы на Яндекс Картах
          </a>
        </div>

        <!-- SOCIALS -->
        <div class="footer-socials">
          <a href="#" aria-label="VK">VK</a>
          <a href="#" aria-label="WhatsApp">WA</a>
          <a href="#" aria-label="Telegram">TG</a>
        </div>

      </div>

      <!-- NAV -->
      <div class="footer-nav">

        <div class="footer-col">
          <h4>Каталог</h4>
          <ul>
            <li><a href="#">Клубничные букеты</a></li>
            <li><a href="#">Клубничные боксы</a></li>
            <li><a href="#">Цветы</a></li>
            <li><a href="#">Комбо-наборы</a></li>
          </ul>
        </div>

        <div class="footer-col">
          <h4>Сервис</h4>
          <ul>
            <li><a href="#">Доставка</a></li>
            <li><a href="#">Корпоративным клиентам</a></li>
            <li><a href="#">Вопросы и ответы</a></li>
            <li><a href="#">Оплата</a></li>
            <li><a href="#">Возврат</a></li>
            <li><a href="#">Программа лояльности</a></li>
          </ul>
        </div>

        <div class="footer-col">
          <h4>Компания</h4>
          <ul>
            <li><a href="#">О нас</a></li>
            <li><a href="#">Видео</a></li>
            <li><a href="#">Контакты</a></li>
            <li><a href="#">Новости</a></li>
            <li><a href="#">Статьи</a></li>
            <li><a href="#">Праздники</a></li>
          </ul>
        </div>

      </div>

    </div>

  </footer>

  <!-- LEGAL BAR -->
  <div class="footer-legal-bar">
    <div class="footer-legal-inner">
      <span>© <?php echo date('Y'); ?>. Все права защищены.</span>
      <a href="#">Политика конфиденциальности</a>
      <a href="#">Публичная оферта</a>
    </div>
  </div>

  <?php
  return ob_get_clean();
}
