# Modul-Input Demo Redaxo 5.x


```php
<?php
// instanziieren
$mform = new MForm();

// fieldset
$mform->addFieldset('Text inputs');

// text field
$mform->addTextField(1.1,array('label'=>'Text','style'=>'width:200px'));

// hidden field
$mform->addHiddenField(1.2,'hidden feld string',array('label'=>'Hidden'));

// readonly field
$mform->addTextReadOnlyField(1.3,'readonly feld string',array('label'=>'Text readonly','style'=>'width:200px'));

// textarea field
$mform->addTextAreaField(1.4,array('label'=>'Textarea','style'=>'height:180px'));

// textarea readonly field
$mform->addTextAreaReadOnlyField(1.5,'string readonly',array('label'=>'Textarea Readonly','style'=>'height:180px'));


// fieldset
$mform->addFieldset('Select and multi-select elements');

// select
$mform->addSelectField(2.1,array(1=>'test-1',2=>'test-2'),array('label'=>'Select'));

// select mit ausgelagerten Options, Size und Label
$mform->addSelectField(2.2);
$mform->setOptions(array(1=>'test-1',2=>'test-2'));
$mform->setSize(5);
$mform->setLabel('Select size 5');

// multiselect
$mform->addMultiSelectField(2.3,array(1=>'test-1',2=>'test-2'),array('label'=>'Multiselect'));

// multiselect
$mform->addMultiSelectField(2.4,array('group'=>array(1=>'test-1',2=>'test-2',3=>'test-3',4=>'test-4'),'group2'=>array(5=>'test-5',6=>'test-6')),array('label'=>'Select groups'), 'full');


// fieldset
$mform->addFieldset('Radio and checkbox elements');

// checkbox
$mform->addCheckboxField(3.1,array(1=>'test-1'),array('label'=>'Checkbox'));

// radiobox
$mform->addRadioField(3.2,array(1=>'test-1',2=>'test-2'),array('label'=>'Radio buttons'));


// fieldset
$mform->addFieldset('System-button elements');

// media button
$mform->addMediaField(1,array('types'=>'gif,jpg','preview'=>1,'category'=>4,'label'=>'Media'));

// medialist button
$mform->addMedialistField(1,array('types'=>'gif,jpg','preview'=>1,'category'=>4,'label'=>'Medialist'));

// link button
$mform->addLinkField(1,array('label'=>'Link','category'=>3));

// linklist button
$mform->addLinklistField(1,array('label'=>'Linklist','category'=>3));

// fieldset
$mform->addFieldset('Custom elements');

// custom link
$mform->addCustomLinkField(5.1,array('label'=>'Customlink'));

// input field
$mform->addInputField("range", 5.2, array('label'=>'Range field'));

// img list
$mform->addImagelistField(5.3, array('label' => 'Imagelist'));


// get formular
echo $mform->show();
?>
```
