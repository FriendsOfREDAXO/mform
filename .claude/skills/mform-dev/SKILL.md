---
name: mform
description: MForm addon development skill. Use when working on FriendsOfREDAXO mform – fields, wrappers, flex repeater, custom link widgets, form builder (code generation), MBlock-to-repeater migration (assistant, mform:migrate, mform:lint), YForm value types, rex_form integration, docs, changelog, README synchronization, compatibility, tests or code-quality checks.
---

# MForm Development Skill

Du arbeitest im REDAXO-Addon MForm (Version 10.x, PHP >= 8.4, REDAXO >= 5.17).

MForm ist ein API-getriebenes Formular-Addon mit vielen produktiv genutzten Einstiegspunkten. Arbeite kompatibel, lokal und dokumentationsbewusst.

## Kernziele

- Öffentliche MForm-APIs stabil halten
- Bestehende Modul-Inputs und gespeicherte Werte nicht brechen
- Repeater-, Widget- und Editor-Kompatibilität sichern
- Erzeugten Code (Formbuilder, Migrations-Konverter) lauffähig halten: Eingabe- und Ausgabe-Code müssen zusammenpassen
- YForm- und rex_form-Integrationen mitdenken
- Änderungen immer mit Tests, Qualitätscheck und Doku-Pflege abschließen

## Wo du was findest

### Einstieg und Struktur

- `package.yml` – Version, Anforderungen, Backend-Seiten, Konsolenbefehle (`mform:migrate`, `mform:lint`)
- `boot.php` – Asset-Registrierung und YForm-Integration
- `install.php` / `update.php` – Install-/Update-Verhalten

### Öffentliche Doku

- `README.md` / `README.de.md` – öffentliche Doku (englisch/deutsch, inhaltlich synchron halten)
- `CHANGELOG.md` – Release-Historie; neue Einträge unter `## Unreleased`, beim Release in `## Version x.y.z` umbenennen
- `docs/` – Handbuch, wird im Backend unter „Dokumentation“ angezeigt:
  - `00_whats_new.md` neue Features · `01_basics.md` Grundlagen · `02_redaxo.md` REDAXO-Widgets (Media, Link, Listen)
  - `03_customlink.md` Custom-Link · `04_imagelist.md` Bildliste · `05_wrapper.md` Modal, Collapse, Tabs, Spalten
  - `06_advanced.md` · `07_repeater.md` Flex-Repeater · `08_mblock_migration.md` Migration · `09_templates.md`
  - `10_outside_modules.md` rex_form/YForm · `11_tutorial_modul.md` · `12_checkbox_group.md`
  - `13_api_reference.md` API-Referenz · `14_fragments_output.md` · `15_mform_output.md` · `16_a11y.md`

### PHP-Kerncode

- `lib/MForm.php` – Hauptklasse und API-Einstieg
- `lib/MForm/MFormElements.php` – alle `add…Field()`/`add…Element()`-Methoden und ihre Signaturen (Maßstab für Doku und Codegenerierung)
- `lib/MForm/Parser/MFormParser.php` – Render-Logik der klassischen Felder
- `lib/MForm/FlexRepeater/` – Flex-Repeater-Renderer (`MFormFlexRepeaterRenderer`)
- `lib/MForm/Repeater/` – `MFormRepeaterHelper` (Ausgabe: `decode`, `values`, `value`, `filterByField`, `sortByField`, `groupByField`, `limitItems` …)
- `lib/MForm/Utils/MFormOutputHelper.php` – `decodeRaw`, `rawValue`, `createLinkData`, `normalizeLinkData` …
- `lib/MForm/Migration/` – `MBlockModuleAnalyzer` (Analyse, Key-Map, `applyKeyMaps()`), `MBlockToRepeaterConverter`, Migrator
- `lib/MForm/Console/` – `MigrateCommand` (`mform:migrate`), `LintCommand` (`mform:lint`)
- `lib/MForm/Lint/`, `lib/MForm/A11y/` – Linter und Barrierefreiheits-Checks
- `lib/Api/` – rex_api-Endpunkte (Formbuilder-Vorschau, A11y-Check, Link-Auflösung)
- `lib/Widget/` – Custom-Link, Medien-/Linklisten, Bildliste
- `lib/yform/` – YForm-Value-Types und Integration
- `pages/` – Backend: `formbuilder.php`, `migration.php` (Assistent), `demo.*.php` und `pages/module/` (Beispielmodule), `settings.php`
- `fragments/`, `ytemplates/` – HTML-Ausgabe bzw. YForm-Templates

