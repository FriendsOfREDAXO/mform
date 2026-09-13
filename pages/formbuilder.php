<?php

/**
 * Visual Form Builder for MForm.
 *
 * Drag & drop UI, that emits MForm PHP code into a copyable text area.
 * No persistence, no module integration - pure frontend tool.
 *
 * @author Friends Of REDAXO
 * @package redaxo5
 * @license MIT
 */

$addon = rex_addon::get('mform');

$infoModalBody = '<p>'
    . 'Klicke ein Feld in der linken Palette an um es einzufuegen. Reihenfolge per Drag&Drop am Griff aendern. '
    . 'Per Klick auf ein eingefuegtes Feld oeffnet sich rechts der Eigenschaftsdialog. '
    . 'Im unteren Bereich entsteht in Echtzeit der MForm-PHP-Code zum Kopieren.'
    . '</p>'
    . '<div class="alert alert-info" style="margin-top:1em;margin-bottom:0">'
    . '<strong>Hinweis:</strong> Der Form Builder bietet bewusst nur eine kuratierte Auswahl der haeufigsten MForm-Felder, '
    . 'um einen einfachen Einstieg zu ermoeglichen. Spezielle Felder wie <code>addRadioImgField</code>, '
    . '<code>addInputField</code> mit eigenen Typen und Layouts wie Column/Collapse sind weiterhin '
    . 'direkt im PHP-Code verfuegbar &ndash; siehe <a href="https://github.com/FriendsOfREDAXO/mform/tree/main/docs" target="_blank" rel="noopener">MForm-Doku</a>. '
    . 'Der generierte Code laesst sich beliebig erweitern.'
    . '</div>';

// TinyMCE-Profile aus DB lesen, falls verfuegbar.
$tinyProfiles = [];
if (rex_addon::get('tinymce')->isAvailable() && class_exists(\FriendsOfRedaxo\TinyMce\Handler\Database::class)) {
    $rows = \FriendsOfRedaxo\TinyMce\Handler\Database::getAllProfiles() ?? [];
    foreach ($rows as $row) {
        if (isset($row['name']) && '' !== (string) $row['name']) {
            $tinyProfiles[] = (string) $row['name'];
        }
    }
    sort($tinyProfiles);
}

// CKEditor-5-Profile, falls das Addon installiert ist.
$cke5Profiles = [];
if (rex_addon::get('cke5')->isAvailable() && rex_sql_table::get(rex::getTable('cke5_profiles'))->exists()) {
    foreach (rex_sql::factory()->getArray('SELECT name FROM ' . rex::getTable('cke5_profiles') . ' ORDER BY name') as $row) {
        if ('' !== (string) ($row['name'] ?? '')) {
            $cke5Profiles[] = (string) $row['name'];
        }
    }
}
$editorProfileOptions = '';
foreach (['tinymce' => $tinyProfiles, 'cke5' => $cke5Profiles] as $editor => $profiles) {
    foreach ($profiles as $name) {
        $editorProfileOptions .= '<option value="' . rex_escape($name) . '" data-editor="' . $editor . '"></option>';
    }
}
$tinyProfileHtml = '<input type="text" class="form-control" data-fb-prop="editorProfile" placeholder="default" list="mform-fb-editor-profiles" autocomplete="off">'
    . '<datalist id="mform-fb-editor-profiles">' . $editorProfileOptions . '</datalist>';

