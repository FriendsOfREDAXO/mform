# Modul Formulare erzeugen


MForm erzeugt Modul-Formulare mit Hilfe der entsprechenden `mform` Klasse, diese benötigt ein Formular-Objekt. Zudem müssen entsprechend Formular-Elemente durch Aufruf der jeweiligen Methoden erzeugt werden. Letztlich wird dann das Formular geparst und kann via `print` oder `echo` ausgegeben werden.


### MForm Objekt-Initialisierung


Ähnlich wie z.B. `rex_form` muss auch für MForm ein Objekt initialisiert werden.


`$objForm = new MForm();`

### MForm Element-Erzeugung


Nach dem das Formular-Objekt initialisiert wurde können durch Aufruf der jeweiligen Element-Methoden Elemente erzeugt werden.

Ab Version 2.2.0 benötigt MForm keine `REX_VALUE[x]`-Übergabe mehr. Da MForm ab der Version 2.2.0 die ab Redaxo 4.5 möglichen `REX_VALUE-ARRAYs` unterstütz gibt es praktisch keine `REX_VALUE`-Limitierung mehr. Wer ein `REX_VALUE` als Array nutzen möchte muss an die `REX_VALUE_ID` den Array-Key Punkt-getrennt anhängen. Die Array-Keys müssen numerisch sein.


`$objForm->addTextField(1.0,array('label'=>'Label Name','style'=>'width:200px'));`

### MForm Formular-Parsing und Anzeige


Wurden alle Elemente definiert muss final das Formular geparst und angezeigt werden.


`echo $objForm->show();`


***


### Beispiel Modul-Input


###### MForm Modul-Input-Code:


```php
<?php
  $objForm = new MForm();
  $objForm->addTextField(1.0,array('label'=>'Label Name','style'=>'width:200px'));
  echo $objForm->show();
?>
```

###### Generiertes Modul-Input-Formular aus obigem Code-Beispiel:


```php
  <table class="rex-module">
    <tr class="default">
      <th>
        <label for="rv1">Label Name</label>
      </th>
      <td>
        <input id="rv1" type="text" name="VALUE[1][0]" value=""  style="width:200px" />
      </td>
    </tr>
  </table>
```

###### Hinweis:

Bei diesem Code Beispiel handelt es sich um den Output mit dem "Tabellen"-Thema des Addons. Es ist auch möglich ein eigenes Thema anzulegen mit einem auf die eigene Bedürfnisse abgestimmten HTML-Output.