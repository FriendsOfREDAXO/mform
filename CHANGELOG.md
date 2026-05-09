# MForm - REDAXO Addon für Modul-Input-Formulare

## Version 9.0.0-beta7

### Fixed

- **FlexRepeater: `setToggleOptions()` auf Selects funktioniert wieder** – nach dem Refactor in beta1 (mform_repeater2) wurden `setToggleOptions(...)` und das daraus entstehende `data-toggle="collapse"` / `data-toggle-item` im Repeater-Template ignoriert. Der `MFormFlexRepeaterRenderer` setzt jetzt automatisch `data-toggle="collapse"` auf Selects mit Toggle-Options und `data-toggle-item` auf den passenden `<option>`s, analog zum klassischen MForm-Pfad außerhalb des Repeaters. Damit greift `initMFormCollapses` aus `assets/mform.js` auch in geklonten Repeater-Items (über den `rex:ready`-Trigger des FlexRepeaters).
- **FlexRepeater: `addCollapseElement()` und `addFieldsetArea()` rendern wieder** – beide Wrapper-Typen wurden im FlexRepeater-Template komplett verworfen (kein `case`). Sie werden jetzt analog zum `mform_wrapper.php`-Fragment ausgegeben, inkl. `<a data-toggle="collapse">`-Button, `.collapse[data-group-collapse-id=…]`-Wrapper und `<fieldset><legend>`.
- **FlexRepeater: `addFieldsetArea($legend, …)` zeigt das Legend wieder** – der `MFormAttributeHandler` leitet den Schluessel `legend` aus den Attributes auf `MFormItem::setLegend()` um, der Renderer las aber aus `getAttributes()['legend']` und bekam dort immer einen Leerstring. Der Renderer liest Legend jetzt ueber `$item->getLegend()`, analog zum klassischen `MFormParser::openWrapperElement`-Pfad.
- **FlexRepeater: weitere Wrapper-Typen rendern wieder** – `tab` / `start-group-tab` / `close-tab` / `close-group-tab`, `inline` / `start-group-inline` / `close-inline` / `close-group-inline`, `column` / `start-group-column` / `close-column` / `close-group-column` und `start-group-collapse` / `close-group-collapse` fielen seit beta1 in den `default`-Case und wurden komplett verworfen. Sie werden jetzt mit Bootstrap-3-Markup ausgegeben (`nav-tabs` + `tab-content`, `form-inline`, `row` + `col-*`, `collapse-group`). Tab-IDs nutzen einen Platzhalter `__MFRTAB_<n>__`, der in `flex-repeater.js` `_renderItem()` pro Item-Klon durch eine eindeutige UID ersetzt wird (gleiche `n` → gleiche UID, damit `href`, Tab-Pane-`id` und `aria-controls` matchen). Damit aktiviert `start-group-collapse` jetzt auch Standalone-Collapse-Toggle ueber `initMFormLinkCollapse`.
- **FlexRepeater: HTML in Labels (z. B. FontAwesome-Icons) wird wieder gerendert** – `renderLabel`, `renderModalBlock`, `renderNestedRepeaterContainer` und das Container-Label in `MFormParser::openFlexRepeaterElement` haben Labels per `htmlspecialchars` escaped, wodurch z. B. `<i class="fas fa-tag"></i>` als Text dargestellt wurde. Labels gelten jetzt – wie im klassischen MForm-Pfad – als Entwickler-HTML und werden roh durchgereicht. Feld-Werte (Headline, Description, Alert) bleiben weiterhin escaped.

### Neu

- **FlexRepeater Layout-Steuerung** – `addRepeaterElement($id, $form, ..., ['layout' => 'horizontal'|'vertical'|'inline'])` (Default `horizontal`) steuert die Default-Darstellung der Felder im Repeater-Item:
  - `horizontal` (Default): Label links (`col-sm-3`) / Feld rechts (`col-sm-9`) via Bootstrap-3 `form-horizontal`-Markup.
  - `vertical`: Label oben, Feld unten (klassisch gestapelt).
  - `inline`: kompakte Stapel-Darstellung mit kleinen Uppercase-Labels.
- **FlexRepeater: Default-Styles für Wrapper im Item-Body** – `<fieldset>` aus `addFieldsetArea()` bekommt sichtbares Border + dezent gestylten `<legend>`; Collapse-Buttons und `.collapse`-Wrapper haben sauberen vertikalen Abstand. Kein Theme-Override nötig für saubere Default-Optik.
- **FlexRepeater: `.mfr-item-body` und `.mfr-nested-body` sind jetzt `.mform`-Scopes** – damit greifen Toggle-/Collapse-Helfer aus `assets/mform.js` (`getParentMForm()`, `initMFormSelectCollapse`, `initMFormToggleCollapse`) sauber pro Repeater-Item statt auf den Top-Level-`.mform` zurueckzufallen.

