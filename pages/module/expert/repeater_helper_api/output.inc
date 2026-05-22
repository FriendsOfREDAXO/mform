<?php

use FriendsOfRedaxo\MForm\Repeater\MFormRepeaterHelper;

// REX_VALUE[id=1] enthält alle Beiträge als JSON-Repeater-String

// 1. Dekodieren – deaktivierte Items (__disabled) werden automatisch herausgefiltert,
//    wenn filterEnabledItems() aufgerufen wird.
$allItems = MFormRepeaterHelper::decode(1);

// 2. Nur aktive Items (Auge-Icon im Backend = aktiv)
$items = MFormRepeaterHelper::filterEnabledItems($allItems);

// 3. Nur veröffentlichte Beiträge
$published = MFormRepeaterHelper::filterByField($items, 'status', 'published');

// 4. Nach Priorität absteigend sortieren (numerisch automatisch erkannt)
$sorted = MFormRepeaterHelper::sortByField($published, 'priority', 'desc');

// 5. Die Top-5 für die Startseite
$topFive = MFormRepeaterHelper::limitItems($sorted, 5);

// 6. Alle aktiven nach Kategorie gruppieren (z. B. für eine Übersichtsseite)
$grouped = MFormRepeaterHelper::groupByField($items, 'category');

// --- Ausgabe: Top-5-Liste ---
if ([] !== $topFive):
?>
<section class="repeater-demo">
    <h2>Top 5 Beiträge</h2>
    <ul class="article-list">
        <?php foreach ($topFive as $item): ?>
        <li class="article-list__item">
            <strong><?= rex_escape((string) ($item['title'] ?? '')) ?></strong>
            <span class="badge"><?= rex_escape((string) ($item['category'] ?? '')) ?></span>
            <span class="priority">Priorität: <?= (int) ($item['priority'] ?? 0) ?></span>
            <?php if (!empty($item['teaser'])): ?>
            <p><?= nl2br(rex_escape((string) $item['teaser'])) ?></p>
            <?php endif; ?>
        </li>
        <?php endforeach; ?>
    </ul>
</section>
<?php endif; ?>

<?php
// --- Ausgabe: Nach Kategorie gruppiert ---
if ([] !== $grouped):
?>
<section class="repeater-demo repeater-demo--grouped">
    <h2>Alle Beiträge nach Kategorie</h2>
    <?php foreach ($grouped as $category => $categoryItems): ?>
    <div class="category-group">
        <h3><?= rex_escape((string) $category) ?> (<?= count($categoryItems) ?>)</h3>
        <ul>
            <?php foreach ($categoryItems as $item): ?>
            <li>
                <?= rex_escape((string) ($item['title'] ?? '')) ?>
                – <?= rex_escape((string) ($item['author'] ?? '')) ?>
                <em>(<?= rex_escape((string) ($item['status'] ?? '')) ?>)</em>
            </li>
            <?php endforeach; ?>
        </ul>
    </div>
    <?php endforeach; ?>
</section>
<?php endif; ?>

<?php
// --- Debug: Statistiken ---
?>
<p class="text-muted small">
    Gesamt: <?= count($allItems) ?> |
    Aktiv: <?= count($items) ?> |
    Veröffentlicht: <?= count($published) ?> |
    Kategorien: <?= count($grouped) ?>
</p>
