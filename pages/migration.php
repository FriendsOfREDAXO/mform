<?php

/**
 * MBlock -> Repeater: Migrationsassistent in fuenf Schritten.
 *
 * 1. Inventar: Module mit MBlock::show(), Slices, Slots, Feldtypen, Risiko
 * 2. Code: Analyse (Legacy-Key-Map je Slot, editierbar) und Konvertierung von Eingabe/Ausgabe
 * 3. Modul: konvertierte Kopie anlegen
 * 4. Daten: Dry-Run aller Slots, Anwenden mit Backup je Lauf-Token, Rollback
 * 5. Umhaengen: Slices auf das neue Modul, Rueckgaengig per Token
 *
 * @author Friends Of REDAXO
 * @license MIT
 *
 * @var rex_addon $this
 */

use FriendsOfRedaxo\MForm\Migration\MBlockInventory;
use FriendsOfRedaxo\MForm\Migration\MBlockModuleAnalyzer;
use FriendsOfRedaxo\MForm\Migration\MBlockToRepeaterConverter;
use FriendsOfRedaxo\MForm\Migration\MBlockToRepeaterMigrator;
use FriendsOfRedaxo\MForm\Migration\YFormMBlockMigrator;

$analyzer = new MBlockModuleAnalyzer();
$converter = new MBlockToRepeaterConverter($analyzer);
$migrator = new MBlockToRepeaterMigrator($converter);
$inventory = new MBlockInventory($analyzer, $migrator);

MBlockToRepeaterMigrator::ensureTables();

$func = rex_request('func', 'string', '');
$moduleId = rex_request('module_id', 'int', 0);
$csrfToken = rex_csrf_token::factory('mform_migration');
$csrf = $csrfToken->getHiddenField();
$csrfOk = '' === $func || $csrfToken->isValid();

$messages = '';
if (!$csrfOk) {
    $messages .= rex_view::error(rex_i18n::msg('csrf_token_invalid'));
    $func = '';
}

$t = static fn (string $key, mixed ...$args): string => rex_i18n::msg('mform_migration_' . $key, ...$args);
$pageUrl = static fn (array $params = []): string => rex_url::currentBackendPage(array_merge($moduleId > 0 ? ['module_id' => $moduleId] : [], $params));

/**
 * Key-Map je Slot aus dem Request: key_map[slot][alt] = neu.
 *
 * @return array<string, array<string, string>>
 */
$readKeyMaps = static function (): array {
    $raw = rex_request('key_map', 'array', []);
    $maps = [];
    foreach ($raw as $slot => $pairs) {
        if (!is_array($pairs)) {
            continue;
        }
        foreach ($pairs as $old => $new) {
            $old = trim((string) $old);
            $new = trim((string) $new);
            if ('' !== $old && '' !== $new) {
                $maps[(string) $slot][$old] = $new;
            }
        }
    }

    return $maps;
};

/**
 * Hinweis-/Warnungsliste eines Ergebnisses.
 *
 * @param list<string> $warnings
 * @param list<string> $notes
 */
$renderNotes = static function (array $warnings, array $notes) use ($t): string {
    $html = '';
    if ([] !== $warnings) {
        $items = '';
        foreach ($warnings as $w) {
            $items .= '<li>' . rex_escape($w) . '</li>';
        }
        $html .= '<div class="alert alert-warning"><strong><i class="rex-icon fa-exclamation-triangle"></i> ' . $t('warnings') . '</strong><ul class="rex-mb-0">' . $items . '</ul></div>';
    }
    if ([] !== $notes) {
        $items = '';
        foreach ($notes as $n) {
            $items .= '<li>' . rex_escape($n) . '</li>';
        }
        $html .= '<div class="alert alert-info"><strong><i class="rex-icon fa-info-circle"></i> ' . $t('notes') . '</strong><ul class="rex-mb-0">' . $items . '</ul></div>';
    }

    return $html;
};

$codeArea = static fn (string $name, string $value, int $rows = 16, bool $readonly = false): string => '<textarea class="form-control" name="' . rex_escape($name) . '" rows="' . $rows . '" style="font-family:monospace;font-size:12px;white-space:pre;"' . ($readonly ? ' readonly onclick="this.select();"' : '') . '>' . rex_escape($value) . '</textarea>';

$riskBadge = static function (string $risk) use ($t): string {
    $class = ['red' => 'label-danger', 'yellow' => 'label-warning', 'green' => 'label-success'][$risk] ?? 'label-default';

    return '<span class="label ' . $class . '">' . $t('risk_' . $risk) . '</span>';
};

$sectionTitle = static fn (int $step, string $title): string => '<span class="badge" style="margin-right:.5em;">' . $step . '</span>' . $title;

// ── Modul laden ─────────────────────────────────────────────────────────────
$module = null;
$analysis = null;
if ($moduleId > 0) {
    $rows = rex_sql::factory()->getArray('SELECT id, name, input, output FROM ' . rex::getTable('module') . ' WHERE id = :id', ['id' => $moduleId]);
    if ([] !== $rows) {
        $module = ['id' => (int) $rows[0]['id'], 'name' => (string) $rows[0]['name'], 'input' => (string) $rows[0]['input'], 'output' => (string) $rows[0]['output']];
        $analysis = $analyzer->analyze($module['input'], $module['output']);
    } else {
        $messages .= rex_view::error($t('module_not_found', $moduleId));
        $moduleId = 0;
    }
}

