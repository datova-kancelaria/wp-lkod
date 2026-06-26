<?php defined('ABSPATH') || exit; ?><!doctype html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo('charset'); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1">
<?php wp_head(); ?>
<style>
/* Mobile typography fix — IDSK 3 v3.0.0-beta.0 má base font-size 2.25rem (36px)
   pre govuk-body, govuk-label atď. ktoré redukuje až @media (min-width:40.0625em).
   Tu zmenšujeme na čitateľnú veľkosť aj na mobile (<641px). */
@media (max-width: 40.0525em) {
  .govuk-header__navigation-item a,
  .govuk-body,
  .govuk-body-m,
  .govuk-label,
  .govuk-fieldset__legend,
  .govuk-table,
  .govuk-table__header,
  .govuk-table__cell,
  .govuk-details,
  .govuk-caption-m {
    font-size: 1.25rem;
    line-height: 1.5;
  }
}

/* Tabuľka v distribúcia accordion — bez okrajov riadkov/buniek */
.govuk-accordion__section-content .govuk-table,
.govuk-accordion__section-content .govuk-table__row,
.govuk-accordion__section-content .govuk-table__header,
.govuk-accordion__section-content .govuk-table__cell {
  border: none;
  box-shadow: none;
}

/* Menu hover — odstrániť farebnú zmenu, ponechať iba podčiarknutie */
.govuk-header__navigation-item a:hover {
  color: inherit;
  background-color: transparent;
}

/* Focus okolo loga v hlavičke.
   IDSK 3 nastavuje homepage linku display:inline a takmer neviditeľný
   box-shadow, takže zdedený outline (.govuk-header__link:focus) sa pri
   blokovom <img> vykreslí ako malý štvorček vedľa loga. Link spravíme
   inline-block, nech focus ring obkolesí celé logo. */
.govuk-header__link--homepage {
  display: inline-block;
}
.govuk-header__link--homepage:focus {
  outline: 3px solid #d96e00;
  outline-offset: 3px;
  box-shadow: none;
}

/* Search input — Edge má iné vertikálne zarovnanie ako Firefox.
   Vynulujeme padding-bottom aby spodná čiara lícovala v oboch prehliadačoch. */
.idsk-searchbar__wrapper input.govuk-input {
  padding-top: 0;
  padding-bottom: 0;
}

/* Medzera medzi breadcrumbs a hlavným nadpisom stránky. */
.govuk-breadcrumbs {
  margin-bottom: 2rem;
}

/* Nadpisy kariet (články) — čitateľnejší riadkovač pri viacriadkových názvoch. */
.idsk-card__heading {
  line-height: 1.5;
}

/* Obmedzenie šírky obsahu na 1120px (IDSK 3 default je 1440px). */
.govuk-width-container {
  max-width: 1120px;
}

/* Grid kategórií — 1 stĺpec na mobile, 3 na tablete, 5 na desktope.
   CSS grid (namiesto flex) garantuje rovnaké stĺpce na plnú šírku bez
   sub-pixel zalamovania. */
.lkod-category-grid {
  display: grid;
  gap: 1rem;
  grid-template-columns: 1fr;
}
@media (min-width: 40.0625em) {
  .lkod-category-grid { grid-template-columns: repeat(3, 1fr); }
}
@media (min-width: 61.875em) {
  .lkod-category-grid { grid-template-columns: repeat(5, 1fr); }
}

/* Mobile fix — jazykový dropdown sa prekrýval s ľavou skupinou (SK/e-Gov).
   Dôvody: idsk-secondary-navigation__dropdown má position:absolute;right:0,
   a navyše dedí width:100% z .idsk-dropdown__wrapper. Na mobile (<641px)
   ho prevedieme na bežný flex item s automatickou šírkou — __header má
   display:flex;justify-content:space-between, takže ho korektne odsadí vpravo. */
