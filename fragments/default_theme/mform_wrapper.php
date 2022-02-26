<?php

switch ($this->type) {
    // DEFAULT STUFF
    case 'wrapper':
        echo '<div class="mform form-horizontal">'.$this->output.'</div>';
        break;
    case 'hidden':
        echo '<div class="hidden" style="display:none">'.$this->label.$this->element.'</div>';
        break;

    // COLUM
    case 'column-open':
        echo '<div class="' . $this->class . '" ' . $this->attributes . '>';
        break;
    case 'columngroup-open':
        echo '<div class="row '.$this->class.'" ' . $this->attributes . '>';
        break;

    // COLLAPSE
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

    // TAB
    case 'tabnavli':
        echo '<li role="presentation" class="' . $this->class . '"><a href="#' . $this->id . '" aria-controls="' . $this->id . '" role="tab" data-toggle="tab">' . $this->value . '</a></li>';
        break;
    case 'tab-open':
        echo '<div role="tabpanel" class="tab-pane ' . $this->class . '" ' . $this->attributes . ' id="' . $this->id . '">';
        break;
    case 'tabgroup-open':
        echo '<div class="nav mform-tabs rex-page-nav" ' . $this->attributes . '><ul class="nav nav-tabs" role="tablist">' . $this->element . '</ul><div class="tab-content">';
        break;

    // FIELDSET
    case 'fieldset-open':
        echo '<fieldset class="' . $this->class . '" ' . $this->attributes . '>' . $this->legend;
        break;
    case 'fieldset-close':
        echo '</fieldset>';
        break;
    case 'legend':
        echo '<legend>' . $this->value . '</legend>';
        break;

    // DIV CLOSE STUFF
    case 'tab-close':
    case 'accordion-close':
    case 'collapse-close':
    case 'columngroup-close':
    case 'column-close':
        echo '</div>';
        break;
    case 'tabgroup-close':
    case 'accordion-collapse-close':
        echo '</div></div>';
        break;
}