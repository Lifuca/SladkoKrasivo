<?php
defined('ABSPATH') || exit;

get_header();
?>

<main class="lr-main lr-main--shop">
  <div class="lr-container woocommerce">

    <?php if (function_exists('woocommerce_breadcrumb')): ?>
      <?php woocommerce_breadcrumb(); ?>
    <?php endif; ?>

    <?php
    while (have_posts()) : the_post();
      wc_get_template_part('content', 'single-product');
    endwhile;
    ?>

  </div>
</main>

<?php get_footer(); ?>
