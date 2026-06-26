<?php defined('ABSPATH') || exit; get_header(); ?>
<main id="main"><div id="lkod-app"></div></main>
<?php
lkod_print_config('publishers');
wp_enqueue_script('vue3');
wp_enqueue_script('lkod-codelists');
wp_enqueue_script(
    'lkod-vue-publishers',
    get_template_directory_uri() . '/assets/vue/publishers.js',
    ['vue3', 'lkod-codelists'],
    '2.1.0',
    true
);
get_footer();