// Key-Maps: Request (editiert) vor Analyse (automatisch).
$keyMaps = $readKeyMaps();
if ([] === $keyMaps && null !== $analysis) {
    $keyMaps = $analysis['key_maps'];
}
$mergeColumns = 1 === rex_request('merge_columns', 'int', 0);
$slotConfig = [];
if (null !== $analysis) {
    foreach ($analysis['slots'] as $slot) {
        $slotConfig[$slot] = ['key_map' => $keyMaps[$slot] ?? [], 'options' => ['merge_columns' => $mergeColumns, 'list_fields' => $analysis['list_fields'][$slot] ?? []]];
    }
}

// ── Aktionen ────────────────────────────────────────────────────────────────
$inputCode = rex_request('input_code', 'string', $module['input'] ?? '');
$outputCode = rex_request('output_code', 'string', $module['output'] ?? '');
$convertResultsHtml = '';
$convertedInput = rex_request('converted_input', 'string', '');
$convertedOutput = rex_request('converted_output', 'string', '');
$createdModule = null;
$dryRun = null;
$applyResult = null;
$singleResult = null;

if ('convert' === $func) {
    $codeAnalysis = $analyzer->analyze($inputCode, $outputCode);
    $inputResult = $converter->convertInput($inputCode, null, $codeAnalysis);
    $outputResult = $converter->convertOutput($outputCode, implode(',', [] !== $codeAnalysis['slots'] ? $codeAnalysis['slots'] : ['1']), $keyMaps);
    $convertedInput = $inputResult['code'];
    $convertedOutput = $outputResult['code'];
    $convertResultsHtml = '<hr><div class="row">
        <div class="col-md-6"><h5><i class="rex-icon fa-sign-in"></i> ' . $t('input_label') . '</h5>' . $renderNotes($inputResult['warnings'], $inputResult['notes']) . $codeArea('input_result', $inputResult['code'], 18, true) . '</div>
        <div class="col-md-6"><h5><i class="rex-icon fa-sign-out"></i> ' . $t('output_label') . '</h5>' . $renderNotes($outputResult['warnings'], $outputResult['notes']) . $codeArea('output_result', $outputResult['code'], 18, true) . '</div>
    </div>';
    $messages .= rex_view::success($t('convert_done'));
}

if ('create_module' === $func && $moduleId > 0) {
    if ('' === trim($convertedInput) && '' === trim($convertedOutput)) {
        $messages .= rex_view::warning($t('create_nothing'));
    } else {
        $createdModule = $migrator->createConvertedModule($moduleId, $convertedInput, $convertedOutput);
        if (null === $createdModule) {
            $messages .= rex_view::error($t('create_missing_module'));
        } else {
            $messages .= rex_view::success($t('create_success', $createdModule['id'], $createdModule['key']));
        }
    }
}

if ('data_dryrun' === $func && $moduleId > 0) {
    $dryRun = $migrator->dryRunSlots($moduleId, $slotConfig);
    $messages .= rex_view::success($t('batch_dryrun_done'));
}

if ('data_apply' === $func && $moduleId > 0) {
    $sliceIds = [];
    foreach ((array) rex_request('slice_ids', 'array', []) as $sid) {
        if ((int) $sid > 0) {
            $sliceIds[] = (int) $sid;
        }
    }
    if ([] === $sliceIds) {
        $messages .= rex_view::info($t('batch_nothing_selected'));
    } else {
        $applyResult = $migrator->applySlots($moduleId, $slotConfig, $sliceIds);
        if ($applyResult['updated'] > 0) {
            $messages .= rex_view::success($t('batch_applied_token', $applyResult['updated'], $applyResult['token']));
        }
        if ($applyResult['skipped'] > 0) {
            $messages .= rex_view::info($t('batch_skipped', $applyResult['skipped']));
        }
        foreach ($applyResult['errors'] as $err) {
            $messages .= rex_view::error(rex_escape($err));
        }
    }
    $dryRun = $migrator->dryRunSlots($moduleId, $slotConfig);
}

if ('data_rollback' === $func) {
    $result = $migrator->rollback(rex_request('run_token', 'string', ''));
    foreach ($result['errors'] as $err) {
        $messages .= rex_view::error(rex_escape($err));
    }
    if ($result['restored'] > 0) {
        $messages .= rex_view::success($t('rollback_success', $result['restored']));
    }
    if ($moduleId > 0) {
        $dryRun = $migrator->dryRunSlots($moduleId, $slotConfig);
    }
}

if ('data_single' === $func) {
    $singleValue = rex_request('data_value', 'string', '');
    $singleSlot = rex_request('single_slot', 'string', '1');
    $singleMap = $keyMaps[$singleSlot] ?? [];
    if ('' !== trim($singleValue)) {
        $singleResult = $converter->convertData($singleValue, $singleSlot, $singleMap, ['merge_columns' => $mergeColumns]);
    }
}

if ('slice_reassign' === $func && $moduleId > 0) {
    $target = rex_request('reassign_target_module_id', 'int', 0);
    $ids = [];
    foreach ((array) rex_request('reassign_slice_ids', 'array', []) as $sid) {
        if ((int) $sid > 0) {
            $ids[] = (int) $sid;
        }
    }
    if ($target <= 0) {
        $messages .= rex_view::error($t('reassign_missing_target'));
    } elseif ([] === $ids) {
        $messages .= rex_view::info($t('reassign_none_selected'));
    } else {
        $result = $migrator->reassign($ids, $target);
        foreach ($result['errors'] as $err) {
            $messages .= rex_view::error(rex_escape($err));
        }
        if ($result['moved'] > 0) {
            $messages .= rex_view::success($t('reassign_success', $result['moved'], $target)) . rex_view::info($t('reassign_token', $result['token']));
        }
    }
}

if ('slice_reassign_revert' === $func) {
    $result = $migrator->revertReassign(rex_request('reassign_token', 'string', ''));
    foreach ($result['errors'] as $err) {
        $messages .= rex_view::info(rex_escape($err));
    }
    if ($result['restored'] > 0) {
        $messages .= rex_view::success($t('reassign_revert_success', $result['restored'], $result['token']));
    }
}

