<?php
defined('ABSPATH') || exit;

get_header(); ?>

<main class="lr-main lr-main--shop">
  <div class="lr-container">

    <!-- Breadcrumbs: top -->
    <div class="lr-shop-breadcrumbs">
      <?php woocommerce_breadcrumb(); ?>
    </div>

    <!-- Title: centered -->
    <header class="lr-shop-header">
      <h1 class="lr-shop-title"><?php woocommerce_page_title(); ?></h1>
    </header>

    <!-- Sorting row: left under title -->
    <div class="lr-shop-toolbar">
      <div class="lr-sort">
        <span class="lr-sort__label">Сортировать:</span>
        <div class="lr-sort__control">
          <?php woocommerce_catalog_ordering(); ?>
        </div>
      </div>
    </div>

    <?php do_action('woocommerce_archive_description'); ?>

    <?php
    // Reset URL: current term page (if in taxonomy) else shop page. Remove query params.
    $reset_url = '';
    if (is_tax()) {
      $term = get_queried_object();
      if ($term && !is_wp_error($term)) {
        $reset_url = get_term_link($term);
      }
    }
    if (!$reset_url || is_wp_error($reset_url)) {
      $reset_url = get_permalink(wc_get_page_id('shop'));
    }
    $reset_url = strtok($reset_url, '?');
    ?>

    <div class="lr-shop-grid">

      <!-- LEFT: Filters -->
      <aside class="lr-shop-sidebar">
        <div class="lr-filters" id="lr-filters">

<!-- PRICE -->
<section class="lr-filter lr-filter--price is-open">
  <button class="lr-filter__head" type="button" aria-expanded="true">
    <span class="lr-filter__title">Цена</span>
    <span class="lr-filter__chev" aria-hidden="true"></span>
  </button>

  <div class="lr-filter__body">
    <div class="lr-price-fields">
      <input class="lr-price-input" type="number" inputmode="numeric" placeholder="От" min="0" step="1" data-role="min-visible">
      <input class="lr-price-input" type="number" inputmode="numeric" placeholder="До" min="0" step="1" data-role="max-visible">
    </div>

    <?php
    if (class_exists('WC_Widget_Price_Filter')) {
      ob_start();

      the_widget('WC_Widget_Price_Filter', [
        'title' => '',
      ], [
        'before_widget' => '',
        'after_widget'  => '',
        'before_title'  => '',
        'after_title'   => '',
      ]);

      $price_html = ob_get_clean();

      // 1) Удаляем submit button (любая вариация: button / input submit)
      $price_html = preg_replace('~<button[^>]*type\s*=\s*["\']submit["\'][^>]*>.*?</button>~is', '', $price_html);
      $price_html = preg_replace('~<button[^>]*class\s*=\s*["\'][^"\']*\bbutton\b[^"\']*["\'][^>]*>.*?</button>~is', '', $price_html);
      $price_html = preg_replace('~<input[^>]*type\s*=\s*["\']submit["\'][^>]*>~is', '', $price_html);

      // 2) Иногда тема выводит текстовый label рядом — оставляем только price_label
      // (ничего больше не режем, чтобы слайдер не сломать)

      echo $price_html;
    }
    ?>
  </div>
</section>



          <!-- CATEGORIES -->
          <section class="lr-filter">
            <button class="lr-filter__head" type="button" aria-expanded="false">
              <span class="lr-filter__title">Разделы</span>
              <span class="lr-filter__chev" aria-hidden="true"></span>
            </button>

            <div class="lr-filter__body" hidden>
              <?php
              if (class_exists('WC_Widget_Product_Categories')) {
                the_widget('WC_Widget_Product_Categories', [
                  'title'        => '',
                  'count'        => 0,
                  'hierarchical' => 1,
                  'dropdown'     => 0,
                ], [
                  'before_widget' => '',
                  'after_widget'  => '',
                  'before_title'  => '',
                  'after_title'   => '',
                ]);
              }
              ?>
            </div>
          </section>

          <!-- ATTRIBUTES (layered nav), excluding YooKassa -->
          <?php
          $exclude_slugs = [
            'yookassa_payment_mode',
            'yookassa_payment_subject',
          ];

          if (function_exists('wc_get_attribute_taxonomies')) {
            $taxes = wc_get_attribute_taxonomies();

            if (!empty($taxes)) {
              foreach ($taxes as $t) {
                $slug = $t->attribute_name; // without pa_
                if (in_array($slug, $exclude_slugs, true)) {
                  continue;
                }

                $tax = wc_attribute_taxonomy_name($slug); // pa_...
                if (!taxonomy_exists($tax)) continue;

                $label = $t->attribute_label ?: $slug;
                ?>
                <section class="lr-filter">
                  <button class="lr-filter__head" type="button" aria-expanded="false">
                    <span class="lr-filter__title"><?php echo esc_html($label); ?></span>
                    <span class="lr-filter__chev" aria-hidden="true"></span>
                  </button>

                  <div class="lr-filter__body" hidden>
                    <?php
                    the_widget('WC_Widget_Layered_Nav', [
                      'title'        => '',
                      'attribute'    => $slug,
                      'display_type' => 'list',
                      'query_type'   => 'and',
                    ], [
                      'before_widget' => '',
                      'after_widget'  => '',
                      'before_title'  => '',
                      'after_title'   => '',
                    ]);
                    ?>
                  </div>
                </section>
                <?php
              }
            }
          }
          ?>

          <!-- Actions -->
          <div class="lr-filter-actions">
            <button class="lr-filter-apply" type="button">Показать</button>
            <a class="lr-filter-reset" href="<?php echo esc_url($reset_url); ?>">Сбросить</a>
          </div>

        </div>
      </aside>

      <!-- RIGHT: Products -->
      <section class="lr-shop-products">
        <?php if (woocommerce_product_loop()) : ?>

          <?php woocommerce_product_loop_start(); ?>

          <?php while (have_posts()) : the_post(); ?>
            <?php wc_get_template_part('content', 'product'); ?>
          <?php endwhile; ?>

          <?php woocommerce_product_loop_end(); ?>

          <?php do_action('woocommerce_after_shop_loop'); ?>

        <?php else : ?>

          <?php do_action('woocommerce_no_products_found'); ?>

        <?php endif; ?>
      </section>

    </div>

  </div>
</main>

<?php get_footer();
