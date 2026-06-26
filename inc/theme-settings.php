<?php
/**
 * LKOD theme — admin settings page "Nastavenia témy".
 *
 * Stores values in wp_options under option group `lkod_theme_settings`.
 * Uses the WordPress Settings API (built-in nonce + sanitization).
 *
 * Fields stored:
 *
 *   Homepage
 *     lkod_hero_title              (string) main heading on homepage
 *     lkod_articles_count          (int 0-12) cards in "Články" (0 hides section)
 *
 *   Branding
 *     lkod_header_logo             (int) attachment ID of header logo
 *     lkod_site_name_override      (string) overrides WP bloginfo('name') in header
 *
 *   Päta
 *     lkod_footer_logo             (int) attachment ID of footer logo
 *     lkod_footer_copyright        (string) plain-text copyright line
 *
 *   Sociálne siete
 *     lkod_social_facebook         (string URL)
 *     lkod_social_instagram        (string URL)
 *     lkod_social_linkedin         (string URL)
 *
 *   Právne odkazy
 *     lkod_url_privacy             (string URL) Zásady ochrany súkromia
 *     lkod_url_terms               (string URL) Podmienky používania
 *     lkod_url_accessibility       (string URL) Vyhlásenie o prístupnosti
 */
defined('ABSPATH') || exit;

/* Admin menu entry. */
add_action('admin_menu', static function (): void {
    add_menu_page(
        'Nastavenia témy',
        'Nastavenia témy',
        'manage_options',
        'lkod-nastavenia',
        'lkod_render_settings_page',
        'dashicons-admin-customizer',
        61
    );
});