// ── YForm (M7) ─────────────────────────────────────────────────────────────
$yform = YFormMBlockMigrator::available() ? new YFormMBlockMigrator($converter) : null;
$yformTable = rex_request('yform_table', 'string', '');
$yformField = rex_request('yform_field', 'string', '');
$yformDry = null;
$yformKeyMap = [];
$yformKeyMapJson = rex_request('yform_key_map', 'string', '');
if ('' !== trim($yformKeyMapJson)) {
    $decodedMap = json_decode($yformKeyMapJson, true);
    if (is_array($decodedMap)) {
        $yformKeyMap = array_map('strval', $decodedMap);
    } else {
        $messages .= rex_view::warning($t('mapping_json_invalid'));
    }
}
$yformValid = null !== $yform && YFormMBlockMigrator::validName($yformTable) && YFormMBlockMigrator::validName($yformField);

if ('yform_dryrun' === $func && $yformValid) {
    $yformDry = $yform->dryRun($yformTable, $yformField, $yformKeyMap, ['merge_columns' => $mergeColumns]);
    $messages .= rex_view::success($t('batch_dryrun_done'));
}
if ('yform_apply' === $func && $yformValid) {
    $ids = [];
    foreach ((array) rex_request('yform_ids', 'array', []) as $id) {
        if ((int) $id > 0) {
            $ids[] = (int) $id;
        }
    }
    if ([] === $ids) {
        $messages .= rex_view::info($t('batch_nothing_selected'));
    } else {
        $result = $yform->apply($yformTable, $yformField, $yformKeyMap, ['merge_columns' => $mergeColumns], $ids);
        foreach ($result['errors'] as $err) {
            $messages .= rex_view::error(rex_escape($err));
        }
        if ($result['updated'] > 0) {
            $messages .= rex_view::success($t('batch_applied_token', $result['updated'], $result['token']));
        }
        if (1 === rex_request('yform_switch_type', 'int', 0)) {
            $switch = $yform->switchFieldToTextarea($yformTable, $yformField);
            $messages .= $switch['changed'] ? rex_view::success(rex_escape($switch['message'])) : rex_view::info(rex_escape($switch['message']));
        }
    }
    $yformDry = $yform->dryRun($yformTable, $yformField, $yformKeyMap, ['merge_columns' => $mergeColumns]);
}

// ── Intro ───────────────────────────────────────────────────────────────────
$entries = $inventory->collect();
$summary = MBlockInventory::summary($entries);

$steps = '';
$stepKeys = [1 => 'inventory', 2 => 'code', 3 => 'module', 4 => 'data', 5 => 'reassign'];
if (null !== $yform) {
    $stepKeys[6] = 'yform';
}
foreach ($stepKeys as $n => $key) {
    $steps .= '<li><a href="#mform-migration-step' . $n . '"><span class="badge">' . $n . '</span> ' . $t('step_' . $key) . '</a></li>';
}
$introBody = '<p>' . $t('intro') . '</p>'
    . '<ol class="list-unstyled" style="display:flex;flex-wrap:wrap;gap:.5em 1.5em;margin:0 0 10px;">' . $steps . '</ol>'
    . '<p class="rex-note">' . $t('intro_console') . '</p>'
    . '<div class="alert alert-warning rex-mb-0"><i class="rex-icon fa-exclamation-triangle"></i> ' . $t('limitations') . '</div>';

echo $messages;
$fragment = new rex_fragment();
$fragment->setVar('title', rex_i18n::msg('mform_migration'), false);
$fragment->setVar('body', $introBody, false);
echo $fragment->parse('core/page/section.php');

// ── Schritt 1: Inventar ─────────────────────────────────────────────────────
$inventoryBody = '<div id="mform-migration-step1"></div>';
if ([] === $entries) {
    $inventoryBody .= '<p class="text-muted rex-mb-0">' . $t('inventory_empty') . '</p>';
} else {
    $rowsHtml = '';
    foreach ($entries as $entry) {
        $a = $entry['analysis'];
        $hints = '';
        foreach ($a['risk_reasons'] as $reason) {
            $hints .= '<li>' . rex_escape($reason) . '</li>';
        }
        $types = [];
        foreach ($entry['field_types'] as $type => $count) {
            $types[] = rex_escape($type) . ' &times;' . $count;
        }
        $probe = [];
        foreach ($entry['probes'] as $slot => $p) {
            $probe[] = $slot . ': ' . $t('inventory_probe', $p['with_markers'], $p['slices'], $p['empty']);
        }
        $active = $entry['id'] === $moduleId;
        $rowsHtml .= '<tr' . ($active ? ' class="info"' : '') . '>'
            . '<td>' . rex_escape($entry['name']) . ' <span class="text-muted">[' . $entry['id'] . ']</span></td>'
            . '<td class="text-right">' . $entry['slice_count'] . '</td>'
            . '<td>' . rex_escape(implode(', ', $a['slots'])) . '<br><small class="text-muted">' . implode('<br>', $probe) . '</small></td>'
            . '<td><small>' . implode(', ', $types) . '</small></td>'
            . '<td>' . $riskBadge($a['risk']) . ('' !== $hints ? '<ul class="rex-mb-0" style="padding-left:1.2em;font-size:11px;">' . $hints . '</ul>' : '') . '</td>'
            . '<td>' . (null !== $entry['migrated_module_id'] ? '<a href="' . rex_url::backendPage('modules/modules', ['function' => 'edit', 'module_id' => $entry['migrated_module_id']]) . '">[' . $entry['migrated_module_id'] . ']</a>' : '&ndash;') . '</td>'
            . '<td class="text-right"><a class="btn btn-xs ' . ($active ? 'btn-primary' : 'btn-default') . '" href="' . rex_url::currentBackendPage(['module_id' => $entry['id']]) . '#mform-migration-step2"><i class="rex-icon fa-search"></i> ' . $t('inventory_analyze') . '</a></td>'
            . '</tr>';
    }
    $inventoryBody .= '<p class="rex-note">' . $t('inventory_summary', $summary['total'], $summary['slices'], $summary['red'], $summary['yellow'], $summary['green']) . '</p>'
        . '<div class="table-responsive"><table class="table table-striped table-hover">'
        . '<thead><tr><th>' . $t('inventory_col_module') . '</th><th class="text-right">' . $t('inventory_col_slices') . '</th><th>' . $t('inventory_col_slots') . '</th><th>' . $t('inventory_col_fields') . '</th><th>' . $t('inventory_col_risk') . '</th><th>' . $t('inventory_col_copy') . '</th><th></th></tr></thead>'
        . '<tbody>' . $rowsHtml . '</tbody></table></div>';
}
$fragment = new rex_fragment();
$fragment->setVar('title', $sectionTitle(1, $t('step_inventory')), false);
$fragment->setVar('body', $inventoryBody, false);
echo $fragment->parse('core/page/section.php');

