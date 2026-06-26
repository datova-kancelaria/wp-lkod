<?php
/**
 * REST endpoint exposing values managed under "Nastavenia témy".
 *
 *   GET /wp-json/tema/v1/nastavenia
 *
 * Public, read-only. No authentication, no parameters. Returns JSON.
 */
defined('ABSPATH') || exit;

add_action('rest_api_init', static function (): void {
    register_rest_route('tema/v1', '/nastavenia', [
        'methods'             => WP_REST_Server::READABLE,
        'callback'            => 'lkod_rest_nastavenia',
        'permission_callback' => '__return_true',
    ]);
});

function lkod_rest_nastavenia(WP_REST_Request $req): WP_REST_Response {
    return new WP_REST_Response([
        'homepage' => [
            'hero_title'             => (string) get_option('lkod_hero_title', ''),
            'articles_count'         => (int)    get_option('lkod_articles_count', 3),
        ],
        'branding' => [
            'header_logo'         => lkod_format_attachment((int) get_option('lkod_header_logo', 0)),
            'site_name_override'  => (string) get_option('lkod_site_name_override', ''),
        ],
        'footer' => [
            'logo'      => lkod_format_attachment((int) get_option('lkod_footer_logo', 0)),
            'copyright' => (string) get_option('lkod_footer_copyright', ''),
        ],
        'social' => [
            'facebook'  => (string) get_option('lkod_social_facebook',  ''),
            'instagram' => (string) get_option('lkod_social_instagram', ''),
            'linkedin'  => (string) get_option('lkod_social_linkedin',  ''),
        ],
        'legal' => [
            'privacy'       => (string) get_option('lkod_url_privacy',       ''),
            'terms'         => (string) get_option('lkod_url_terms',         ''),
            'accessibility' => (string) get_option('lkod_url_accessibility', ''),
        ],
    ]);
}

/**
 * Resolve attachment ID to { id, url, alt } or null when not set / not found.
 */
function lkod_format_attachment(int $att_id): ?array {
    if ($att_id <= 0) return null;
    $url = wp_get_attachment_image_url($att_id, 'full');
    if (!$url) return null;
    return [
        'id'  => $att_id,
        'url' => $url,
        'alt' => (string) get_post_meta($att_id, '_wp_attachment_image_alt', true),
    ];
}
