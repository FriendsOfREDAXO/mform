<?php
/**
 * site.demo.php
 * @copyright Copyright (c) 2015 by Doerr Softwaredevelopment
 * @author mail[at]joachim-doerr[dot]com Joachim Doerr
 *
 * @package redaxo4.5
 * @version 3.0.0
 */

$arrMarkitupSettings = '$arrMarkitupSettings';
$strModulInputDemo = <<<EOT
<?php
/**
 * MODUL INPUT DEMO
 * @copyright Copyright (c) 2015 by Doerr Softwaredevelopment
 * @author mail[at]joachim-doerr[dot]com Joachim Doerr
 *
 * @package redaxo4.5
 * @version 3.0.0
 */

EOT;

$strModulInputDemo .= '
// instanziieren
$mForm = new MForm();

// html
$mForm->addHtml(\'<b>HTML Code</b>\');

// headline
$mForm->addHeadline(\'Text-Input und Hidden Elemente\');

// text field
$mForm->addTextField(1,array(\'label\'=>\'Input\',\'style\'=>\'width:200px\'));

// hidden field
$mForm->addHiddenField(2,\'hidden feld string\',array(\'label\'=>\'Hidden\',\'style\'=>\'width:200px\'));

// readonly field
$mForm->addTextReadOnlyField(3,\'readonly feld string\',array(\'label\'=>\'Readonly\',\'style\'=>\'width:200px\'));

// textarea field
$mForm->addTextAreaField(4,array(\'label\'=>\'Textarea\',\'style\'=>\'width:300px;height:180px\'));

// markitup
$mForm->addTextAreaField(5,array(\'label\'=>\'Rex Markitup\',\'class\'=>"rex-markitup"));

// textarea readonly field
$mForm->addTextReadOnlyField(6,\'string readonly\',array(\'label\'=>\'Readonly\',\'style\'=>\'width:300px;height:180px\'));


// headline
$mForm->addHeadline(\'Select und Multiselect Elemente\');

// select
$mForm->addSelectField(7,array(1=>\'test-1\',2=>\'test-2\'),array(\'label\'=>\'Select\'));

// select mit ausgelagerten Options, Size und Label
$mForm->addSelectField(8);
$mForm->addOptions(array(1=>\'test-1\',2=>\'test-2\'));
$mForm->setSize(5);
$mForm->setLabel(\'Select\');

// select sql
$mForm->addSelectField(9);
$mForm->addSqlOptions(\'SELECT name,id FROM \'.$REX[\'TABLE_PREFIX\'].\'article WHERE status=1 ORDER BY name\');
$mForm->setSize(1);
$mForm->setLabel(\'Optionen via Sql\');

// multiselect
$mForm->addMultiSelectField(10,array(1=>\'test-1\',2=>\'test-2\'),array(\'label\'=>\'Multiselect\',\'size\'=>\'8\'));

// multiselect
$mForm->addMultiSelectField(11,array(1=>\'test-1\',2=>\'test-2\',3=>\'test-3\',4=>\'test-4\'),array(\'label\'=>\'Multiselect\'), \'full\');


// headline
$mForm->addHeadline(\'Radio und Checkbox Elemente\');

// checkbox
$mForm->addCheckboxField(12,array(1=>\'test-1\'),array(\'label\'=>\'Checkbox\'));

// radiobox
$mForm->addRadioField(13,array(1=>\'test-1\',2=>\'test-2\'),array(\'label\'=>\'Radio Buttons\'));


// headline
$mForm->addHeadline(\'System-Button Elemente\');

// media button
$mForm->addMediaField(1,array(\'types\'=>\'gif,jpg\',\'preview\'=>1,\'category\'=>4,\'label\'=>\'Bild\'));

// medialist button
$mForm->addMedialistField(1,array(\'types\'=>\'gif,jpg\',\'preview\'=>1,\'category\'=>4,\'label\'=>\'Bildliste\'));

// link button
$mForm->addLinkField(1,array(\'label\'=>\'Link\',\'category\'=>3));

// linklist button
$mForm->addLinklistField(1,array(\'label\'=>\'Linkliste\',\'category\'=>3));


// headline
$mForm->addHeadline(\'Text Elemente\');

// description
$mForm->addDescription(\'Beschreibungstext auch Mehrzeilig\');

// HTML
$mForm->addHtml(\'<b>HTML <i>Text</i></b>\');


// get formular
echo $mForm->show();

?>

<br/> Test zwischen zwei verschiedenen MForm Instanzen.  <br/>

<?php

// instanziieren
$mForm = new mform();


// headline
$mForm->addHeadline(\'Neues Form\');

// text field
$mForm->addTextField(14,array(\'label\'=>\'Input\',\'style\'=>\'width:200px\'));

// custom link
$mForm->addCustomLinkField(15);
$mForm->setLabel(\'Custom Link Element\');


// get formular
echo $mForm->show();

?>
';