/**
 * Schritt 6: YForm-Felder mit MBlock-Daten (M7), modulunabhaengig.
 */
$renderYFormSection = static function () use ($yform, $yformDry, $yformTable, $yformField, $yformKeyMapJson, $mergeColumns, $csrf, $pageUrl, $t, $sectionTitle): void {
    if (null === $yform) {
        return;
    }
    $body = '<div id="mform-migration-step6"></div><p>' . $t('yform_intro') . '</p>';
    $candidates = $yform->findCandidates();
    if ([] === $candidates) {
        $body .= '<p class="text-muted rex-mb-0">' . $t('yform_empty') . '</p>';
    } else {
        $rows = '';
        foreach ($candidates as $c) {
            $active = $c['table'] === $yformTable && $c['field'] === $yformField;
            $rows .= '<tr' . ($active ? ' class="info"' : '') . '><td><code>' . rex_escape($c['table'] . '.' . $c['field']) . '</code><br><small class="text-muted">' . rex_escape($c['label']) . '</small></td>'
                . '<td>' . rex_escape($c['type_name']) . '</td><td class="text-right">' . $c['rows'] . '</td><td class="text-right">' . $c['with_markers'] . '</td>'
                . '<td class="text-right"><form action="' . $pageUrl() . '#mform-migration-step6" method="post" style="display:inline">' . $csrf . '<input type="hidden" name="func" value="yform_dryrun"><input type="hidden" name="yform_table" value="' . rex_escape($c['table']) . '"><input type="hidden" name="yform_field" value="' . rex_escape($c['field']) . '"><input type="hidden" name="yform_key_map" value="' . rex_escape($yformKeyMapJson) . '"><button type="submit" class="btn btn-xs ' . ($active ? 'btn-primary' : 'btn-default') . '"><i class="rex-icon fa-search"></i> ' . $t('batch_dryrun') . '</button></form></td></tr>';
        }
        $body .= '<div class="table-responsive"><table class="table table-striped table-hover"><thead><tr><th>' . $t('yform_col_field') . '</th><th>' . $t('col_type') . '</th><th class="text-right">' . $t('yform_col_rows') . '</th><th class="text-right">' . $t('yform_col_markers') . '</th><th></th></tr></thead><tbody>' . $rows . '</tbody></table></div>';
        $body .= '<div class="row"><div class="col-sm-8"><div class="form-group"><label class="control-label">' . $t('mapping_json') . '</label>'
            . '<form action="' . $pageUrl() . '#mform-migration-step6" method="post">' . $csrf . '<input type="hidden" name="func" value="yform_dryrun"><input type="hidden" name="yform_table" value="' . rex_escape($yformTable) . '"><input type="hidden" name="yform_field" value="' . rex_escape($yformField) . '">'
            . '<div class="input-group"><input type="text" class="form-control" name="yform_key_map" value="' . rex_escape($yformKeyMapJson) . '" placeholder="{&quot;REX_MEDIA_1&quot;:&quot;media&quot;}"><span class="input-group-btn"><button type="submit" class="btn btn-default"' . ('' === $yformTable ? ' disabled' : '') . '>' . $t('batch_dryrun') . '</button></span></div></form>'
            . '<p class="help-block rex-note">' . $t('yform_mapping_note') . '</p></div></div></div>';
    }

    if (null !== $yformDry) {
        $rowsHtml = '';
        $selectable = 0;
        foreach ($yformDry['rows'] as $row) {
            $state = $row['skipped'] ? '<span class="label label-default">' . $t('batch_state_skip') . '</span>' : ($row['changed'] ? '<span class="label label-success">' . $t('batch_state_change') . '</span>' : '<span class="label label-default">' . $t('batch_state_nochange') . '</span>');
            $warn = '';
            if ([] !== $row['warnings']) {
                $items = '';
                foreach ($row['warnings'] as $w) {
                    $items .= '<li>' . rex_escape($w) . '</li>';
                }
                $warn = '<ul class="text-warning rex-mb-0" style="font-size:11px;padding-left:1.2em;">' . $items . '</ul>';
            }
            $checkbox = '<span class="text-muted">&ndash;</span>';
            if ($row['changed']) {
                ++$selectable;
                $checkbox = '<input type="checkbox" name="yform_ids[]" value="' . $row['id'] . '" checked>';
            }
            $rowsHtml .= '<tr><td>' . $checkbox . '</td><td>' . $row['id'] . '</td><td>' . $row['count'] . '</td><td>' . $state . $warn . '</td></tr>';
        }
        $table = '<h5>' . rex_escape($yformTable . '.' . $yformField) . '</h5><p class="rex-note">' . $t('yform_summary', $yformDry['total'], $yformDry['changed'], $yformDry['warnings']) . '</p>'
            . '<div class="table-responsive"><table class="table table-striped table-hover"><thead><tr><th style="width:32px;"><i class="rex-icon fa-check"></i></th><th>' . $t('yform_col_id') . '</th><th>' . $t('batch_col_count') . '</th><th>' . $t('batch_col_state') . '</th></tr></thead><tbody>' . $rowsHtml . '</tbody></table></div>';
        if ($selectable > 0) {
            $body .= '<hr><form action="' . $pageUrl() . '#mform-migration-step6" method="post" onsubmit="return confirm(\'' . rex_escape($t('batch_confirm_backup')) . '\');">' . $csrf
                . '<input type="hidden" name="func" value="yform_apply"><input type="hidden" name="yform_table" value="' . rex_escape($yformTable) . '"><input type="hidden" name="yform_field" value="' . rex_escape($yformField) . '"><input type="hidden" name="yform_key_map" value="' . rex_escape($yformKeyMapJson) . '"><input type="hidden" name="merge_columns" value="' . ($mergeColumns ? 1 : 0) . '">'
                . $table
                . '<div class="checkbox"><label><input type="checkbox" name="yform_switch_type" value="1"> ' . $t('yform_switch_type') . '</label><p class="help-block rex-note" style="margin-bottom:0;">' . $t('yform_switch_type_note') . '</p></div>'
                . '<div class="alert alert-info"><i class="rex-icon fa-shield"></i> ' . $t('batch_backup_note') . '</div>'
                . '<button type="submit" class="btn btn-save"><i class="rex-icon fa-database"></i> ' . $t('yform_apply') . '</button></form>';
        } else {
            $body .= '<hr>' . $table . '<div class="alert alert-info">' . $t('batch_nothing_selectable') . '</div>';
        }
    }

    $fragment = new rex_fragment();
    $fragment->setVar('title', $sectionTitle(6, $t('step_yform')), false);
    $fragment->setVar('body', $body, false);
    echo $fragment->parse('core/page/section.php');
};

