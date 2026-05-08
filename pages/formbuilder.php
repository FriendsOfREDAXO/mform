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

$intro = '<p>'
    . 'Klicke ein Feld in der linken Palette an um es einzufuegen. Reihenfolge per Drag&Drop am Griff aendern. '
    . 'Per Klick auf ein eingefuegtes Feld oeffnet sich rechts der Eigenschaftsdialog. '
    . 'Im unteren Bereich entsteht in Echtzeit der MForm-PHP-Code zum Kopieren.'
    . '</p>'
    . '<div class="alert alert-info" style="margin-top:1em">'
    . '<strong>Hinweis:</strong> Der Form Builder bietet bewusst nur eine kuratierte Auswahl der haeufigsten MForm-Felder, '
    . 'um einen einfachen Einstieg zu ermoeglichen. Spezielle Felder wie <code>addRadioImgField</code>, '
    . '<code>addColorSwatchField</code>, <code>addToggleCheckboxField</code>, <code>addInputField</code> mit eigenen Typen, '
    . 'Layouts wie Column/Collapse/Modal oder Conditional Fieldsets sind weiterhin '
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

$fragment = new rex_fragment();
$fragment->setVar('title', rex_i18n::msg('mform_info'), false);
$fragment->setVar('body', $intro, false);
echo $fragment->parse('core/page/section.php');

$body = <<<'HTML'
<div id="mform-fb" class="mform-fb">
    <div class="mform-fb__palette">
        <h4>Felder</h4>
        <ul class="mform-fb__field-list" data-fb-palette>
            <li class="mform-fb__pal-item" data-type="text">Text</li>
            <li class="mform-fb__pal-item" data-type="textarea">Textarea</li>
            <li class="mform-fb__pal-item" data-type="select">Select</li>
            <li class="mform-fb__pal-item" data-type="radio">Radio</li>
            <li class="mform-fb__pal-item" data-type="checkbox">Checkbox</li>
            <li class="mform-fb__pal-item" data-type="checkboxgroup">Checkbox Group</li>
            <li class="mform-fb__pal-item" data-type="hidden">Hidden</li>
            <li class="mform-fb__pal-item" data-type="headline">Headline</li>
            <li class="mform-fb__pal-item" data-type="description">Description</li>
            <li class="mform-fb__pal-item" data-type="html">HTML-Block</li>
            <li class="mform-fb__pal-item" data-type="media">Media</li>
            <li class="mform-fb__pal-item" data-type="medialist">Medialist</li>
            <li class="mform-fb__pal-item" data-type="imagelist">Imagelist</li>
            <li class="mform-fb__pal-item" data-type="link">Link</li>
            <li class="mform-fb__pal-item" data-type="linklist">Linklist</li>
            <li class="mform-fb__pal-item" data-type="customlink">Custom Link</li>
            <li class="mform-fb__pal-item" data-type="customlinkmultiple">Custom Link Multiple</li>
        </ul>
        <h4 style="margin-top:1.5em">Wrapper</h4>
        <ul class="mform-fb__field-list" data-fb-palette-wrap>
            <li class="mform-fb__pal-item mform-fb__pal-item--wrap" data-type="repeater">Flex Repeater</li>
            <li class="mform-fb__pal-item mform-fb__pal-item--wrap" data-type="tab">Tab</li>
            <li class="mform-fb__pal-item mform-fb__pal-item--wrap" data-type="fieldset">Fieldset</li>
        </ul>
        <div class="mform-fb__actions">
            <button type="button" class="btn btn-default" data-fb-action="clear">Alles loeschen</button>
        </div>
    </div>

    <div class="mform-fb__canvas-wrap">
        <h4>Bauflaeche</h4>
        <div class="mform-fb__canvas" data-fb-canvas>
            <p class="mform-fb__hint">Klick links auf ein Feld, um es hier einzufuegen</p>
        </div>

        <h4 style="margin-top:1.5em">Eingabe (Modul-Input)</h4>
        <div class="mform-fb__code-bar">
            <button type="button" class="btn btn-primary btn-xs" data-fb-action="copy">Code kopieren</button>
            <span class="mform-fb__copy-msg" data-fb-copy-msg></span>
        </div>
        <pre class="mform-fb__code"><code data-fb-code>// Noch keine Felder hinzugefuegt.</code></pre>

        <h4 style="margin-top:1.5em">Ausgabe (Modul-Output)</h4>
        <div class="mform-fb__code-bar">
            <button type="button" class="btn btn-primary btn-xs" data-fb-action="copy-output">Code kopieren</button>
            <span class="mform-fb__copy-msg" data-fb-copy-output-msg></span>
        </div>
        <pre class="mform-fb__code"><code data-fb-output>// Noch keine Felder hinzugefuegt.</code></pre>
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
        </form>
    </div>
</div>
HTML;

$body = str_replace('{{TINY_PROFILE_FIELD}}', $tinyProfileHtml, $body);

$fragment = new rex_fragment();
$fragment->setVar('title', rex_i18n::msg('mform_formbuilder'), false);
$fragment->setVar('body', $body, false);
echo $fragment->parse('core/page/section.php');
