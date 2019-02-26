<?php

$buttonId = $counter;
$categoryId = 0;
$name = $this->getFieldName();
$value = htmlspecialchars($this->getValue());

if ($this->getElement('multiple') == 1) {
    $widget = rex_var_linklist::getWidget($buttonId, $name, $value, []);
} else {
    $widget = rex_var_custom_link::getWidget($buttonId, $name, $value, []);
}

$class_group = trim('form-group ' . $this->getHTMLClass() . ' ' . $this->getWarningClass());

$notice = [];
if ($this->getElement('notice') != '') {
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
<div class="<?php echo $class_group ?>" id="<?php echo $this->getHTMLId() ?>">
    <label class="control-label" for="<?php echo $this->getFieldId() ?>"><?php echo $this->getLabel() ?></label>
    <?php echo $widget; ?>
    <?php echo $notice ?>
</div>