if (null === $module) {
    $analysis = null;
}
if (null === $analysis) {
    $fragment = new rex_fragment();
    $fragment->setVar('title', $sectionTitle(2, $t('step_code')), false);
    $fragment->setVar('body', '<div id="mform-migration-step2"></div><p class="text-muted rex-mb-0">' . $t('select_module_hint') . '</p>', false);
    echo $fragment->parse('core/page/section.php');
    $renderYFormSection();

    return;
}

// ── Schritt 2: Analyse und Code ─────────────────────────────────────────────
$analysisHtml = '<div id="mform-migration-step2"></div>'
    . '<p>' . $t('module_headline', rex_escape($module['name']), $module['id']) . ' ' . $riskBadge($analysis['risk']) . '</p>';

if (!$analysis['has_mblock']) {
    $analysisHtml .= '<div class="alert alert-info rex-mb-0">' . $t('no_mblock_in_module') . '</div>';
} else {
    $analysisHtml .= $renderNotes(array_merge($analysis['risk_reasons'], $analysis['warnings']), []);

    // Key-Map je Slot (editierbar) und Felder.
    $keyMapForm = '';
    foreach ($analysis['calls'] as $call) {
        $slot = $call['slot'];
        $fieldRows = '';
        foreach ($call['fields'] as $field) {
            $fieldRows .= '<tr><td><code>' . rex_escape($field['name']) . '</code></td><td>' . rex_escape($field['type']) . '</td>'
                . '<td>' . (null !== $field['legacy_key'] ? '<code>' . rex_escape($field['legacy_key']) . '</code>' : '&ndash;') . '</td>'
                . '<td>' . (null !== $field['legacy_key'] ? '<input type="text" class="form-control input-sm" name="key_map[' . rex_escape($slot) . '][' . rex_escape($field['legacy_key']) . ']" value="' . rex_escape($keyMaps[$slot][$field['legacy_key']] ?? $field['target'] ?? '') . '">' : '&ndash;') . '</td></tr>';
        }
        $options = [];
        foreach ($call['options'] as $k => $v) {
            $options[] = rex_escape($k . ' => ' . $v);
        }
        $keyMapForm .= '<h5>' . $t('slot_headline', rex_escape($slot), $call['line'], rex_escape($call['form_var'] ?? '-')) . '</h5>'
            . ([] !== $options ? '<p class="rex-note">' . $t('slot_options') . ': ' . implode(', ', $options) . '</p>' : '')
            . ([] !== $call['unknown_options'] ? '<p class="text-warning">' . $t('slot_unknown_options') . ': ' . rex_escape(implode(', ', $call['unknown_options'])) . '</p>' : '');
        if ('' !== $fieldRows) {
            $keyMapForm .= '<div class="table-responsive"><table class="table table-condensed"><thead><tr><th>' . $t('col_field') . '</th><th>' . $t('col_type') . '</th><th>' . $t('col_legacy_key') . '</th><th>' . $t('col_new_key') . '</th></tr></thead><tbody>' . $fieldRows . '</tbody></table></div>';
        } elseif ('html' === $call['form_kind']) {
            $keyMapForm .= '<p class="text-warning">' . $t('html_form_manual') . '</p>';
        }
    }
    $analysisHtml .= $keyMapForm;
}

