<?php

$buttonId = $counter;
$categoryId = 0;
$name = $this->getFieldName();
$value = htmlspecialchars($this->getValue());
$parameters = array(
    'media' => ($this->getElement('media') == 1),
    'mailto' => ($this->getElement('mailto') == 1),
    'external' => ($this->getElement('extern') == 1),
    'intern' => ($this->getElement('intern') == 1),
    'phone' => ($this->getElement('phone') == 1),
    'types' => $this->getElement('types'),
    'category' => $this->getElement('category'),
    'media_category' => $this->getElement('media_category'),
);

$widget = rex_var_custom_link::getWidget($buttonId, $name, $value, $parameters);

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
