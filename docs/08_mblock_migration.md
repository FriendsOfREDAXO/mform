# Migration von MBlock

MForm 10 unterstützt MBlock nicht mehr. Bestehende Module mit `MBlock::show($id, $mform->show())` laufen technisch weiter, weil MForm nur HTML liefert, sie werden aber nicht mehr getestet und in Issues nicht mehr supportet. Der Weg nach vorn ist der MForm-Repeater. Diese Seite beschreibt den Migrationsassistenten und die manuelle Migration.

1. Migrationsassistent (Backend und Konsole)
2. Manuelle Migration auf den MForm-Repeater

## 1) Migrationsassistent

Im Backend unter `MForm -> MBlock zu Repeater` führt ein Assistent in fünf Schritten durch die Migration. Dasselbe gibt es für große Installationen auf der Konsole (`mform:migrate`), dazu einen Linter (`mform:lint`).

### Die fünf Schritte

1. **Inventar.** Alle Module mit `MBlock::show()`: Anzahl Slices, erkannte Slots (`REX_VALUE[n]`), Feldtypen, ob die gespeicherten Werte wirklich MBlock-Marker tragen, und ein Risiko-Badge (grün: nur Umbenennungen, gelb: Legacy-Keys oder Optionen ohne Entsprechung, rot: HTML-/Heredoc-Formular, Verschachtelung oder Gridblock). „Analysieren“ lädt das Modul in Schritt 2.
2. **Code analysieren und konvertieren.** Der Analyzer liest jeden `MBlock::show()`-Aufruf, seinen Slot, die Formular-Variable, die Optionen und die Felder des Block-Formulars. Aus numerischen Widgets leitet er die **Legacy-Key-Map** ab: `addMediaField(1)` → `REX_MEDIA_1 => media`, `addMediaField(2)` → `media_2`, `addLinkField(3)` → `REX_LINK_3 => link_3`, Medialist/Linklist analog, `addCustomLinkField("$id.0.1")` → `1 => link`. Die Zielnamen sind in der Tabelle editierbar und gelten für den Eingabe-Code, die Ausgabe-Fallbacks und die Datenmigration. „Konvertieren“ liefert Eingabe- und Ausgabe-Code mit Hinweisen; beide Felder sind vorher editierbar.
3. **Konvertiertes Modul anlegen.** Kopie des Moduls mit dem konvertierten Code, Name und Key mit Präfix `mfr_<Zeitstempel>`. Das Original bleibt unverändert.
4. **Daten migrieren.** Probelauf über alle erkannten Slots (jede `valueN`-Spalte), danach Anwenden auf die ausgewählten Slices. Vor jedem Schreiben landet der alte Wert in `rex_mform_migration_backup`; alle Änderungen eines Durchgangs hängen an einem **Lauf-Token**. Die Liste „Migrationsläufe“ nimmt einen Lauf mit einem Klick zurück. Optional: einzelnen Rohwert testen, Gridblock-Spalten zusammenführen.
5. **Slices umhängen.** Slices des Originals auf die Kopie (vorbelegt), protokolliert in `rex_mform_migration_reassign_history`, Rückgängig per Token.

### Was der Konverter umschreibt

Eingabe:

- Feldnamen-Präfixe `"$id.0.feld"` / `'1.0.feld'` → `'feld'` (für alle Slots des Moduls, andere Slots wie `2.0.setting` bleiben)
- numerische Widgets im Block-Formular → Zielname aus der Key-Map (`addMediaField('media')`, `addCustomLinkField('link')`)
- `MBlock::show($id, $form->show(), [...])` und `MBlock::show($id, $form, [...])` → `MForm::factory()->addFlexRepeaterElement($id, $form, [...])->show()`
- `$blocks = MBlock::show(...)` + `->addHtml($blocks)` → `->addFlexRepeaterElement(...)` direkt im Formular-Baum
- Optionen: `min`, `max`, `copy_paste` werden übernommen; `online_offline`, `sortable`, `toggle`, `delete_confirm` entfallen (der Repeater hat das immer); unbekannte Optionen werden gemeldet
- Hidden-Feld `mblock_offline` und `use ...MBlock;` entfallen
- auskommentierter Code wird ignoriert