/* Register settings + fields. */
add_action('admin_init', static function (): void {
    $group = 'lkod_theme_settings';

    // Homepage
    register_setting($group, 'lkod_hero_title',            ['type' => 'string',  'sanitize_callback' => 'sanitize_text_field', 'default' => 'Vitajte na stránke otvorených dát obce/mesta']);
    register_setting($group, 'lkod_articles_count',        ['type' => 'integer', 'sanitize_callback' => 'lkod_sanitize_count_0_12', 'default' => 3]);

    // Branding
    register_setting($group, 'lkod_header_logo',           ['type' => 'integer', 'sanitize_callback' => 'absint',              'default' => 0]);
    register_setting($group, 'lkod_site_name_override',    ['type' => 'string',  'sanitize_callback' => 'sanitize_text_field', 'default' => '']);

    // Päta
    register_setting($group, 'lkod_footer_logo',           ['type' => 'integer', 'sanitize_callback' => 'absint',              'default' => 0]);
    register_setting($group, 'lkod_footer_copyright',      ['type' => 'string',  'sanitize_callback' => 'sanitize_text_field', 'default' => '']);

    // Sociálne siete
    register_setting($group, 'lkod_social_facebook',       ['type' => 'string',  'sanitize_callback' => 'esc_url_raw',         'default' => '']);
    register_setting($group, 'lkod_social_instagram',      ['type' => 'string',  'sanitize_callback' => 'esc_url_raw',         'default' => '']);
    register_setting($group, 'lkod_social_linkedin',       ['type' => 'string',  'sanitize_callback' => 'esc_url_raw',         'default' => '']);

    // Právne odkazy
    register_setting($group, 'lkod_url_privacy',           ['type' => 'string',  'sanitize_callback' => 'esc_url_raw',         'default' => '']);
    register_setting($group, 'lkod_url_terms',             ['type' => 'string',  'sanitize_callback' => 'esc_url_raw',         'default' => '']);
    register_setting($group, 'lkod_url_accessibility',     ['type' => 'string',  'sanitize_callback' => 'esc_url_raw',         'default' => '']);

    // Kontakt prevádzkovateľa + navigačné odkazy
    register_setting($group, 'lkod_url_operator_contact',  ['type' => 'string',  'sanitize_callback' => 'esc_url_raw',         'default' => '']);
    register_setting($group, 'lkod_url_rss',               ['type' => 'string',  'sanitize_callback' => 'esc_url_raw',         'default' => '']);
    register_setting($group, 'lkod_url_sitemap',           ['type' => 'string',  'sanitize_callback' => 'esc_url_raw',         'default' => '']);

    /* --- Section: Homepage --------------------------------------- */
    add_settings_section(
        'lkod_section_homepage',
        'Úvodná stránka',
        static function (): void {
            echo '<p>Nastavenia hero sekcie a počtu zobrazených položiek na úvodnej stránke.</p>';
        },
        'lkod-nastavenia'
    );

    add_settings_field('lkod_hero_title', 'Hlavný nadpis', 'lkod_field_text', 'lkod-nastavenia', 'lkod_section_homepage',
        ['option' => 'lkod_hero_title', 'label_for' => 'lkod_hero_title', 'placeholder' => 'Vitajte na stránke...']);

    add_settings_field('lkod_articles_count', 'Počet článkov', 'lkod_field_number', 'lkod-nastavenia', 'lkod_section_homepage',
        ['option' => 'lkod_articles_count', 'label_for' => 'lkod_articles_count', 'min' => 0, 'max' => 12, 'help' => '0–12. Hodnota 0 skryje celú sekciu Články.']);

    /* --- Section: Branding --------------------------------------- */
    add_settings_section(
        'lkod_section_branding',
        'Branding',
        static function (): void {
            echo '<p>Logo a názov, ktoré sa zobrazia v hlavičke stránky.</p>';
        },
        'lkod-nastavenia'
    );

    add_settings_field('lkod_header_logo', 'Logo v hlavičke', 'lkod_field_media', 'lkod-nastavenia', 'lkod_section_branding',
        ['option' => 'lkod_header_logo', 'label_for' => 'lkod_header_logo']);

    add_settings_field('lkod_site_name_override', 'Názov v hlavičke', 'lkod_field_text', 'lkod-nastavenia', 'lkod_section_branding',
        ['option' => 'lkod_site_name_override', 'label_for' => 'lkod_site_name_override', 'placeholder' => get_bloginfo('name'),
         'help' => 'Ak je prázdne, použije sa WP názov stránky („' . esc_html(get_bloginfo('name')) . '").']);

    /* --- Section: Päta ------------------------------------------- */
    add_settings_section(
        'lkod_section_footer',
        'Päta stránky',
        static function (): void {
            echo '<p>Nastavenia, ktoré sa zobrazia v päte (footer).</p>';
        },
        'lkod-nastavenia'
    );

    add_settings_field('lkod_footer_logo', 'Logo v päte', 'lkod_field_media', 'lkod-nastavenia', 'lkod_section_footer',
        ['option' => 'lkod_footer_logo', 'label_for' => 'lkod_footer_logo']);

    add_settings_field('lkod_footer_copyright', 'Copyright text', 'lkod_field_text', 'lkod-nastavenia', 'lkod_section_footer',
        ['option' => 'lkod_footer_copyright', 'label_for' => 'lkod_footer_copyright', 'placeholder' => '© 2026 Názov organizácie']);

    /* --- Section: Sociálne siete -------------------------------- */
    add_settings_section(
        'lkod_section_social',
        'Sociálne siete',
        static function (): void {
            echo '<p>URL adresy na profily organizácie. Prázdne pole = odkaz sa nezobrazí.</p>';
        },
        'lkod-nastavenia'
    );

    foreach ([
        'lkod_social_facebook'  => 'Facebook',
        'lkod_social_instagram' => 'Instagram',
        'lkod_social_linkedin'  => 'LinkedIn',
    ] as $opt => $label) {
        add_settings_field($opt, $label, 'lkod_field_url', 'lkod-nastavenia', 'lkod_section_social',
            ['option' => $opt, 'label_for' => $opt, 'placeholder' => 'https://...']);
    }

    /* --- Section: Právne odkazy --------------------------------- */
    add_settings_section(
        'lkod_section_legal',
        'Právne odkazy',
        static function (): void {
            echo '<p>Odkazy zobrazené v päte. Každý sa zobrazí len ak je URL vyplnená.</p>';
        },
        'lkod-nastavenia'
    );

    foreach ([
        'lkod_url_privacy'       => 'Zásady ochrany súkromia',
        'lkod_url_terms'         => 'Podmienky používania',
        'lkod_url_accessibility' => 'Vyhlásenie o prístupnosti',
    ] as $opt => $label) {
        add_settings_field($opt, $label, 'lkod_field_url', 'lkod-nastavenia', 'lkod_section_legal',
            ['option' => $opt, 'label_for' => $opt, 'placeholder' => 'https://...']);
    }

    /* --- Section: Kontakt prevádzkovateľa + navigačné odkazy ------ */
    add_settings_section(
        'lkod_section_operator',
        'Kontakt prevádzkovateľa a navigačné odkazy',
        static function (): void {
            echo '<p>Údaje o prevádzkovateľovi webového sídla a doplnkové odkazy. Zobrazia sa v päte. Prázdne polia sa nezobrazia.</p>';
        },
        'lkod-nastavenia'
    );

    add_settings_field('lkod_url_operator_contact', 'Kontakt na prevádzkovateľa', 'lkod_field_url', 'lkod-nastavenia', 'lkod_section_operator',
        ['option' => 'lkod_url_operator_contact', 'label_for' => 'lkod_url_operator_contact',
         'placeholder' => 'https://... alebo mailto:kontakt@example.sk',
         'help' => 'URL adresa kontaktnej stránky alebo mailto: odkaz.']);

    add_settings_field('lkod_url_rss', 'Odkaz na RSS kanál', 'lkod_field_url', 'lkod-nastavenia', 'lkod_section_operator',
        ['option' => 'lkod_url_rss', 'label_for' => 'lkod_url_rss', 'placeholder' => home_url('/feed/'),
         'help' => 'Ak je prázdne, zobrazí sa default WP feed: ' . esc_html(home_url('/feed/'))]);

    add_settings_field('lkod_url_sitemap', 'Odkaz na mapu stránky', 'lkod_field_url', 'lkod-nastavenia', 'lkod_section_operator',
        ['option' => 'lkod_url_sitemap', 'label_for' => 'lkod_url_sitemap', 'placeholder' => home_url('/wp-sitemap.xml'),
         'help' => 'Ak je prázdne, zobrazí sa default WP sitemap: ' . esc_html(home_url('/wp-sitemap.xml'))]);
});

