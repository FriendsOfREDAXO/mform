<?php
/** @var rex_fragment $this */

switch ($this->getVar('type')) {
    // DEFAULT STUFF
    case 'wrapper':
        echo '<div class="mform form-horizontal">' . $this->getVar('output') . '</div>';
        break;
    case 'hidden':
        echo '<div class="hidden" style="display:none">' . $this->getVar('label') . $this->getVar('element') . '</div>';
        break;

    // COLUM
    case 'start-group-column':
        echo '<div class="row ' . $this->getVar('class') . '" ' . $this->getVar('attributes') . '>';
        break;
    case 'column':
        echo '<div class="' . $this->getVar('class') . '" ' . $this->getVar('attributes') . '>';
        break;

    // INLINE
    case 'inline':
        $options = $this->vars;
        $options['notClosedFormGroup'] = true;
        $options['notCloseInputWrapper'] = true;
        $this->subfragment('mform/mform_default.php', $options);
        break;
    case 'close-inline':
        echo '</div></div>';
        break;

    // COLLAPSE
    case 'collapse-button';
        echo '<a class="btn btn-white btn-block ' . $this->getVar('class') . '" ' . $this->getVar('attributes') . '>' . $this->getVar('value') . '</a>';
        break;
    case 'start-group-collapse':
        echo '<div class="collapse-group ' . $this->getVar('class') . '" ' . $this->getVar('attributes') . '>';
        break;
    case 'collapse':
        echo $this->getVar('label') . '<div class="collapse ' . $this->getVar('class') . '" ' . $this->getVar('attributes') . '>';
        break;

    // TAB
    case 'tabnavli':
        echo '<li role="presentation" class="' . $this->getVar('class') . '"><a href="#' . $this->getVar('value') . '" aria-controls="' . $this->getVar('value') . '" role="tab" data-toggle="tab" data-tab-item="' . $this->getVar('value') . '">' . $this->getVar('label') . '</a></li>';
        break;
    case 'tab':
        echo '<div role="tabpanel" class="tab-pane ' . $this->getVar('class') . '" ' . $this->getVar('attributes') . '>';
        break;
    case 'start-group-tab':
        echo '<div class="nav mform-tabs rex-page-nav" ' . $this->getVar('attributes') . '><ul class="nav nav-tabs" role="tablist">' . $this->getVar('element') . '</ul><div class="tab-content">';
        break;

    // FIELDSET
    case 'fieldset':
        echo '<fieldset class="' . $this->getVar('class') . '" ' . $this->getVar('attributes') . '>' . $this->getVar('legend');
        break;
    case 'close-fieldset':
        echo '</fieldset>';
        break;
    case 'legend':
        echo '<legend>' . $this->getVar('legend') . '</legend>';
        break;

    // DIV CLOSE STUFF
    case 'close-tab':
    case 'close-collapse':
    case 'close-column':
    case 'close-group-collapse':
    case 'close-group-column':
        echo '</div>';
        break;
    case 'close-group-tab':
        echo '</div></div>';
        break;
}