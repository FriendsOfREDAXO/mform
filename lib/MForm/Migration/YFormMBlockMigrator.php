<?php

/**
 * @author Friends Of REDAXO
 * @license MIT
 */

namespace FriendsOfRedaxo\MForm\Migration;

use rex;
use rex_addon;
use rex_sql;
use rex_sql_exception;

use function count;
use function in_array;
use function is_string;
use function sprintf;

/**
 * M7: MBlock-Werte in YForm-Tabellen (Wert-Typ `mblock` oder Text-/Textarea-Spalten
 * mit MBlock-JSON) in das Repeater-Format konvertieren. Zieltyp ist ein JSON-Feld:
 * die Spalte behaelt den konvertierten JSON-Text, der Feldtyp kann auf `textarea`
 * umgestellt werden, damit YForm das nicht mehr vorhandene MBlock-Widget nicht rendert.
 *
 * Backup je Datensatz in `rex_mform_migration_backup` (module_id 0, slice_id = Datensatz-Id,
 * slot_column = "yform:tabelle.spalte"), Rollback ueber MBlockToRepeaterMigrator::rollback().
 *
 * @phpstan-type Candidate array{table: string, field: string, label: string, type_name: string, rows: int, with_markers: int}
 * @phpstan-type RowResult array{id: int, count: int, changed: bool, skipped: bool, warnings: list<string>, notes: list<string>}
 */
final class YFormMBlockMigrator
{
    public const COLUMN_PREFIX = 'yform:';

    private MBlockToRepeaterConverter $converter;

    public function __construct(?MBlockToRepeaterConverter $converter = null)
    {
        $this->converter = $converter ?? new MBlockToRepeaterConverter();
    }

    public static function available(): bool
    {
        return rex_addon::get('yform')->isAvailable();
    }

    /**
     * Felder mit MBlock-Bezug: Typ `mblock` oder Text-Spalten, deren Werte MBlock-Marker tragen.
     *
     * @return list<Candidate>
     */
    public function findCandidates(): array
    {
        if (!self::available()) {
            return [];
        }
        $sql = rex_sql::factory();
        $fields = $sql->getArray(
            'SELECT table_name, name, label, type_name FROM ' . rex::getTable('yform_field') . " WHERE type_id = 'value' AND type_name IN ('mblock', 'textarea', 'text', 'json') ORDER BY table_name, prio",
        );

        $candidates = [];
        foreach ($fields as $field) {
            $table = (string) $field['table_name'];
            $column = (string) $field['name'];
            if (!self::validName($table) || !self::validName($column) || !self::columnExists($table, $column)) {
                continue;
            }
            $counts = rex_sql::factory()->getArray(
                'SELECT COUNT(*) AS rows_total, SUM(CASE WHEN `' . $column . "` LIKE '%checkbox_block_hold%' OR `" . $column . "` LIKE '%mblock_offline%' OR `" . $column . "` LIKE '{\"GBS%' THEN 1 ELSE 0 END) AS with_markers FROM `" . $table . '`',
            );
            $rows = (int) ($counts[0]['rows_total'] ?? 0);
            $withMarkers = (int) ($counts[0]['with_markers'] ?? 0);
            if ('mblock' !== $field['type_name'] && 0 === $withMarkers) {
                continue;
            }
            $candidates[] = [
                'table' => $table,
                'field' => $column,
                'label' => is_string($field['label']) ? $field['label'] : '',
                'type_name' => (string) $field['type_name'],
                'rows' => $rows,
                'with_markers' => $withMarkers,
            ];
        }

        return $candidates;
    }

    /**
     * @param array<int|string, string> $legacyKeyMap
     * @param array{merge_columns?: bool, nested?: bool, list_fields?: array<string, string>} $options
     * @return array{rows: list<RowResult>, total: int, changed: int, warnings: int}
     */
    public function dryRun(string $table, string $field, array $legacyKeyMap = [], array $options = []): array
    {
        $empty = ['rows' => [], 'total' => 0, 'changed' => 0, 'warnings' => 0];
        if (!self::validName($table) || !self::validName($field) || !self::columnExists($table, $field)) {
            return $empty;
        }
        $rows = [];
        $changed = 0;
        $warnings = 0;
        foreach (rex_sql::factory()->getArray('SELECT id, `' . $field . '` AS v FROM `' . $table . '` ORDER BY id') as $row) {
            $original = is_string($row['v']) ? $row['v'] : '';
            $result = $this->converter->convertData($original, '1', $legacyKeyMap, $options);
            $hasItems = $result['count'] > 0 && '' !== $result['json'];
            $isChanged = $hasItems && self::normalizeJson($original) !== self::normalizeJson($result['json']);
            if ($isChanged) {
                ++$changed;
            }
            if ($hasItems && [] !== $result['warnings']) {
                ++$warnings;
            }
            $rows[] = [
                'id' => (int) $row['id'],
                'count' => $result['count'],
                'changed' => $isChanged,
                'skipped' => !$hasItems,
                'warnings' => $hasItems ? $result['warnings'] : [],
                'notes' => $result['notes'],
            ];
        }

        return ['rows' => $rows, 'total' => count($rows), 'changed' => $changed, 'warnings' => $warnings];
    }