### Architektur-Notiz fuer kuenftige Anpassungen

Damit es nicht wieder dazu kommt, dass das Modul-Input das gesamte Formular gleichzeitig togglet:

- **Jeder klonbare Item-Body braucht einen eigenen `.mform`-Wrapper.** Toggle-/Collapse-Logik in `assets/mform.js` arbeitet ueber `getParentMForm()` und sucht den naechsten `.mform`-Vorfahren als Scope. Ohne eigenen Scope greift der Selektor auf den globalen Modul-Wrapper.
- **Bootstrap-3 ist Pflicht-Annahme** im Backend (kein Flex/Grid, kein `:has()`). Layout im Repeater bitte ueber `form-horizontal` + `col-sm-*`-Markup im PHP-Renderer aufbauen, nicht ueber neues Custom-Grid. Spaeterer Bootstrap-Exit wird so auf einen Schlag in `MFormFlexRepeaterRenderer::wrapFormGroup()` und im klassischen `mform_default.php`-Fragment moeglich.
- **Toggle-IDs (`data-group-collapse-id`, `data-toggle-item`) sind nur innerhalb eines `.mform`-Scopes eindeutig.** Im FlexRepeater duerfen Item-Klone die selbe ID tragen, weil der Scope der Item-Body ist. Beim Hinzufuegen weiterer wiederholbarer Strukturen (z. B. neue Nested-Container) immer `<div class="mform ...">` als Wurzel verwenden.
- **Layout-Schalter gehen ueber `data-mfr-layout` am Container**, nicht ueber inline `style`/CSS-Variablen am Item. Custom-Themes hooken sich ueber `.mfr-container[data-mfr-layout="…"]` ein.

---

Nonst noch

### Neu

- **`MFormOutput`** (`FriendsOfRedaxo\MForm\Output\MFormOutput`) – Fluent, immutable Output-Helper für Repeater-Daten:
  - Chainable: `filter`, `where`, `sort`, `reverse`, `limit`, `skip`, `page`, `map`
  - Terminals: `all`, `first`, `last`, `count`, `isEmpty`, `pluck`, `group`
  - Empty-State: `whenEmpty()`
  - Render-API: `render`, `renderList`, `renderGrid`, `renderChunks`, `renderFragment`
  - Framework-Presets für `renderGrid()`: `GRID_BOOTSTRAP`, `GRID_TAILWIND`, `GRID_UIKIT`, `GRID_NONE` – inkl. globalem Default via `setDefaultGridFramework()`
  - **`MFormOutput::tag()`** – HTML-Builder mit Class-Array-Support, Boolean-Attributen (HTML5), automatisch escaped Werten und Auto-Close für Void-Tags
- **Single-Value Helfer** für klassische `REX_VALUE[…]`-Felder:
  - `linkUrl()` / `link()` – auflöst `rex-article://`, `rex-media://`, `mailto:`, `tel:`, externe URLs und numerische Artikel-IDs; setzt automatisch `target="_blank" rel="noopener"` für externe Links
  - `picture()` – baut `<picture>` aus einer Media-Query → media_manager-Type-Map, nimmt `alt` aus Mediapool-Metadaten
  - `mediaList()` – splittet CSV von `addMedialistField`/`addImagelistField` zu existierenden `rex_media`-Instanzen
  - `richtext()` – semantischer Marker für TinyMCE-Output (kein doppeltes Escape), optional Tag-Whitelist
  - `excerpt()` – Plaintext-Auszug aus HTML, getrimmt auf N Wörter
- **Demo-Modul „Content-Pflege-Modul [Output via MFormOutput]"** (`Backend → Demos → Repeater`) – realer redaktioneller Workflow: Headline + Intro + Repeater-Sektionen mit Titel, TinyMCE-Text, Hauptbild, Link und Galerie, ausgegeben über `MFormOutput`.
- **Doku-Seite „MFormOutput – Fluent Module Output"** (`Backend → MForm → Dokumentation → MFormOutput`) – mit Framework-Vergleichstabelle, `tag()`-Beispielen, Single-Value-Helfern und Sicherheitshinweis.
- **SECURITY.md** – Verantwortungsvolle Offenlegung über GitHub Security Advisory, Versionsmatrix, Reaktionszeiten nach CVSS-Schweregrad und klar abgegrenzter Scope. Über die Doku-Seite **Sicherheit** im Backend (`Backend → MForm → Dokumentation → Sicherheit`) auch direkt einsehbar.
- **GitHub Action „Static Analysis“** (`.github/workflows/static-analysis.yml`) – läuft bei Push, PR und manuell (`workflow_dispatch`):
  - PHP-Lint Matrix (8.1, 8.2, 8.3, 8.4)
  - PHP-CS-Fixer Dry-Run (`@PSR12`-Fallback, falls keine eigene Konfig vorhanden)
  - **Rexstan** (`^3.0`) auf Basis des aktuellen REDAXO-Latest-Release, scope-begrenzt auf das mform-Addon

