# System Button Elemente


Die Gruppe der System-Button Elemente enthält alle Redaxo eigenen Link und Medien Buttons, dazu gehören Einzel Buttons und auch Listen Buttons.


### System-Buttons als Formular Elemente


###### Die unterschiedlichen System-Buttons werden jeweils durch ihre eigene Methoden angesteuert, es stehen 4 System-Button Elemente zur Verfügung:


* `addLinkField`
* `addLinklistField`
* `addMediaField`
* `addMedialistField`


Diese 4 Typen können als Formular Elemente eingesetzt werden. Dabei ist zu beachten, dass der Konstruktor der jeweiligen Methode Parameter/Variablen nach folgendem Schema erwartet:


###### Erwartete Übergabewerte der System-Button Methoden:


`(ID, $arrParameters, CAT_ID, $arrAttributes)`


* ID => `1`
* $arrParameters => `array('types'=>'gif,jpg','preview'=>1)`
* CAT_ID => `1`
* $arrAttributes => `array('label'=>'Label Name')`


##### Hinweis:


* Der erste Übergabewerte `id` ist ein Pflichtwert.
* Die weiteren Übergabewerte sind optional.
* Optionen und Attribute können nur als Arrays übergeben werden.
* `label` ist das einzige zulässige Attribut für die System-Button-Elemente.
* Der erste Wert `ID` muss der `REX_VALUE_ID` entsprechen.


###### Link-Button


```php
  $objForm->addLinkField(1,array('label'=>'Link','category'=>2));
```

```php
  $objForm->addLinkField(2,array(), 2, array('label'=>'Link 2'));
```

```php
  $objForm->addLinkField(3);
  $objForm->setCategory(2);
  $objForm->setLabel('Link 3');
```

###### Linklisten-Button


```php
  $objForm->addLinklistField(1,array('label'=>'Linkliste','category'=>2));
```

```php
  $objForm->addLinklistField(2,array(), 2, array('label'=>'Linkliste 2'));
```

```php
  $objForm->addLinklistField(3);
  $objForm->setCategory(2);
  $objForm->setLabel('Linkliste 3');
```


###### Media-Button


```php
  $objForm->addMediaField(1,array('types'=>'gif,jpg','preview'=>1,'category'=>2,'label'=>'Bild'));
```

```php
  $objForm->addMediaField(2,array('types'=>'gif,jpg','preview'=>1), 2, array('label'=>'Bild 2'));
```

```php
  $objForm->addMediaField(3);
  $objForm->setParameters(array('types'=>'gif,jpg','preview'=>1));
  $objForm->setCategory(2);
  $objForm->setLabel('Bild 3');
```


###### Medialisten-Button

```php
  $objForm->addMedialistField(1,array('types'=>'gif,jpg','preview'=>1,'category'=>2,'label'=>'Bild'));
```

```php
  $objForm->addMedialistField(2,array('types'=>'gif,jpg','preview'=>1), 2, array('label'=>'Bild 2'));
```

```php
  $objForm->addMedialistField(3);
  $objForm->setParameters(array('types'=>'gif,jpg','preview'=>1));
  $objForm->setCategory(2);
  $objForm->setLabel('Bild 3');
```


***


### Parameter in System-Button-Elementen


###### Liste aller erlaubten Parameter für System-Button-Elemente:


* Media-Button und Medialist-Button
  * `types`
  * `preview`
  * `category`
  * `label`
* Link-Button und Linklist-Button
  * `category`
  * `label`


###### Hinweis:


* Der Parameter "category" verarbeitet nur numerische Werte.


##### Wiki-Links zum Thema:


* Grundlagen
  * [Elementzuweisungen](https://github.com/FriendsOfREDAXO/mform/wiki/Elementzuweisungen)
      * [Elementen Parameter zuweisen](https://github.com/FriendsOfREDAXO/mform/wiki/Elementen-Parameter-zuweisen)


***


### Attribute in System-Button-Elementen


##### Hinweis:

* `label` ist das einzige zulässige Attribut für die System-Button-Elemente.


##### Wiki-Links zum Thema:


* Grundlagen
  * [Elementzuweisungen](https://github.com/FriendsOfREDAXO/mform/wiki/Elementzuweisungen)
      * [Elementen Attribute zuweisen](https://github.com/FriendsOfREDAXO/mform/wiki/Elementen-Attribute-zuweisen)


***


### Kategorie durch die "setCategory" Methode festlegen


##### Hinweis:


* Die `setCategory` Methode verarbeitet ausschließlich numerische Werte.


##### Wiki-Links zum Thema:


* Grundlagen
  * [Elementzuweisungen](https://github.com/FriendsOfREDAXO/mform/wiki/Elementzuweisungen)
      * [Sonstige Zuweisungen](https://github.com/FriendsOfREDAXO/mform/wiki/Sonstige-Zuweisungen)