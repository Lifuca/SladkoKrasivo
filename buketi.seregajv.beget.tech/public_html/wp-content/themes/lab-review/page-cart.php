<?php
/**
 * Page: Cart (WooCommerce)
 * Автоподхватится, если страница корзины имеет slug "cart".
 */

if (!defined('ABSPATH')) exit;

get_header(); ?>

<main class="lr-main lr-cart">
  <div class="container">

    <header class="lr-pagehead">
      <h1 class="lr-pagehead__title"><?php echo esc_html(get_the_title()); ?></h1>
    </header>

    <?php
    // Выводим стандартный контент страницы (если ты добавишь текст в админке)
    while (have_posts()) : the_post();
      if (trim(get_the_content())) {
        echo '<div class="lr-pagecontent">';
        the_content();
        echo '</div>';
      }
    endwhile;
    ?>

    <div class="lr-cart__wrap">
      <?php
      // Корзина WooCommerce (штатная)
      echo do_shortcode('[woocommerce_cart]');
      ?>
    </div>

  </div>
</main>

<?php get_footer();
