<?php
/** @var rex_fragment $this */

switch ($this->getVar('type')) {
    case 'multiselect';
        $this->setVar('attributes', $this->getVar('attributes') . ' multiple');
    case 'select';
        foreach (rex_view::getJsFiles() as $file) {
            if (str_contains($file, 'bootstrap-select')
                && !str_contains($this->getVar('class'), 'selectpicker')
                && !str_contains($this->getVar('class'), 'none-selectpicker')) {
                $this->setVar('class', $this->getVar('class') . ' selectpicker');
            }
        }
        echo $this->getVar('javascript') . '<select id="' . $this->getVar('id') . '" name="REX_INPUT_VALUE' . $this->getVar('varId') . '" class="' . $this->getVar('class') . '" ' . $this->getVar('attributes') . ' data-selected="' . $this->getVar('value') . '">' . $this->getVar('options') . '</select>' . $this->getVar('hidden');
        break;
    case 'option':
        echo '<option value="' . $this->getVar('value') . '"' . $this->getVar('attributes') . '>' . $this->getVar('label') . '</option>';
        break;
    case 'optgroup':
        echo '<optgroup label="' . $this->getVar('label') . '">' . $this->getVar('options') . '</optgroup>';
        break;
}