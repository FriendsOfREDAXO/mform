---
name: mform-dev
description: MForm addon development skill. Use when working on FriendsOfREDAXO mform, especially fields, wrappers, flex repeater, custom link widgets, YForm value types, rex_form integration, docs, changelog, README synchronization, compatibility, or code-quality checks.
---

# MForm Development Skill

Du arbeitest im REDAXO-Addon MForm.

MForm ist ein API-getriebenes Formular-Addon mit vielen produktiv genutzten Einstiegspunkten. Arbeite kompatibel, lokal und dokumentationsbewusst.

## Kernziele

- Öffentliche MForm-APIs stabil halten
- Bestehende Modul-Inputs und gespeicherte Werte nicht brechen
- Repeater-, Widget- und Editor-Kompatibilität sichern
- YForm- und rex_form-Integrationen mitdenken
- Änderungen immer mit Qualitätscheck und Doku-Pflege abschließen

## Wo du was findest

### Einstieg und Struktur

- `package.yml` – Version, Anforderungen, Backend-Seiten
- `boot.php` – Asset-Registrierung und YForm-Integration
- `install.php` / `update.php` – Install-/Update-Verhalten

### Öffentliche Doku

- `README.md` – öffentliche englische Doku
- `README.de.md` – öffentliche deutsche Doku
- `CHANGELOG.md` – Release-Historie
- `docs/00_whats_new.md` – Übersicht neuer Features
- `docs/05_wrapper.md` – Wrapper wie Modal, Collapse, Tabs
- `docs/07_repeater.md` – Flex-Repeater, Ausgabe, Vergleich, Optionen
- `docs/08_mblock_migration.md` – Migration und Kompatibilität mit MBlock
- `docs/10_outside_modules.md` – Nutzung z. B. mit rex_form/YForm

### PHP-Kerncode

- `lib/MForm.php` – Hauptklasse und API-Einstieg
- `lib/MForm/Elements*` bzw. Klassen unter `lib/MForm/` – Felddefinitionen und Bausteine
- `lib/MForm/Parser/` – Parser- und Renderlogik
- `lib/MForm/FlexRepeater/` – Flex-Repeater-Renderer und Builder
- `lib/Widget/` – Widget-nahe Logik
- `lib/yform/` – YForm-Value-Types und Integration
- `fragments/` – HTML-Struktur der Ausgabe
- `ytemplates/` – YForm-Templates für bootstrap/classic

### Assets

- `assets/mform.js` – allgemeines MForm-JS
- `assets/js/flex-repeater.js` – Kernlogik des Flex-Repeaters
- `assets/js/customlink.js` – Custom-Link-Widget
- `assets/js/list-widget.js`, `imglist.js` – Widget-Logik
- `assets/css/` – Styles für Widgets und Repeater

## Kritische Bereiche

### Öffentliche API

Änderungen an diesen APIs nur sehr vorsichtig:

- `addRepeaterElement()`
- `addModalElement()`
- `addCustomLinkField()`
- `addCustomLinkMultipleField()`
- `addConditionalFieldsetArea()`
- `addColorSwatchField()`
- `MFormRepeaterHelper::*`
- `MFormOutputHelper::*`

### Kompatibilität

Besonders sensibel sind:

- Repeater-Speicherformat und Hidden-Value-Synchronisierung
- `__disabled`-Handling für Online/Offline im Repeater
- Nested Repeater
- Modal-IDs innerhalb von Repeatern
- TinyMCE-, MarkdownEditor- und klassische Widget-Kompatibilität
- MBlock-Migration und `useCustomLinkForClassicWidgets(true)`
- YForm-Value-Types und Listenansicht im Manager
- Nutzung außerhalb klassischer Module, z. B. rex_form oder YForm

## Arbeitsweise

1. Finde den kleinsten Codepfad, der das Verhalten kontrolliert.
2. Ändere nur diesen Bereich.
3. Prüfe sofort danach den engsten sinnvollen Check.
4. Aktualisiere anschließend Doku, README und Changelog, wenn Nutzerverhalten betroffen ist.

## Qualitätschecks

### PHP / REDAXO

Nach relevanten Änderungen immer statische Analyse ausführen:

(Pfede können je nach Setup variieren)

```bash
docker exec -it coreweb bash -c "cd /var/www/html/public && php redaxo/bin/console rexstan:analyze redaxo/src/addons/mform/"
```

### Manuelle/verhaltensnahe Checks

Wenn betroffen, mindestens gedanklich oder praktisch prüfen:

- Repeater add/remove/sort/copy/paste
- Nested Repeater
- Modal im Repeater
- Widget-Popup und Wertübernahme
- YForm-/rex_form-Integration
- Ausgabehilfen wie `decode()`, `filterByField()`, `sortByField()`

## Doku-Pflege

Bei geänderten Features, APIs, Kompatibilitätsregeln oder Nutzungshinweisen immer diese Dateien prüfen und aktualisieren:

- `CHANGELOG.md`
- `README.md`
- `README.de.md`
- passende Dateien unter `docs/`

### Zuordnung

- Neue Nutzerfeatures: `docs/00_whats_new.md`
- Wrapper/Modal/Collapse: `docs/05_wrapper.md`
- Repeater: `docs/07_repeater.md`
- MBlock-Kompatibilität/Migration: `docs/08_mblock_migration.md`
- Nutzung außerhalb von Modulen: `docs/10_outside_modules.md`

## README-Regel

Halte die deutsche und englische README inhaltlich synchron, soweit es um Features, Credits, Einordnung und Nutzung geht.

## Entscheidungsregel bei Unsicherheit

Wenn eine Änderung zwischen Modernisierung und Kompatibilität abwägen muss, entscheide standardmäßig für Kompatibilität.