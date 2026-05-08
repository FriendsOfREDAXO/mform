# MForm 9.1 – Issue & Ideen-Sammlung

> Ready-to-paste GitHub-Issue-Texte für `FriendsOfREDAXO/mform`.
> Einzelne Issues sind durch `---` getrennt; jeder Block beginnt mit `## TITLE:` (Issue-Titel) und enthält `## LABELS:` plus den Body.

---

## TITLE: [9.1] Roadmap / Tracking Issue

## LABELS: type: tracking, milestone: 9.1

## BODY:

Tracking-Issue für das Release **9.1**. Ziel: Vervollständigung des Visual Form Builders, einige fehlende kleinere API-Felder und Quality-of-Life-Verbesserungen.

### Scope (Must)

- [ ] #BUILDER-API-PARITY (Sammelschirm)
- [ ] #BUILDER-IMPORT
- [ ] #BUILDER-SAVE-MODULE
- [ ] #BUILDER-SEARCH
- [ ] #API-HTML5-INPUTS

### Scope (Should)

- [ ] #BUILDER-JSON-EXPORT
- [ ] #BUILDER-LIVE-PREVIEW
- [ ] #BUILDER-SNIPPETS

### Scope (Nice)

- [ ] #BUILDER-UNDO
- [ ] #API-EXTRA-FIELDS
- [ ] #DOCS-API-EXPLORER

### Postpone (9.2)

- [ ] Conditional Logic – visueller Editor
- [ ] Bootstrap-5-Templates parallel zu BS3
- [ ] Native Dark-Mode-Klassen für gerenderte Module

---

## TITLE: [Builder] Fehlende API-Methoden in Palette ergänzen

## LABELS: type: enhancement, area: formbuilder, milestone: 9.1

## BODY:

Der Visual Form Builder (eingeführt in 9.0.0-beta6) deckt aktuell 16 Feldtypen ab. Im MForm-API gibt es weitere `add*`-Methoden, die noch keinen Palette-Eintrag haben.

**Ziel:** Builder-Palette deckt 100 % der Inhalts-/Container-API ab.

### Tasks

- [ ] `addColorSwatchField()` – Color-Swatch (klein)
- [ ] `addModalElement()` – Bootstrap-Modal als Sub-Form (mittel)
- [ ] `addCollapseElement()` – Collapse-Container (mittel)
- [ ] `addAccordionElement()` – Accordion-Container (mittel)
- [ ] `addColumnElement()` – Bootstrap-Spalte (mittel)
- [ ] `addInlineElement()` – Inline-Group (klein)
- [ ] `addToggleCheckboxField()` – Switch-Checkbox (klein)
- [ ] `addMultiSelectField()` – Multi-Select (klein)
- [ ] `addRadioImgField()`, `addRadioIconField()`, `addRadioColorField()` – visuelle Radios (mittel)
- [ ] `addTextReadOnlyField()`, `addTextAreaReadOnlyField()` – Readonly-Varianten (klein)
- [ ] `addAlertInfo/Warning/Danger/Success` – Alert-Varianten als eigene Palette-Items (klein)
- [ ] `addConditionalFieldsetArea()` – Conditional-Wrapper (groß; Properties-Panel mit Bedingungs-Editor)

Pro Feldtyp:
1. Palette-Eintrag in `pages/formbuilder.php`
2. Properties-Schema in `assets/js/formbuilder.js`
3. Code-Emitter für `MForm`-Method-Chain (mit Default-Optimierung – nur abweichende Optionen ausgeben)
4. CHANGELOG-Eintrag

---

## TITLE: [Builder] Reverse-Engineering: PHP-Modul-Code → Builder-State

## LABELS: type: enhancement, area: formbuilder, milestone: 9.1

## BODY:

Aktuell ist der Builder ein Einweg-Tool: Klick → Code. Für bestehende Module wäre **Round-Trip** wichtig.

### User Story

