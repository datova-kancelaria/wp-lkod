<?php
defined('ABSPATH') || exit;
/**
 * Šablóna stránky "Všetky články" (slug: vsetky-clanky).
 * Vypíše všetky publikované príspevky (post) ako idsk-card grid
 * so stránkovaním. Karty sledujú oficiálnu IDSK 3 vertikálnu kartu.
 */
get_header();

$paged = max(1, (int) get_query_var('paged') ?: (int) get_query_var('page'));
$q = new WP_Query([
    'post_type'      => 'post',
    'post_status'    => 'publish',
    'posts_per_page' => 12,
    'paged'          => $paged,
    'orderby'        => 'date',
    'order'          => 'DESC',
]);
?>
<main id="main">
  <div class="govuk-main-wrapper">
    <div class="govuk-width-container">

      <nav class="govuk-breadcrumbs" aria-label="Drobčekový navigačný pás">
        <ol class="govuk-breadcrumbs__list">
          <li class="govuk-breadcrumbs__list-item">
            <a class="govuk-breadcrumbs__link" href="<?php echo esc_url(home_url('/')); ?>">Otvorené dáta mesta/obce</a>
          </li>
          <li class="govuk-breadcrumbs__list-item">Všetky články</li>
        </ol>
      </nav>

      <h1 class="govuk-heading-l">Všetky články</h1>

      <?php if ($q->have_posts()) : ?>
        <div class="govuk-grid-row" style="display:flex;flex-wrap:wrap">
          <?php while ($q->have_posts()) : $q->the_post(); ?>
            <div class="govuk-grid-column-one-quarter" style="display:flex;margin-bottom:1.5rem">
              <div class="idsk-card idsk-card--vertical" style="width:100%">
                <?php if (has_post_thumbnail()) : ?>
                  <div class="idsk-card__image-wrapper">
                    <?php the_post_thumbnail('medium', ['alt' => esc_attr(get_the_title())]); ?>
                  </div>
                <?php endif; ?>
                <div class="idsk-card__content">
                  <h3 class="idsk-card__heading">
                    <a class="govuk-link" href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                  </h3>
                  <p class="idsk-card__description"><?php echo esc_html(wp_trim_words(get_the_excerpt(), 25)); ?></p>
                  <div class="idsk-card__date-tags">
                    <time datetime="<?php echo esc_attr(get_the_date('Y-m-d')); ?>"><?php echo esc_html(get_the_date('j.n.Y')); ?></time>
                  </div>
                </div>
              </div>
            </div>
          <?php endwhile; ?>
        </div>

        <?php
        $links = paginate_links([
            'total'     => $q->max_num_pages,
            'current'   => $paged,
            'prev_text' => '← Predchádzajúca',
            'next_text' => 'Zobraziť ďalšie →',
            'type'      => 'array',
        ]);
        if ($links && $q->max_num_pages > 1) :
        ?>
          <hr class="govuk-section-break govuk-section-break--m govuk-section-break--visible">
          <nav aria-label="Stránkovanie" style="display:flex;flex-wrap:wrap;gap:1rem;align-items:center">
            <?php foreach ($links as $link) : ?>
              <span class="govuk-body" style="margin:0"><?php echo wp_kses_post($link); ?></span>
            <?php endforeach; ?>
          </nav>
        <?php endif; ?>

      <?php else : ?>
        <p class="govuk-body">Zatiaľ neboli pridané žiadne články.</p>
      <?php endif; wp_reset_postdata(); ?>

    </div>
  </div>
</main>
<?php get_footer();