### Assets

- `assets/mform.js` – allgemeines MForm-JS
- `assets/js/flex-repeater.js` – Kernlogik des Flex-Repeaters
- `assets/js/formbuilder.js` – Formbuilder inkl. Code-Erzeugung (Eingabe- und Ausgabe-Code)
- `assets/js/customlink.js`, `list-widget.js`, `imglist.js`, `linkmap-bridge.js`, `mediaplace-bridge.js` – Widgets
- `assets/css/` – Styles (`mform-tokens.css` mit Design-Tokens, Repeater, Formbuilder, Widgets)

### Tests

- `tests/Unit/` – PHPUnit-Unit-Tests ohne REDAXO (Migration, Repeater-Datenformat, Lint, A11y), Fixtures in `tests/Fixtures/`
- `tests/Redaxo/` – PHPUnit gegen eine laufende REDAXO-Instanz (CI)
- `tests/e2e/smoke.spec.js` – Playwright-Smoke (Repeater, Formbuilder, Migration)

## Feste Regeln (häufige Fehlerquellen)

### Slots und Schlüssel

- REDAXO-Slots je Block: `REX_VALUE` 1–20, `REX_MEDIA`, `REX_MEDIALIST`, `REX_LINK`, `REX_LINKLIST` je nur 1–10. Höhere Nummern werden nicht gespeichert.
- Außerhalb eines Repeaters brauchen Felder eine **numerische Slot-ID** – auch in Spalten, Fieldsets, Tabs, Collapse, Accordion, Inline und Modal (Layout-Wrapper speichern nichts).
- **String-Schlüssel** gibt es nur innerhalb eines Repeaters. Sie müssen über alle Layout-Wrapper desselben Repeaters eindeutig sein; ein verschachtelter Repeater hat eigene Schlüssel.
- Ausgabe eines Repeater-Slots über `MFormRepeaterHelper::decode(<slot>)` (nicht `rex_var::toArray()`); `decode(<int>)` findet den aktuellen Block über den Artikel-Kontext.

### Feldtypen

- `addCheckboxField()` ist ein **Einzelschalter**: Der Parser rendert nur die erste Option, gespeichert wird deren Wert oder nichts. Für Mehrfachauswahl `addCheckboxGroupField()` (speichert kommasepariert). `addToggleCheckboxField()` ist ebenfalls ein Einzelschalter.
- Medien-/Link-Felder (`addMediaField`, `addMedialistField`, `addImagelistField`, `addLinkField`, `addLinklistField`) haben die Signatur `(id, ?array $parameter, mixed $catId, ?array $attributes)`. Filter wie `types`, `category`/`media_category`, `preview` sind **Parameter** – klassisches Formular und Flex-Repeater müssen sie gleich behandeln.
- `addSelectField(id, options, attributes, int $size = 1, defaultValue)`: der Default-Wert ist das 5. Argument.

### Formbuilder (`assets/js/formbuilder.js`)

- Slots und Schlüssel vergibt zentral `computeNames()`; Eingabe-Code, Ausgabe-Code, Sichtbarkeitsbedingungen und die Anzeige im Canvas nutzen dieselbe Vergabe (`codeIdFor()`). Keine eigene Schlüssel-Logik daneben bauen.
- Variablennamen im Ausgabe-Code sind über den ganzen Code eindeutig (`uniqueVarName()`), Schleifenvariablen `$row`/`$row2`/`$row3` sind reserviert.
- Nach Änderungen an der Code-Erzeugung den erzeugten Code wirklich ausführen: Modul anlegen, Block speichern, neu öffnen (Zustände wieder da?), Ausgabe im Artikel-Kontext rendern.

### MBlock-Migration

- Editierte Zielnamen (Assistent `key_map[slot][legacy]`, Konsole `--map`) über `MBlockModuleAnalyzer::applyKeyMaps()` in die Analyse übernehmen, damit Anzeige, Code und Daten dieselben Namen nutzen.
- `mform:migrate --apply`/`--create-module` ohne `MBlock::show()` enden mit Exit-Code 1; `mform:lint` mit Exit-Code 1 bei Fehlern.

## Kritische Bereiche

### Öffentliche API

Änderungen an diesen APIs nur sehr vorsichtig:

