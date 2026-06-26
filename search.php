<?php
defined('ABSPATH') || exit;
/**
 * Globálne WP vyhľadávanie (?s=) — delegát na index.php ktorý cez is_search()
 * vykreslí zoznam výsledkov s IDSK 3 layout-om.
 *
 * Pozn.: hlavné vyhľadávanie datasetov má vlastnú Vue stránku
 * /vyhladavanie/ a používa parameter ?q= (nie ?s=).
 */
get_template_part('index');
