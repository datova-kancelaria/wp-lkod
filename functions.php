<?php
/**
 * LKOD IDSK3 theme
 *
 * Frontend Vue 3 + REST API. Labels are resolved client-side from the
 * plugin's native codelist endpoints (/wp-json/wp-lkod/v1/*).
 *
 * The theme exposes a small set of data endpoints under
 *   /wp-json/lkod-theme/v1/
 * which return raw IRIs only — never resolved labels.
 */
defined('ABSPATH') || exit;

require_once get_template_directory() . '/inc/theme-settings.php';
require_once get_template_directory() . '/inc/rest-settings.php';
require_once get_template_directory() . '/inc/required-plugins.php';

/* On activation, ensure the pages backing the custom templates exist. */
add_action('after_switch_theme', static function (): void {
    $pages = [
        'vyhladavanie'   => __('Vyhľadávanie', 'lkod-idsk3'),
        'poskytovatelia' => __('Poskytovatelia dát', 'lkod-idsk3'),
        'vsetky-clanky'  => __('Všetky články', 'lkod-idsk3'),
    ];
    foreach ($pages as $slug => $title) {
        $existing = get_page_by_path($slug, OBJECT, 'page');
        if ($existing) {
            if ($existing->post_title !== $title) {
                wp_update_post([
                    'ID'         => $existing->ID,
                    'post_title' => $title,
                ]);
            }
            continue;
        }
        wp_insert_post([
            'post_title'   => $title,
            'post_name'    => $slug,
            'post_type'    => 'page',
            'post_status'  => 'publish',
            'post_content' => '',
            'comment_status' => 'closed',
            'ping_status'    => 'closed',
        ]);
    }
    flush_rewrite_rules();
});

/* Fallback document title for known slugs (if an admin renames the page). */
add_filter('pre_get_document_title', static function (string $title): string {
    if (!is_page()) return $title;

    $slug = get_post_field('post_name', get_queried_object_id());
    $map = [
        'vyhladavanie'   => 'Vyhľadávanie',
        'poskytovatelia' => 'Poskytovatelia dát',
        'vsetky-clanky'  => 'Všetky články',
    ];
    if (!isset($map[$slug])) return $title;

    $sep      = apply_filters('document_title_separator', '-');
    $sitename = get_bloginfo('name', 'display');
    return $map[$slug] . ' ' . $sep . ' ' . $sitename;
}, 10);

/* Recompute dataset slug from title-sk after save (priority 99 = after the
   plugin has stored the meta). Note: this also changes slugs of existing
   datasets, so their old URLs stop working. */
add_action('save_post_dcat_dataset', 'lkod_sync_dataset_slug', 99, 2);
function lkod_sync_dataset_slug(int $post_id, WP_Post $post): void {
    if (wp_is_post_revision($post_id) || wp_is_post_autosave($post_id)) return;
    if ($post->post_status !== 'publish') return;

    $title = trim((string) get_post_meta($post_id, 'title-sk', true));
    if ($title === '') return;

    $desired = sanitize_title($title);
    if ($desired === '' || $desired === $post->post_name) return;

    $unique = wp_unique_post_slug($desired, $post_id, $post->post_status, $post->post_type, $post->post_parent);

    /* Recursion guard — wp_update_post re-triggers save_post. */
    remove_action('save_post_dcat_dataset', 'lkod_sync_dataset_slug', 99);
    wp_update_post(['ID' => $post_id, 'post_name' => $unique]);
    add_action('save_post_dcat_dataset', 'lkod_sync_dataset_slug', 99, 2);
}

/* Same as lkod_sync_dataset_slug, but for articles (title is in post_title). */
add_action('save_post_post', 'lkod_sync_post_slug', 99, 2);
function lkod_sync_post_slug(int $post_id, WP_Post $post): void {
    if (wp_is_post_revision($post_id) || wp_is_post_autosave($post_id)) return;
    if ($post->post_status !== 'publish') return;

    $title = trim((string) $post->post_title);
    if ($title === '') return;

    $desired = sanitize_title($title);
    if ($desired === '' || $desired === $post->post_name) return;

    $unique = wp_unique_post_slug($desired, $post_id, $post->post_status, $post->post_type, $post->post_parent);

    remove_action('save_post_post', 'lkod_sync_post_slug', 99);
    wp_update_post(['ID' => $post_id, 'post_name' => $unique]);
    add_action('save_post_post', 'lkod_sync_post_slug', 99, 2);
}

