Modul-Input Demo Redaxo 4.4.x

Für Redaxo 4.4.x müssen sie die Version 2.1.6 nutzen diese benötigt noch die REX_VALUE[x]-Übergabewerte.

<?php
 /*
 MODUL INPUT DEMO

 @copyright  Copyright (c) 2011 by Doerr Softwaredevelopment
 @author     Joachim Doerr <mail@doerr-softwaredevelopment.com>
 @version    1.0.3

 @rex_param  id     1
 @rex_param  name   001 - MODUL INPUT DEMO
 */

 // instanziieren
 $objForm = new mform();


 // headline
 $objForm->addHeadline('Text-Input und Hidden Elemente');

 // text field
 $objForm->addTextField(1,'REX_VALUE[1]',array('label'=>'Name','style'=>'width:200px'));

 // hidden field
 $objForm->addHiddenField(2,'hidden feld string',array('label'=>'Hidden','style'=>'width:200px'));

 // readonly field
 $objForm->addTextReadOnlyField(3,'readonly feld string',array('label'=>'Readonly','style'=>'width:200px'));

 // textarea field
 $objForm->addTextAreaField(4,'REX_VALUE[4]',array('label'=>'Textarea','style'=>'width:300px;height:180px'));

 // textarea readonly field
 $objForm->addTextAreaReadOnlyField(5,'readonly feld string',array('label'=>'Textarea Readonly','style'=>'width:300px;height:180px'));


 // headline
 $objForm->addHeadline('Select und Multiselect Elemente');

 // select
 $objForm->addSelectField(6,'REX_VALUE[6]',array(1=>'test-1',2=>'test-2'),array('label'=>'Select Name'));
 // select mit ausgelagerten Options, Size und Label
 $objForm->addSelectField(7,'REX_VALUE[7]');
 $objForm->addOptions(array(1=>'test-1',2=>'test-2'));
 $objForm->setSize(5);
 $objForm->setLabel('Select');

 // multiselect
 $objForm->addMultiSelectField(8,'REX_VALUE[8]',array(1=>'test-1',2=>'test-2'),array('label'=>'Select','size'=>'8'));

 // multiselect
 $objForm->addMultiSelectField(9,'REX_VALUE[9]',array(1=>'test-1',2=>'test-2',3=>'test-3',4=>'test-4'),array('label'=>'Multiselect'), 'full');


 // headline
 $objForm->addHeadline('Radio und Checkbox Elemente');

 // checkbox
 $objForm->addCheckboxField(10,'REX_VALUE[10]',array(1=>'test-1'),array('label'=>'Checkbox','data'=>'test-data-checkbox'));

 // radiobox
 $objForm->addRadioField(11,'REX_VALUE[11]',array(1=>'test-1',2=>'test-2'),array('label'=>'Radio Buttons','radio-attr'=>array(1=>array('data'=>'text-data-radio-1'), 2=>array('data'=>'text-data-radio-2'))));

 // headline
 $objForm->addHeadline('System-Button Elemente');

 // media button
 $objForm->addMediaField(1,'REX_FILE[1]',array('types'=>'gif,jpg','preview'=>1,'category'=>4,'label'=>'Bild'));

 // medialist button
 $objForm->addMedialistField(2,'REX_MEDIALIST[2]',array('types'=>'gif,jpg','preview'=>1,'category'=>4,'label'=>'Bildliste'));

 // link button
 $objForm->addLinkField(1,'REX_LINK_ID[1]',array('label'=>'Link','category'=>3));

 // linklist button
 $objForm->addLinklistField(1,'REX_LINKLIST[1]',array('label'=>'Linkliste','category'=>3));


 // headline
 $objForm->addHeadline('Text Elemente');

 // description
 $objForm->addDescription('Beschreibungstext auch Mehrzeilig');

 // HTML
 $objForm->addHtml('<b>HTML <i>Text</i></b>');


 // get formular
 echo $objForm->show_mform();

?>
Markitup Textfeld einsetzen

<?php
 /*
 MODUL INPUT DEMO

 @copyright  Copyright (c) 2011 by Doerr Softwaredevelopment
 @author     Joachim Doerr <mail@doerr-softwaredevelopment.com>
 @version    1.0.3

 @rex_param  id     2
 @rex_param  name   002 - MODUL MARKITUP INPUT DEMO
 */

 if(OOAddon::isAvailable('markitup'))
 {
   a287_markitup::markitup('textarea.markitup1');
 }

 // instanziieren
 $objForm = new mform();

 // headline
 $objForm->addHeadline('Markitup Textfeld');

 // markitup
 $objForm->addTextAreaField(1,'REX_VALUE[1]',array('label'=>'Textarea','class'=>"markitup1"));

 // get formular
 echo $objForm->show_mform();

?>