# Lokálny katalóg otvorených dát pre WordPress (WP-LKOD)

**Lokálny katalóg otvorených dát pre WordPress** je riešenie publikovania otvorených dát určené pre samosprávy, organizácie štátnej a verejnej správy postavené na oficiálnom
**dizajn manuáli IDSK 3** ([@id-sk/frontend](https://www.npmjs.com/package/@id-sk/frontend) `3.0.0-beta.0`).
Frontend tvoria odľahčené **Vue 3** aplikácie napojené na **REST API**; metadáta sú v štandarde
**DCAT‑AP‑SK**. Súčasťou inštalačného balíka je téma a plugin pre WordPress.

- **Verzia:** 1.0.0
- **Licencia:** GPL‑2.0‑or‑later
- **Text domain:** `lkod-idsk3`
- **Vydavateľ:** `Ministerstvo investícií, regionálneho rozvoja a informtizácie SR`

---

## Obsah

1. [Požiadavky](#požiadavky)
2. [Inštalácia](#inštalácia)
3. [Prvé spustenie](#prvé-spustenie)
4. [Nastavenia témy](#nastavenia-témy)
5. [Správa obsahu](#správa-obsahu)
6. [Stránky a šablóny](#stránky-a-šablóny)
7. [Pätička a povinná publicita](#pätička-a-povinná-publicita)
8. [REST API](#rest-api)
9. [Pre vývojárov](#pre-vývojárov)
10. [Prístupnosť a dizajn manuál](#prístupnosť-a-dizajn-manuál)

---

## Požiadavky

| Komponent | Minimum |
|---|---|
| WordPress | 6.0+ (testované do 6.7) |
| PHP | 8.0+ |
| Plugin **WP‑LKOD** | **povinný** — registruje typy obsahu (datasety, dátové služby…) a číselníky |

> ⚠️ **Téma bez pluginu WP‑LKOD nefunguje.** Plugin definuje vlastné typy obsahu
> (`dcat_dataset`, `dcat_data_service`, `dcat_contact_point`, `dcat_category`) a REST
> číselníky (`/wp-json/wp-lkod/v1/…`), z ktorých téma číta. IDSK 3 (CSS/JS) je už
> súčasťou témy v `assets/idsk/` — netreba ho inštalovať zvlášť.

---

## Inštalácia

1. V administrácii: **Vzhľad → Témy → Pridať novú → Nahrať tému**.
2. Nahrajte súbor `lkod-idsk3-v1.0.0.zip` a kliknite **Inštalovať**.
3. Uistite sa, že je aktívny plugin **WP‑LKOD**.
4. Kliknite **Aktivovať**.

Alternatívne rozbaľte priečinok `lkod-idsk3/` do `wp-content/themes/`.

---

## Prvé spustenie

Pri aktivácii téma automaticky:

- **Vytvorí stránky** potrebné pre vlastné šablóny (ak ešte neexistujú):
  - `vyhladavanie` — Vyhľadávanie
  - `poskytovatelia` — Poskytovatelia dát
  - `vsetky-clanky` — Všetky články
- Zaregistruje dve **pozície menu**: *Hlavná navigácia* a *Pätičková navigácia*.
- Obnoví permalinky.

Odporúčané kroky po aktivácii:

1. **Nastavenia → Trvalé odkazy** — zvoľte „Názov príspevku“ (pekné URL).
2. **Vzhľad → Menu** — priraďte menu k pozícii *Hlavná navigácia* (ak nepriradíte, použije sa
   predvolené: Domov, Vyhľadávanie, Poskytovatelia dát).
3. **Nastavenia témy** — vyplňte logo, názov, pätičku a odkazy (viď nižšie).

---

## Nastavenia témy

Administrácia → **Nastavenia témy** (vlastná stránka). Hodnoty sa ukladajú cez natívne
WordPress Settings API.

### Úvodná stránka
| Pole | Popis | Predvolené |
|---|---|---|
| Hlavný nadpis | Nadpis hero sekcie | „Vitajte na stránke otvorených dát…“ |
| Počet článkov | Koľko článkov na úvode (0–12; **0 = sekcia skrytá**) | 3 |

### Branding
| Pole | Popis |
|---|---|
| Logo v hlavičke | Obrázok loga (mediálna knižnica) |
| Názov v hlavičke | Prepíše názov stránky; prázdne = WP názov |

### Päta
| Pole | Popis |
|---|---|
| Logo v päte | Obrázok loga do pätičky |
| Copyright text | Riadok s autorskými právami; prázdne = `© {rok} {názov}` |

### Sociálne siete
URL na Facebook, Instagram, LinkedIn. Prázdne pole = odkaz sa nezobrazí.

### Právne odkazy a prevádzkovateľ
Zásady ochrany súkromia, Podmienky používania, Vyhlásenie o prístupnosti, Kontakt na
prevádzkovateľa, RSS, Mapa stránky. Každý odkaz sa zobrazí v pätičke len ak je vyplnený
(RSS a Mapa stránky majú rozumný default).

---

## Správa obsahu

Obsah pridávate cez typy, ktoré registruje plugin **WP‑LKOD**:

| Typ | Účel | Kde sa zobrazí |
|---|---|---|
| **Dataset** (`dcat_dataset`) | Hlavná jednotka katalógu (DCAT‑AP‑SK metadáta + distribúcie) | Vyhľadávanie + detail datasetu |
| **Dátová služba** (`dcat_data_service`) | Prístupová služba (API endpoint) | V detaile datasetu pri distribúcii typu „služba“ |
| **Kontaktný bod** (`dcat_contact_point`) | Meno + e‑mail kontaktu | Detail datasetu / služby |
| **Kategória** (`dcat_category`) | Tematická kategória s ikonou | Dlaždice na úvode + filter |
| **Článok** (`post`) | Aktuality | Sekcia „Články“ + stránka Všetky články |

**Detail datasetu** zobrazuje metadáta, distribúcie (rozbaľovací accordion) a — pri
distribúcii s prístupovou službou — kompletný detail dátovej služby (URL prístupového bodu,
HVD kategória, právny predpis, kontaktný bod, dokumentácia, špecifikácia).

Slug datasetu aj článku sa po uložení automaticky odvodí od názvu (pekné URL).

---

## Stránky a šablóny

| Šablóna | Použitie |
|---|---|
| `front-page.php` | Úvodná stránka (Vue: hero, kategórie, články) |
| `page-vyhladavanie.php` | `/vyhladavanie/` — vyhľadávanie a filtre datasetov (Vue) |
| `page-poskytovatelia.php` | `/poskytovatelia/` — zoznam poskytovateľov (Vue) |
| `page-vsetky-clanky.php` | `/vsetky-clanky/` — všetky články (PHP grid + stránkovanie) |
| `single-dcat_dataset.php` | Detail datasetu (Vue) |
| `single.php`, `page.php`, `archive.php`, `search.php`, `404.php` | Delegujú na `index.php` (IDSK 3 layout) |
| `index.php` | Univerzálny fallback |

---

## Pätička a povinná publicita

Pätička obsahuje **lištu povinnej publicity Plánu obnovy** — kombinované logo
**EÚ (NextGenerationEU) · Plán obnovy · MIRRI** podľa dizajn manuálu planobnovy.sk.

- Obrázok: `assets/images/funding/funding-strip.png` (farebná verzia, transparentné pozadie)
- Zarovnaná doprava, responzívna (max. šírka 760 px)

Ak vaša samospráva spadá pod iný rezort, vymeňte tento obrázok za príslušnú kombináciu
log z [planobnovy.sk → Dokumenty](https://www.planobnovy.sk/realizacia/dokumenty/).

---

## REST API

Téma poskytuje vlastné read‑only endpointy (vracajú **iba surové IRI**, popisky sa
resolvujú na strane klienta z číselníkov pluginu):

| Endpoint | Popis |
|---|---|
| `GET /wp-json/lkod-theme/v1/datasets` | Vyhľadávanie + filtre + stránkovanie |
| `GET /wp-json/lkod-theme/v1/dataset/{id}` | Úplný detail datasetu |
| `GET /wp-json/lkod-theme/v1/facets` | Hodnoty pre filtre (publisher, téma, formát…) |
| `GET /wp-json/lkod-theme/v1/homepage` | Články + témy pre úvod |
| `GET /wp-json/lkod-theme/v1/categories` | Kategórie (`dcat_category`) |
| `GET /wp-json/tema/v1/nastavenia` | Hodnoty z „Nastavenia témy“ |

Číselníky (popisky IRI) poskytuje plugin pod `/wp-json/wp-lkod/v1/…`.

---

## Pre vývojárov

### Architektúra

- **Frontend = Vue 3 + REST.** Vue aplikácie sú v `assets/vue/`:
  `home.js`, `search.js`, `publishers.js`, `dataset.js`, `codelists.js` (zdieľaný resolver
  číselníkov). Vue sa načítava cez CDN (`vue.global.prod.js`).
- **PHP vrstva** (`functions.php`) registruje šablóny, REST endpointy témy a vkladá
  `window.LKOD_CONFIG` (URL, ID, nastavenia) do stránky.
- **IDSK 3** (CSS/JS) je predkompilovaný v `assets/idsk/`. JS sa nahráva ako UMD bundle
  (`frontend.bundle.js`) a inicializuje cez `GOVUKFrontend.initAll()`.

### Štruktúra

```
lkod-idsk3/
├─ assets/
│  ├─ idsk/      # predkompilovaný IDSK 3 (CSS/JS/fonty) — needitovať
│  ├─ vue/       # Vue 3 aplikácie jednotlivých stránok
│  └─ images/    # logá (vrátane lišty povinnej publicity)
├─ inc/
│  ├─ theme-settings.php   # admin stránka „Nastavenia témy“
│  └─ rest-settings.php    # REST endpoint nastavení
├─ languages/
├─ *.php          # šablóny (viď tabuľka vyššie)
├─ style.css      # hlavička témy
└─ README.md
```

### Úpravy a cache

Pri zmene súborov v `assets/vue/*.js` alebo `*.css` **zvýšte verziu** v `wp_enqueue_*`
(parameter `ver`) — inak prehliadač použije starú verziu z cache. Príklad:
`single-dcat_dataset.php` nahráva `dataset.js` s verziou `2.1.0`.

### Vlastné/IDSK štýlové override

Theme‑wide CSS override (napr. šírka kontajnera, mriežka kategórií, riadkovanie nadpisov
kariet, focus loga) sú v `<style>` bloku v `header.php`; ten sa načítava **po** IDSK CSS,
takže má prednosť. Drobné štýly (napr. lišta publicity) sú cez `wp_add_inline_style` vo
`functions.php`.

### Závislosť na plugine

Téma číta meta polia a číselníky pluginu WP‑LKOD. Mapovanie meta → frontend je v helperoch
vo `functions.php` (`lkod_distributions`, `lkod_data_service`, `lkod_dataset_row`…). Pri
zmene dátového modelu pluginu skontrolujte tieto helpery.

---

## Prístupnosť a dizajn manuál

Téma dodržiava **Jednotný dizajn manuál slovenského webu (IDSK 3)** — komponenty
`govuk-header`, `govuk-footer`, `idsk-card`, `govuk-accordion`, `govuk-table` a ich
prístupnostné vzory (focus stavy, ARIA, sémantické nadpisy, drobčeková navigácia).
Cieľom je súlad s WCAG 2.1 AA, ako vyžaduje legislatíva pre weby verejnej správy SR.

---

*Téma je určená na ďalšiu distribúciu samosprávam. Otázky a úpravy konzultujte s autorom.*