/* Sanitization helpers. */
function lkod_sanitize_count_0_12($value): int {
    $n = (int) $value;
    return max(0, min(12, $n));
}

/* Field renderers. */
function lkod_field_text(array $args): void {
    $id    = esc_attr($args['option']);
    $value = (string) get_option($args['option'], '');
    $ph    = esc_attr($args['placeholder'] ?? '');
    printf(
        '<input type="text" id="%1$s" name="%1$s" value="%2$s" class="regular-text" placeholder="%3$s">',
        $id, esc_attr($value), $ph
    );
    if (!empty($args['help'])) {
        printf('<p class="description">%s</p>', esc_html($args['help']));
    }
}

function lkod_field_textarea(array $args): void {
    $id    = esc_attr($args['option']);
    $value = (string) get_option($args['option'], '');
    $ph    = esc_attr($args['placeholder'] ?? '');
    printf(
        '<textarea id="%1$s" name="%1$s" rows="3" class="large-text" placeholder="%3$s">%2$s</textarea>',
        $id, esc_textarea($value), $ph
    );
    if (!empty($args['help'])) {
        printf('<p class="description">%s</p>', esc_html($args['help']));
    }
}

function lkod_field_url(array $args): void {
    $id    = esc_attr($args['option']);
    $value = (string) get_option($args['option'], '');
    $ph    = esc_attr($args['placeholder'] ?? 'https://');
    printf(
        '<input type="url" id="%1$s" name="%1$s" value="%2$s" class="regular-text" placeholder="%3$s">',
        $id, esc_attr($value), $ph
    );
    if (!empty($args['help'])) {
        printf('<p class="description">%s</p>', esc_html($args['help']));
    }
}

