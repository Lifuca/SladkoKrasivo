<?php
defined('ABSPATH') || exit;

/**
 * Combo modal — template part (UI v2 + data for JS totals)
 * - “Добавить букет / клубнику / подарок” opens inline picker (no redirect)
 * - picker renders products from product_cat slugs (change slugs if needed)
 * - each product card button has data-price (number)
 * - modal root has data-base-price (current product price) for totals
 */

function lr_combo_get_products_by_cat_slug($cat_slug, $limit = 12){
  if (!function_exists('wc_get_products')) return [];
  return wc_get_products([
    'status'   => 'publish',
    'limit'    => $limit,
    'orderby'  => 'date',
    'order'    => 'DESC',
    'category' => [$cat_slug],
  ]);
}

/** CHANGE THESE SLUGS if your product categories use other slugs */
$combo_cats = [
  'flowers' => 'tsvety',
  'berry'   => 'klubnika-v-shokolade',
  'gifts'   => 'podarki',
];

$panels = [
  'flowers' => [
    'title'    => 'Выберите букет',
    'products' => lr_combo_get_products_by_cat_slug($combo_cats['flowers'], 12),
  ],
  'berry' => [
    'title'    => 'Выберите клубнику',
    'products' => lr_combo_get_products_by_cat_slug($combo_cats['berry'], 12),
  ],
  'gifts' => [
    'title'    => 'Выберите подарок',
    'products' => lr_combo_get_products_by_cat_slug($combo_cats['gifts'], 12),
  ],
];

/** Base product price (current product) */
$base_price = 0;
if (function_exists('wc_get_product')) {
  $base = wc_get_product(get_the_ID());
  if ($base) $base_price = (float) $base->get_price();
}

$combo_img = home_url('/wp-content/uploads/2026/02/kombo.png');
?>

