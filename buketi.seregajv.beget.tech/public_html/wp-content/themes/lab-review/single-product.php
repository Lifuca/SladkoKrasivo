<?php
/**
 * WooCommerce Single Product Template (Theme wrapper)
 * @package lab-review
 */

if (!defined('ABSPATH')) exit;

get_header(); ?>

<main class="lr-main lr-product">
  <div class="container">

    <?php
    /**
     * Хлебные крошки (если WooCommerce включён)
     */
    if (function_exists('woocommerce_breadcrumb')) {
      woocommerce_breadcrumb([
        'wrap_before' => '<nav class="lr-breadcrumbs" aria-label="Breadcrumbs">',
        'wrap_after'  => '</nav>',
      ]);
    }
    ?>

    <?php
    // Стандартный вывод товара WooCommerce
    while (have_posts()) : the_post();
      wc_get_template_part('content', 'single-product');
    endwhile;
    ?>

  </div>
</main>

<?php get_footer();