function lkod_field_email(array $args): void {
    $id    = esc_attr($args['option']);
    $value = (string) get_option($args['option'], '');
    $ph    = esc_attr($args['placeholder'] ?? '');
    printf(
        '<input type="email" id="%1$s" name="%1$s" value="%2$s" class="regular-text" placeholder="%3$s">',
        $id, esc_attr($value), $ph
    );
    if (!empty($args['help'])) {
        printf('<p class="description">%s</p>', esc_html($args['help']));
    }
}

function lkod_field_number(array $args): void {
    $id    = esc_attr($args['option']);
    $value = (int) get_option($args['option'], 0);
    $min   = isset($args['min']) ? (int) $args['min'] : 0;
    $max   = isset($args['max']) ? (int) $args['max'] : 100;
    printf(
        '<input type="number" id="%1$s" name="%1$s" value="%2$d" min="%3$d" max="%4$d" class="small-text">',
        $id, $value, $min, $max
    );
    if (!empty($args['help'])) {
        printf('<p class="description">%s</p>', esc_html($args['help']));
    }
}

function lkod_field_media(array $args): void {
    $id     = esc_attr($args['option']);
    $att_id = (int) get_option($args['option'], 0);
    $src    = $att_id ? wp_get_attachment_image_url($att_id, 'medium') : '';
    ?>
    <div class="lkod-media-field" data-target="<?php echo $id; ?>">
        <div class="lkod-media-preview" style="margin-bottom:8px;">
            <?php if ($src): ?>
                <img src="<?php echo esc_url($src); ?>" alt="" style="max-width:200px;height:auto;border:1px solid #ddd;">
            <?php endif; ?>
        </div>
        <input type="hidden" id="<?php echo $id; ?>" name="<?php echo $id; ?>" value="<?php echo $att_id; ?>">
        <button type="button" class="button lkod-media-select">Vybrať obrázok</button>
        <button type="button" class="button lkod-media-clear" <?php echo $att_id ? '' : 'style="display:none"'; ?>>Odstrániť</button>
    </div>
    <?php
}

/* Settings page renderer. */
function lkod_render_settings_page(): void {
    if (!current_user_can('manage_options')) return;
    ?>
    <div class="wrap">
        <h1><?php echo esc_html(get_admin_page_title()); ?></h1>

        <?php if (isset($_GET['settings-updated'])): ?>
            <div class="notice notice-success is-dismissible"><p>Nastavenia uložené.</p></div>
        <?php endif; ?>

        <form action="options.php" method="post">
            <?php
            settings_fields('lkod_theme_settings');
            do_settings_sections('lkod-nastavenia');
            submit_button('Uložiť zmeny');
            ?>
        </form>
    </div>
    <?php
}

/* Enqueue the WP media uploader + custom JS, only on the settings page. */
add_action('admin_enqueue_scripts', static function (string $hook): void {
    if ($hook !== 'toplevel_page_lkod-nastavenia') return;

    wp_enqueue_media();

    $js = <<<'JS'
jQuery(function ($) {
  $('.lkod-media-field').each(function () {
    var $wrap   = $(this);
    var target  = $wrap.data('target');
    var $input  = $wrap.find('input[type=hidden]');
    var $preview= $wrap.find('.lkod-media-preview');
    var $clear  = $wrap.find('.lkod-media-clear');

    $wrap.on('click', '.lkod-media-select', function (e) {
      e.preventDefault();
      var frame = wp.media({
        title: 'Vybrať obrázok',
        button: { text: 'Použiť obrázok' },
        library: { type: 'image' },
        multiple: false,
      });
      frame.on('select', function () {
        var att = frame.state().get('selection').first().toJSON();
        $input.val(att.id);
        $preview.html('<img src="' + att.url + '" alt="" style="max-width:200px;height:auto;border:1px solid #ddd;">');
        $clear.show();
      });
      frame.open();
    });

    $wrap.on('click', '.lkod-media-clear', function (e) {
      e.preventDefault();
      $input.val('');
      $preview.empty();
      $clear.hide();
    });
  });
});
JS;
    wp_add_inline_script('media-editor', $js);
});
