# Používateľská príručka
Tento dokument slúži ako používateľská príručka pre rozšírenie Lokálny katalóg otvorených dát (WPLKOD) pre redakčný systém WordPress.

Na publikáciu dát v lokálnom katalógu otvorených dát je potrebný prístup k nainštalovanému redakčnému systému WordPress spolu s nainštalovaným a nakonfigurovaným  rozšírením WPLKOD.

Viac informácií o inštalácii a konfigurácii rozšírenia WPLKOD nájdete v inštalačnej a konfiguračne príručke.

Ďalšie znalosti ohľadne publikovania otvorených údajov je možné získať na metodickom portáli:
https://slovak-egov.atlassian.net/wiki/spaces/opendata/overview?homepageId=20054027

Rozšírenie WPLKOD umožňuje správu a publikáciu otvorených dát v lokálnom katalógu a tieto dáta automaticky publikuje na prístupovom bode, cez ktorý je možné metadáta harvestovať do Národného katalógu otvorených dát (https://data.slovensko.sk).

Predmetom správy metadát otvorených údajov sú tieto objekty:

| Objekt              | Popis                                                                                                                                                                                                                                       |
| ------------------- | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| Dataset             | Dataset predstavuje súbor dát, ktorý je publikovaný alebo spravovaný jedným zdrojom (subjektom) a je dostupný alebo stiahnuteľný v jednom alebo viacerých formátoch.                                                                        |
| Dátová séria        | Dátová séria je špeciálny typ datasetu, ktorý zoskupuje viacero súvisiacich datasetov zdieľajúcich spoločné charakteristiky. Dátová séria neobsahuje distribúcie a teda ani konkrétne dáta v niektrom z formátov.                           |
| Distribúcia         | Distribúcia je konkrétna dostupná reprezentácia datasetu v určitom formáte alebo forme. Jeden dataset môže mať viacero distribúcií – napríklad ten istý obsah dostupný ako CSV, JSON, XML alebo prostredníctvom dátovej služby (napr. API). |
| Dátová služba       | Dátová služba predstavuje možnosť prístupu k údajom, obvykle dynamickým a parametrizovateľným spôsobom (API rozhranie REST, SOAP alebo SPARQL).                                                                                             |
| Kontaktný bod       | Kontaktný bod poskytuje kontaktné informácie, prostredníctvom ktorých sa môžu používatelia obrátiť na zodpovednú osobu alebo organizáciu vo veci datasetu, distribúcie či katalógu.                                                         |
| Kategória datasetov | Zoskupenie datasetov podľa spoločnej témy. Kategórie nie sú súčasťou špecifikácie DCAT-AP-SK a slúžia len pre potrebu lokálneho katalógu.                                                                                                   |

Jednotlivé objekty otvorených dát je možné v rámci WPLKOD spravovať ako články redakčného systému a platia pre ne rovnaké pravidlá a spôsoby administrácie (napr. koncepty alebo oneskorená publikácia). Viac informácií je možné získať v dokumentácii redakčného systému:
https://wordpress.org/documentation/category/publishing/
## Správa datasetov a dátových sérií
Datasety a rovnako aj dátové série je možné spravovať v module Lokálny katalóg > Datasety. Každý dataset je samostatný článok, pre ktorý je v rámci editora v redakčnom systéme dostupný formulár s možnosťou vloženia údajov.
Súčasťou správy datasetov je aj správa dátových služieb - editácia prebieha cez rovnaký formulár a distribúcií - súčasť formuláru datasetu. Dataset môže mať viac distribúcií, avšak dátová séria nemôže mať ani jednu. Dataset môže byť súčasťou jednej dátovej série, do ktorej je môže byť zaradený pri jeho editácii.

Formulár datasetu je štandardne v slovenskom jazyku a pre niektoré vlastnosti je možné zadávať aj hodnoty v iných jazykoch podľa nastavení WPLKOD.
## Správa dátových služieb
Dátové služby umožňujú dynamický spôsob distribúcie otvorených dát a pred ich zaradením v rámci niektorého z datasetov je nutné vytvorenie dátovej služby v module Lokálny katalóg > Dátové služby.
## Správa kontaktných bodov
Kontaktný bod tvorí meno alebo názov a e-mailová adresa, kde môže návštevník získať viac informácii o poskytovaných otvorených údajoch.
Správa kontaktných bodov je možná v module Lokálny katalóg > Kontaktné body.
Evidované kontaktné body je možné spárovať s katalógom (v nastaveniach) alebo s jednotlivými datasetmi a dátovými službami vo formuláre týchto objektov.
## Správa používateľov
Pridávať alebo odoberať správcov lokálneho katalógu je možné štandardným spôsobom v module Používatelia.
Správca lokálneho katalógu musí mať možnosť vkladať, editovať alebo publikovať články v rámci redakčného systému WordPress.
Všeobecne pre role redakčného systému platí:

| Rola                                | Možnosť správy                      |
| ----------------------------------- | ----------------------------------- |
| Odberateľ                           | nie je                              |
| Prispievateľ                        | áno, ale bez možnosti publikácie    |
| Autor                               | áno, s publikáciou svojich objektov |
| Editor                              | áno                                 |
| Administrátor / Super administrátor | áno                                 |
## Pridávanie datasetov vo formáte JSON alebo XML
Štandardne WordPress blokuje upload týchto formátov. Ak chcete povoliť upload formátov, použite plugin, kde nastavíte povolené typy súborov napr. File Upload Types plugin.
