# Repeater

Das Repeater-Feld ermöglicht es Ihnen, eine Gruppe von Feldern zu erstellen, die beliebig oft wiederholt werden können. Das ist z.B. bei sich wiederholenden Informationen wie Ansprechpersonen, Leistungen und Layouts wie Tabellen, Listen, mehrspaltige Inhalte oder Reiter sinnvoll.

Repeater ist keine 1:1-Übernahme von MBlock, sondern ist ein neuer, moderner Ansatz, um wiederholende Inhalte zu erstellen. Es ist ein eigenständiges Element, das in MForm integriert ist.

## Beispiele

### Eingabe-Modul

```php
<?php
use FriendsOfRedaxo\MForm;

$formtorepeat = MForm::factory();
$formtorepeat->addFieldsetArea('fieldset1', MForm::factory()
->addTextField('item', ['label' => 'List-Item'])
);

$mform = MForm::factory();
$mform->addTextField(1, ['label' => 'Headline']);
$mform->addRepeaterElement(2, $formtorepeat);

echo $mform->show();
```

### Ausgabe-Modul

```php
<?php
use FriendsOfRedaxo\MForm\Repeater\MFormRepeaterHelper;

$items = rex_var::toArray('REX_VALUE[2]');
$items = MFormRepeaterHelper::prepareItemsForOutput($items);
?>

<h1>REX_VALUE[1]</h1>

<ul>
    <?php foreach ($items as $item) : ?>
        <li><?php echo $item['item']; ?></li>
    <?php endforeach; ?>
</ul>
```

---

## Nested Repeater mit TinyMCE

Repeater lassen sich verschachteln (Level 1 + Level 2). TinyMCE kann auf beiden Ebenen verwendet werden – der Repeater behandelt Destroy/Reinit automatisch beim Verschieben und Löschen.

### Eingabe-Modul

```php
<?php
use FriendsOfRedaxo\MForm;

// Level-2-Formular: Textabschnitte je Schritt
$stepForm = MForm::factory()
    ->addTextField('title', ['label' => 'Schritt-Titel'])
    ->addTextAreaField('body', ['label' => 'Inhalt (TinyMCE)', 'class' => 'tiny-editor', 'data-profile' => 'default'])
;

// Level-1-Formular: Abschnitte mit eingebettetem Level-2-Repeater
$sectionForm = MForm::factory()
    ->addTextField('section_title', ['label' => 'Abschnitts-Titel'])
    ->addTextAreaField('intro', ['label' => 'Einleitung (TinyMCE)', 'class' => 'tiny-editor', 'data-profile' => 'default'])
    ->addRepeaterElement('steps', $stepForm, true, true, [
        'label'              => 'Schritte',
        'btn_text'           => 'Schritt hinzufügen',
        'confirm_delete'     => true,
        'confirm_delete_msg' => 'Schritt wirklich entfernen?',
        'min'                => 1,
        'max'                => 10,
    ])
;

$mform = MForm::factory()
    ->addTextField(1, ['label' => 'Seitentitel'])
    ->addRepeaterElement(2, $sectionForm, true, true, [
        'label'              => 'Abschnitte',
        'btn_text'           => 'Abschnitt hinzufügen',
        'confirm_delete'     => true,
        'confirm_delete_msg' => 'Abschnitt wirklich entfernen?',
        'collapsed'          => true,
        'first_open'         => true,
        'show_toggle_all'    => true,
    ])
;

echo $mform->show();
```

### Ausgabe-Modul

```php
<?php
use FriendsOfRedaxo\MForm\Repeater\MFormRepeaterHelper;

$sections = rex_var::toArray('REX_VALUE[2]');
$sections = MFormRepeaterHelper::prepareItemsForOutput($sections);
?>

<h1><?= rex_escape('REX_VALUE[1]') ?></h1>

<?php foreach ($sections as $section) : ?>
<section>
    <h2><?= rex_escape($section['section_title'] ?? '') ?></h2>

    <?php if (!empty($section['intro'])) : ?>
        <div class="intro"><?= $section['intro'] ?></div>
    <?php endif; ?>

    <?php if (!empty($section['steps'])) : ?>
    <ol>
        <?php foreach ($section['steps'] as $step) : ?>
        <li>
            <h3><?= rex_escape($step['title'] ?? '') ?></h3>
            <?php if (!empty($step['body'])) : ?>
                <div class="step-body"><?= $step['body'] ?></div>
            <?php endif; ?>
        </li>
        <?php endforeach; ?>
    </ol>
    <?php endif; ?>
</section>
<?php endforeach; ?>
```

### Hinweise zu TinyMCE

- Die Textarea muss die CSS-Klasse `tiny-editor` erhalten, damit das TinyMCE-Addon sie erkennt.
- `data-profile` gibt das TinyMCE-Profil an (Standard: `default`).
- Beim Drag-and-Drop sowie beim Klick auf „Nach oben / Nach unten" wird TinyMCE automatisch korrekt destroyed und reinitialisiert – kein manueller Eingriff nötig.
- TinyMCE-Inhalte werden vor jedem DOM-Move in die Textarea zurückgeschrieben und nach der Neuinitialisierung wieder geladen.

### Standardverhalten und Optionen

- Repeater-Items sind standardmäßig reduziert, das erste Item bleibt geöffnet.
- Im Header jedes Items gibt es einen "Danach hinzufügen"-Button.
- Im Header jedes Items gibt es ein Auge-Icon zum Aktivieren/Deaktivieren für die Ausgabe.
- Ist ein Item deaktiviert, bleibt es im Backend editierbar, wird aber in der Ausgabe über `MFormRepeaterHelper::prepareItemsForOutput()` entfernt.
- Im Repeater-Toolbar gibt es optional einen "Alle auf / zu"-Button.

### Aktiv/Inaktiv (Auge) und Ausgabe

- Das Auge-Icon steuert den Status pro Item (aktiv/inaktiv).
- Inaktive Items werden als Metadaten mit dem Schlüssel `__disabled` im Repeater-JSON gespeichert.
- Für die Ausgabe sollte der Repeater-Array immer über `MFormRepeaterHelper::prepareItemsForOutput()` laufen.
- Die Methode entfernt inaktive Items rekursiv (auch in nested Repeatern) und entfernt den Metaschlüssel aus der Ausgabe.

Verfügbare Optionen im Repeater-Array:

- `collapsed` (bool, default: `true`): Initial alle Items reduziert anzeigen.
- `first_open` (bool, default: `true`): Erstes Item trotz `collapsed=true` geöffnet lassen.
- `show_toggle_all` (bool, default: `true`): Button "Alle auf / zu" in der Toolbar anzeigen.

### Sortable-Kompatibilität

- MForm lädt SortableJS nur, wenn `window.Sortable` noch nicht vorhanden ist.
- Wenn ein anderes Addon Sortable bereits global bereitstellt, wird diese Instanz verwendet.
