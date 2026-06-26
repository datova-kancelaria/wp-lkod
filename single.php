<?php
defined('ABSPATH') || exit;
/**
 * Single post fallback (napr. blogové príspevky) — delegát na index.php.
 * Custom post type dcat_dataset má vlastnú šablónu single-dcat_dataset.php.
 */
get_template_part('index');
