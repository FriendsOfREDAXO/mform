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
<h1>REX_VALUE[1]</h1>

<ul>
    <?php foreach (REX_VALUE[2] as $item) : ?>
        <li><?php echo $item['item']; ?></li>
    <?php endforeach; ?>
</ul>
```
