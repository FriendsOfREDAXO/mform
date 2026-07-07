<?php

/**
 * Renderer-Paritaet: Parser vs. Flex-Repeater (Smoke-Checks).
 *
 * Diese Seite hilft bei der visuellen und technischen Gegenprobe,
 * damit Wrapper-Fixes nicht in nur einem Renderpfad landen.
 */

use FriendsOfRedaxo\MForm;
use FriendsOfRedaxo\MForm\FlexRepeater\MFormFlexRepeaterRenderer;

$fragment = new rex_fragment();
$fragment->setVar('title', rex_i18n::msg('mform_info'), false);
$fragment->setVar(
    'body',
    '<p>'
    . rex_i18n::msg('mform_demo_renderer_parity_description')
    . '</p>',
    false,
);
echo $fragment->parse('core/page/section.php');

$buildForm = static function (): MForm {
    return MForm::factory()
        ->addTextField(1.1, ['label' => 'Titel'])
            ->setTooltipInfo('Tooltip-Test')
        ->addColumnElement(6, MForm::factory()->addTextField(1.2, ['label' => 'Spalte A']), [
            'data-group-column-row-class' => 'parity-column-row',
            'data-group-row-class' => 'parity-row',
        ])
        ->addColumnElement(6, MForm::factory()->addTextField(1.3, ['label' => 'Spalte B']))
        ->addModalElement(
            'Modal-Konfiguration',
            MForm::factory()->addTextField(1.4, ['label' => 'Im Modal']),
            'btn-info',
            'right',
            [
                'data-modal-row-class' => 'parity-modal-row',
                'data-group-row-class' => 'parity-modal-group-row',
            ],
        )
        ->addTextField(1.5, ['label' => 'Volle Breite'])
            ->setFull()
        ->addInputField('datetime', 1.8, ['label' => 'Datum/Zeit'])
        ->addInputField('password', 1.11, ['label' => 'Passwort'])
        ->addTextField(1.9, ['label' => 'Text mit Datalist'])
            ->setOptions([
                'Alpha',
                'Beta',
            ])
        ->addInputField('markitup', 1.10, ['label' => 'Markitup Fallback'])
        ->addTabElement(
            'Tab Inhalt',
            MForm::factory()->addTextField(1.6, ['label' => 'Tab Feld A']),
            true,
            false,
            [
                'tab-icon' => 'fa-file-text-o',
                'nav-class' => 'parity-tab-nav',
                'data-group-tab-style' => 'modern',
                'data-group-tab-layout' => 'vertical',
            ],
        )
        ->addTabElement(
            'Tab Meta',
            MForm::factory()->addTextField(1.7, ['label' => 'Tab Feld B']),
            false,
            true,
            [
                'tab-icon' => 'fa-cog',
            ],
        );
};

$parserForm = $buildForm();
$flexForm = $buildForm();

$parserHtml = $parserForm->show();
$flexHtml = MFormFlexRepeaterRenderer::renderTemplate($flexForm, 1);

$checks = [
    [
        'label' => 'Tooltip-Icon gerendert',
        'needle' => 'mblock-info-tooltip',
    ],
    [
        'label' => 'Tooltip-Default-Icon (info-circle) aktiv',
        'needle' => 'fa-info-circle',
    ],
    [
        'label' => 'Column-Row-Klasse uebernommen',
        'needle' => 'parity-column-row',
    ],
    [
        'label' => 'Generische Row-Klasse uebernommen',
        'needle' => 'parity-row',
    ],
    [
        'label' => 'Modal-Row-Klasse uebernommen',
        'needle' => 'parity-modal-row',
    ],
    [
        'label' => 'Modal-Group-Row-Klasse uebernommen',
        'needle' => 'parity-modal-group-row',
    ],
    [
        'label' => 'Full-Layout-Klasse vorhanden',
        'needle' => 'col-sm-12',
    ],
    [
        'label' => 'Tab-Nav Zusatzklasse uebernommen',
        'needle' => 'parity-tab-nav',
    ],
    [
        'label' => 'Tab-Layout vertikal Klasse vorhanden',
        'needle' => 'mform-tabs--vertical',
    ],
    [
        'label' => 'Tab-Style modern Klasse vorhanden',
        'needle' => 'mform-tabs--modern',
    ],
    [
        'label' => 'Tab Pull-Right Klasse vorhanden',
        'needle' => 'pull-right',
    ],
    [
        'label' => 'Tab Active-Markierung vorhanden',
        'needle' => ' active',
    ],
    [
        'label' => 'Datetime-Feld gerendert',
        'needle' => 'type="datetime"',
    ],
    [
        'label' => 'Password-Feld gerendert',
        'needle' => 'type="password"',
    ],
    [
        'label' => 'Datalist am Input vorhanden',
        'needle' => '<datalist id=',
    ],
    [
        'label' => 'Markitup-Feld vorhanden',
        'needle' => 'Markitup Fallback',
    ],
];

$rows = '';
foreach ($checks as $check) {
    $needle = $check['needle'];
    $parserOk = str_contains($parserHtml, $needle);
    $flexOk = str_contains($flexHtml, $needle);

    $parserBadge = $parserOk
        ? '<span class="label label-success">OK</span>'
        : '<span class="label label-danger">Fehlt</span>';
    $flexBadge = $flexOk
        ? '<span class="label label-success">OK</span>'
        : '<span class="label label-danger">Fehlt</span>';

    $rows .= '<tr>'
        . '<td>' . rex_escape($check['label']) . '</td>'
        . '<td><code>' . rex_escape($needle) . '</code></td>'
        . '<td>' . $parserBadge . '</td>'
        . '<td>' . $flexBadge . '</td>'
        . '</tr>';
}

$checkBody = '<div class="table-responsive">'
    . '<table class="table table-striped table-hover">'
    . '<thead><tr><th>Check</th><th>Needle</th><th>Parser</th><th>Flex-Repeater</th></tr></thead>'
    . '<tbody>' . $rows . '</tbody>'
    . '</table>'
    . '</div>';

$fragment = new rex_fragment();
$fragment->setVar('title', rex_i18n::msg('mform_demo_renderer_parity_checks'), false);
$fragment->setVar('body', $checkBody, false);
echo $fragment->parse('core/page/section.php');

$htmlBody = '<div class="row">'
    . '<div class="col-lg-6">'
    . '<h4>Parser HTML</h4>'
    . '<pre style="max-height:420px;overflow:auto"><code>' . rex_escape($parserHtml) . '</code></pre>'
    . '</div>'
    . '<div class="col-lg-6">'
    . '<h4>Flex-Repeater Template HTML</h4>'
    . '<pre style="max-height:420px;overflow:auto"><code>' . rex_escape($flexHtml) . '</code></pre>'
    . '</div>'
    . '</div>';

$fragment = new rex_fragment();
$fragment->setVar('title', rex_i18n::msg('mform_demo_renderer_parity_html'), false);
$fragment->setVar('body', $htmlBody, false);
echo $fragment->parse('core/page/section.php');
