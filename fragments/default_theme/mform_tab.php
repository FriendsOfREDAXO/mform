<?php

switch ($this->type) {
    case 'tabnavli':
        echo '<li role="presentation" class="' . $this->class . '"><a href="#' . $this->id . '" aria-controls="' . $this->id . '" role="tab" data-toggle="tab">' . $this->value . '</a></li>';
        break;
    case 'tab-open':
        echo '<div role="tabpanel" class="tab-pane ' . $this->class . '" ' . $this->attributes . ' id="' . $this->id . '">';
        break;
    case 'tabgroup-open':
        echo '<div class="nav mform-tabs rex-page-nav" ' . $this->attributes . '><ul class="nav nav-tabs" role="tablist">' . $this->element . '</ul><div class="tab-content">';
        break;
    case 'tabgroup-close':
        echo '</div></div>';
        break;
    case 'tab-close':
        echo '</div>';
        break;
}
