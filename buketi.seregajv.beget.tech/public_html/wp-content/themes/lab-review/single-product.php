<?php
defined('ABSPATH') || exit;

get_header();

while (have_posts()) : the_post();
  global $product;
  $product = wc_get_product(get_the_ID());
  if (!$product) break;

  // бонусы (как число, допускаем дроби)
  $bonus_raw = $product->get_meta('_lr_bonus_points');
  $bonus = is_numeric($bonus_raw) ? (float)$bonus_raw : 0;

  // изображения
  $image_ids = [];
  $main_id = (int) $product->get_image_id();
  if ($main_id) $image_ids[] = $main_id;
  $gallery_ids = $product->get_gallery_image_ids();
  if (!empty($gallery_ids)) {
    foreach ($gallery_ids as $gid) {
      $gid = (int)$gid;
      if ($gid && !in_array($gid, $image_ids, true)) $image_ids[] = $gid;
    }
  }

  // fallback: если нет картинок — плейсхолдер Woo
  if (empty($image_ids)) {
    $placeholder = wc_placeholder_img_src('woocommerce_single');
  }

  // URLs для 4 картинок блока "как у референса"
  $packaging_img = home_url('/wp-content/uploads/2026/02/upakovka.png');
  $card_img      = home_url('/wp-content/uploads/2026/02/otkritka.png');
  $carry_img     = home_url('/wp-content/uploads/2026/02/perenoska.png');
  $manual_img    = home_url('/wp-content/uploads/2026/02/insrukciya.png');
?>