@media (max-width: 40.0525em) {
  .idsk-secondary-navigation__header {
    flex-wrap: wrap;
    align-items: center;
    gap: 0.25rem 1rem;
  }
  .idsk-secondary-navigation__dropdown {
    position: relative;
    right: auto;
    width: auto;
  }
  /* Menšie nadpisy kategórií (signpost) na mobile. */
  .govuk-signpost__title {
    font-size: 1.5rem;
  }
}
</style>
</head>
<body <?php body_class(); ?> style="overflow-x:hidden">
<?php wp_body_open(); ?>
<script>document.body.className += ' govuk-frontend-supported';</script>
<?php /* Skip-link bude doplnený neskôr v rámci accessibility úprav.
   <a class="govuk-skip-link" href="#main">Preskočiť na obsah</a>
*/ ?>

<div class="govuk-header__wrapper">
  <header class="govuk-header idsk-shadow-head" data-module="govuk-header">

    <!-- ====== Sekundárna navigácia (oficiálna gov.sk + jazyk) ====== -->
    <div class="govuk-header__container">
      <div class="idsk-secondary-navigation govuk-width-container">
        <div class="idsk-secondary-navigation__header">

          <div class="idsk-secondary-navigation__heading">
            <div class="idsk-secondary-navigation__heading-title">
              <span class="idsk-secondary-navigation__heading-mobile">SK</span>
              <span class="idsk-secondary-navigation__heading-desktop">Oficiálna stránka</span>
              <button class="govuk-button govuk-button--texted--inverse idsk-secondary-navigation__heading-button"
                      aria-expanded="false"
                      aria-label="Oficiálna stránka verejnej správy">
                <span class="idsk-secondary-navigation__heading-mobile">e-Gov</span>
                <span class="idsk-secondary-navigation__heading-desktop"><b>verejnej správy</b></span>
                <span class="material-icons">arrow_drop_down</span>
              </button>
            </div>

            <div class="idsk-secondary-navigation__body hidden" data-testid="secnav-children">
              <div class="idsk-secondary-navigation__text">
                <div>
                  <h3 class="govuk-body-s"><b>Doména gov.sk je oficiálna</b></h3>
                  <p class="govuk-body-s">Toto je oficiálna webová stránka orgánu verejnej moci Slovenskej republiky. Oficiálne stránky využívajú najmä doménu gov.sk.</p>
                </div>
                <div>
                  <h3 class="govuk-body-s"><b>Táto stránka je zabezpečená</b></h3>
                  <p class="govuk-body-s max-width77-desktop">Buďte pozorní a vždy sa uistite, že zdieľate informácie iba cez zabezpečenú webovú stránku verejnej správy SR.</p>
                </div>
              </div>
            </div>
          </div>

          <?php /* Jazykový prepínač skrytý — preklad sa zatiaľ nepoužíva.
          <div class="idsk-dropdown__wrapper idsk-secondary-navigation__dropdown" data-pseudolabel="jazykové menu">
            <button class="govuk-button govuk-button--texted--inverse idsk-secondary-navigation__heading-button idsk-dropdown"
                    aria-label="Rozbaliť jazykové menu"
                    aria-haspopup="listbox">
              <span>slovenčina</span>
              <span class="material-icons" aria-hidden="true">arrow_drop_down</span>
            </button>
            <ul class="idsk-dropdown__options idsk-shadow-medium">
              <li class="idsk-dropdown__option idsk-pseudolabel__wrapper" data-pseudolabel="eng">
                <a href="#" lang="en">eng</a>
              </li>
              <li class="idsk-dropdown__option idsk-pseudolabel__wrapper" data-pseudolabel="slo">
                <a href="#" lang="sk">slo</a>
              </li>
            </ul>
          </div>
          */ ?>

        </div>
      </div>
    </div>

    <!-- ====== Predheader: logo + mobile menu + search ====== -->
    <div class="govuk-predheader govuk-width-container">
      <div class="govuk-header__logo">
        <a href="<?php echo esc_url(home_url('/')); ?>"
           class="govuk-header__link govuk-header__link--homepage"
           title="Odkaz na titulnú stránku">
          <?php
          /* Header logo + name + tagline z "Nastavenia témy". */
          $header_logo_id = (int) get_option('lkod_header_logo', 0);
          $header_logo    = $header_logo_id ? wp_get_attachment_image_url($header_logo_id, 'full') : '';
          $header_logo_alt= $header_logo_id ? (string) get_post_meta($header_logo_id, '_wp_attachment_image_alt', true) : '';
          $site_name      = trim((string) get_option('lkod_site_name_override', '')) ?: get_bloginfo('name');
          ?>
          <?php if ($header_logo) : ?>
            <img src="<?php echo esc_url($header_logo); ?>"
                 alt="<?php echo esc_attr($header_logo_alt ?: ('Logo ' . $site_name)); ?>"
                 style="max-height:60px;max-width:100%;width:auto;display:block">
          <?php else : ?>
            <span class="govuk-header__logotype">
              <span class="govuk-header__logotype-text"><?php echo esc_html($site_name); ?></span>
            </span>
          <?php endif; ?>
        </a>
      </div>

      <div class="govuk-header__btns-search">
        <div class="govuk-header__mobile-menu desktop-hidden">
          <button type="button"
                  class="govuk-header__menu-button font-bold govuk-js-header-toggle"
                  aria-controls="navigation"
                  hidden>Menu</button>
        </div>

        <form class="idsk-searchbar__wrapper" role="search"
              method="get" action="<?php echo esc_url(home_url('/vyhladavanie/')); ?>"
              style="width:100%;max-width:280px;align-self:center">
          <label for="lkod-header-search" class="govuk-visually-hidden">Vyhľadávanie</label>
          <input class="govuk-input" type="search" id="lkod-header-search"
                 placeholder="Zadajte hľadaný výraz" title="Zadajte hľadaný výraz" name="q"
                 style="width:100%;min-width:0;height:2.4rem;font-size:1.1875rem;line-height:normal">
          <button type="submit" class="govuk-button govuk-button__basic" aria-label="Hľadať">
            <span class="material-icons" aria-hidden="true">search</span>
          </button>
        </form>
      </div>
    </div>

    <!-- ====== Hlavná navigácia (vo vnútri <header>!) ====== -->
    <nav id="navigation" aria-label="Menu" class="govuk-header__navigation govuk-width-container">
      <span class="text">Menu</span>
      <div class="govuk-header__navigation-list">
        <?php lkod_render_header_nav(); ?>
      </div>
    </nav>

  </header>
