<?php
/**
 * Template Part: Hero (под hero.css)
 */
if (!defined('ABSPATH')) exit;

$u = wp_get_upload_dir();
$uploads = trailingslashit($u['baseurl']);
?>

<section class="hero">
  <div class="hero-inner">

    <!-- LEFT: SLIDER -->
    <div class="hero-slider">
      <div class="swiper hero-swiper">
        <div class="swiper-wrapper">

          <div class="swiper-slide">
            <div class="hero-slide">
              <img class="hero-slide-bg" src="<?php echo esc_url($uploads . '2026/01/main-slider2.webp'); ?>" alt="Бонусная программа" loading="lazy">
              <div class="hero-slide-content">
                <h2>ВЕРНЁМ ДО 15%<br>БОНУСАМИ</h2>
                <p>А также подарим 300 рублей за установку электронной карты клиента, которые можно списать на первый заказ.</p>
                <a href="#" class="hero-btn">Получить бонус</a>
              </div>
            </div>
          </div>

          <div class="swiper-slide">
            <div class="hero-slide">
              <img class="hero-slide-bg" src="<?php echo esc_url($uploads . '2026/01/main-slider3.webp'); ?>" alt="Акции и спецпредложения" loading="lazy">
              <div class="hero-slide-content">
                <h2>АКЦИИ И<br>СПЕЦПРЕДЛОЖЕНИЯ</h2>
                <p>Подписывайтесь на наши соцсети — там появляются самые выгодные предложения и новые поступления.</p>
                <a href="#" class="hero-btn">Смотреть акции</a>
              </div>
            </div>
          </div>

          <div class="swiper-slide">
            <div class="hero-slide">
              <img class="hero-slide-bg" src="<?php echo esc_url($uploads . '2026/01/main-slider1.webp'); ?>" alt="Подарки на любой случай" loading="lazy">
              <div class="hero-slide-content">
                <h2>ПОДАРКИ<br>НА ЛЮБОЙ СЛУЧАЙ</h2>
                <p>Подберём набор под событие и бюджет — красиво упакуем и доставим.</p>
                <a href="#" class="hero-btn">Выбрать подарок</a>
              </div>
            </div>
          </div>

        </div>

        <!-- Навигация/пагинация можно оставить, если у тебя это использует hero.js -->
        <div class="hero-pagination"></div>
      </div>
    </div>

    <!-- RIGHT: OFFERS (под твой CSS “шахматкой”) -->
    <div class="hero-offers">

      <div class="hero-col hero-col--left">
        <a class="hero-offer" href="#">
          <img src="<?php echo esc_url($uploads . '2026/01/6c4636a42550f40bcb8f5ea3bc5781e8.webp'); ?>" alt="" loading="lazy">
          <span>Букеты</span>
        </a>

        <a class="hero-offer" href="#">
          <img src="<?php echo esc_url($uploads . '2026/01/img_20251210_164101.jpg'); ?>" alt="" loading="lazy">
          <span>Клубника</span>
        </a>
      </div>

      <div class="hero-col hero-col--right">
        <a class="hero-offer" href="#">
          <img src="<?php echo esc_url($uploads . '2026/01/9e92ab270a78f5e46d0a03a036f79809.jpg.webp'); ?>" alt="" loading="lazy">
          <span>Наборы</span>
        </a>

        <a class="hero-offer" href="#">
          <img src="<?php echo esc_url($uploads . '2026/01/1757683995_10491849.jpg'); ?>" alt="" loading="lazy">
          <span>Акции</span>
        </a>
      </div>

    </div>

  </div>
</section>
