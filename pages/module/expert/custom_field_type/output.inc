<?php
use FriendsOfRedaxo\MForm\Repeater\MFormRepeaterHelper;
$v = (rex_var::toArray('REX_VALUE[1]') ?: [])[0] ?? [];
?>
<p>Bewertung: <?= str_repeat('★', (int) ($v['rating'] ?? 0)) ?> (<?= (int) ($v['rating'] ?? 0) ?>)</p>
<ul>
<?php foreach (MFormRepeaterHelper::decode(2) as $entry): ?>
    <li><?= rex_escape((string) ($entry['title'] ?? '')) ?>: <?= str_repeat('★', (int) ($entry['rating'] ?? 0)) ?></li>
<?php endforeach; ?>
</ul>
