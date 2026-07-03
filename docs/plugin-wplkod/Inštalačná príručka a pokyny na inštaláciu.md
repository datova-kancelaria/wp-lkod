# Inštalačná príručka a pokyny na inštaláciu

Tento dokument slúži ako inštalačná príručka pre rozšírenie Lokálny katalóg otvorených dát pre redakčný systém WordPress. Popisovaný produkt nie je možné prevádzkovať mimo prostredia redakčného systému WordPress. Z tohto dôvodu, je pre úspešnú inštaláciu potrebné vykonať všeobecné kroky pre inštaláciu viažuce sa na tento redakčný systém.

Viac informácií je možné nájsť tu:
https://wordpress.org/documentation/category/installation/

Pre inštaláciu pluginu je nevyhnutné disponovať administrátorským prístupom pre nainštalovaný redakčný systém a poznať adresu, na ktorej je nainštalovaný.

Pred inštaláciou sa odporúča uistiť, či je prevádzkové prostredie správne a dostatočne nakonfigurované. Nesprávna konfigurácia môže viesť k nemožnosti inštalácie alebo k iným problémom. Overenie konfigurácie je závislé najmä od spôsobu prevádzky a poskytovateľa webhostingových služieb.

Základné požiadavky na inštaláciu sú:
redakčný systém WordPress vo verzii 6.9 alebo 7.0
skriptovací programovací jazyk PHP 8.0 alebo vyšší

Odporúčané nastavenia PHP sú:

|Názov|Hodnota|
|-|-|
|file\_uploads|On|
|memory\_limit|256M alebo viac|
|post\_max\_size|55M alebo viac|
|upload\_max\_filesize|50M alebo viac|
|max\_execution\_time|60 alebo viac|

Pluginu musí byť tiež umožnené pristupovať k externým zdrojom, najmä pre prístup k číselníkovým hodnotám podľa špecifikácie DCAT-AP-SK.

Aktuálnu verziu inštalačného balíčka pluginu je možné získať z na adrese:
https://github.com/datova-kancelaria/wp-lkod/tree/main/inc/required-plugins
Poslednú verziu balíčka vo formáte wp-lkod.zip je potrebné pred inštaláciou stiahnuť.

Po prihlásení do administračného rozhrania redakčného systému WordPress je potrebné prejsť do modulu Pluginy > Pridať plugin.

!\[788](plugin-wplkod//attachments/pridat-pluginy.png)

Pokračujte stlačením tlačidla "Nahrať plugin" a nahrajte súbor wplkod.zip do ponúkaného administračného rozhrania. Následne potvrďte inštaláciu pomocou tlačidla "Inštalovať teraz".

!\[791](plugin-wplkod/attachments/nahrat-plugin.png)

Potvrdená inštalácia plugin prebehne a zobrazí sa hlásenie umožňujúce aktiváciu pluginu pomocou tlačidla "Aktivovať plugin".

!\[661](attachments/instalovat.png)

Skontrolovať výsledok inštalácie je možné v rámci administračného rozhrania redakčného systému v module Pluginy > Nainštalované pluginy. Plugin "Lokálny katalóg otvorených dát" by sa mal nachádzať v zozname a zároveň byť aktivovaný.

!\[797](attachments/pluginy.png)
Pred začatím práce s metadátami otvorených dát je potrebná konfigurácia lokálneho katalógu. Bližšie informácie sa nachádzajú v Konfiguračnej príručke.

## Aktualizácia pluginu

Pred inštaláciou novej verzie pluginu odporúčame vykonať plné zálohovanie celého redakčného systému.
Pre aktualizáciu nainštalovaného pluginu je potrebné vykonať rovnaký postup ako pri prvotnej inštalácii: nahrať nový zip súbor, ale počas inštalácie je nevyhnutné potvrdiť prepísanie nainštalovaného pluginu novou verziou:

!\[788](attachments/aktualizacia.png)

