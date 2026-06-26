<?php defined('ABSPATH') || exit; get_header(); the_post(); ?>
<main id="main"><div id="lkod-app"></div></main>
<?php
lkod_print_config('dataset', ['datasetId' => (int) get_the_ID()]);
wp_enqueue_script('vue3');
wp_enqueue_script('lkod-codelists');
wp_enqueue_script(
    'lkod-vue-dataset',
    get_template_directory_uri() . '/assets/vue/dataset.js',
    ['vue3', 'lkod-codelists'],
    '2.2.0',
    true
);
get_footer();
