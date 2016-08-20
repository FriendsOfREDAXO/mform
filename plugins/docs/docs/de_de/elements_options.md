# Elementen Optionen zuweisen


Mit der Methode `addOptions` können Formular-Elemente diverse Optionen zugewiesen werden.


##### Es gibt 2 Wege Select-, Radio- und Checkbox-Elemente Attribute zuzuweisen:


* Als Wert über den Konstruktor der Select-, Radio- und Checkbox-Elemente.
* Als Wert über den Konstruktor der `addOptions` Methode.


##### Hinweis:


* Optionen werden als Array dem Konstruktor übergeben.
* Die Übergabe-Arrays müssen nach folgendem Schema Aufgebaut sein: `array('1_name'=>'1_wert', '2_name'=>'2_wert')`
* Optionen sind für Select-, Radio- und Checkbox-Elementen erforderlich. 


###### Beispiel für Optionenübergaben

```php
  $objForm->addSelectField(1);
  $objForm->addOptions(array(1=>'test-1',2=>'test-2'));
  $objForm->setLabel('Select Name');

  $objForm->addSelectField(2,array(1=>'test-1',2=>'test-2'),array('label'=>'Select Name'));

  $objForm->addCheckboxField(3);
  $objForm->addOptions(array(1=>'test-1'));
  $objForm->setLabel('Checkbox Name');

  $objForm->addCheckboxField(4,array(1=>'test-1',2=>'test-2'),array('label'=>'Checkbox Name'));
```


##### Hinweis:


* Die `addOptions` Methode verarbeitet keine Fremdwerte wie beispielsweise die `setAttributes` Methode.


###### Folgende Select-, Radio- und Checkbox-Elemente benötigen zwingend Optionen:


* Select-Elemente
  * `addSelectField`
  * `addMultiSelectField`
* Checkbox- und Radio-Elemente
  * `addCheckboxField`
  * `addRadioField`


##### Hinweis:


* Auch wenn Optionen anderen Elementen zugewiesen werden können haben diese jedoch ausschließlich für Select-, Radio- und Checkbox-Elemente eine Bedeutung. 
* Select-, Radio- und Checkbox-Elemente benötigen Optionen zwingend, werden keine zugewiesen, wird das Formular-Element nicht erzeugt.