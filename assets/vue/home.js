/* global Vue, LkodCodelists */
(function () {
  const { createApp, ref, onMounted } = Vue;
  const cfg = window.LKOD_CONFIG;
  const CL  = window.LkodCodelists;

  createApp({
    setup() {
      const posts      = ref([]);
      const categories = ref([]);   // dcat_category posts z pluginu
      const loading    = ref(true);

      const articlesCount = Math.max(0, Math.min(12, parseInt(cfg.articlesCount || 3, 10)));

      /* Skrátenie textu na max 200 znakov, zachovanie celých slov. */
      function truncate(text, max = 200) {
        if (!text) return '';
        if (text.length <= max) return text;
        const cut = text.slice(0, max).replace(/\s+\S*$/, '');
        return cut + '…';
      }

      onMounted(async () => {
        try {
          const [home, cats] = await Promise.all([
            fetch(cfg.datasetApi + 'homepage').then(r => r.ok ? r.json() : { posts: [] }),
            fetch(cfg.datasetApi + 'categories').then(r => r.ok ? r.json() : []),
          ]);
          posts.value = (home.posts || []).slice(0, articlesCount).map(p => ({
            ...p,
            excerpt: truncate(p.excerpt, 200),
          }));
          categories.value = Array.isArray(cats) ? cats : [];
        } finally {
          loading.value = false;
        }
      });

      function categoryUrl(id) {
        return cfg.searchUrl + '?categories[]=' + encodeURIComponent(id);
      }

      const clankyUrl = (cfg.homeUrl || '/') + 'vsetky-clanky/';

      return {
        posts, categories, loading, categoryUrl, clankyUrl,
        heroTitle: cfg.heroTitle || 'Vitajte na stránke otvorených dát obce/mesta',
      };
    },

    template: `
<div class="govuk-main-wrapper">
  <div class="govuk-width-container">

    <div class="govuk-grid-row">
      <div class="govuk-grid-column-two-thirds">
        <h1 class="govuk-heading-xl">{{ heroTitle }}</h1>
      </div>
    </div>

    <p v-if="loading" class="govuk-body">Načítava sa…</p>

    <template v-else>
      <section v-if="categories.length">
        <h2 class="govuk-heading-l">Kategórie</h2>
        <div class="lkod-category-grid">
          <a v-for="cat in categories" :key="cat.id"
             :href="categoryUrl(cat.id)"
             class="govuk-signpost govuk-signpost__card govuk-signpost__card--vertical govuk-signpost__link">
            <div class="govuk-signpost__icon govuk-signpost__icon--vertical">
              <img v-if="cat.icon_url" :src="cat.icon_url" :alt="cat.name"
                   style="max-height:56px;width:auto;display:inline-block;vertical-align:middle">
            </div>
            <div class="govuk-signpost__container">
              <div>
                <h3 class="govuk-signpost__title">{{ cat.name }}</h3>
              </div>
            </div>
          </a>
        </div>
      </section>

      <section v-if="posts.length" style="margin-top:3rem">
        <div style="display:flex;justify-content:space-between;align-items:baseline;gap:1rem;flex-wrap:wrap">
          <h2 class="govuk-heading-l" style="margin-bottom:1rem">Články</h2>
          <a class="govuk-link" :href="clankyUrl">Zobraziť všetky články →</a>
        </div>
        <div class="govuk-grid-row" style="display:flex;flex-wrap:wrap">
          <div v-for="post in posts" :key="post.id" class="govuk-grid-column-one-quarter" style="display:flex;margin-bottom:1.5rem">
            <div class="idsk-card idsk-card--vertical" style="width:100%">
              <div v-if="post.thumbnail" class="idsk-card__image-wrapper">
                <img :src="post.thumbnail" :alt="post.title">
              </div>
              <div class="idsk-card__content">
                <h3 class="idsk-card__heading">
                  <a class="govuk-link" :href="post.permalink" target="_self">{{ post.title }}</a>
                </h3>
                <p class="idsk-card__description">{{ post.excerpt }}</p>
                <div class="idsk-card__date-tags">
                  <time :datetime="post.date">{{ post.date }}</time>
                </div>
              </div>
            </div>
          </div>
        </div>
      </section>
    </template>

  </div>
</div>`
  }).mount('#lkod-app');
})();