</div>
<?php
/**
 * Render primary header navigation as <ul> with IDSK 3 classes.
 * Marks the current page with --active + aria-current="page".
 */
function lkod_render_header_nav(): void {
    $current = trailingslashit(home_url(add_query_arg([], $GLOBALS['wp']->request)));

    $items = [
        home_url('/')                => __('Domov', 'lkod-idsk3'),
        home_url('/vyhladavanie/')   => __('Vyhľadávanie', 'lkod-idsk3'),
        home_url('/poskytovatelia/') => __('Poskytovatelia dát', 'lkod-idsk3'),
    ];

    if (has_nav_menu('primary')) {
        wp_nav_menu([
            'theme_location' => 'primary',
            'container'      => false,
            'items_wrap'     => '<ul>%3$s</ul>',
            'walker'         => new Lkod_Header_Nav_Walker(),
        ]);
        return;
    }

    echo '<ul>';
    foreach ($items as $url => $label) {
        $is_active = trailingslashit($url) === $current;
        printf(
            '<li class="govuk-header__navigation-item%s"%s><a class="govuk-header__link" href="%s">%s</a></li>',
            $is_active ? ' govuk-header__navigation-item--active' : '',
            $is_active ? ' aria-current="page"' : '',
            esc_url($url), esc_html($label)
        );
    }
    echo '</ul>';
}

/** Walker that emits IDSK 3 nav-item classes for wp_nav_menu output. */
class Lkod_Header_Nav_Walker extends Walker_Nav_Menu {
    public function start_el(&$output, $item, $depth = 0, $args = null, $id = 0) {
        $classes  = ['govuk-header__navigation-item'];
        $current  = in_array('current-menu-item', (array) $item->classes, true)
                 || in_array('current_page_item', (array) $item->classes, true);
        if ($current) $classes[] = 'govuk-header__navigation-item--active';

        $output .= sprintf(
            '<li class="%s"%s><a class="govuk-header__link" href="%s">%s</a>',
            esc_attr(implode(' ', $classes)),
            $current ? ' aria-current="page"' : '',
            esc_url($item->url),
            esc_html($item->title)
        );
    }
}
