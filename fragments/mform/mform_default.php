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

$labelWrapperOpen = '<div class="' . $this->getVar('labelColClass') . '">';
$labelWrapperClose = '</div>';
$inputWrapperOpen = '<div class="' . $this->getVar('formItemColClass') . '">';
$inputWrapperClose = '</div>';

if ($this->getVar('inline')) {
    $labelWrapperOpen = '';
    $labelWrapperClose = '';
    $inputWrapperOpen = '';
    $inputWrapperClose = '';
}

echo '<div class="form-group ' . $this->getVar('class') . '">' . $labelWrapperOpen . $this->getVar('label') . $this->getVar('infoTooltip') . $this->getVar('infoCollapseButton') . $labelWrapperClose . $inputWrapperOpen . $this->getVar('element') . $inputWrapperClose . '</div>' . $this->getVar('infoCollapse');