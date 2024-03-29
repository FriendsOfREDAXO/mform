<?php
$labelColClass = $formItemColClass = ''; 
switch ($this->type) {
    case 'default':
        $labelColClass = 'col-sm-2 control-label';
        $formItemColClass = 'col-sm-10';
        break;
    case 'default_full':
    case 'default_custom_full':
        $labelColClass = 'col-sm-12';
        $formItemColClass = 'col-sm-12';
        break;
}

$labelWrapper = '<div class="' . $labelColClass . '">';
$labelWrapperEnd = '</div>';
$inputWrapper = '<div class="' . $formItemColClass . '">';
$inputWrapperEnd = '</div>';

if (true === $this->inline) {
    $labelWrapper = '';
    $labelWrapperEnd = '';
    $inputWrapper = '';
    $inputWrapperEnd = '';
}

echo '<div class="form-group ' . $this->class . '">' . $labelWrapper . $this->label . $this->infoTooltip . $this->infoCollapseButton . $labelWrapperEnd . $inputWrapper . $this->element . $inputWrapperEnd . '</div>' . $this->infoCollapse;
