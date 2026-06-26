<?php
defined('ABSPATH') || exit;
/* Read theme settings (managed under wp-admin → Nastavenia témy). */
$footer_logo_id  = (int) get_option('lkod_footer_logo', 0);
$footer_logo     = $footer_logo_id ? wp_get_attachment_image_url($footer_logo_id, 'medium') : '';
$footer_logo_alt = $footer_logo_id ? (string) get_post_meta($footer_logo_id, '_wp_attachment_image_alt', true) : '';

$copyright = trim((string) get_option('lkod_footer_copyright', ''));
if ($copyright === '') {
    $copyright = '© ' . date('Y') . ' ' . get_bloginfo('name');
}

$socials = array_filter([
    'Facebook'  => (string) get_option('lkod_social_facebook',  ''),
    'Instagram' => (string) get_option('lkod_social_instagram', ''),
    'LinkedIn'  => (string) get_option('lkod_social_linkedin',  ''),
]);

/* Päta — všetky navigačné odkazy v jednom riadku. Defaults pre RSS a Sitemap
   sa použijú vždy, ostatné len ak má admin vyplnené URL. */
$footer_nav_links = array_filter([
    'Zásady ochrany súkromia'   => (string) get_option('lkod_url_privacy',           ''),
    'Podmienky používania'      => (string) get_option('lkod_url_terms',             ''),
    'Vyhlásenie o prístupnosti' => (string) get_option('lkod_url_accessibility',     ''),
    'Kontakt na prevádzkovateľa'=> (string) get_option('lkod_url_operator_contact',  ''),
    'RSS'                       => trim((string) get_option('lkod_url_rss',     '')) ?: home_url('/feed/'),
    'Mapa stránky'              => trim((string) get_option('lkod_url_sitemap', '')) ?: home_url('/wp-sitemap.xml'),
]);
?>
<footer class="govuk-footer">
  <div class="govuk-width-container">

    <div class="lkod-footer__funding">
      <img src="<?php echo esc_url(get_template_directory_uri() . '/assets/images/funding/funding-strip.png'); ?>"
           alt="Financované Európskou úniou – NextGenerationEU. Plán obnovy. Ministerstvo investícií, regionálneho rozvoja a informatizácie Slovenskej republiky."
           width="1134" height="172" loading="lazy">
    </div>

    <div class="govuk-footer__meta">

      <div class="govuk-footer__meta-item govuk-footer__meta-item--grow">
        <?php if (!empty($socials)): ?>
          <h2 class="govuk-visually-hidden">Sociálne siete</h2>
          <ul class="govuk-footer__inline-list">
            <?php foreach ($socials as $label => $url): ?>
              <li class="govuk-footer__inline-list-item">
                <a class="govuk-footer__link"
                   href="<?php echo esc_url($url); ?>"
                   target="_blank" rel="noopener">
                  <?php echo esc_html($label); ?>
                </a>
              </li>
            <?php endforeach; ?>
          </ul>
        <?php endif; ?>

        <?php if (!empty($footer_nav_links)): ?>
          <ul class="govuk-footer__inline-list">
            <?php foreach ($footer_nav_links as $label => $url): ?>
              <li class="govuk-footer__inline-list-item">
                <a class="govuk-footer__link" href="<?php echo esc_url($url); ?>">
                  <?php echo esc_html($label); ?>
                </a>
              </li>
            <?php endforeach; ?>
          </ul>
        <?php endif; ?>

        <span class="govuk-footer__licence-description">
          <?php echo nl2br(esc_html($copyright)); ?>
        </span>
      </div>

      <?php if ($footer_logo): ?>
        <div class="govuk-footer__meta-item">
          <a class="govuk-footer__link govuk-footer__copyright-logo" href="<?php echo esc_url(home_url('/')); ?>">
            <img src="<?php echo esc_url($footer_logo); ?>"
                 alt="<?php echo esc_attr($footer_logo_alt ?: get_bloginfo('name')); ?>"
                 style="max-height:80px;max-width:100%;width:auto;display:block">
          </a>
        </div>
      <?php endif; ?>

    </div>
  </div>
</footer>
<?php wp_footer(); ?>
</body>
</html>
