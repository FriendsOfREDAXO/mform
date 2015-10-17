<?php
/**
 * @copyright Copyright (c) 2015 by Joachim Doerr
 * @author mail[at]joachim-doerr[dot]com Joachim Doerr
 *
 * @package redaxo5
 * @version 4.0.0
 * @license MIT
 */

$strModulInputHeadline = '
  <h2 class="rex-hl2">' . $this->i18n('demo_modul') . '</h2>
';

$strModulInputDemo = <<<EOT
<?php
/*
MODUL INPUT DEMO

@copyright  Copyright (c) 2011 by Doerr Softwaredevelopment
@author     Joachim Doerr <mail@doerr-softwaredevelopment.com>
@version    3.3.0

@rex_param  id     1
@rex_param  name   001 - MODUL INPUT DEMO
*/

EOT;

$strModulInputDemo .= '
// instanziieren
$mform = new mform();

// html
$mform->addHtml(\'<b>HTML Code</b>\');

// headline
$mform->addHeadline(\'Text-Input und Hidden Elemente\');

// text field
$mform->addTextField(1,array(\'label\'=>\'Input\',\'style\'=>\'width:200px\'));

// hidden field
$mform->addHiddenField(2,\'hidden feld string\',array(\'label\'=>\'Hidden\',\'style\'=>\'width:200px\'));

// readonly field
$mform->addTextReadOnlyField(3,\'readonly feld string\',array(\'label\'=>\'Readonly\',\'style\'=>\'width:200px\'));

// textarea field
$mform->addTextAreaField(4,array(\'label\'=>\'Textarea\',\'style\'=>\'width:300px;height:180px\'));

// markitup
$mform->addTextAreaField(5,array(\'label\'=>\'Textarea\',\'class\'=>"markitup1"));

// textarea readonly field
$mform->addTextReadOnlyField(6,\'string readonly\',array(\'label\'=>\'Readonly\',\'style\'=>\'width:300px;height:180px\'));


// headline
$mform->addHeadline(\'Select und Multiselect Elemente\');

// select
$mform->addSelectField(7,array(1=>\'test-1\',2=>\'test-2\'),array(\'label\'=>\'Select\'));

// select mit ausgelagerten Options, Size und Label
$mform->addSelectField(8);
$mform->addOptions(array(1=>\'test-1\',2=>\'test-2\'));
$mform->setSize(5);
$mform->setLabel(\'Select\');

// multiselect
$mform->addMultiSelectField(9,array(1=>\'test-1\',2=>\'test-2\'),array(\'label\'=>\'Multiselect\',\'size\'=>\'8\'));

// multiselect
$mform->addMultiSelectField(10,array(1=>\'test-1\',2=>\'test-2\',3=>\'test-3\',4=>\'test-4\'),array(\'label\'=>\'Multiselect\'), \'full\');


// headline
$mform->addHeadline(\'Radio und Checkbox Elemente\');

// checkbox
$mform->addCheckboxField(11,array(1=>\'test-1\'),array(\'label\'=>\'Checkbox\'));

// radiobox
$mform->addRadioField(12,array(1=>\'test-1\',2=>\'test-2\'),array(\'label\'=>\'Radio Buttons\'));


// headline
$mform->addHeadline(\'System-Button Elemente\');

// media button
$mform->addMediaField(1,array(\'types\'=>\'gif,jpg\',\'preview\'=>1,\'category\'=>4,\'label\'=>\'Bild\'));

// medialist button
$mform->addMedialistField(1,array(\'types\'=>\'gif,jpg\',\'preview\'=>1,\'category\'=>4,\'label\'=>\'Bildliste\'));

// link button
$mform->addLinkField(1,array(\'label\'=>\'Link\',\'category\'=>3));

// linklist button
$mform->addLinklistField(1,array(\'label\'=>\'Linkliste\',\'category\'=>3));

// customlink button
$objForm->addCustomLinkField(1,array(\'label\'=>\'Custom Link\',\'style\'=>\'width:200px\'));


// headline
$mform->addHeadline(\'Text Elemente\');

// description
$mform->addDescription(\'Beschreibungstext auch Mehrzeilig\');

// HTML
$mform->addHtml(\'<b>HTML <i>Text</i></b>\');


// get formular
echo $mform->show_mform();

?>

<br/> Test zwischen zwei verschiedenen MForm Instanzen.  <br/>

<?php

// instanziieren
$mform = new mform();


// headline
$mform->addHeadline(\'Neues Form\');

// text field
$mform->addTextField(13.1,array(\'label\'=>\'Input1\',\'style\'=>\'width:200px\'));

// text field
$mform->addTextField(13.2,array(\'label\'=>\'Input2\',\'style\'=>\'width:200px\'));

// text field
$mform->addTextField(13.3,array(\'label\'=>\'Input3\',\'style\'=>\'width:200px\'));


// get formular
echo $mform->show_mform();

?>
';

echo rex_view::content('block', $strModulInputHeadline . rex_string::highlight($strModulInputDemo));
