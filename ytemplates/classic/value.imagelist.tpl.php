<?php

$buttonId = $counter;
$name = $this->getFieldName();
$value = htmlspecialchars($this->getValue());

$widget_params = [];
$widget_params['category'] = 0;
if ('' != $this->getElement('category')) {
    $widget_params['category'] = (int) $this->getElement('category');
}
$widget_params['preview'] = $this->getElement('preview');
if ('' != $this->getElement('types')) {
    $widget_params['types'] = trim($this->getElement('types'));
}

$widget = rex_var_imglist::getWidget($buttonId, $name, $value, $widget_params);

$class_group = trim('form-group ' . $this->getHTMLClass() . ' ' . $this->getWarningClass());

$notice = [];
if ('' != $this->getElement('notice')) {
    $notice[] = rex_i18n::translate($this->getElement('notice'), false);
}
if (isset($this->params['warning_messages'][$this->getId()]) && !$this->params['hide_field_warning_messages']) {
    $notice[] = '<span class="text-warning">' . rex_i18n::translate($this->params['warning_messages'][$this->getId()], false) . '</span>'; //    var_dump();
}
if (count($notice) > 0) {
    $notice = '<p class="help-block">' . implode('<br />', $notice) . '</p>';
} else {
    $notice = '';
}

?>
<div data-be-media-wrapper="<?= $this->getFieldName() ?>" class="<?= $class_group ?>" id="<?= $this->getHTMLId() ?>">
    <label class="control-label" for="<?= $this->getFieldId() ?>"><?= $this->getLabel() ?></label>
    <?= $widget ?>
    <?= $notice ?>
</div>