// Konvertierungsformular (Code editierbar, Key-Map wird mitgeschickt).
$analysisHtml .= '
<form action="' . $pageUrl() . '#mform-migration-step2" method="post">
    ' . $csrf . '
    <input type="hidden" name="func" value="convert">
    <input type="hidden" name="module_id" value="' . $moduleId . '">
    <p class="rex-note">' . $t('keymap_note') . '</p>
    <div class="row">
        <div class="col-md-6"><div class="form-group"><label class="control-label"><i class="rex-icon fa-sign-in"></i> ' . $t('input_label') . '</label>' . $codeArea('input_code', $inputCode) . '</div></div>
        <div class="col-md-6"><div class="form-group"><label class="control-label"><i class="rex-icon fa-sign-out"></i> ' . $t('output_label') . '</label>' . $codeArea('output_code', $outputCode) . '</div></div>
    </div>
    <div class="rex-form-panel-footer"><div class="btn-toolbar">
        <button type="submit" class="btn btn-primary"><i class="rex-icon fa-cogs"></i> ' . $t('convert') . '</button>
    </div></div>
</form>';

$analysisHtml .= $convertResultsHtml;

$fragment = new rex_fragment();
$fragment->setVar('title', $sectionTitle(2, $t('step_code')), false);
$fragment->setVar('body', $analysisHtml, false);
echo $fragment->parse('core/page/section.php');

// Key-Map als Hidden-Felder fuer die Folgeschritte.
$keyMapHidden = '<input type="hidden" name="merge_columns" value="' . ($mergeColumns ? 1 : 0) . '">';
foreach ($keyMaps as $slot => $map) {
    foreach ($map as $old => $new) {
        $keyMapHidden .= '<input type="hidden" name="key_map[' . rex_escape((string) $slot) . '][' . rex_escape((string) $old) . ']" value="' . rex_escape($new) . '">';
    }
}

// ── Schritt 3: Modul anlegen ────────────────────────────────────────────────
$existingCopy = null;
foreach ($entries as $entry) {
    if ($entry['id'] === $moduleId) {
        $existingCopy = $entry['migrated_module_id'];
    }
}
$moduleBody = '<div id="mform-migration-step3"></div><p>' . $t('create_intro') . '</p>';
if (null !== $createdModule) {
    $moduleBody .= '<div class="alert alert-success">' . $t('create_success', $createdModule['id'], $createdModule['key']) . ' <a href="' . rex_url::backendPage('modules/modules', ['function' => 'edit', 'module_id' => $createdModule['id']]) . '">' . $t('create_open') . '</a></div>';
} elseif (null !== $existingCopy) {
    $moduleBody .= '<p class="rex-note">' . $t('create_existing', $existingCopy) . ' <a href="' . rex_url::backendPage('modules/modules', ['function' => 'edit', 'module_id' => $existingCopy]) . '">' . $t('create_open') . '</a></p>';
}
if ('' !== trim($convertedInput) || '' !== trim($convertedOutput)) {
    $moduleBody .= '
<form action="' . $pageUrl() . '#mform-migration-step3" method="post" onsubmit="return confirm(\'' . rex_escape($t('create_confirm')) . '\');">
    ' . $csrf . $keyMapHidden . '
    <input type="hidden" name="func" value="create_module">
    <input type="hidden" name="module_id" value="' . $moduleId . '">
    <textarea name="converted_input" hidden>' . rex_escape($convertedInput) . '</textarea>
    <textarea name="converted_output" hidden>' . rex_escape($convertedOutput) . '</textarea>
    <button type="submit" class="btn btn-save"><i class="rex-icon fa-plus"></i> ' . $t('create_module') . '</button>
</form>';
} else {
    $moduleBody .= '<p class="text-muted rex-mb-0">' . $t('create_convert_first') . '</p>';
}
$fragment = new rex_fragment();
$fragment->setVar('title', $sectionTitle(3, $t('step_module')), false);
$fragment->setVar('body', $moduleBody, false);
echo $fragment->parse('core/page/section.php');

// ── Schritt 4: Daten ────────────────────────────────────────────────────────
$dataBody = '<div id="mform-migration-step4"></div><p>' . $t('data_intro_wizard') . '</p>';

$slotList = implode(', ', array_map(static fn (string $s): string => 'value' . $s, $analysis['slots']));
$dataBody .= '
<form action="' . $pageUrl() . '#mform-migration-step4" method="post">
    ' . $csrf . '
    <input type="hidden" name="func" value="data_dryrun">
    <input type="hidden" name="module_id" value="' . $moduleId . '">';
foreach ($keyMaps as $slot => $map) {
    foreach ($map as $old => $new) {
        $dataBody .= '<input type="hidden" name="key_map[' . rex_escape((string) $slot) . '][' . rex_escape((string) $old) . ']" value="' . rex_escape($new) . '">';
    }
}
$dataBody .= '
    <p class="rex-note">' . $t('data_slots', rex_escape($slotList)) . '</p>
    <div class="checkbox"><label><input type="checkbox" name="merge_columns" value="1"' . ($mergeColumns ? ' checked' : '') . '> ' . $t('merge_columns') . '</label><p class="help-block rex-note" style="margin-bottom:0;">' . $t('merge_columns_note') . '</p></div>
    <div class="rex-form-panel-footer"><div class="btn-toolbar">
        <button type="submit" class="btn btn-primary"><i class="rex-icon fa-search"></i> ' . $t('batch_dryrun') . '</button>
    </div></div>
</form>';

