# YForm-Modul: Objparams

> ## Inhalt
> - [Zweck der Objparams](#zweck-der-objparams)
> - [Allgemeine Objparams des Formular](#allgemeine-objparams)
>	- [Formular anzeigen](#formular-anzeigen)
>	- [Eindeutige Namen für Felder](#eindeutige-namen)
>	- [CSS-ID für Formular](#css-id-formular)
>	- [CSS-Klasse für Formular](#css-klasse-formular)
>	- [CSS-ID für den HTML-Wrapper](#css-id-wrapper)
>	- [CSS-Klasse für den HTML-Wrapper](#css-klasse-wrapper)
>	- [Ausgabe der Label](#ausgabe-label)
> - [Objparams zur Formular-Optik](#objparams-optik)
>	- [Themes](#themes)
>	- [Antworttext](#antworttext)
>	- [Submit-Button benennen](#submit-benennen)
>	- [Submit-Button anzeigen](#submit-anzeigen)
>	- [CSS-Klasse für Fehler](#css-klasse-fehler)
>	- ["Echte" Feldnamen](#echte-feldnamen)
> - [Objparams zum Formularversand](#objparams-formularversand)
>	- [Versandmethode des Formulars](#versandmethode)
>	- [Zieladresse des Formulars](#zieladresse)
>	- [Sprunganker](#sprunganker)
>	- [Formular anzeigen nach Abschicken](#formular-anzeigen-nach-abschicken)


## Zweck der Objparams

<a name="zweck-der-objparams"></a>
**Objektparameter** fungieren vor allem als Einstellungen, die das ganze Formular betreffen. Diese Paramenter können - ähnlich wie die Values oder Validates – als einzeilige Anweisung gesetzt werden.

Zusätzlich kann man bestimmen, ob der Objektparameter an genau der Stelle des Formulars verändert wird, an der er im Formular gesetzt wird (`runtime`) oder den Wert initial setzt (`init`, das ist die Standardeinstellung).

Die **allgemeine Syntax** für das Setzen eines objparams lautet so:

	// Im YForm-Formbuilder
	objparams|key|newvalue|[init/runtime]

	// In PHP
	$xform->setObjectparams('key', 'newvalue', '[init/runtime]');

- key = die Bezeichnung des Wertes
- newvalue = der Neue Wert, der gesetzt werden soll
- Der letzte Parameter ist optional und lautet `init` (default) oder `runtime`.
- **Im Folgenden werden alle objparams mit Beispiel aufgelistet.**

<a name="allgemeine-objparams"></a>
## Allgemeine Objparams des Formular

<a name="formular-anzeigen"></a>
### Formular anzeigen

	// Im YForm-Formbuilder
	objparams|form_show|0

	// In PHP
	$xform->setObjectparams('form_show','1');

Mit dem Wert `0` wird das Formular nach dem Abschicken nicht angezeigt. Dieses Ausblenden benötigt man, wenn man eine Formulaktion auslösen will, aber kein sichtbares Formular haben möchte. **Beispiel:** Ein User wird durch den Aufruf einer bestimmten URL freigeschaltet.  
Der Defaultwert ist `1` (anzeigen).

<a name="eindeutige namen"></a>
### Eindeutige Namen Für Felder

	// Im YForm-Formbuilder
	objparams|form_name|formular

	// In PHP
	$xform->setObjectparams('form_name','zweites_formular');

Wenn man mehrere Formulare auf einer Seite verwenden möchte, muss der `form_name` für jedes Formular verschieden sein. Der hier gewählte Name wird bei jedem Feld eines Formulars dem Namen und der ID hinzugefügt, so erhält man eine Eindeutigkeit.  
Der Defaultwert ist `formular`.

<a name="css-id-formular"></a>
### CSS-ID für Formular

	// Im YForm-Formbuilder
	objparams|form_id|contact_form

	// In PHP
	$xform->setObjectparams('form_id','contact_form');

Damit kann dem Formular eine individuelle CSS-ID vergeben werden.  
Default-Ausgabe:
`<form id="form_formular">`

<a name="css-klasse-formular"></a>
### CSS-Klasse für Formular

	// Im YForm-Formbuilder
	objparams|form_class|contact_form

	// In PHP
	$xform->setObjectparams('form_class','contact_form');

Damit kann dem Formular eine individuelle CSS-Klasse vergeben werden.  
Default-Ausgabe:
`<form class="rex-xform">`

<a name="css-id-wrapper"></a>
### CSS-ID für den HTML-Wrapper

	// Im YForm-Formbuilder
	objparams|form_wrap_id|contact_form

	// In PHP
	$xform->setObjectparams('form_wrap_id','contact_form');

Damit kann dem das Formular umgebenden Container eine individuelle CSS-ID vergeben werden.  
Default-Ausgabe:
`<form id="xform">`

<a name="css-klasse-wrapper"></a>
### CSS-Klasse für den HTML-Wrapper

	// Im YForm-Formbuilder
	objparams|form_wrap_class|contact_form

	// In PHP
	$xform->setObjectparams('form_wrap_class','contact_form');

Damit kann dem das Formular umgebenden Container eine individuelle CSS-Klasse vergeben werden.  
Default-Ausgabe:
`<form class="xform">`

<a name="ausgabe-label"></a>
### Ausgabe der Label

	// Im YForm-Formbuilder
	objparams|form_label_type|html

	// In PHP
	$xform->setObjectparams('form_label_type','html');

Wenn man den Wert hier auf `plain` setzt, werden die Feld-Label nicht als HTML interpretiert, sondern mit `htmlspecialchars` und `nl2br` maskiert.  
Default ist `html`.

---

<a name="objparams-optik"></a>
## Objparams zur Formular-Optik

<a name="themes"></a>
### Themes

	// Im YForm-Formbuilder
	objparams|form_skin|classic

	// In PHP
	$xform->setObjectparams('form_skin','classic');

YForm verfügt über `Templates`, in denen das HTML-Markup definiert ist, das die Felder umgibt. Im Ordner `ytemplates` gibt es Unterordner für jedes Theme, in denen dann die Templates für die einzelnen Felder zu finden sind. Auf diese Weise kann man schnell eigene Themes definieren, die auf dem Basis-Theme aufbauen: Wenn es für einen Feldtyp ein eigenes Template gibt, wird dieses verwendet, anonsten das des Basis-Themes.
Der Defaultwert lautet `bootstrap`, d.h. als Basis-Theme ist das HTML-Schema des CSS-Frameworks "Bootstrap" hinterlegt.

<a name="antworttext"></a>
### Antworttext

	// Im YForm-Formbuilder
	objparams|answertext|<p>Das Formular wurde abgeschickt.</p>

	// In PHP
	$xform->setObjectparams('answertext','Das Formular wurde abgeschickt.');

Hier kann ein optionaler Antworttext definiert werden, der nach dem Abschicken angezeigt wird. Im Prinzip ist die Funktion identisch mit der vordefinierten Aktion `Meldung bei erfolgreichen Versand`, wo man allerdings noch die Wahl hat zwischen Plaintext, HTML und Textile. Falls beides definiert wurde, überschreibt die vordefinierte Aktion den bei answertext hinterlegten Wert.

<a name="submit-benennen"></a>
### Submit-Button benennen

	// Im YForm-Formbuilder
	objparams|submit_btn_label|Formular senden

	// In PHP
	$xform->setObjectparams('submit_btn_label','Formular senden');

Damit kann die Standard-Button-Beschriftung `Abschicken` verändert werden.

<a name="submit-anzeigen"></a>
### Submit-Button anzeigen

	// Im YForm-Formbuilder
	objparams|submit_btn_show|0

	// In PHP
	$xform->setObjectparams('submit_btn_show',0);

Mit dem Wert `0` wird der Standard-Submit-Button versteckt. Dies ist zum Beispiel sinnvoll, wenn man eigene Buttons definiert hat.  
Default ist `1` (Anzeigen).

<a name="css-klasse-fehler"></a>
### CSS-Klasse für Fehler

	// Im YForm-Formbuilder
	objparams|error_class|my_form_error

	// In PHP
	$xform->setObjectparams('error_class','my_form_error');

Diese individuelle CSS-Klasse kommt an zwei Stellen zum Tragen:  
1. im Container mit den Fehlerhinweisen zu Beginn des Formulars:  
`<div class="alert alert-danger my_form_error">`  
2. im Container aller Felder, die bei einer Validierung fehlschlagen:  
`<div class="form-group YForm-element my_form_error">`.  
So kann man sowohl Label als auch Feld als fehlerhaft formatieren.

Die Default-CSS-Klasse ist `form_warning`.

<a name="echte-feldnamen"></a>
### "Echte" Feldnamen

	// Im YForm-Formbuilder
	objparams|real_field_names|1

	// In PHP
	$xform->setObjectparams('real_field_names',1);

Mit dem auf `1` gesetzten Wert werden exakt die Feldnamen im Formular genommen, die auch in der Formulardefinition gesetzt wurden. Der Feldname lautet dann z.B. nicht mehr `name="FORM[formular][2]"`, sondern `name=vorname`.  
Der Default-Wert ist `0`.

---

<a name="objparams-formularversand"></a>
## Objparams zum Formularversand

<a name="versandmethode"></a>
### Versandmethode des Formulars

	// Im YForm-Formbuilder`


	// In PHP
	$xform->setObjectparams('form_method','get');

Mit dem Wert `get` wir die Versandmethode auf get geändert, d.h. alle Feldwerte sind als get-Paramater in der URL enthalten.  
Der Defaultwert ist `post`.

<a name="zieladresse"></a>
### Zieladresse des Formulars

	// Im YForm-Formbuilder`
	objparams|form_action|zielseite.html

	// In PHP mit rex_getUrl() auf die Artikel-ID 5
	$xform->setObjectparams('form_action',rex_getUrl(5));

Als Ziel nach dem Abschicken kann eine andere Adresse definiert werden, z.B. für eine ausführliche Danke-Seite. Es könnte auch die aktuelle Artikel-ID gesetzt weden, ergänzt um weitere Parameter.  
Der Defaultwert ist `index.php`, bzw. die URL der Formularseite.

<a name="sprunganker"></a>
### Sprunganker

	// Im YForm-Formbuilder
	objparams|form_anchor|my_form

	// In PHP
	$xform->setObjectparams('my_form');

Wenn sich ein Formular weiter unten auf der Seite befindet, sieht man nach dem Abschicken zunächst keine Erfolgs- oder Fehlermeldung. Über den `form_anchor`lässt sich ein Sprunganker definieren, der in der  URL nach dem Abschicken angehängt wird, so dass die Seite zum Anker springt. Im Normalfall wird man als Anker die ID des Formulars nutzen.  
Der Defaultwert ist leer.

<a name="formular-anzeigen-nach-abschicken"></a>
### Formular anzeigen nach Abschicken

	// Im YForm-Formbuilder`
	objparams|form_showformafterupdate|1

	// In PHP
	$xform->setObjectparams('form_showformafterupdate',1);

Mit dem Wert `1` kann man das Formular nach dem Versand nochmal anzeigen, um zum Beispiel direkt eine neue Eingabe zu ermöglichen oder die eingegebenen Werte erneut zum Verändern anzubieten.  
Default ist `0` (nicht anzeigen).
