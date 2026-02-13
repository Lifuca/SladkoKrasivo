<?php
if (!defined('ABSPATH')) exit;

/**
 * Products by Categories (front-page block)
 * Настройка секций — массив $sections ниже.
 */

/** Секции (slug категории + заголовок + кол-во товаров) */
$sections = [
  [
    'slug'  => 'bukety',             // <-- slug категории
    'title' => 'Букеты',
    'limit' => 8,
  ],
  [
    'slug'  => 'klubnika-v-shokolade',// <-- slug категории
    'title' => 'Клубника в шоколаде',
    'limit' => 8,
  ],
  [
    'slug'  => 'podarki',            // <-- slug категории
    'title' => 'Подарочные наборы',
    'limit' => 8,
  ],
];

?>
<section class="lr-products">
  <div class="container">
    <?php foreach ($sections as $s): ?>
      <?php
      $term = get_term_by('slug', $s['slug'], 'product_cat');
      if (!$term || is_wp_error($term)) continue;

      $q = new WP_Query([
        'post_type'      => 'product',
        'post_status'    => 'publish',
        'posts_per_page' => (int)($s['limit'] ?? 8),
        'orderby'        => 'date',
        'order'          => 'DESC',
        'tax_query'      => [
          [
            'taxonomy' => 'product_cat',
            'field'    => 'term_id',
            'terms'    => (int)$term->term_id,
          ]
        ],
        'meta_query'     => WC()->query->get_meta_query(),
        'tax_query'      => array_merge(
          [
            [
              'taxonomy' => 'product_cat',
              'field'    => 'term_id',
              'terms'    => (int)$term->term_id,
            ]
          ],
          WC()->query->get_tax_query()
        ),
      ]);

      $term_link = get_term_link($term, 'product_cat');
      ?>
      <div class="lr-products__section">
        <div class="lr-products__head">
          <h2 class="lr-products__title"><?php echo esc_html($s['title'] ?: $term->name); ?></h2>
          <?php if (!is_wp_error($term_link)): ?>
            <a class="lr-products__all" href="<?php echo esc_url($term_link); ?>">Смотреть все</a>
          <?php endif; ?>
        </div>

        <?php if ($q->have_posts()): ?>
          <div class="lr-products__grid">
            <?php while ($q->have_posts()): $q->the_post(); ?>
              <div class="lr-products__item">
                <?php
                  // Используем твой override карточки, если он есть: woocommerce/content-product.php
                  wc_get_template_part('content', 'product');
                ?>
              </div>
            <?php endwhile; wp_reset_postdata(); ?>
          </div>
        <?php else: ?>
          <p class="lr-products__empty">Пока нет товаров в этой категории.</p>
        <?php endif; ?>
      </div>
    <?php endforeach; ?>
  </div>
</section>
