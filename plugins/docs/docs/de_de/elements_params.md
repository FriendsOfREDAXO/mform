# Elementen Parameter zuweisen


Mit der Methode `setParameters` können System-Button-Elementen diverse Parameter zugewiesen werden.


##### Es gibt in der Regel 2 Wege System-Buttons mit Parametern zu versehen:


* Als Wert über den Konstruktor der System-Button-Methode.
* Als Wert über den Konstruktor der `setParameters` Methode.


##### Hinweis:


* Parameter werden als Array dem Konstruktor übergeben.
* Das Übergabe-Array muss nach folgendem Schema Aufgebaut sein: `array('1_name'=>'1_wert', '2_name'=>'2_wert')`


###### Beispiel für Parameterübergaben


```php
  $objForm->addLinkField(1);
  $objForm->setParameters(array('category'=>1,'label'=>'Interner Link'));

  $objForm->addLinkField(2,array('category'=>1,'label'=>'Interner Link'));
  
  $objForm->addMediaField(1);
  $objForm->setParameters(array('types'=>'gif,jpg','preview'=>1,'category'=>4,'label'=>'Bild'));

  $objForm->addMediaField(1,array('types'=>'gif,jpg','preview'=>1,'category'=>4,'label'=>'Bild'));
```

##### Hinweis:


* Das Attribut `Label` ruft die `setLabel` Methode auf.


###### Folgende System-Button-Elemente reagieren auf Parameter:


* System-Button-Elemente
  * `addLinkField`
  * `addLinklistField`
  * `addMediaField`
  * `addMedialistField`


##### Hinweis: 


* Auch wenn Parameter für alle anderen Elemente zugewiesen werden können haben diese jedoch ausschließlich für System-Button-Elemente eine Bedeutung.


###### Zulässige Parameter für Link-, und Linklisten-Buttons:


* `category`, `label`
* Dem zulässigen `label` Attribut kann ein Array oder String übergeben werden.


###### Zulässige Parameter für Media-, und Medialiste-Buttons:

* `types`, `preview`, `category`, `label`
* Dem zulässigen `label` Attribut kann ein Array oder String übergeben werden.