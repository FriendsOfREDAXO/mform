<?php

switch ($this->type) {
    case 'select';
        echo $this->javascript . '<select id="' . $this->id . '" name="REX_INPUT_VALUE' . $this->varId . '" class="' . $this->class . '" ' . $this->attributes . ' data-selected="' . $this->value . '">' . $this->options . '</select>' . $this->hidden;
        break;
    case 'option':
        echo '<option value="' . $this->value . '"' . $this->attributes . '>' . $this->label . '</option>';
        break;
    case 'optgroup':
        echo '<optgroup label="' . $this->label . '">' . $this->options . '</optgroup>';
        break;
}