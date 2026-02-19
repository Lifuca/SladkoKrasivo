<?php
if (!defined('ABSPATH')) exit;

// Woo support (если у тебя этого ещё нет)
add_action('after_setup_theme', function () {
  add_theme_support('woocommerce');
});

// Сайдбар фильтров каталога
add_action('widgets_init', function () {
  register_sidebar([
    'name'          => 'Фильтры каталога',
    'id'            => 'lr-shop-filters',
    'description'   => 'Левая колонка фильтров на страницах каталога WooCommerce',
    'before_widget' => '<section class="lr-filter">',
    'after_widget'  => '</section>',
    'before_title'  => '<h4 class="lr-filter__title">',
    'after_title'   => '</h4>',
  ]);
});
