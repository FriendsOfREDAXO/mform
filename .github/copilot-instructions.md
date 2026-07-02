# GitHub Copilot Instructions für MForm

## Projektcharakter

MForm ist ein produktiv genutztes REDAXO-Addon für Modul-Input-Formulare, Repeater, Wrapper, Widgets, YForm-Value-Types und inzwischen auch Integrationen wie den Flex-Repeater.

Bei Änderungen gilt:

- Öffentliche APIs stabil halten
- Bestehende Modul-Inputs und gespeicherte Werte nicht brechen
- Repeater-, Widget- und Editor-Kompatibilität sehr vorsichtig behandeln
- Dokumentation, README und Changelog immer mitpflegen
- Code-Qualität nach jeder relevanten Änderung prüfen

## Wo du was findest

### Einstieg und Metadaten

- `package.yml` – Version, Anforderungen, Backend-Seiten
- `boot.php` – Asset-Registrierung, YForm-Template-Pfade, Backend-Initialisierung
- `install.php` / `update.php` – Install-/Update-Logik

### Öffentliche Doku

- `README.md` – öffentliche Addon-Übersicht
- `README.de.md` – deutsche Variante der öffentlichen Doku
- `CHANGELOG.md` – Release-Historie
- `docs/00_whats_new.md` – Einstieg in neue Features
- `docs/01_basics.md` bis `docs/12_checkbox_group.md` – thematische Fach-Doku
- `docs/07_repeater.md` – Flex-Repeater, Nutzung, Vergleich, Ausgabe
- `docs/08_mblock_migration.md` – Migrationsleitfaden für MBlock-Nutzer
- `docs/10_outside_modules.md` – Nutzung außerhalb klassischer Modul-Inputs

### PHP-Kerncode

- `lib/MForm.php` – zentrale Einstiegsklasse
- `lib/MForm/` – Elemente, Parser, DTOs, Repeater-Helfer, Flex-Repeater, Utils
- `lib/MForm/Parser/` – Rendering- und Parser-Logik
- `lib/MForm/FlexRepeater/` – Flex-Repeater-Renderer und zugehörige Hilfen
- `lib/Widget/` – Widget-bezogene Integrationen
- `lib/yform/` – YForm-Value-Types und YForm-spezifische Integration
- `fragments/` – HTML-Ausgabe und Wrapper-Strukturen
- `ytemplates/` – YForm-Templates für bootstrap/classic

### Assets

- `assets/mform.js` – allgemeines JS
- `assets/js/flex-repeater.js` – aktueller Flex-Repeater
- `assets/js/customlink.js` – Custom-Link-Widget
- `assets/js/list-widget.js` / `imglist.js` – Listen-/Bild-Widgets
- `assets/css/` – Widget-, Repeater- und Basis-Styles
- `assets/toggle/` – Toggle-Assets

### Demos und Referenzflächen

- Backend-Demo-Seiten aus `package.yml` unter `demo_*`
- Doku und Beispiele in `docs/`

## Kompatibilität ist zentral

### Nicht leichtfertig ändern

- Öffentliche Methoden wie `addTextField()`, `addRepeaterElement()`, `addModalElement()`, `addCustomLinkField()`, `addCustomLinkMultipleField()`, `addConditionalFieldsetArea()`
- Speicherformate bestehender Felder
- Verhalten der Repeater-Helfer `decode()`, `filterByField()`, `sortByField()`, `groupByField()`, `limitItems()`
- YForm-Value-Types und deren Parameterformate
- MBlock-Migrationspfade und MBlock-Kompatibilität
- DOM-Strukturen und JS-Selektoren in Widgets und Repeatern

### Besondere Vorsicht bei

- Flex-Repeater-ID-Generierung und Reindexing
- Nested Repeater / Repeater im Repeater
- Modals im Repeater
- TinyMCE-, MarkdownEditor- und Widget-Kompatibilität
- `custom_link`, `custom_link_multi`, `color_swatch`
- klassische REDAXO-Widgets und `useCustomLinkForClassicWidgets(true)`
- Nutzung außerhalb klassischer Module, z. B. YForm oder rex_form

## Arbeitsweise

- Immer den kleinsten steuernden Codepfad ändern
- Keine unnötigen breiten Refactorings
- Nach der ersten Änderung sofort den engsten sinnvollen Check ausführen
- Bei nutzerrelevanten Änderungen direkt Doku, README und Changelog mitprüfen

## Code-Qualität

### PHP

- REDAXO-Core-Methoden bevorzugen
- Bestehenden Stil im Addon respektieren
- Public APIs nicht ohne Not ändern
- Typen und PHPDoc sauber halten, soweit der bestehende Codepfad das bereits vorsieht

### JavaScript

- Bestehende Initialisierungs- und Repeater-Patterns respektieren
- Keine Änderungen an Widget-/Editor-Initialisierung ohne Folgetests
- Wenn `assets/js/flex-repeater.js` geändert wird, an Nested-Repeater, Copy/Paste, Modal-IDs und Hidden-Value-Synchronisierung denken

## Pflichtchecks

### Statische Analyse

Nach relevanten PHP-Änderungen:

(Pfade können je nach Setup variieren)

```bash
docker exec -it coreweb bash -c "cd /var/www/html/public && php redaxo/bin/console rexstan:analyze redaxo/src/addons/mform/"
```

Wenn nur ein kleiner Bereich angepasst wurde, zuerst enger prüfen, am Ende aber das Addon insgesamt mitdenken.

### Weitere sinnvolle Checks

- Backend-Demo-Seiten öffnen bzw. mitdenken
- Repeater hinzufügen, sortieren, löschen, kopieren, einfügen
- Nested Repeater prüfen
- Widget-Popups und Wertübernahme prüfen
- YForm-/rex_form-Auswirkungen prüfen, wenn betroffen

## Doku-Pflege ist Pflicht

Wenn sich Feature-Verhalten, API, Speicherformat, Editor-Kompatibilität, Repeater-Verhalten, YForm-/rex_form-Integration oder Build-/Installationshinweise ändern, müssen auch diese Dateien geprüft und bei Bedarf angepasst werden:

- `CHANGELOG.md`
- `README.md`
- `README.de.md`
- passende Dateien unter `docs/`

Nicht nur Code ändern und Dokumentation auslassen.

## Changelog-Regeln

- Einträge release-tauglich formulieren
- Keine Entwicklungsnotizen oder Chat-Verläufe eintragen
- Kompatibilitätsfolgen benennen, wenn relevant

## README-Regeln

- README und README.de als öffentliche Einstiegsdoku behandeln
- Inhalte zwischen DE und EN konsistent halten
- Feature-Übersicht, Nutzung, Credits und Einordnung aktuell halten

## Doku-Regeln

- Neue Features in `docs/00_whats_new.md` einordnen, wenn sie für Nutzer relevant sind
- Spezifische Änderungen in der jeweils passenden Fach-Doku ergänzen, z. B. Repeater in `docs/07_repeater.md`, Wrapper in `docs/05_wrapper.md`
- Bei MBlock-Auswirkungen immer auch `docs/08_mblock_migration.md` prüfen

## Entscheidungsregel bei Unsicherheit

- Bestehendes Verhalten und Kompatibilität gehen vor unnötiger Modernisierung
- Vorhandene Patterns im Addon wiederverwenden statt neue Abstraktionen einzuführen
- Bei Änderungen mit Nutzerwirkung immer Code, Doku, README und Changelog gemeinsam betrachten