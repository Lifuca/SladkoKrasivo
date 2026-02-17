<?php
defined('ABSPATH') || exit;

get_header(); ?>

<main class="lr-main lr-main--shop">
  <div class="lr-container">
    <?php woocommerce_content(); ?>
  </div>
</main>

<?php get_footer();
