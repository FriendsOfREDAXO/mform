# Table Manager: Grundprinzip

> ## Inhalt
> - [Erste Schritte](#erste-schritte)
> - [Aufbau des Table Manager](#aufbau)
> - [Zweck des Table Manager](#zweck)
> - [Ausgabe der Table Manager-Daten im Frontend](#ausgabe)
> - [Backups der Table Manager-Tabellen](#backups)
> - [Für Entwickler: Table Manager erweitern](#erweitern)

Der Table Manager in YForm dient zum Erstellen und Bearbeiten von Datenbanktabellen sowie zum Verwalten von tabellarischen Daten innerhalb von Redaxo.

> Hinweis: Der Table Manager ist nicht für den Zugriff aller Redaxo-Datenbanktabellen, bspw. `rex_article` gedacht. Um direkt auf die Tabellen einer Redaxo-Installation zuzugreifen, gibt es das [Adminer-Addon von Friends Of Redaxo](https://github.com/FriendsOfREDAXO/adminer). Adminer ist wie PHPMyAdmin eine Webanwendung zur Administration von Datenbanken.

<a name="erste schritte"></a>
## Erste Schritte

Im Wesentlichen sind folgende Schritte notwendig, um mit dem Table Manager zu starten:

1. [Tabelle im Table Manager erstellen](table_manager_optionen.md).
2. [Felder der neuen Tabelle hinzufügen](table_manager_feldtypen.md). 
3. Datensätze in die Felder der neuen Tabelle eintragen.

> Tipp für Neulinge: Alternativ zu Schritt 1 und 2 kann auch ein vorkonfiguriertes Tableset importiert werden, das bereits Tabelle und Felder enthält. Eine Anleitung mit Muster-Tablesets für Kontaktformular, Team und Projekte gibt es im Abschnitt [Tableset importieren](table_manager_optionen.md#tableset-importieren)

<a name="aufbau"></a>
## Aufbau des Table Manager

Der Table Manager wird im Menü über `Addons > YForm > Table Manager` aufgerufen. Dort lassen sich Tabellen und deren Eingabe-Felder hinzufügen. Alle Tabellen, die auf `aktiv` gestellt sind, werden im Menü über `Tabellen > Name_Der_Tabelle` erreicht. Dort lassen sich die Datensätze der jeweiligen Tabelle bearbeiten.

<a name="zweck"></a>
## Zweck des Table Manager

Der Table Manager wird üblicherweise dann eingesetzt, wenn in Redaxo tabellarische Daten erzeugt, verwaltet oder aufgezeichnet werden müssen, zum Beispiel:

* Archivierung aller Anfragen eines Kontaktformulars
* Archivierung aller Bestellungen eines Bestellformulars
* Verwaltung von Kursen, Terminen und Verstaltungen
* Verwaltung von News einschließlich zugehöriger Eigenschaften (Kategorien, Tags, ...)
* Verwaltung von Produkten einschließlich zugehöriger Eigenschaften (Größen, Preis, ...) 

Außerdem kann der Table Manager anhand einer Table Manager-Tabelle den Formular-Code 
- für das YForm Formbuilder-Modul erzeugen, 
- für die PHP-Variante von YForm vorbereiten und
- die Feld-Platzhalter für ein E-Mail-Template auflisten.

Die Daten können dann in Modulen und Addons im Frontend und Backend verwendet werden.

<a name="ausgabe"></a>
## Ausgabe der Table Manager-Daten im Frontend

Daten im Table Manager werden in der SQL-Datenbank abgelegt, die bei der Redaxo-Installation angegeben wurde. Die einfachste Möglichkeit ist daher, über das [rex_sql-Objekt](https://github.com/redaxo/redaxo/wiki/Aenderungen-in-REDAXO-5#rex_sql) die Daten auszulesen.

> Tipp: Um beispielsweise jede News oder jedes Produkt statt via GET-Parameter über eine eigene URL aufzurufen, obwohl kein eigener Artikel existiert, kann das [URL-Addon von Thomas Blum](https://github.com/tbaddade/redaxo_url) verwendet werden.


<a name="backups"></a>
## Backups der Table Manager-Tabellen

Datensätze können manuell exportiert werden, sofern die Tabelle im Table Manager konfiguriert ist. Außerdem lässt sich über das Cronjob-Addon ein regelmäßiger Datenbank-Export einrichten.

Und es gibt ein neues History-Plugin, mit dem Änderungen an Datensätzen nachverfolgt werden können.

<a name="erweitern"></a>
## Für Entwickler: Table Manager erweitern

Es ist möglich, eigene Feldtypen zu definieren und dem Table Manager hinzuzufügen. Das Geo-Plugin für YForm bspw. ist eine Möglichkeit, sich mit der Erweiterung von YForm und dem Table Manager vertraut zu machen.
