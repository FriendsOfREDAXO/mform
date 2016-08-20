# Elementen Attribute zuweisen


Mit der `setAttributes` Methode können beliebigen Formular-Elementen Attribute zugewiesen werden.



##### Es gibt in der Regel 2 Wege Elementen Attribute zuzuweisen:

* Als Wert über den Konstruktor der Element-Methode.
* Als Wert über den Konstruktor der `setAttributes` Methode.


##### Hinweis:


* Attribute werden als Array dem Konstruktor übergeben.
* Das Übergabe-Array muss nach folgendem Schema Aufgebaut sein: `array('1_name'=>'1_wert', '2_name'=>'2_wert')`


###### Beispiel für Attributübergaben


```php
  $objForm->addTextField(1);
  $objForm->setLabel('Label Name');
  $objForm->setAttributes(array('style'=>'width:200px'));
  $objForm->setAttributes(array('class'=>'text_input_feld'));

  $objForm->addTextField(2);
  $objForm->setAttributes(array('label'=>'Label Name','style'=>'width:200px','class'=>'text_input_feld'));

  $objForm->addTextField(3,array('label'=>'Label Name','style'=>'width:200px','class'=>'text_input_feld'));
```


##### Hinweis:


* Das Attribut `label` ruft die `setLabel` Methode auf.
* Das Attribut `validation` ruft die `setValidiations` Methode auf.
* Das Attribut `size` ruft die `setSize` Methode auf.
* Das Attribut `default-value` ruft die `setDefaultValue` Methode auf.


##### Attribute können folgenden Formular-Elementen zugewiesen werden:


* Text-Input- und Hidden-Elemente
  * `addTextField`
  * `addHiddenField`
  * `addTextAreaField`
  * `addTextReadOnlyField`
  * `addTextAreaReadOnlyField`
* Select-Elemente
  * `addSelectField`
  * `addMultiSelectField`
* Checkbox- und Radio-Elemente
  * `addCheckboxField`
  * `addRadioField`
* Strukturelle-Elemente
  * `addHtml`
  * `addHeadline`
  * `addDescription`
  * `addFieldset`


##### Hinweis:


* Auch wenn Attribute Elementen zugewiesen werden können welche in der Liste nicht aufgeführt wurden, werden diese jedoch dann nicht im HTML dieser Elemente geschrieben.


###### Zulässigen und unzulässige Attribut Typen:


* Alle möglichen Attribute ausgenommen `id`, `name`, `type`, `value`, `checked` und `selected`.
* Das zulässige `validation` Attribut muss ein Array übergeben.
* Dem zulässigen `label` Attribut kann ein Array oder String übergeben werden.