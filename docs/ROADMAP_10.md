# MForm 10 – Plan

Stand: 11.09.2026, Basis 9.5.0. Dieser Plan ist die Arbeitsgrundlage für MForm 10 auf `main`.

## 1. Rahmen

| Thema | Entscheidung |
|---|---|
| Branches | `main` = MForm 10 (seit 11.09.2026, vorher Branch `10.x`). `9.x` = Wartung für 9.5.x (nur Bugfixes, keine neuen Optionen). |
| Version | `package.yml` auf `10.0.0-dev`, Releases als `10.0.0-beta.1` … `10.0.0`. Tags ohne „v“. |
| PHP | Mindestversion `>=8.4` (bisher 8.0), damit `\Dom\HTMLDocument` ohne Fallback-Pfad eingesetzt wird. REDAXO `^5.17` bleibt. Wer PHP < 8.4 hat, bleibt auf 9.x. |
| Kompatibilität | Keine Breaking Changes an der MForm-API: alle `add*()`-Signaturen, gespeicherten Datenformate, Templates und Fragmente bleiben. Einzige Ausnahme ist MBlock (siehe 2). |
| Qualität | Jeder PR läuft gegen rexstan, php-cs-fixer und die neue Test-Suite (siehe 5.7). |

## 2. MBlock: Support fällt weg

Was „fällt weg“ konkret heißt:

- MBlock wird nicht mehr getestet, nicht mehr dokumentiert und in Issues nicht mehr supportet. Es gibt keinen `conflicts`-Eintrag, denn für die Migration müssen beide Addons parallel laufen.
- `MBlock::show($id, $mform->show())` mit String funktioniert technisch weiter, weil MForm nur HTML liefert. Wer das Objekt übergibt, profitiert von den 9.5-Fixes, bekommt aber keine Garantie mehr.
- `MForm::useCustomLinkForClassicWidgets()` bleibt als MForm-API erhalten (kein BC-Bruch), der Docblock und die Doku verlieren den MBlock-Bezug.

Aufräumarbeiten für 10:

- `docs/08_mblock_migration.md` wird zu „Migration von MBlock“: Kapitel 1 (Weiterverwendung, Compat-Modus) entfällt, Kapitel 2 und 3 werden um den neuen Assistenten erweitert.
- Demo-Modul `pages/module/expert/mblock_medialist_linklist/` entfällt.
- CSS-Klasse `mblock-info-tooltip` im Label-Renderer wird `mform-info-tooltip`; die alte Klasse bleibt als zweite Klasse am Element, damit fremdes CSS nicht bricht.
- Kommentare in `MFormItemManipulator`, `MFormParser`, `MForm.php`, die MBlock als Begründung nennen, werden auf „mehrfaches `show()`“ umformuliert. Das Verhalten selbst bleibt, es ist auch ohne MBlock richtig.

## 3. Migration MBlock → Repeater: Code und Daten

### Was 9.5 schon kann

- `MBlockToRepeaterConverter`: Eingabe-Code (`MBlock::show()` → `addFlexRepeaterElement()`, Präfixe `$id.0.` entfernen, numerische Media-/Link-Keys auf sprechende Keys), Ausgabe-Code (`rex_var::toArray()` → `MFormRepeaterHelper::decode()`), Daten eines Slots (GBS-Wrapper auflösen, Legacy-Keys `REX_MEDIA_1`, `REX_LINK_1`, `1` mappen).
- `MBlockToRepeaterMigrator`: Dry-Run und selektives Anwenden pro Slice, Liste der Module mit Slices.
- Seite „Migration“: Konverter-Formular, konvertiertes Modul anlegen, Slices auf das neue Modul umhängen mit Rückgängig per Token.

### Lücken, die 10 schließt

