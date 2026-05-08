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
    . '</p>';

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
            <li class="mform-fb__pal-item" data-type="hidden">Hidden</li>
            <li class="mform-fb__pal-item" data-type="headline">Headline</li>
            <li class="mform-fb__pal-item" data-type="description">Description</li>
        </ul>
        <h4 style="margin-top:1.5em">Wrapper</h4>
        <ul class="mform-fb__field-list" data-fb-palette-wrap>
            <li class="mform-fb__pal-item mform-fb__pal-item--wrap" data-type="repeater">Flex Repeater</li>
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

        <h4 style="margin-top:1.5em">PHP-Code</h4>
        <div class="mform-fb__code-bar">
            <button type="button" class="btn btn-primary btn-xs" data-fb-action="copy">Code kopieren</button>
            <span class="mform-fb__copy-msg" data-fb-copy-msg></span>
        </div>
        <pre class="mform-fb__code"><code data-fb-code>// Noch keine Felder hinzugefuegt.</code></pre>
    </div>

    <div class="mform-fb__props" data-fb-props>
        <h4>Eigenschaften</h4>
        <p class="mform-fb__hint" data-fb-props-empty>Klicke ein Feld in der Bauflaeche an.</p>
        <form class="mform-fb__props-form" data-fb-props-form style="display:none">
            <div class="form-group">
                <label>Label</label>
                <input type="text" class="form-control" data-fb-prop="label">
            </div>
            <div class="form-group" data-fb-prop-group="defaultValue">
                <label>Default-Value</label>
                <input type="text" class="form-control" data-fb-prop="defaultValue">
            </div>
            <div class="form-group" data-fb-prop-group="placeholder">
                <label>Placeholder</label>
                <input type="text" class="form-control" data-fb-prop="placeholder">
            </div>
            <div class="form-group" data-fb-prop-group="options">
                <label>Optionen <small>(eine pro Zeile, optional <code>key=label</code>)</small></label>
                <textarea class="form-control" rows="5" data-fb-prop="options"></textarea>
            </div>
            <div class="form-group" data-fb-prop-group="required">
                <label class="checkbox">
                    <input type="checkbox" data-fb-prop="required"> Required
                </label>
            </div>
            <div class="form-group" data-fb-prop-group="tinymce">
                <label class="checkbox">
                    <input type="checkbox" data-fb-prop="tinymce"> TinyMCE-Editor (class="tinymce-editor")
                </label>
            </div>
            <div class="form-group" data-fb-prop-group="full">
                <label class="checkbox">
                    <input type="checkbox" data-fb-prop="full"> setFull()
                </label>
            </div>
            <div class="form-group" data-fb-prop-group="repeaterMin">
                <label>Min Items</label>
                <input type="number" class="form-control" data-fb-prop="repeaterMin" min="0">
            </div>
            <div class="form-group" data-fb-prop-group="repeaterMax">
                <label>Max Items</label>
                <input type="number" class="form-control" data-fb-prop="repeaterMax" min="0">
            </div>
        </form>
    </div>
</div>
HTML;

$fragment = new rex_fragment();
$fragment->setVar('title', rex_i18n::msg('mform_formbuilder'), false);
$fragment->setVar('body', $body, false);
echo $fragment->parse('core/page/section.php');
