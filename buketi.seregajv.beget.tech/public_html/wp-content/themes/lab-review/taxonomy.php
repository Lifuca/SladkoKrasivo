<?php
defined('ABSPATH') || exit;

get_header(); ?>

<main class="lr-main lr-main--shop">
  <div class="lr-container woocommerce">

    <?php
    // Если это таксономии WooCommerce — отдаем рендер Woo
    if (function_exists('is_product_taxonomy') && is_product_taxonomy()) {
      woocommerce_content();
    } else {
      // Обычные таксономии (если появятся)
      if (have_posts()) {
        while (have_posts()) { the_post();
          the_title('<h2>','</h2>');
          the_excerpt();
        }
      }
    }
    ?>

  </div>
</main>

<?php get_footer();