<div class="lr-combo"
     id="lrComboModal"
     data-lr-combo-modal
     data-ajax-url="<?php echo esc_url(admin_url('admin-ajax.php')); ?>"
     data-nonce="<?php echo esc_attr(wp_create_nonce('lr_combo_nonce')); ?>"
     data-base-product-id="<?php echo esc_attr(get_the_ID()); ?>"
     data-base-price="<?php echo esc_attr($base_price); ?>"
     hidden>

  <div class="lr-combo__overlay" data-lr-combo-close></div>

  <div class="lr-combo__dialog" role="dialog" aria-modal="true" aria-label="Собери комбо">
    <button class="lr-combo__close" type="button" aria-label="Закрыть" data-lr-combo-close>×</button>

    <div class="lr-combo__grid">

      <!-- LEFT -->
      <div class="lr-combo__left">

        <!-- Default (image + usps) -->
        <div class="lr-combo__left-default" data-lr-combo-left-default>
          <div class="lr-combo__imgwrap">
            <img class="lr-combo__img"
                 src="<?php echo esc_url($combo_img); ?>"
                 alt="Комбо"
                 loading="lazy"
                 decoding="async">
          </div>

          <!-- USP: серые SVG (mask через CSS) -->
          <div class="lr-combo-usps" aria-label="Преимущества">
            <div class="lr-combo-usps__grid">
              <article class="lr-combo-usp">
                <div class="lr-combo-usp__ico lr-ico lr-ico--zvezda" aria-hidden="true"></div>
                <div class="lr-combo-usp__t">Гарантия качества</div>
                <div class="lr-combo-usp__d">72 часа гарантии свежести на каждый букет. Если что-то не так — заменим.</div>
              </article>

              <article class="lr-combo-usp">
                <div class="lr-combo-usp__ico lr-ico lr-ico--kamera" aria-hidden="true"></div>
                <div class="lr-combo-usp__t">Фотоконтроль</div>
                <div class="lr-combo-usp__d">Отправим фото вашего заказа перед доставкой в удобный мессенджер.</div>
              </article>

              <article class="lr-combo-usp">
                <div class="lr-combo-usp__ico lr-ico lr-ico--podarok" aria-hidden="true"></div>
                <div class="lr-combo-usp__t">Подарок для вас</div>
                <div class="lr-combo-usp__d">Дарим бонусы на следующие покупки — приятно возвращаться.</div>
              </article>

              <article class="lr-combo-usp">
                <div class="lr-combo-usp__ico lr-ico lr-ico--procent" aria-hidden="true"></div>
                <div class="lr-combo-usp__t">Кешбэк</div>
                <div class="lr-combo-usp__d">Начисляем бонусы после покупки — используйте их в личном кабинете.</div>
              </article>
            </div>
          </div>
        </div>

        <!-- Picker (products) -->
        <div class="lr-combo-pick" data-lr-combo-pick hidden>
          <div class="lr-combo-pick__top">
            <button class="lr-combo-pick__back" type="button" data-lr-combo-back>← Назад</button>
            <div class="lr-combo-pick__ttl" data-lr-combo-pick-title>Выберите</div>
          </div>

          <div class="lr-combo-pick__panels">
            <?php foreach ($panels as $key => $panel) : ?>
              <div class="lr-combo-panel" data-lr-combo-panel="<?php echo esc_attr($key); ?>" hidden>
                <div class="lr-combo-grid" role="list">
                  <?php if (!empty($panel['products'])) : ?>
                    <?php foreach ($panel['products'] as $p) :
                      /** @var WC_Product $p */
                      $pid   = $p->get_id();
                      $name  = $p->get_name();
                      $link  = get_permalink($pid);
                      $img   = $p->get_image_id()
                        ? wp_get_attachment_image_url($p->get_image_id(), 'woocommerce_thumbnail')
                        : wc_placeholder_img_src();
                      $price_html = $p->get_price_html();
                      $price_num  = (float) $p->get_price();
                    ?>
                      <article class="lr-combo-card" role="listitem" data-lr-combo-card data-product-id="<?php echo esc_attr($pid); ?>">
                        <a class="lr-combo-card__img" href="<?php echo esc_url($link); ?>" tabindex="-1" aria-hidden="true">
                          <img src="<?php echo esc_url($img); ?>" alt="<?php echo esc_attr($name); ?>" loading="lazy" decoding="async">
                        </a>

                        <div class="lr-combo-card__body">
                          <div class="lr-combo-card__name"><?php echo esc_html($name); ?></div>
                          <div class="lr-combo-card__price"><?php echo wp_kses_post($price_html); ?></div>

                          <button class="lr-combo-card__btn" type="button"
                                  data-lr-combo-select="<?php echo esc_attr($key); ?>"
                                  data-product-id="<?php echo esc_attr($pid); ?>"
                                  data-price="<?php echo esc_attr($price_num); ?>">
                            Выбрать
                          </button>
                        </div>
                      </article>
                    <?php endforeach; ?>
                  <?php else : ?>
                    <div class="lr-combo-empty">
                      Нет товаров в категории (проверь slug: <strong><?php echo esc_html($combo_cats[$key]); ?></strong>)
                    </div>
                  <?php endif; ?>
                </div>
              </div>
            <?php endforeach; ?>
          </div>

          <div class="lr-combo-pick__foot">
            <div class="lr-combo-pick__hint">Выберите товар — скидка +2,5% за каждую категорию.</div>
          </div>
        </div>

      </div>

      <!-- RIGHT -->
      <div class="lr-combo__right">
        <div class="lr-combo__title">
          <span class="lr-combo__title--desk">СОБЕРИ КОМБО И<br>ПОЛУЧИ СКИДКУ ДО 10%</span>
          <span class="lr-combo__title--mob">СОБЕРИ КОМБО И<br>ПОЛУЧИ СКИДКУ ДО 7,5%</span>
        </div>

        <div class="lr-combo__list" role="list">

            <!-- hidden selected ids (source of truth for add-to-cart) -->
            <input type="hidden" data-lr-combo-picked="flowers" value="">
            <input type="hidden" data-lr-combo-picked="berry" value="">
            <input type="hidden" data-lr-combo-picked="gifts" value="">

            <!-- FLOWERS SLOT -->
            <div class="lr-combo-slot" data-lr-slot="flowers">

                <!-- default CTA -->
                <div class="lr-combo__item" role="listitem" data-lr-slot-default>
                <div class="lr-combo__icon lr-ico lr-ico--cveti" aria-hidden="true"></div>
                <div class="lr-combo__text">
                    <div class="lr-combo__h">Добавьте букет</div>
                    <div class="lr-combo__p">и получите скидку <span>2,5%</span></div>
                    <button class="lr-combo__btn" type="button" data-lr-combo-open="flowers">ДОБАВИТЬ БУКЕТ</button>
                </div>
                </div>

                <!-- selected row -->
                <div class="lr-combo-picked" data-lr-slot-picked hidden>
                <div class="lr-combo-picked__img"><img data-lr-picked-img alt=""></div>
                <div class="lr-combo-picked__info">
                    <div class="lr-combo-picked__name" data-lr-picked-name></div>
                    <div class="lr-combo-picked__price" data-lr-picked-price></div>
                    <button class="lr-combo-picked__link" type="button" data-lr-picked-replace="flowers">Заменить</button>
                </div>
                <button class="lr-combo-picked__rm" type="button" aria-label="Удалить" data-lr-picked-remove="flowers">🗑</button>
                </div>

            </div>

            <!-- BERRY SLOT -->
            <div class="lr-combo-slot" data-lr-slot="berry">

                <div class="lr-combo__item" role="listitem" data-lr-slot-default>
                <div class="lr-combo__icon lr-ico lr-ico--klubnica" aria-hidden="true"></div>
                <div class="lr-combo__text">
                    <div class="lr-combo__h">Добавьте клубнику</div>
                    <div class="lr-combo__p">и получите скидку <span>2,5%</span></div>
                    <button class="lr-combo__btn" type="button" data-lr-combo-open="berry">ДОБАВИТЬ КЛУБНИКУ</button>
                </div>
                </div>

                <div class="lr-combo-picked" data-lr-slot-picked hidden>
                <div class="lr-combo-picked__img"><img data-lr-picked-img alt=""></div>
                <div class="lr-combo-picked__info">
                    <div class="lr-combo-picked__name" data-lr-picked-name></div>
                    <div class="lr-combo-picked__price" data-lr-picked-price></div>
                    <button class="lr-combo-picked__link" type="button" data-lr-picked-replace="berry">Заменить</button>
                </div>
                <button class="lr-combo-picked__rm" type="button" aria-label="Удалить" data-lr-picked-remove="berry">🗑</button>
                </div>

            </div>

            <!-- GIFTS SLOT -->
            <div class="lr-combo-slot" data-lr-slot="gifts">

                <div class="lr-combo__item" role="listitem" data-lr-slot-default>
                <div class="lr-combo__icon lr-ico lr-ico--podarok" aria-hidden="true"></div>
                <div class="lr-combo__text">
                    <div class="lr-combo__h">Добавьте подарок</div>
                    <div class="lr-combo__p">и получите скидку <span>2,5%</span></div>
                    <button class="lr-combo__btn" type="button" data-lr-combo-open="gifts">ДОБАВИТЬ ПОДАРОК</button>
                </div>
                </div>

                <div class="lr-combo-picked" data-lr-slot-picked hidden>
                <div class="lr-combo-picked__img"><img data-lr-picked-img alt=""></div>
                <div class="lr-combo-picked__info">
                    <div class="lr-combo-picked__name" data-lr-picked-name></div>
                    <div class="lr-combo-picked__price" data-lr-picked-price></div>
                    <button class="lr-combo-picked__link" type="button" data-lr-picked-replace="gifts">Заменить</button>
                </div>
                <button class="lr-combo-picked__rm" type="button" aria-label="Удалить" data-lr-picked-remove="gifts">🗑</button>
                </div>

            </div>

            </div>

        <!-- Postcard row (icon in square, like items) -->
        <div class="lr-combo__free">
          <div class="lr-combo__icon lr-ico lr-ico--otkritka" aria-hidden="true"></div>
          <div class="lr-combo__free-text">Ко всем комбо мы дарим бесплатную открытку</div>
        </div>

        <!-- Totals (updated by JS) -->
        <div class="lr-combo__summary">
          <div class="lr-combo__row">
            <div class="lr-combo__label">Скидка</div>
            <div class="lr-combo__val"><span data-lr-combo-total-discount>0</span>%</div>
          </div>
          <div class="lr-combo__row">
            <div class="lr-combo__label">-</div>
            <div class="lr-combo__val"><span data-lr-combo-discount-amount>0</span> ₽</div>
          </div>
          <div class="lr-combo__row lr-combo__row--total">
            <div class="lr-combo__label">ВСЕГО</div>
            <div class="lr-combo__val"><span data-lr-combo-total>0</span> ₽</div>
          </div>
        </div>

        <button class="lr-btn lr-btn--ghost lr-combo__checkout" type="button" data-lr-combo-addtocart>
          ДОБАВИТЬ В КОРЗИНУ
        </button>

      </div>

    </div>
  </div>
</div>