if (null !== $dryRun) {
    $selectable = 0;
    $tables = '';
    foreach ($dryRun as $slot => $result) {
        $rowsHtml = '';
        foreach ($result['rows'] as $row) {
            if ($row['skipped']) {
                $state = '<span class="label label-default">' . $t('batch_state_skip') . '</span>';
            } elseif ($row['changed']) {
                $state = '<span class="label label-success">' . $t('batch_state_change') . '</span>';
            } else {
                $state = '<span class="label label-default">' . $t('batch_state_nochange') . '</span>';
            }
            $warn = '';
            if ([] !== $row['warnings']) {
                $items = '';
                foreach ($row['warnings'] as $w) {
                    $items .= '<li>' . rex_escape($w) . '</li>';
                }
                $warn = '<ul class="text-warning rex-mb-0" style="font-size:11px;padding-left:1.2em;">' . $items . '</ul>';
            }
            $checkbox = '<span class="text-muted">&ndash;</span>';
            if ($row['changed']) {
                ++$selectable;
                $checkbox = '<input type="checkbox" name="slice_ids[]" value="' . $row['slice_id'] . '" checked>';
            }
            $rowsHtml .= '<tr><td>' . $checkbox . '</td><td>' . $row['slice_id'] . '</td><td>' . $row['article_id'] . ('' !== trim($row['article_name']) ? ' &ndash; ' . rex_escape($row['article_name']) : '') . '</td><td>' . $row['clang_id'] . '</td><td>' . $row['count'] . '</td><td>' . $state . $warn . '</td></tr>';
        }
        $tables .= '<h5>' . $t('batch_slot_headline', rex_escape((string) $slot), rex_escape($result['column'])) . '</h5>'
            . '<p class="rex-note">' . $t('batch_summary', $result['total'], $result['changed'], $result['warnings'], rex_escape($result['column'])) . '</p>'
            . '<div class="table-responsive"><table class="table table-striped table-hover"><thead><tr><th style="width:32px;"><i class="rex-icon fa-check"></i></th><th>' . $t('batch_col_slice') . '</th><th>' . $t('batch_col_article') . '</th><th>' . $t('batch_col_clang') . '</th><th>' . $t('batch_col_count') . '</th><th>' . $t('batch_col_state') . '</th></tr></thead><tbody>' . $rowsHtml . '</tbody></table></div>';
    }

    if ($selectable > 0) {
        $dataBody .= '
<form action="' . $pageUrl() . '#mform-migration-step4" method="post" onsubmit="return confirm(\'' . rex_escape($t('batch_confirm_backup')) . '\');">
    ' . $csrf . $keyMapHidden . '
    <input type="hidden" name="func" value="data_apply">
    <input type="hidden" name="module_id" value="' . $moduleId . '">
    ' . $tables . '
    <div class="alert alert-info"><i class="rex-icon fa-shield"></i> ' . $t('batch_backup_note') . '</div>
    <button type="submit" class="btn btn-save"><i class="rex-icon fa-database"></i> ' . $t('batch_apply') . '</button>
</form>';
    } else {
        $dataBody .= $tables . '<div class="alert alert-info">' . $t('batch_nothing_selectable') . '</div>';
    }
}

// Rollback-Liste.
$runs = $migrator->getRuns(10);
if ([] !== $runs) {
    $runRows = '';
    foreach ($runs as $run) {
        $action = $run['open'] > 0
            ? '<form action="' . $pageUrl() . '#mform-migration-step4" method="post" style="display:inline" onsubmit="return confirm(\'' . rex_escape($t('rollback_confirm')) . '\');">' . $csrf . $keyMapHidden . '<input type="hidden" name="func" value="data_rollback"><input type="hidden" name="module_id" value="' . $moduleId . '"><input type="hidden" name="run_token" value="' . rex_escape($run['token']) . '"><button type="submit" class="btn btn-xs btn-warning"><i class="rex-icon fa-undo"></i> ' . $t('rollback_btn') . '</button></form>'
            : '<span class="label label-default">' . $t('rollback_done') . '</span>';
        $runRows .= '<tr><td><code>' . rex_escape($run['token']) . '</code></td><td>' . rex_escape($run['module_name']) . ' [' . $run['module_id'] . ']</td><td>' . $run['slices'] . '</td><td>' . rex_escape($run['columns']) . '</td><td>' . rex_escape($run['createdate']) . '<br><small class="text-muted">' . rex_escape($run['createuser']) . '</small></td><td>' . $action . '</td></tr>';
    }
    $dataBody .= '<hr><h5><i class="rex-icon fa-history"></i> ' . $t('runs_headline') . '</h5><div class="table-responsive"><table class="table table-condensed"><thead><tr><th>' . $t('runs_col_token') . '</th><th>' . $t('inventory_col_module') . '</th><th>' . $t('inventory_col_slices') . '</th><th>' . $t('runs_col_columns') . '</th><th>' . $t('runs_col_date') . '</th><th></th></tr></thead><tbody>' . $runRows . '</tbody></table></div>';
}

// Einzelwert testen.
$dataBody .= '<hr><details' . (null !== $singleResult ? ' open' : '') . '><summary style="cursor:pointer;"><strong>' . $t('data_single_headline') . '</strong></summary>
<form action="' . $pageUrl() . '#mform-migration-step4" method="post" style="margin-top:10px;">
    ' . $csrf . $keyMapHidden . '
    <input type="hidden" name="func" value="data_single">
    <input type="hidden" name="module_id" value="' . $moduleId . '">
    <div class="row">
        <div class="col-sm-9"><div class="form-group"><label class="control-label">' . $t('data_label') . '</label>' . $codeArea('data_value', rex_request('data_value', 'string', ''), 6) . '</div></div>
        <div class="col-sm-3"><div class="form-group"><label class="control-label">' . $t('batch_slot') . '</label><input type="text" class="form-control" name="single_slot" value="' . rex_escape(rex_request('single_slot', 'string', $analysis['slots'][0] ?? '1')) . '"></div></div>
    </div>
    <button type="submit" class="btn btn-default"><i class="rex-icon fa-database"></i> ' . $t('data_convert') . '</button>
