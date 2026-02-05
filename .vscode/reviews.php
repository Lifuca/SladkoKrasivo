<style>
.yandex-reviews {
  padding: 60px 0;
}

.yandex-reviews__inner {
  max-width: 1140px;
  margin: 0 auto;
  padding: 0 20px;
}

.yandex-reviews__widget {
  width: 100%;
  max-width: 760px;
  margin: 0 auto;
  position: relative;
  overflow: hidden;
}

.yandex-reviews__widget iframe {
  width: 100%;
  height: 420px;
  border: 1px solid #E1EDEB;
  border-radius: 8px;
  box-sizing: border-box;
  display: block;
  background: #fff;
  transition: height 0.45s ease;
}

.yandex-reviews__widget a {
  position: absolute;
  left: 0;
  bottom: 8px;
  width: 100%;
  text-align: center;
  font-size: 10px;
  font-family: "YS Text", Arial, sans-serif;
  color: #E1EDEB;
  text-decoration: none;
}

@media (hover: hover) {
  .yandex-reviews__widget:hover iframe {
    height: 840px;
  }
}

@media (hover: none) {
  .yandex-reviews__widget iframe {
    height: 700px;
  }
}

@media (max-width: 768px) {
  .yandex-reviews__widget iframe {
    height: 360px;
  }
}
</style>

<section class="yandex-reviews">
  <div class="yandex-reviews__inner">
    <div class="yandex-reviews__widget">
      <iframe
        src="https://yandex.ru/maps-reviews-widget/180594864669?comments"
        loading="lazy"
        referrerpolicy="no-referrer-when-downgrade">
      </iframe>
      <a
        href="https://yandex.ru/maps/org/ptichka_nevelichka/180594864669/"
        target="_blank"
        rel="noopener">
        Любая организация на Яндекс Картах
      </a>
    </div>
  </div>
</section>
