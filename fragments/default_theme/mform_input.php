<?php
$wrapperOpen = '';
$wrapperClose = '';
switch ($this->type) {
    case 'text':    
        break;
    case 'radio':
        $wrapperOpen = '<div class="radio"><label class="description" for="' . $this->id . '">';
        $wrapperClose = $this->label . '</label></div>';
        break;
    case 'checkbox':
        $wrapperOpen = '<div class="checkbox"><label class="description" for="' . $this->id . '">';
        $wrapperClose = ' ' . $this->label . '</label></div>';
        break;
}

if ('datalist' === $this->type) {
    echo '<datalist id="' . $this->id . '">' . $this->options . '</datalist>';
} elseif ('datalist-option' == $this->type) {
    echo '<option ' . $this->attributes . '>' . $this->value . '</option>';
} else {
    echo(isset($wrapperOpen) ? $wrapperOpen : '') . '<input id="' . $this->id . '" type="' . $this->type . '" name="REX_INPUT_VALUE' . $this->varId . '" value="' . $this->value . '" class="' . $this->class . '" ' . $this->attributes . '>' . $this->datalist . (isset($wrapperClose) ? $wrapperClose : '');
}