- `addRepeaterElement()` / `addFlexRepeaterElement()`
- `addModalElement()`, `addTabElement()`, `addCollapseElement()`, `addColumnElement()`
- `addCustomLinkField()`, `addCustomLinkMultipleField()`
- `addConditionalFieldsetArea()`, `setVisibleIf()`/`addVisibleIf()`
- `addColorSwatchField()`, `addCheckboxGroupField()`, `addTagsField()`
- `MFormRepeaterHelper::*`, `MFormOutputHelper::*`
- `MBlockModuleAnalyzer`/`MBlockToRepeaterConverter` (werden von Konsole und Assistent genutzt)

### Kompatibilität

Besonders sensibel sind:

- Repeater-Speicherformat und Hidden-Value-Synchronisierung (Werte roh lesen, Entities nur als Rückfall umwandeln)
- `__disabled`-Handling für Online/Offline im Repeater
- Nested Repeater, Radio-Gruppen je Item
- Modal-IDs innerhalb von Repeatern
- TinyMCE-, CKE5-, MarkdownEditor- und klassische Widget-Kompatibilität (auch in neu hinzugefügten Repeater-Items)
- MBlock-Migration und `useCustomLinkForClassicWidgets(true)`
- YForm-Value-Types und Listenansicht im Manager
- Nutzung außerhalb klassischer Module, z. B. rex_form oder YForm

## Arbeitsweise

1. Finde den kleinsten Codepfad, der das Verhalten kontrolliert.
2. Ändere nur diesen Bereich.
3. Schreibe oder erweitere einen Test (Unit-Test bei PHP-Logik, E2E-Assertion bei Formbuilder/Repeater-UI).
4. Prüfe sofort danach den engsten sinnvollen Check.
5. Aktualisiere anschließend Doku, README und Changelog, wenn Nutzerverhalten betroffen ist.

## Qualitätschecks

```bash
composer install                              # einmalig: PHPUnit
vendor/bin/phpunit --testsuite unit           # Unit-Tests (laufen ohne REDAXO)
find lib pages -name '*.php' -print0 | xargs -0 -n1 php -l   # Syntax
node --check assets/js/formbuilder.js         # JS-Syntax (analog für geänderte JS-Dateien)
npm install && npx playwright test            # E2E-Smoke gegen eine laufende Instanz (siehe playwright.config.js)
```

Statische Analyse mit rexstan in einer REDAXO-Instanz (Pfade je nach Setup):

```bash
php redaxo/bin/console rexstan:analyze redaxo/src/addons/mform/
```

Wenn rexstan wegen fehlender optionaler Scan-Pfade aus der globalen `redaxo/data/addons/rexstan/user-config.neon` abbricht, die betroffenen Verzeichnisse in der Zielumgebung kurz anlegen und den MForm-Check danach erneut starten. Ist die Zielumgebung unbekannt, vorher den Namen der Umgebung beim Nutzer erfragen. Das ist ein Umgebungsproblem, kein MForm-Fehler.

Die CI (`.github/workflows/static-analysis.yml`) führt PHP-Lint, PHP-CS-Fixer (dry-run), PHPUnit (unit), rexstan, PHPUnit gegen REDAXO und den Playwright-Smoke aus. Code-Stil: `.php-cs-fixer.dist.php`.

### Manuelle/verhaltensnahe Checks

Wenn betroffen, mindestens gedanklich oder praktisch prüfen:

- Repeater add/remove/sort/copy/paste, auch mit Editoren (CKE5, TinyMCE)
- Nested Repeater
- Modal im Repeater
- Widget-Popup und Wertübernahme (Filter `types`/Kategorie)
- Formbuilder: erzeugten Code speichern, neu öffnen, Ausgabe rendern
- Migrationsassistent und `mform:migrate` (Probelauf, Key-Map, Exit-Codes)
- YForm-/rex_form-Integration
- Ausgabehilfen wie `decode()`, `filterByField()`, `sortByField()`

## Doku-Pflege

Bei geänderten Features, APIs, Kompatibilitätsregeln oder Nutzungshinweisen immer diese Dateien prüfen und aktualisieren:

- `CHANGELOG.md`
- `README.md` und `README.de.md`
- passende Dateien unter `docs/` (Zuordnung siehe oben)

Code-Beispiele in der Doku müssen gegen die Signaturen in `lib/MForm/MFormElements.php` stimmen und mit `php -l` sauber sein. Vollständige Modul-Beispiele mit `use FriendsOfRedaxo\MForm;` und `MForm::factory()`.

## README-Regel

Halte die deutsche und englische README inhaltlich synchron, soweit es um Features, Credits, Einordnung und Nutzung geht.

## Entscheidungsregel bei Unsicherheit

Wenn eine Änderung zwischen Modernisierung und Kompatibilität abwägen muss, entscheide standardmäßig für Kompatibilität.
