<?php
/** @var rex_fragment $this */

switch ($this->getVar('type')) {
    default:
    case 'default':
        $this->setVar('labelColClass', 'col-sm-2 control-label');
        $this->setVar('formItemColClass', 'col-sm-10');
        break;
    case 'default_full':
    case 'default_custom_full':
        $this->setVar('labelColClass', 'col-sm-12');
        $this->setVar('formItemColClass', 'col-sm-12');
        break;
}

$labelWrapper = '<div class="' . $this->getVar('labelColClass') . '">';
$labelWrapperEnd = '</div>';
$inputWrapper = '<div class="' . $this->getVar('formItemColClass') . '">';
$inputWrapperEnd = '</div>';

if ($this->getVar('inline')) {
    $labelWrapper = '';
    $labelWrapperEnd = '';
    $inputWrapper = '';
    $inputWrapperEnd = '';
}

echo '<div class="form-group ' . $this->getVar('class') . '">' . $labelWrapper . $this->getVar('label') . $this->getVar('infoTooltip') . $this->getVar('infoCollapseButton') . $labelWrapperEnd . $inputWrapper . $this->getVar('element') . $inputWrapperEnd . '</div>' . $this->getVar('infoCollapse');