<main class="lr-main lr-main--product">
  <div class="lr-container">

    <div class="lr-sp-breadcrumbs">
      <?php if (function_exists('woocommerce_breadcrumb')) woocommerce_breadcrumb(); ?>
    </div>

    <?php woocommerce_output_all_notices(); ?>

    <section class="lr-sp" data-lr-sp>
      <!-- LEFT: Gallery -->
      <div class="lr-sp__left">

        <div class="lr-sp-gal">
          <div class="lr-sp-gal__stage">

            <button class="lr-sp-gal__nav lr-sp-gal__nav--prev" type="button" aria-label="Предыдущее фото" data-lr-sp-prev>
              <span aria-hidden="true">‹</span>
            </button>

            <button class="lr-sp-gal__nav lr-sp-gal__nav--next" type="button" aria-label="Следующее фото" data-lr-sp-next>
              <span aria-hidden="true">›</span>
            </button>

            <div class="lr-sp-gal__track" data-lr-sp-track>
              <?php if (!empty($image_ids)) : ?>
                <?php foreach ($image_ids as $idx => $img_id) :
                  $src = wp_get_attachment_image_url($img_id, 'large');
                  $srcset = wp_get_attachment_image_srcset($img_id, 'large');
                  $sizes = '(max-width: 1020px) 100vw, 620px';
                  $alt = trim(get_post_meta($img_id, '_wp_attachment_image_alt', true));
                  if ($alt === '') $alt = get_the_title();
                ?>
                  <div class="lr-sp-gal__slide" data-lr-sp-slide data-idx="<?php echo esc_attr($idx); ?>">
                    <img
                      class="lr-sp-gal__img"
                      src="<?php echo esc_url($src); ?>"
                      <?php if (!empty($srcset)) : ?>srcset="<?php echo esc_attr($srcset); ?>" sizes="<?php echo esc_attr($sizes); ?>"<?php endif; ?>
                      alt="<?php echo esc_attr($alt); ?>"
                      loading="<?php echo $idx === 0 ? 'eager' : 'lazy'; ?>"
                      decoding="async"
                    />
                  </div>
                <?php endforeach; ?>
              <?php else : ?>
                <div class="lr-sp-gal__slide" data-lr-sp-slide data-idx="0">
                  <img class="lr-sp-gal__img" src="<?php echo esc_url($placeholder); ?>" alt="<?php echo esc_attr(get_the_title()); ?>" loading="eager" decoding="async" />
                </div>
              <?php endif; ?>
            </div>
          </div>

          <?php if (!empty($image_ids) && count($image_ids) > 1) : ?>
            <div class="lr-sp-gal__thumbs" data-lr-sp-thumbs>
              <?php foreach ($image_ids as $idx => $img_id) :
                $thumb = wp_get_attachment_image_url($img_id, 'woocommerce_gallery_thumbnail');
                if (!$thumb) $thumb = wp_get_attachment_image_url($img_id, 'thumbnail');
                $alt = trim(get_post_meta($img_id, '_wp_attachment_image_alt', true));
                if ($alt === '') $alt = get_the_title();
              ?>
                <button class="lr-sp-gal__thumb <?php echo $idx === 0 ? 'is-active' : ''; ?>" type="button" aria-label="Фото <?php echo esc_attr($idx + 1); ?>" data-lr-sp-thumb data-idx="<?php echo esc_attr($idx); ?>">
                  <img class="lr-sp-gal__thumb-img" src="<?php echo esc_url($thumb); ?>" alt="<?php echo esc_attr($alt); ?>" loading="lazy" decoding="async" />
                </button>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>

        </div>
      </div>

      <!-- RIGHT: Summary -->
      <div class="lr-sp__right">
        <h1 class="lr-sp__title"><?php the_title(); ?></h1>

        <div class="lr-sp__meta">
          <div class="lr-sp__price">
            <?php echo wp_kses_post($product->get_price_html()); ?>
          </div>

          <?php if ($bonus > 0) : ?>
            <div class="lr-sp__bonus" title="Бонусы за покупку">
              + бонус <span class="lr-sp__bonus-val"><?php echo esc_html(rtrim(rtrim(number_format($bonus, 2, '.', ''), '0'), '.')); ?>₽</span>
              <span class="lr-sp__bonus-q" aria-hidden="true">?</span>
            </div>
          <?php endif; ?>
        </div>

        <div class="lr-sp__actions">
          <div class="lr-sp__add">
            <?php woocommerce_template_single_add_to_cart(); ?>
          </div>

          <button class="lr-btn lr-btn--ghost" type="button" data-lr-combo aria-controls="lrComboModal" aria-haspopup="dialog">
            Собрать комбо -10%
          </button>
        </div>

        <div class="lr-sp__note">
          Состав букета может быть незначительно изменен. При этом стилистика и цветовая гамма останутся неизменными.
        </div>

        <div class="lr-sp__short">
          <?php woocommerce_template_single_excerpt(); ?>
        </div>

        <div class="lr-sp__desc">
          <?php the_content(); ?>
        </div>
      </div>
    </section>

    <!-- BLOCK: 4 cards like reference -->
    <section class="lr-sp-ref" aria-label="Комплектация">
      <div class="lr-sp-ref__grid">
        <figure class="lr-sp-ref__item">
          <div class="lr-sp-ref__imgwrap">
            <img class="lr-sp-ref__img" src="<?php echo esc_url($packaging_img); ?>" alt="Фирменная дизайнерская упаковка" loading="lazy" decoding="async">
          </div>
          <figcaption class="lr-sp-ref__cap">Фирменная дизайнерская упаковка</figcaption>
        </figure>

        <figure class="lr-sp-ref__item">
          <div class="lr-sp-ref__imgwrap">
            <img class="lr-sp-ref__img" src="<?php echo esc_url($card_img); ?>" alt="Записка с теплыми словами" loading="lazy" decoding="async">
          </div>
          <figcaption class="lr-sp-ref__cap">Записка с теплыми словами</figcaption>
        </figure>

        <figure class="lr-sp-ref__item">
          <div class="lr-sp-ref__imgwrap">
            <img class="lr-sp-ref__img" src="<?php echo esc_url($carry_img); ?>" alt="Переноска и аквабокс для цветов" loading="lazy" decoding="async">
          </div>
          <figcaption class="lr-sp-ref__cap">Переноска и аквабокс для цветов</figcaption>
        </figure>

        <figure class="lr-sp-ref__item">
          <div class="lr-sp-ref__imgwrap">
            <img class="lr-sp-ref__img" src="<?php echo esc_url($manual_img); ?>" alt="Инструкция о хранении" loading="lazy" decoding="async">
          </div>
          <figcaption class="lr-sp-ref__cap">Инструкция о хранении</figcaption>
        </figure>
      </div>
    </section>

    <!-- Yandex reviews block -->
    <?php get_template_part('template-parts/yandex-reviews'); ?>

    <!-- Related products (moved here) -->
    <?php woocommerce_output_related_products(); ?>

    <!-- Info blocks like screenshot -->
    <section class="lr-sp-info" aria-label="Информация">
      <div class="lr-sp-info__top">
        <div class="lr-sp-info__topgrid">
          <div class="lr-sp-info__topitem">
            <div class="lr-sp-info__ico" aria-hidden="true">★</div>
            <div class="lr-sp-info__t">Гарантия качества</div>
            <div class="lr-sp-info__d">Поменяем букет или вернём деньги если что-то пошло не так</div>
          </div>

          <div class="lr-sp-info__topitem">
            <div class="lr-sp-info__ico" aria-hidden="true">📷</div>
            <div class="lr-sp-info__t">Фотоконтроль</div>
            <div class="lr-sp-info__d">Отправляем фото заказа перед доставкой в любой мессенджер</div>
          </div>

          <div class="lr-sp-info__topitem">
            <div class="lr-sp-info__ico" aria-hidden="true">🎁</div>
            <div class="lr-sp-info__t">Доставка</div>
            <div class="lr-sp-info__d">Доставим в указанное время и оповестим о выполнении заказа по SMS</div>
          </div>

          <div class="lr-sp-info__topitem">
            <div class="lr-sp-info__ico" aria-hidden="true">%</div>
            <div class="lr-sp-info__t">Кешбэк до 15%</div>
            <div class="lr-sp-info__d">Возвращаем до 15% бонусами в личный кабинет от каждого заказа</div>
          </div>
        </div>
      </div>

      <div class="lr-sp-info__bottom">
        <div class="lr-sp-info__grid">
          <article class="lr-sp-info__cell">
            <h3 class="lr-sp-info__h">Условия доставки</h3>
            <p class="lr-sp-info__p">Доставка по Саратову — от 300 руб. Стоимость доставки в отдаленные районы рассчитывается индивидуально менеджером.</p>
          </article>

          <article class="lr-sp-info__cell">
            <h3 class="lr-sp-info__h">Срок хранения</h3>
            <p class="lr-sp-info__p">Срок годности клубники в шоколаде — 12 часов, клубники без шоколада — 24 часа. Ягоду необходимо хранить в холодильнике при температуре +4…+7 градусов. Не держите клубнику на солнце или в тепле.</p>
          </article>

          <article class="lr-sp-info__cell">
            <h3 class="lr-sp-info__h">Интервал доставки</h3>
            <p class="lr-sp-info__p">Доставка цветов по Саратову осуществляется в часовом интервале. Самая ранняя доставка с 09:00 до 10:00, самая поздняя с 20:00 до 21:00.</p>
          </article>

          <article class="lr-sp-info__cell">
            <h3 class="lr-sp-info__h">Способ оплаты</h3>
            <p class="lr-sp-info__p">Мы работаем по 100% предоплате. Оплата производится онлайн после подтверждения заказа менеджером. После оформления заказа с Вами свяжется менеджер и проконсультирует.</p>
          </article>
        </div>
      </div>
    </section>

    <!-- Dual promo -->
    <?php get_template_part('template-parts/dual-promo'); ?>

  </div>
<?php get_template_part('template-parts/combo-modal'); ?>
</main>

<?php
endwhile;

get_footer();