| # | Lücke | Ziel in 10 |
|---|---|---|
| M1 | Legacy-Key-Map muss von Hand eingetragen werden | Der Konverter leitet sie aus dem Eingabe-Code ab: `addMediaField(1)` → `media`, `addMediaField(2)` → `media_2`, `addLinkField(3)` → `link_3`, Medialist/Linklist analog. Vorschau zeigt die Map, sie bleibt editierbar. |
| M2 | Nur ein Slot pro Modul | Alle Slots (`REX_VALUE[1..20]`) eines Moduls werden erkannt und in einem Lauf migriert. |
| M3 | Verschachteltes MBlock (Gridblock-Wrapper, MBlock in MBlock) wird nur gemeldet | Verschachtelung wird zu verschachtelten Repeatern (Level 2) konvertiert, Code und Daten. |
| M4 | Online/Offline (`mblock_offline`, `checkbox_block_hold`) geht verloren | Mapping auf das Repeater-Metafeld `__disabled`. |
| M5 | Medialist/Linklist-Werte (kommasepariert) werden als String übernommen | Werden in das Format der MForm-Custom-Widgets überführt, inklusive Prüfung, ob die Dateien existieren. |
| M6 | Mehrsprachige Werte (clang-Arrays) | Werden erkannt und je Sprache migriert. |
| M7 | MBlock in YForm-Tabellen (Wert-Typ `mblock`) | Konverter für YForm-Spalten: Zieltyp ist das neue YForm-Value `repeater_light` (siehe 5.4) oder ein JSON-Feld. |
| M8 | Kein Backup der alten Werte | Vor dem Anwenden werden Slice-ID, Slot und alter Wert in eine Backup-Tabelle geschrieben; Rollback pro Lauf-Token wie beim Umhängen. |
| M9 | Nur Backend, viele Klicks | Console-Command `mform:migrate --module=ID [--dry-run]` für große Installationen, mit Report. |
| M10 | Man weiß nicht, wo MBlock überhaupt steckt | „Modul-Inventar“: listet alle Module mit `MBlock::show()`, Anzahl Slices, verwendete Feldtypen und Migrationsrisiko (grün/gelb/rot). |

Zielbild ist ein Assistent in fünf Schritten: Inventar → Code konvertieren und prüfen → neues Modul anlegen → Daten migrieren (Dry-Run, dann Anwenden mit Backup) → Slices umhängen. Jeder Schritt hat ein Rückgängig.

Stand Beta 1: M1, M2, M3 (Daten rekursiv, Code-Hinweise), M4, M8, M9, M10 umgesetzt (`pages/migration.php`, `mform:migrate`, `mform:lint`). Offen für Beta 2: M5, M6, M7.

## 4. Offene Issues: Prüfung und Einordnung

Geprüft am 11.09.2026 gegen den Code von 9.5.0.

| Issue | Stand in 9.5.0 | Einordnung für 10 |
|---|---|---|
| #437 Renderpfade vereinheitlichen | Teilweise umgesetzt: `MFormLabelRenderer`, `MFormLayoutCore`, `setFull()` in beiden Pfaden. Offen: Tab-Logik in gemeinsamen Helper, Paritäts-Tests. | 10.0 Beta 1. Wird durch die Test-Suite (5.7) abgesichert. |
| #429 YForm `repeater_light` | Nicht umgesetzt. | 10.0 Beta 2, zugleich Ziel der YForm-Migration (M7). |
| #425 / #403 Builder-Palette | Offen sind genau: `addCollapseElement`, `addAccordionElement`, `addColumnElement`, `addInlineElement`, `addRadioImgField`, `addRadioIconField`, `addRadioColorField`, `addTextReadOnlyField`, `addTextAreaReadOnlyField`, `addConditionalFieldsetArea`. `addInputField` fehlt ebenfalls. | 10.0 Beta 2. #403 kann geschlossen werden, #425 führt die Restliste. |
| #418 Tracking 9.2 | Überholt. | Schließen, ersetzt durch ein Tracking-Issue „10.0“. |
| #417 Conditional Logic, visueller Editor | API `addConditionalFieldsetArea()` und `data-mform-condition` existieren, der Builder emittiert die Attribute bereits (`conditionalWrapperAttrsPhp`). Der visuelle Editor fehlt. | 10.0 RC. |
| #412 Erweiterte Felder | Nichts davon vorhanden. | Rating und Tags in 10.0 RC, Rest nach 10.0. |
| #411 Builder Undo/Redo | Nicht umgesetzt. | Nach 10.0 (10.1). |
| #409 HTML5-Inputs | Keine der Methoden vorhanden. | 10.0 Beta 1, kleiner Aufwand, hoher Nutzen. |
| #407 Builder Live-Preview | Nicht umgesetzt. | 10.0 Beta 2. |
| #406 Builder JSON-Export/Import | Nicht umgesetzt. | 10.0 Beta 2. |
| #402 `\Dom\HTMLDocument` | Nicht umgesetzt. | 10.0 Beta 1, vollständig: Parser und SVG-Konverter auf `\Dom\HTMLDocument` / `\Dom\XMLDocument`, `utf8_decode()` und libxml-Workarounds entfallen. |
| #399 Field-Type-Registry | Nicht umgesetzt. | 10.0 Beta 1, Fundament für 5.1 und 5.2. |
| #397 A11y-Prüfung Media/Links | Nicht umgesetzt. | 10.0 RC, opt-in. |

## 5. Neue Themen für 10

