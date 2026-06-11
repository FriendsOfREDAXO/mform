<?php

/**
 * MBlock -> MForm-9-Repeater Migrationswerkzeug.
 *
 * Konvertiert Modul-Code (Eingabe + Ausgabe) von MBlock auf den MForm-Repeater.
 * Das Werkzeug erzeugt Vorschlags-Code und veraendert keine Module oder Daten.
 *
 * @author Friends Of REDAXO
 * @license MIT
 *
 * @var rex_addon $this
 */

use FriendsOfRedaxo\MForm\Migration\MBlockToRepeaterConverter;
use FriendsOfRedaxo\MForm\Migration\MBlockToRepeaterMigrator;

$converter = new MBlockToRepeaterConverter();
$migrator = new MBlockToRepeaterMigrator($converter);

$func = rex_request('func', 'string', '');
$repeaterId = rex_request('repeater_id', 'string', '1');
$inputCode = rex_request('input_code', 'string', '');
$outputCode = rex_request('output_code', 'string', '');
$dataValue = rex_request('data_value', 'string', '');
$legacyKeyMapJson = rex_request('legacy_key_map_json', 'string', '');
$legacyKey1Target = trim(rex_request('legacy_key_1_target', 'string', ''));
$createConvertedModule = 1 === rex_request('create_converted_module', 'int', 0);
$lastCreatedModuleId = rex_request('last_created_module_id', 'int', 0);
$lastReassignToken = rex_request('last_reassign_token', 'string', '');
$reassignSliceIds = (array) rex_request('reassign_slice_ids', 'array', []);
$reassignTargetModuleId = rex_request('reassign_target_module_id', 'int', 0);

$batchModuleId = rex_request('batch_module_id', 'int', 0);
$batchSlotId = rex_request('batch_slot_id', 'string', '1');
$batchSliceIds = (array) rex_request('slice_ids', 'array', []);

$inputResult = null;
$outputResult = null;
$dataResult = null;
$batchResult = null;
$batchApplyResult = null;
$moduleCreateMessage = '';
$convertMessage = '';
$pageMessages = '';
$reassignHistoryTable = rex::getTable('mform_migration_reassign_history');

/** @var array<string, string> $legacyKeyMap */
$legacyKeyMap = [];
if ('' !== trim($legacyKeyMapJson)) {
    $decodedMap = json_decode($legacyKeyMapJson, true);
    if (is_array($decodedMap)) {
        foreach ($decodedMap as $oldKey => $newKey) {
            if (is_scalar($newKey)) {
                $old = trim((string) $oldKey);
                $new = trim((string) $newKey);
                if ('' !== $old && '' !== $new) {
                    $legacyKeyMap[$old] = $new;
                }
            }
        }
    } elseif (in_array($func, ['convert', 'data_dryrun', 'data_apply'], true)) {
        $pageMessages .= rex_view::warning(rex_i18n::msg('mform_migration_mapping_json_invalid'));
    }
}
if ('' !== $legacyKey1Target) {
    $legacyKeyMap['1'] = $legacyKey1Target;
}

$pageUrl = rex_url::currentBackendPage();
$convertAction = $pageUrl . '#mform-migration-tool';
$dataAction = $pageUrl . '#mform-migration-data';
$batchAction = $pageUrl . '#mform-migration-batch';

