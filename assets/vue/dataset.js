/* global Vue, LkodCodelists */
(function () {
  const { createApp, ref, reactive, onMounted, nextTick } = Vue;
  const cfg = window.LKOD_CONFIG;
  const CL  = window.LkodCodelists;

  createApp({
    setup() {
      const ds       = ref(null);
      const loading  = ref(true);
      const error    = ref('');
      const openDist = reactive({});   // { [index]: bool } — accordion open state

      const maps = reactive({
        pub: {}, theme: {}, freq: {}, lic: {}, pd: {}, fmt: {}, mt: {}, spatial: {}, hvd: {},
      });

      /* Vue-side toggle for distribution accordion. IDSK 3 JS may not always
         init reliably with Vue dynamic rendering, so we drive the
         govuk-accordion__section--expanded class ourselves. */
      function toggleDist(i) { openDist[i] = !openDist[i]; }

      onMounted(async () => {
        try {
          const [data, pub, theme, freq, lic, pd, fmt, mt, spatial, hvd] = await Promise.all([
            fetch(cfg.datasetApi + 'dataset/' + cfg.datasetId)
              .then(r => { if (!r.ok) throw new Error('not_found'); return r.json(); }),
            CL.publishers(),
            CL.theme(),
            CL.frequency(),
            CL.license(),
            CL.personalData(),
            CL.format(),
            CL.mediaType(),
            CL.spatial(),
            CL.hvdCategory(),
          ]);
          ds.value      = data;
          maps.pub      = pub.map;
          maps.theme    = theme.map;
          maps.freq     = freq.map;
          maps.lic      = lic.map;
          maps.pd       = pd.map;
          maps.fmt      = fmt.map;
          maps.mt       = mt.map;
          maps.spatial  = spatial.map;
          maps.hvd      = hvd.map;
        } catch (e) {
          error.value = 'Dataset nebol nájdený.';
        } finally {
          loading.value = false;
          /* Initialise IDSK 3 JS components (accordion, etc.) on the freshly
             rendered Vue DOM. DOMContentLoaded already fired before Vue mounted,
             so the global initAll() call missed our nodes. */
          await nextTick();
          if (window.GOVUKFrontend && typeof window.GOVUKFrontend.initAll === 'function') {
            window.GOVUKFrontend.initAll();
          }
        }
      });

      function pubFilterUrl(iri)   { return cfg.searchUrl + '?publisher[]=' + encodeURIComponent(iri); }
      function themeFilterUrl(iri) { return cfg.searchUrl + '?theme[]=' + encodeURIComponent(iri); }

      const rPub     = iri => CL.resolve(maps.pub,     iri);
      const rTheme   = iri => CL.resolve(maps.theme,   iri);
      const rFreq    = iri => CL.resolve(maps.freq,    iri);
      const rLic     = iri => CL.resolve(maps.lic,     iri);
      const rPd      = iri => CL.resolve(maps.pd,      iri);
      const rMt      = iri => CL.resolve(maps.mt,      iri);
      const rSpatial = iri => CL.resolve(maps.spatial, iri);
      const rHvd     = iri => CL.resolve(maps.hvd,     iri);
      const rFmt     = iri => iri ? (maps.fmt[iri] || iri.split('/').pop().toUpperCase()) : '';
      const distLabel = (n) => n === 1 ? 'distribúcia' : (n < 5 ? 'distribúcie' : 'distribúcií');

      /* Slovak short date format: "21. 5. 2026".
         Accepts ISO 8601 (with time/TZ) or Y-m-d. Empty/invalid → vráti pôvodné. */
      function formatDate(value) {
        if (!value) return '';
        const d = new Date(value);
        if (isNaN(d.getTime())) return value;
        return d.toLocaleDateString('sk-SK', { day: 'numeric', month: 'numeric', year: 'numeric' });
      }

      /* Temporal resolution: "5d" → "5 dní", "1mo" → "1 mesiac", atď.
         Units per plugin TimeResolutionProperty: y, mo, d, h, m, s. */
      function formatTemporalResolution(value) {
        if (!value) return '';
        const m = String(value).match(/^(\d+)([a-z]+)$/);
        if (!m) return value;
        const n = parseInt(m[1], 10);
        const unit = m[2];
        const labels = {
          y:  ['rok',     'roky',     'rokov'],
          mo: ['mesiac',  'mesiace',  'mesiacov'],
          d:  ['deň',     'dni',      'dní'],
          h:  ['hodina',  'hodiny',   'hodín'],
          m:  ['minúta',  'minúty',   'minút'],
          s:  ['sekunda', 'sekundy',  'sekúnd'],
        }[unit];
        if (!labels) return value;
        const word = n === 1 ? labels[0] : (n < 5 ? labels[1] : labels[2]);
        return n + ' ' + word;
      }

      /* Spatial resolution: numeric → "100 m". Empty/zero → ''. */
      function formatSpatialResolution(value) {
        if (!value || parseFloat(value) === 0) return '';
        return value + ' m';
      }

      return {
        ds, loading, error, cfg,
        openDist, toggleDist,
        pubFilterUrl, themeFilterUrl,
        rPub, rTheme, rFreq, rLic, rPd, rMt, rSpatial, rHvd, rFmt, distLabel,
        formatDate, formatTemporalResolution, formatSpatialResolution,
      };
    },

    template: `
<div class="govuk-main-wrapper">
  <div class="govuk-width-container">

    <p v-if="loading" class="govuk-body">Načítava sa…</p>
    <div v-else-if="error" class="govuk-notification-banner govuk-notification-banner--warning" role="alert">
      <div class="govuk-notification-banner__header">
        <h2 class="govuk-notification-banner__title">Chyba</h2>
      </div>
      <div class="govuk-notification-banner__content">
        <p class="govuk-notification-banner__heading">{{ error }}</p>
      </div>
    </div>

    <template v-else-if="ds">

      <nav class="govuk-breadcrumbs" aria-label="Drobčekový navigačný pás">
        <ol class="govuk-breadcrumbs__list">
          <li class="govuk-breadcrumbs__list-item"><a class="govuk-breadcrumbs__link" :href="cfg.homeUrl">Otvorené dáta mesta/obce</a></li>
          <li class="govuk-breadcrumbs__list-item"><a class="govuk-breadcrumbs__link" :href="cfg.searchUrl">Vyhľadávanie</a></li>
          <li class="govuk-breadcrumbs__list-item">{{ ds.title }}</li>
        </ol>
      </nav>

      <h1 class="govuk-heading-l">{{ ds.title }}</h1>

      <p v-if="ds.publisher" class="govuk-caption-l" style="margin-bottom:1.5rem">
        <a class="govuk-link govuk-link--text-colour" :href="pubFilterUrl(ds.publisher)">{{ rPub(ds.publisher) }}</a>
      </p>

      <p v-if="ds.description" class="govuk-body-l">{{ ds.description }}</p>

      <div v-if="ds.is_hvd || ds.is_popular || ds.is_minimum || ds.is_serie"
           style="display:flex;flex-wrap:wrap;gap:6px;margin:0 0 1rem">
        <span v-if="ds.is_hvd"
              style="background:#7e2c8f;color:#fff;padding:4px 12px;font-family:'Source Sans Pro',arial,sans-serif;font-size:0.875rem;font-weight:700">HVD</span>
        <span v-if="ds.is_popular"
              style="background:#cc3700;color:#fff;padding:4px 12px;font-family:'Source Sans Pro',arial,sans-serif;font-size:0.875rem;font-weight:700">Najžiadanejší</span>
        <span v-if="ds.is_minimum"
              style="background:#0b4ea2;color:#fff;padding:4px 12px;font-family:'Source Sans Pro',arial,sans-serif;font-size:0.875rem;font-weight:700">Publikačné minimum</span>
        <span v-if="ds.is_serie"
              style="background:#00703c;color:#fff;padding:4px 12px;font-family:'Source Sans Pro',arial,sans-serif;font-size:0.875rem;font-weight:700">Dátová séria</span>
      </div>

      <hr class="govuk-section-break govuk-section-break--m govuk-section-break--visible">

      <h2 class="govuk-heading-m">Metadáta</h2>
      <div style="overflow-x:auto;max-width:100%">
      <table class="govuk-table">
        <tbody class="govuk-table__body">
          <tr v-if="ds.theme_iris && ds.theme_iris.length" class="govuk-table__row">
            <th scope="row" class="govuk-table__header">Téma</th>
            <td class="govuk-table__cell">
              <div v-for="iri in ds.theme_iris" :key="iri">
                <a class="govuk-link" :href="themeFilterUrl(iri)">{{ rTheme(iri) }}</a>
              </div>
            </td>
          </tr>
          <tr v-if="ds.categories && ds.categories.length" class="govuk-table__row">
            <th scope="row" class="govuk-table__header">Kategória</th>
            <td class="govuk-table__cell">
              <div v-for="cat in ds.categories" :key="cat.id">{{ cat.name }}</div>
            </td>
          </tr>
          <tr v-if="ds.hvd_categories && ds.hvd_categories.length" class="govuk-table__row">
            <th scope="row" class="govuk-table__header">HVD kategória</th>
            <td class="govuk-table__cell">
              <div v-for="iri in ds.hvd_categories" :key="iri">{{ rHvd(iri) }}</div>
            </td>
          </tr>
          <tr v-if="ds.documentation" class="govuk-table__row">
            <th scope="row" class="govuk-table__header">Dokumentácia</th>
            <td class="govuk-table__cell">
              <a class="govuk-link" :href="ds.documentation" target="_blank" rel="noopener">Zobraziť dokumentáciu</a>
            </td>
          </tr>
          <tr v-if="ds.website" class="govuk-table__row">
            <th scope="row" class="govuk-table__header">Webová stránka</th>
            <td class="govuk-table__cell" style="word-break:break-word">
              <a class="govuk-link" :href="ds.website" target="_blank" rel="noopener">{{ ds.website }}</a>
            </td>
          </tr>
          <tr v-if="ds.specification" class="govuk-table__row">
            <th scope="row" class="govuk-table__header">Odkaz na špecifikáciu</th>
            <td class="govuk-table__cell" style="word-break:break-word">
              <a class="govuk-link" :href="ds.specification" target="_blank" rel="noopener">{{ ds.specification }}</a>
            </td>
          </tr>
          <tr v-if="ds.periodicity" class="govuk-table__row">
            <th scope="row" class="govuk-table__header">Periodicita aktualizácie</th>
            <td class="govuk-table__cell">{{ rFreq(ds.periodicity) }}</td>
          </tr>
          <tr class="govuk-table__row">
            <th scope="row" class="govuk-table__header">Kontaktný bod</th>
            <td class="govuk-table__cell">
              <template v-if="ds.contact">
                <div v-if="ds.contact.name">{{ ds.contact.name }}</div>
                <div v-if="ds.contact.email"><a class="govuk-link" :href="'mailto:'+ds.contact.email">{{ ds.contact.email }}</a></div>
              </template>
              <span v-else>—</span>
            </td>
          </tr>
          <tr v-if="ds.spatial" class="govuk-table__row">
            <th scope="row" class="govuk-table__header">Územný prvok z registra adries</th>
            <td class="govuk-table__cell">{{ rSpatial(ds.spatial) }}</td>
          </tr>
          <tr v-if="ds.spatial_optional && ds.spatial_optional.length" class="govuk-table__row">
            <th scope="row" class="govuk-table__header">Súvisiace geografické územie</th>
            <td class="govuk-table__cell">
              <div v-for="iri in ds.spatial_optional" :key="iri">{{ rSpatial(iri) }}</div>
            </td>
          </tr>
          <tr v-if="ds.date_from || ds.date_to" class="govuk-table__row">
            <th scope="row" class="govuk-table__header">Časová platnosť</th>
            <td class="govuk-table__cell">
              <div v-if="ds.date_from"><strong>od:</strong> {{ formatDate(ds.date_from) }}</div>
              <div v-if="ds.date_to"><strong>do:</strong> {{ formatDate(ds.date_to) }}</div>
            </td>
          </tr>
          <tr v-if="ds.temporal_resolution" class="govuk-table__row">
            <th scope="row" class="govuk-table__header">Časové rozlíšenie</th>
            <td class="govuk-table__cell">{{ formatTemporalResolution(ds.temporal_resolution) }}</td>
          </tr>
          <tr v-if="formatSpatialResolution(ds.spatial_resolution)" class="govuk-table__row">
            <th scope="row" class="govuk-table__header">Priestorové rozlíšenie</th>
            <td class="govuk-table__cell">{{ formatSpatialResolution(ds.spatial_resolution) }}</td>
          </tr>
          <tr v-if="ds.related && ds.related.length" class="govuk-table__row">
            <th scope="row" class="govuk-table__header">Súvisiaci zdroj</th>
            <td class="govuk-table__cell" style="word-break:break-word">
              <div v-for="url in ds.related" :key="url">
                <a class="govuk-link" :href="url" target="_blank" rel="noopener">{{ url }}</a>
              </div>
            </td>
          </tr>
          <tr v-if="ds.eurovoc && ds.eurovoc.length" class="govuk-table__row">
            <th scope="row" class="govuk-table__header">Klasifikácia podľa EuroVoc</th>
            <td class="govuk-table__cell" style="word-break:break-word">
              <div v-for="uri in ds.eurovoc" :key="uri">
                <a class="govuk-link" :href="uri" target="_blank" rel="noopener">{{ uri }}</a>
              </div>
            </td>
          </tr>
          <tr v-if="ds.legislation && ds.legislation.length" class="govuk-table__row">
            <th scope="row" class="govuk-table__header">Aplikovateľná legislatíva</th>
            <td class="govuk-table__cell" style="word-break:break-word">
              <div v-for="uri in ds.legislation" :key="uri">
                <a class="govuk-link" :href="uri" target="_blank" rel="noopener">{{ uri }}</a>
              </div>
            </td>
          </tr>
          <tr v-if="ds.issued" class="govuk-table__row">
            <th scope="row" class="govuk-table__header">Dátum vydania</th>
            <td class="govuk-table__cell">{{ formatDate(ds.issued) }}</td>
          </tr>
          <tr v-if="ds.modified" class="govuk-table__row">
            <th scope="row" class="govuk-table__header">Dátum zmeny</th>
            <td class="govuk-table__cell">{{ formatDate(ds.modified) }}</td>
          </tr>
          <tr v-if="ds.keywords && ds.keywords.length" class="govuk-table__row">
            <th scope="row" class="govuk-table__header">Kľúčové slová</th>
            <td class="govuk-table__cell">
              <div style="display:flex;flex-wrap:wrap;gap:6px">
                <span v-for="kw in ds.keywords" :key="kw"
                      style="background:#e8e8e8;color:#0b0c0c;padding:4px 12px;font-family:'Source Sans Pro',arial,sans-serif;font-size:0.875rem;font-weight:700">{{ kw }}</span>
              </div>
            </td>
          </tr>

          <!-- Custom metadata (admin-defined) -->
          <tr v-for="cm in (ds.custom_metadata || [])" :key="cm.name" class="govuk-table__row">
            <th scope="row" class="govuk-table__header">{{ cm.label }}</th>
            <td class="govuk-table__cell" style="word-break:break-word">
              <template v-for="(val, idx) in cm.values" :key="idx">
                <div>
                  <a v-if="cm.type === 'url'" class="govuk-link" :href="val" target="_blank" rel="noopener">{{ val }}</a>
                  <a v-else-if="cm.type === 'email'" class="govuk-link" :href="'mailto:' + val">{{ val }}</a>
                  <span v-else-if="cm.type === 'date'">{{ formatDate(val) }}</span>
                  <span v-else>{{ val }}</span>
                </div>
              </template>
            </td>
          </tr>
        </tbody>
      </table>
      </div>

      <div v-if="ds.distributions && ds.distributions.length">
        <hr class="govuk-section-break govuk-section-break--m govuk-section-break--visible">
        <h2 class="govuk-heading-m">
          {{ ds.distributions.length }} {{ distLabel(ds.distributions.length) }}
        </h2>

        <div class="govuk-accordion" data-module="govuk-accordion" id="dist-accordion">
          <div v-for="(dist, i) in ds.distributions" :key="i"
               class="govuk-accordion__section"
               :class="{ 'govuk-accordion__section--expanded': openDist[i] }">

            <div class="govuk-accordion__section-header" @click="toggleDist(i)"
                 role="button" tabindex="0"
                 :aria-expanded="!!openDist[i]"
                 style="cursor:pointer">
              <h3 class="govuk-accordion__section-heading">
                <span class="govuk-accordion__section-button" :id="'dist-heading-'+i">{{ dist.title }}</span>
              </h3>
              <div v-if="dist.format" class="govuk-accordion__section-summary govuk-body" :id="'dist-summary-'+i">
                {{ rFmt(dist.format) }}
              </div>
            </div>

            <div :id="'dist-content-'+i" class="govuk-accordion__section-content">
              <p v-if="dist.url" class="govuk-body">
                <a class="govuk-link" :href="dist.url" target="_blank" rel="noopener">Stiahnuť distribúciu</a>
              </p>
              <div style="overflow-x:auto;max-width:100%">
              <table class="govuk-table">
                <tbody class="govuk-table__body">
                  <tr v-if="dist.method === 'service'" class="govuk-table__row">
                    <th scope="row" class="govuk-table__header">Spôsob prístupu</th>
                    <td class="govuk-table__cell">Prístupová služba</td>
                  </tr>
                  <tr v-if="dist.license_aw" class="govuk-table__row">
                    <th scope="row" class="govuk-table__header">Typ autorského diela</th>
                    <td class="govuk-table__cell">{{ rLic(dist.license_aw) }}</td>
                  </tr>
                  <tr v-if="dist.author_name" class="govuk-table__row">
                    <th scope="row" class="govuk-table__header">Meno autora diela</th>
                    <td class="govuk-table__cell">{{ dist.author_name }}</td>
                  </tr>
                  <tr v-if="dist.license_db" class="govuk-table__row">
                    <th scope="row" class="govuk-table__header">Typ originálnej databázy</th>
                    <td class="govuk-table__cell">{{ rLic(dist.license_db) }}</td>
                  </tr>
                  <tr v-if="dist.original_author_name" class="govuk-table__row">
                    <th scope="row" class="govuk-table__header">Meno autora originálnej databázy</th>
                    <td class="govuk-table__cell">{{ dist.original_author_name }}</td>
                  </tr>
                  <tr v-if="dist.license_sp" class="govuk-table__row">
                    <th scope="row" class="govuk-table__header">Typ špeciálnej právnej ochrany</th>
                    <td class="govuk-table__cell">{{ rLic(dist.license_sp) }}</td>
                  </tr>
                  <tr v-if="dist.personal_data" class="govuk-table__row">
                    <th scope="row" class="govuk-table__header">Typ výskytu osobných údajov</th>
                    <td class="govuk-table__cell">{{ rPd(dist.personal_data) }}</td>
                  </tr>
                  <tr v-if="dist.format" class="govuk-table__row">
                    <th scope="row" class="govuk-table__header">Formát súboru</th>
                    <td class="govuk-table__cell">{{ rFmt(dist.format) }}</td>
                  </tr>
                  <tr v-if="dist.media_type" class="govuk-table__row">
                    <th scope="row" class="govuk-table__header">Typ média</th>
                    <td class="govuk-table__cell">{{ rMt(dist.media_type) }}</td>
                  </tr>
                  <tr v-if="dist.compress" class="govuk-table__row">
                    <th scope="row" class="govuk-table__header">Formát kompresie</th>
                    <td class="govuk-table__cell">{{ rMt(dist.compress) }}</td>
                  </tr>
                  <tr v-if="dist.package" class="govuk-table__row">
                    <th scope="row" class="govuk-table__header">Balíčkovací formát</th>
                    <td class="govuk-table__cell">{{ rMt(dist.package) }}</td>
                  </tr>
                  <tr v-if="dist.conforms_to" class="govuk-table__row">
                    <th scope="row" class="govuk-table__header">Schéma</th>
                    <td class="govuk-table__cell" style="word-break:break-word">
                      <a class="govuk-link" :href="dist.conforms_to" target="_blank" rel="noopener">{{ dist.conforms_to }}</a>
                    </td>
                  </tr>
                  <tr v-if="dist.legislation && dist.legislation.length" class="govuk-table__row">
                    <th scope="row" class="govuk-table__header">Právny predpis</th>
                    <td class="govuk-table__cell" style="word-break:break-word">
                      <div v-for="uri in dist.legislation" :key="uri">
                        <a class="govuk-link" :href="uri" target="_blank" rel="noopener">{{ uri }}</a>
                      </div>
                    </td>
                  </tr>
                </tbody>
              </table>
              </div>

              <div v-if="dist.service">
                <h4 class="govuk-heading-s" style="margin-top:1rem">Prístupová služba</h4>
                <div style="overflow-x:auto;max-width:100%">
                <table class="govuk-table">
                  <tbody class="govuk-table__body">
                    <tr v-if="dist.service.name" class="govuk-table__row">
                      <th scope="row" class="govuk-table__header">Názov</th>
                      <td class="govuk-table__cell">{{ dist.service.name }}</td>
                    </tr>
                    <tr v-if="dist.service.endpoint_url" class="govuk-table__row">
                      <th scope="row" class="govuk-table__header">URL prístupového bodu</th>
                      <td class="govuk-table__cell" style="word-break:break-word">
                        <a class="govuk-link" :href="dist.service.endpoint_url" target="_blank" rel="noopener">{{ dist.service.endpoint_url }}</a>
                      </td>
                    </tr>
                    <tr v-if="dist.service.endpoint_description" class="govuk-table__row">
                      <th scope="row" class="govuk-table__header">Popis prístupového bodu</th>
                      <td class="govuk-table__cell" style="word-break:break-word">
                        <a class="govuk-link" :href="dist.service.endpoint_description" target="_blank" rel="noopener">{{ dist.service.endpoint_description }}</a>
                      </td>
                    </tr>
                    <tr v-if="dist.service.is_hvd" class="govuk-table__row">
                      <th scope="row" class="govuk-table__header">Sprístupňuje HVD dáta</th>
                      <td class="govuk-table__cell">Áno</td>
                    </tr>
                    <tr v-if="dist.service.hvd_categories && dist.service.hvd_categories.length" class="govuk-table__row">
                      <th scope="row" class="govuk-table__header">HVD kategória</th>
                      <td class="govuk-table__cell">
                        <div v-for="iri in dist.service.hvd_categories" :key="iri">{{ rHvd(iri) }}</div>
                      </td>
                    </tr>
                    <tr v-if="dist.service.legislation && dist.service.legislation.length" class="govuk-table__row">
                      <th scope="row" class="govuk-table__header">Právny predpis</th>
                      <td class="govuk-table__cell" style="word-break:break-word">
                        <div v-for="uri in dist.service.legislation" :key="uri">
                          <a class="govuk-link" :href="uri" target="_blank" rel="noopener">{{ uri }}</a>
                        </div>
                      </td>
                    </tr>
                    <tr v-if="dist.service.contact" class="govuk-table__row">
                      <th scope="row" class="govuk-table__header">Kontaktný bod</th>
                      <td class="govuk-table__cell">
                        <div v-if="dist.service.contact.name">{{ dist.service.contact.name }}</div>
                        <div v-if="dist.service.contact.email"><a class="govuk-link" :href="'mailto:'+dist.service.contact.email">{{ dist.service.contact.email }}</a></div>
                      </td>
                    </tr>
                    <tr v-if="dist.service.documentation" class="govuk-table__row">
                      <th scope="row" class="govuk-table__header">Odkaz na dokumentáciu</th>
                      <td class="govuk-table__cell" style="word-break:break-word">
                        <a class="govuk-link" :href="dist.service.documentation" target="_blank" rel="noopener">{{ dist.service.documentation }}</a>
                      </td>
                    </tr>
                    <tr v-if="dist.service.specification" class="govuk-table__row">
                      <th scope="row" class="govuk-table__header">Odkaz na špecifikáciu</th>
                      <td class="govuk-table__cell" style="word-break:break-word">
                        <a class="govuk-link" :href="dist.service.specification" target="_blank" rel="noopener">{{ dist.service.specification }}</a>
                      </td>
                    </tr>
                    <tr v-for="cm in (dist.service.custom_metadata || [])" :key="cm.name" class="govuk-table__row">
                      <th scope="row" class="govuk-table__header">{{ cm.label }}</th>
                      <td class="govuk-table__cell" style="word-break:break-word">
                        <template v-for="(val, idx) in cm.values" :key="idx">
                          <div>
                            <a v-if="cm.type === 'url'" class="govuk-link" :href="val" target="_blank" rel="noopener">{{ val }}</a>
                            <a v-else-if="cm.type === 'email'" class="govuk-link" :href="'mailto:' + val">{{ val }}</a>
                            <span v-else-if="cm.type === 'date'">{{ formatDate(val) }}</span>
                            <span v-else>{{ val }}</span>
                          </div>
                        </template>
                      </td>
                    </tr>
                  </tbody>
                </table>
                </div>
              </div>

            </div>
          </div>
        </div>
      </div>

      <div v-if="ds.serie && ds.serie.length">
        <hr class="govuk-section-break govuk-section-break--m govuk-section-break--visible">
        <h2 class="govuk-heading-m">Ďalšie datasety z tejto série</h2>
        <ul class="govuk-list govuk-list--bullet">
          <li v-for="s in ds.serie" :key="s.id">
            <a class="govuk-link" :href="s.permalink">{{ s.title }}</a>
          </li>
        </ul>
      </div>

    </template>
  </div>
</div>`
  }).mount('#lkod-app');
})();
