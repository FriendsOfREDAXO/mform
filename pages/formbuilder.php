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

if ([] === $tinyProfiles) {
    $tinyProfileHtml = '<input type="text" class="form-control" data-fb-prop="tinymceProfile" placeholder="default">'
        . '<p class="help-block" style="margin-top:4px"><small>Hinweis: TinyMCE-Addon nicht installiert oder keine Profile vorhanden. Profilname kann manuell eingetragen werden.</small></p>';
} else {
    $tinyProfileHtml = '<select class="form-control" data-fb-prop="tinymceProfile"><option value="">(Standardprofil)</option>';
    foreach ($tinyProfiles as $name) {
        $tinyProfileHtml .= '<option value="' . rex_escape($name) . '">' . rex_escape($name) . '</option>';
    }
    $tinyProfileHtml .= '</select>';
}

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
            <li class="mform-fb__pal-item" data-type="select">Select</li>
            <li class="mform-fb__pal-item" data-type="radio">Radio</li>
            <li class="mform-fb__pal-item" data-type="checkbox">Checkbox</li>
            <li class="mform-fb__pal-item" data-type="togglecheckbox">Toggle Checkbox</li>
            <li class="mform-fb__pal-item" data-type="checkboxgroup">Checkbox Group</li>
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
        </ul>
        <h4 style="margin-top:1.5em">Wrapper</h4>
        <ul class="mform-fb__field-list" data-fb-palette-wrap>
            <li class="mform-fb__pal-item mform-fb__pal-item--wrap" data-type="repeater">Flex Repeater</li>
            <li class="mform-fb__pal-item mform-fb__pal-item--wrap" data-type="tab">Tab</li>
            <li class="mform-fb__pal-item mform-fb__pal-item--wrap" data-type="fieldset">Fieldset</li>
            <li class="mform-fb__pal-item mform-fb__pal-item--wrap" data-type="modal">Modal</li>
        </ul>
        <p class="mform-fb__palette-empty" data-fb-palette-empty style="display:none">Keine Treffer in der Palette.</p>
        <div class="mform-fb__actions">
            <button type="button" class="btn btn-info btn-block" data-toggle="modal" data-target="#mform-fb-info"><i class="rex-icon fa-info-circle"></i> Hilfe &amp; Hinweise</button>
            <button type="button" class="btn btn-default btn-block" data-fb-action="clear" style="margin-top:6px">Alles loeschen</button>
        </div>
    </div>

    <div class="mform-fb__canvas-wrap">
        <h4>Bauflaeche</h4>
        <div class="mform-fb__canvas" data-fb-canvas>
            <p class="mform-fb__hint">Klick links auf ein Feld, um es hier einzufuegen</p>
        </div>

        <h4 style="margin-top:1.5em">Eingabe (Modul-Input)</h4>
        <div class="alert alert-warning" style="display:none" data-fb-slot-warning></div>
        <div class="mform-fb__code-bar">
            <button type="button" class="btn btn-primary btn-xs" data-fb-action="copy">Code kopieren</button>
            <span class="mform-fb__copy-msg" data-fb-copy-msg></span>
        </div>
        <textarea class="mform-fb__code" data-fb-code data-mform-code-language="php" readonly spellcheck="false">// Noch keine Felder hinzugefuegt.</textarea>

        <h4 style="margin-top:1.5em">Ausgabe (Modul-Output)</h4>
        <div class="mform-fb__code-bar">
            <button type="button" class="btn btn-primary btn-xs" data-fb-action="copy-output">Code kopieren</button>
            <span class="mform-fb__copy-msg" data-fb-copy-output-msg></span>
        </div>
        <textarea class="mform-fb__code" data-fb-output data-mform-code-language="php" readonly spellcheck="false">// Noch keine Felder hinzugefuegt.</textarea>
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
            <div class="form-group" data-fb-prop-group="htmlContent">
                <label>HTML <small>(wird 1:1 in das Formular eingefuegt)</small></label>
                <textarea class="form-control" rows="6" data-fb-prop="htmlContent" placeholder="<hr><p class='text-muted'>Hinweis ...</p>"></textarea>
            </div>
            <div class="form-group" data-fb-prop-group="required">
                <label class="checkbox">
                    <input type="checkbox" data-fb-prop="required"> Required
                </label>
            </div>
            <div class="form-group" data-fb-prop-group="tinymce">
                <label class="checkbox">
                    <input type="checkbox" data-fb-prop="tinymce"> TinyMCE-Editor
                </label>
            </div>
            <div class="form-group" data-fb-prop-group="tinymceProfile">
                <label>TinyMCE-Profil <small>(data-profile)</small></label>
                {{TINY_PROFILE_FIELD}}
            </div>
            <div class="form-group" data-fb-prop-group="full">
                <label class="checkbox">
                    <input type="checkbox" data-fb-prop="full"> setFull()
                </label>
            </div>

            <hr data-fb-prop-group="visibilityEnabled">
            <div class="form-group" data-fb-prop-group="visibilityEnabled">
                <label class="checkbox">
                    <input type="checkbox" data-fb-prop="visibilityEnabled"> Sichtbarkeit an Bedingung koppeln
                </label>
                <p class="help-block" style="margin-top:4px"><small>Das Feld wird nur angezeigt, wenn das gewaehlte Quellfeld die Bedingung erfuellt.</small></p>
            </div>
            <div class="form-group" data-fb-prop-group="visibilitySourceUid">
                <label>Quellfeld</label>
                <select class="form-control" data-fb-prop="visibilitySourceUid">
                    <option value="">Bitte waehlen</option>
                </select>
            </div>
            <div class="form-group" data-fb-prop-group="visibilityOperator">
                <label>Operator</label>
                <select class="form-control" data-fb-prop="visibilityOperator">
                    <option value="eq">ist gleich</option>
                    <option value="neq">ist ungleich</option>
                    <option value="contains">enthaelt</option>
                    <option value="in">ist in Liste</option>
                    <option value="gt">ist groesser als</option>
                    <option value="lt">ist kleiner als</option>
                    <option value="empty">ist leer</option>
                    <option value="not_empty">ist nicht leer</option>
                </select>
            </div>
            <div class="form-group" data-fb-prop-group="visibilityValue">
                <label>Vergleichswert</label>
                <input type="text" class="form-control" data-fb-prop="visibilityValue" placeholder="z. B. 1 oder image">
                <p class="help-block" style="margin-top:4px"><small>Bei "ist in Liste" mehrere Werte kommasepariert angeben.</small></p>
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

$body = str_replace('{{TINY_PROFILE_FIELD}}', $tinyProfileHtml, $body);

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