### 5.1 Field-Type-Registry (#399) als Fundament
`MForm::registerFieldType('relation-select', RendererClass::class)`, ein Interface mit `render(MFormItem, Context)` für Parser und Flex-Repeater, dazu Registrierung eines Builder-Palette-Eintrags. Erste Nutzer: `relation_select`, `linkmap`, `mediaplace`. Damit müssen Fremd-Addons nicht mehr auf `addHtml()` ausweichen.

### 5.2 Linkmap-Bridge für Link-Felder
`addLinkField()`, `addCustomLinkField()` und `addLinklistField()` öffnen bei installiertem Linkmap das Overlay statt des Popups, analog zur MediaPlace-Bridge in 9.4. Die `ylink`-Quellen des Custom-Link-Widgets nutzen den Datensatz-Picker von Linkmap (`yform://tabelle/id`), das eigene YForm-Popup wird optional. Fallback ohne Linkmap bleibt wie heute.

### 5.3 Datenformat des Repeaters mit Version
Repeater-JSON bekommt einen Versionsmarker (`__v`), `MFormRepeaterHelper::decode()` migriert alte Werte transparent. Optional typisierte Zugriffe: `decode()->items()`, `->get('link')` mit Auflösung von Media-, Link- und Datensatz-Werten.

### 5.4 YForm `repeater_light` (#429)
Schlankes Value mit Sub-Feldtypen text, textarea, select, checkbox, number, optional ein Richtext-Feld je Zeile. Nutzt die Repeater-Engine aus 5.3, kein eigener Datenpfad.

### 5.5 Builder-Ausbau
Palette-Parität (#425), JSON-Export/Import (#406), Live-Preview über `rex_api_function` in einer Iframe-Sandbox (#407), Conditional-Editor (#417). Undo/Redo (#411) erst danach.

### 5.6 HTML5-Felder und erweiterte Felder
`addNumberField`, `addRangeField`, `addDateField`, `addDateTimeField`, `addTimeField`, `addEmailField`, `addColorField` (#409). Danach `addRatingField` und `addTagsField` (#412) als erste zwei der erweiterten Felder.

### 5.7 Test-Suite

Stand Beta 1: PHPUnit-Grundstock vorhanden (`tests/Unit`, `tests/Redaxo`, CI-Jobs). Offen: Golden-Snapshots für Wrapper-Kombinationen, Playwright-Smoke im Workflow.

PHPUnit für Parser, Flex-Repeater-Renderer, Konverter und Migrator. Golden-HTML-Snapshots für Wrapper-Kombinationen in beiden Renderpfaden (#437). Ein Playwright-Smoke-Test für Repeater, Builder und Migrationsseite im GitHub-Workflow. Die Regressionen aus 9.4.x bis 9.5.0 (Widget-IDs, doppeltes Escaping) werden als erste Tests festgehalten.

### 5.8 Kleinere Punkte
- Modul-Linter als Console-Command: findet `MBlock::show()`, veraltete Aufrufe und numerische Media-Keys in Repeatern (Warnung aus dem Konverter).
- Dark-Mode-Audit der Eingabefelder (Kommentar in #437: Schatten, Linien, Radius uneinheitlich).
- Englische Doku für die Kernkapitel, Doku-Seite „Was ist neu in 10“.
- Deprecation-Policy: Was in 10 als veraltet markiert wird, fliegt frühestens in 11.

## 6. Reihenfolge

| Meilenstein | Inhalt |
|---|---|
| 10.0.0-beta.1 | Branch-Setup, PHP 8.4, MBlock-Aufräumen (2), Migrationsassistent M1–M4 und M8–M10, Field-Type-Registry (5.1), HTML5-Felder (5.6), Test-Suite-Grundstock (5.7), DOM-Umstellung (#402), Rest von #437. |
| 10.0.0-beta.2 | Migration M5–M7, Repeater-Datenversion (5.3), `repeater_light` (5.4), Builder-Parität, Export/Import, Live-Preview (5.5), Linkmap-Bridge (5.2). |
| 10.0.0-rc.1 | Conditional-Editor (#417), Rating und Tags (#412), A11y-Prüfung (#397), Dark-Mode-Audit, Doku. |
| 10.0.0 | Stabilisierung, Migrationsleitfaden 9 → 10. |
| 10.1 | Undo/Redo (#411), weitere erweiterte Felder. |

## 7. Regeln für Beiträge auf `main` (MForm 10)

1. Kein PR ändert Signaturen bestehender `add*()`-Methoden oder gespeicherte Datenformate.
2. Neues Verhalten kommt über neue Optionen mit sicherem Standard.
3. Jeder PR referenziert ein Issue und ergänzt CHANGELOG (Abschnitt „10.0.0-dev“) und Doku.
4. Migrationswerkzeuge ändern Daten nur nach Dry-Run und mit Backup.
