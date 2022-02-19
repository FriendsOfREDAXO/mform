<?php

switch ($this->type) {
    case 'text':
        break;
    case 'radio':
        $this->wrapperOpen = '<div class="radio"><label class="description" for="' . $this->id . '">';
        $this->wrapperClose = $this->label . '</label></div>';
        break;
    case 'checkbox':
        $this->wrapperOpen = '<div class="checkbox"><label class="description" for="' . $this->id . '">';
        $this->wrapperClose = ' ' . $this->label . '</label></div>';
        break;
}

if ($this->type == 'datalist') {
    echo '<datalist id="' . $this->id . '">' . $this->options . '</datalist>';
} else if ($this->type == 'datalist-option') {
    echo '<option ' . $this->attributes . '>' . $this->value . '</option>';
} else {
    echo (property_exists($this, 'wrapperOpen') ? $this->wrapperOpen : '') . '<input id="' . $this->id . '" type="' . $this->type . '" name="REX_INPUT_VALUE' . $this->varId . '" value="' . $this->value . '" class="' . $this->class . '" ' . $this->attributes . '>' . $this->datalist . (property_exists($this, 'wrapperClose') ? $this->wrapperClose : '');
}
