<?php
defined('ABSPATH') || exit;
/**
 * 404 fallback — delegát na index.php ktorý detekuje is_404() a vykreslí
 * zodpovedajúcu IDSK 3 chybovú stránku.
 */
get_template_part('index');
