<?php

switch ($this->type) {
    case 'fieldset-open':
        echo '<fieldset class="' . $this->class . '" ' . $this->attributes . '>' . $this->legend;
        break;
    case 'fieldset-close':
        echo '</fieldset>';
        break;
    case 'legend':
        echo '<legend>' . $this->value . '</legend>';
        break;
}
