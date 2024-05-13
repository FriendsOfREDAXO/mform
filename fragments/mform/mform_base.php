<?php
/** @var rex_fragment $this */

switch ($this->getVar('type')) {
    case 'label':
        if ($this->getVar('value') !== '') {
            echo '<label for="' . $this->getVar('id') . '">' . $this->getVar('value') . '</label>';
        }
        break;
    case 'alert':
        echo '<div class="mform-alert alert ' . $this->getVar('class') . '">' . $this->getVar('output') . '</div>';
        break;
    case 'html':
        echo $this->getVar('output');
        break;
    case 'headline':
        echo '<div class="form-group mform-headline ' . $this->getVar('class') . '" ' . $this->getVar('attributes') . '><h3>' . $this->getVar('output') . '</h3></div>';
        break;
    case 'description':
        echo '<div class="form-group mform-description"><p class="'.(($this->getVar('full', false))?'col-sm-offset-2 col-sm-10':'col-sm-12').' small '. $this->getVar('class') .'"><i class="rex-icon rex-icon-info"></i> ' . $this->getVar('output') . '</p></div>';
        break;
    case 'tooltip-info':
        echo '<a href="#" class="mblock-info-tooltip" data-toggle="tooltip" title="' . $this->getVar('value') . '"><i class="fa ' . $this->getVar('infoTooltipIcon') . '"></i></a>';
        break;
}