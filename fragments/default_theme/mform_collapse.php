<?php

switch ($this->type) {
    case 'collapse-button';
        echo '<a class="' . $this->class . '" ' . $this->attributes . '>' . $this->value . '</a>';
        break;
    case 'collapse-open':
        echo $this->legend . '<div class="collapse ' . $this->class . '" ' . $this->attributes . ' id="' . $this->id . '">';
        break;
    case 'accordion-open':
        echo '<div class="panel-group" ' . $this->attributes . ' id="' . $this->id . '">';
        break;
    case 'accordion-collapse-open':
        echo '<div class="panel panel-default">' . $this->legend . '<div class="collapse ' . $this->class . '" id="' . $this->id . '">';
        break;
    case 'accordion-collapse-close':
        echo '</div></div>';
        break;
    case 'accordion-close':
    case 'collapse-close':
        echo '</div>';
        break;
}
