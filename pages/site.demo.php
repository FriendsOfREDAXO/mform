<?php
/**
 * site.demo.php
 * @copyright Copyright (c) 2015 by Doerr Softwaredevelopment
 * @author mail[at]joachim-doerr[dot]com Joachim Doerr
 *
 * @package redaxo4.5
 * @version 3.0.0
 */

$inputDemo = <<<EOT
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

$inputDemo .= '
// instanziieren
$MForm = new MForm();

// html
$MForm->addHtml(\'<b>HTML Code</b>\');

// headline
$MForm->addHeadline(\'Text-Input und Hidden Elemente\');

// text field
$MForm->addTextField(1,array(\'label\'=>\'Input\',\'style\'=>\'width:200px\'));

// hidden field
$MForm->addHiddenField(2,\'hidden feld string\',array(\'label\'=>\'Hidden\',\'style\'=>\'width:200px\'));

// readonly field
$MForm->addTextReadOnlyField(3,\'readonly feld string\',array(\'label\'=>\'Readonly\',\'style\'=>\'width:200px\'));

// textarea field
$MForm->addTextAreaField(4,array(\'label\'=>\'Textarea\',\'style\'=>\'width:300px;height:180px\'));

// markitup
$MForm->addTextAreaField(5,array(\'label\'=>\'Rex Markitup\',\'class\'=>"rex-markitup"));

// textarea readonly field
$MForm->addTextReadOnlyField(6,\'string readonly\',array(\'label\'=>\'Readonly\',\'style\'=>\'width:300px;height:180px\'));


// headline
$MForm->addHeadline(\'Select und Multiselect Elemente\');

// select
$MForm->addSelectField(7,array(1=>\'test-1\',2=>\'test-2\'),array(\'label\'=>\'Select\'));

// select mit ausgelagerten Options, Size und Label
$MForm->addSelectField(8);
$MForm->addOptions(array(1=>\'test-1\',2=>\'test-2\'));
$MForm->setSize(5);
$MForm->setLabel(\'Select\');

// select sql
$MForm->addSelectField(9);
$MForm->addSqlOptions(\'SELECT name,id FROM \'.$REX[\'TABLE_PREFIX\'].\'article WHERE status=1 ORDER BY name\');
$MForm->setSize(1);
$MForm->setLabel(\'Optionen via Sql\');

// multiselect
$MForm->addMultiSelectField(10,array(1=>\'test-1\',2=>\'test-2\'),array(\'label\'=>\'Multiselect\',\'size\'=>\'8\'));

// multiselect
$MForm->addMultiSelectField(11,array(1=>\'test-1\',2=>\'test-2\',3=>\'test-3\',4=>\'test-4\'),array(\'label\'=>\'Multiselect\'), \'full\');


// headline
$MForm->addHeadline(\'Radio und Checkbox Elemente\');

// checkbox
$MForm->addCheckboxField(12,array(1=>\'test-1\'),array(\'label\'=>\'Checkbox\'));

// radiobox
$MForm->addRadioField(13,array(1=>\'test-1\',2=>\'test-2\'),array(\'label\'=>\'Radio Buttons\'));


// headline
$MForm->addHeadline(\'System-Button Elemente\');

// media button
$MForm->addMediaField(1,array(\'types\'=>\'gif,jpg\',\'preview\'=>1,\'category\'=>4,\'label\'=>\'Bild\'));

// medialist button
$MForm->addMedialistField(1,array(\'types\'=>\'gif,jpg\',\'preview\'=>1,\'category\'=>4,\'label\'=>\'Bildliste\'));

// link button
$MForm->addLinkField(1,array(\'label\'=>\'Link\',\'category\'=>3));

// linklist button
$MForm->addLinklistField(1,array(\'label\'=>\'Linkliste\',\'category\'=>3));


// headline
$MForm->addHeadline(\'Text Elemente\');

// description
$MForm->addDescription(\'Beschreibungstext auch Mehrzeilig\');

// HTML
$MForm->addHtml(\'<b>HTML <i>Text</i></b>\');


// get formular
echo $MForm->show();

?>

<br/> Test zwischen zwei verschiedenen MForm Instanzen.  <br/>

<?php

// instanziieren
$MForm = new mform();


// headline
$MForm->addHeadline(\'Neues Form\');

// text field
$MForm->addTextField(14,array(\'label\'=>\'Input\',\'style\'=>\'width:200px\'));

// custom link
$MForm->addCustomLinkField(15);
$MForm->setLabel(\'Custom Link Element\');


// get formular
echo $MForm->show();

?>
';
