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

          <button class="lr-btn lr-btn--ghost" type="button" data-lr-combo>
            Собрать комбо -10%
          </button>
        </div>

        <div class="lr-sp__short">
          <?php woocommerce_template_single_excerpt(); ?>
        </div>

        <div class="lr-sp__desc">
          <?php the_content(); ?>
        </div>
      </div>
    </section>

    <!-- USP blocks -->
    <section class="lr-sp-usps" aria-label="Преимущества">
      <div class="lr-sp-usps__grid">
        <article class="lr-sp-usp">
          <div class="lr-sp-usp__ico" aria-hidden="true">✓</div>
          <div class="lr-sp-usp__t">Гарантия качества</div>
          <div class="lr-sp-usp__d">72 часа гарантии свежести на каждый букет. Если что-то не так — заменим.</div>
        </article>

        <article class="lr-sp-usp">
          <div class="lr-sp-usp__ico" aria-hidden="true">📷</div>
          <div class="lr-sp-usp__t">Фотоконтроль</div>
          <div class="lr-sp-usp__d">Отправим фото вашего заказа перед доставкой в удобный мессенджер.</div>
        </article>

        <article class="lr-sp-usp">
          <div class="lr-sp-usp__ico" aria-hidden="true">🎁</div>
          <div class="lr-sp-usp__t">Подарок для вас</div>
          <div class="lr-sp-usp__d">Дарим бонусы на следующие покупки — приятно возвращаться.</div>
        </article>

        <article class="lr-sp-usp">
          <div class="lr-sp-usp__ico" aria-hidden="true">%</div>
          <div class="lr-sp-usp__t">Кешбэк</div>
          <div class="lr-sp-usp__d">Начисляем бонусы после покупки — используйте их в личном кабинете.</div>
        </article>
      </div>
    </section>

    <?php woocommerce_output_related_products(); ?>

  </div>
</main>

<?php
endwhile;

get_footer();
