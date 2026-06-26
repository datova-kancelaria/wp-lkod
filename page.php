<?php
defined('ABSPATH') || exit;
/**
 * Generická WP stránka — delegát na index.php.
 * Špecifické sluggované stránky majú vlastné šablóny:
 *   page-vyhladavanie.php, page-poskytovatelia.php
 */
get_template_part('index');
