# System Button Elemente

> ## Inhalt
> - [System-Buttons als Formular Elemente](#System-Buttons)
> - [Link-Button Elemente](#Link-Button)
> - [Linklisten-Button Elemente](#Linklisten-Button)
> - [Media-Button Elemente](#Media-Button)
> - [Medialisten-Button Elemente](#Medialisten-Button)
> - [Parameter und Attribute in System-Button-Elementen](#Parameter)
> - [Weiterführende Links](#Links)

Die Gruppe der System-Button Elemente enthält alle Redaxo eigenen Link- und Medien-Buttons, dazu gehören Einzel-Buttons und auch Listen-Buttons.


<a name="System-Buttons"></a>
## System-Buttons als Formular Elemente


Die unterschiedlichen System-Buttons werden jeweils durch ihre eigene Methoden angesteuert, es stehen 4 System-Button Elemente zur Verfügung:


* `addLinkField`
* `addLinklistField`
* `addMediaField`
* `addMedialistField`


> **Wichtig**
>
> * Diese 4 Typen können als Formular Elemente eingesetzt werden. 
> * Die jeweiligen Typen-Methode nehmen als Parameter Parameter, Category_Id's, Attribute entgegen.


*Exemplarische Übergabewerte, in den folgenden Beispiele nutzen wir diese Variablen:*

* $id => `1`
* $parameters => `array('types'=>'gif,jpg','preview'=>1)`
* $category => `1`
* $attributes => `array('label'=>'Label Name')`


> **Hinweis**
>
> * Der erste Übergabewerte `$id` ist ein Pflichtwert.
> * Die weiteren Übergabewerte sind optional.
> * Optionen und Attribute können nur als Arrays übergeben werden.
> * `label` ist das einzige zulässige Attribut für die System-Button-Elemente.
> * Der Wert `$id` muss der `REX_LINK_ID`, `REX_LINKLIST_ID`, `REX_MEDIA_ID` oder `REX_MEDIALIST_ID` entsprechen.
    

<a name="Link-Button"></a>
## Link-Button

*Erwartete Übergabewerte der `addLinkField` Methode:*

`($id, $parameter, $catId, $attributes)`

*Einfaches Link-Element mit Category Zuweisung und Label*

```
// instance mform
$mform = new MForm();

// add link field
$mform->addLinkField(1, array('label'=>'Label Name','category'=>1));
```

```
// instance mform
$mform = new MForm();

// add link field
$mform->addLinkField(1, array(), 1, array('label'=>'Label Name'));
```

```
// instance mform
$mform = new MForm();

// add link field
$mform->addLinkField(1);
$mform->setCategory(1);
$mform->setLabel('Label Name');
```


<a name="Linklisten-Button"></a>
## Linklisten-Button Elemente

*Erwartete Übergabewerte der `addLinklistField` Methode:*

`($id, $parameter, $catId, $attributes)`

*Einfaches Linklisten-Element mit Category Zuweisung und Label*

```
// instance mform
$mform = new MForm();

// add link list field
$mform->addLinklistField(1, array('label'=>'Label Name','category'=>1));
```

```
// instance mform
$mform = new MForm();

// add link list field
$mform->addLinklistField(1, array(), 1, array('label'=>'Label Name'));
```

```
// instance mform
$mform = new MForm();

// add link list field
$mform->addLinklistField(1);
$mform->setCategory(1);
$mform->setLabel('Label Name');
```


<a name="Media-Button"></a>
## Media-Button Elemente


*Erwartete Übergabewerte der `addMediaField` Methoden:*


`($id, $parameter, $catId, $attributes)`

*Einfaches Media-Element mit Category Zuweisung und Label*

```
// instance mform
$mform = new MForm();

// add media filed
$mform->addMediaField(1, array('types'=>'gif,jpg','preview'=>1,'category'=>2,'label'=>'Label Name'));
```

```
// instance mform
$mform = new MForm();

// add media field
$mform->addMediaField(1, array('types'=>'gif,jpg','preview'=>1), 1, array('label'=>'Label Name'));
```

```
// instance mform
$mform = new MForm();

// add media field
$mform->addMediaField(1);
$mform->setParameters(array('types'=>'gif,jpg','preview'=>1));
$mform->setCategory(1);
$mform->setLabel('Label Name');
```


<a name="Medialisten-Button"></a>
## Medialisten-Button Elemente

*Erwartete Übergabewerte der `addMedialistField` Methode:*


`($id, $parameter, $catId, $attributes)`

*Einfaches Medialisten-Element mit Category Zuweisung und Label*

```
// instance mform
$mform = new MForm();

// add media list field
$mform->addMedialistField(1, array('types'=>'gif,jpg','preview'=>1,'category'=>1,'label'=>'Label Name'));
```

```
// instance mform
$mform = new MForm();

// add media list field
$mform->addMedialistField(1, array('types'=>'gif,jpg','preview'=>1), 1, array('label'=>'Label Name'));
```

```
// instance mform
$mform = new MForm();

// add media list field
$mform->addMedialistField(1);
$mform->setParameters(array('types'=>'gif,jpg','preview'=>1));
$mform->setCategory(1);
$mform->setLabel('Label Name');
```


<a name="Parameter"></a>
## Parameter und Attribute in System-Button-Elementen

*Liste aller erlaubten Parameter und Attribute für System-Button-Elemente:*

* Media-Button und Medialist-Button
  * `types`
  * `preview`
  * `category`
  * `label`
* Link-Button und Linklist-Button
  * `category`
  * `label`

> **Hinweis**
>
> * Der Parameter `category` verarbeitet nur nummerische Werte.
> * `label` ist das einzige zulässige Attribut für die System-Button-Elemente.


<a name="Links"></a>
## Weiterführende Links

*Generell / Allgemein*

* [Elementzuweisungen](elements_general.md)
* [Elementen Attribute zuweisen](elements_attributes.md)
* [Elementen Parameter zuweisen](elements_params.md)
* [Sonstige Zuweisungen](elements_others.md)
