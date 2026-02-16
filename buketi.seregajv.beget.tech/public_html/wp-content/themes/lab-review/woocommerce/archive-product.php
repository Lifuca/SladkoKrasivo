<?php
defined('ABSPATH') || exit;

get_header(); ?>

<main class="lr-main lr-shop">
  <div class="lr-container">

    <?php
    do_action('woocommerce_before_main_content');
    do_action('woocommerce_archive_description');

    if (woocommerce_product_loop()) : ?>

      <header class="lr-shopbar">
        <div class="lr-shopbar__left">
          <h1 class="lr-shopbar__title"><?php echo esc_html(woocommerce_page_title(false)); ?></h1>
          <div class="lr-shopbar__count"><?php woocommerce_result_count(); ?></div>
        </div>

        <div class="lr-shopbar__right">
          <?php woocommerce_catalog_ordering(); ?>
        </div>
      </header>

      <?php woocommerce_product_loop_start(); ?>

      <?php while (have_posts()) : the_post(); ?>
        <?php do_action('woocommerce_shop_loop'); ?>
        <?php wc_get_template_part('content', 'product'); ?>
      <?php endwhile; ?>

      <?php woocommerce_product_loop_end(); ?>

      <div class="lr-shop-pagination">
        <?php do_action('woocommerce_after_shop_loop'); ?>
      </div>

    <?php else : ?>
      <?php do_action('woocommerce_no_products_found'); ?>
    <?php endif; ?>

    <?php do_action('woocommerce_after_main_content'); ?>

  </div>
</main>

<?php get_footer();
