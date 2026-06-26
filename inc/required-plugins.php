<?php
/**
 * Required plugin: WP-LKOD (bundled via TGM Plugin Activation).
 *
 * The theme ships the official "Lokálny katalóg otvorených dát" plugin as a
 * pre-packaged zip in inc/required-plugins/. On theme activation the admin is
 * prompted to install + activate it (one install package for the whole site).
 */
defined('ABSPATH') || exit;

require_once get_template_directory() . '/inc/class-tgm-plugin-activation.php';

add_action('tgmpa_register', static function (): void {
    $plugins = [
        [
            'name'     => 'Lokálny katalóg otvorených dát',
            'slug'     => 'wp-lkod',
            'source'   => 'wp-lkod.zip',
            'required' => true,
            'version'  => '1.0',
        ],
    ];

    $config = [
        'id'           => 'lkod-idsk3',
        'default_path' => get_template_directory() . '/inc/required-plugins/',
        'menu'         => 'tgmpa-install-plugins',
        'parent_slug'  => 'themes.php',
        'capability'   => 'edit_theme_options',
        'has_notices'  => true,
        'dismissable'  => false,
        'is_automatic' => false, // prompt — admin klikne Inštalovať/Aktivovať
        'strings'      => [
            'page_title'                  => 'Inštalovať povinné pluginy',
            'menu_title'                  => 'Inštalovať pluginy',
            'installing'                  => 'Inštaluje sa plugin: %s',
            'updating'                    => 'Aktualizuje sa plugin: %s',
            'oops'                        => 'Niečo sa pokazilo s API pluginu.',
            'notice_can_install_required' => _n_noop(
                'Téma LKOD IDSK 3 vyžaduje plugin: %1$s.',
                'Téma LKOD IDSK 3 vyžaduje tieto pluginy: %1$s.',
                'lkod-idsk3'
            ),
            'notice_ask_to_update' => _n_noop(
                'Nasledujúci plugin treba aktualizovať na najnovšiu verziu, aby bol kompatibilný s témou LKOD IDSK 3: %1$s.',
                'Nasledujúce pluginy treba aktualizovať na najnovšiu verziu, aby boli kompatibilné s témou LKOD IDSK 3: %1$s.',
                'lkod-idsk3'
            ),
            'notice_can_activate_required' => _n_noop(
                'Nasledujúci povinný plugin je momentálne neaktívny: %1$s.',
                'Nasledujúce povinné pluginy sú momentálne neaktívne: %1$s.',
                'lkod-idsk3'
            ),
            'install_link' => _n_noop(
                'Spustiť inštaláciu pluginu',
                'Spustiť inštaláciu pluginov',
                'lkod-idsk3'
            ),
            'update_link' => _n_noop(
                'Spustiť aktualizáciu pluginu',
                'Spustiť aktualizáciu pluginov',
                'lkod-idsk3'
            ),
            'activate_link' => _n_noop(
                'Aktivovať plugin',
                'Aktivovať pluginy',
                'lkod-idsk3'
            ),
            'return'                      => 'Späť na inštaláciu pluginov',
            'plugin_activated'            => 'Plugin bol úspešne aktivovaný.',
            'activated_successfully'      => 'Nasledujúci plugin bol úspešne aktivovaný:',
            'plugin_already_active'       => 'Žiadna akcia — plugin „%1$s" je už aktívny.',
            'plugin_needs_higher_version' => 'Plugin nebol aktivovaný — vyžaduje sa novšia verzia pluginu „%1$s".',
            'complete'                    => 'Všetky pluginy boli úspešne nainštalované a aktivované. %1$s',
            'dismiss'                     => 'Zatvoriť toto upozornenie',
            'notice_cannot_install_activate' => 'Je potrebné nainštalovať alebo aktivovať jeden či viac pluginov.',
            'contact_admin'               => 'Kontaktujte správcu tejto stránky pre pomoc.',
            'nag_type'                    => 'notice-warning',
        ],
    ];

    tgmpa($plugins, $config);
});
