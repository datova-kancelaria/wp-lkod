/* global Vue, LkodCodelists */
(function () {
  const { createApp, ref, computed, onMounted } = Vue;
  const cfg = window.LKOD_CONFIG;
  const CL  = window.LkodCodelists;

  createApp({
    setup() {
      const loading = ref(true);
      const all     = ref([]);
      const search  = ref('');
      const sort    = ref('count');
      const perPage = ref(10);
      const page    = ref(1);

      onMounted(async () => {
        try {
          const [facets, pubs] = await Promise.all([
            fetch(cfg.datasetApi + 'facets').then(r => r.ok ? r.json() : { publisher_counts: [] }),
            CL.publishers(),
          ]);
          const counts = {};
          (facets.publisher_counts || []).forEach(p => { counts[p.iri] = p.count; });

          all.value = (pubs.items || []).map(p => ({
            iri:   p.key,
            name:  p.label || p.key,
            count: counts[p.key] || 0,
          }));

          (facets.publisher_counts || []).forEach(p => {
            if (!all.value.some(x => x.iri === p.iri)) {
              all.value.push({ iri: p.iri, name: CL.resolve(pubs.map, p.iri), count: p.count });
            }
          });
        } finally {
          loading.value = false;
        }
      });

      const filtered = computed(() => {
        let list = all.value;
        if (search.value) {
          const q = search.value.toLowerCase();
          list = list.filter(p => p.name.toLowerCase().includes(q));
        }
        if (sort.value === 'name') {
          list = list.slice().sort((a, b) => a.name.localeCompare(b.name, 'sk'));
        } else {
          list = list.slice().sort((a, b) => b.count - a.count);
        }
        return list;
      });

      const total     = computed(() => filtered.value.length);
      const pages     = computed(() => Math.max(1, Math.ceil(total.value / perPage.value)));
      const pageItems = computed(() => filtered.value.slice((page.value - 1) * perPage.value, page.value * perPage.value));

      function setSort(v)    { sort.value = v; page.value = 1; }
      function setPerPage(v) { perPage.value = parseInt(v, 10); page.value = 1; }
      function setPage(p)    { page.value = p; window.scrollTo(0, 0); }

      function datasetUrl(iri) { return cfg.searchUrl + '?publisher[]=' + encodeURIComponent(iri); }
      /* Slovenské skloňovanie: 1 dataset, 2–4 datasety, 0 a 5+ datasetov. */
      function countLabel(n) {
        if (n === 1)          return '1 dataset';
        if (n >= 2 && n <= 4) return n + ' datasety';
        return n + ' datasetov';
      }

      return {
        loading, all, search, sort, perPage, page,
        filtered, total, pages, pageItems,
        setSort, setPerPage, setPage, datasetUrl, countLabel, cfg,
      };
    },

    template: `
<div class="govuk-main-wrapper">
  <div class="govuk-width-container">

    <nav class="govuk-breadcrumbs" aria-label="Drobčekový navigačný pás">
      <ol class="govuk-breadcrumbs__list">
        <li class="govuk-breadcrumbs__list-item"><a class="govuk-breadcrumbs__link" :href="cfg.homeUrl">Otvorené dáta mesta/obce</a></li>
        <li class="govuk-breadcrumbs__list-item">Poskytovatelia dát</li>
      </ol>
    </nav>

    <h1 class="govuk-heading-l">Poskytovatelia dát</h1>

    <div class="govuk-grid-row">

      <div class="govuk-grid-column-one-third">
        <div class="govuk-form-group">
          <label class="govuk-label" for="pub-search">Vyhľadávanie</label>
          <div class="idsk-searchbar__wrapper" role="search" style="max-width:300px">
            <input class="govuk-input" type="search" id="pub-search"
                   v-model="search" placeholder="Zadajte hľadaný výraz"
                   style="width:100%;min-width:0;height:2.4rem;font-size:1.1875rem;line-height:normal">
            <button type="button" class="govuk-button govuk-button__basic" aria-label="Hľadať">
              <span class="material-icons" aria-hidden="true">search</span>
            </button>
          </div>
        </div>

        <div class="govuk-form-group">
          <label class="govuk-label" for="pub-sort">Zoradiť podľa</label>
          <select class="govuk-select" id="pub-sort" :value="sort" @change="setSort($event.target.value)"
                  style="width:100%;max-width:300px;min-width:0;font-size:1.1875rem">
            <option value="count">Počtu datasetov</option>
            <option value="name">Názvu</option>
          </select>
        </div>
      </div>

      <div class="govuk-grid-column-two-thirds">

        <div style="display:flex;justify-content:space-between;align-items:center;gap:1rem;flex-wrap:wrap;margin-bottom:1rem">
          <p class="govuk-body" style="margin:0;font-weight:700">{{ total }} výsledkov</p>
          <div style="display:flex;align-items:center;gap:0.5rem">
            <label class="govuk-label" for="pub-perpage" style="margin:0">Výsledky na stranu</label>
            <select class="govuk-select" id="pub-perpage"
                    :value="perPage" @change="setPerPage($event.target.value)"
                    style="min-width:0;font-size:1.1875rem;width:auto">
              <option value="10">10</option>
              <option value="25">25</option>
              <option value="50">50</option>
            </select>
          </div>
        </div>

        <p v-if="loading" class="govuk-body">Načítava sa…</p>

        <template v-else>
          <p v-if="!pageItems.length" class="govuk-body">
            Neboli nájdení žiadni poskytovatelia.
          </p>

          <template v-for="(pub, idx) in pageItems" :key="pub.iri">
            <article>
              <h3 class="govuk-heading-s" style="margin:0 0 4px">
                <a class="govuk-link" :href="datasetUrl(pub.iri)">{{ pub.name }}</a>
              </h3>
              <p class="govuk-caption-m" style="font-style:italic;margin:0">{{ countLabel(pub.count) }}</p>
            </article>
            <hr v-if="idx < pageItems.length - 1" class="govuk-section-break govuk-section-break--m govuk-section-break--visible">
          </template>

          <hr v-if="pages > 1" class="govuk-section-break govuk-section-break--m govuk-section-break--visible">
          <nav v-if="pages > 1" aria-label="Stránkovanie"
               style="display:flex;align-items:center;justify-content:space-between;gap:1rem">
            <a v-if="page > 1" href="#" class="govuk-link" @click.prevent="setPage(page-1)">← Predchádzajúca</a>
            <span v-else></span>
            <span class="govuk-body" style="margin:0">Strana {{ page }} z {{ pages }}</span>
            <a v-if="page < pages" href="#" class="govuk-link" @click.prevent="setPage(page+1)">Zobraziť nasledujúce →</a>
            <span v-else></span>
          </nav>
        </template>

      </div>
    </div>
  </div>
</div>`
  }).mount('#lkod-app');
})();