add_action('after_setup_theme', static function (): void {
    load_theme_textdomain('lkod-idsk3', get_template_directory() . '/languages');

    add_theme_support('title-tag');
    add_theme_support('post-thumbnails');
    add_theme_support('custom-logo');
    add_theme_support('html5', ['search-form', 'comment-form', 'gallery', 'caption', 'script', 'style']);

    register_nav_menus([
        'primary' => __('Hlavná navigácia', 'lkod-idsk3'),
        'footer'  => __('Pätičková navigácia', 'lkod-idsk3'),
    ]);
});

add_action('wp_enqueue_scripts', static function (): void {
    $base = get_template_directory_uri();

    wp_enqueue_style(
        'lkod-fonts',
        'https://fonts.googleapis.com/css2?family=Source+Sans+Pro:wght@400;600;700;800&display=swap',
        [],
        null
    );
    /* Material Icons (used by IDSK 3 header dropdown + search icons) */
    wp_enqueue_style(
        'material-icons',
        'https://fonts.googleapis.com/icon?family=Material+Icons',
        [],
        null
    );

    /* Official IDSK 3 (id-sk/frontend@3.0.0-beta.0) — installed via npm,
       compiled CSS/JS shipped under assets/idsk/. */
    wp_enqueue_style('idsk3', $base . '/assets/idsk/frontend.min.css', ['lkod-fonts'], '3.0.0-beta.0');

    /* Povinná publicita – lišta log (EU / Plán obnovy / MIRRI) v pätičke. */
    wp_add_inline_style('idsk3', '
      .lkod-footer__funding{margin-bottom:24px;}
      .lkod-footer__funding img{width:100%;max-width:760px;height:auto;display:block;margin-left:auto;}
    ');

    /* shared scripts — registered, enqueued by page templates as needed */
    wp_register_script('vue3', 'https://unpkg.com/vue@3/dist/vue.global.prod.js', [], '3.4', false);
    wp_register_script('lkod-codelists', $base . '/assets/vue/codelists.js', [], '2.0.0', false);

    /* IDSK 3 JS — UMD bundle (frontend.min.js is ESM and breaks in <script>).
       all.bundle.js exposes window.GOVUKFrontend globally. */
    wp_enqueue_script('idsk3', $base . '/assets/idsk/frontend.bundle.js', [], '3.0.0-beta.0', true);
}, 5);

/* Init IDSK 3 components on every page */
add_action('wp_footer', static function (): void {
    echo '<script>
      window.addEventListener("DOMContentLoaded", function () {
        if (window.GOVUKFrontend && typeof window.GOVUKFrontend.initAll === "function") {
          window.GOVUKFrontend.initAll();
        }
      });
    </script>';
}, 99);

/* Include datasets in the frontend search results. */
add_action('pre_get_posts', static function (WP_Query $q): void {
    if (!$q->is_main_query() || is_admin()) return;
    if ($q->is_search()) {
        $types = (array) $q->get('post_type');
        if (empty($types) || $types === ['post']) {
            $q->set('post_type', ['post', 'dcat_dataset']);
        }
    }
});

/* Return the first non-empty language value from a { sk, en, … } assoc array. */
function lkod_pick_lang_value($value, array $langs = ['sk', 'en', 'cz', 'fr', 'ua']): string {
    if (!is_array($value)) return is_string($value) ? $value : '';
    foreach ($langs as $lang) {
        if (!empty($value[$lang]) && is_string($value[$lang])) {
            return $value[$lang];
        }
    }
    return '';
}

function lkod_meta_lang(int $post_id, string $key, array $langs = ['sk', 'en', 'cz']): string {
    foreach ($langs as $lang) {
        $val = get_post_meta($post_id, $key . '-' . $lang, true);
        if (is_string($val) && $val !== '') return $val;
    }
    $val = get_post_meta($post_id, $key, true);
    return is_string($val) ? $val : '';
}

/* Distributions are stored as multiple post_meta rows (single_valued=false),
   so read them all with get_post_meta(..., false). Also accepts the legacy
   JSON-encoded format (single distribution or array of distributions). */
function lkod_distributions(int $post_id): array {
    $rows = get_post_meta($post_id, 'distributions', false);
    if (empty($rows)) return [];

    $result = [];
    foreach ($rows as $item) {
        if (is_array($item)) {
            $result[] = $item;
            continue;
        }
        if (is_string($item) && $item !== '') {
            /* Legacy JSON: a single distribution or an array of them. */
            $decoded = json_decode($item, true);
            if (!is_array($decoded)) continue;

            if (isset($decoded[0]) && is_array($decoded[0])) {
                foreach ($decoded as $d) {
                    if (is_array($d)) $result[] = $d;
                }
            } else {
                $result[] = $decoded;
            }
        }
    }
    return $result;
}

/* Access service (dcat_data_service post ID in $d['service']) → { name, url }. */
function lkod_distribution_service(array $d): array {
    $sid = (int) ($d['service'] ?? 0);
    if ($sid <= 0) return ['name' => '', 'url' => ''];
    $post = get_post($sid);
    if (!$post || $post->post_type !== 'dcat_data_service') return ['name' => '', 'url' => ''];
    return [
        'name' => get_the_title($post),
        'url'  => (string) get_post_meta($sid, 'endpoint_url', true),
    ];
}

/* Full dcat_data_service detail for the dataset detail page. Multi-value
   fields are multi-row meta, so they are read with get_post_meta(..., false). */
function lkod_data_service(array $d): ?array {
    $sid = (int) ($d['service'] ?? 0);
    if ($sid <= 0) return null;
    $post = get_post($sid);
    if (!$post || $post->post_type !== 'dcat_data_service') return null;

    return [
        'name'           => lkod_meta_lang($sid, 'name') ?: get_the_title($post),
        'endpoint_url'   => (string) get_post_meta($sid, 'endpoint_url', true),
        'is_hvd'         => (bool)   get_post_meta($sid, 'is_hvd', true),
        'hvd_categories' => array_values(array_filter(array_map('strval', (array) get_post_meta($sid, 'hvd_categories')))),
        'legislation'    => array_values(array_filter(array_map('strval', (array) get_post_meta($sid, 'applicable_legislation')))),
        'contact'        => lkod_contact_point($sid),
        'documentation'  => (string) get_post_meta($sid, 'documentation', true),
        'specification'  => (string) get_post_meta($sid, 'conforms_to', true),
        'endpoint_description' => (string) get_post_meta($sid, 'endpoint_description', true),
        'custom_metadata'      => lkod_dataset_custom_metadata($sid, 'dcat_data_service'),
    ];
}

/* Distribution download URL (uploaded file or external URL). */
function lkod_distribution_url(array $d): string {
    $method = $d['method'] ?? 'file';
    if ($method === 'service') return '';
    $type = $d['file_type'] ?? 'url';
    if ($type === 'upload' && !empty($d['upload'])) {
        return (string) wp_get_attachment_url((int) $d['upload']);
    }
    return is_string($d['url'] ?? null) ? $d['url'] : '';
}

/* Distribution display title (language-aware, falls back to the format name). */
function lkod_distribution_title(array $d): string {
    if (isset($d['title']) && is_array($d['title'])) {
        foreach (['sk', 'en', 'fr', 'ua', 'cz'] as $lang) {
            if (!empty($d['title'][$lang]) && is_string($d['title'][$lang])) {
                return $d['title'][$lang];
            }
        }
    }
    /* Legacy: 'title-sk' etc. or 'title' as plain string. */
    foreach (['title-sk', 'title-en', 'title-fr', 'title-ua', 'title-cz'] as $k) {
        if (!empty($d[$k]) && is_string($d[$k])) return $d[$k];
    }
    if (isset($d['title']) && is_string($d['title']) && $d['title'] !== '') {
        return $d['title'];
    }
    if (!empty($d['format']) && is_string($d['format'])) {
        return strtoupper(basename(rtrim($d['format'], '/')));
    }
    return 'Distribúcia';
}

/* Short plain-text excerpt of a description. */
function lkod_excerpt(string $text, int $length = 220): string {
    $text = wp_strip_all_tags($text);
    if (mb_strlen($text) <= $length) return $text;
    return rtrim(mb_substr($text, 0, $length)) . '…';
}

/* Map the dataset-type IRIs (multi-row meta 'type') to boolean flags. */
function lkod_dataset_type_flags(int $post_id): array {
    $types = array_values(array_filter(array_map('strval', get_post_meta($post_id, 'type', false) ?: [])));
    return [
        'is_hvd'        => in_array('http://publications.europa.eu/resource/authority/dataset-type/HVD', $types, true),
        'is_minimum'    => in_array('https://data.gov.sk/def/dataset-type/1', $types, true),
        'is_popular'    => in_array('https://data.gov.sk/def/dataset-type/3', $types, true),
    ];
}

/* Build a dataset row in the search-list shape. */
function lkod_dataset_row(WP_Post $post): array {
    $pid = $post->ID;

    $themes  = array_values(array_filter((array) get_post_meta($pid, 'theme')));
    $formats = [];
    foreach (lkod_distributions($pid) as $d) {
        if (!empty($d['format']) && !in_array($d['format'], $formats, true)) {
            $formats[] = $d['format'];
        }
    }

    /* Keywords are multi-row meta (one row per keyword) → read with single=false. */
    $keywords = array_values(array_filter(
        array_map('strval', get_post_meta($pid, 'keywords-sk', false) ?: []),
        static fn($v) => $v !== ''
    ));

    $type_flags = lkod_dataset_type_flags($pid);

    return array_merge([
        'id'          => $pid,
        'title'       => get_the_title($post),
        'description' => lkod_excerpt(lkod_meta_lang($pid, 'description')),
        'permalink'   => get_permalink($post),
        'publisher'   => (string) get_post_meta($pid, 'publisher', true),
        'theme_iris'  => $themes,
        'format_iris' => $formats,
        'keywords'    => $keywords,
        'modified'    => substr((string)(get_post_meta($pid, 'modified', true) ?: get_the_modified_date('Y-m-d', $post)), 0, 10),
    ], $type_flags);
}

/* Admin-defined custom metadata (wp_options 'custom_metadata') that applies to
   dcat_dataset and has stored values on this post. */
function lkod_dataset_custom_metadata(int $post_id, string $post_type = 'dcat_dataset'): array {
    $definitions = get_option('custom_metadata', []);
    if (!is_array($definitions)) return [];

    $result = [];
    foreach ($definitions as $def) {
        if (!is_array($def)) continue;
        $name  = trim((string) ($def['name']  ?? ''));
        $label = trim((string) ($def['label'] ?? ''));
        $type  = trim((string) ($def['type']  ?? 'text'));
        $use   = (array)         ($def['use']   ?? []);

        if (!$name || !in_array($post_type, $use, true)) continue;

        /* CustomMetadataProperty is a MultiProperty → may have multiple rows. */
        $values = get_post_meta($post_id, $name, false);
        if (!is_array($values)) $values = [];

        $values = array_values(array_filter(array_map('strval', $values), static fn($v) => $v !== ''));
        if (empty($values)) continue;

        $result[] = [
            'name'   => $name,
            'label'  => $label !== '' ? $label : $name,
            'type'   => in_array($type, ['text', 'email', 'url', 'date', 'list'], true) ? $type : 'text',
            'values' => $values,
        ];
    }
    return $result;
}

/* Normalise temporal_resolution (string "5d" or [value, unit]) to a string,
   so casting it for JSON output never emits a warning. */
function lkod_temporal_resolution_string($value): string {
    if (is_string($value)) return $value;
    if (is_array($value)) {
        $v = (string) ($value['value'] ?? $value[0] ?? '');
        $u = (string) ($value['unit']  ?? $value[1] ?? '');
        if ($v !== '' && $u !== '') return $v . $u;
    }
    return '';
}

/* Contact point (name + email) referenced by a post's 'contact_point' meta. */
function lkod_contact_point(int $post_id): ?array {
    $cp_id = (int) get_post_meta($post_id, 'contact_point', true);
    if (!$cp_id) return null;
    $post = get_post($cp_id);
    if (!$post || $post->post_status !== 'publish') return null;
    return [
        'name'  => lkod_meta_lang($cp_id, 'name') ?: get_the_title($post),
        'email' => (string) get_post_meta($cp_id, 'email', true),
    ];
}

/* Theme REST routes — return raw IRIs only (labels are resolved client-side). */
add_action('rest_api_init', static function (): void {
    $ns = 'lkod-theme/v1';

    register_rest_route($ns, '/datasets', [
        'methods'  => 'GET',
        'callback' => 'lkod_rest_datasets',
        'permission_callback' => '__return_true',
    ]);
    register_rest_route($ns, '/dataset/(?P<id>\d+)', [
        'methods'  => 'GET',
        'callback' => 'lkod_rest_dataset',
        'permission_callback' => '__return_true',
    ]);
    register_rest_route($ns, '/facets', [
        'methods'  => 'GET',
        'callback' => 'lkod_rest_facets',
        'permission_callback' => '__return_true',
    ]);
    register_rest_route($ns, '/homepage', [
        'methods'  => 'GET',
        'callback' => 'lkod_rest_homepage',
        'permission_callback' => '__return_true',
    ]);
    register_rest_route($ns, '/categories', [
        'methods'  => 'GET',
        'callback' => 'lkod_rest_categories',
        'permission_callback' => '__return_true',
    ]);
});

/* ---- /datasets (search + filter + paginate) ------------------- */
function lkod_rest_datasets(WP_REST_Request $req): WP_REST_Response {
    $per_page = max(1, min(50, (int) ($req->get_param('per_page') ?: 10)));
    $page     = max(1, (int) ($req->get_param('page') ?: 1));
    $search   = sanitize_text_field((string) ($req->get_param('s') ?: ''));
    $pubs     = array_filter(array_map('sanitize_text_field', (array) ($req->get_param('publisher') ?: [])));
    $themes   = array_filter(array_map('sanitize_text_field', (array) ($req->get_param('theme') ?: [])));
    $cats     = array_values(array_filter(array_map('absint', (array) ($req->get_param('categories') ?: []))));
    $types    = array_filter(array_map('sanitize_text_field', (array) ($req->get_param('type') ?: [])));
    $periods  = array_filter(array_map('sanitize_text_field', (array) ($req->get_param('periodicity') ?: [])));
    $formats  = array_filter(array_map('sanitize_text_field', (array) ($req->get_param('format') ?: [])));
    $dtypes   = array_filter(array_map('sanitize_text_field', (array) ($req->get_param('dist_type') ?: [])));
    $licenses = array_filter(array_map('sanitize_text_field', (array) ($req->get_param('license') ?: [])));
    $keyword  = sanitize_text_field((string) ($req->get_param('keyword') ?: ''));
    $sort     = in_array($req->get_param('sort'), ['date', 'title', 'relevance'], true) ? $req->get_param('sort') : 'relevance';

    $args = [
        'post_type'      => 'dcat_dataset',
        'post_status'    => 'publish',
        'posts_per_page' => $per_page,
        'paged'          => $page,
        'orderby'        => $sort === 'date' ? 'modified' : ($sort === 'title' ? 'title' : 'relevance'),
        'order'          => $sort === 'title' ? 'ASC' : 'DESC',
    ];
    if ($search !== '') $args['s'] = $search;

    $mq = ['relation' => 'AND'];
    if ($pubs)    $mq[] = ['key' => 'publisher',   'value' => $pubs,   'compare' => 'IN'];
    if ($themes)  $mq[] = ['key' => 'theme',       'value' => $themes, 'compare' => 'IN'];
    if ($cats)    $mq[] = ['key' => 'categories',  'value' => array_map('strval', $cats), 'compare' => 'IN'];
    if ($types)   $mq[] = ['key' => 'type',        'value' => $types,   'compare' => 'IN'];
    if ($periods) $mq[] = ['key' => 'periodicity', 'value' => $periods, 'compare' => 'IN'];
    if ($keyword) $mq[] = ['key' => 'keywords-sk', 'value' => $keyword, 'compare' => 'LIKE'];

    /* Distribučné polia (format, licencia, typ distribúcie) sú vnorené v
       serializovanej meta 'distributions' (multi-row). Filtrujeme cez LIKE
       na serializovaný blob — IRI/pattern je unikátny reťazec. Viac hodnôt
       v jednom filtri = OR. */
    if ($formats) {
        $g = ['relation' => 'OR'];
        foreach ($formats as $f) $g[] = ['key' => 'distributions', 'value' => $f, 'compare' => 'LIKE'];
        $mq[] = $g;
    }
    if ($licenses) {
        $g = ['relation' => 'OR'];
        foreach ($licenses as $l) $g[] = ['key' => 'distributions', 'value' => $l, 'compare' => 'LIKE'];
        $mq[] = $g;
    }
    if ($dtypes) {
        $g = ['relation' => 'OR'];
        foreach ($dtypes as $dt) {
            if ($dt === 'file')    $g[] = ['key' => 'distributions', 'value' => '"method";s:4:"file"',    'compare' => 'LIKE'];
            if ($dt === 'service') $g[] = ['key' => 'distributions', 'value' => '"method";s:7:"service"', 'compare' => 'LIKE'];
        }
        if (count($g) > 1) $mq[] = $g;
    }

    if (count($mq) > 1) $args['meta_query'] = $mq;

    $q = new WP_Query($args);
    $items = array_map('lkod_dataset_row', $q->posts);

    return new WP_REST_Response([
        'total' => (int) $q->found_posts,
        'pages' => max(1, (int) $q->max_num_pages),
        'page'  => $page,
        'items' => $items,
    ]);
}

/* ---- /dataset/{id} (full detail, raw IRIs) -------------------- */
function lkod_rest_dataset(WP_REST_Request $req): WP_REST_Response {
    $id   = (int) $req['id'];
    $post = get_post($id);
    if (!$post || $post->post_type !== 'dcat_dataset' || $post->post_status !== 'publish') {
        return new WP_REST_Response(['error' => 'not_found'], 404);
    }

    /* Sibling datasets in the same series. */
    $serie = [];
    $in_serie = (int) get_post_meta($id, 'in_serie', true);
    if ($in_serie) {
        $siblings = get_posts([
            'post_type'   => 'dcat_dataset',
            'post_status' => 'publish',
            'numberposts' => 20,
            'exclude'     => [$id],
            'meta_query'  => [['key' => 'in_serie', 'value' => (string) $in_serie]],
        ]);
        foreach ($siblings as $s) {
            $serie[] = ['id' => $s->ID, 'title' => get_the_title($s), 'permalink' => get_permalink($s)];
        }
    }

    /* distributions — return raw IRIs for every codelist field */
    $dists = array_map(static fn(array $d): array => [
        'title'         => lkod_distribution_title($d),
        'url'           => lkod_distribution_url($d),
        'format'        => (string) ($d['format'] ?? ''),
        'media_type'    => (string) ($d['media_type'] ?? ''),
        'license_aw'    => (string) ($d['authors_work_type'] ?? ''),
        'license_db'    => (string) ($d['original_database_type'] ?? ''),
        'license_sp'    => (string) ($d['database_protected_by_special_rights_type'] ?? ''),
        'personal_data' => (string) ($d['personal_data_containment_type'] ?? ''),
        'conforms_to'   => (string) ($d['conforms_to'] ?? ''),
        'compress'      => (string) ($d['compress_format'] ?? ''),
        'package'       => (string) ($d['package_format'] ?? ''),
        'author_name'          => lkod_pick_lang_value($d['author_name']          ?? null),
        'original_author_name' => lkod_pick_lang_value($d['original_author_name'] ?? null),
        'method'               => (string) ($d['method'] ?? ''),
        'service_name'         => lkod_distribution_service($d)['name'],
        'service_url'          => lkod_distribution_service($d)['url'],
        'service'              => lkod_data_service($d),
        'legislation'          => array_values(array_filter(
            array_map('strval', (array) ($d['applicable_legislation'] ?? [])),
            static fn($v) => $v !== ''
        )),
    ], lkod_distributions($id));

    $hvd_categories = array_values(array_filter((array) get_post_meta($id, 'hvd_categories')));

    /* Kategórie datasetu (dcat_category post ID → názov). */
    $categories = [];
    foreach (array_filter(array_map('absint', (array) get_post_meta($id, 'categories', false))) as $cid) {
        $cpost = get_post($cid);
        if ($cpost && $cpost->post_type === 'dcat_category') {
            $categories[] = ['id' => $cid, 'name' => get_the_title($cpost)];
        }
    }

    return new WP_REST_Response(array_merge([
        'id'             => $id,
        'title'          => get_the_title($post),
        'description'    => lkod_meta_lang($id, 'description'),
        'publisher'      => (string) get_post_meta($id, 'publisher', true),
        'theme_iris'     => array_values(array_filter((array) get_post_meta($id, 'theme'))),
        'periodicity'    => (string) get_post_meta($id, 'periodicity', true),
        'spatial'        => (string) get_post_meta($id, 'spatial', true),
        'date_from'      => (string) get_post_meta($id, 'date_from', true),
        'date_to'        => (string) get_post_meta($id, 'date_to', true),
        'issued'         => substr((string) get_post_meta($id, 'issued', true), 0, 10),
        'modified'       => substr((string)(get_post_meta($id, 'modified', true) ?: get_the_modified_date('Y-m-d', $post)), 0, 10),
        'documentation'  => (string) get_post_meta($id, 'documentation', true),
        'website'        => (string) get_post_meta($id, 'website', true),
        'specification'  => (string) get_post_meta($id, 'conforms_to', true),
        'spatial_optional'      => array_values(array_filter((array) get_post_meta($id, 'spatial_optional'))),
        'related'               => array_values(array_filter((array) get_post_meta($id, 'related'))),
        'eurovoc'               => array_values(array_filter((array) get_post_meta($id, 'eurovoc'))),
        'legislation'           => array_values(array_filter((array) get_post_meta($id, 'applicable_legislation'))),
        'temporal_resolution'   => lkod_temporal_resolution_string(get_post_meta($id, 'temporal_resolution', true)),
        'spatial_resolution'    => (function ($v) { return is_scalar($v) ? (string) $v : ''; })(get_post_meta($id, 'spatial_resolution', true)),
        'keywords'       => array_values(array_filter(array_map('strval', get_post_meta($id, 'keywords-sk', false) ?: []))),
        'contact'        => lkod_contact_point($id),
        'hvd_categories' => $hvd_categories,
        'categories'     => $categories,
        'is_serie'       => (bool) get_post_meta($id, 'is_serie', true),
        'distributions'  => $dists,
        'serie'          => $serie,
        'custom_metadata'=> lkod_dataset_custom_metadata($id),
    ], lkod_dataset_type_flags($id)));
}

/* ---- /facets (which IRIs are actually used + publisher counts) -- */
function lkod_rest_facets(WP_REST_Request $req): WP_REST_Response {
    global $wpdb;

    $pub_rows = $wpdb->get_results($wpdb->prepare("
        SELECT pm.meta_value AS iri, COUNT(*) AS cnt
        FROM {$wpdb->postmeta} pm
        INNER JOIN {$wpdb->posts} p ON p.ID = pm.post_id
        WHERE pm.meta_key = %s AND pm.meta_value <> ''
          AND p.post_type = %s AND p.post_status = 'publish'
        GROUP BY pm.meta_value
        ORDER BY cnt DESC
    ", 'publisher', 'dcat_dataset'));

    $publisher_counts = [];
    $publisher_iris   = [];
    foreach ($pub_rows as $r) {
        $publisher_counts[] = ['iri' => $r->iri, 'count' => (int) $r->cnt];
        $publisher_iris[]   = $r->iri;
    }

    $theme_iris = array_values(array_filter($wpdb->get_col($wpdb->prepare("
        SELECT DISTINCT pm.meta_value
        FROM {$wpdb->postmeta} pm
        INNER JOIN {$wpdb->posts} p ON p.ID = pm.post_id
        WHERE pm.meta_key = %s AND pm.meta_value <> ''
          AND p.post_type = %s AND p.post_status = 'publish'
        ORDER BY pm.meta_value
    ", 'theme', 'dcat_dataset'))));

    /* Category facet — counts per dcat_category post ID used by datasets. */
    $cat_rows = $wpdb->get_results($wpdb->prepare("
        SELECT pm.meta_value AS id, COUNT(*) AS cnt
        FROM {$wpdb->postmeta} pm
        INNER JOIN {$wpdb->posts} p ON p.ID = pm.post_id
        WHERE pm.meta_key = %s AND pm.meta_value <> ''
          AND p.post_type = %s AND p.post_status = 'publish'
        GROUP BY pm.meta_value
        ORDER BY cnt DESC
    ", 'categories', 'dcat_dataset'));
    $category_counts = [];
    foreach ($cat_rows as $r) {
        $category_counts[] = ['id' => (int) $r->id, 'count' => (int) $r->cnt];
    }

    /* Typ datasetu + periodicita — DISTINCT z meta. */
    $type_iris = array_values(array_filter($wpdb->get_col($wpdb->prepare("
        SELECT DISTINCT pm.meta_value
        FROM {$wpdb->postmeta} pm
        INNER JOIN {$wpdb->posts} p ON p.ID = pm.post_id
        WHERE pm.meta_key = %s AND pm.meta_value <> ''
          AND p.post_type = %s AND p.post_status = 'publish'
        ORDER BY pm.meta_value
    ", 'type', 'dcat_dataset'))));

    $periodicity_iris = array_values(array_filter($wpdb->get_col($wpdb->prepare("
        SELECT DISTINCT pm.meta_value
        FROM {$wpdb->postmeta} pm
        INNER JOIN {$wpdb->posts} p ON p.ID = pm.post_id
        WHERE pm.meta_key = %s AND pm.meta_value <> ''
          AND p.post_type = %s AND p.post_status = 'publish'
        ORDER BY pm.meta_value
    ", 'periodicity', 'dcat_dataset'))));

    /* Format / licencia / typ distribúcie — vnorené v distribúciách, treba scan. */
    $dist = lkod_collect_distribution_facets();

    return new WP_REST_Response([
        'publisher_iris'    => $publisher_iris,
        'publisher_counts'  => $publisher_counts,
        'theme_iris'        => $theme_iris,
        'category_counts'   => $category_counts,
        'type_iris'         => $type_iris,
        'periodicity_iris'  => $periodicity_iris,
        'format_iris'       => $dist['format_iris'],
        'license_iris'      => $dist['license_iris'],
        'dist_types'        => $dist['dist_types'],
    ]);
}

/* ---- Helper: zozbieranie facetov z distribúcií (jeden scan) ---- */
function lkod_collect_distribution_facets(): array {
    $formats = []; $licenses = []; $methods = [];
    $ids = get_posts([
        'post_type'   => 'dcat_dataset',
        'post_status' => 'publish',
        'numberposts' => -1,
        'fields'      => 'ids',
    ]);
    foreach ($ids as $id) {
        foreach (lkod_distributions((int) $id) as $d) {
            if (!empty($d['format']) && is_string($d['format']))               $formats[$d['format']] = true;
            if (!empty($d['authors_work_type']) && is_string($d['authors_work_type'])) $licenses[$d['authors_work_type']] = true;
            $m = $d['method'] ?? '';
            if ($m === 'file' || $m === 'service')                             $methods[$m] = true;
        }
    }
    return [
        'format_iris'  => array_keys($formats),
        'license_iris' => array_keys($licenses),
        'dist_types'   => array_keys($methods),
    ];
}

/* ---- /homepage (articles + theme IRIs) ------------------------- */
function lkod_rest_homepage(WP_REST_Request $req): WP_REST_Response {
    /* Počet článkov podľa nastavenia témy (wp-admin → Nastavenia témy).
       0 = sekcia skrytá, max 12. */
    $articles_count = max(0, min(12, (int) get_option('lkod_articles_count', 3)));
    $posts_raw = $articles_count > 0 ? get_posts([
        'post_type'   => 'post',
        'post_status' => 'publish',
        'numberposts' => $articles_count,
        'orderby'     => 'date',
        'order'       => 'DESC',
    ]) : [];
    $posts = array_map(static fn(WP_Post $p): array => [
        'id'        => $p->ID,
        'title'     => get_the_title($p),
        'excerpt'   => wp_strip_all_tags(get_the_excerpt($p) ?: wp_trim_words(get_the_content(null, false, $p), 25)),
        'permalink' => get_permalink($p),
        'date'      => get_the_date('j.n.Y', $p),
        'thumbnail' => (string) (get_the_post_thumbnail_url($p, 'medium') ?: ''),
    ], $posts_raw);

    global $wpdb;
    $theme_iris = array_values(array_filter($wpdb->get_col($wpdb->prepare("
        SELECT DISTINCT pm.meta_value
        FROM {$wpdb->postmeta} pm
        INNER JOIN {$wpdb->posts} p ON p.ID = pm.post_id
        WHERE pm.meta_key = %s AND pm.meta_value <> ''
          AND p.post_type = %s AND p.post_status = 'publish'
    ", 'theme', 'dcat_dataset'))));

    return new WP_REST_Response([
        'posts'      => $posts,
        'theme_iris' => $theme_iris,
    ]);
}

/* ---- /categories (custom post type dcat_category) -------------- */
function lkod_rest_categories(WP_REST_Request $req): WP_REST_Response {
    $cats_raw = get_posts([
        'post_type'   => 'dcat_category',
        'post_status' => 'publish',
        'numberposts' => 50,
        'orderby'     => 'title',
        'order'       => 'ASC',
    ]);
    $items = array_map(static function (WP_Post $p): array {
        $icon_id  = (int) get_post_meta($p->ID, 'icon', true);
        $icon_url = $icon_id ? wp_get_attachment_image_url($icon_id, 'medium') : '';
        return [
            'id'        => $p->ID,
            'name'      => get_the_title($p),
            'icon_url'  => (string) $icon_url,
            'permalink' => get_permalink($p),
        ];
    }, $cats_raw);

    return new WP_REST_Response($items);
}

/* Inject window.LKOD_CONFIG into the page for the Vue apps. */
function lkod_print_config(string $page, array $extra = []): void {
    $cfg = array_merge([
        'page'          => $page,
        'datasetApi'    => esc_url_raw(rest_url('lkod-theme/v1/')),
        'pluginApi'     => esc_url_raw(rest_url('wp-lkod/v1/')),
        'homeUrl'       => esc_url_raw(home_url('/')),
        'searchUrl'     => esc_url_raw(home_url('/vyhladavanie/')),
        'publishersUrl' => esc_url_raw(home_url('/poskytovatelia/')),
        /* Theme settings (managed in wp-admin → Nastavenia témy) */
        'heroTitle'           => (string) get_option('lkod_hero_title', 'Vitajte na stránke otvorených dát obce/mesta'),
        'articlesCount'       => (int) get_option('lkod_articles_count', 3),
    ], $extra);
    echo '<script>window.LKOD_CONFIG = ' . wp_json_encode($cfg) . ';</script>';
}