### Code-Qualität

- **`empty()` aus `lib/` und `pages/` entfernt** – alle ~50 Vorkommen durch strikte Vergleiche ersetzt (`'' !== $x`, `[] !== $x`, `null !== $x`). Stabilere Semantik, keine impliziten Falsy-Casts mehr.
- **TODO-Marker aufgelöst** – veraltete `// TODO`- und `/** TODO */`-Blöcke in `MFormElements.php` und `MFormRepeaterHelper.php` entweder umgesetzt oder durch erklärende Kommentare ersetzt.
- **Inline-CSS/JS aus `pages/docs.php` ausgelagert** in `assets/css/docs.css` und `assets/js/docs.js`. Assets werden in `boot.php` nur auf den Doku-Seiten registriert. Cache-Busting übernimmt REDAXO automatisch.

### Fixed

- **Doku: TOC-Filter funktioniert wieder nach PJAX-Navigation** – `assets/js/docs.js` initialisiert sich jetzt über `rex:ready` (jQuery) statt nur über `DOMContentLoaded`. Init-Routinen sind idempotent (Marker-basiert), keine Doppel-Bindings beim Re-Init.

---

## Version 9.0.0-beta6

### Neu

- **Visueller Form Builder** (`Backend → MForm → Form Builder`) – Drei-Spalten-Oberfläche zum Zusammenklicken von MForm-Modulen mit Drag-and-Drop und Live-PHP-Code-Generator.
  - 16 Feld-Typen: Text, Textarea, Select, Radio, Checkbox, Hidden, Headline, Description, Media, Medialist, Imagelist, Link, Linklist, Custom Link, Custom Link Multiple, Flex Repeater
  - 2 Repeater-Verschachtelungs-Ebenen, mit eigenständigen Drop-Zonen
  - Feld-Eigenschaften: Label, Default-Value, Placeholder, Hinweistext, CSS-Klassen, Zeilen (Textarea), Optionen, Required, TinyMCE-Editor, `setFull()`, Custom-Link-Linktypen-Toggles, Link-/Media-Kategorien, Extern-Link-Prefix, Media-Type-Whitelist, Add-Button-Text
  - Im Canvas: Repeater einklappbar, Item **Duplizieren / Kopieren / Einfügen** mit rekursiver UID/ID-Neuvergabe
  - Generator emittiert nur Optionen, die vom Core-Default abweichen

### Changed

- **Flex Repeater Defaults** angepasst:
  - `collapsed` jetzt `false` (war `true`)
  - `first_open` jetzt `false` (war `true` – ohne `collapsed=true` ohne Wirkung)
  - `open` (neu hinzugefügtes Item geöffnet) jetzt `true` (war `false`)
  - `copy_paste` jetzt `true` (war `false`)
  - `confirm_delete` jetzt `true` (war `false`)
  - Hinweis: Bestehende Module ohne explizite Optionen verhalten sich daher leicht anders. Bei Bedarf alte Werte explizit setzen.
- Neue Repeater-Optionen im Builder verfügbar: `default_count`, `collapsed`, `first_open`, `show_toggle_all`, `open`, `copy_paste`, `confirm_delete`, `confirm_delete_msg`, `btn_text`, `btn_class`.

### Demo

- **Layout Preview Demo** (`Backend → MForm → Demos → Layout Preview`) – Demo-Seite für `HtmlToSvgConverter` und `LayoutPreviewBuilder` mit Beispielen für SVG-Tag-Sets und CSS-Style-Attribut-Mapping.

---

## Version 9.0.0-beta5

### Code-Qualität

- **Rexstan: 0 Fehler im gesamten Addon** – Alle statischen Analyse-Fehler (`variable.undefined`, `method.notFound`, `nullCoalesce.offset`, `argument.type`) behoben. Betrifft `boot.php`, Demo-Seiten (`pages/demo.*.php`), `pages/docs.php` sowie alle YForm-Templates (`ytemplates/bootstrap/` und `ytemplates/classic/`).
  - `$this`-Aufrufe in Addon-Includes auf `rex_addon::get('mform')` umgestellt
  - Explizite `@var`- und `@psalm-scope-this`-Annotationen in allen YForm-Templates ergänzt
  - `$counter ?? 0`-Fallback in Templates für YForm-injizierte Variablen
  - Null-Guard für `rex_file::get()` in `docs.php`

### Fixed

