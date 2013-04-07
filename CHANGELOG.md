## MForm Changelog

### 2.2.1-rc.1

* Ab dieser Version nur noch für Redaxo 4.5.x
* duplizieren der Lang Files entfernt
* `Undefined variable: strDefaultTemplateThemeName` Notice gefixt
* remove print_r

### 2.2.0-rc.6

* `fieldset`-Bug in `class.parse_mform.php` behoben
* `string`-error in `class.get_mform_array.php` gefixt
* `Undefined offset`-error in `class.get_mform_array.php` gefixt
* `Use of undefined constant int`-error in `class.get_mform_array.php` gefixt
* `unserialize(): Error` gefixt durch `isSerial` Methode
* `Use of undefined constant add`-error gefixt
* Korrekte RC-Version in `config.in.php` hinterlegt

### 2.2.0-rc.5

* Call `isAdmin()` error gefixt
* `string`-error gefixt

### 2.2.0-rc.4

* Mehrsprachigkeit für Labels hinzugefügt
* Mehrsprachigkeit für Default-Values hinzugefügt
* Mehrsprachigkeit für Headlines, Description, Fieldsets und HTML hinzugefügt
* Parsley auch beim Hinzufügen eines Moduls aktivieren
* Default-Values für Multiselect hinzugefügt

### 2.2.0-rc.3

* jQuery 1.8.3 für Rex v.4.4 hinzugefügt

### 2.2.0-rc.2

* Versions-Nummer in Addon-Headline integriert

### 2.2.0-rc.1

* Wenn Rex < 4.5.0 `duplicate` Lang Dateien für _utf8.lang
* Install nun ab Rex 4.4.0 möglich
* `method_exists` für VALUE-Arrays nur ab Rex 4.5.0 bereitzustellen

### 2.2.0

* `value[x][y]`-Array-Funktionalität integriert
* Element-ID als Count-ID nun unabhängig von `VALUE[id]`
* `classes` in `lib` umbenannt
* Diverse Dateinamen umbenannt, Dateiendung `.inc.php` wo möglich in `.php` umbenannt
* `a_967` aus Lib-Classes- und Funktionen-Namen entfernt
* Ordner `extensions` entfernt, Extension-Function in Ordner `functions` verschoben und umbenannt
* `getMFormArray` Classe um `getRexVars` Methode erweitert, die REX_VALUE[x]-Übergabe entfällt hierdurch
* `LICENSE.md` hinzugefügt
* `_changelog.txt` in `CHANGELOG.md` umgewandelt
* `README.textile` in `README.md` umbenannt
* `_utf8.lang` Dateien in `.lang` umbenannt
* ISO Lang Dateien entfernt
* Settings-übernahme wird nun sofort im Select berücksichtigt

### 2.1.4-rc.4

* `$arrElement[default]` in `$arrElement[value]` umbenannt
* `default-value` hinzugefügt

### 2.1.4-rc.3

* Callback Methode integriert für `callabel`-Aufrufe

### 2.1.4-rc.2

* Checkboxen und Radiobuttons verarbeiten nun auch Attribute
* CSS wird über die index.php aufgerufen, was die `.htaccess` im Ordner `templates` einspart
  
### 2.1.4

* Default Validation Vorbereitung hinzugefügt
* Custom Validation ansatzweise vorbereitet
* neue Methode addFieldset hinzugefügt
* Code etwas aufgeräumt
* Default Div Layer Theme fertiggestellt

### 2.1.3

* Template System erweitert
* addHtml optimiert, dass HTML ohne Template-Datei ausgegeben wird
* diverse Code-Optimierungen
* alle unnötigen und alten Funktionen entfernt
* Output Extension so modifiziert das Default-Template CSS Datei geladen wird
* Template Dateien werden direkt aus dem Template-Ordner geladen entsprechende `.htaccess` hinzugefügt
* Ordner `files` entfernt
* Settings Formular für Themeauswahl integriert

### 2.1.2

* diverse Bugfixes
* Fehler welche PHP Warnings und Notices verursachten entfernt
* Nicht valide Tags aus dem HTML Output entfernt

### 1.2 (R4 Compatible)

* initial release