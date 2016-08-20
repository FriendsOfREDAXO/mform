# Radio und Checkbox Elemente


Die Gruppe Radio- und Checkbox-Elemente stellt Checkbox-Formular-Elemente und Radio-Buttons zur Verfügung.


### Radio und Checkboxen als Formular Elemente


###### Eine Radiobuttons-Gruppe oder Checkbox wird jeweils durch ihre eigene Methode aufgerufen:


* `addRadioField`
* `addCheckboxField`


##### Hinweis: 


Zu beachten ist, dass die Checkboxen Methode sich signifikant von der Radiobutton Methode insofern unterscheidet, dass die Radiobuttons Methode alle im `$arrOptions`-Array enthaltenen Reihen-Elemente zu Radiobutton-Felder weiter verarbeitet wo hingegen die Checkbox Methode nur das letzte Reihen-Element zu einem Checkboxen-Feld verarbeitet. Will man mehrere Checkboxen anlegen muss entsprechend oft die Checkbox-Methode bemüht werden, was natürlich auch für Radiobutton-Gruppen gilt.

Beide Typen können als Formular Elemente eingesetzt werden. Dabei ist zu beachten, dass der Konstruktor der jeweiligen Methode Parameter/Variablen nach folgendem Schema erwartet:


###### Erwartete Übergabewerte der “addRadioField”, “addCheckboxField” Methoden:


`(ID, $arrOptions, $arrAttributes, $strDefaultValue)`


* ID => ` 1 `
* $arrOptions => `array('1_name'=>'1_wert', '2_name'=>'2_wert')`
* $arrAttributes => `array('label'=>'Label Name')`
* $strDefaultValue => 


##### Hinweis:


* Der erste Übergabewerte `id` ist ein Pflichtwert.
* Die weiteren Übergabewerte sind optional.
* Optionen und Attribute können nur als Arrays übergeben werden.
* Der erste Wert `ID` muss der `REX_VALUE_ID` entsprechen.
* Optionen sind zwingend erforderlich.


###### Radiobutton-Elemente


```php
  $objForm->addRadioField(1.1,array(1=>'test-1',2=>'test-2'),array('label'=>'Radio Buttons'));
```

```php
  $objForm->addRadioField(1.2);
  $objForm->setOptions(array(1=>'test-1',2=>'test-2'));
  $objForm->setLabel('Radio Buttons 2');
```

###### Checkboxen-Element


```php
  $objForm->addCheckboxField(2.1,array(1=>'test-1'),array('label'=>'Select'));
```

```php
  $objForm->addCheckboxField(2.2);
  $objForm->setOptions(array(1=>'test-1'));
  $objForm->setLabel('Select 2');
```


***


### Optionen dem Konstruktor der Radio- und Checkbox-Methoden übergeben


##### Hinweis: 


* Damit ein Radiobutton- oder Checkbox-Elementen mit Wert erscheint sind Optionen zwingend erforderlich.


##### Wiki-Links zum Thema:


* Grundlagen
  * [Elementzuweisungen](https://github.com/FriendsOfREDAXO/mform/wiki/Elementzuweisungen)
  	  * [Elementen Optionen zuweisen](https://github.com/FriendsOfREDAXO/mform/wiki/Elementen-Optionen-zuweisen)


*** 


### Attribute in Radiobutton- oder Checkbox-Elementen


##### Wiki-Links zum Thema:


* Grundlagen
  * [Elementzuweisungen](https://github.com/FriendsOfREDAXO/mform/wiki/Elementzuweisungen)
  	  * [Elementen Attribute zuweisen](https://github.com/FriendsOfREDAXO/mform/wiki/Elementen-Attribute-zuweisen)


***


### Default-Value in Radiobutton- oder Checkbox-Elementen


##### Wiki-Links zum Thema:


* Grundlagen
  * [Elementzuweisungen](https://github.com/FriendsOfREDAXO/mform/wiki/Elementzuweisungen)
  	  * [Default-Values definieren](https://github.com/FriendsOfREDAXO/mform/wiki/Default-Values-definieren)