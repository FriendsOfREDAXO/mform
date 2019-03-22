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

## Einsatz außerhalb von MForm

Das Custom-Link Element ist auch als Widget in YForm und als REXVAR einsetzbar. 

In YForm findet man es im Table-Manager.  

PHP-Notation: 

`$yform->setValueField('custom_link', array('Link','Links','1','1','1','1'));`

PIPE-Notation: 

`custom_link|Link|Links|1|1|1|1|`

Einsatz als "normales" Modul-Widget: 

`REX_CUSTOM_LINK[id=1 widget=1 external=1 intern=0 mailto=0 phone=1 media=1]`



**Dieser Bereich der Doku muss noch witer ausgearbeitet werden.**
