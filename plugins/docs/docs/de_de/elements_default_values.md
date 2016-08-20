# Elemente mit Default-Value versehen


Will man einem Formular-Element einen Default-Wert vergeben kann man hierfür die `setDefaultValue` Methode nutzen. Die Konstruktoren einiger Element-Methoden erwarten auch einen Entsprechenden Wert.


##### Es gibt je nach Element 3 Wege um Default-Werte zu setzen:


* Als Wert über den Konstruktor der Element-Methode.
* Als Wert über den Konstruktor der `setDefaultValue` Methode.
* Als Wert über den Konstruktor der `setAttributes` Methode.


##### Hinweis:


* Default-Werte werden als String dem Konstruktor übergeben.


###### Beispiel für Default-Values

```php
  $objForm->addTextField(1);
  $objForm->setLabel('Label Name');
  $objForm->setDefaultValue('Default Value');

  $objForm->addTextField(2,array('label'=>'Label Name','style'=>'width:200px','default-value'=>'DEFAULT VAL'));

  $objForm->addTextField(3,array('label'=>'Label Name','style'=>'width:200px'),'','DEFAULT VAL');
```


###### Default-Values können für folgende Formular-Elemente definiert werden:


* Text-Input- und Hidden-Elemente
  * `addTextField`
  * `addTextAreaField`
* Select-Elemente
  * `addSelectField`
  * `addMultiSelectField`
* Checkbox- und Radio-Elemente
  * `addCheckboxField`
  * `addRadioField`


##### Hinweis:


* Auch wenn Default-Values Elementen zugewiesen werden können welche in der Liste nicht aufgeführt wurden, werden diese jedoch dann nicht im HTML dieser Elemente geschrieben.
* Default-Values von Select-, Radio- und Checkbox-Elemente dürfen nur deren "Key-Werten" entsprechen.