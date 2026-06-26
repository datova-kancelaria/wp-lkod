<?php
defined('ABSPATH') || exit;
/**
 * Univerzálny fallback šablóny pre WordPress.
 *
 * WordPress vyžaduje aby každá klasická téma mala index.php — používa sa
 * keď žiadna špecifickejšia šablóna (single-*, page-*, archive-*, …)
 * nezhoduje s aktuálnym dotazom.
 *
 * V tejto téme má vlastné šablóny:
 *   front-page.php           — homepage
 *   page-vyhladavanie.php    — /vyhladavanie/
 *   page-poskytovatelia.php  — /poskytovatelia/
 *   single-dcat_dataset.php  — detail datasetu
 *
 * index.php pokryje: blogové príspevky, archívy, výsledky vyhľadávania,
 * generické stránky a 404.
 */
get_header(); ?>

<main id="main">
  <div class="govuk-main-wrapper">
    <div class="govuk-width-container">

      <?php if (have_posts()) : ?>

        <?php if (is_archive() || is_home()) : ?>
          <h1 class="govuk-heading-l"><?php echo esc_html(get_the_archive_title() ?: __('Príspevky', 'lkod-idsk3')); ?></h1>
        <?php elseif (is_search()) : ?>
          <h1 class="govuk-heading-l"><?php
            printf(esc_html__('Výsledky vyhľadávania: %s', 'lkod-idsk3'), esc_html(get_search_query()));
          ?></h1>
        <?php endif; ?>

        <?php while (have_posts()) : the_post(); ?>
          <?php if (is_singular()) : ?>
            <!-- Single article/page view — bez karty -->
            <article <?php post_class(); ?>>
              <h1 class="govuk-heading-l"><?php the_title(); ?></h1>
              <?php if (!is_page()) : ?>
                <p class="govuk-caption-m" style="font-style:italic"><?php echo esc_html(get_the_date()); ?></p>
              <?php endif; ?>
              <div class="govuk-body">
                <?php the_content(); ?>
              </div>
            </article>
          <?php else : ?>
            <!-- List view (archive/home/search) — idsk-card -->
            <article <?php post_class('idsk-card idsk-card--vertical'); ?>>
              <div class="idsk-card__heading">
                <h2 class="govuk-heading-m">
                  <a class="govuk-link" href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                </h2>
                <p class="govuk-caption-m"><?php echo esc_html(get_the_date()); ?></p>
              </div>
              <p class="idsk-card__description"><?php echo esc_html(get_the_excerpt()); ?></p>
            </article>
          <?php endif; ?>
        <?php endwhile; ?>

        <?php
        the_posts_pagination([
            'mid_size'  => 2,
            'prev_text' => __('← Predchádzajúca', 'lkod-idsk3'),
            'next_text' => __('Zobraziť ďalšie →', 'lkod-idsk3'),
        ]);
        ?>

      <?php else : ?>

        <h1 class="govuk-heading-l">
          <?php echo esc_html(is_404() ? __('Stránka nenájdená', 'lkod-idsk3') : __('Žiadne príspevky', 'lkod-idsk3')); ?>
        </h1>
        <p class="govuk-body">
          <?php if (is_404()) : ?>
            <?php esc_html_e('Hľadaný obsah neexistuje alebo bol presunutý.', 'lkod-idsk3'); ?>
            <a class="govuk-link" href="<?php echo esc_url(home_url('/')); ?>"><?php esc_html_e('Vrátiť sa na domovskú stránku', 'lkod-idsk3'); ?></a>.
          <?php else : ?>
            <?php esc_html_e('Nenašli sa žiadne príspevky.', 'lkod-idsk3'); ?>
          <?php endif; ?>
        </p>

      <?php endif; ?>

    </div>
  </div>
</main>

<?php get_footer(); ?>
