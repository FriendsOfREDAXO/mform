<?php
/** @var rex_fragment $this */

$wrapperOpen = '';
$wrapperClose = '';

switch ($this->getVar('type')) {
    case 'text':
        break;
    case 'radio':
        if (str_contains($this->getVar('attributes'), ':id=')) {
            if (preg_match('/:id="([^"]+)"/', $this->getVar('attributes'), $matches)) {
                // Der Wert von ":id" befindet sich im ersten Element von $matches
                $this->setVar('attributes', $this->getVar('attributes') . ' :name="\'REX_INPUT_VALUE[\'+'.$matches[1].'+\']\'"', false);
            }
        }
        $wrapperOpen = '<div class="radio"><label class="description">';
        $wrapperClose = $this->getVar('label') . '</label></div>';
        break;
    case 'checkbox':
        if (str_contains($this->getVar('attributes'), 'data-mform-toggle')) {
            $wrapperOpen = '<div class="checkbox mform-toggle-checkbox"><label class="description">';
        } else {
            $wrapperOpen = '<div class="checkbox"><label class="description">';
        }
        $wrapperClose = ' ' . $this->getVar('label') . '</label></div>';
        break;
}

if ($this->getVar('type') == 'datalist') {
    echo '<datalist id="' . $this->getVar('id') . '">' . $this->getVar('options') . '</datalist>';
} else if ($this->getVar('type') == 'datalist-option') {
    echo '<option ' . $this->getVar('attributes') . '>' . $this->getVar('value') . '</option>';
} else {
    echo $wrapperOpen . '<input id="' . $this->getVar('id') . '" type="' . $this->getVar('type') . '" name="REX_INPUT_VALUE' . $this->getVar('varId') . '" value="' . $this->getVar('value') . '" class="' . $this->getVar('class') . '" ' . $this->getVar('attributes') . '>' . $this->getVar('datalist') . $wrapperClose;
}