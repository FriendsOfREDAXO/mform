# Select und Multiselect Elemente


Die Gruppe Select und Multiselect Elemente umschließt alle Select Formular Elemente. Dabei ist vor allem das Multiselect Element in Modul-Input-Formularen eine Neuheit, da dieses dank jQuery ganz ohne komplizierte Modul-Actions auskommt.


### Select-Typen als Formular Elemente


###### Es gibt 2 Select-Typen die bereit gestellt werden, diese werden durch ihre jeweils eigene Methode aufgerufen:


* `addSelectField`
* `addMultiSelectField`


Diese 2 Typen können als Formular Elemente eingesetzt werden. Dabei ist zu beachten, dass der Konstruktor der jeweiligen Methode Parameter/Variablen nach folgendem Schema erwartet:


###### Erwartete Übergabewerte der “addSelectField”, “addMultiSelectField” Methoden:


`(ID, $arrOptions, $arrAttributes, $strSize, $strDefaultValue)`


* ID => `1`
* $arrOptions => `array('1_name'=>'1_wert', '2_name'=>'2_wert')`
* $arrAttributes => `array('label'=>'Label Name's)`
* $strSize => `1` `full`
* $strDefaultValue) => 


##### Hinweis:


* Der erste Übergabewerte `id` ist ein Pflichtwert.
* Die weiteren Übergabewerte sind optional.
* Optionen und Attribute können nur als Arrays übergeben werden.
* Der erste Wert `ID` muss der `REX_VALUE_ID` entsprechen.
* Optionen sind zwingend erforderlich.


###### Einfaches Select-Element


```php
  $objForm->addSelectField(1.1,array(1=>'test-1',2=>'test-2'),array('label'=>'Select'));
```

```php
  $objForm->addSelectField(1.2);
  $objForm->setOptions(array(1=>'test-1',2=>'test-2'));
  $objForm->setLabel('Select 2');
```


###### Multiselect-Element


```php
  $objForm->addMultiSelectField(2.1,array(1=>'test-1',2=>'test-2'),array('label'=>'Select'));
```

```php
  $objForm->addMultiSelectField(2.2);
  $objForm->setOptions(array(1=>'test-1',2=>'test-2'));
  $objForm->setLabel('Select 2');
```


***


### Optionen in Select- und Multiselect-Elementen


##### Hinweis: 

* Damit ein Select oder Multiselect-Feld mit Inhalt erscheint sind Optionen zwingend erforderlich.


##### Wiki-Links zum Thema:


* Grundlagen
  * [Elementzuweisungen](https://github.com/FriendsOfREDAXO/mform/wiki/Elementzuweisungen)
  	  * [Elementen Optionen zuweisen](https://github.com/FriendsOfREDAXO/mform/wiki/Elementen-Optionen-zuweisen)


*** 


### Attribute in Select- und Multiselect-Elementen


##### Hinweis:


* Die Attribute werden in den `<select>`-Tag geschrieben.


##### Wiki-Links zum Thema:


* Grundlagen
  * [Elementzuweisungen](https://github.com/FriendsOfREDAXO/mform/wiki/Elementzuweisungen)
  	  * [Elementen Attribute zuweisen](https://github.com/FriendsOfREDAXO/mform/wiki/Elementen-Attribute-zuweisen)


***


### Size von Select- und Multiselect-Elementen


##### Wiki-Links zum Thema:


* Grundlagen
  * [Elementzuweisungen](https://github.com/FriendsOfREDAXO/mform/wiki/Elementzuweisungen)
  	  * [Sonstige Zuweisungen](https://github.com/FriendsOfREDAXO/mform/wiki/Sonstige-Zuweisungen)


***


### Default-Value in Select- und Multiselect-Elementen


##### Wiki-Links zum Thema:


* Grundlagen
  * [Elementzuweisungen](https://github.com/FriendsOfREDAXO/mform/wiki/Elementzuweisungen)
  	  * [Default-Values definieren](https://github.com/FriendsOfREDAXO/mform/wiki/Default-Values-definieren)