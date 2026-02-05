<?php
if (!defined('ABSPATH')) exit;

get_header(); ?>

<main class="lr-main">
  <div class="lr-container">
    <h1>Lab-Review тема включена</h1>
    <p class="lr-muted">Это базовый скелет. Дальше подключим блоки и WooCommerce.</p>

    <?php if (have_posts()): ?>
      <?php while (have_posts()): the_post(); ?>
        <article style="padding:14px 0;border-top:1px solid rgba(0,0,0,.08)">
          <h2 style="margin:0 0 8px">
            <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
          </h2>
          <?php the_excerpt(); ?>
        </article>
      <?php endwhile; ?>
    <?php endif; ?>
  </div>
</main>

<?php get_footer(); ?>
