# Custom-Link Element

Das Custom MForm Custom-Link-Element ermöglicht es durch den Einsatz eines Feldes mehrere Link-Typen definieren zu können.    

Die Link Typen des Custom-Link-Elements:

* `data-extern`
* `data-intern`
* `data-media`
* `data-mailto`
* `data-phone` (veraltet: data-tel )

> data-tel wird in späteren Versionen entfallen. 

Jeder dieser Typen kann aktiviert oder deaktiviert werden. Per default sind folgende Typen aktiv:

* `data-extern`
* `data-intern`
* `data-media`

Beispiel: 

```php
$mform->addCustomLinkField(6, array('label'=>'Media', 'data-phone'=>'disable', 'data-mailto'=>'disable', 'data-intern'=>'disable'));
```


> ***Wichtig*** Beim Einsatz mit **mblock** muss ein numerischer Key verwendet werden: 

Beispiel: 

`$Mform->addCustomlinkField("$id.0.1")` 



## Einsatz außerhalb von MForm

### rex_form

Das Custom-Link Element ist auch als Widget in rex_form, YForm und als REXVAR einsetzbar. 

In rex_form einfach ein bestehendes Objekt mit folgender Zeile erweitern:

```php
$field = $form->addField('', 'mein_custom_link_field', null, ['internal::fieldClass' => 'rex_form_widget_customlink_element'], true);
```

Zum Einstellen des Custom-Link Elements stehen neben den klassischen rex_form Methoden wie z.B.

`$field->setLabel('Mein CustomLink Feld');`

folgende zusätzliche Möglichkeiten zur Verfügung:

* Link (intern) deaktivieren: `$field->setIntern(false);`
* Link (extern) deaktivieren: `$field->setExternal(false);`
* Medienlink deaktivieren: `$field->setMedia(false);`
* Email-Link deaktivieren: `$field->setMailto(false);`
* Telefonlink deaktivieren: `$field->setPhone(false);`

Im Umkehrschluss können die einzelnen Felder durch Übergabe des Wertes `true` einzeln wieder aktiviert werden. Dies ist allerdings nicht zwingend nötig, da standardmäßig alle Felder auf `true` gesetzt sind.

Weitere Einstellungen für spezielle Linkfelder:
* Kategorie-ID setzen (bezieht sich auf interne Links): `$field->setCategoryId(1);`
* Medienkategorie-ID setzen (bezieht sich auf das Medienlink-Feld): `$field->setMediaCategoryId(1);`
* Dateitypen definieren (bezieht sich auf das Medienlink-Feld): `$field->setTypes('jpg,gif,png,pdf');`


### YForm

In YForm findet man es im Table-Manager.  

PHP-Notation: 

`$yform->setValueField('custom_link', array('Link','Links','1','1','1','1'));`

PIPE-Notation: 

`custom_link|Link|Links|1|1|1|1|`

### Einsatz als "normales" Modul-Widget: 

`REX_CUSTOM_LINK[id=1 widget=1 external=1 intern=0 mailto=0 phone=1 media=1]`
