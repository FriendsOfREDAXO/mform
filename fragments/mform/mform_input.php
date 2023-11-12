<?php
/** @var rex_fragment $this */

switch ($this->getVar('type')) {
    case 'text':
        break;
    case 'radio':
        if (str_contains($this->getVar('attributes'), ':id=')) {
            if (preg_match('/:id="([^"]+)"/', $this->getVar('attributes'), $matches)) {
                // Der Wert von ":id" befindet sich im ersten Element von $matches
                $this->setVar('attributes', $this->getVar('attributes') . ' :name="\'REX_INPUT_VALUE[\'+'.$matches[1].'+\']\'"');
            }
        }
        $this->setVar('wrapperOpen', '<div class="radio"><label class="description">');
        $this->setVar('wrapperClose', $this->getVar('label') . '</label></div>');
        break;
    case 'checkbox':
        $this->setVar('wrapperOpen', '<div class="checkbox"><label class="description">');
        $this->setVar('wrapperClose', ' ' . $this->getVar('label') . '</label></div>');
        break;
}

if ($this->getVar('type') == 'datalist') {
    echo '<datalist id="' . $this->getVar('id') . '">' . $this->getVar('options') . '</datalist>';
} else if ($this->getVar('type') == 'datalist-option') {
    echo '<option ' . $this->getVar('attributes') . '>' . $this->getVar('value') . '</option>';
} else {
    echo (property_exists($this, 'wrapperOpen') ? $this->getVar('wrapperOpen') : '') . '<input id="' . $this->getVar('id') . '" type="' . $this->getVar('type') . '" name="REX_INPUT_VALUE' . $this->getVar('varId') . '" value="' . $this->getVar('value') . '" class="' . $this->getVar('class') . '" ' . $this->getVar('attributes') . '>' . $this->getVar('datalist') . $this->getVar('wrapperClose', '');
}