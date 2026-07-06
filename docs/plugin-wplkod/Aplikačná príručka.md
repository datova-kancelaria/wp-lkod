# Aplikačná príručka
Tento dokument slúži ako aplikačná príručka pre rozšírenie Lokálny katalóg otvorených dát pre redakčný systém WordPress. Pri vývoji popisovaného produktu boli použité API rozhrania redakčného systému WordPress, ktoré sú datailne popísané v jeho dokumentácii:
https://developer.wordpress.org/

Plugin umožňuje správu a nastavenie lokálneho katalógu otvorených dát a ich publikovanie vo forme listovateľného katalógu a integračného rozhrania s výstupom vhodným pre harvestáciu Národným katalógom otvorených dát (NKOD; https://data.slovensko.sk/).

Rozhranie lokálneho katalógu je v súlade so špecifikáciu DCAT-AP-SK 3.0.0. Viac informácií je možné získať na adrese:
https://htmlpreview.github.io/?https://github.com/slovak-egov/centralny-model-udajov/blob/develop/tbox/national/dcat-ap-sk/index.html

Spracovávané údaje pluginu sa dajú rozdeliť nasledovne:

| Údaj                                             | Spôsob uloženia a spracovania                         |     |
| ------------------------------------------------ | ----------------------------------------------------- | --- |
| Metadáta lokálneho katalógu                      | Nastavenia red. systému WordPress                     |     |
| Metadáta datasetov, dátových sérií a distribúcií | Článok red. systému WordPress typu dcat_dataset       |     |
| Metadáta dátových služieb                        | Článok red. systému WordPress typu dcat_data_service  |     |
| Údaje o kontaktných bodoch                       | Článok red. systému WordPress typu dcat_contact_point |     |
| Údaje o kategóriách datasetov                    | Článok red. systému WordPress typu dcat_category      |     |
| Súbory s distrubúciami otvorených údajov         | WordPress Media                                       |     |
Metaúdaje objektov podľa DCAT-AP-SK sú spracovávané ako metadáta článkov podľa typu objektu.
## Katalóg

| Názov                        | Dátový typ           | Povinný                                                 | Viacjazyčný | Spôsob editácie               |
| ---------------------------- | -------------------- | ------------------------------------------------------- | ----------- | ----------------------------- |
| Názov                        | String               | áno                                                     | áno         | input text                    |
| Popis                        | String               | áno                                                     | áno         | textarea                      |
| Kontaktný bod - meno a email | String + Email + Typ | nie                                                     | len meno    | výber z definovaných bodov    |
| Domovská stránka             | URL                  | nie                                                     | nie         | automaticky vyplnený údaj     |
| Väzba: dataset               | Viaceré IRI          | nie (katalóg môže byť zadaný pred vytvorením datasetov) | nie         | automaticky vyplnený údaj     |
| Poskytovateľ                 | IRI                  | áno                                                     | nie         | názov + IRI v rámci nastavení |
Katalógy nie je možné vkladať opakovane, ale súčasťou nastavení budú vyznačené polia, ktoré vytvoria jeden záznam katalógu na výstupe RDF.
## Dataset

| Názov                            | Dátový typ           | Povinný                | Viacjazyčný | Spôsob editácie                                                                                   |
| -------------------------------- | -------------------- | ---------------------- | ----------- | ------------------------------------------------------------------------------------------------- |
| Názov                            | String               | áno                    | áno         | input text                                                                                        |
| Popis                            | String               | áno                    | áno         | textarea                                                                                          |
| Dátum publikácie                 | DateTime             | nie                    | nie         | input text s kalendárom a časom                                                                   |
| Dátum modifikácie                | DateTime             | nie                    | nie         | input text s kalendárom a časom                                                                   |
| Téma                             | Viaceré IRI          | áno                    | nie         | Výber z číselníkovej hodnoty (fixný číselník) + možnosť pridať viaceré hodnoty                    |
| Periodicita aktualizácie         | IRI                  | áno                    | nie         | Výber z číselníkovej hodnoty (fixný číselník)                                                     |
| Kľúčové slová                    | Viaceré String       | áno                    | áno         | textarea                                                                                          |
| Typ                              | Viaceré IRI          | nie                    | nie         | Výber z číselníkovej hodnoty (fixný číselník)                                                     |
| Územný prvok z registra adries   | Viaceré IRI          | áno                    | nie         | Výber z číselníkovej hodnoty (fixný číselník NUTS)                                                |
| Súvisiace geografické územie     | Viaceré IRI          | nie                    | nie         | Vloženie IRI hodnoty, ale bez výberu z číselníka                                                  |
| Časové pokrytie                  | Date + Date          | nie                    | nie         | Výber 2 dátumov - input text s kalendárom                                                         |
| Kontaktný bod - meno a email     | String + Email + Typ | nie                    | len meno    | výber z definovaných bodov                                                                        |
| Webová stránka                   | URL                  | nie                    | nie         | input text                                                                                        |
| Odkaz na dokumentáciu            | URL                  | nie                    | nie         | input text                                                                                        |
| Odkaz na špecifikáciu            | URL                  | nie                    | nie         | input text                                                                                        |
| Súvisiaci zdroj                  | URL                  | nie                    | nie         | input text                                                                                        |
| Klasifikácia podľa EuroVoc       | Viaceré IRI          | nie                    | nie         | Vloženie IRI hodnoty, ale bez výberu z číselníka (číselník existuje alebo obsahuje 16000+ hodnôt) |
| Priestorové rozlíšenie v metroch | Decimal              | nie                    | nie         | input text                                                                                        |
| Časové rozlíšenie                | Duration             | nie                    | nie         | hodnota a výber jednotky                                                                          |
| Právny predpis                   | IRI                  | pre HVD                | nie         | Vloženie IRI hodnoty, ale bez výberu z číselníka                                                  |
| Kategória HVD                    | Viaceré IRI          | pre HVD                | nie         | Výber z číselníkovej hodnoty (fixný číselník)                                                     |
| Väzba: Je súčasťou               | IRI                  | nie                    | nie         | Výber z definovaných dátových sérií                                                               |
| Väzba: Distribúcia datasetu      | Viaceré IRI          | ak nie je dátová séria | nie         | automaticky vyplnený údaj                                                                         |
| Je dátovou sériou                | Boolean              | nie                    | nie         | checkbox                                                                                          |
| Poskytovateľ                     | IRI                  | áno                    | nie         | výber z definovaných poskytovateľov                                                               |
## Distribúcia
| Názov                                                                     | Dátový typ                    | Povinný                 | Viacjazyčný                                      | Spôsob editácie                                                 |
| ------------------------------------------------------------------------- | ----------------------------- | ----------------------- | ------------------------------------------------ | --------------------------------------------------------------- |
| Špecifikácia podmienok použitia - Typ autorského diela                    | IRI                           | áno                     | nie                                              | Výber z číselníkovej hodnoty (fixný číselník)                   |
| Špecifikácia podmienok použitia - Typ originálnej databázy                | IRI                           | áno                     | nie                                              | Výber z číselníkovej hodnoty (fixný číselník)                   |
| Špecifikácia podmienok použitia - Typ špeciálnej právnej ochrany databázy | IRI                           | áno                     | nie                                              | Výber z číselníkovej hodnoty (fixný číselník)                   |
| Špecifikácia podmienok použitia - Typ výskytu osobných údajov             | IRI                           | áno                     | nie                                              | Výber z číselníkovej hodnoty (fixný číselník)                   |
| Špecifikácia podmienok použitia - Meno autora diela                       | String                        | nie                     | áno                                              | input text                                                      |
| Špecifikácia podmienok použitia - Meno autora originálnej databázy        | String                        | nie                     | áno                                              | input text                                                      |
| Spôsob prístupu                                                           | Enum                          | áno                     | nie                                              | Výber:<br>- prístupová služba<br>- súbor na stiahnutie          |
| Väzba: prístupová služba                                                  | IRI                           | nie                     | nie                                              | Výber z definovaných dátových služieb.                          |
| Spôsob vloženia súboru na stiahnutie                                      | Enum                          | pre súbor na stiahnutie | nie                                              | Výber:<br>- lokálny súbor (upload)<br>- externý súbor (len URL) |
| Odkaz na stiahnutie súboru                                                | URL                           | pre súbor na stiahnutie | nie                                              | input text / upload                                             |
| Prístupové URL                                                            | URL                           | áno                     | nie                                              | -                                                               |
| Formát súboru na stiahnutie                                               | IRI                           | pre súbor na stiahnutie | nie                                              | Výber z číselníkovej hodnoty (fixný číselník)                   |
| Typ média súboru na stiahnutie                                            | IRI                           | pre súbor na stiahnutie | nie                                              | Výber z číselníkovej hodnoty (fixný číselník)                   |
| Právny predpis                                                            | pre distribúcie HVD datasetov | nie                     | Vloženie IRI hodnoty, ale bez výberu z číselníka | Pre distribúcie HVD datasetov sa automaticky doplní http        |
| Odkaz na strojovo-čitateľnú schému súboru na stiahnutie                   | URL                           | nie                     | nie                                              | input text                                                      |
| Typ média kompresného formátu                                             | IRI                           | nie                     | nie                                              | Výber z číselníkovej hodnoty (fixný číselník)                   |
| Typ média balíčkovacieho formátu                                          | IRI                           | nie                     | nie                                              | Výber z číselníkovej hodnoty (fixný číselník)                   |
| Názov distribúcie datasetu                                                | String                        | nie                     | áno                                              | input text                                                      |
## Dátová služba
| Názov                              | Dátový typ           | Povinný                                | Viacjazyčný | Spôsob editácie                                  |
| ---------------------------------- | -------------------- | -------------------------------------- | ----------- | ------------------------------------------------ |
| Názov                              | String               | áno                                    | áno         | input text                                       |
| Prístupový bod                     | URL                  | áno                                    | nie         | input text                                       |
| Sprístupňuje dáta pre HVD datasety | Boolean              | nie                                    | nie         | checkbox                                         |
| Kategória HVD                      | Viaceré IRI          | pre služby sprístupňujúce HVD datasety | nie         | Výber z číselníkovej hodnoty (fixný číselník)    |
| Právny predpis                     | IRI                  | pre HVD                                | nie         | Vloženie IRI hodnoty, ale bez výberu z číselníka |
| Kontaktný bod - meno a email       | String + Email + Typ | pre služby sprístupňujúce HVD datasety | len meno    | výber z definovaných bodov                       |
| Odkaz na dokumentáciu              | URL                  | pre služby sprístupňujúce HVD datasety | nie         | input text                                       |
| Odkaz na špecifikáciu              | URL                  | pre služby sprístupňujúce HVD datasety | nie         | input text                                       |
| Popis prístupového bodu            | URL                  | nie                                    | nie         | input text                                       |

## Číselníky
Pre niektoré metadáta určuje špecifikácia DCAT-AP-SK výber hodnoty z číselníka, ktoré sa načítavajú z týchto zdrojov. Pre správnu funkciu musí mať redakčný systém možnosť vykonávať HTTP GET požiadavky smerom na tieto zdroje:
* https://data.slovensko.sk/
* https://raw.githubusercontent.com/slovak-egov/centralny-model-udajov/refs/heads/main/cbox/national/nuts2004.ttl
Načítavanie číselníkov je cachované na obdobie 24 hodín.

## Výstup RDF
K spracovaným metaúdajom otvorených dát je možné pristupovať interaktívne na webovej stránke katalógu alebo pomocou integračného rozhrania. Prístupový bod tohto integračného rozhrania je obvykle dostupný na relatívnom URL /wp-json/wp-lkod/v1/catalog.
Kontrolu generovaného obsahu je možné získať v rámci modulo Lokálny katalóg > RDF výstup. Všetky metadáta tohto rozhrania sú dostupné vo formáte RDF 1.1 Turtle.
Rozhranie môže byť dočasne alebo trvale deaktivované v závislosti od nastavení lokálneho katalógu otvorených dát. Viac informácií je možné nájsť v konfiguračnej príručke.

## Technológie a požiadavky
Pre správne používanie je potrebná inštalácia do podporovanej verzie redakčného systému. Odporúčame používať poslednú verziu redakčného systému, ktorá je aktívne podporovaná a sú pre ňu vydávané.
Pre vývoj a testovanie boli použité verzie WordPress 6.9 a 7.0.
Pre správne fungovanie je potrebné zabezpečiť podporu skriptovacieho programovacieho jazyka PHP 8.0 a vyššie.

## Použité komponenty tretích strán
Okrem integrácie v rámci redakčného systému WordPress sú pre správne fungovanie pluginu využívané tieto komponenty tretích strán:
https://github.com/sweetrdf/easyrdf (poskytuje základné prostriedky na prácu s RDF údajmi a grafmi).






