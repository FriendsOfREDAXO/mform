<?php

/**
 * @var rex_yform_value_medialist $this
 * @psalm-scope-this rex_yform_value_medialist
 */

$buttonId = isset($counter) ? (int) $counter : 0;
$name = $this->getFieldName();
$value = htmlspecialchars((string) $this->getValue());

$widgetParams = [];
if ('' !== (string) $this->getElement('category')) {
    $widgetParams['category'] = (int) $this->getElement('category');
}

$types = trim((string) $this->getElement('types'));
if ($types !== '' && $types !== '*') {
    $widgetParams['types'] = $types;
}

$view = trim((string) $this->getElement('view'));
if ($view !== '') {
    $widgetParams['view'] = $view;
}

$views = trim((string) $this->getElement('views'));
if ($views !== '') {
    $widgetParams['views'] = $views;
}

if ((string) $this->getElement('view_switch') !== '') {
    $widgetParams['view_switch'] = 1 == $this->getElement('view_switch');
}

$toolbar = trim((string) $this->getElement('toolbar'));
if ($toolbar !== '') {
    $widgetParams['toolbar'] = $toolbar;
}

if ((string) $this->getElement('hide_label') !== '') {
    $widgetParams['hide_label'] = 1 == $this->getElement('hide_label');
}

$widget = rex_var_custom_medialist::getWidget($buttonId, $name, $value, $widgetParams);

$classGroup = trim('form-group ' . $this->getHTMLClass() . ' ' . $this->getWarningClass());

$notice = [];
if ('' != $this->getElement('notice')) {
    $notice[] = rex_i18n::translate($this->getElement('notice'), false);
}
if (isset($this->params['warning_messages'][$this->getId()]) && !$this->params['hide_field_warning_messages']) {
    $notice[] = '<span class="text-warning">' . rex_i18n::translate($this->params['warning_messages'][$this->getId()], false) . '</span>';
}
if (count($notice) > 0) {
    $notice = '<p class="help-block">' . implode('<br />', $notice) . '</p>';
} else {
    $notice = '';
}

?>
<div data-be-media-wrapper="<?= $this->getFieldName() ?>" class="<?= $classGroup ?>" id="<?= $this->getHTMLId() ?>">
    <label class="control-label" for="<?= $this->getFieldId() ?>"><?= $this->getLabel() ?></label>
    <?= $widget ?>
    <?= $notice ?>
</div>