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
    case 'collapse-button':
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
        $isActive = 1 === preg_match('/(^|\s)active(\s|$)/', (string) $this->getVar('class'));
        echo '<li role="presentation" class="' . $this->getVar('class') . '" data-tab-nav-item="' . $this->getVar('value') . '"><a href="#" role="tab" aria-selected="' . ($isActive ? 'true' : 'false') . '" data-mform-tab-toggle="1" data-tab-item="' . $this->getVar('value') . '">' . $this->getVar('label') . '</a></li>';
        break;
    case 'tab':
        echo '<div role="tabpanel" class="tab-pane ' . $this->getVar('class') . '" ' . $this->getVar('attributes') . '>';
        break;
    case 'start-group-tab':
        echo '<div class="nav mform-tabs rex-page-nav ' . $this->getVar('class') . '" data-mform-tabs="1" ' . $this->getVar('attributes') . '><ul class="nav nav-tabs" role="tablist">' . $this->getVar('element') . '</ul><div class="tab-content">';
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

        // MODAL
    case 'modal':
        $modalId = $this->getVar('id');
        $label = $this->getVar('label');
        $btnClass = 'btn ' . ($this->getVar('class') ?: 'btn-default');
        // extract alignment from rendered attributes string (data-modal-align="left|center|right")
        $modalAlignRaw = '';
        $modalRowClass = '';
        if (preg_match('/data-modal-align="([^"]*?)"/', (string) $this->getVar('attributes'), $_alignM)) {
            $modalAlignRaw = $_alignM[1];
        }
        if (preg_match('/data-modal-row-class="([^"]*?)"/', (string) $this->getVar('attributes'), $_rowM)) {
            $modalRowClass = trim((string) $_rowM[1]);
        }
        if (preg_match('/data-group-row-class="([^"]*?)"/', (string) $this->getVar('attributes'), $_groupRowM)) {
            $modalRowClass = trim($modalRowClass . ' ' . (string) $_groupRowM[1]);
        }
        $modalAlignClass = match ($modalAlignRaw) {
            'center' => 'text-center',
            'right'  => 'text-right',
            default  => 'text-left',
        };
        echo '<div class="row form-group mfr-modal-wrapper ' . rex_escape($modalRowClass) . '"><div class="col-sm-12 ' . $modalAlignClass . '">';
        echo '<button type="button" class="' . rex_escape($btnClass) . '" data-toggle="modal" data-target="#' . rex_escape($modalId) . '">';
        echo '<i class="fa fa-cog"></i> ' . rex_escape($label);
        echo '</button>';
        echo '</div></div>';
        echo '<div class="modal fade" id="' . rex_escape($modalId) . '" tabindex="-1" role="dialog">';
        echo '<div class="modal-dialog" role="document"><div class="modal-content">';
        echo '<div class="modal-header">';
        echo '<button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span></button>';
        echo '<h4 class="modal-title">' . rex_escape($label) . '</h4>';
        echo '</div>';
        echo '<div class="modal-body" style="padding: 15px 30px"><div class="mform form-horizontal">';
        break;
    case 'close-modal':
        echo '</div></div>'; // close .mform form-horizontal + modal-body
        echo '<div class="modal-footer">';
        echo '<button type="button" class="btn btn-primary" data-dismiss="modal">' . rex_i18n::msg('mform_modal_apply') . '</button>';
        echo '</div>';
        echo '</div></div></div>'; // modal-content, modal-dialog, modal
        break;
}
