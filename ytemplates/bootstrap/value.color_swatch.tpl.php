<?php

/**
 * @var rex_yform_value_color_swatch $this
 * @var array<string, string|array{label: string, preview?: string}> $swatches
 * @psalm-scope-this rex_yform_value_color_swatch
 */

$value   = rex_escape($this->getValue() ?? '');
$fieldId = $this->getFieldId();
$fieldName = $this->getFieldName();

// Vorschau-Farbe für aktuellen Wert ermitteln
$previewColor = '';
$previewClass = '';
if ('' !== $value) {
    if (str_starts_with($value, '.')) {
        $previewClass = ' mform-cs-preview--class';
        // Nach passender preview-Farbe in Swatches suchen
        foreach ($swatches as $key => $config) {
            if ((string) $key === $value && is_array($config) && isset($config['preview'])) {
                $previewClass .= ' mform-cs-preview--class-color';
                $previewColor = rex_escape($config['preview']);
                break;
            }
        }
    } else {
        $previewColor = $value;
    }
}

$notice = [];
if ('' !== (string) $this->getElement('notice')) {
    $notice[] = rex_i18n::translate($this->getElement('notice'), false);
}
if (isset($this->params['warning_messages'][$this->getId()]) && !$this->params['hide_field_warning_messages']) {
    $notice[] = '<span class="text-warning">' . rex_i18n::translate($this->params['warning_messages'][$this->getId()]) . '</span>';
}
$noticeHtml = '' !== implode('', $notice)
    ? '<p class="help-block small">' . implode('<br />', $notice) . '</p>'
    : '';

$class_group = 'form-group ' . $this->getHTMLClass();
if (!empty($this->getWarningClass())) {
    $class_group .= ' ' . $this->getWarningClass();
}

// Swatches-HTML aufbauen
$swatchesHtml = '';
foreach ($swatches as $key => $config) {
    $key = (string) $key;
    if (is_array($config)) {
        $label       = rex_escape($config['label'] ?? $key);
        $swatchPreview = rex_escape($config['preview'] ?? '');
    } else {
        $label       = rex_escape((string) $config);
        $swatchPreview = '';
    }

    $isCssClass = str_starts_with($key, '.');
    $keyEsc = rex_escape($key);

    if ($isCssClass) {
        $style = '' !== $swatchPreview
            ? ' style="background:' . $swatchPreview . '"'
            : '';
        $dataPreview = '' !== $swatchPreview ? ' data-preview-color="' . $swatchPreview . '"' : '';
        $swatchClass = 'mform-cs-swatch mform-cs-swatch--class';
        $swatchesHtml .= '<button type="button" class="' . $swatchClass . '" data-value="' . $keyEsc . '"'
            . $dataPreview . ' title="' . $label . '"'
            . $style . '>'
            . ($swatchPreview === '' ? substr(ltrim($key, '.'), 0, 2) : '')
            . '</button>';
    } else {
        $swatchesHtml .= '<button type="button" class="mform-cs-swatch" data-value="' . $keyEsc . '"'
            . ' style="background:' . $keyEsc . '" title="' . $label . '"></button>';
    }
}

$previewStyle = '' !== $previewColor ? ' style="background:' . $previewColor . '"' : '';
$previewCssClass = 'mform-cs-preview' . $previewClass;

?>
<div class="<?= trim($class_group) ?>" id="<?= $this->getHTMLId() ?>">
    <label class="control-label" for="<?= $fieldId ?>"><?= $this->getLabel() ?></label>
    <div class="mform-color-swatch">
        <span class="<?= $previewCssClass ?>"<?= $previewStyle ?>></span>
        <input type="text"
               class="form-control"
               id="<?= $fieldId ?>"
               name="<?= $fieldName ?>"
               value="<?= $value ?>"
               autocomplete="off" />
        <button type="button" class="mform-cs-toggle btn btn-default" title="Farbe wählen">
            <i class="rex-icon fa-tint"></i>
        </button>
        <div class="mform-cs-popup">
            <?= $swatchesHtml ?>
        </div>
    </div>
    <?= $noticeHtml ?>
</div>
