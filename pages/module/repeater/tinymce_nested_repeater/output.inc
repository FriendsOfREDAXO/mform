<?php
$sections = \FriendsOfRedaxo\MForm\Repeater\MFormRepeaterHelper::decode('REX_VALUE[id=2]');
?>

<h1>REX_VALUE[1]</h1>

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
