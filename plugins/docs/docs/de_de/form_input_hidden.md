# Text-Input- und Hidden-Elemente


Die Gruppe Text-Input- und Hidden-Elemente umschließt alle Texteingabe-Formular-Elemente durch welche Text-Strings erfasst werden können.


### Input-Typen als Formular-Element


###### Unterschiedliche Input-Typen werden durch jeweils ihre eigene "Methoen" angesteuert, es gibt 5 Typen:


* `addTextField`
* `addHiddenField`
* `addTextAreaField`
* `addTextReadOnlyField`
* `addTextAreaReadOnlyField`


Diese 5 Typen können als Formular-Elemente eingesetzt werden. Dabei ist zu beachten, dass der Konstruktor der jeweiligen Typen-Methode Parameter, Variablen, Default-Values und Validierungen nach folgenden Schemen erwartet: 


###### Erwartete Übergabewerte der "addTextField", "addTextAreaField" Methoden:


`(ID, $arrAttributes, $arrValidations, $strDefaultValue)`


###### Erwartete Übergabewerte der "addHiddenField", "addTextReadOnlyField", "addTextAreaReadOnlyField" Methoden:


`(ID,$strDefaultValue,$arrAttributes)`


###### Exemplarische Übergabewerte:


* ID => `1`
* $arrAttributes => `array('label'=>'Label Name')`
* $arrValidations => `array('key'=>'Validation')`
* $strDefaultValue => `'Default-Value'`


##### Hinweis:


* Der erste Übergabewert `ID` ist ein Pflichtwert.
* Die weiteren Übergabewerte sind optional.
* Attribute können nur als Array übergeben werden.
* Der erste Wert `ID` muss der `REX_VALUE_ID` entsprechen.


###### Einfaches Text-Input-Element


```php
  $objForm->addTextField(1.1,array('label'=>'Label Name','style'=>'width:200px'));
```

```php
  $objForm->addTextField(1.2);
  $objForm->setAttributes(array('label'=>'name','style'=>'width:200px','validation'=>array('compare','empty')));
```

```php
  $objForm->addTextField(1.3);
  $objForm->setAttributes(array('style'=>'width:200px'));
  $objForm->setLabel('Name');
  $objForm->setValidations(array('compare','empty'));
```


###### Hidden-Input-Element


```php
  $objForm->addHiddenField(2.1,'Hidden Value Wert');
```


###### Readonly-Input-Elemente


```php
  $objForm->addTextReadOnlyField(2.2,'Readonly Value Wert',array('label'=>'Text Readonly','style'=>'width:200px'));
  $objForm->addTextAreaReadOnlyField(2.3,'Readonly Value Wert',array('label'=>'Textarea Readonly','style'=>'width:300px;height:180px');
```


###### Textarea-Element


```php
  $objForm->addTextAreaField(3.1,array('label'=>'Textarea','style'=>'width:300px;height:180px'));
```


***


### Attribute Text-Input- und Hidden-Elementen zuweisen


##### Wiki-Links zum Thema:


* Grundlagen
  * [Elementzuweisungen](https://github.com/FriendsOfREDAXO/mform/wiki/Elementzuweisungen)
      * [Elementen Attribute zuweisen](https://github.com/FriendsOfREDAXO/mform/wiki/Elementen-Attribute-zuweisen)


***


### Validierungen Text-Input-Elementen zuweisen


##### Wiki-Links zum Thema:


* Grundlagen
  * [Elementzuweisungen](https://github.com/FriendsOfREDAXO/mform/wiki/Elementzuweisungen)
      * [Elementen Validierungen zuweisen](https://github.com/FriendsOfREDAXO/mform/wiki/Elementen-Validierungen-zuweisen)


***


### Default-Value Text-Input-Elementen zuweisen


##### Wiki-Links zum Thema:


* Grundlagen
  * [Elementzuweisungen](https://github.com/FriendsOfREDAXO/mform/wiki/Elementzuweisungen)
      * [Default-Values definieren](https://github.com/FriendsOfREDAXO/mform/wiki/Default-Values-definieren)