> Als Modul-Entwickler möchte ich ein bestehendes MForm-Modul (oder ein gespeichertes `input.php`) in den Builder laden, dort visuell anpassen und den Code wieder ausgeben, damit ich Altmodule visuell weiterpflegen kann.

### Akzeptanzkriterien

- [ ] Textarea „Code importieren" oder Dropdown „Aus Modul laden" (siehe Save-Modul-Issue)
- [ ] Token-basierter Mini-Parser für `MForm`-Method-Chains (`$mform->addXyz(...)->add...`)
- [ ] Unterstützt alle Builder-Feldtypen, Wrapper (Repeater/Fieldset/Tab), inkl. verschachtelter Repeater
- [ ] Felder, die der Parser nicht versteht, werden als Warnung gelistet, nicht ignoriert
- [ ] Round-Trip-Test: `Builder-State → Code → Builder-State` ergibt identischen State

### Technische Notizen

- Parser darf den Code **nicht** evaluieren (kein `eval`).
- Reine String/Token-Analyse, ggf. via `nikic/php-parser` (bereits transitive Dep?).

---

## TITLE: [Builder] Direkt als Modul speichern

## LABELS: type: enhancement, area: formbuilder, milestone: 9.1

## BODY:

Statt Copy-Paste in den Modul-Editor soll der Builder direkt in `rex_module.input/output` schreiben können.

### Akzeptanzkriterien

- [ ] Dropdown „Ziel-Modul" mit Liste der bestehenden Module + Option „Neues Modul"
- [ ] Buttons: „In Input speichern", „In Output speichern", „In Beides speichern"
- [ ] Persistenz über `rex_api_function` (`rex_api_mform_save_module`), CSRF-pflichtig, `admin[]`-Permission
- [ ] Vorhandenen Code im Ziel-Slot anzeigen + Diff-Modal vor Überschreiben
- [ ] Erfolgs-/Fehler-Toast in der UI

### Sicherheitsaspekte

- API-Endpoint nutzt `rex_response::cleanOutputBuffers()` + `sendJson` + CSRF-Token
- Nur Admins (oder explizite `mform[edit_modules]`-Perm)
- Niemals Code direkt aus User-Input in `eval` oder `include` – es wird ausschließlich als Text in die DB geschrieben

---

## TITLE: [Builder] JSON-Export/Import des Builder-State

## LABELS: type: enhancement, area: formbuilder, milestone: 9.1

## BODY:

Der Builder-State (Liste der Felder + Properties + Hierarchie) soll als JSON exportiert/importiert werden können.

### Use Cases

- Versionierung der Modul-Definition in Git
- Teilen von Builder-Setups in Foren/Issues
- Backup vor größeren Refactorings

### Akzeptanzkriterien

- [ ] Buttons „State exportieren" / „State importieren" in Builder-Toolbar
- [ ] Export = Download `.mform-builder.json` mit Versionsmarker (`{"mformBuilderVersion": 1, "fields": [...]}`)
- [ ] Import: File-Upload + Validierung gegen Versionsmarker
- [ ] Migration-Hook für künftige State-Format-Änderungen (Version-Bump)

---

## TITLE: [Builder] Live-Preview des aktuellen Builder-State

## LABELS: type: enhancement, area: formbuilder, milestone: 9.1

## BODY:

Aktuell sieht man nur den emittierten PHP-Code, nicht das gerenderte Ergebnis.

### Akzeptanzkriterien

- [ ] Tab oder Splitscreen „Vorschau" neben „Code"
- [ ] AJAX-Render via `rex_api_function`: nimmt Builder-State, baut MForm-Instanz auf, ruft `show()`, gibt HTML zurück
- [ ] Iframe-Sandbox, damit das Backend-CSS die Preview nicht stört
- [ ] Refresh-Button + optionaler Auto-Refresh (debounced)
- [ ] Preview erkennt Theme (Light/Dark) – CSS-Variablen synchron

### Risiken

- Für Felder mit komplexen Backend-Widgets (Mediapool, Custom-Link) muss die Preview sauber graceful degraden.

