<?php
if ( ! defined('ABSPATH') ) exit;
?>

<footer class="site-footer">

  <div class="footer-container">

    <!-- LEFT -->
    <div class="footer-left">

      <div class="footer-logo">
        <img src="<?php echo esc_url(get_stylesheet_directory_uri() . '/assets/img/logo-footer.svg'); ?>" alt="LabReview">
      </div>

      <div class="footer-socials">
        <a href="#" class="social-link">Telegram</a>
        <a href="#" class="social-link">WhatsApp</a>
      </div>

      <div class="footer-contacts">
        <a class="footer-phone" href="tel:+79999999999">+7 (999) 999-99-99</a>
        <a class="footer-mail" href="mailto:info@site.ru">info@site.ru</a>
      </div>

      <div class="footer-payments">
        <span>Visa</span>
        <span>Mastercard</span>
        <span>Mir</span>
      </div>

    </div>

    <!-- RIGHT -->
    <div class="footer-right">

      <div class="footer-cols">

        <div class="footer-col">
          <h4>Покупателям</h4>
          <ul>
            <li><a href="#">Доставка</a></li>
            <li><a href="#">Оплата</a></li>
            <li><a href="#">Бонусы</a></li>
            <li><a href="#">Промокоды</a></li>
            <li><a href="#">Вопросы</a></li>
          </ul>
        </div>

        <div class="footer-col">
          <h4>Каталог</h4>
          <ul>
            <li><a href="#">Букеты</a></li>
            <li><a href="#">Комбо</a></li>
            <li><a href="#">Наборы</a></li>
            <li><a href="#">Подарки</a></li>
            <li><a href="#">Акции</a></li>
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

  </div>

</footer>

<?php wp_footer(); ?>
</body>
</html>
