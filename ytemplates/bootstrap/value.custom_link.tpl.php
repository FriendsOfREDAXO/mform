<?php

$buttonId = $counter;
$categoryId = 0;
$name = $this->getFieldName();
$value = htmlspecialchars($this->getValue());
$parameters = [
    'media' => (1 == $this->getElement('media')),
    'mailto' => (1 == $this->getElement('mailto')),
    'external' => (1 == $this->getElement('external')),
    'intern' => (1 == $this->getElement('intern')),
    'phone' => (1 == $this->getElement('phone')),
    'types' => $this->getElement('types'),
    'category' => $this->getElement('category'),
    'media_category' => $this->getElement('media_category'),
    'ylink' => $this->getElement('ylink'),
];

$widget = rex_var_custom_link::getWidget($buttonId, $name, $value, $parameters);

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
<div class="<?= $class_group ?>" id="<?= $this->getHTMLId() ?>">
    <label class="control-label" for="<?= $this->getFieldId() ?>"><?= $this->getLabel() ?></label>
    <?= $widget ?>
    <?= $notice ?>
</div>
