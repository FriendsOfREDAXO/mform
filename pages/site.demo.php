<?php
/*
site.demo.php

@copyright Copyright (c) 2013 by Doerr Softwaredevelopment
@author mail[at]joachim-doerr[dot]com Joachim Doerr

@package redaxo4
@version 2.2.0
*/

$arrMarkitupSettings = '$arrMarkitupSettings';

$strModulInputDemo = <<<EOT
<?php
/*
MODUL INPUT DEMO

@copyright Copyright (c) 2013 by Doerr Softwaredevelopment
@author mail[at]joachim-doerr[dot]com Joachim Doerr

@package redaxo4
@version 2.2.0
*/

if(OOAddon::isAvailable('markitup'))
{
  a287_markitup::markitup('textarea.markitup1');
}

EOT;

$strModulInputDemo .= '
// instanziieren
$objForm = new mform();

// html
$objForm->addHtml(\'<b>HTML Code</b>\');

// headline
$objForm->addHeadline(\'Text-Input und Hidden Elemente\');

// text field
$objForm->addTextField(1.1,array(\'label\'=>\'Input\',\'style\'=>\'width:200px\'));

// hidden field
$objForm->addHiddenField(1.2,\'hidden feld string\',array(\'label\'=>\'Hidden\',\'style\'=>\'width:200px\'));

// readonly field
$objForm->addTextReadOnlyField(2.1,\'readonly feld string\',array(\'label\'=>\'Readonly\',\'style\'=>\'width:200px\'));

// textarea field
$objForm->addTextAreaField(2.2,array(\'label\'=>\'Textarea\',\'style\'=>\'width:300px;height:180px\'));

// markitup
$objForm->addTextAreaField(2.3,array(\'label\'=>\'Textarea\',\'class\'=>"markitup1"));

// textarea readonly field
$objForm->addTextReadOnlyField(3,\'string readonly\',array(\'label\'=>\'Readonly\',\'style\'=>\'width:300px;height:180px\'));


// headline
$objForm->addHeadline(\'Select und Multiselect Elemente\');

// select
$objForm->addSelectField(4,array(1=>\'test-1\',2=>\'test-2\'),array(\'label\'=>\'Select\'));

// select mit ausgelagerten Options, Size und Label
$objForm->addSelectField(5);
$objForm->addOptions(array(1=>\'test-1\',2=>\'test-2\'));
$objForm->setSize(5);
$objForm->setLabel(\'Select\');

// multiselect
$objForm->addMultiSelectField(7.1,array(1=>\'test-1\',2=>\'test-2\'),array(\'label\'=>\'Multiselect\',\'size\'=>\'8\'));

// multiselect
$objForm->addMultiSelectField(7.2,array(1=>\'test-1\',2=>\'test-2\',3=>\'test-3\',4=>\'test-4\'),array(\'label\'=>\'Multiselect\'), \'full\');


// headline
$objForm->addHeadline(\'Radio und Checkbox Elemente\');

// checkbox
$objForm->addCheckboxField(8.1,array(1=>\'test-1\'),array(\'label\'=>\'Checkbox\'));

// radiobox
$objForm->addRadioField(8.2,array(1=>\'test-1\',2=>\'test-2\'),array(\'label\'=>\'Radio Buttons\'));


// headline
$objForm->addHeadline(\'System-Button Elemente\');

// media button
$objForm->addMediaField(1,array(\'types\'=>\'gif,jpg\',\'preview\'=>1,\'category\'=>4,\'label\'=>\'Bild\'));

// medialist button
$objForm->addMedialistField(2,array(\'types\'=>\'gif,jpg\',\'preview\'=>1,\'category\'=>4,\'label\'=>\'Bildliste\'));

// link button
$objForm->addLinkField(1,array(\'label\'=>\'Link\',\'category\'=>3));

// linklist button
$objForm->addLinklistField(1,array(\'label\'=>\'Linkliste\',\'category\'=>3));


// headline
$objForm->addHeadline(\'Text Elemente\');

// description
$objForm->addDescription(\'Beschreibungstext auch Mehrzeilig\');

// HTML
$objForm->addHtml(\'<b>HTML <i>Text</i></b>\');


// get formular
echo $objForm->show_mform();

?>

<br/> Test zwischen zwei verschiedenen MForm Instanzen.  <br/>

<?php

// instanziieren
$objForm = new mform();


// headline
$objForm->addHeadline(\'Neues Form\');

// text field
$objForm->addTextField(12,array(\'label\'=>\'Input\',\'style\'=>\'width:200px\'));


// get formular
echo $objForm->show_mform();

?>
';