- **Custom-Link-Widget: Werte und Vorschau nach Reload korrekt** – Bereits gespeicherte Werte werden im Single- und Multi-Widget wieder korrekt übernommen und lesbar aufgelöst, auch für `redaxo://ID`-Links. Der Vorschau-Button für Medien steht jetzt direkt hinter dem Namensfeld und ist mit bewusstem Abstand von den restlichen Aktionen getrennt.
- **Linklist im Flex-Repeater: Artikelnamen nach Reload korrekt** – Bereits gespeicherte Linklisten-Einträge werden nach dem erneuten Öffnen nicht mehr nur als Platzhalter wie `Artikel 5` angezeigt, sondern wieder zu den echten Artikelnamen aufgelöst.
- **Custom-Link-Multi: Trash-Icon** – Der Entfernen-Button je Eintrag zeigt jetzt ein `fa-trash`-Symbol statt des durchgestrichenen Link-Icons.
- **Custom-Link: E-Mail-Validierung** – Die `mailto:`-Eingabe prüft vor dem Speichern, ob die eingegebene E-Mail-Adresse syntaktisch valide ist. Ungültige Eingaben werden mit einem Hinweis abgelehnt.
- **Custom-Link: Telefon-Validierung** – Die `tel:`-Eingabe lässt nur Ziffern, `+`, `-`, Leerzeichen und Klammern zu. Ungültige Eingaben werden mit einem Hinweis abgelehnt.

---

## Version 9.0.0-beta4

### Fixed

- **Flex-Repeater: Custom-Link-Widgets korrekt gerendert** – `addCustomLinkField()`, `addMFormLinkField()`, `addMediaField()`, `addMFormMediaField()` und `addCustomLinkMultipleField()` zeigten im Flex-Repeater zuvor den Platzhalter „Widget-Typ nicht unterstützt". Sie werden jetzt als vollständige Widgets gerendert.
- **Flex-Repeater: Bilderliste vollständig identisch zum normalen Widget** – `addImagelistField()` nutzt jetzt `rex_var_custom_medialist::getWidget()` direkt und rendert damit das vollständige Widget mit Galerie/Raster/Listen-Ansicht, View-Toggle und vertikaler Toolbar – identisch zur Nutzung außerhalb des Flex-Repeaters. Zuvor wurde nur ein einfaches Skelett ohne Preview und Ansichts-Umschalter angezeigt.
- **Flex-Repeater: Readonly-Felder korrekt gerendert** – `addTextReadonlyField()` und `addTextareaReadonlyField()` gaben zuvor einen leeren String zurück. Werden jetzt als `readonly`-Input/Textarea dargestellt.
- **Flex-Repeater: Radio- und Checkbox-Styling** – `addRadioField()` und `addCheckboxField()` rendern jetzt Bootstrap-3-konforme `<div class="radio">` / `<div class="checkbox">`-Wrapper statt nackter `radio-inline`-Labels. Vertikale Abstände und Ausrichtung korrigiert.
- **Flex-Repeater: Dead-Code entfernt** – Unreachable-Code-Block nach dem `custom-link-multi`-Case im Renderer bereinigt.

---

## Version 9.0.0-beta3

### Neu

- **`addModalElement()`** – Bootstrap-Modal als Sub-Formular direkt im Modul-Input
  - Trigger-Button und Modal-Titel konfigurierbar über `$label`
  - Sub-Formular als MForm-Instanz, Callable oder HTML-String (`$form`)
  - Button-Klasse (`$btnClass`, Standard `btn-default`) und Ausrichtung (`$align`: `'left'`, `'center'`, `'right'`) frei wählbar
  - Felder werden beim Speichern des Moduls normal übernommen – kein AJAX erforderlich
  - Vollständig im Flex-Repeater unterstützt (eindeutige IDs per Row via `__MFRID__`-Placeholder)
  - Einfache Felder (Text, Select, Checkbox) auch innerhalb des Modals verwendbar; TinyMCE/MarkdownEditor nicht empfohlen
- **`addColorSwatchField()`** – Farbwähler mit Vorschau-Input und Swatches-Popup
  - Speichert direkte Hex-Werte (`#2f77bc`) **oder** CSS-Klassennamen (`.bg-primary`)
  - Swatches unterstützen optionalen `preview`-Schlüssel für CSS-Klassen-Einträge
  - Vollständig im Flex-Repeater unterstützt
  - Verfügbar in: Klassisches Modul ✓ · YForm ✓
- **YForm Value-Type `color_swatch`** – ColorSwatch-Feld nativ in YForm-Formularen nutzbar
  - Swatches als JSON im Feld-Parameter konfigurierbar
  - Korrekte Listenansicht im YForm-Manager (Farbvorschau-Quadrat bei Hex-Werten)

### Fixed

- Verfügbarkeitstabelle in der Dokumentation korrigiert: `addToggleCheckboxField` und `addColorSwatchField` spiegeln jetzt die tatsächlich vorhandenen Implementierungen wider