---

## TITLE: [Builder] Snippet-Bibliothek (eigene Bausteine)

## LABELS: type: enhancement, area: formbuilder

## BODY:

User soll im Canvas eine Auswahl markieren und als „eigenen Baustein" speichern können – wiederverwendbar in der Palette.

### Akzeptanzkriterien

- [ ] Multi-Select im Canvas (Shift-Click)
- [ ] Button „Als Snippet speichern" → Modal mit Name + Beschreibung
- [ ] Persistenz in `rex_config['mform']['snippets']` als JSON-Array
- [ ] Eigener Palette-Bereich „Eigene Bausteine"
- [ ] Snippet exportieren/importieren als JSON (gleiches Format wie #BUILDER-JSON-EXPORT, aber Sub-Tree)
- [ ] Löschen + Umbenennen aus der Palette

---

## TITLE: [Builder] Undo/Redo + Keyboard-Shortcuts

## LABELS: type: enhancement, area: formbuilder

## BODY:

### Akzeptanzkriterien

- [ ] Undo/Redo-Stack auf Builder-State-Ebene (kein DOM-Diff)
- [ ] Stack-Tiefe: 50 Schritte
- [ ] Shortcuts: `Cmd/Ctrl+Z`, `Cmd/Ctrl+Shift+Z`, `Cmd/Ctrl+D` (duplizieren), `Delete` (Item entfernen, mit Bestätigung)
- [ ] Buttons in Toolbar (Undo/Redo) mit aktiv/inaktiv-State

---

## TITLE: [Builder] Palette-Suche / Filter

## LABELS: type: enhancement, area: formbuilder, milestone: 9.1

## BODY:

Mit den neuen Field-Typen aus #BUILDER-API-PARITY und #API-HTML5-INPUTS wird die Palette zu lang.

### Akzeptanzkriterien

- [ ] Suchfeld am Kopf der Palette
- [ ] Live-Filter auf Label und Synonyme (`text`, `textarea`, `eingabe`, `string` …)
- [ ] Ergebnis-Hervorhebung (Match-Highlight optional)
- [ ] `Esc` leert das Suchfeld

---

## TITLE: [API] HTML5-Input-Wrapper: range/date/datetime-local/time/number/email/color

## LABELS: type: enhancement, area: api, milestone: 9.1

## BODY:

Häufig nachgefragte HTML5-Native-Inputs als eigene `add*`-Methoden.

### Tasks

- [ ] `addNumberField($id, $attributes, $defaultValue)` – `<input type="number">` mit `min/max/step`
- [ ] `addRangeField(...)` – `<input type="range">` + Live-Wertanzeige (kleines JS-Snippet)
- [ ] `addDateField(...)` – `<input type="date">`
- [ ] `addDateTimeField(...)` – `<input type="datetime-local">`
- [ ] `addTimeField(...)` – `<input type="time">`
- [ ] `addEmailField(...)` – `<input type="email">` mit Pattern-Validation
- [ ] `addColorField(...)` – nativer Browser-Colorpicker (Pendant zu ColorSwatch)

Alle Felder müssen:
- Im Flex-Repeater funktionieren
- Im Builder per Palette-Eintrag verfügbar sein
- Default-Optimierung (Code-Emitter gibt nur abweichende Optionen aus)
- In den Demo-Seiten gelistet werden

---

## TITLE: [API] Erweiterte Felder: Rating, Tags, JSON, Signature, IconPicker

## LABELS: type: enhancement, area: api

## BODY:

Optionale Felder höherer Komplexität. Diskussion vor Umsetzung.

### Vorschläge

- `addRatingField()` – Sterne 0–5, speichert int
- `addTagsField()` – Tagify (oder eigenes Mini-Widget), speichert CSV oder JSON
- `addJsonField()` – CodeMirror/Monaco für JSON, mit Live-Validierung
- `addIconPickerField()` – FontAwesome-Picker mit Suche (nutzt evtl. FA-Asset von REDAXO-Backend)
- `addSignatureField()` – Signature-Pad (für YForm-Formularmodule)
- `addDateRangeField()` – Zwei verknüpfte Date-Inputs (Start/Ende mit Cross-Validation)
- `addAddressField()` – Strukturiertes Adress-Set (Straße/PLZ/Ort)

### Vorgehen

Pro Feld eigenes Sub-Issue, Diskussion zu Library-Wahl/Asset-Größe.
Vorschlag: Mit `addRatingField` und `addTagsField` starten – beide leicht, hoher Nutzen.

---

## TITLE: [DX] Interaktiver API-Explorer auf der Docs-Seite

## LABELS: type: enhancement, area: docs

## BODY:

Auf der bestehenden Docs-Seite (`pages/docs.php`) soll ein interaktiver Explorer entstehen: für jede `add*`-Methode ein kleines Live-Beispiel mit umschaltbaren Parametern.

### Akzeptanzkriterien

- [ ] Liste aller `add*`-Methoden (per Reflection aus `MFormElements`)
- [ ] Pro Methode: Parameter als Form-Inputs, Live-Preview + Code-Snippet
- [ ] Filter/Suche
- [ ] Copy-to-Clipboard für Snippet

### Synergie

Vieles kann aus dem Builder wiederverwendet werden (Properties-Panel-Renderer, Code-Emitter).

---

## TITLE: [Quality] CI: Rexstan + PHP-CS-Fixer als GitHub Action

## LABELS: type: chore, area: ci

## BODY:

In 9.0.0-beta5 wurde Rexstan auf 0 Fehler gebracht. Damit das so bleibt:

### Akzeptanzkriterien

- [ ] GitHub Action `static-analysis.yml`: Rexstan + PHP-CS-Fixer dry-run auf Push/PR
- [ ] Job läuft im offiziellen REDAXO-Test-Container (`coreweb` oder ähnliches Setup)
- [ ] Badge im README

---

## TITLE: [Quality] Inline-JS aus PHP-Includes auslagern (`assets/js/`)

## LABELS: type: chore, area: code-quality

## BODY:

Audit-Issue: Verbleibende `rex_view::addJsCode()` und `echo "<script>…"` aus `lib/MForm/*` in dedizierte JS-Dateien überführen, mit Nonce für Bootstrapping.

### Akzeptanzkriterien

- [ ] `grep -rn "addJsCode\|<script" lib/` Audit-Liste
- [ ] Pro Treffer: Migration in `assets/js/<modul>.js`, Einbindung via `rex_view::addJsFile`
- [ ] Verbleibende Inline-Snippets nur mit Nonce
- [ ] Cache-Buster über Addon-Version (`?v=<package.yml-version>`)

---

## TITLE: [Architektur] Bootstrap-5-Templates parallel zu Bootstrap-3 (POSTPONE 9.2)

## LABELS: type: enhancement, area: templates, milestone: 9.2

## BODY:

REDAXO 6 wird auf BS5 setzen. Vorbereitung:

- [ ] Neuer Template-Ordner `ytemplates/bootstrap5/`
- [ ] Schalter im Theme/Setting
- [ ] Migrations-Hinweise in CHANGELOG

Mit BS3 als Default beibehalten, BS5 opt-in. Kein Breaking-Change in 9.1.

---

## TITLE: [Architektur] Conditional Logic – visueller Editor (POSTPONE 9.2)

## LABELS: type: enhancement, area: formbuilder, milestone: 9.2

## BODY:

Visueller Editor für „Zeige Feld X **wenn** Feld Y == Wert Z".

### Skizze

- Pro Feld neuer Properties-Tab „Sichtbarkeit"
- Bedingungen als JSON am Wrapper: `data-mform-condition='[{"field":"foo","op":"eq","value":"bar"}]'`
- Kleine Vanilla-JS-Engine im Backend-Edit wertet bei `change` aus
- Bestand-API `addConditionalFieldsetArea()` bleibt bestehen, Builder erzeugt sie automatisch

---
