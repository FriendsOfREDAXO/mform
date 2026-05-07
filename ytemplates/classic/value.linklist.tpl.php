<?php

$buttonId = $counter;
$name = $this->getFieldName();
$value = htmlspecialchars((string) $this->getValue());

$parameters = [];
if ('' !== (string) $this->getElement('category')) {
    $parameters['category'] = $this->getElement('category');
}

$toolbar = trim((string) $this->getElement('toolbar'));
if ($toolbar !== '') {
    $parameters['toolbar'] = $toolbar;
}

$widget = rex_var_custom_linklist::getWidget($buttonId, $name, $value, $parameters);

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
<div class="<?= $classGroup ?>" id="<?= $this->getHTMLId() ?>">
    <label class="text <?= $this->getWarningClass() ?>" for="<?= $this->getFieldId() ?>"><?= $this->getLabel() ?></label>
    <?= $widget ?>
    <?= $notice ?>
    <div class="rex-clearer"></div>
</div>