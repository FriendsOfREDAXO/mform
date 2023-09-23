<?php

switch ($this->type) {
    case 'label':
        echo '<label for="' . $this->id . '">' . $this->value . '</label>';
        break;
    case 'alert':
        echo '<div class="mform-alert alert ' . $this->class . '">' . $this->output . '</div>';
        break;
    case 'html':
        echo $this->output;
        break;
    case 'headline':
        echo '<div class="form-group mform-headline ' . $this->class . '" ' . $this->attributes . '><h3>' . $this->output . '</h3></div>';
        break;
    case 'description':
        echo '<div class="form-group mform-description"><p class="col-sm-offset-2 col-sm-10 small"><i class="rex-icon rex-icon-info"></i> ' . $this->output . '</p></div>';
        break;
    case 'tooltip-info':
        echo '<a href="#" class="mblock-info-tooltip" data-toggle="tooltip" title="' . $this->value . '"><i class="fa ' . $this->infoTooltipIcon . '"></i></a>';
        break;
}