Ausgabe:

- `rex_var::toArray("REX_VALUE[n]")` → `MFormRepeaterHelper::decode(n)` für alle Repeater-Slots, andere Slots bleiben
- Zugriffe auf `$item['REX_MEDIA_1']`, `$item[1]` bekommen einen Fallback `($item['media'] ?? ($item['REX_MEDIA_1'] ?? ''))`

Daten:

- flaches MBlock-Array oder `{"GBS<hash>":{"VALUE":{"n":[...]}}}` → flaches Repeater-Array
- `checkbox_block_hold` entfällt, `mblock_offline == "1"` → `__disabled: true`
- Legacy-Keys werden über die Key-Map umbenannt
- verschachtelte Item-Listen mit MBlock-Markern werden rekursiv nach denselben Regeln konvertiert
- mehrere GBS-Wrapper (Gridblock-Spalten): standardmäßig nur die erste Spalte mit Warnung, mit „Spalten zusammenführen“ alle der Reihe nach

Daten, Listen und Sprachen:

- Medialist-/Linklist-/Bildlisten-Werte (kommasepariert) werden normalisiert (getrimmt, Leereinträge und Dubletten entfernt) und gegen Medienpool bzw. Struktur geprüft; fehlende Dateien oder Artikel erscheinen als Warnung je Slice. Welche Felder Listen sind, leitet der Analyzer aus dem Modul-Code ab.
- Mehrsprachige Werte, also ein Objekt mit Sprach-Ids als Schlüssel und MBlock-Listen als Werte, werden je Sprache konvertiert; die Struktur bleibt erhalten.

### YForm-Tabellen (Schritt 6)

Felder vom Wert-Typ `mblock` oder Text-Spalten, deren Werte MBlock-Marker tragen, listet Schritt 6 des Assistenten (bzw. `mform:migrate --yform`). Probelauf und Anwenden je Datensatz wie in Schritt 4, mit Backup unter einem Lauf-Token und Rückgängig in der Liste der Migrationsläufe. Die Spalte behält das konvertierte JSON (Repeater-Format), ein eigenes YForm-Value dafür gibt es nicht. Optional stellt der Assistent den Feldtyp von `mblock` auf `textarea` um, damit YForm kein MBlock-Widget mehr erwartet. Ein Key-Mapping muss hier von Hand angegeben werden, weil es keinen Modul-Code gibt.

### Grenzen

- HTML-/Heredoc-Formulare (`MBlock::show($id, $htmlString)`) werden erkannt, aber nicht umgeschrieben. Der Repeater braucht ein MForm-Objekt.
- Verschachteltes MBlock wird erkannt und die Daten rekursiv konvertiert; den inneren Repeater (`addFlexRepeaterElement('block', MForm::factory()...)`) musst du im konvertierten Code prüfen.
- Der Konverter erzeugt Vorschlagscode. Prüfe das Ergebnis vor dem Einsatz, teste auf Staging.

### Konsole

```bash
php bin/console mform:migrate                      # Inventar
php bin/console mform:migrate --module=12          # Analyse, Code-Hinweise, Dry-Run aller Slots
php bin/console mform:migrate --module=12 --show-code
php bin/console mform:migrate --module=12 --create-module
php bin/console mform:migrate --module=12 --apply  # schreibt mit Backup, gibt Lauf-Token aus
php bin/console mform:migrate --module=12 --reassign=57
php bin/console mform:migrate --rollback=TOKEN
php bin/console mform:migrate --revert-reassign=TOKEN   # oder "last"
php bin/console mform:migrate --runs
php bin/console mform:migrate --yform                       # YForm-Felder mit MBlock-Daten
php bin/console mform:migrate --yform=rex_news.blocks --map='{"REX_MEDIA_1":"media"}' [--apply] [--switch-type]
php bin/console mform:lint [--module=12] [--yform] [--severity=warning] [--json]
```

Weitere Optionen von `mform:migrate`: `--slot=1,3` (nur diese Slots), `--map='{"REX_MEDIA_1":"media"}'` (Key-Map überschreiben), `--merge-columns`, `--json`.

