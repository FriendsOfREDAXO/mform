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

$strModulInputDemo = '<?php
// instanziieren
$mform = new mform();

// headline
$mform->addHeadline(\'MForm Demo Formular\');
// description
$mform->addDescription(\'Dieses Formular ist ein Demo-Formular. MForm generiert ausschließlich Modul-Input-Formulare. Es gibt keinen Output für diese Demo.\');

// fieldset
$mform->addFieldset(\'Fieldset Headline\');
// multiselect
$mform->addMultiSelectField(1,array(1=>\'test-1\',2=>\'test-2\'),array(\'label\'=>\'Multiselect\',\'size\'=>\'8\', \'class\'=>\'test123123\'));
// textinput
$mform->addTextField(2.1,array(\'label\'=>\'Input1\'));
// textinput
$mform->addTextField(2.2,array(\'label\'=>\'Input2\'));

// fieldset
$mform->addFieldset(\'Input Fields\');
// text field
$mform->addTextField(3.1,array(\'label\'=>\'Input\'));
// hidden field
$mform->addHiddenField(3.2,\'hidden feld string\',array(\'label\'=>\'Hidden\'));
// readonly field
$mform->addTextReadOnlyField(3.3,\'readonly feld string\',array(\'label\'=>\'Readonly\'));

// fieldset
$mform->addFieldset(\'Textarears\');
// textarea field
$mform->addTextAreaField(4.1,array(\'label\'=>\'Textarea\',\'style\'=>\'height:280px\'));
// markitup
$mform->addTextAreaField(4.2,array(\'label\'=>\'Markitup\',\'class\'=>\'markitupEditor-markdown_full\', \'id\'=>\'markitup_markdown_1\', \'full\'=>1));
// markitup
$mform->addTextAreaField(4.3,array(\'label\'=>\'Redactor\',\'class\'=>\'redactorEditor-full\', \'id\'=>\'redactor_1\', \'full\'=>1));
// textarea readonly field
$mform->addTextReadOnlyField(4.4,\'string readonly\',array(\'label\'=>\'Readonly\',\'style\'=>\'height:80px\'));

// fieldset
$mform->addFieldset(\'Select und Multiselects\');
// select
$mform->addSelectField(5.1,array(1=>\'test-1\',2=>\'test-2\'),array(\'label\'=>\'Default select\'));
// select mit ausgelagerten Options, Size und Label
$mform->addSelectField(5.2);
$mform->addOptions(array(1=>\'test-1\',2=>\'test-2\'));
$mform->setSize(5);
$mform->setLabel(\'Select size 5\');
// multiselect
$mform->addMultiSelectField(5.3,array(1=>\'test-1\',2=>\'test-2\'),array(\'label\'=>\'Multiselect\',\'size\'=>\'8\'));
// multiselect
$mform->addMultiSelectField(5.4,array(1=>\'test-1\',2=>\'test-2\',3=>\'test-3\',4=>\'test-4\',5=>\'test-5\'),array(\'label\'=>\'Multiselect full height\'), \'full\');

// fieldset
$mform->addFieldset(\'Radio und Checkboxes\');
// checkbox
$mform->addCheckboxField(6.1,array(1=>\'test-1\'),array(\'label\'=>\'Checkbox\'));
// radiobox
$mform->addRadioField(6.2,array(1=>\'test-1\',2=>\'test-2\'),array(\'label\'=>\'Radio Buttons\'));

// fieldset
$mform->addFieldset(\'System-Elements\');
// media button
$mform->addMediaField(1,array(\'types\'=>\'gif,jpg\',\'preview\'=>1,\'category\'=>4,\'label\'=>\'Bild\'));
// medialist button
$mform->addMedialistField(1,array(\'types\'=>\'gif,jpg\',\'preview\'=>1,\'category\'=>4,\'label\'=>\'Bildliste\'));
// link button
$mform->addLinkField(1,array(\'label\'=>\'Link\',\'category\'=>3));
// linklist button
$mform->addLinklistField(1,array(\'label\'=>\'Linkliste\',\'category\'=>3));

// fieldset
$mform->closeFieldset();

// headline
$mform->addHeadline(\'Text Elemente\');
// description
$mform->addDescription(\'Beschreibungstext auch Mehrzeilig\');
// HTML
$mform->addHtml(\'<b>HTML <i>Text</i></b>\');

// get formular
echo $mform->show();
?>

<br/> Test zwischen zwei verschiedenen MForm Instanzen.  <br/>

<?php
// instanziieren
$mform = new mform();

// fieldset
$mform->addFieldset(\'Neues Form\');
// text field
$mform->addTextField(7.1,array(\'label\'=>\'Input1\',\'style\'=>\'width:200px\'));
// text field
$mform->addTextField(7.2,array(\'label\'=>\'Input2\',\'style\'=>\'width:200px\'));
// text field
$mform->addTextField(7.3,array(\'label\'=>\'Input3\',\'style\'=>\'width:200px\'));

// get formular
echo $mform->show();
?>';

echo rex_view::content('block', $strModulInputHeadline . rex_string::highlight($strModulInputDemo));
