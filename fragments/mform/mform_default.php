<?php

/** @var rex_fragment $this */

switch ($this->getVar('type')) {
    default:
    case 'default':
        $this->setVar('labelColClass', 'col-sm-2 control-label');
        $this->setVar('formItemColClass', 'col-sm-10');
        break;
    case 'default_custom':
        /* $this->getVar('labelColClass') and $this->getVar('formItemColClass') already have the correct values from the call. */
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
$notice = $this->getVar('notice') ? '<p class="help-block small">' . $this->getVar('notice') . '</p>' : '';
$inputWrapperClose = ($this->getVar('notCloseInputWrapper')) ? '' : ($notice . '</div>');
$formGroupClose = ($this->getVar('notClosedFormGroup')) ? '' : '</div>';

echo '<div class="form-group ' . $this->getVar('class') . '" ' . $this->getVar('formGroupAttributes') . '>' . $labelWrapperOpen . $this->getVar('label') . $this->getVar('infoTooltip') . $this->getVar('infoCollapseButton') . $labelWrapperClose . $inputWrapperOpen . $this->getVar('element') . $inputWrapperClose . $formGroupClose . $this->getVar('infoCollapse');
