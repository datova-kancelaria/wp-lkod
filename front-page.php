<?php defined('ABSPATH') || exit; get_header(); ?>
<main id="main"><div id="lkod-app"></div></main>
<?php
lkod_print_config('home');
wp_enqueue_script('vue3');
wp_enqueue_script('lkod-codelists');
wp_enqueue_script(
    'lkod-vue-home',
    get_template_directory_uri() . '/assets/vue/home.js',
    ['vue3', 'lkod-codelists'],
    '2.0.0',
    true
);
get_footer();
