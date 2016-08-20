# Table Manager: Optionen
 
> ## Inhalte
> [Tabelle erstellen](#tabelle-erstellen)
> [Tabelle migrieren](#tabelle-migrieren)
> [Tableset importieren / exportieren](#tableset-importieren)
 
Um Tabellen im Table Manager zu bearbeiten, gibt es drei verschiedene Möglichkeiten:

* eine neue [Datenbank-Tabelle erstellen](#tabelle-erstellen).
* eine vorhandene Datenbank-Tabelle in den [Table Manager migrieren](#tabelle-migrieren)
* eine neue Datenbank-Tabelle einschließlich aller benötigten Felder anhand eines [Tablesets imporiteren](#tableset-importieren).

<a name="tabelle-erstellen"></a>
## Tabelle erstellen

So fügt man dem Table Manager eine neue Tabelle hinzu:

* Im Menü auf YForm klicken,
* Table Manager öffnen,
* über das +-Symbol eine neue Tabelle hinzufügen.

Anschließend können der Tabelle folgende Optionen zugewiesen werden:

Option | Erläuterungen
------ | ------
Priorität | Legt fest, an welcher Position sich die neue Tabelle zwischen bestehenden Tabellen einreiht, beispielsweise im Menü.
Name | Der Name der Datenbank-Tabelle, wie sie in MySQL heißt und über SQL-Querys aufgerufen wird.
Bezeichnung | Der Name der Tabelle, wie sie im Menü aufgelistet wird.
Beschreibung | Informationen zur Tabelle, zum Beispiel eine Kurzanleitung für den Kunden oder Informationen über den Aufbau der Tabelle als Merkhilfe. Die Beschreibung wird angezeigt beim direkten Aufruf einer Tabelle.
aktiv | Legt fest, ob die Tabelle bearbeitet werden kann oder nicht.
Datensätze pro Seite | Legt fest, ab wie vielen Datensätzen die Tabellen-Übersicht in Seiten unterteilt wird.
Standardsortierung Feld | Legt fest, nach welchem Feld die Tabellen-Übersicht zu Beginn sortiert wird.
Stadndardsortierung Richtung |  Legt fest, ob das gewählte Feld auf- oder absteigend sortiert wird.
Suche aktiv | Zeigt die Schaltfläche `Datensatz suchen` in der Tabellen-Übersicht an.
In der Navigation versteckt | Legt fest, ob die Tabelle auch im Menü angezeigt wird. oder nur im Table Manager. (Hilfreich, um beispielsweise relationale Datenbank-Tabellen auszublenden.)
Export der Daten erlauben | Zeigt die Schaltfläche `Datensätze exportieren` in der Tabellen-Übersicht an.
Import von Daten erlauben | [Zeigt die Schaltfläche `Datensätze importieren` in der Tabellen-Übersicht an.

> **Hinweis:**
> Solange die Tabelle über keine Felder verfügt, kann hier nur `id` ausgewählt werden. Man kann zunächst die Standard-Sortierung nach id-Feld belassen, dann neue Felder hinzufügen und anschließend die Sortierung der Tabelle neu festlegen. Zum Beispiel nach Name, Datum oder den selbst festgelegten Feldern.

> **Hinweis:** 
> Alternativ kann auch ein vorhandenes Tableset importiert werden.
In diesem Fall wird durch den abschließenden Klick auf `hinzufügen` die Datenbank-Tabelle erstellt.

> **Tipp:** 
> Wenn die Datenbank über Import/Export oder über einen Backup-Crobjob gesichert werden soll, sollte die Tabelle den Präfix `rex_` behalten. Zur besseren Übersicht empfiehlt es sich, der Tabelle einen eigenen Projekt-Präfix zu geben, z.B. `rex_kunde_projekte` oder `rex_kunde_mitarbeiter`.


<a name="tabelle-migrieren"></a>
## Tabelle migrieren

Der Migrationsmanager erstellt aus einer vorhandenen Tabelle eine, die über den Table Manager verwaltet werden kann. Dazu ist in der Tabelle ein Autoinkrement-Feld mit dem Namen `id` nötig. Ohne dieses Feld funktioniert der Tablemanager nicht.

So migrieren Sie eine vorhandene Tabelle in den Table Manager:

* Im Menü auf YForm klicken,
* Table Manager öffnen,
* den Button `Tabelle migrieren` anklicken,
* vorhandene Datenbank-Tabelle auswählen und
* mit `Abschicken` bestätigen.

> **Hinweis:** Falls die Datenbank-Tabelle über kein id-Feld verfügt, kann man die Option `id-Feld konvertieren falls nicht vorhanden` benutzen. Dabei wird jenes Feld in `id` umbenannt, das als `PRIMARY` und `AUTO_INCREMENT` eingetragen ist.

<a name="tableset-importieren"></a>
## Tableset importieren / exportieren

Seit Redaxo 5 gibt es eine neue Möglichkeit, eine Tabelle im Table Manager zu erstellen: den Import via Tableset (JSON).

1. Im Menü auf YForm klicken,
2. Table Manager öffnen,
3. die Schaltfläche `Tableset importieren` anklicken,
4. JSON-Datei auswählen und 
5. mit Abschicken bestätigen.

Anschließend wird die Tabelle einschließlich aller in der JSON-Datei hinterlegten Felder, Parameter und Standardwerten importiert.

Mit diesem Demo-Tablesets (JSON) kann man direkt starten:
- [Kontaktformular](demo_tableset-rex_yf_messages.json)
- [Mitarbeiter / Team](demo_tableset-rex_yf_staff.json)
- [Projekte / Referenzen](demo_tableset-rex_yf_projects.json)
