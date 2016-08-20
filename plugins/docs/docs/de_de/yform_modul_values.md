# YForm-Modul: Values

> **Hinweis:** 
> Dieser Abschnitt der Doku ist noch nicht fertig. Du kannst dich auf [GitHub](https://github.com/yakamara/redaxo_yform_docs/) an der Fertigstellung beteiligen.

- [Zweck der Values](#zweck-der-values)
- [Value-Klassen](#value-klassen)

<a name="zweck-der-values"></a>
## Zweck der Values

Mit diesen Klassen werden alle sichtbaren und verstecken Felder definiert.

> Die Value-Klassen sind hier zu finden:  
> `src/addons/yform/lib/yform/values/`


Beispiele (Schreibweisen): **yForm Formbuilder** und **PHP**

Die PHP-Beispiele können in diesem Formular getestet/eingesetzt werden:

	<?php
	$yform = new rex_yform();
	$yform->setObjectparams('form_action', rex_getUrl(REX_ARTICLE_ID,REX_CLANG_ID));

	$yform->setValueField('text', array("wert1","Wert 1"));

	
	echo $yform->getForm();
	?>


<a name="value-klassen"></a>
## Value-Klassen

	
	
###article

#####Definition
	article|article_id
		
#####Beispiel Formbuilder
	_
	
#####Beispiel PHP
	_
	
	
	
	
	
###be_link

#####Definition
	_
	
#####Beispiel Formbuilder
	_
	
#####Beispiel PHP
	_
		
	
	
	
		
###be_manager_relation

#####Definition
	be_link|name|label|defaultwert|no_db
		
#####Beispiel Formbuilder
	_
	
#####Beispiel PHP
	_
	

	
	
	
	
		
###be_media

#####Definition
	be_media|name|label|defaultwert|no_db
	
#####Beispiel Formbuilder
	_
	
#####Beispiel PHP
	_

	
	
	
	
		
###be_medialist

#####Definition
	be_medialist|name|label|preview|category|types|no_db|	
#####Beispiel Formbuilder
	_
	
#####Beispiel PHP
	_

	
	
	
	
		
###be_select_category

#####Definition
	_
	
#####Beispiel Formbuilder
	_
	
#####Beispiel PHP
	_

	
	
	
	
	
###be_table

#####Definition
	be_table|name|label|Anzahl Spalten|Menge,Preis/Stück
	
#####Beispiel Formbuilder
	_
	
#####Beispiel PHP
	_

	
	
	
	
		
###captcha

#####Definition
	captcha|Beschreibungstext|Fehlertext
	
#####Beispiel Formbuilder
	_
	
#####Beispiel PHP
	_

	
	
	
	
	
###captcha_calc

#####Definition
	captcha_calc|Beschreibungstext|Fehlertext
	
#####Beispiel Formbuilder
	_
	
#####Beispiel PHP
	_

	
	
	
		
###checkbox

#####Definition
	checkbox|name|label|Values(0,1)|default clicked (0/1)|[no_db]	
#####Beispiel Formbuilder
	_
	
#####Beispiel PHP
	_

	
	
	
	
		
###checkbox_sql

#####Definition
	checkbox_sql|label|Bezeichnung:|select id,name from table order by name|	
#####Beispiel Formbuilder
	_
	
#####Beispiel PHP
	_

	
	
	
	
		
###date

#####Definition
	date|name|label| jahrstart | [jahrsende/+5 ]| [Anzeigeformat###Y###-###M###-###D###] | [1/Aktuelles Datum voreingestellt] | [no_db]
	
#####Beispiel Formbuilder
	_
	
#####Beispiel PHP
	_

	
	
	
	
	
###datestamp

#####Definition
	datestamp|name| [YmdHis/U/dmy/mysql] | [no_db] | [0-wird immer neu gesetzt,1-nur wenn leer]
		
#####Beispiel Formbuilder
	_
	
#####Beispiel PHP
	_

	
	
	
	
		
###datetime

#####Definition
	datetime|name|label| jahrstart | jahrsende | minutenformate 00,15,30,45 | [Anzeigeformat ###Y###-###M###-###D### ###H###h ###I###m] |[1/Aktuelles Datum voreingestellt]|[no_db]
	
#####Beispiel Formbuilder
	_
	
#####Beispiel PHP
	_

	
	
	
	
		
###email

#####Definition
	email|name|label|defaultwert|[no_db]|cssclassname
	
#####Beispiel Formbuilder
	_
	
#####Beispiel PHP
	_

	
	
	
	
		
###emptyname

#####Definition
	emptyname|name|
		
#####Beispiel Formbuilder
	_
	
#####Beispiel PHP
	_

	
	
	
	
		
###fieldset

#####Definition
	fieldset|name|label|[class]|[onlyclose/onlycloseall/onlyopen/closeandopen]	
#####Beispiel Formbuilder
	_
	
#####Beispiel PHP
	_

	
	
	
	
		
###float

#####Definition
	float|name|label|scale|defaultwert|[no_db]
	
#####Beispiel Formbuilder
	_
	
#####Beispiel PHP
	_

	
	
	
	
	
###generate_key

#####Definition
	generate_key|name|[no_db]
		
#####Beispiel Formbuilder
	_
	
#####Beispiel PHP

	
	
	
	
		
###generate_password

#####Definition
	generate_password|name|[no_db]
		
#####Beispiel Formbuilder
	_
	
#####Beispiel PHP
	-

	
	
	
	
		
###hashvalue

#####Definition
	hashvalue|name|[title]|field|(md5/sha1/sha512/...)|[salt]|[no_db]
	
#####Beispiel Formbuilder
	_
	
#####Beispiel PHP
	-

	
	
	
	
		
###hidden
definiert ein versteckes Feld

#####Definition
	hidden|name|(default)value||[no_db]
	 	
#####Beispiel Formbuilder
	hidden|name|(default)value||[no_db]
	
#####Beispiel PHP
	$yform->setValueField('hidden', array("name", "<h1>Headline</h1>"));

	
	
	
	
		
###html
gibt html aus

#####Definition
	html|name|[html]
	
#####Beispiel Formbuilder
	html|headline|<h1>Headline</h1>
		
#####Beispiel PHP
	$yform->setValueField('html', array("headline", "<h1>Headline</h1>"));

	
	
	
	
		
###!!index

#####Definition
	index|name|label1,label2,label3|[no_db]|[func/md5/sha]	
#####Beispiel Formbuilder
	_
	
#####Beispiel PHP
	-

	
	
	
	
		
###!!integer

#####Definition
	integer|name|label|defaultwert|[no_db]	
#####Beispiel Formbuilder
	_
	
#####Beispiel PHP
	-

	
	
	
	
		
###ip
übergibt die IP des Users.

#####Definition
	ip|name|[no_db]
	
#####Beispiel Formbuilder
	ip|ip	
	
#####Beispiel PHP
	$yform->setValueField('ip', array("ip"));
	
	-

	
	
	
	
		
###!!mediafile

#####Definition
	mediafile|name|label|groesseinkb|endungenmitpunktmitkommasepariert|pflicht=1|min_err,max_err,type_err,empty_err|[no_db]|mediacatid|user
	
#####Beispiel Formbuilder
	_
	
#####Beispiel PHP
	-

	
	
	
	
		
###!!objparams

#####Definition
	objparams|key|newvalue|[init/runtime]	
#####Beispiel Formbuilder
	_
	
#####Beispiel PHP
	-
--
	
	
	
	
		
	
###!!password


#####Definition
	password|name|label|default_value|[no_db]
	
#####Beispiel Formbuilder
	password|name|label|default_value
	
#####Beispiel PHP
	$yform->setValueField('password', array("name","label", "default_value"));

	
	
	
	
		
###php


#####Definition
	php|name|<?php  ?>
		
#####Beispiel Formbuilder
	php|name|<?php  ?>
		
#####Beispiel PHP
	$yform->setValueField('php', array("name","<?php  ?>"));

	
	
	
	
		
###!!prio

#####Definition
	prio|name|label|fields|scope|defaultwert	
#####Beispiel Formbuilder
	prio|name|label|fields|scope|defaultwert	
#####Beispiel PHP
	$yform->setValueField('prio', array("name","label", "fields", "scope", "defaultwert"));
	
	
	
	
		
###radio
definiert eine Gruppe von Radio-Buttons.

#####Definition
	radio|name|label|Frau=w,Herr=m|[no_db]|[defaultwert]
	
#####Beispiel Formbuilder
	radio|anrede|Anrede|Frau=w,Herr=m
	
#####Beispiel PHP
	$yform->setValueField('radio', array("anrede","Anrede", "Frau=w,Herr=m"));
	
	
	
	
		
###radio_sql
definiert eine Gruppe von Radio-Buttons, via einen mySQL-Query

#####Definition
	radio_sql|name|label|select id,name from table order by name|[defaultvalue]|[no_db]|
	
#####Beispiel Formbuilder
	radio_sql|partner|Partner|select id,name from rex_partner order by name	
#####Beispiel PHP
	$yform->setValueField('radio_sql', array("partner","Partner","select id,name from rex_partner order by name"));
	
	
	
	
		
###readtable
liest einen Datensatz und übergibt die ausgelesenen Werte in den E-mail value_pool, die einem E-Mail-Template über Platzhalter werden können.

#####Definition
	readtable|tablename|feldname|label
	
#####Beispiel Formbuilder
	text|name|Name
	readtable|rex_user|name|name
	action|tpl2email|testtemplate||info@mustermann.de


#####Beispiel PHP
	$yform->setValueField('text', array("name","Name"));
	$yform->setValueField('readtable', array("rex_user","name","name"));
	$yform->setActionField('tpl2email', array("testtemplate","","info@mustermann.de"));


	
liest aus der Tabelle **rex_user** einen Datensatz 

	SELECT * FROM rex_user WHERE name='[eingabe feld name]'

und sendet eine E-Mail mit dem E-Mail-Template "testtemplate" and die E-Mail-Adresse:


	
	
	
	
		
###!!remembervalues

#####Definition
	remembervalues|name|label|label1,label2,label3,label4|opt:default:1/0|opt:dauerinsekunden
	
#####Beispiel Formbuilder
	_
	
#####Beispiel PHP
	-

	
	
	
	
		
###resetbutton
definiert einen Reset-Button, mit dem Eingaben zurückgesetzt werden können.

#####Definition
	resetbutton|name|label|value|cssclassname
	
#####Beispiel Formbuilder
	resetbutton|reset|reset|Reset
	
#####Beispiel PHP
	$yform->setValueField('resetbutton', array("reset","reset","Reset"));
	

	
	
	
	
		
###select
definiert ein Auswahlliste, dessen Optionen über kommagetrennte Angaben erstellt werden.

#####Definition
	select|name|label|Option,Option|[no_db]|defaultwert|multiple=1|selectsize
	
#####Beispiel Formbuilder
	select|anrede|Anrede|Frau=w,Mann=m

#####Beispiel PHP
	$yform->setValueField('select', array("anrede","Anrede","Frau=w,Mann=m"));


	
	
	
	
		
###select_sql
definiert ein Auswahlliste, dessen Optionen über einen mySQL-Query gefüllt werden kann. In diesem Beispiel wird ein Leeroption hinzugefügt. Durch die letzten beiden Parameter wird eine Mehrfachauswahl und die Anzeigegröße der Liste auf 3 gesetzt.

#####Definition
	select_sql|label|Bezeichnung:| select id,name from table order by name | [defaultvalue] | [no_db] |1/0 Leeroption|Leeroptionstext|1/0 Multiple Feld|selectsize	
#####Beispiel Formbuilder
	select_sql|kategorie|Kategorie|select id,name from rex_produkte_kategorie order by name |||1|keine Kategorie|1|3
	
#####Beispiel PHP
	$yform->setValueField('select_sql', array("kategorie","Kategorie","select id,name from rex_produkte_kategorie order by name", "", "", "1", "keine Kategorie", "1", "3"));

	
	
	
	
		
###!!showvalue

#####Definition
	showvalue|name|label|defaultwert
		
#####Beispiel Formbuilder
	_
	
#####Beispiel PHP
	-

	
	
	
	
	
###submit
definiert einen Submit-Button

#####Definition
	submit|label|value_on_button|[no_db]|cssclassname|[value_to_save_if_clicked]
	
#####Beispiel Formbuilder
	submit|send|Absenden|no_db|submit_bttn
	
#####Beispiel PHP
	$yform->setValueField('submit', array("send","Absenden","no_db","submit_bttn"));


	
	
	
	
	
###submits
definiert mehrere Submit-Buttons

#####Definition
	submit|label|labelvalue1_on_button1,labelvalue2_on_button2 | [value_1_to_save_if_clicked,value_2_to_save_if_clicked]|[no_db]|[Default-Wert]| [cssclassname1,cssclassname2]
	
#####Beispiel Formbuilder
	submits|send|abonnieren,cancel|1,0
	
#####Beispiel PHP
	$yform->setValueField('submits', array("send","abonnieren,cancel","1,0"));


	
	
	
	
	
###text
Ausgabe eines einzeiligen Eingabefeldes. Über ***default*** kann ein Wert angezeigt oder auch eine CSS-Klasse dem Feld übergeben werden.

#####Definition
	text|name|label|defaultwert|[no_db]|cssclassname	
#####Beispiel Formbuilder
	text|name|Name|Dein Name||f_name
	
#####Beispiel PHP
	$yform->setValueField('text', array("name","Name","Dein Name","", "f_name"));


	
	
	
	
	
###textarea
Ausgabe eines mehrzeiligen Eingabefeldes. Über ***default*** kann ein Wert angezeigt werden.

#####Definition
	textarea|name|label|default|[no_db]
	
#####Beispiel Formbuilder
	textarea|kommentar|Kommentar|Ihr Kommentar
	
#####Beispiel PHP
	$yform->setValueField('textarea', array("kommentar","Kommentar"));


	
	
	
	
	
###time
ermöglicht die Ausgabe zweier Auswahllisten, für die Stunden und Minuten, in den definierten Auswahl-Bereichen (Raster).

#####Definition
	time|name|label|[stundenraster 0,1,2,3,4,5]|[minutenraster 00,15,30,45]|[Anzeigeformat ###H###h ###I###m]|[no_db]
	
#####Beispiel Formbuilder
	time|uhrzeit|Uhrzeit|08,09,10,11,12|00,15,30,45|###H### ###m###

#####Beispiel PHP
	$yform->setValueField('time', array("uhrzeit", "Uhrzeit", "08,09,10,11,12", "00,15,30,45", "###H### ###m###"));


	
	
	
	
	
###!!uniqueform

#####Definition
	uniqueform|name|table|Fehlermeldung
	
#####Beispiel Formbuilder
	_
	
#####Beispiel PHP
	-

	
	
	
	
	
###!!upload

#####Definition
	upload|name | label | Maximale Größe in Kb oder Range 100,500 | endungenmitpunktmitkommasepariert | pflicht=1 | min_err,max_err,type_err,empty_err,delete_file_msg | Speichermodus(upload/database/no_save) | `database`: Dateiname wird gespeichert in Feldnamen | Eigener Uploadordner [optional] | Dateiprefix [optional] |
	
#####Beispiel Formbuilder
	_
	
#####Beispiel PHP
	-


	
	