---

## Version 9.0.0-beta2

### Neu

- **`MFormOutputHelper::createLinkData()` / `normalizeLinkData()`** – Unified Link-API als einheitlicher Einstieg für alle Link-Typen
  - Akzeptiert alle Eingabeformen: einfachen String (`redaxo://`, `https://`, `mailto:`, `tel:`), Array mit `id`/`name` (Repeater-Format aus Issue #357), bereits vorbereitetes `customlink_*`-Array
  - Parameter `mode` (`frontend` | `raw` | `strict`): steuert ob Backend-Labels (`name` mit ID-Suffix) erhalten bleiben oder bereinigt werden
  - Optionaler Parameter `extern_blank` (bool) für `target="_blank"` bei externen Links
- **`MFormOutputHelper::normalizeRepeaterItems()`** – normalisiert Link-Felder in einem kompletten Repeater-Array in einem Schritt
  - Standard: fügt normalisiertes Array als `<feldname>_normalized` hinzu (kein Datenverlust)
  - Option `replace => true` überschreibt das Original-Feld
- **`MForm::useCustomLinkForClassicWidgets(true)`** – rendert `addMediaField()` und `addLinkField()` intern über das `custom_link`-Widget
  - Kein Änderung am Speicherformat (`REX_MEDIA_n` / `REX_LINK_n` bleiben identisch)
  - Standard `false` für vollständige Rückwärtskompatibilität
- **`addCustomLinkMultipleField()`** – mehrere Custom-Links in einem Feld (Repeater-basiert), Single-Format bleibt unverändert
- **`addConditionalFieldsetArea()`** – regelbasierte Anzeige von Formularbereichen
- **`addFlexRepeaterElement()`** – neuer Flex-Repeater als Standardpfad, inkl. stabiler Initialisierung in dynamischen Backend-Kontexten
- **Kopieren / Einfügen für Flex-Repeater** – `copy_paste => true` am `addRepeaterElement()`
  - Copy-Button pro Item speichert Daten im `sessionStorage`
  - Einfügen-Button fügt das kopierte Item als neues Element am Ende ein
  - Clipboard bleibt nach Seitenreload erhalten; `__disabled`-Status wird nicht übernommen
- **`MFormRepeaterHelper::decode()`** – bequemes Dekodieren von Repeater-Werten inkl. Filterung deaktivierter Items
- **`MFormRepeaterHelper::filterByField()`** – Items nach Feldwert filtern (optionaler strikter Vergleich)
- **`MFormRepeaterHelper::sortByField()`** – Items nach Feldwert sortieren (asc/desc, auto numerisch/alphabetisch)
- **`MFormRepeaterHelper::groupByField()`** – Items nach Feldwert gruppieren
- **`MFormRepeaterHelper::limitItems()`** – Items begrenzen / Pagination-Unterstützung
- **Template-API:** `MForm::registerTemplate($key, $class)`, `MForm::fromTemplate($key)`, `->applyTemplate($key)` über interne Registry (projektweite Defaults wiederverwendbar)
- **YForm Value-Type `custom_link_multi`** – mehrere Custom-Links in einem YForm-Feld (JSON-Array)
- **`addMFormMediaField()`** – MForm-natives Media-Widget ohne Reindex-Problem beim Klonen in MBlock
- **Dokumentationsseite „Was ist neu in MForm 9?"** als Einstiegsseite in den Backend-Docs

### Erweitert

- Repeater-UI: Aktiv/Inaktiv-Status pro Item (Auge-Icon), Ausgabe-Filter für deaktivierte Items, verbesserte Toggle-All-Logik
- Repeater-Header: Statusanzeige als gefüllter Punkt (grün = aktiv, rot = offline)
- Repeater-Symbole und Bedienung vereinheitlicht (konsistentes Auf-/Zuklappen inkl. verschachtelter Repeater)
- Linklist/Medialist-Repeater-Widget: robuste Übernahme aus Linkmap/Mediapool-Popups
- Medialist-Widget: View-Switch zwischen Listen- und Rasteransicht (`view`, `view_switch`)
- Medialist-Widget: Toggle wieder als Symbol-Button in der Toolbar
- Medialist-Widget: echte Datei-Previews analog zur `imagelist`
- `imagelist` auf das neue List-Widget-Muster umgestellt (schlanker Wrapper um `medialist`)
- Neue Demo-Module ergänzt (Conditional Fields Builder, erweiterte Repeater-Szenarien)
- Dokumentation erweitert: Repeater, Output-Filter, Conditional Fields, neue Einstiegsseite
- Navigations- und Titelkonsistenz im Backend verbessert
- Bestehende Helper `prepareCustomLink()`, `getCustomUrl()`, `getCustomLinkUrl()` bleiben unverändert, werden intern auf die neue Normalisierung geroutet

### Fixed

- TinyMCE-Kompatibilität: stabileres Save/Destroy/Reinit bei Add/Move/Sort/Remove im Repeater
- Medialist-Widget: Preview-URLs korrekt aufbereitet (HTML-Entity-Decode für `rex_medialistbutton_preview`)
- YForm Value-Type `custom_link`: Bug im Classic-Template (`extern` → `external`) behoben; Anker-Button über `anchor: 0` ausblendbar
- Verhindert Reindex-Probleme in MBlock beim Klonen von Blöcken (via `useCustomLinkForClassicWidgets`)

### Migration

- Bestehende MBlock-Module mit `addMediaField()` / `addLinkField()` benötigen `MForm::useCustomLinkForClassicWidgets(true)` vor dem `MForm::factory()`-Aufruf, um Reindex-Probleme beim Klonen von Blöcken zu vermeiden – in einem Modul-Input reicht der einmalige Aufruf; das Zurücksetzen auf `false` ist nur nötig, wenn im selben Request weitere MForm-Instanzen ohne das Flag folgen (z. B. in `boot.php` oder Addon-Seiten)
- Repeater-Ausgabe: `MFormRepeaterHelper::decode()` ist bei Verwendung des Online/Offline-Toggles (`__disabled`) erforderlich; für einfache Repeater ohne Toggle bleibt `json_decode()` ausreichend
- Namespace-Änderungen: keine neuen Breaking Changes gegenüber v8 (Namespace `FriendsOfRedaxo\MForm` bleibt)
- Siehe [08_mblock_migration.md](08_mblock_migration.md) für den Migrationsleitfaden

## Version 8.1.6
- fix: `notice` Attribut kann nun auch über den Parameter-Array übergeben werden (z.B. `addMediaField`, `addTextField` etc.) – schließt #389

## Version 8.0.8
- add simple anchor link support for custom link

## Version 8.0.7
- add toggle wrapper for inputs element

## Version 8.0.6
- add form-group-class attribute  
- add html to svg converter
- fix toggle issues with repeater

## Version 8.0.5
- Add new icon radio input element field `addIconRadioField`
- fix some repeater issues with cke5 and collapses

## Version 8.0.4
- Repeater issues with addLinkField, addCustomLinkField, addMediaField, addInputs, addToggleCheckboxField, addCollapseElement, setToggleOptions solved
- Repeater CustomLink yForm link option issue solved
- Fix radio collapse not work in repeater issue 
- cke5 reinit issue fixed

## Version 8.0.3
- add showWrapper functionality

## Version 8

- **Major Release and major changes included**
- **IMPORTANT: breaking namespace change -> from `MForm` to `FriendsOfRedaxo\MForm`**
- **New:** alpine repeater with 2 level support
- **New:** Complete docs by Alexander Walther @alxndr-w
- All demo modules now directly installable
- **New:** addRadioImgField

and much more

## Version 8-beta7

- **IMPORTANT: breaking namespace change -> from `MForm` to `FriendsOfRedaxo\MForm`**
- new docs added - thanks @alxndr-w
- added install button to collapse title for example modules
- fixed multiple select default value issue
- fixed select '0' value issue
- ensured full option functionality for description
- some beauty changes
- custom link name issue fixed
- add english translations
- add placeholder example
- add outputs for each examples

## Version 8-beta6

- fix button style, ensure that buttons ever will display, added potential disable style for buttons
- min and max count of blocks for repeater
- rename `addRadioImgInlineField` to `addRadioImgField` and `addRadioColorInlineField` to `addRadioColorField`

## Version 8-beta5

- added delete confirmation option for repeater group and fields
- added radio image / color inline field
- default value support for repeater added

## Version 8-beta4

- fix repeater parentId issue
- added handling for cke5 move preparation
- fix :id label issue
- added the option to hide the repeater if empty
- css for repeater exchanged

## Version 8-beta3

- added repeater examples
- fix :id, move addInputs method to elements class

## Version 8-beta2

- add recursion for repeater mform subform obj structure

## Version 8-beta1

- change mform css filename
- remove unused $inline variable
- add inputs for default form input forms
- alpine repeater with 2 level support
- remove dynamic creation of property in fragments

## Version 7.2.2

- fix PHP 8.1 deprecation warnings
- prevent module action when Gridblock addon is used @skerbis

## Version 7.2.1

- make mform manipulable

## Version 7.2.0

- Stretch height of ImageList

## Version 7.1.2

- PRESAVE-Action: Die REX_VALUES werden nach einer PRESAVE Aktion aus dem $_POST geladen.
- Dadurch sind Validierungen ohne Neueingabe des Contents jetzt möglich. @skerbis

## Version 7.1.1

- use mblock:change event to reinit mform elements

## Version 7.1.0

- remove form-group wrapper for hidden input fields
- fix collapse accordion aria-expanded click issue
- added the selectpicker class by default, to remove it you have use the class `none-selectpicker` on your select element
- added mblock compatibility for mform usage of with the selectpicker
- fix some small issues to get ability for combined and nested wrapper element usage  

## Version 7.0.0

- added column element for some form input column elements
- removed element fragments and added stuff to wrapper fragment
- removed author email address
- wrapper fragment changed, remove id handling in output and js for accordion, collapse and tab
- properties direction changed in `addAccordionField`, `addCollapseField`
- open properties added to `addTabField`
- added inline wrapper element `addInlineElement`
- minor css changes @skerbis
- add php 7.x compatibility
- add fragment files
- remove data themes and use fragments as theme templates
- add radio-, checkbox and select toggle options for collapse
- add radio-, checkbox and select toggle options for tabs
- create generic collapse, accordion and tab handling for mblock usage
- remove deprecated stuff
- remove default theme config form in addon page
- ytemplates moved from data to addon root path
- remove docs plugin and unused lang strings
- make example modules installable
- revised all example modules remove all .ini's and use instead of them .inc files
- add new wrapper example files
- validations removed
- changed screenshot @skerbis
- correcting readme @skerbis
- add english readme @sckerbis
- add readme to backend pages @skerbis
- add changelog to backend navigation @skerbis 
- fix double quote issue @dtpop

### Migration to from v6.x.x to v7.x.x

1. Removed class methods:
   1. addEditorField
   2. addCke5Field
   3. addFieldset
   4. closeFieldset
   5. addTab
   6. closeTab
   7. addCollapse
   8. closeCollapse
   9. addAccordion
   10. closeAccordion
   11. isSerial
   12. setToggle
   13. setValidation
   14. setValidations

2. Renamed class methods:
   1. addOption => setOption 
   2. addAttribute => setAttribute
   4. disableOptions => setDisableOptions
   5. disableOption => setDisableOption
   6. addFieldsetField => addFieldsetArea
   7. addCollapseField => addCollapseElement
   8. addTabField => addTabElement
   9. addAccordionField => addAccordionElement
   10. addTooltipInfo => setTooltipInfo
   11. addCollapseInfo => setCollapseInfo
   12. addParameters => setParameters
   13. addParameter => setParameter

### Migration how do?

- `Call to undefined method MForm::addFieldset()` or `MForm::closeFieldset()`
  - Use `addFieldsetArea` like `MForm::factory()->addFieldsetArea('Label', MForm::factory()->addTextField(1, ['label' => 'Text']));`
- `Call to undefined method MForm::addCollapse()` or `MForm::closeCollapse()`
    - Use `addCollapseElement` like `MForm::factory()->addCollapseElement('Collapse', MForm::factory()->addTextField(1, ['label' => 'Text']));`
- `Call to undefined method MForm::addTab()` or `MForm::closeTab()`
    - Use `addTabElement` like `MForm::factory()->addTabElement('Tab', MForm::factory()->addTextField(1, ['label' => 'Text']));`
- `Call to undefined method MForm::addAccordion()` or `MForm::closeAccordion()`
    - Use `addAccordionElement` like `MForm::factory()->addAccordionElement('Accordion' MForm::factory()->addTextField(1, ['label' => 'Text']));`
- `Call to undefined method MForm::addEditorField()` or `MForm::addCke5Field`
  - Use `addTextAreaField` with editor attributes like `$mform->addTextAreaField('1', ['class' => 'cke5-editor', 'data-lang' => \Cke5\Utils\Cke5Lang::getUserLang(), 'data-profile' => 'default']);`
- `Call to undefined method MForm::addOption()` or `MForm::addAttribute()` or `MForm::disableOptions()` or `MForm::disableOption()`
    - Check the list `Renamed class methods` and use the new method name instead of the old one
- `Call to undefined method MForm::setToggle()`
    - Use `addToggleCheckboxField` instead of `addCheckboxField` with `setToggle`

## Version 6.1.2

- Fix: UTF-8 encoding for arabic and other charsets in link and link-lists

## Version 6.1.1

- Adding return types for yform methods

## Version 6.1.0

- New: Dark-mode support for REDAXO >= 5.13 @schuer @skerbis

## Version 6.0.9 – 6.0.13

- Select fix. @dtpop, @IngoWinter, @skerbis, multiple issues fixed regarding single select- and multiselect-fields
- Allow callable @DanielWeitenauer
- new check for JSON Values 1.x.x


## Version 6.0.6

- fixed: delete all entries in imagelist @ynamite 
- fixed: wrong var prevents cusrtom classes on tabs @bitshiftersgmbh 

## Version 6.0.4

- Fixed missing external link in widget
- Some minor fixes
thx @lexplatt  @Hirbod

## Version 6.0.3
prepareCustomLink fixed


## Version 6.0.2
- readme style fixes @crydotsnake 
- remove .formcontrol on input fields type color @olien
- some validation methods changed and calls deleted @skerbis


## Version 6.0.1
- added some docs
- minor bugfixes


## Version 6.0.0

* use rex_factory_trait in MForm class
* added YForm Links in custom_link
* removed `parsley` validation @skerbis, you should use html validations: https://developer.mozilla.org/en-US/docs/Learn/Forms/Form_validation
* deprecated: `closeCollapse`, `closeTab`, `closeAccordion`
* change `addCollapse`, `addAccordion`, `addTab` functionality, use `addForm` to add content in this methods
* added `addForm` method
* added Media inUseCheck for media inside `custom_link` and `imagelist` in YForm
* added some styling
* added some Svensk översättning @interweave-media 
* added some English translation @ynamite
* added some docs @skerbis

### Breaking changes: 

The REX_CUSTOM_LINK Var now saves the data in a regular REX_VAR. So the usage of REX_CUSTOM_LINK is not backward compatible. You should move the values from Linklist to a value field. 

Parsley has been removed. AddValidation is functionless. 

removed `closeCollapse`, `closeTab`, `closeAccordion`
> Look at the new wrapper field examples


## Version 5.3

* added custom link as widget
* exchange custom link for yform and rex_form
* added image list widget for mfrom, rex_form and yform 

## Version 5.2.5

* add possibility to disable select options
* fix fieldset and grouping issues

## Version 5.2.4

* re-add special input types support

## Version 5.2.3

* removed tab history

## Version 5.2.2

* Parameter must be an array warning fix

## Version 5.2.1

* fixed: Media-Button Parameter type space
* Link-Title for Custom-Link Buttons added
* Attributes for media- and link-elements added, which allows validation via Parsley
* fixed: c14n html body wrapping removed

Changes: 

* now uses includeCurrentPageSubPath to show pages @christophboecker
* Cache buster will be added by rex core @staabm
* init.js simplified @staabm


## Version 5.2.0 pre-release

* Add Info Tooltip
* [Bootstrap Toggle Checkbox](http://www.bootstraptoggle.com) als neues Element `addToggleCheckboxField` hinzugefügt
```
$mform->addToggleCheckboxField('1.show_icons', [1 => ''], ['label' => 'Icon verwenden', 'label-col-class' => 'col-md-3', 'form-item-col-class' => 'col-md-9'], null, 1);
```
* `setLabelColClass` und `setFormItemColClass` hinzugefügt, ermöglicht das überschrieben der Standard "col-md-x" Classen
```
$mform->addTextField('2.0.title', ['label' => 'Titel'], ['empty']);
$mform->setLabelColClass('col-md-3');
$mform->setFormItemColClass('col-md-9');
```
* 4 Alert Message Elemente hinzugefügt
```
$mform->addAlertInfo('Heads up! This alert needs your attention, but it\'s not super important.');
$mform->addAlertSuccess('Well done! You successfully read this important alert message.');
$mform->addAlertDanger('Oh snap! Change a few things up and try submitting again.');
$mform->addAlertWarning("<strong>Warning!</strong> Better check yourself, you're not looking too good.");
```
* Collapse Panel für Formular elemente hinzugefügt, das steuern der Collapse über Checkboxen ist möglich
* Output helper class `MFormOutputHelper` bereit gestellt

## Version 5.1.0

* Javascript für Multipe Selects entfernt, dafür nötiges Hidden-Input ebenfalls entfernt. 
    * **Zu beachten bei Updates**:
        * Ein Hidden-Input Feld welches Komma-separiert die selected-options aufnimmt gibt es nicht mehr.
        * Multiple Selects werden künftig als JSON-String direkt im REX_VALUE gespeichert.
        * Dies Wirkt sich auf die Auswertung der REX_VALUES im Modul-Output aus.
        * Künftig muss für diese REX_VALUES `rex_var::toArray` genutzt werden um die JSON-Strings in Arrays zu decodieren.
        * Beim editieren alter REX_VALUES gehen keine zuordnungen verloren, beim erneuten Speichern wird im neuen Format gespeichert.
        * Im DB Column des REX_VALUES wird aus dem String `1,2` der JSON-String `["1","2"]`
* Docu Plugin hinzugefügt
    * Das alte MForm Github Wiki wurde in das Docu-Plugin übernommen
    * Alle Inhalte wurden überarbeitet
    * Thanks Alexander Walther, Paul Götz, Tim Filler
* Bootstrap Tabs integriert
* Selected und Checked haben einen Leerzeichen-Prefix erhalten.
* EN-Sprachdatei wurde übersetzt    
    * Thanks Thomas Skerbis, ynamite 
