# Table Manager: Validierungen

> ## Inhalt
> - [compare](#compare)
> - [compare_value](#compare_value)
> - [customfunction](#customfunction)
> - [email](#email)
> - [empty](#empty)
> - [intfromto](#intfromto)
> - [size](#size)
> - [size_range](#size_range)
> - [type](#type)
> - [unique](#unique)


<a name="compare"></a>
## compare

Vergleicht zwei Eingabe-Werte <b>miteinander</b>.

Option | Erläuterung
------ | ------
Priorität | Reihenfolge des Feldes in der Feldübersicht und beim Abarbeiten der Validierungen.
1. Feldname | Name des Tabellenfeldes, das für die Überprüfung herangezogen wird, z.B. `password`, `email`
2. Feldname | Name des Tabellenfeldes, das für die Überprüfung herangezogen wird, z.B. `password2`, `email_verified`
Vergleichsart | Operator, wie `Feld 1` und `Feld 2` verglichen werden sollen, z.B. `!=`, `!=`, `>`, `<` 
Fehlermeldung | Hinweis, der erscheint, wenn der Vergleich beider Felder `false` ergibt.

> Tipp: Diese Validierung kann z. B. bei Online-Tarifrechnern oder Ähnlichem eingesetzt werden, um serverseitig unzulässige Konfigurationen durch den Nutzer auszuschließen.

<a name="compare_value"></a>
## compare_value

Vergleicht einen Eingabe-Wert mit einem <b>fest definierten Wert</b>.

Option | Erläuterung
------ | ------
Priorität | Reihenfolge des Feldes in der Feldübersicht und beim Abarbeiten der Validierungen.
1. Feldname | Name des Tabellenfeldes, das für die Überprüfung herangezogen wird, z.B. `checkbox_agb`, `newsletter_consent`
Vergleichswert | Fest definierter Wert, der für den Vergleich herangezogen wird, z.B. `1` (bei Checkboxen) 
Vergleichsart |  Operator, wie `Feld 1` und `Vergleichswert` vergleichen werden sollen, bspw. `!=`, `!=`, `>`, `<` 
Fehlermeldung | Hinweis, der erscheint, wenn die Überprüfung `false` ergibt.

<a name="customfunction"></a>
## customfunction

Ruft eine eigene <b>PHP-Funktion</b> für einen Vergleich auf.

Option | Erläuterung
------ | ------
Priorität | Reihenfolge des Feldes in der Feldübersicht und beim Abarbeiten der Validierungen.
Name | Name des Tabellenfeldes, das für die Überprüfung herangezogen wird, z.B. `name`, `email`, `phone`, `zip`
Name der Funktion | Funktion, die den Wert überprüfen soll
Weitere Parameter |
Fehlermeldung | Hinweis, der erscheint, wenn die Funktion `false` als Rückgabewert liefert.

<a name="email"></a>
## email

Überprüft, ob der Eingabe-Typ eine <b>E-Mail-Adresse</b> ist.

Option | Erläuterung
------ | ------
Priorität | Reihenfolge des Feldes in der Feldübersicht und beim Abarbeiten der Validierungen.
Name |  Name des Tabellenfeldes, das für die Überprüfung herangezogen wird, z.B. `email`, `contact`
Fehlermeldung | Hinweis, der erscheint, wenn keine gültige E-Mail-Adresse angegeben wurde.

> Hinweis: Falls das E-Mail-Feld ein Pflichtfeld ist, muss auch die Validierung `empty` hinzugefügt werden, da ein leeres Feld eine keine ungültige E-Mail-Adresse darstellt.

> Hinweis: Die Validierung ist (noch) nicht RFC-konform, sondern wird nach dem Regex-Schema `"#^[\w.+-]{2,}\@\w[\w.-]*.\w+$#u";` überprüft. 

<a name="empty"></a>
## empty

Überprüft, ob ein Eingabe-Wert <b>vorhanden</b> ist.

Option | Erläuterung
------ | ------
Priorität | Reihenfolge des Feldes in der Feldübersicht und beim Abarbeiten der Validierungen.
Name |  Name des Tabellenfeldes, das für die Überprüfung herangezogen wird, z.B. `email`, `name`
Fehlermeldung | Hinweis, der erscheint, wenn die Eingabe leer ist.

<a name="intfromto"></a>
## intfromto

Überprüft, ob der Eingabe-Wert <b>zwischen zwei Zahlen</b> liegt.

Option | Erläuterung
------ | ------
Priorität | Reihenfolge des Feldes in der Feldübersicht und beim Abarbeiten der Validierungen.
Name |  Name des Tabellenfeldes, das für die Überprüfung herangezogen wird, z.B. `email`, `name`
Von | Wert, der mindestens eingegeben werden muss, z.B. `0`, `5`, `1000`
Bis | Wert, der höchstens eingegeben werden darf, z.B. `5`,`10`,`2030`
Fehlermeldung | Hinweis, der erscheint, wenn die Eingabe nicht im erlaubten Bereich liegt.

<a name="size"></a>
## size

Überprüft, ob der Eingabe-Wert eine <b>bestimmte Anzahl von Zeichen</b> hat.

Option | Erläuterung
------ | ------
Priorität | Reihenfolge des Feldes in der Feldübersicht und beim Abarbeiten der Validierungen.
Name |  Name des Tabellenfeldes, das für die Überprüfung herangezogen wird, z.B. `customer_id`, `pin`
Anzahl der Zeichen | Anzahl der Zeichen, die eingegeben werden sollen, z.B. `5`,`10`,`42`
Fehlermeldung | Hinweis, der erscheint, wenn die Eingabe die festgelegte Anzahl von Zeichen unter- oder überschreitet.

<a name="size_range"></a>
## size_range

Überprüft, ob die <b>Länge</b> des Eingabe-Werts <b>zwischen zwei Zahlen</b> liegt.

Option | Erläuterung
------ | ------
Priorität | Reihenfolge des Feldes in der Feldübersicht und beim Abarbeiten der Validierungen.
Name |  Name des Tabellenfeldes, das für die Überprüfung herangezogen wird, z.B. `customer_id`, `password`
Minimale Anzahl der Zeichen (opt) | Anzahl der Zeichen, die mindestens werden sollen, z.B. `0`, `5`, `7`
Maximale Anzahl der Zeichen (opt) | Anzahl der Zeichen, die höchstens werden sollen, z.B. `5`,`10`,`15`
Fehlermeldung | Hinweis, der erscheint, wenn die Eingabe den festgelegten Bereich an Zeichen unter- oder überschreitet.

<a name="type"></a>
## type

Überprüft, ob <b>der Typ</b> des Eingabe-Werts korrekt ist.

Option | Erläuterung
------ | ------
Priorität | Reihenfolge des Feldes in der Feldübersicht und beim Abarbeiten der Validierungen.
Name |  Name des Tabellenfeldes, das für die Überprüfung herangezogen wird, z.B. `zip`, `phone`, `name`, `email`, `website`
Prüfung nach | Typ, der überprüft werden soll, z.B. `int`, `float`, `numeric`, `string`, `email`, `url`, `date`, `hex`]
Fehlermeldung | Hinweis, der erscheint, wenn die Eingabe nicht dem festgelegten Typ entspricht.
Feld muss nicht ausgefüllt werden | Gibt an, ob die Validierung erfolgreich ist, wenn keine Eingabe stattfindet. 

<a name="unique"></a>
## unique

Überprüft ob der Eingabe-Wert <b>noch nicht in anderen Datensätzen vorhanden</b> ist.

Option | Erläuterung
------ | ------
Priorität | Reihenfolge des Feldes in der Feldübersicht und beim Abarbeiten der Validierungen.
Names |  Namen der Tabellenfelder, die für die Überprüfung herangezogen werden, z.B. `id`, `customer_id`, `email,email_verified`
Fehlermeldung | Hinweis, der erscheint, wenn die Eingabe bereits in einem anderen Datensatz existiert.
Tabelle [opt] | Name der Tabelle, in der die Felder durchsucht werden.