    /**
     * Schreibt die konvertierten Werte (alle oder nur die angegebenen Datensatz-Ids), mit Backup je Datensatz.
     *
     * @param array<int|string, string> $legacyKeyMap
     * @param array{merge_columns?: bool, nested?: bool, list_fields?: array<string, string>} $options
     * @param list<int>|null $ids
     * @return array{updated: int, skipped: int, errors: list<string>, token: string}
     */
    public function apply(string $table, string $field, array $legacyKeyMap = [], array $options = [], ?array $ids = null, ?string $runToken = null): array
    {
        if (!self::validName($table) || !self::validName($field) || !self::columnExists($table, $field)) {
            return ['updated' => 0, 'skipped' => 0, 'errors' => ['Tabelle oder Spalte ungueltig.'], 'token' => ''];
        }
        MBlockToRepeaterMigrator::ensureTables();
        $token = $runToken ?? MBlockToRepeaterMigrator::newToken();
        $updated = 0;
        $skipped = 0;
        $errors = [];

        $where = '';
        $params = [];
        if (null !== $ids) {
            $ids = array_values(array_filter(array_map('intval', $ids), static fn (int $id): bool => $id > 0));
            if ([] === $ids) {
                return ['updated' => 0, 'skipped' => 0, 'errors' => ['Keine Datensaetze ausgewaehlt.'], 'token' => $token];
            }
            $where = ' WHERE id IN (' . implode(',', array_fill(0, count($ids), '?')) . ')';
            $params = $ids;
        }

        foreach (rex_sql::factory()->getArray('SELECT id, `' . $field . '` AS v FROM `' . $table . '`' . $where . ' ORDER BY id', $params) as $row) {
            $original = is_string($row['v']) ? $row['v'] : '';
            $result = $this->converter->convertData($original, '1', $legacyKeyMap, $options);
            if ($result['count'] < 1 || '' === $result['json'] || self::normalizeJson($original) === self::normalizeJson($result['json'])) {
                ++$skipped;
                continue;
            }
            try {
                $backup = rex_sql::factory();
                $backup->setTable(rex::getTable(MBlockToRepeaterMigrator::TABLE_BACKUP));
                $backup->setValue('run_token', $token);
                $backup->setValue('module_id', 0);
                $backup->setValue('slice_id', (int) $row['id']);
                $backup->setValue('slot_column', self::COLUMN_PREFIX . $table . '.' . $field);
                $backup->setValue('old_value', $original);
                $backup->setValue('new_value', $result['json']);
                $backup->setValue('createdate', date('Y-m-d H:i:s'));
                $backup->setValue('createuser', null !== rex::getUser() ? rex::getUser()->getLogin() : (PHP_SAPI === 'cli' ? 'console' : 'system'));
                $backup->setValue('reverted', 0);
                $backup->insert();

                rex_sql::factory()->setQuery('UPDATE `' . $table . '` SET `' . $field . '` = ? WHERE id = ?', [$result['json'], (int) $row['id']]);
                ++$updated;
            } catch (rex_sql_exception $e) {
                $errors[] = sprintf('%s #%d: %s', $table, (int) $row['id'], $e->getMessage());
            }
        }

        return ['updated' => $updated, 'skipped' => $skipped, 'errors' => $errors, 'token' => $token];
    }

    /**
     * Stellt den Feldtyp auf `textarea` um (JSON bleibt in der Spalte), damit YForm kein
     * MBlock-Widget mehr erwartet. Nur fuer Felder vom Typ mblock.
     *
     * @return array{changed: bool, message: string}
     */
    public function switchFieldToTextarea(string $table, string $field): array
    {
        if (!self::available() || !self::validName($table) || !self::validName($field)) {
            return ['changed' => false, 'message' => 'Tabelle oder Spalte ungueltig.'];
        }
        $sql = rex_sql::factory();
        $sql->setQuery('SELECT id, type_name FROM ' . rex::getTable('yform_field') . " WHERE table_name = ? AND name = ? AND type_id = 'value'", [$table, $field]);
        if (0 === $sql->getRows()) {
            return ['changed' => false, 'message' => sprintf('Feld %s.%s nicht gefunden.', $table, $field)];
        }
        if ('mblock' !== (string) $sql->getValue('type_name')) {
            return ['changed' => false, 'message' => sprintf('Feld %s.%s ist bereits vom Typ %s.', $table, $field, (string) $sql->getValue('type_name'))];
        }
        rex_sql::factory()->setQuery('UPDATE ' . rex::getTable('yform_field') . " SET type_name = 'textarea' WHERE id = ?", [(int) $sql->getValue('id')]);
        if (class_exists(\rex_yform_manager_table::class)) {
            \rex_yform_manager_table::deleteCache();
        }

        return ['changed' => true, 'message' => sprintf('Feld %s.%s auf textarea umgestellt.', $table, $field)];
    }

    public static function validName(string $name): bool
    {
        return 1 === preg_match('/^[a-zA-Z0-9_]+$/', $name);
    }

    private static function columnExists(string $table, string $column): bool
    {
        try {
            $columns = rex_sql::factory()->getArray('SHOW COLUMNS FROM `' . $table . '` LIKE ?', [$column]);
        } catch (rex_sql_exception) {
            return false;
        }

        return [] !== $columns;
    }

    private static function normalizeJson(string $raw): string
    {
        $decoded = json_decode(trim($raw), true);
        if (null === $decoded) {
            return trim($raw);
        }

        return (string) json_encode($decoded, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }
}
