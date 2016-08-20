# YForm: Einführung

## Formulare im Frontend

Das AddOn YForm dient vor allem zur Generierung von Formularen im Frontend. Formulare sind oft komplex und ziehen meist umfangreiche Nacharbeit mit sich. YForm versucht, durch flexible Verzahnung verschiedener Komponenten möglichst viele dieser Aufgaben zu übernehmen.

YForm enthält nicht nur alle gängigen Formular-Feldtypen, sondern stellt auch vielfältige Validierungen bereit, Funktionen zum Versand von E-Mails sowie Aktionen, die zum Beispiel Daten in eine Datenbank schreiben oder Weiterleitungen ausführen.

Dazu installiert YFom ein Modul namens `YForm-Formbuilder`. Nach einer allgemeinen Einführung in den [Formbuilder](yform_modul_allgemein.md) werden die zahlreichen Optionen aller [Values](yform_modul_values.md), [Validates](yform_modul_validates.md) und [Actions](yform_modul_actions.md) erklärt. Auch [allgemeine Formular-Paramater](yform_modul_objparams.md) und die [Verzeichnisstruktur](yform_modul_struktur.md) werden behandelt.

Das Erstellen von [E-Mail-Templates](email_templates.md) wird in einem eigenen Kapitel beschrieben.

## Tabellenverwaltung im Backend

YForm kann aber nicht nur Formulare für das Frontend generieren sowie Formulareingaben per E-Mail versenden oder in eine Datenbank speichern.

Der Admin kann mit Hilfe des Table Managers auch Datenbank-Tabellen "zusammenklicken" und diese - ergänzt z.B. durch Validierungen - im Backend samt Eingabemaske zur Verfügung stellen. Diese automatisch erzeugten Daten-Verwaltungen können dann wiederum den Code für ein dazu passendes Frontend-Formular generieren.

Nach einer Einführung in das [Grundprinzip](table_manager_grundprinzip.md) werden die [Tabellenoptionen](table_manager_optionen.md) sowie [Feldtypen](table_manager_feldtypen.md) ausfürlich abgehandelt. Auch die [Feld-Validierung](table_manager_validierungen.md) und die [Verknüpfung von Tabellen](table_manager_feldtypen_be-relation.md) kommen zur Sprache.

> **Hinweis:**  
> Die Dokumentation ist noch nicht ganz fertig, wird aber im Laufe der kommenden Wochen fertiggestellt.
> 
> Diese Dokumentation wird auf Github gepflegt:  
> https://github.com/yakamara/redaxo_yform_docs
> Ergänzungen oder Korrekturen bitte am besten direkt dort als Issue oder Pull request erstellen.