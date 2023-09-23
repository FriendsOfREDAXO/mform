<?php

switch ($this->type) {
    case 'multiselect':
        $this->attributes .= ' multiple';
        // no break
    case 'select':
        foreach (rex_view::getJsFiles() as $file) {
            if (str_contains($file, 'bootstrap-select')
                && !str_contains($this->class, 'selectpicker')
                && !str_contains($this->class, 'none-selectpicker')) {
                $this->class .= ' selectpicker';
            }
        }
        echo $this->javascript . '<select id="' . $this->id . '" name="REX_INPUT_VALUE' . $this->varId . '" class="' . $this->class . '" ' . $this->attributes . ' data-selected="' . $this->value . '">' . $this->options . '</select>' . $this->hidden;
        break;
    case 'option':
        echo '<option value="' . $this->value . '"' . $this->attributes . '>' . $this->label . '</option>';
        break;
    case 'optgroup':
        echo '<optgroup label="' . $this->label . '">' . $this->options . '</optgroup>';
        break;
}
