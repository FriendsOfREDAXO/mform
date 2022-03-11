<?php
//dump($this->type);
switch ($this->type) {
    // DEFAULT STUFF
    case 'wrapper':
        echo '<div class="mform form-horizontal">' . $this->output . '</div>';
        break;
    case 'hidden':
        echo '<div class="hidden" style="display:none">' . $this->label . $this->element . '</div>';
        break;

    // COLUM
    case 'start-group-column':
        echo '<div class="row ' . $this->class . '" ' . $this->attributes . '>';
        break;
    case 'column':
        echo '<div class="' . $this->class . '" ' . $this->attributes . '>';
        break;

    // INLINE
    case 'start-group-inline':
        echo '<div class="form-inline-row">';
        break;
    case 'inline':
        echo '<div class="form-inline ' . $this->class . '" ' . $this->attributes . '>' . $this->label;
        break;

    // COLLAPSE
    case 'collapse-button';
        echo '<a class="btn btn-white btn-block ' . $this->class . '" ' . $this->attributes . '>' . $this->value . '</a>';
        break;
    case 'start-group-collapse':
        echo '<div class="collapse-group ' . $this->class . '" ' . $this->attributes . '>';
        break;
    case 'collapse':
        echo $this->label . '<div class="collapse ' . $this->class . '" ' . $this->attributes . '>';
        break;

    // TAB
    case 'tabnavli':
        echo '<li role="presentation" class="' . $this->class . '"><a href="#' . $this->value . '" aria-controls="' . $this->value . '" role="tab" data-toggle="tab" data-tab-item="' . $this->value . '">' . $this->label . '</a></li>';
        break;
    case 'tab':
        echo '<div role="tabpanel" class="tab-pane ' . $this->class . '" ' . $this->attributes . '>';
        break;
    case 'start-group-tab':
        echo '<div class="nav mform-tabs rex-page-nav" ' . $this->attributes . '><ul class="nav nav-tabs" role="tablist">' . $this->element . '</ul><div class="tab-content">';
        break;

    // FIELDSET
    case 'fieldset':
        echo '<fieldset class="' . $this->class . '" ' . $this->attributes . '>' . $this->legend;
        break;
    case 'close-fieldset':
        echo '</fieldset>';
        break;
    case 'legend':
        echo '<legend>' . $this->legend . '</legend>';
        break;

    // DIV CLOSE STUFF
    case 'close-tab':
    case 'close-collapse':
    case 'close-column':
    case 'close-inline':
    case 'close-group-collapse':
    case 'close-group-column':
    case 'close-group-inline':
        echo '</div>';
        break;
    case 'close-group-tab':
        echo '</div></div>';
        break;
}