</form>';
if (null !== $singleResult) {
    $dataBody .= $renderNotes($singleResult['warnings'], $singleResult['notes']) . '<label class="control-label" style="margin-top:10px;">' . $t('data_result') . '</label>' . $codeArea('data_result', $singleResult['json'], 8, true);
}
$dataBody .= '</details>';

$fragment = new rex_fragment();
$fragment->setVar('title', $sectionTitle(4, $t('step_data')), false);
$fragment->setVar('body', $dataBody, false);
echo $fragment->parse('core/page/section.php');

// ── Schritt 5: Umhaengen ────────────────────────────────────────────────────
$reassignBody = '<div id="mform-migration-step5"></div><p>' . $t('reassign_intro') . '</p>';

$targetId = $createdModule['id'] ?? $existingCopy ?? 0;
$targetOptions = '<option value="0">&ndash;</option>';
foreach (rex_sql::factory()->getArray('SELECT id, name FROM ' . rex::getTable('module') . ' WHERE id <> :id ORDER BY name ASC', ['id' => $moduleId]) as $mod) {
    $targetOptions .= '<option value="' . (int) $mod['id'] . '"' . ((int) $mod['id'] === $targetId ? ' selected' : '') . '>' . rex_escape((string) $mod['name']) . ' [' . (int) $mod['id'] . ']</option>';
}

$sliceRows = '';
$slices = rex_sql::factory()->getArray(
    'SELECT s.id, s.article_id, s.clang_id, COALESCE(a.name, \'\') AS article_name FROM ' . rex::getTable('article_slice') . ' s LEFT JOIN ' . rex::getTable('article') . ' a ON a.id = s.article_id AND a.clang_id = s.clang_id WHERE s.module_id = :m ORDER BY s.id',
    ['m' => $moduleId],
);
foreach ($slices as $slice) {
    $sliceRows .= '<tr><td><input type="checkbox" name="reassign_slice_ids[]" value="' . (int) $slice['id'] . '" checked></td><td>' . (int) $slice['id'] . '</td><td>' . (int) $slice['article_id'] . ('' !== (string) $slice['article_name'] ? ' &ndash; ' . rex_escape((string) $slice['article_name']) : '') . '</td><td>' . (int) $slice['clang_id'] . '</td></tr>';
}

if ('' === $sliceRows) {
    $reassignBody .= '<p class="text-muted">' . $t('batch_no_slices') . '</p>';
} else {
    $reassignBody .= '
<form action="' . $pageUrl() . '#mform-migration-step5" method="post" onsubmit="return confirm(\'' . rex_escape($t('reassign_confirm')) . '\');">
    ' . $csrf . $keyMapHidden . '
    <input type="hidden" name="func" value="slice_reassign">
    <input type="hidden" name="module_id" value="' . $moduleId . '">
    <div class="row"><div class="col-sm-8"><div class="form-group">
        <label class="control-label" for="mform-migration-reassign-target">' . $t('reassign_target') . '</label>
        <select class="form-control selectpicker" data-live-search="true" id="mform-migration-reassign-target" name="reassign_target_module_id">' . $targetOptions . '</select>
        <p class="help-block rex-note">' . $t('reassign_note') . '</p>
    </div></div></div>
    <div class="table-responsive"><table class="table table-striped table-condensed"><thead><tr><th style="width:32px;"><i class="rex-icon fa-check"></i></th><th>' . $t('batch_col_slice') . '</th><th>' . $t('batch_col_article') . '</th><th>' . $t('batch_col_clang') . '</th></tr></thead><tbody>' . $sliceRows . '</tbody></table></div>
    <button type="submit" class="btn btn-warning"><i class="rex-icon fa-exchange"></i> ' . $t('reassign_btn') . '</button>
</form>';
}

$reassignRuns = $migrator->getReassignRuns(10);
if ([] !== $reassignRuns) {
    $runRows = '';
    foreach ($reassignRuns as $run) {
        $action = $run['open'] > 0
            ? '<form action="' . $pageUrl() . '#mform-migration-step5" method="post" style="display:inline" onsubmit="return confirm(\'' . rex_escape($t('reassign_revert_confirm')) . '\');">' . $csrf . $keyMapHidden . '<input type="hidden" name="func" value="slice_reassign_revert"><input type="hidden" name="module_id" value="' . $moduleId . '"><input type="hidden" name="reassign_token" value="' . rex_escape($run['token']) . '"><button type="submit" class="btn btn-xs btn-warning"><i class="rex-icon fa-undo"></i> ' . $t('reassign_revert_btn') . '</button></form>'
            : '<span class="label label-default">' . $t('rollback_done') . '</span>';
        $runRows .= '<tr><td><code>' . rex_escape($run['token']) . '</code></td><td>' . $run['slices'] . '</td><td>' . $run['old_module_id'] . ' &rarr; ' . $run['new_module_id'] . '</td><td>' . rex_escape($run['createdate']) . '</td><td>' . $action . '</td></tr>';
    }
    $reassignBody .= '<hr><h5><i class="rex-icon fa-history"></i> ' . $t('reassign_runs_headline') . '</h5><div class="table-responsive"><table class="table table-condensed"><thead><tr><th>' . $t('runs_col_token') . '</th><th>' . $t('inventory_col_slices') . '</th><th>' . $t('reassign_col_modules') . '</th><th>' . $t('runs_col_date') . '</th><th></th></tr></thead><tbody>' . $runRows . '</tbody></table></div>';
}

$fragment = new rex_fragment();
$fragment->setVar('title', $sectionTitle(5, $t('step_reassign')), false);
$fragment->setVar('body', $reassignBody, false);
echo $fragment->parse('core/page/section.php');

$renderYFormSection();
