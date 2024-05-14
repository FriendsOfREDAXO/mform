<?php
use FriendsOfRedaxo\MForm;
// init mform
$mform = MForm::factory()
    // add fieldset area
    ->addFieldsetArea('Fieldset Element', MForm::factory()
        // headline, description and some other elements
        ->addHeadline('MForm Demo Headline')
        ->addDescription('Mform Demo Description for any descriptions texts in your modul input formular.')
        ->addHtml('<b>HTML <i>Text</i></b><br>')
    )
    // add second fieldset area
    ->addFieldsetArea('Alert Messages', MForm::factory()
        ->addAlertInfo('Info Alert Message')
        ->addAlertDanger('Danger Alert Message')
        ->addAlertSuccess('Success Alert Message')
        ->addAlertWarning('Warning Alert Message')
        ->addAlertError('Error Alert Message')
    )
    // add third fieldset area
    ->addFieldsetArea('', MForm::factory()
        // toogle checkbox with tooltip
        ->addToggleCheckboxField(2, [1 => 'Test-Checkbox'], ['label' => 'Checkbox'])
        ->setTooltipInfo('Tooltip Test-Checkbox Label.', 'fa-question-circle')
    );
// parse form
echo $mform->show();