$body = <<<'HTML'
<div id="mform-fb" class="mform-fb">
    <div class="mform-fb__palette">
        <h4>Felder</h4>
        <div class="mform-fb__palette-search">
            <input type="text" class="form-control" data-fb-palette-search placeholder="Feld suchen (z. B. color, alert, link)">
        </div>
        <ul class="mform-fb__field-list" data-fb-palette>
            <li class="mform-fb__pal-item" data-type="text">Text</li>
            <li class="mform-fb__pal-item" data-type="textarea">Textarea</li>
            <li class="mform-fb__pal-item" data-type="number">Number</li>
            <li class="mform-fb__pal-item" data-type="range">Range</li>
            <li class="mform-fb__pal-item" data-type="date">Date</li>
            <li class="mform-fb__pal-item" data-type="datetime">Date/Time</li>
            <li class="mform-fb__pal-item" data-type="time">Time</li>
            <li class="mform-fb__pal-item" data-type="email">E-Mail</li>
            <li class="mform-fb__pal-item" data-type="color">Color (nativ)</li>
            <li class="mform-fb__pal-item" data-type="select">Select</li>
            <li class="mform-fb__pal-item" data-type="radio">Radio</li>
            <li class="mform-fb__pal-item" data-type="checkbox">Checkbox</li>
            <li class="mform-fb__pal-item" data-type="togglecheckbox">Toggle Checkbox</li>
            <li class="mform-fb__pal-item" data-type="checkboxgroup">Checkbox Group</li>
            <li class="mform-fb__pal-item" data-type="tags">Tags</li>
            <li class="mform-fb__pal-item" data-type="hidden">Hidden</li>
            <li class="mform-fb__pal-item" data-type="headline">Headline</li>
            <li class="mform-fb__pal-item" data-type="description">Description</li>
            <li class="mform-fb__pal-item" data-type="alertinfo">Alert Info</li>
            <li class="mform-fb__pal-item" data-type="alertwarning">Alert Warning</li>
            <li class="mform-fb__pal-item" data-type="alertdanger">Alert Danger</li>
            <li class="mform-fb__pal-item" data-type="alertsuccess">Alert Success</li>
            <li class="mform-fb__pal-item" data-type="html">HTML-Block</li>
            <li class="mform-fb__pal-item" data-type="media">Media</li>
            <li class="mform-fb__pal-item" data-type="medialist">Medialist</li>
            <li class="mform-fb__pal-item" data-type="imagelist">Imagelist</li>
            <li class="mform-fb__pal-item" data-type="link">Link</li>
            <li class="mform-fb__pal-item" data-type="linklist">Linklist</li>
            <li class="mform-fb__pal-item" data-type="customlink">Custom Link</li>
            <li class="mform-fb__pal-item" data-type="customlinkmultiple">Custom Link Multiple</li>
            <li class="mform-fb__pal-item" data-type="colorswatch">Color Swatch</li>
            <li class="mform-fb__pal-item" data-type="radioimg">Radio Image</li>
            <li class="mform-fb__pal-item" data-type="radioicon">Radio Icon</li>
            <li class="mform-fb__pal-item" data-type="radiocolor">Radio Color</li>
            <li class="mform-fb__pal-item" data-type="textreadonly">Text (readonly)</li>
            <li class="mform-fb__pal-item" data-type="textareareadonly">Textarea (readonly)</li>
        </ul>
        <h4 style="margin-top:1.5em">Wrapper</h4>
        <ul class="mform-fb__field-list" data-fb-palette-wrap>
            <li class="mform-fb__pal-item mform-fb__pal-item--wrap" data-type="repeater">Flex Repeater</li>
            <li class="mform-fb__pal-item mform-fb__pal-item--wrap" data-type="tab">Tab</li>
            <li class="mform-fb__pal-item mform-fb__pal-item--wrap" data-type="fieldset">Fieldset</li>
            <li class="mform-fb__pal-item mform-fb__pal-item--wrap" data-type="modal">Modal</li>
            <li class="mform-fb__pal-item mform-fb__pal-item--wrap" data-type="collapse" title="Aufklappbarer Bereich mit Toggle-Link">Collapse</li>
            <li class="mform-fb__pal-item mform-fb__pal-item--wrap" data-type="accordion" title="Mehrere aufklappbare Bereiche, nur einer offen: mehrere Accordion-Elemente hintereinander anlegen">Accordion</li>
            <li class="mform-fb__pal-item mform-fb__pal-item--wrap" data-type="column" title="Eine Spalte im 12er-Raster; nebeneinander liegende Spalten bilden eine Zeile">Spalte (Column)</li>
            <li class="mform-fb__pal-item mform-fb__pal-item--wrap" data-type="column" data-preset="6+6" title="Zwei Spalten je halbe Breite">2 Spalten (6 + 6)</li>
            <li class="mform-fb__pal-item mform-fb__pal-item--wrap" data-type="column" data-preset="4+4+4" title="Drei Spalten je ein Drittel">3 Spalten (4 + 4 + 4)</li>
            <li class="mform-fb__pal-item mform-fb__pal-item--wrap" data-type="inline" title="Felder nebeneinander in einer Zeile (form-inline)">Inline</li>
        </ul>
        <p class="mform-fb__palette-empty" data-fb-palette-empty style="display:none">Keine Treffer in der Palette.</p>
        <div class="mform-fb__actions">
            <button type="button" class="btn btn-info btn-block" data-toggle="modal" data-target="#mform-fb-info"><i class="rex-icon fa-info-circle"></i> Hilfe &amp; Hinweise</button>
            <button type="button" class="btn btn-default btn-block" data-fb-action="clear" style="margin-top:6px">Alles loeschen</button>
            <div class="btn-group btn-group-justified" style="margin-top:6px">
                <a class="btn btn-default btn-sm" data-fb-action="export" title="Builder-Stand als JSON herunterladen"><i class="rex-icon fa-download"></i> Export</a>
                <a class="btn btn-default btn-sm" data-fb-action="import" title="Builder-Stand aus JSON laden"><i class="rex-icon fa-upload"></i> Import</a>
            </div>
            <input type="file" accept=".json,application/json" data-fb-import-file style="display:none">
            <p class="mform-fb__copy-msg" data-fb-state-msg style="margin:4px 0 0;min-height:1.2em"></p>
        </div>
    </div>

    <div class="mform-fb__canvas-wrap">
        <h4>Bauflaeche</h4>
        <div class="mform-fb__canvas" data-fb-canvas>
            <p class="mform-fb__hint">Klick links auf ein Feld, um es hier einzufuegen</p>
        </div>

        <div class="alert alert-warning" style="display:none" data-fb-slot-warning></div>

        <div class="mform-fb__sections" data-fb-sections>
            <div class="mform-fb__sections-bar">
                <button type="button" class="btn btn-default btn-xs" data-fb-sections-toggle title="Alle Abschnitte auf- oder zuklappen"><i class="rex-icon fa-regular fa-window-minimize"></i> Alle auf / zu</button>
                <div class="mform-fb__sections-actions">
                    <button type="button" class="btn btn-primary btn-xs" data-fb-action="copy" title="Eingabecode in die Zwischenablage kopieren"><i class="rex-icon fa-clipboard"></i> Eingabecode kopieren</button>
                    <span class="mform-fb__copy-msg" data-fb-copy-msg></span>
                </div>
            </div>

            <section class="mform-fb__section" data-fb-section="preview">
                <h4 class="mform-fb__section-head"><button type="button" class="mform-fb__section-toggle" data-fb-section-toggle aria-expanded="true"><i class="rex-icon fa-chevron-down mform-fb__section-chevron"></i> Vorschau</button></h4>
                <div class="mform-fb__section-body" data-fb-section-body>
                    <div class="mform-fb__code-bar" data-fb-preview-bar data-fb-preview-url="{{PREVIEW_URL}}" data-fb-preview-csrf="{{PREVIEW_CSRF}}">
                        <button type="button" class="btn btn-default btn-xs" data-fb-action="preview"><i class="rex-icon fa-refresh"></i> Vorschau aktualisieren</button>
                        <label class="checkbox-inline" style="margin-left:8px;font-weight:normal"><input type="checkbox" data-fb-preview-auto checked> automatisch</label>
                        <span class="mform-fb__copy-msg" data-fb-preview-msg></span>
                    </div>
                    <iframe class="mform-fb__preview" data-fb-preview title="Vorschau" sandbox="allow-scripts allow-same-origin allow-forms allow-popups" style="width:100%;min-height:160px;border:1px solid var(--mform-border, #dfe3e9);background:var(--mform-surface, #fff)"></iframe>
                </div>
            </section>

            <section class="mform-fb__section" data-fb-section="input">
                <h4 class="mform-fb__section-head"><button type="button" class="mform-fb__section-toggle" data-fb-section-toggle aria-expanded="false"><i class="rex-icon fa-chevron-down mform-fb__section-chevron"></i> Eingabe (Modul-Input)</button></h4>
                <div class="mform-fb__section-body" data-fb-section-body hidden>
                    <div class="mform-fb__code-bar">
                        <button type="button" class="btn btn-primary btn-xs" data-fb-action="copy">Code kopieren</button>
                        <span class="mform-fb__copy-msg" data-fb-copy-msg></span>
                    </div>
                    <textarea class="mform-fb__code" data-fb-code data-mform-code-language="php" readonly spellcheck="false">// Noch keine Felder hinzugefuegt.</textarea>
                </div>
            </section>

            <section class="mform-fb__section" data-fb-section="output">
                <h4 class="mform-fb__section-head"><button type="button" class="mform-fb__section-toggle" data-fb-section-toggle aria-expanded="false"><i class="rex-icon fa-chevron-down mform-fb__section-chevron"></i> Ausgabe (Modul-Output)</button></h4>
                <div class="mform-fb__section-body" data-fb-section-body hidden>
                    <div class="mform-fb__code-bar">
                        <button type="button" class="btn btn-primary btn-xs" data-fb-action="copy-output">Code kopieren</button>
                        <span class="mform-fb__copy-msg" data-fb-copy-output-msg></span>
                    </div>
                    <textarea class="mform-fb__code" data-fb-output data-mform-code-language="php" readonly spellcheck="false">// Noch keine Felder hinzugefuegt.</textarea>
                </div>
            </section>
        </div>
    </div>

    <div class="mform-fb__props" data-fb-props>
        <h4>Eigenschaften</h4>
        <p class="mform-fb__hint" data-fb-props-empty>Klicke ein Feld in der Bauflaeche an.</p>
        <form class="mform-fb__props-form" data-fb-props-form style="display:none">
            <div class="form-group" data-fb-prop-group="label">
                <label>Label</label>
                <input type="text" class="form-control" data-fb-prop="label">
            </div>
            <div class="form-group" data-fb-prop-group="category">
                <label>Kategorie-ID <small>(optional, fuer Media-/Link-Picker)</small></label>
                <input type="number" class="form-control" data-fb-prop="category" min="0">
            </div>
            <div class="form-group" data-fb-prop-group="defaultValue">
                <label>Default-Value</label>
                <input type="text" class="form-control" data-fb-prop="defaultValue">
            </div>
            <div class="form-group" data-fb-prop-group="placeholder">
                <label>Placeholder</label>
                <input type="text" class="form-control" data-fb-prop="placeholder">
            </div>
            <div class="form-group" data-fb-prop-group="notice">
                <label>Hinweistext <small>(notice / Hilfetext unter dem Feld)</small></label>
                <input type="text" class="form-control" data-fb-prop="notice">
            </div>
            <div class="form-group" data-fb-prop-group="cssClass">
                <label>CSS-Klassen</label>
                <input type="text" class="form-control" data-fb-prop="cssClass" placeholder="z. B. mt-3 text-muted">
            </div>
            <div class="form-group" data-fb-prop-group="inputMin">
                <label>Min <small>(min-Attribut)</small></label>
                <input type="text" class="form-control" data-fb-prop="inputMin">
            </div>
            <div class="form-group" data-fb-prop-group="inputMax">
                <label>Max <small>(max-Attribut)</small></label>
                <input type="text" class="form-control" data-fb-prop="inputMax">
            </div>
            <div class="form-group" data-fb-prop-group="inputStep">
                <label>Step <small>(step-Attribut)</small></label>
                <input type="text" class="form-control" data-fb-prop="inputStep">
            </div>
            <div class="form-group" data-fb-prop-group="rows">
                <label>Zeilen <small>(rows fuer Textarea)</small></label>
                <input type="number" class="form-control" data-fb-prop="rows" min="1">
            </div>
            <div class="form-group" data-fb-prop-group="options">
                <label>Optionen <small>(eine pro Zeile, optional <code>key=label</code>)</small></label>
                <textarea class="form-control" rows="5" data-fb-prop="options"></textarea>
            </div>
            <div class="form-group" data-fb-prop-group="colorSwatchHelp">
                <div class="alert alert-info mform-fb__colorswatch-help" style="margin-bottom:8px">
                    <strong>ColorSwatch Hilfe</strong><br>
                    Pro Zeile eine Farbe. Formate:<br>
                    <code>#2f77bc=Blau</code><br>
                    <code>.text-primary = Primaer CSS | #2f77bc</code> <small>(mit Preview-Farbe)</small>
                </div>
                <button type="button" class="btn btn-default btn-xs" data-fb-action="colorswatch-example">Beispiel-Palette einfuegen</button>
            </div>
            <div class="form-group" data-fb-prop-group="radioOptionsHelp">
                <p class="help-block rex-note">Format je Zeile: <code>wert=Label|extra</code>. Extra ist je nach Typ der Bildpfad (Radio Image), die Icon-Klasse (Radio Icon, z. B. <code>fa fa-star</code>) oder die Farbe (Radio Color, z. B. <code>#2f6ea8</code> oder <code>transparent</code>).</p>
            </div>
            <div class="form-group" data-fb-prop-group="a11yAlt">
                <label class="checkbox">
                    <input type="checkbox" data-fb-prop="a11yAlt"> ALT-Text im Medienpool pruefen <small>('a11y' =&gt; ['med_alt'], siehe Doku „Barrierefreiheit“)</small>
                </label>
            </div>
            <div class="form-group" data-fb-prop-group="collapseOpen">
                <label class="checkbox"><input type="checkbox" data-fb-prop="collapseOpen"> Initial geoeffnet <small>(openCollapse)</small></label>
            </div>
            <div class="form-group" data-fb-prop-group="collapseHideToggle">
                <label class="checkbox"><input type="checkbox" data-fb-prop="collapseHideToggle"> Toggle-Link ausblenden <small>(hideToggleLinks)</small></label>
            </div>
            <div class="form-group" data-fb-prop-group="columnSize">
                <label>Spaltenbreite <small>(1 bis 12 von 12)</small></label>
                <input type="number" class="form-control" data-fb-prop="columnSize" min="1" max="12" value="6">
                <p class="help-block" style="margin-top:4px"><small>Nebeneinander liegende Spalten bilden eine Zeile, z. B. 6 + 6 oder 4 + 4 + 4. Spalte anklicken und dann links ein Feld waehlen, oder Felder hineinziehen.</small></p>
            </div>
            <div class="form-group" data-fb-prop-group="alertText">
                <label>Alert-Text</label>
                <textarea class="form-control" rows="4" data-fb-prop="alertText"></textarea>
            </div>
            <div class="form-group" data-fb-prop-group="isMulti">
                <label class="checkbox">
                    <input type="checkbox" data-fb-prop="isMulti"> Mehrfachauswahl <small>(addMultiSelectField)</small>
                </label>
            </div>
            <div class="form-group" data-fb-prop-group="cbgLayout">
                <label>Layout</label>
                <select class="form-control" data-fb-prop="cbgLayout">
                    <option value="horizontal">horizontal</option>
                    <option value="vertical">vertical</option>
                </select>
            </div>
            <div class="form-group" data-fb-prop-group="cbgMode">
                <label>Modus</label>
                <select class="form-control" data-fb-prop="cbgMode">
                    <option value="checkbox">Mehrfachauswahl (checkbox)</option>
                    <option value="radio">Einfachauswahl (radio)</option>
                </select>
            </div>
            <div class="form-group" data-fb-prop-group="tagsHelp">
                <p class="help-block" style="margin:0"><small>Tags: Die Optionen oben sind Vorschlaege, eine je Zeile ohne <code>key=</code>. Gespeichert wird kommasepariert (<code>news,blog</code>).</small></p>
            </div>
            <div class="form-group" data-fb-prop-group="tagsAllowNew">
                <label class="checkbox">
                    <input type="checkbox" data-fb-prop="tagsAllowNew"> Freie Eingabe erlauben <small>(allow_new; aus = nur Vorschlaege)</small>
                </label>
            </div>
            <div class="form-group" data-fb-prop-group="tagsMax">
                <label>Maximale Anzahl <small>(max, 0 = unbegrenzt)</small></label>
                <input type="number" class="form-control" data-fb-prop="tagsMax" min="0">
            </div>
            <div class="form-group" data-fb-prop-group="htmlContent">
                <label>HTML <small>(wird 1:1 in das Formular eingefuegt)</small></label>
                <textarea class="form-control" rows="6" data-fb-prop="htmlContent" placeholder="<hr><p class='text-muted'>Hinweis ...</p>"></textarea>
            </div>
            <div class="form-group" data-fb-prop-group="required">
                <label class="checkbox">
                    <input type="checkbox" data-fb-prop="required"> Required
                </label>
            </div>
            <div class="form-group" data-fb-prop-group="editor">
                <label>Editor</label>
                <select class="form-control" data-fb-prop="editor">
                    <option value="">Kein Editor (Textarea)</option>
                    <option value="tinymce">TinyMCE (tiny-editor)</option>
                    <option value="cke5">CKEditor 5 (cke5-editor)</option>
                    <option value="markdown">MarkdownEditor (markdowneditor-editor)</option>
                </select>
            </div>
            <div class="form-group" data-fb-prop-group="editorProfile">
                <label>Editor-Profil <small>(data-profile, leer = Standard)</small></label>
                {{TINY_PROFILE_FIELD}}
            </div>
            <div class="form-group" data-fb-prop-group="customAttrs">
                <label>Weitere Attribute <small>(eine je Zeile, <code>name=wert</code>)</small></label>
                <textarea class="form-control" rows="3" data-fb-prop="customAttrs" placeholder="maxlength=120&#10;data-foo=bar"></textarea>
                <p class="help-block" style="margin-top:4px"><small>Landen 1:1 im Attribut-Array des Feldes, z. B. <code>maxlength</code>, <code>data-*</code>, <code>autocomplete</code>. <code>class</code> wird mit den CSS-Klassen zusammengefuehrt.</small></p>
            </div>
            <div class="form-group" data-fb-prop-group="full">
                <label class="checkbox">
                    <input type="checkbox" data-fb-prop="full"> setFull()
                </label>
            </div>

            <hr data-fb-prop-group="visibilityEnabled">
            <div class="form-group" data-fb-prop-group="visibilityEnabled">
                <label class="checkbox">
                    <input type="checkbox" data-fb-prop="visibilityEnabled"> Sichtbarkeit an Bedingungen koppeln
                </label>
                <p class="help-block" style="margin-top:4px"><small>Das Feld wird nur angezeigt, wenn die Bedingungen zutreffen. Bei "ist in Liste" mehrere Werte kommasepariert angeben.</small></p>
            </div>
            <div class="form-group" data-fb-prop-group="visibilityConditions">
                <div class="mform-fb__conds" data-fb-conditions></div>
                <div class="mform-fb__cond-actions">
                    <button type="button" class="btn btn-default btn-xs" data-fb-cond-add><i class="rex-icon fa-plus"></i> Bedingung</button>
                    <select class="form-control input-sm mform-fb__cond-logic" data-fb-cond-logic title="Verknuepfung">
                        <option value="all">alle muessen zutreffen</option>
                        <option value="any">eine genuegt</option>
                    </select>
                </div>
            </div>

            <!-- CustomLink: Linktypen -->
            <div class="form-group" data-fb-prop-group="clTypeIntern">
                <label>Linktypen</label>
                <div>
                    <label class="checkbox" style="display:inline-block; margin-right:.75em">
                        <input type="checkbox" data-fb-prop="clTypeIntern"> Intern
                    </label>
                </div>
            </div>
            <div class="form-group" data-fb-prop-group="clTypeExtern">
                <label class="checkbox"><input type="checkbox" data-fb-prop="clTypeExtern"> Extern</label>
            </div>
            <div class="form-group" data-fb-prop-group="clTypeMedia">
                <label class="checkbox"><input type="checkbox" data-fb-prop="clTypeMedia"> Media</label>
            </div>
            <div class="form-group" data-fb-prop-group="clTypeMailto">
                <label class="checkbox"><input type="checkbox" data-fb-prop="clTypeMailto"> Mailto</label>
            </div>
            <div class="form-group" data-fb-prop-group="clTypeTel">
                <label class="checkbox"><input type="checkbox" data-fb-prop="clTypeTel"> Tel</label>
            </div>

            <!-- CustomLink: Beschraenkungen -->
            <div class="form-group" data-fb-prop-group="linkCategory">
                <label>Intern-Link Start-Kategorie <small>(data-link-category)</small></label>
                <input type="number" class="form-control" data-fb-prop="linkCategory" min="0">
            </div>
            <div class="form-group" data-fb-prop-group="mediaCategory">
                <label>Media Start-Kategorie <small>(data-media-category)</small></label>
                <input type="number" class="form-control" data-fb-prop="mediaCategory" min="0">
            </div>
            <div class="form-group" data-fb-prop-group="externPrefix">
                <label>Extern-Link Prefix <small>(data-extern-link-prefix)</small></label>
                <input type="text" class="form-control" data-fb-prop="externPrefix" placeholder="https://www.">
            </div>
            <div class="form-group" data-fb-prop-group="mediaType">
                <label>Media-Typen <small>(data-media-type, z. B. <code>jpg,png,pdf</code>)</small></label>
                <input type="text" class="form-control" data-fb-prop="mediaType" placeholder="jpg,png,pdf">
            </div>

            <div class="form-group" data-fb-prop-group="btnAdd">
                <label>Add-Button-Label <small>(btn_add fuer Multi-Custom-Link)</small></label>
                <input type="text" class="form-control" data-fb-prop="btnAdd" placeholder="Link hinzufuegen">
            </div>

            <div class="form-group" data-fb-prop-group="repeaterMin">
                <label>Min Items</label>
                <input type="number" class="form-control" data-fb-prop="repeaterMin" min="0">
            </div>
            <div class="form-group" data-fb-prop-group="repeaterMax">
                <label>Max Items</label>
                <input type="number" class="form-control" data-fb-prop="repeaterMax" min="0">
            </div>
            <div class="form-group" data-fb-prop-group="repeaterDefaultCount">
                <label>Default Count <small>(automatisch erzeugte Items beim ersten Aufruf)</small></label>
                <input type="number" class="form-control" data-fb-prop="repeaterDefaultCount" min="0">
            </div>
            <div class="form-group" data-fb-prop-group="repeaterCollapsed">
                <label class="checkbox"><input type="checkbox" data-fb-prop="repeaterCollapsed"> Items eingeklappt anzeigen <small>(collapsed)</small></label>
            </div>
            <div class="form-group" data-fb-prop-group="repeaterFirstOpen">
                <label class="checkbox"><input type="checkbox" data-fb-prop="repeaterFirstOpen"> Erstes Item geoeffnet <small>(first_open)</small></label>
            </div>
            <div class="form-group" data-fb-prop-group="repeaterShowToggleAll">
                <label class="checkbox"><input type="checkbox" data-fb-prop="repeaterShowToggleAll"> "Alle ein-/ausklappen"-Button <small>(show_toggle_all)</small></label>
            </div>
            <div class="form-group" data-fb-prop-group="repeaterShowAddButton">
                <label class="checkbox"><input type="checkbox" data-fb-prop="repeaterShowAddButton"> "Hinzufuegen"-Buttons in der Toolbar auch bei vorhandenen Items <small>(show_add_button; aus = nur solange der Repeater leer ist)</small></label>
            </div>
            <div class="form-group" data-fb-prop-group="repeaterOpen">
                <label class="checkbox"><input type="checkbox" data-fb-prop="repeaterOpen"> Neu hinzugefuegtes Item geoeffnet <small>(open)</small></label>
            </div>
            <div class="form-group" data-fb-prop-group="repeaterCopyPaste">
                <label class="checkbox"><input type="checkbox" data-fb-prop="repeaterCopyPaste"> Kopieren/Einfuegen erlauben <small>(copy_paste)</small></label>
            </div>
            <div class="form-group" data-fb-prop-group="repeaterConfirmDelete">
                <label class="checkbox"><input type="checkbox" data-fb-prop="repeaterConfirmDelete"> Loeschen bestaetigen <small>(confirm_delete)</small></label>
            </div>
            <div class="form-group" data-fb-prop-group="repeaterConfirmDeleteMsg">
                <label>Bestaetigungs-Text <small>(confirm_delete_msg)</small></label>
                <input type="text" class="form-control" data-fb-prop="repeaterConfirmDeleteMsg">
            </div>
            <div class="form-group" data-fb-prop-group="repeaterBtnText">
                <label>Add-Button-Text <small>(btn_text)</small></label>
                <input type="text" class="form-control" data-fb-prop="repeaterBtnText" placeholder="Hinzufuegen">
            </div>
            <div class="form-group" data-fb-prop-group="repeaterBtnClass">
                <label>Add-Button-Klasse <small>(btn_class)</small></label>
                <input type="text" class="form-control" data-fb-prop="repeaterBtnClass" placeholder="btn-primary">
            </div>
            <div class="form-group" data-fb-prop-group="tabPullRight">
                <label class="checkbox"><input type="checkbox" data-fb-prop="tabPullRight"> Tab rechts ausrichten <small>(pull right)</small></label>
            </div>
            <div class="form-group" data-fb-prop-group="tabIcon">
                <label>Tab-Icon <small>(tab-icon, z. B. <code>fa-cog</code>)</small></label>
                <input type="text" class="form-control" data-fb-prop="tabIcon" placeholder="fa-cog">
            </div>
            <div class="form-group" data-fb-prop-group="tabStyle">
                <label>Tab-Stil</label>
                <select class="form-control" data-fb-prop="tabStyle">
                    <option value="">Standard</option>
                    <option value="modern">modern</option>
                </select>
            </div>
            <div class="form-group" data-fb-prop-group="tabLayout">
                <label>Tab-Layout</label>
                <select class="form-control" data-fb-prop="tabLayout">
                    <option value="">Standard</option>
                    <option value="vertical">vertical (Navigation links)</option>
                </select>
            </div>
            <div class="form-group" data-fb-prop-group="modalBtnClass">
                <label>Modal-Button-Klasse</label>
                <input type="text" class="form-control" data-fb-prop="modalBtnClass" placeholder="btn-default">
            </div>
            <div class="form-group" data-fb-prop-group="modalAlign">
                <label>Modal-Ausrichtung</label>
                <select class="form-control" data-fb-prop="modalAlign">
                    <option value="left">left</option>
                    <option value="center">center</option>
                    <option value="right">right</option>
                </select>
            </div>
        </form>
    </div>
</div>
HTML;

$body = str_replace(
    ['{{TINY_PROFILE_FIELD}}', '{{PREVIEW_URL}}', '{{PREVIEW_CSRF}}'],
    [$tinyProfileHtml, rex_escape(rex_url::backendController(['rex-api-call' => 'mform_builder_preview'], false)), rex_escape(rex_csrf_token::factory('mform_builder')->getValue())],
    $body,
);

$fragment = new rex_fragment();
$fragment->setVar('title', rex_i18n::msg('mform_formbuilder'), false);
$fragment->setVar('body', $body, false);
echo $fragment->parse('core/page/section.php');

// Info-Modal (Bootstrap 3) -- wird ueber den Hilfe-Button in der Palette geoeffnet.
echo '<div class="modal fade" id="mform-fb-info" tabindex="-1" role="dialog" aria-labelledby="mform-fb-info-title" aria-hidden="true">'
    . '<div class="modal-dialog modal-lg" role="document">'
    . '<div class="modal-content">'
    . '<div class="modal-header">'
    . '<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>'
    . '<h4 class="modal-title" id="mform-fb-info-title">' . rex_i18n::msg('mform_info') . '</h4>'
    . '</div>'
    . '<div class="modal-body">' . $infoModalBody . '</div>'
    . '<div class="modal-footer">'
    . '<button type="button" class="btn btn-default" data-dismiss="modal">Schliessen</button>'
    . '</div>'
    . '</div>'
    . '</div>'
    . '</div>';
