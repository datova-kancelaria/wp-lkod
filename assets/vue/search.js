/* global Vue, LkodCodelists */
(function () {
  const { createApp, ref, reactive, computed, onMounted, nextTick } = Vue;
  const cfg = window.LKOD_CONFIG;
  const CL  = window.LkodCodelists;

  createApp({
    setup() {
      const loading = ref(false);
      const results = ref([]);
      const total   = ref(0);
      const pages   = ref(1);

      const pubMap   = ref({});
      const themeMap = ref({});
      const pubIris   = ref([]);
      const themeIris = ref([]);
      const allCategories  = ref([]);  // [{id, name, icon_url}, ...]
      const categoryCounts = ref({});  // { [id]: count }

      /* Nové filtre — mapy pre labely + zoznamy dostupných hodnôt z facetov */
      const typeMap    = ref({});      // dataset-type codelist
      const periodMap  = ref({});      // frequency codelist
      const licenseMap = ref({});      // license codelist
      const typeIris        = ref([]);
      const periodicityIris = ref([]);
      const formatIris      = ref([]);
      const licenseIris     = ref([]);
      const distTypes       = ref([]); // ['file','service']
      const distTypeLabels  = { file: 'Súbor na stiahnutie', service: 'Dátová služba' };

      const f = reactive({
        s: '', publisher: [], theme: [], categories: [],
        type: [], periodicity: [], format: [], dist_type: [], license: [],
        keyword: '',
        sort: 'relevance', per_page: 10, page: 1,
      });

      function readUrl() {
        const p = new URLSearchParams(window.location.search);
        /* URL param is "q" — WP reserves "s" for its built-in search which 404s
           on static pages. Internal REST API still uses "s" (see fetchResults). */
        f.s         = p.get('q')        || '';
        f.sort      = p.get('sort')     || 'relevance';
        f.per_page  = parseInt(p.get('per_page') || '10', 10);
        f.page      = parseInt(p.get('page')     || '1',  10);
        f.publisher = p.getAll('publisher[]');
        f.theme     = p.getAll('theme[]');
        f.categories= p.getAll('categories[]').map(v => parseInt(v, 10)).filter(n => !isNaN(n));
        f.type        = p.getAll('type[]');
        f.periodicity = p.getAll('periodicity[]');
        f.format      = p.getAll('format[]');
        f.dist_type   = p.getAll('dist_type[]');
        f.license     = p.getAll('license[]');
        f.keyword     = p.get('keyword') || '';
      }
      function pushUrl() {
        const p = new URLSearchParams();
        if (f.s)                    p.set('q', f.s);
        if (f.sort !== 'relevance') p.set('sort', f.sort);
        if (f.per_page !== 10)      p.set('per_page', f.per_page);
        if (f.page !== 1)           p.set('page', f.page);
        f.publisher.forEach(v  => p.append('publisher[]',  v));
        f.theme.forEach(v      => p.append('theme[]',      v));
        f.categories.forEach(v => p.append('categories[]', v));
        f.type.forEach(v        => p.append('type[]',        v));
        f.periodicity.forEach(v => p.append('periodicity[]', v));
        f.format.forEach(v      => p.append('format[]',      v));
        f.dist_type.forEach(v   => p.append('dist_type[]',   v));
        f.license.forEach(v     => p.append('license[]',     v));
        if (f.keyword)            p.set('keyword', f.keyword);
        history.pushState({}, '', window.location.pathname + (p.toString() ? '?' + p.toString() : ''));
      }

      async function fetchResults() {
        loading.value = true;
        const p = new URLSearchParams();
        if (f.s) p.set('s', f.s);
        p.set('sort',     f.sort);
        p.set('per_page', f.per_page);
        p.set('page',     f.page);
        f.publisher.forEach(v  => p.append('publisher[]',  v));
        f.theme.forEach(v      => p.append('theme[]',      v));
        f.categories.forEach(v => p.append('categories[]', v));
        f.type.forEach(v        => p.append('type[]',        v));
        f.periodicity.forEach(v => p.append('periodicity[]', v));
        f.format.forEach(v      => p.append('format[]',      v));
        f.dist_type.forEach(v   => p.append('dist_type[]',   v));
        f.license.forEach(v     => p.append('license[]',     v));
        if (f.keyword)            p.set('keyword', f.keyword);
        try {
          const r = await fetch(cfg.datasetApi + 'datasets?' + p.toString());
          const d = r.ok ? await r.json() : { items: [], total: 0, pages: 1 };
          results.value = d.items || [];
          total.value   = d.total || 0;
          pages.value   = d.pages || 1;
        } catch (e) {
          results.value = []; total.value = 0; pages.value = 1;
        } finally {
          loading.value = false;
        }
      }

      async function fetchFacets() {
        try {
          const [facets, pubs, themes, cats, dtype, freq, lic] = await Promise.all([
            fetch(cfg.datasetApi + 'facets').then(r => r.ok ? r.json() : {}).catch(() => ({})),
            CL.publishers(),
            CL.theme(),
            fetch(cfg.datasetApi + 'categories').then(r => r.ok ? r.json() : []).catch(() => []),
            CL.datasetType(),
            CL.frequency(),
            CL.license(),
          ]);
          pubIris.value   = facets.publisher_iris || [];
          themeIris.value = facets.theme_iris     || [];
          pubMap.value    = pubs.map   || {};
          themeMap.value  = themes.map || {};
          allCategories.value = Array.isArray(cats) ? cats : [];
          const counts = {};
          (facets.category_counts || []).forEach(c => { counts[c.id] = c.count; });
          categoryCounts.value = counts;

          typeMap.value    = dtype.map || {};
          periodMap.value  = freq.map  || {};
          licenseMap.value = lic.map   || {};
          typeIris.value        = facets.type_iris        || [];
          periodicityIris.value = facets.periodicity_iris || [];
          formatIris.value      = facets.format_iris      || [];
          licenseIris.value     = facets.license_iris     || [];
          distTypes.value       = facets.dist_types       || [];
        } catch (e) { /* fail silently */ }
      }

      function categoryName(id) {
        const c = allCategories.value.find(x => x.id === id);
        return c ? c.name : ('#' + id);
      }

      const activeFilters = computed(() => {
        const list = [];
        f.publisher.forEach(iri =>
          list.push({ type: 'publisher', value: iri, label: 'Poskytovateľ: ' + CL.resolve(pubMap.value, iri) }));
        f.theme.forEach(iri =>
          list.push({ type: 'theme', value: iri, label: 'Téma: ' + CL.resolve(themeMap.value, iri) }));
        f.categories.forEach(id =>
          list.push({ type: 'category', value: id, label: 'Kategória: ' + categoryName(id) }));
        f.type.forEach(iri =>
          list.push({ type: 'type', value: iri, label: 'Typ datasetu: ' + CL.resolve(typeMap.value, iri) }));
        f.format.forEach(iri =>
          list.push({ type: 'format', value: iri, label: 'Formát: ' + formatLabel(iri) }));
        f.dist_type.forEach(k =>
          list.push({ type: 'dist_type', value: k, label: 'Typ distribúcie: ' + (distTypeLabels[k] || k) }));
        f.periodicity.forEach(iri =>
          list.push({ type: 'periodicity', value: iri, label: 'Periodicita: ' + CL.resolve(periodMap.value, iri) }));
        f.license.forEach(iri =>
          list.push({ type: 'license', value: iri, label: 'Licencia: ' + CL.resolve(licenseMap.value, iri) }));
        if (f.keyword)
          list.push({ type: 'keyword', value: f.keyword, label: 'Kľúčové slovo: ' + f.keyword });
        return list;
      });

      const filteredPubs = computed(() =>
        pubIris.value.map(iri => ({ iri, name: CL.resolve(pubMap.value, iri) }))
      );
      const filteredThemes = computed(() =>
        themeIris.value.map(iri => ({ iri, label: CL.resolve(themeMap.value, iri) }))
      );
      const filteredCategories = computed(() => allCategories.value);
      const filteredTypes = computed(() =>
        typeIris.value.map(iri => ({ iri, label: CL.resolve(typeMap.value, iri) }))
      );
      const filteredFormats = computed(() =>
        formatIris.value.map(iri => ({ iri, label: formatLabel(iri) }))
      );
      const filteredPeriods = computed(() =>
        periodicityIris.value.map(iri => ({ iri, label: CL.resolve(periodMap.value, iri) }))
      );
      const filteredLicenses = computed(() =>
        licenseIris.value.map(iri => ({ iri, label: CL.resolve(licenseMap.value, iri) }))
      );
      const filteredDistTypes = computed(() =>
        distTypes.value.map(k => ({ key: k, label: distTypeLabels[k] || k }))
      );

      const pagesArr = computed(() => {
        const arr = [];
        for (let i = 1; i <= pages.value; i++) arr.push(i);
        return arr;
      });

      function toggleCheck(arr, value) {
        const i = arr.indexOf(value);
        if (i === -1) arr.push(value); else arr.splice(i, 1);
        f.page = 1; pushUrl(); fetchResults();
      }
      function removeFilter(type, value) {
        if (type === 'publisher')   f.publisher   = f.publisher.filter(v => v !== value);
        if (type === 'theme')       f.theme       = f.theme.filter(v => v !== value);
        if (type === 'category')    f.categories  = f.categories.filter(v => v !== value);
        if (type === 'type')        f.type        = f.type.filter(v => v !== value);
        if (type === 'periodicity') f.periodicity = f.periodicity.filter(v => v !== value);
        if (type === 'format')      f.format      = f.format.filter(v => v !== value);
        if (type === 'dist_type')   f.dist_type   = f.dist_type.filter(v => v !== value);
        if (type === 'license')     f.license     = f.license.filter(v => v !== value);
        if (type === 'keyword')     f.keyword     = '';
        f.page = 1; pushUrl(); fetchResults();
      }
      function setSort(v)    { f.sort = v; f.page = 1; pushUrl(); fetchResults(); }
      function setPerPage(v) { f.per_page = parseInt(v, 10); f.page = 1; pushUrl(); fetchResults(); }
      function setPage(p)    { f.page = p; pushUrl(); fetchResults(); window.scrollTo(0, 0); }
      function doSearch()    { f.page = 1; pushUrl(); fetchResults(); }
      function applyKeyword(){ f.page = 1; pushUrl(); fetchResults(); }

      const pubName     = iri => CL.resolve(pubMap.value, iri);
      const themeLabel  = iri => CL.resolve(themeMap.value, iri);
      const formatLabel = uri => uri ? uri.split('/').pop().toUpperCase() : '';

      /* Slovenské skloňovanie: 1 dataset, 2–4 datasety, 0 a 5+ datasetov. */
      function datasetWord(n) {
        if (n === 1) return 'dataset';
        if (n >= 2 && n <= 4) return 'datasety';
        return 'datasetov';
      }

      onMounted(async () => {
        readUrl();
        await Promise.all([fetchFacets(), fetchResults()]);
        /* Pri načítaní: na desktope (≥641px) filtre otvorené, na mobile zatvorené.
           Jednorazový DOM zásah po vyrenderovaní fasiet — Vue ho neprepisuje,
           takže používateľ ich môže ďalej voľne prepínať. */
        await nextTick();
        if (window.matchMedia('(min-width: 641px)').matches) {
          document.querySelectorAll('#lkod-app .govuk-details').forEach(d => { d.open = true; });
        }
      });

      return {
        loading, results, total, pages, pagesArr, f,
        activeFilters,
        filteredPubs, filteredThemes, filteredCategories,
        filteredTypes, filteredFormats, filteredPeriods, filteredLicenses, filteredDistTypes,
        categoryCounts,
        toggleCheck, removeFilter, setSort, setPerPage, setPage, doSearch, applyKeyword,
        pubName, themeLabel, formatLabel, datasetWord, cfg,
      };
    },

    template: `
<div class="govuk-main-wrapper">
  <div class="govuk-width-container">

    <nav class="govuk-breadcrumbs" aria-label="Drobčekový navigačný pás">
      <ol class="govuk-breadcrumbs__list">
        <li class="govuk-breadcrumbs__list-item"><a class="govuk-breadcrumbs__link" :href="cfg.homeUrl">Otvorené dáta mesta/obce</a></li>
        <li class="govuk-breadcrumbs__list-item">Vyhľadávanie</li>
      </ol>
    </nav>

    <h1 class="govuk-heading-l">Výsledky vyhľadávania</h1>

    <div class="govuk-grid-row">

      <!-- Sidebar -->
      <div class="govuk-grid-column-one-third">

        <div class="govuk-form-group">
          <label class="govuk-label" for="fs-search">Vyhľadávanie</label>
          <div class="idsk-searchbar__wrapper" role="search" style="max-width:300px">
            <input class="govuk-input" type="search" id="fs-search"
                   v-model="f.s" placeholder="Zadajte hľadaný výraz"
                   @keyup.enter="doSearch"
                   style="width:100%;min-width:0;height:2.4rem;font-size:1.1875rem;line-height:normal">
            <button class="govuk-button govuk-button__basic" aria-label="Hľadať" @click="doSearch">
              <span class="material-icons" aria-hidden="true">search</span>
            </button>
          </div>
        </div>

        <div class="govuk-form-group">
          <label class="govuk-label" for="fs-sort">Zoradiť podľa</label>
          <select class="govuk-select" id="fs-sort" :value="f.sort" @change="setSort($event.target.value)"
                  style="width:100%;max-width:300px;min-width:0;font-size:1.1875rem">
            <option value="relevance">Relevantnosti</option>
            <option value="date">Dátumu zmeny</option>
            <option value="title">Názvu</option>
          </select>
        </div>

        <details v-if="filteredPubs.length" class="govuk-details">
          <summary class="govuk-details__summary">
            <span class="govuk-details__summary-text">Poskytovateľ</span>
          </summary>
          <div class="govuk-details__text">
            <div class="govuk-checkboxes govuk-checkboxes--small">
              <div v-for="pub in filteredPubs" :key="pub.iri" class="govuk-checkboxes__item">
                <input class="govuk-checkboxes__input" type="checkbox"
                       :id="'pub-'+pub.iri"
                       :checked="f.publisher.includes(pub.iri)"
                       @change="toggleCheck(f.publisher, pub.iri)">
                <label class="govuk-label govuk-checkboxes__label" :for="'pub-'+pub.iri">{{ pub.name }}</label>
              </div>
            </div>
          </div>
        </details>

        <details v-if="filteredThemes.length" class="govuk-details">
          <summary class="govuk-details__summary">
            <span class="govuk-details__summary-text">Téma</span>
          </summary>
          <div class="govuk-details__text">
            <div class="govuk-checkboxes govuk-checkboxes--small">
              <div v-for="th in filteredThemes" :key="th.iri" class="govuk-checkboxes__item">
                <input class="govuk-checkboxes__input" type="checkbox"
                       :id="'th-'+th.iri"
                       :checked="f.theme.includes(th.iri)"
                       @change="toggleCheck(f.theme, th.iri)">
                <label class="govuk-label govuk-checkboxes__label" :for="'th-'+th.iri">{{ th.label }}</label>
              </div>
            </div>
          </div>
        </details>

        <details v-if="filteredCategories.length" class="govuk-details">
          <summary class="govuk-details__summary">
            <span class="govuk-details__summary-text">Kategória</span>
          </summary>
          <div class="govuk-details__text">
            <div class="govuk-checkboxes govuk-checkboxes--small">
              <div v-for="cat in filteredCategories" :key="cat.id" class="govuk-checkboxes__item">
                <input class="govuk-checkboxes__input" type="checkbox"
                       :id="'cat-'+cat.id"
                       :checked="f.categories.includes(cat.id)"
                       @change="toggleCheck(f.categories, cat.id)">
                <label class="govuk-label govuk-checkboxes__label" :for="'cat-'+cat.id">
                  {{ cat.name }}
                  <span v-if="categoryCounts[cat.id]" class="govuk-caption-m"> ({{ categoryCounts[cat.id] }})</span>
                </label>
              </div>
            </div>
          </div>
        </details>

        <details v-if="filteredTypes.length" class="govuk-details">
          <summary class="govuk-details__summary">
            <span class="govuk-details__summary-text">Typ datasetu</span>
          </summary>
          <div class="govuk-details__text">
            <div class="govuk-checkboxes govuk-checkboxes--small">
              <div v-for="t in filteredTypes" :key="t.iri" class="govuk-checkboxes__item">
                <input class="govuk-checkboxes__input" type="checkbox"
                       :id="'type-'+t.iri"
                       :checked="f.type.includes(t.iri)"
                       @change="toggleCheck(f.type, t.iri)">
                <label class="govuk-label govuk-checkboxes__label" :for="'type-'+t.iri">{{ t.label }}</label>
              </div>
            </div>
          </div>
        </details>

        <details v-if="filteredFormats.length" class="govuk-details">
          <summary class="govuk-details__summary">
            <span class="govuk-details__summary-text">Formát</span>
          </summary>
          <div class="govuk-details__text">
            <div class="govuk-checkboxes govuk-checkboxes--small">
              <div v-for="fmt in filteredFormats" :key="fmt.iri" class="govuk-checkboxes__item">
                <input class="govuk-checkboxes__input" type="checkbox"
                       :id="'fmt-'+fmt.iri"
                       :checked="f.format.includes(fmt.iri)"
                       @change="toggleCheck(f.format, fmt.iri)">
                <label class="govuk-label govuk-checkboxes__label" :for="'fmt-'+fmt.iri">{{ fmt.label }}</label>
              </div>
            </div>
          </div>
        </details>

        <details v-if="filteredDistTypes.length" class="govuk-details">
          <summary class="govuk-details__summary">
            <span class="govuk-details__summary-text">Typ distribúcie</span>
          </summary>
          <div class="govuk-details__text">
            <div class="govuk-checkboxes govuk-checkboxes--small">
              <div v-for="dt in filteredDistTypes" :key="dt.key" class="govuk-checkboxes__item">
                <input class="govuk-checkboxes__input" type="checkbox"
                       :id="'dt-'+dt.key"
                       :checked="f.dist_type.includes(dt.key)"
                       @change="toggleCheck(f.dist_type, dt.key)">
                <label class="govuk-label govuk-checkboxes__label" :for="'dt-'+dt.key">{{ dt.label }}</label>
              </div>
            </div>
          </div>
        </details>

        <details v-if="filteredPeriods.length" class="govuk-details">
          <summary class="govuk-details__summary">
            <span class="govuk-details__summary-text">Periodicita aktualizácie</span>
          </summary>
          <div class="govuk-details__text">
            <div class="govuk-checkboxes govuk-checkboxes--small">
              <div v-for="per in filteredPeriods" :key="per.iri" class="govuk-checkboxes__item">
                <input class="govuk-checkboxes__input" type="checkbox"
                       :id="'per-'+per.iri"
                       :checked="f.periodicity.includes(per.iri)"
                       @change="toggleCheck(f.periodicity, per.iri)">
                <label class="govuk-label govuk-checkboxes__label" :for="'per-'+per.iri">{{ per.label }}</label>
              </div>
            </div>
          </div>
        </details>

        <details v-if="filteredLicenses.length" class="govuk-details">
          <summary class="govuk-details__summary">
            <span class="govuk-details__summary-text">Licencia</span>
          </summary>
          <div class="govuk-details__text">
            <div class="govuk-checkboxes govuk-checkboxes--small">
              <div v-for="lic in filteredLicenses" :key="lic.iri" class="govuk-checkboxes__item">
                <input class="govuk-checkboxes__input" type="checkbox"
                       :id="'lic-'+lic.iri"
                       :checked="f.license.includes(lic.iri)"
                       @change="toggleCheck(f.license, lic.iri)">
                <label class="govuk-label govuk-checkboxes__label" :for="'lic-'+lic.iri">{{ lic.label }}</label>
              </div>
            </div>
          </div>
        </details>

        <details class="govuk-details">
          <summary class="govuk-details__summary">
            <span class="govuk-details__summary-text">Kľúčové slová</span>
          </summary>
          <div class="govuk-details__text">
            <div class="idsk-searchbar__wrapper" role="search" style="max-width:300px">
              <input class="govuk-input" type="search"
                     v-model="f.keyword" placeholder="Zadajte kľúčové slovo"
                     @keyup.enter="applyKeyword"
                     style="width:100%;min-width:0;height:2.4rem;font-size:1.1875rem;line-height:normal">
              <button class="govuk-button govuk-button__basic" aria-label="Filtrovať podľa kľúčového slova" @click="applyKeyword">
                <span class="material-icons" aria-hidden="true">search</span>
              </button>
            </div>
          </div>
        </details>

      </div>

      <!-- Results -->
      <div class="govuk-grid-column-two-thirds">

        <div style="display:flex;justify-content:space-between;align-items:center;gap:1rem;flex-wrap:wrap;margin-bottom:1rem">
          <p class="govuk-body" style="margin:0;font-weight:700">{{ total.toLocaleString('sk') }} {{ datasetWord(total) }}</p>
          <div style="display:flex;align-items:center;gap:0.5rem">
            <label class="govuk-label" for="fs-perpage" style="margin:0">Výsledky na stranu</label>
            <select class="govuk-select" id="fs-perpage"
                    :value="f.per_page" @change="setPerPage($event.target.value)"
                    style="min-width:0;font-size:1.1875rem;width:auto">
              <option value="10">10</option>
              <option value="25">25</option>
              <option value="50">50</option>
            </select>
          </div>
        </div>

        <div v-if="activeFilters.length" class="govuk-button-group">
          <button v-for="af in activeFilters" :key="af.type+af.value"
                  class="govuk-button govuk-button--texted"
                  @click="removeFilter(af.type, af.value)">
            {{ af.label }} ×
          </button>
        </div>

        <p v-if="loading" class="govuk-body">Načítava sa…</p>

        <template v-else>
          <p v-if="!results.length" class="govuk-body">
            Neboli nájdené žiadne datasety zodpovedajúce zadaným kritériám.
          </p>

          <template v-for="(ds, idx) in results" :key="ds.id">
            <article>
              <h3 class="govuk-heading-s" style="margin:0 0 4px">
                <a class="govuk-link" :href="ds.permalink">{{ ds.title }}</a>
              </h3>
              <p v-if="ds.publisher" class="govuk-caption-m" style="font-style:italic;margin:0 0 8px">{{ pubName(ds.publisher) }}</p>
              <div v-if="ds.is_hvd || ds.is_popular || ds.is_minimum || (ds.format_iris && ds.format_iris.length)"
                   style="display:flex;flex-wrap:wrap;gap:6px;margin:0">
                <span v-if="ds.is_hvd"
                      style="background:#7e2c8f;color:#fff;padding:4px 12px;font-family:'Source Sans Pro',arial,sans-serif;font-size:0.875rem;font-weight:700">HVD</span>
                <span v-if="ds.is_popular"
                      style="background:#cc3700;color:#fff;padding:4px 12px;font-family:'Source Sans Pro',arial,sans-serif;font-size:0.875rem;font-weight:700">Najžiadanejší</span>
                <span v-if="ds.is_minimum"
                      style="background:#0b4ea2;color:#fff;padding:4px 12px;font-family:'Source Sans Pro',arial,sans-serif;font-size:0.875rem;font-weight:700">Publikačné minimum</span>
                <span v-for="uri in ds.format_iris" :key="'fmt-'+uri"
                      style="background:#1d70b8;color:#fff;padding:4px 12px;font-family:'Source Sans Pro',arial,sans-serif;font-size:0.875rem;font-weight:700">{{ formatLabel(uri) }}</span>
              </div>
            </article>
            <hr v-if="idx < results.length - 1" class="govuk-section-break govuk-section-break--m govuk-section-break--visible">
          </template>

          <hr v-if="pages > 1" class="govuk-section-break govuk-section-break--m govuk-section-break--visible">
          <nav v-if="pages > 1" aria-label="Stránkovanie"
               style="display:flex;align-items:center;justify-content:space-between;gap:1rem">
            <a v-if="f.page > 1" href="#" class="govuk-link" @click.prevent="setPage(f.page-1)">← Predchádzajúca</a>
            <span v-else></span>
            <span class="govuk-body" style="margin:0">Strana {{ f.page }} z {{ pages }}</span>
            <a v-if="f.page < pages" href="#" class="govuk-link" @click.prevent="setPage(f.page+1)">Zobraziť nasledujúce →</a>
            <span v-else></span>
          </nav>
        </template>

      </div>
    </div>
  </div>
</div>`
  }).mount('#lkod-app');
})();