`mform:lint` meldet `MBlock::show()`, MBlock-`use`-Statements, `mblock_offline`-Hidden-Felder, numerische Widget-Ids und Präfix-Feldnamen in Repeater-Formularen sowie `rex_var::toArray()` für Repeater-Slots. Exit-Code 1 bei Fehlern, damit er in CI laufen kann.

## 2) Manuelle Migration

Wenn der Konverter nicht alle Besonderheiten deines Moduls abdecken kann, ist die manuelle Migration der zuverlässige Weg.

## Zielbild in MForm 9

- Wiederholende Inhalte laufen über `addRepeaterElement(...)`.
- Speicherung erfolgt als JSON in `REX_VALUE`.
- Der Aktiv/Inaktiv-Status pro Item wird intern über `__disabled` gespeichert.
- Für die Frontend-Ausgabe wird `MFormRepeaterHelper::decode()` genutzt.

### Schritt 1: Altmodul inventarisieren

Prüfe pro Modul:

- Welche Felder sind je MBlock-Item enthalten?
- Gibt es verschachtelte Blöcke?
- Welche alten Schlüssel werden verwendet (z. B. numerisch)?

Empfehlung: Im neuen Repeater sprechende Feldnamen einsetzen (`title`, `text`, `image`, `link`) statt rein numerischer Keys.

### Schritt 2: Eingabe auf Repeater umbauen

Beispiel für ein neues Repeater-Subformular:

```php
<?php
use FriendsOfRedaxo\MForm;

$itemForm = MForm::factory()
    ->addTextField('title', ['label' => 'Titel'])
    ->addTextAreaField('text', ['label' => 'Text'])
    ->addMediaField('image', ['label' => 'Bild'])
    ->addCustomLinkField('link', ['label' => 'Link']);

echo MForm::factory()
    ->addRepeaterElement(1, $itemForm, true, true, [
        'label' => 'Einträge',
        'btn_text' => 'Eintrag hinzufügen',
        'max' => 100,
        'copy_paste' => true,
    ])
    ->show();
```

### Schritt 3: Ausgabe auf Repeater-Helper umstellen

```php
<?php
use FriendsOfRedaxo\MForm\Repeater\MFormRepeaterHelper;
use FriendsOfRedaxo\MForm\Utils\MFormOutputHelper;

$items = MFormRepeaterHelper::decode(1);

foreach ($items as $item) {
    $title = (string) ($item['title'] ?? '');
    $text = (string) ($item['text'] ?? '');

    $link = MFormOutputHelper::createLinkData($item['link'] ?? '');

    echo '<h3>' . rex_escape($title) . '</h3>';
    echo '<div>' . $text . '</div>';

    if ('' !== $link['customlink_url']) {
        echo '<a href="' . rex_escape($link['customlink_url']) . '"'
            . $link['customlink_target']
            . '>' . rex_escape($link['customlink_text']) . '</a>';
    }
}
```

Hinweis: Link-Felder können je nach MForm-Version als String oder als Array vorliegen. `createLinkData()` normalisiert beide Varianten.

### Schritt 4: Bestehende Daten migrieren (optional)

Es gibt zwei Strategien:

1. Soft-Migration: neue Eingabe ab jetzt über Repeater, Alt-Datensätze bleiben bis zur Neuspeicherung.
2. Hart-Migration: einmalige Konvertierung alter Werte in das neue Repeater-JSON.

Empfehlung:

1. Erst Feldmapping pro Modul definieren.
2. Dann Dry-Run ausführen.
3. Danach mit Backup auf Staging testen.
4. Erst anschließend produktiv schreiben.

### Schritt 5: Checkliste nach der Migration

- Eingabe vollständig auf `addRepeaterElement(...)` umgestellt.
- Frontend-Ausgabe über `MFormRepeaterHelper::decode()` umgestellt.
- Link-Felder über `MFormOutputHelper::createLinkData()` oder `normalizeRepeaterItems()` normalisiert.
- Repeater-Aktionen getestet: Anlegen, Sortieren, Kopieren, Löschen, Speichern.
- Bei Nested-Strukturen auch verschachtelte Repeater getestet.
- Falls MBlock noch parallel läuft: `useCustomLinkForClassicWidgets` bewusst gesetzt oder bewusst nicht gesetzt.
