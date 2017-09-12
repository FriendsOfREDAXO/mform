# Sonstige Elementzuweisungen


###### Liste sonstiger Elementzuweisungen


* `setLabel`
* `setSize`
* `setCategory`
* `setMultiple`


***


### Größe des Select-Feldes durch "setSize" festlegen


Es ist durch die Methode `setSize` möglich einem Select-Feld eine feste Größe zuzuweisen. Dabei kann man sich für einen numerischen Wert entscheiden oder via `full` das Feld automatisch berechnen lassen. `full` sorgt dann dafür, dass alle Select-Optionen angezeigt werden. `setSize` ist für einfache Select-Felder als auch für Multi-Select-Felder funktional.

##### Es gibt 2 Wege um die Größe eines Select-Feldes zu setzen:


* Als Wert über den Konstruktor der Element-Methode.
* Als Wert über den Konstruktor der `setSize`-Methode.


##### Hinweis:


* Es sind ausschließlich numerische Werte und das Wort `full` erlaubt.
* Die Methode `setSize` wirkt sich nur auf Select-Elemente aus.


###### Beispiele für "setSize"


```php
  $objForm->addMultiSelectField(1,array(1=>'test-1',2=>'test-2'),array('label'=>'Select'),'full');
```

```php
  $objForm->addMultiSelectField(2);
  $objForm->setOptions(array(1=>'test-1',2=>'test-2'));
  $objForm->setLabel('Select 2');
  $objForm->setSize(2);
```


###### "setSize" kann für folgende Formular-Elemente definiert werden:


* Select-Elemente
  * `addSelectField`
  * `addMultiSelectField`


***


### Kategorie durch die "setCategory" Methode festlegen


Redaxo bietet die Möglichkeit Link-, Linklisten-Buttons oder Media- und Medialisten-Buttons jeweils eine Kategorie zuzuweisen. Es handelt sich bei Link-Buttons um Kategorien aus der Seiten-Struktur und bei Media-Buttons um Medienpool-Kategorien welche jeweils durch eine eindeutige ID definiert werden können.


##### Es gibt 2 Wege um eine Kategorie zu zuweisen:


* Als Wert über den Konstruktor der Element-Methode.
* Als Wert über den Konstruktor der `setCategory`-Methode.


##### Hinweis:


* Die `setCategory`-Methode verarbeitet ausschließlich numerische Werte.


###### Beispiele für "setCategory"


```php
  $objForm->addLinkField(1);
  $objForm->setCategory(2);
  $objForm->setLabel('Link');
```


###### "setCategory" kann für folgende Formular-Elemente definiert werden:


* System-Button-Elemente
  * `addLinkField`
  * `addLinklistField`
  * `addMediaField`
  * `addMedialistField`