// Historientabelle fuer Reassign/Rollback bei Bedarf anlegen.
rex_sql::factory()->setQuery(
    'CREATE TABLE IF NOT EXISTS ' . $reassignHistoryTable . ' (
        id int(10) unsigned NOT NULL AUTO_INCREMENT,
        reassign_token varchar(32) NOT NULL,
        slice_id int(10) unsigned NOT NULL,
        old_module_id int(10) unsigned NOT NULL,
        new_module_id int(10) unsigned NOT NULL,
        createdate datetime NOT NULL,
        createuser varchar(255) NOT NULL,
        reverted tinyint(1) NOT NULL DEFAULT 0,
        revertedate datetime NULL,
        PRIMARY KEY (id),
        KEY reassign_token_idx (reassign_token),
        KEY slice_id_idx (slice_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
);

// Fuer die Migration nur Module mit MBlock-Bezug laden (Input/Output).
$allModules = rex_sql::factory()->getArray(
    'SELECT id, name FROM ' . rex::getTable('module') . '
     WHERE input LIKE :mblock OR output LIKE :mblock
     ORDER BY name ASC, id ASC',
    ['mblock' => '%MBlock%'],
);

if ([] === $allModules) {
    // Fallback: Wenn keine Treffer gefunden werden, alle Module anzeigen.
    $allModules = rex_sql::factory()->getArray(
        'SELECT id, name FROM ' . rex::getTable('module') . ' ORDER BY name ASC, id ASC'
    );
    $pageMessages .= rex_view::info(rex_i18n::msg('mform_migration_select_module_fallback_all'));
}

// Modul aus DB laden.
$loadModuleId = rex_request('load_module_id', 'int', 0);
if ('load_module' === $func && $loadModuleId > 0) {
    $modSql = rex_sql::factory();
    $modSql->setQuery('SELECT input, output FROM ' . rex::getTable('module') . ' WHERE id = :id LIMIT 1', ['id' => $loadModuleId]);
    if ($modSql->getRows() === 1) {
        $inputCode = (string) $modSql->getValue('input');
        $outputCode = (string) $modSql->getValue('output');
        $pageMessages .= rex_view::success(rex_i18n::msg('mform_migration_module_loaded', $loadModuleId));
    }
}

if ('convert' === $func) {
    if (!rex_csrf_token::factory('mform_migration')->isValid()) {
        echo rex_view::error(rex_i18n::msg('csrf_token_invalid'));
    } else {
        if ('' !== trim($inputCode)) {
            $inputResult = $converter->convertInput($inputCode, $repeaterId);
        }
        if ('' !== trim($outputCode)) {
            $outputResult = $converter->convertOutput($outputCode, $repeaterId);
        }
        if ('' !== trim($dataValue)) {
            $dataResult = $converter->convertData($dataValue, $repeaterId, $legacyKeyMap);
        }

        if (null !== $inputResult || null !== $outputResult || null !== $dataResult) {
            $convertMessage .= rex_view::success(rex_i18n::msg('mform_migration_convert_done'));
        }

        if ($createConvertedModule) {
            if ($loadModuleId <= 0) {
                $moduleCreateMessage .= rex_view::error(rex_i18n::msg('mform_migration_create_missing_module'));
            } else {
                $inputForNewModule = null !== $inputResult ? $inputResult['code'] : trim($inputCode);
                $outputForNewModule = null !== $outputResult ? $outputResult['code'] : trim($outputCode);

                if ('' === trim((string) $inputForNewModule) && '' === trim((string) $outputForNewModule)) {
                    $moduleCreateMessage .= rex_view::info(rex_i18n::msg('mform_migration_create_nothing'));
                } else {
                    $sourceSql = rex_sql::factory();
                    $sourceSql->setQuery('SELECT * FROM ' . rex::getTable('module') . ' WHERE id = :id LIMIT 1', ['id' => $loadModuleId]);

                    if (1 !== $sourceSql->getRows()) {
                        $moduleCreateMessage .= rex_view::error(rex_i18n::msg('mform_migration_create_missing_module'));
                    } else {
                        $source = $sourceSql->getArray()[0];

                        $timestamp = date('Ymd_His');
                        $suffix = substr((string) md5((string) microtime(true) . (string) $loadModuleId), 0, 6);
                        $newKey = 'mfr_' . $timestamp . '_' . $suffix;
                        $newName = 'mfr_' . $timestamp . ' ' . (string) $source['name'];

                        $insertSql = rex_sql::factory();
                        $insertSql->setTable(rex::getTable('module'));

                        foreach ($source as $column => $value) {
                            if ('id' === (string) $column) {
                                continue;
                            }
                            $insertSql->setValue((string) $column, $value);
                        }

                        $insertSql->setValue('name', $newName);
                        if (array_key_exists('key', $source)) {
                            $insertSql->setValue('key', $newKey);
                        }
                        if ('' !== trim((string) $inputForNewModule)) {
                            $insertSql->setValue('input', (string) $inputForNewModule);
                        }
                        if ('' !== trim((string) $outputForNewModule)) {
                            $insertSql->setValue('output', (string) $outputForNewModule);
                        }

                        $insertSql->insert();
                        $newModuleId = (int) $insertSql->getLastId();
                        $createdKey = array_key_exists('key', $source) ? $newKey : '-';

                        $loadModuleId = $newModuleId;
                            $lastCreatedModuleId = $newModuleId;
                        $moduleCreateMessage .= rex_view::success(rex_i18n::msg('mform_migration_create_success', $newModuleId, $createdKey));
                    }
                }
            }
        }
    }
}

// Batch-Dry-Run: Slices eines Moduls in den Speicher konvertieren (kein Schreiben).
if ('data_dryrun' === $func) {
    if (!rex_csrf_token::factory('mform_migration')->isValid()) {
        echo rex_view::error(rex_i18n::msg('csrf_token_invalid'));
    } elseif ($batchModuleId > 0) {
        $batchResult = $migrator->dryRun($batchModuleId, $batchSlotId, $legacyKeyMap);
        $batchMessages = rex_view::success(rex_i18n::msg('mform_migration_batch_dryrun_done'));
    }
}

// Batch-Apply: ausgewaehlte Slices wirklich schreiben, danach erneut Dry-Run anzeigen.
if ('data_apply' === $func) {
    if (!rex_csrf_token::factory('mform_migration')->isValid()) {
        echo rex_view::error(rex_i18n::msg('csrf_token_invalid'));
    } elseif ($batchModuleId > 0) {
        $sliceIds = [];
        foreach ($batchSliceIds as $sid) {
            $sid = (int) $sid;
            if ($sid > 0) {
                $sliceIds[] = $sid;
            }
        }
        if ([] === $sliceIds) {
            $batchApplyResult = [
                'updated' => 0,
                'skipped' => 0,
                'errors' => [],
            ];
            $batchMessages = rex_view::info(rex_i18n::msg('mform_migration_batch_nothing_selected'));
        } else {
            $batchApplyResult = $migrator->apply($sliceIds, $batchSlotId, $legacyKeyMap);
        }
        $batchResult = $migrator->dryRun($batchModuleId, $batchSlotId, $legacyKeyMap);
    }
}

$csrf = rex_csrf_token::factory('mform_migration')->getHiddenField();

$sliceReassignMessage = '';

// Slices auf anderes Modul umhaengen.
if ('slice_reassign' === $func) {
    if (!rex_csrf_token::factory('mform_migration')->isValid()) {
        $sliceReassignMessage .= rex_view::error(rex_i18n::msg('csrf_token_invalid'));
    } elseif ($reassignTargetModuleId <= 0) {
        $sliceReassignMessage .= rex_view::error(rex_i18n::msg('mform_migration_reassign_missing_target'));
    } else {
        $validIds = [];
        foreach ($reassignSliceIds as $sid) {
            $sid = (int) $sid;
            if ($sid > 0) {
                $validIds[] = $sid;
            }
        }
        if ([] === $validIds) {
            $sliceReassignMessage .= rex_view::info(rex_i18n::msg('mform_migration_reassign_none_selected'));
        } else {
            $token = substr((string) md5((string) microtime(true) . implode('-', $validIds) . '-' . $reassignTargetModuleId), 0, 12);
            $placeholders = implode(',', array_fill(0, count($validIds), '?'));

            // Vorherigen module_id-Stand protokollieren (Rollback-Basis).
            $oldRows = rex_sql::factory()->getArray(
                'SELECT id, module_id FROM ' . rex::getTable('article_slice') . ' WHERE id IN (' . $placeholders . ')',
                $validIds,
            );

            foreach ($oldRows as $oldRow) {
                $ins = rex_sql::factory();
                $ins->setTable($reassignHistoryTable);
                $ins->setValue('reassign_token', $token);
                $ins->setValue('slice_id', (int) $oldRow['id']);
                $ins->setValue('old_module_id', (int) $oldRow['module_id']);
                $ins->setValue('new_module_id', $reassignTargetModuleId);
                $ins->setValue('createdate', date('Y-m-d H:i:s'));
                $ins->setValue('createuser', rex::getUser() ? (string) rex::getUser()->getLogin() : 'system');
                $ins->setValue('reverted', 0);
                $ins->insert();
            }

            $params = array_merge([$reassignTargetModuleId], $validIds);
            rex_sql::factory()->setQuery(
                'UPDATE ' . rex::getTable('article_slice') . ' SET module_id = ? WHERE id IN (' . $placeholders . ')',
                $params,
            );
            $lastReassignToken = $token;
            $sliceReassignMessage .= rex_view::success(rex_i18n::msg('mform_migration_reassign_success', count($validIds), $reassignTargetModuleId));
            $sliceReassignMessage .= rex_view::info(rex_i18n::msg('mform_migration_reassign_token', $token));
        }
    }
    if ($batchModuleId > 0) {
        $batchResult = $migrator->dryRun($batchModuleId, $batchSlotId, $legacyKeyMap);
    }
}

// Letzte Reassign-Aktion rueckgaengig machen.
if ('slice_reassign_revert' === $func) {
    if (!rex_csrf_token::factory('mform_migration')->isValid()) {
        $sliceReassignMessage .= rex_view::error(rex_i18n::msg('csrf_token_invalid'));
    } else {
        $token = trim($lastReassignToken);
        if ('' === $token) {
            $latest = rex_sql::factory()->getArray(
                'SELECT reassign_token FROM ' . $reassignHistoryTable . ' WHERE reverted = 0 ORDER BY id DESC LIMIT 1'
            );
            if ([] !== $latest) {
                $token = (string) $latest[0]['reassign_token'];
            }
        }

        if ('' === $token) {
            $sliceReassignMessage .= rex_view::info(rex_i18n::msg('mform_migration_reassign_revert_none'));
        } else {
            $rows = rex_sql::factory()->getArray(
                'SELECT id, slice_id, old_module_id FROM ' . $reassignHistoryTable . ' WHERE reassign_token = :t AND reverted = 0 ORDER BY id ASC',
                ['t' => $token],
            );

            if ([] === $rows) {
                $sliceReassignMessage .= rex_view::info(rex_i18n::msg('mform_migration_reassign_revert_none_for_token', $token));
            } else {
                $count = 0;
                foreach ($rows as $row) {
                    rex_sql::factory()->setQuery(
                        'UPDATE ' . rex::getTable('article_slice') . ' SET module_id = :m WHERE id = :id',
                        [
                            'm' => (int) $row['old_module_id'],
                            'id' => (int) $row['slice_id'],
                        ],
                    );
                    ++$count;
                }

                rex_sql::factory()->setQuery(
                    'UPDATE ' . $reassignHistoryTable . ' SET reverted = 1, revertedate = :dt WHERE reassign_token = :t AND reverted = 0',
                    [
                        'dt' => date('Y-m-d H:i:s'),
                        't' => $token,
                    ],
                );

                $sliceReassignMessage .= rex_view::success(rex_i18n::msg('mform_migration_reassign_revert_success', $count, $token));
                $lastReassignToken = '';
            }
        }
    }

    if ($batchModuleId > 0) {
        $batchResult = $migrator->dryRun($batchModuleId, $batchSlotId, $legacyKeyMap);
    }
}

// ── Intro ─────────────────────────────────────────────────────────────────
$introBody = '<p>' . rex_i18n::msg('mform_migration_intro') . '</p>'
    . '<ul>'
    . '<li>' . rex_i18n::msg('mform_migration_intro_input') . '</li>'
    . '<li>' . rex_i18n::msg('mform_migration_intro_output') . '</li>'
    . '</ul>'
    . '<div class="alert alert-warning"><i class="rex-icon fa-exclamation-triangle"></i> ' . rex_i18n::msg('mform_migration_limitations') . '</div>'
    . '<p class="text-muted"><i class="rex-icon fa-info-circle"></i> ' . rex_i18n::msg('mform_migration_intro_hint') . '</p>';

$fragment = new rex_fragment();
$fragment->setVar('title', rex_i18n::msg('mform_migration'), false);
$fragment->setVar('body', $introBody, false);
echo $pageMessages;
echo $fragment->parse('core/page/section.php');

/**
 * Rendert die Hinweis-/Warnungs-Liste eines Konvertierungsergebnisses.
 *
 * @param array{code: string, notes: list<string>, warnings: list<string>}|null $result
 */
$renderResult = static function (?array $result, string $textareaName, string $emptyMsg): string {
    if (null === $result) {
        return '<p class="text-muted">' . rex_escape($emptyMsg) . '</p>';
    }

    $html = '';

    if ([] !== $result['warnings']) {
        $items = '';
        foreach ($result['warnings'] as $w) {
            $items .= '<li>' . rex_escape($w) . '</li>';
        }
        $html .= '<div class="alert alert-warning"><strong><i class="rex-icon fa-exclamation-triangle"></i> '
            . rex_i18n::msg('mform_migration_warnings') . '</strong><ul class="rex-mb-0">' . $items . '</ul></div>';
    }

    if ([] !== $result['notes']) {
        $items = '';
        foreach ($result['notes'] as $n) {
            $items .= '<li>' . rex_escape($n) . '</li>';
        }
        $html .= '<div class="alert alert-info"><strong><i class="rex-icon fa-info-circle"></i> '
            . rex_i18n::msg('mform_migration_notes') . '</strong><ul class="rex-mb-0">' . $items . '</ul></div>';
    }

    $html .= '<label class="control-label">' . rex_i18n::msg('mform_migration_result') . '</label>';
    $html .= '<textarea class="form-control" name="' . rex_escape($textareaName) . '" rows="18" readonly '
        . 'style="font-family:monospace;font-size:12px;white-space:pre;" onclick="this.select();">'
        . rex_escape($result['code']) . '</textarea>';

    return $html;
};

/**
 * Rendert das Ergebnis der Datenmigration (JSON statt Code).
 *
 * @param array{json: string, count: int, notes: list<string>, warnings: list<string>}|null $result
 */
$renderDataResult = static function (?array $result): string {
    if (null === $result) {
        return '<p class="text-muted">' . rex_escape(rex_i18n::msg('mform_migration_data_empty')) . '</p>';
    }

    $html = '';

    if ([] !== $result['warnings']) {
        $items = '';
        foreach ($result['warnings'] as $w) {
            $items .= '<li>' . rex_escape($w) . '</li>';
        }
        $html .= '<div class="alert alert-warning"><strong><i class="rex-icon fa-exclamation-triangle"></i> '
            . rex_i18n::msg('mform_migration_warnings') . '</strong><ul class="rex-mb-0">' . $items . '</ul></div>';
    }

    if ([] !== $result['notes']) {
        $items = '';
        foreach ($result['notes'] as $n) {
            $items .= '<li>' . rex_escape($n) . '</li>';
        }
        $html .= '<div class="alert alert-info"><strong><i class="rex-icon fa-info-circle"></i> '
            . rex_i18n::msg('mform_migration_notes') . '</strong><ul class="rex-mb-0">' . $items . '</ul></div>';
    }

    $html .= '<label class="control-label">' . rex_i18n::msg('mform_migration_data_result') . '</label>';
    $html .= '<textarea class="form-control" name="data_result" rows="10" readonly '
        . 'style="font-family:monospace;font-size:12px;white-space:pre;" onclick="this.select();">'
        . rex_escape($result['json']) . '</textarea>';

    return $html;
};

// ── Formular ───────────────────────────────────────────────────────────────
// Modul-Auswahl Dropdown
$moduleSelectOptions = '<option value="0">&ndash; ' . rex_i18n::msg('mform_migration_select_module') . '</option>';
foreach ($allModules as $mod) {
    $sel = ((int) $mod['id'] === $loadModuleId) ? ' selected' : '';
    $moduleSelectOptions .= '<option value="' . (int) $mod['id'] . '"' . $sel . '>'
        . rex_escape((string) $mod['name']) . ' [' . (int) $mod['id'] . ']</option>';
}

$content = '
    <div id="mform-migration-tool"></div>
    ' . $convertMessage . '
    ' . $moduleCreateMessage . '
<form action="' . $convertAction . '" method="post">
    ' . $csrf . '
    <input type="hidden" name="func" value="convert">

    <div class="row">
        <div class="col-sm-6">
            <div class="form-group">
                <label class="control-label" for="mform-migration-load-module">' . rex_i18n::msg('mform_migration_select_module_label') . '</label>
                <div class="input-group">
                    <select class="form-control" id="mform-migration-load-module" name="load_module_id">' . $moduleSelectOptions . '</select>
                    <span class="input-group-btn">
                        <button type="submit" name="func" value="load_module" class="btn btn-default"><i class="rex-icon fa-download"></i> ' . rex_i18n::msg('mform_migration_load_module_btn') . '</button>
                    </span>
                </div>
                <p class="help-block rex-note">' . rex_i18n::msg('mform_migration_select_module_note') . '</p>
            </div>
        </div>
        <div class="col-sm-3">
            <div class="form-group">
                <label class="control-label" for="mform-migration-repeater-id">' . rex_i18n::msg('mform_migration_repeater_id') . '</label>
                <input type="text" class="form-control" id="mform-migration-repeater-id" name="repeater_id" value="' . rex_escape($repeaterId) . '">
                <p class="help-block rex-note">' . rex_i18n::msg('mform_migration_repeater_id_note') . '</p>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="form-group">
                <label class="control-label" for="mform-migration-input"><i class="rex-icon fa-sign-in"></i> ' . rex_i18n::msg('mform_migration_input_label') . '</label>
                <textarea class="form-control" id="mform-migration-input" name="input_code" rows="18" style="font-family:monospace;font-size:12px;white-space:pre;">' . rex_escape($inputCode) . '</textarea>
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                <label class="control-label" for="mform-migration-output"><i class="rex-icon fa-sign-out"></i> ' . rex_i18n::msg('mform_migration_output_label') . '</label>
                <textarea class="form-control" id="mform-migration-output" name="output_code" rows="18" style="font-family:monospace;font-size:12px;white-space:pre;">' . rex_escape($outputCode) . '</textarea>
            </div>
        </div>
    </div>

    <div class="rex-form-panel-footer">
        <div class="checkbox" style="margin-top:0; margin-bottom:10px;">
            <label>
                <input type="checkbox" name="create_converted_module" value="1"' . ($createConvertedModule ? ' checked' : '') . ' onclick="if(this.checked){return confirm(\'' . rex_escape(rex_i18n::msg('mform_migration_create_confirm')) . '\');}">
                ' . rex_i18n::msg('mform_migration_create_module') . '
            </label>
            <p class="help-block rex-note" style="margin-bottom:0;">' . rex_i18n::msg('mform_migration_create_note') . '</p>
        </div>
        <div class="btn-toolbar">
            <button type="submit" class="btn btn-primary"><i class="rex-icon fa-cogs"></i> ' . rex_i18n::msg('mform_migration_convert') . '</button>
        </div>
    </div>
</form>';

$fragment = new rex_fragment();
$fragment->setVar('title', rex_i18n::msg('mform_migration_tool'), false);
$fragment->setVar('body', $content, false);
echo $fragment->parse('core/page/section.php');

// ── Ergebnisse (ausserhalb der Form, eigene Section) ─────────────────────────
if (null !== $inputResult || null !== $outputResult) {
    $resultsContent = '<div class="row">
        <div class="col-md-6">
            <h5><i class="rex-icon fa-sign-in"></i> ' . rex_i18n::msg('mform_migration_input_label') . '</h5>
            ' . $renderResult($inputResult, 'input_result', rex_i18n::msg('mform_migration_input_empty')) . '
        </div>
        <div class="col-md-6">
            <h5><i class="rex-icon fa-sign-out"></i> ' . rex_i18n::msg('mform_migration_output_label') . '</h5>
            ' . $renderResult($outputResult, 'output_result', rex_i18n::msg('mform_migration_output_empty')) . '
        </div>
    </div>';
    $fragment = new rex_fragment();
    $fragment->setVar('title', rex_i18n::msg('mform_migration_result'), false);
    $fragment->setVar('body', $resultsContent, false);
    echo $fragment->parse('core/page/section.php');
}

// ── Datenmigration ───────────────────────────────────────────────────────────
$dataContent = '
<div id="mform-migration-data"></div>
<form action="' . $dataAction . '" method="post">
    ' . $csrf . '
    <input type="hidden" name="func" value="convert">
    <input type="hidden" name="repeater_id" value="' . rex_escape($repeaterId) . '">

    <p>' . rex_i18n::msg('mform_migration_data_intro') . '</p>

    <div class="form-group">
        <label class="control-label" for="mform-migration-data"><i class="rex-icon fa-database"></i> ' . rex_i18n::msg('mform_migration_data_label') . '</label>
        <textarea class="form-control" id="mform-migration-data" name="data_value" rows="8" style="font-family:monospace;font-size:12px;white-space:pre;">' . rex_escape($dataValue) . '</textarea>
        <p class="help-block rex-note">' . rex_i18n::msg('mform_migration_data_note') . '</p>
    </div>

    <div class="row">
        <div class="col-sm-4">
            <div class="form-group">
                <label class="control-label" for="mform-migration-legacy-1">' . rex_i18n::msg('mform_migration_mapping_key1') . '</label>
                <input type="text" class="form-control" id="mform-migration-legacy-1" name="legacy_key_1_target" value="' . rex_escape($legacyKey1Target) . '" placeholder="link">
            </div>
        </div>
        <div class="col-sm-8">
            <div class="form-group">
                <label class="control-label" for="mform-migration-legacy-map-json">' . rex_i18n::msg('mform_migration_mapping_json') . '</label>
                <input type="text" class="form-control" id="mform-migration-legacy-map-json" name="legacy_key_map_json" value="' . rex_escape($legacyKeyMapJson) . '" placeholder="{&quot;1&quot;:&quot;link&quot;}">
                <p class="help-block rex-note">' . rex_i18n::msg('mform_migration_mapping_note') . '</p>
            </div>
        </div>
    </div>

    <div class="rex-form-panel-footer">
        <div class="btn-toolbar">
            <button type="submit" class="btn btn-primary"><i class="rex-icon fa-database"></i> ' . rex_i18n::msg('mform_migration_data_convert') . '</button>
        </div>
    </div>
</form>';

$fragment = new rex_fragment();
$fragment->setVar('title', rex_i18n::msg('mform_migration_data_tool'), false);
$fragment->setVar('body', $dataContent, false);
echo $fragment->parse('core/page/section.php');

if (null !== $dataResult) {
    $dataResultContent = $renderDataResult($dataResult);
    $fragment = new rex_fragment();
    $fragment->setVar('title', rex_i18n::msg('mform_migration_data_result'), false);
    $fragment->setVar('body', $dataResultContent, false);
    echo $fragment->parse('core/page/section.php');
}

// ── Batch-Datenmigration (Datenbank) ─────────────────────────────────────────
$modules = $migrator->getModulesWithSlices();

$moduleOptions = '<option value="0">&ndash;</option>';
foreach ($modules as $mod) {
    $selected = ($mod['id'] === $batchModuleId) ? ' selected' : '';
    $label = $mod['name'] . ' [' . $mod['id'] . '] – ' . rex_i18n::msg('mform_migration_batch_slices', $mod['slice_count']);
    $moduleOptions .= '<option value="' . $mod['id'] . '"' . $selected . '>' . rex_escape($label) . '</option>';
}

$batchMessages = $batchMessages ?? '';
if (null !== $batchApplyResult) {
    if ($batchApplyResult['updated'] > 0) {
        $batchMessages .= rex_view::success(rex_i18n::msg('mform_migration_batch_applied', $batchApplyResult['updated']));
    }
    if ($batchApplyResult['skipped'] > 0) {
        $batchMessages .= rex_view::info(rex_i18n::msg('mform_migration_batch_skipped', $batchApplyResult['skipped']));
    }
    foreach ($batchApplyResult['errors'] as $err) {
        $batchMessages .= rex_view::error(rex_escape($err));
    }
}

// Dry-Run-Tabelle (falls vorhanden) als auswaehlbare Apply-Form rendern.
$batchTable = '';
if (null !== $batchResult && [] !== $batchResult['rows']) {
    $summary = rex_i18n::msg(
        'mform_migration_batch_summary',
        $batchResult['total'],
        $batchResult['changed'],
        $batchResult['warnings'],
        rex_escape($batchResult['column']),
    );

    $rowsHtml = '';
    $selectableCount = 0;
    foreach ($batchResult['rows'] as $row) {
        if ($row['skipped']) {
            $stateBadge = '<span class="label label-default">' . rex_i18n::msg('mform_migration_batch_state_skip') . '</span>';
        } elseif ($row['changed']) {
            $stateBadge = '<span class="label label-success">' . rex_i18n::msg('mform_migration_batch_state_change') . '</span>';
        } else {
            $stateBadge = '<span class="label label-default">' . rex_i18n::msg('mform_migration_batch_state_nochange') . '</span>';
        }

        $warnHtml = '';
        if ([] !== $row['warnings']) {
            $items = '';
            foreach ($row['warnings'] as $w) {
                $items .= '<li>' . rex_escape($w) . '</li>';
            }
            $warnHtml = '<ul class="text-warning rex-mb-0" style="font-size:11px;">' . $items . '</ul>';
        }

        $checkbox = '<span class="text-muted">&ndash;</span>';
        if ($row['changed']) {
            ++$selectableCount;
            $checkbox = '<input type="checkbox" name="slice_ids[]" value="' . $row['slice_id'] . '" checked>';
        }

        $rowsHtml .= '<tr>'
            . '<td>' . $checkbox . '</td>'
            . '<td>' . $row['slice_id'] . '</td>'
            . '<td>' . $row['article_id']
            . ('' !== trim($row['article_name']) ? ' &ndash; ' . rex_escape($row['article_name']) : '')
            . '</td>'
            . '<td>' . $row['clang_id'] . '</td>'
            . '<td>' . $row['count'] . '</td>'
            . '<td>' . $stateBadge . $warnHtml . '</td>'
            . '</tr>';
    }

    $batchTable = '
    <p class="rex-note rex-mt-2">' . $summary . '</p>';

    if ($selectableCount > 0) {
        $batchTable .= '
    <form action="' . $batchAction . '" method="post" onsubmit="return confirm(\'' . rex_escape(rex_i18n::msg('mform_migration_batch_confirm')) . '\');">
        ' . $csrf . '
        <input type="hidden" name="func" value="data_apply">
        <input type="hidden" name="batch_module_id" value="' . $batchModuleId . '">
        <input type="hidden" name="batch_slot_id" value="' . rex_escape($batchSlotId) . '">
        <input type="hidden" name="legacy_key_1_target" value="' . rex_escape($legacyKey1Target) . '">
        <input type="hidden" name="legacy_key_map_json" value="' . rex_escape($legacyKeyMapJson) . '">
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead><tr>
                    <th style="width:32px;"><i class="rex-icon fa-check"></i></th>
                    <th>' . rex_i18n::msg('mform_migration_batch_col_slice') . '</th>
                    <th>' . rex_i18n::msg('mform_migration_batch_col_article') . '</th>
                    <th>' . rex_i18n::msg('mform_migration_batch_col_clang') . '</th>
                    <th>' . rex_i18n::msg('mform_migration_batch_col_count') . '</th>
                    <th>' . rex_i18n::msg('mform_migration_batch_col_state') . '</th>
                </tr></thead>
                <tbody>' . $rowsHtml . '</tbody>
            </table>
        </div>
        <div class="alert alert-warning"><i class="rex-icon fa-exclamation-triangle"></i> ' . rex_i18n::msg('mform_migration_batch_backup') . '</div>
        <button type="submit" class="btn btn-save"><i class="rex-icon fa-database"></i> ' . rex_i18n::msg('mform_migration_batch_apply') . '</button>
    </form>';
    }

    // ── Reassign-Formular (immer sichtbar, wenn Slices vorhanden) ──────────
    $reassignModuleOptions = '<option value="0">&ndash;</option>';
    foreach ($allModules as $mod) {
        $selRa = ((int) $mod['id'] === $lastCreatedModuleId) ? ' selected' : '';
        $reassignModuleOptions .= '<option value="' . (int) $mod['id'] . '"' . $selRa . '>'
            . rex_escape((string) $mod['name']) . ' [' . (int) $mod['id'] . ']</option>';
    }

    // Checkboxen fuer alle Slices (nicht nur geaenderte)
    $reassignRowsHtml = '';
    foreach ($batchResult['rows'] as $row) {
        if ($row['skipped']) {
            continue;
        }
        $reassignRowsHtml .= '<tr>'
            . '<td><input type="checkbox" name="reassign_slice_ids[]" value="' . $row['slice_id'] . '" checked></td>'
            . '<td>' . $row['slice_id'] . '</td>'
            . '<td>' . $row['article_id']
            . ('' !== trim($row['article_name']) ? ' &ndash; ' . rex_escape($row['article_name']) : '')
            . '</td>'
            . '<td>' . $row['clang_id'] . '</td>'
            . '</tr>';
    }

    $batchTable .= '
    <hr>
    ' . $sliceReassignMessage . '
    <form action="' . $batchAction . '" method="post" onsubmit="return confirm(\'' . rex_escape(rex_i18n::msg('mform_migration_reassign_confirm')) . '\');">
        ' . $csrf . '
        <input type="hidden" name="func" value="slice_reassign">
        <input type="hidden" name="batch_module_id" value="' . $batchModuleId . '">
        <input type="hidden" name="batch_slot_id" value="' . rex_escape($batchSlotId) . '">
        <input type="hidden" name="last_created_module_id" value="' . $lastCreatedModuleId . '">
        <input type="hidden" name="last_reassign_token" value="' . rex_escape($lastReassignToken) . '">
        <input type="hidden" name="legacy_key_1_target" value="' . rex_escape($legacyKey1Target) . '">
        <input type="hidden" name="legacy_key_map_json" value="' . rex_escape($legacyKeyMapJson) . '">
        <h5><i class="rex-icon fa-exchange"></i> ' . rex_i18n::msg('mform_migration_reassign_title') . '</h5>
        <div class="row">
            <div class="col-sm-8">
                <div class="form-group">
                    <label class="control-label" for="mform-migration-reassign-target">' . rex_i18n::msg('mform_migration_reassign_target') . '</label>
                    <select class="form-control" id="mform-migration-reassign-target" name="reassign_target_module_id">' . $reassignModuleOptions . '</select>
                    <p class="help-block rex-note">' . rex_i18n::msg('mform_migration_reassign_note') . '</p>
                </div>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-striped table-condensed">
                <thead><tr>
                    <th style="width:32px;"><i class="rex-icon fa-check"></i></th>
                    <th>' . rex_i18n::msg('mform_migration_batch_col_slice') . '</th>
                    <th>' . rex_i18n::msg('mform_migration_batch_col_article') . '</th>
                    <th>' . rex_i18n::msg('mform_migration_batch_col_clang') . '</th>
                </tr></thead>
                <tbody>' . $reassignRowsHtml . '</tbody>
            </table>
        </div>
        <div class="btn-toolbar">
            <button type="submit" class="btn btn-warning"><i class="rex-icon fa-exchange"></i> ' . rex_i18n::msg('mform_migration_reassign_btn') . '</button>
        </div>
    </form>
    <form action="' . $batchAction . '" method="post" style="margin-top:8px;" onsubmit="return confirm(\'' . rex_escape(rex_i18n::msg('mform_migration_reassign_revert_confirm')) . '\');">
        ' . $csrf . '
        <input type="hidden" name="func" value="slice_reassign_revert">
        <input type="hidden" name="batch_module_id" value="' . $batchModuleId . '">
        <input type="hidden" name="batch_slot_id" value="' . rex_escape($batchSlotId) . '">
        <input type="hidden" name="last_created_module_id" value="' . $lastCreatedModuleId . '">
        <input type="hidden" name="last_reassign_token" value="' . rex_escape($lastReassignToken) . '">
        <input type="hidden" name="legacy_key_1_target" value="' . rex_escape($legacyKey1Target) . '">
        <input type="hidden" name="legacy_key_map_json" value="' . rex_escape($legacyKeyMapJson) . '">
        <button type="submit" class="btn btn-default"><i class="rex-icon fa-undo"></i> ' . rex_i18n::msg('mform_migration_reassign_revert_btn') . '</button>
    </form>';

    if (0 === $selectableCount) {
        $batchTable .= '
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead><tr>
                    <th style="width:32px;"><i class="rex-icon fa-check"></i></th>
                    <th>' . rex_i18n::msg('mform_migration_batch_col_slice') . '</th>
                    <th>' . rex_i18n::msg('mform_migration_batch_col_article') . '</th>
                    <th>' . rex_i18n::msg('mform_migration_batch_col_clang') . '</th>
                    <th>' . rex_i18n::msg('mform_migration_batch_col_count') . '</th>
                    <th>' . rex_i18n::msg('mform_migration_batch_col_state') . '</th>
                </tr></thead>
                <tbody>' . $rowsHtml . '</tbody>
            </table>
        </div>
        <div class="alert alert-info"><i class="rex-icon fa-info-circle"></i> ' . rex_i18n::msg('mform_migration_batch_nothing_selectable') . '</div>';
    }
} elseif (null !== $batchResult) {
    $batchTable = '<p class="text-muted rex-mt-2">' . rex_escape(rex_i18n::msg('mform_migration_batch_no_slices')) . '</p>';
}

$batchContent = '<div id="mform-migration-batch"></div>' . $batchMessages . '
<form action="' . $batchAction . '" method="post">
    ' . $csrf . '
    <input type="hidden" name="func" value="data_dryrun">
    <input type="hidden" name="legacy_key_1_target" value="' . rex_escape($legacyKey1Target) . '">
    <input type="hidden" name="legacy_key_map_json" value="' . rex_escape($legacyKeyMapJson) . '">

    <p>' . rex_i18n::msg('mform_migration_batch_intro') . '</p>

    <div class="row">
        <div class="col-sm-8">
            <div class="form-group">
                <label class="control-label" for="mform-migration-batch-module">' . rex_i18n::msg('mform_migration_batch_module') . '</label>
                <select class="form-control selectpicker" id="mform-migration-batch-module" name="batch_module_id">' . $moduleOptions . '</select>
            </div>
        </div>
        <div class="col-sm-4">
            <div class="form-group">
                <label class="control-label" for="mform-migration-batch-slot">' . rex_i18n::msg('mform_migration_batch_slot') . '</label>
                <input type="text" class="form-control" id="mform-migration-batch-slot" name="batch_slot_id" value="' . rex_escape($batchSlotId) . '">
                <p class="help-block rex-note">' . rex_i18n::msg('mform_migration_batch_slot_note') . '</p>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-sm-4">
            <div class="form-group">
                <label class="control-label" for="mform-migration-batch-legacy-1">' . rex_i18n::msg('mform_migration_mapping_key1') . '</label>
                <input type="text" class="form-control" id="mform-migration-batch-legacy-1" name="legacy_key_1_target" value="' . rex_escape($legacyKey1Target) . '" placeholder="link">
            </div>
        </div>
        <div class="col-sm-8">
            <div class="form-group">
                <label class="control-label" for="mform-migration-batch-map-json">' . rex_i18n::msg('mform_migration_mapping_json') . '</label>
                <input type="text" class="form-control" id="mform-migration-batch-map-json" name="legacy_key_map_json" value="' . rex_escape($legacyKeyMapJson) . '" placeholder="{&quot;1&quot;:&quot;link&quot;}">
                <p class="help-block rex-note">' . rex_i18n::msg('mform_migration_mapping_note') . '</p>
            </div>
        </div>
    </div>

    <div class="rex-form-panel-footer">
        <div class="btn-toolbar">
            <button type="submit" class="btn btn-primary"><i class="rex-icon fa-search"></i> ' . rex_i18n::msg('mform_migration_batch_dryrun') . '</button>
        </div>
    </div>
</form>' . $batchTable;

$fragment = new rex_fragment();
$fragment->setVar('title', rex_i18n::msg('mform_migration_batch_tool'), false);
$fragment->setVar('body', $batchContent, false);
echo $fragment->parse('core/page/section.php');
