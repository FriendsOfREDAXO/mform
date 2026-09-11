<?php

/**
 * @author Friends Of REDAXO
 * @license MIT
 */

namespace FriendsOfRedaxo\MForm\Migration;

use rex;
use rex_sql;
use rex_sql_column;
use rex_sql_exception;
use rex_sql_index;
use rex_sql_table;

use function array_key_exists;
use function count;
use function in_array;
use function is_string;
use function sprintf;
use function trim;

/**
 * Datenmigration MBlock -> Repeater auf Slice-Ebene, mit Backup und Rollback.
 *
 * Liest die Slices eines Moduls aus `rex_article_slice`, konvertiert die
 * MBlock-Werte der Slot-Spalten (`valueN`) und schreibt das Ergebnis bei Bedarf
 * zurueck. Vor jedem Schreiben landet der alte Wert in
 * `rex_mform_migration_backup`; ein Lauf-Token fasst alle Aenderungen eines
 * Durchgangs zusammen und erlaubt den Rollback. Das Umhaengen von Slices auf
 * ein anderes Modul wird in `rex_mform_migration_reassign_history` protokolliert.
 *
 * @phpstan-type DryRunRow array{
 *     slice_id: int,
 *     article_id: int,
 *     article_name: string,
 *     clang_id: int,
 *     count: int,
 *     changed: bool,
 *     skipped: bool,
 *     warnings: list<string>,
 *     notes: list<string>
 * }
 * @phpstan-type DryRunResult array{column: string, rows: list<DryRunRow>, total: int, changed: int, warnings: int}
 * @phpstan-type SlotConfig array{key_map?: array<int|string, string>, options?: array{merge_columns?: bool, nested?: bool, list_fields?: array<string, string>, check_existence?: bool}}
 */
final class MBlockToRepeaterMigrator
{
    public const TABLE_BACKUP = 'mform_migration_backup';
    public const TABLE_REASSIGN = 'mform_migration_reassign_history';

    private MBlockToRepeaterConverter $converter;

    public function __construct(?MBlockToRepeaterConverter $converter = null)
    {
        $this->converter = $converter ?? new MBlockToRepeaterConverter();
    }

    public function getConverter(): MBlockToRepeaterConverter
    {
        return $this->converter;
    }

    /**
     * Legt die Protokolltabellen an (idempotent). Wird aus install.php und
     * vor jedem schreibenden Zugriff aufgerufen.
     */
    public static function ensureTables(): void
    {
        rex_sql_table::get(rex::getTable(self::TABLE_BACKUP))
            ->ensurePrimaryIdColumn()
            ->ensureColumn(new rex_sql_column('run_token', 'varchar(32)'))
            ->ensureColumn(new rex_sql_column('module_id', 'int(10) unsigned', false, '0'))
            ->ensureColumn(new rex_sql_column('slice_id', 'int(10) unsigned'))
            ->ensureColumn(new rex_sql_column('slot_column', 'varchar(191)'))
            ->ensureColumn(new rex_sql_column('old_value', 'longtext', true))
            ->ensureColumn(new rex_sql_column('new_value', 'longtext', true))
            ->ensureColumn(new rex_sql_column('createdate', 'datetime'))
            ->ensureColumn(new rex_sql_column('createuser', 'varchar(255)'))
            ->ensureColumn(new rex_sql_column('reverted', 'tinyint(1)', false, '0'))
            ->ensureColumn(new rex_sql_column('revertedate', 'datetime', true))
            ->ensureIndex(new rex_sql_index('run_token_idx', ['run_token']))
            ->ensureIndex(new rex_sql_index('slice_id_idx', ['slice_id']))
            ->ensure();

        rex_sql_table::get(rex::getTable(self::TABLE_REASSIGN))
            ->ensurePrimaryIdColumn()
            ->ensureColumn(new rex_sql_column('reassign_token', 'varchar(32)'))
            ->ensureColumn(new rex_sql_column('slice_id', 'int(10) unsigned'))
            ->ensureColumn(new rex_sql_column('old_module_id', 'int(10) unsigned'))
            ->ensureColumn(new rex_sql_column('new_module_id', 'int(10) unsigned'))
            ->ensureColumn(new rex_sql_column('createdate', 'datetime'))
            ->ensureColumn(new rex_sql_column('createuser', 'varchar(255)'))
            ->ensureColumn(new rex_sql_column('reverted', 'tinyint(1)', false, '0'))
            ->ensureColumn(new rex_sql_column('revertedate', 'datetime', true))
            ->ensureIndex(new rex_sql_index('reassign_token_idx', ['reassign_token']))
            ->ensureIndex(new rex_sql_index('slice_id_idx', ['slice_id']))
            ->ensure();
    }

    /**
     * Validiert eine Slot-Id (1..20) und liefert den Spaltennamen `valueN`.
     */
    public static function slotColumn(string $slotId): ?string
    {
        $slotId = trim($slotId);
        if (!preg_match('/^\d+$/', $slotId)) {
            return null;
        }
        $n = (int) $slotId;
        if ($n < 1 || $n > 20) {
            return null;
        }

        return 'value' . $n;
    }

    /**
     * Liefert alle Module, die in `rex_article_slice` verwendet werden.
     *
     * @return list<array{id: int, name: string, slice_count: int}>
     */
    public function getModulesWithSlices(): array
    {
        $sql = rex_sql::factory();
        $rows = $sql->getArray(
            'SELECT s.module_id AS id, m.name AS name, COUNT(*) AS slice_count
             FROM ' . rex::getTable('article_slice') . ' s
             LEFT JOIN ' . rex::getTable('module') . ' m ON m.id = s.module_id
             WHERE s.module_id > 0
             GROUP BY s.module_id, m.name
             ORDER BY m.name ASC, s.module_id ASC',
        );

        $modules = [];
        foreach ($rows as $row) {
            $name = is_string($row['name'] ?? null) ? (string) $row['name'] : '';
            $modules[] = [
                'id' => (int) $row['id'],
                'name' => '' !== $name ? $name : '(Modul ' . (int) $row['id'] . ')',
                'slice_count' => (int) $row['slice_count'],
            ];
        }

        return $modules;
    }

    /**
     * Dry-Run fuer einen Slot: konvertiert alle Slices eines Moduls in den
     * Speicher und liefert eine Vorschau, ohne etwas zu schreiben.
     *
     * @param array<int|string, string> $legacyKeyMap
     * @param array{merge_columns?: bool, nested?: bool, list_fields?: array<string, string>, check_existence?: bool} $options
     *
     * @return DryRunResult
     */
    public function dryRun(int $moduleId, string $slotId, array $legacyKeyMap = [], array $options = []): array
    {
        $column = self::slotColumn($slotId);
        if (null === $column) {
            return ['column' => '', 'rows' => [], 'total' => 0, 'changed' => 0, 'warnings' => 0];
        }

        $sql = rex_sql::factory();
        $slices = $sql->getArray(
            'SELECT s.id, s.article_id, s.clang_id, s.' . $column . ' AS slot_value,
                    COALESCE(a.name, \'\') AS article_name
             FROM ' . rex::getTable('article_slice') . ' s
             LEFT JOIN ' . rex::getTable('article') . ' a
                 ON a.id = s.article_id AND a.clang_id = s.clang_id
             WHERE s.module_id = :mid
             ORDER BY s.id ASC',
            ['mid' => $moduleId],
        );

        $rows = [];
        $changedCount = 0;
        $warnCount = 0;

        foreach ($slices as $slice) {
            $original = is_string($slice['slot_value'] ?? null) ? (string) $slice['slot_value'] : '';
            $result = $this->converter->convertData($original, $slotId, $legacyKeyMap, $options);

            $newJson = (string) $result['json'];
            $hasItems = $result['count'] > 0 && '' !== $newJson;
            $changed = $hasItems && $this->normalizeJson($original) !== $this->normalizeJson($newJson);
            $skipped = !$hasItems;

            if ($changed) {
                ++$changedCount;
            }
            if (count($result['warnings']) > 0 && !$skipped) {
                ++$warnCount;
            }

            $rows[] = [
                'slice_id' => (int) $slice['id'],
                'article_id' => (int) $slice['article_id'],
                'article_name' => is_string($slice['article_name'] ?? null) ? (string) $slice['article_name'] : '',
                'clang_id' => (int) $slice['clang_id'],
                'count' => (int) $result['count'],
                'changed' => $changed,
                'skipped' => $skipped,
                'warnings' => $skipped ? [] : $result['warnings'],
                'notes' => $result['notes'],
            ];
        }

        return [
            'column' => $column,
            'rows' => $rows,
            'total' => count($rows),
            'changed' => $changedCount,
            'warnings' => $warnCount,
        ];
    }

    /**
     * Dry-Run fuer mehrere Slots eines Moduls in einem Lauf.
     *
     * @param array<string, SlotConfig> $slots Slot-Id => Konfiguration (Key-Map, Optionen)
     *
     * @return array<string, DryRunResult> Slot-Id => Ergebnis
     */
    public function dryRunSlots(int $moduleId, array $slots): array
    {
        $results = [];
        foreach ($slots as $slotId => $config) {
            $slotId = (string) $slotId;
            $results[$slotId] = $this->dryRun($moduleId, $slotId, $config['key_map'] ?? [], $config['options'] ?? []);
        }

        return $results;
    }

    /**
     * Wendet die Migration eines Slots auf die ausgewaehlten Slices an. Jeder
     * geaenderte Wert wird vorher unter dem Lauf-Token gesichert.
     *
     * @param list<int> $sliceIds
     * @param array<int|string, string> $legacyKeyMap
     * @param array{merge_columns?: bool, nested?: bool, list_fields?: array<string, string>, check_existence?: bool} $options
     *
     * @return array{updated: int, skipped: int, errors: list<string>, token: string}
     */
    public function apply(array $sliceIds, string $slotId, array $legacyKeyMap = [], array $options = [], ?string $runToken = null, int $moduleId = 0): array
    {
        $column = self::slotColumn($slotId);
        if (null === $column || [] === $sliceIds) {
            return ['updated' => 0, 'skipped' => 0, 'errors' => [null === $column ? 'Ungueltige Slot-Id.' : 'Keine Slices ausgewaehlt.'], 'token' => ''];
        }

        self::ensureTables();
        $token = $runToken ?? self::newToken();

        $updated = 0;
        $skipped = 0;
        $errors = [];

        foreach ($sliceIds as $sliceId) {
            $read = rex_sql::factory();
            $read->setQuery(
                'SELECT module_id, ' . $column . ' AS slot_value FROM ' . rex::getTable('article_slice') . ' WHERE id = :id',
                ['id' => $sliceId],
            );
            if (0 === $read->getRows()) {
                $errors[] = sprintf('Slice %d nicht gefunden.', $sliceId);
                continue;
            }

            $original = is_string($read->getValue('slot_value')) ? (string) $read->getValue('slot_value') : '';
            $result = $this->converter->convertData($original, $slotId, $legacyKeyMap, $options);
            $newJson = (string) $result['json'];

            if ($result['count'] < 1 || '' === $newJson) {
                ++$skipped;
                continue;
            }
            if ($this->normalizeJson($original) === $this->normalizeJson($newJson)) {
                ++$skipped;
                continue;
            }

            try {
                $backup = rex_sql::factory();
                $backup->setTable(rex::getTable(self::TABLE_BACKUP));
                $backup->setValue('run_token', $token);
                $backup->setValue('module_id', $moduleId > 0 ? $moduleId : (int) $read->getValue('module_id'));
                $backup->setValue('slice_id', $sliceId);
                $backup->setValue('slot_column', $column);
                $backup->setValue('old_value', $original);
                $backup->setValue('new_value', $newJson);
                $backup->setValue('createdate', date('Y-m-d H:i:s'));
                $backup->setValue('createuser', self::currentUser());
                $backup->setValue('reverted', 0);
                $backup->insert();

                $write = rex_sql::factory();
                $write->setTable(rex::getTable('article_slice'));
                $write->setWhere('id = :id', ['id' => $sliceId]);
                $write->setValue($column, $newJson);
                $write->update();
                ++$updated;
            } catch (rex_sql_exception $e) {
                $errors[] = sprintf('Slice %d: %s', $sliceId, $e->getMessage());
            }
        }

        return ['updated' => $updated, 'skipped' => $skipped, 'errors' => $errors, 'token' => $token];
    }

    /**
     * Wendet die Migration mehrerer Slots unter einem gemeinsamen Lauf-Token an.
     *
     * @param array<string, SlotConfig> $slots Slot-Id => Konfiguration
     * @param list<int>|null $sliceIds null = alle Slices des Moduls
     *
     * @return array{updated: int, skipped: int, errors: list<string>, token: string, per_slot: array<string, array{updated: int, skipped: int}>}
     */
    public function applySlots(int $moduleId, array $slots, ?array $sliceIds = null): array
    {
        $token = self::newToken();
        $ids = $sliceIds ?? $this->sliceIdsOfModule($moduleId);

        $total = ['updated' => 0, 'skipped' => 0, 'errors' => [], 'token' => $token, 'per_slot' => []];
        foreach ($slots as $slotId => $config) {
            $slotId = (string) $slotId;
            $result = $this->apply($ids, $slotId, $config['key_map'] ?? [], $config['options'] ?? [], $token, $moduleId);
            $total['updated'] += $result['updated'];
            $total['skipped'] += $result['skipped'];
            foreach ($result['errors'] as $error) {
                $total['errors'][] = 'Slot ' . $slotId . ': ' . $error;
            }
            $total['per_slot'][$slotId] = ['updated' => $result['updated'], 'skipped' => $result['skipped']];
        }

        return $total;
    }

    /**
     * Stellt die alten Werte eines Laufs wieder her.
     *
     * @return array{restored: int, errors: list<string>}
     */
    public function rollback(string $token): array
    {
        self::ensureTables();
        $token = trim($token);
        if ('' === $token) {
            return ['restored' => 0, 'errors' => ['Kein Lauf-Token angegeben.']];
        }

        $rows = rex_sql::factory()->getArray(
            'SELECT id, slice_id, slot_column, old_value FROM ' . rex::getTable(self::TABLE_BACKUP) . ' WHERE run_token = :t AND reverted = 0 ORDER BY id DESC',
            ['t' => $token],
        );
        if ([] === $rows) {
            return ['restored' => 0, 'errors' => [sprintf('Fuer Token %s gibt es keine offene Sicherung.', $token)]];
        }

        $restored = 0;
        $errors = [];
        foreach ($rows as $row) {
            $column = (string) $row['slot_column'];
            $oldValue = is_string($row['old_value']) ? $row['old_value'] : '';
            try {
                if (str_starts_with($column, YFormMBlockMigrator::COLUMN_PREFIX)) {
                    // YForm-Datensatz: "yform:tabelle.spalte", slice_id = Datensatz-Id.
                    [$table, $field] = array_pad(explode('.', substr($column, strlen(YFormMBlockMigrator::COLUMN_PREFIX)), 2), 2, '');
                    if (!YFormMBlockMigrator::validName($table) || !YFormMBlockMigrator::validName($field)) {
                        $errors[] = sprintf('Sicherung %d: ungueltige Spalte.', (int) $row['id']);
                        continue;
                    }
                    rex_sql::factory()->setQuery('UPDATE `' . $table . '` SET `' . $field . '` = ? WHERE id = ?', [$oldValue, (int) $row['slice_id']]);
                } else {
                    if (!preg_match('/^value\d+$/', $column)) {
                        $errors[] = sprintf('Sicherung %d: ungueltige Spalte.', (int) $row['id']);
                        continue;
                    }
                    $write = rex_sql::factory();
                    $write->setTable(rex::getTable('article_slice'));
                    $write->setWhere('id = :id', ['id' => (int) $row['slice_id']]);
                    $write->setValue($column, $oldValue);
                    $write->update();
                }

                rex_sql::factory()->setQuery(
                    'UPDATE ' . rex::getTable(self::TABLE_BACKUP) . ' SET reverted = 1, revertedate = :dt WHERE id = :id',
                    ['dt' => date('Y-m-d H:i:s'), 'id' => (int) $row['id']],
                );
                ++$restored;
            } catch (rex_sql_exception $e) {
                $errors[] = sprintf('Slice %d: %s', (int) $row['slice_id'], $e->getMessage());
            }
        }

        return ['restored' => $restored, 'errors' => $errors];
    }

    /**
     * Letzte Migrationslaeufe (fuer Rollback-Auswahl).
     *
     * @return list<array{token: string, module_id: int, module_name: string, slices: int, columns: string, createdate: string, createuser: string, open: int}>
     */
    public function getRuns(int $limit = 20): array
    {
        self::ensureTables();
        $rows = rex_sql::factory()->getArray(
            'SELECT b.run_token, b.module_id, COALESCE(m.name, \'\') AS module_name,
                    COUNT(DISTINCT b.slice_id) AS slices,
                    GROUP_CONCAT(DISTINCT b.slot_column ORDER BY b.slot_column SEPARATOR \', \') AS columns,
                    MIN(b.createdate) AS createdate, MIN(b.createuser) AS createuser,
                    SUM(CASE WHEN b.reverted = 0 THEN 1 ELSE 0 END) AS open
             FROM ' . rex::getTable(self::TABLE_BACKUP) . ' b
             LEFT JOIN ' . rex::getTable('module') . ' m ON m.id = b.module_id
             GROUP BY b.run_token, b.module_id, m.name
             ORDER BY MIN(b.id) DESC
             LIMIT ' . max(1, $limit),
        );

        $runs = [];
        foreach ($rows as $row) {
            $runs[] = [
                'token' => (string) $row['run_token'],
                'module_id' => (int) $row['module_id'],
                'module_name' => (string) $row['module_name'],
                'slices' => (int) $row['slices'],
                'columns' => (string) $row['columns'],
                'createdate' => (string) $row['createdate'],
                'createuser' => (string) $row['createuser'],
                'open' => (int) $row['open'],
            ];
        }

        return $runs;
    }

    /**
     * Haengt Slices auf ein anderes Modul um und protokolliert den alten Stand.
     *
     * @param list<int> $sliceIds
     *
     * @return array{moved: int, token: string, errors: list<string>}
     */
    public function reassign(array $sliceIds, int $targetModuleId): array
    {
        $ids = [];
        foreach ($sliceIds as $id) {
            if ((int) $id > 0) {
                $ids[] = (int) $id;
            }
        }
        if ([] === $ids) {
            return ['moved' => 0, 'token' => '', 'errors' => ['Keine Slices ausgewaehlt.']];
        }
        if ($targetModuleId <= 0) {
            return ['moved' => 0, 'token' => '', 'errors' => ['Kein Zielmodul angegeben.']];
        }

        self::ensureTables();
        $token = self::newToken(12);
        $placeholders = implode(',', array_fill(0, count($ids), '?'));

        $oldRows = rex_sql::factory()->getArray(
            'SELECT id, module_id FROM ' . rex::getTable('article_slice') . ' WHERE id IN (' . $placeholders . ')',
            $ids,
        );

        foreach ($oldRows as $oldRow) {
            $ins = rex_sql::factory();
            $ins->setTable(rex::getTable(self::TABLE_REASSIGN));
            $ins->setValue('reassign_token', $token);
            $ins->setValue('slice_id', (int) $oldRow['id']);
            $ins->setValue('old_module_id', (int) $oldRow['module_id']);
            $ins->setValue('new_module_id', $targetModuleId);
            $ins->setValue('createdate', date('Y-m-d H:i:s'));
            $ins->setValue('createuser', self::currentUser());
            $ins->setValue('reverted', 0);
            $ins->insert();
        }

        rex_sql::factory()->setQuery(
            'UPDATE ' . rex::getTable('article_slice') . ' SET module_id = ? WHERE id IN (' . $placeholders . ')',
            array_merge([$targetModuleId], $ids),
        );

        return ['moved' => count($oldRows), 'token' => $token, 'errors' => []];
    }

    /**
     * Macht ein Umhaengen rueckgaengig. Ohne Token: das letzte offene.
     *
     * @return array{restored: int, token: string, errors: list<string>}
     */
    public function revertReassign(string $token = ''): array
    {
        self::ensureTables();
        $token = trim($token);
        if ('' === $token) {
            $latest = rex_sql::factory()->getArray(
                'SELECT reassign_token FROM ' . rex::getTable(self::TABLE_REASSIGN) . ' WHERE reverted = 0 ORDER BY id DESC LIMIT 1',
            );
            if ([] !== $latest) {
                $token = (string) $latest[0]['reassign_token'];
            }
        }
        if ('' === $token) {
            return ['restored' => 0, 'token' => '', 'errors' => ['Keine rueckgaengig machbare Umhaengung gefunden.']];
        }

        $rows = rex_sql::factory()->getArray(
            'SELECT id, slice_id, old_module_id FROM ' . rex::getTable(self::TABLE_REASSIGN) . ' WHERE reassign_token = :t AND reverted = 0 ORDER BY id ASC',
            ['t' => $token],
        );
        if ([] === $rows) {
            return ['restored' => 0, 'token' => $token, 'errors' => [sprintf('Fuer Token %s gibt es keine offene Umhaengung mehr.', $token)]];
        }

        $count = 0;
        foreach ($rows as $row) {
            rex_sql::factory()->setQuery(
                'UPDATE ' . rex::getTable('article_slice') . ' SET module_id = :m WHERE id = :id',
                ['m' => (int) $row['old_module_id'], 'id' => (int) $row['slice_id']],
            );
            ++$count;
        }
        rex_sql::factory()->setQuery(
            'UPDATE ' . rex::getTable(self::TABLE_REASSIGN) . ' SET reverted = 1, revertedate = :dt WHERE reassign_token = :t AND reverted = 0',
            ['dt' => date('Y-m-d H:i:s'), 't' => $token],
        );

        return ['restored' => $count, 'token' => $token, 'errors' => []];
    }

    /**
     * Letzte Umhaengungen (fuer die Rueckgaengig-Auswahl).
     *
     * @return list<array{token: string, slices: int, old_module_id: int, new_module_id: int, createdate: string, open: int}>
     */
    public function getReassignRuns(int $limit = 10): array
    {
        self::ensureTables();
        $rows = rex_sql::factory()->getArray(
            'SELECT reassign_token, COUNT(*) AS slices, MIN(old_module_id) AS old_module_id, MIN(new_module_id) AS new_module_id,
                    MIN(createdate) AS createdate, SUM(CASE WHEN reverted = 0 THEN 1 ELSE 0 END) AS open
             FROM ' . rex::getTable(self::TABLE_REASSIGN) . '
             GROUP BY reassign_token ORDER BY MIN(id) DESC LIMIT ' . max(1, $limit),
        );
        $runs = [];
        foreach ($rows as $row) {
            $runs[] = [
                'token' => (string) $row['reassign_token'],
                'slices' => (int) $row['slices'],
                'old_module_id' => (int) $row['old_module_id'],
                'new_module_id' => (int) $row['new_module_id'],
                'createdate' => (string) $row['createdate'],
                'open' => (int) $row['open'],
            ];
        }

        return $runs;
    }

    /**
     * Legt eine konvertierte Kopie eines Moduls an (Name und Key mit Praefix `mfr_`).
     *
     * @return array{id: int, key: string, name: string}|null null, wenn das Quellmodul fehlt
     */
    public function createConvertedModule(int $sourceModuleId, string $input, string $output): ?array
    {
        $sourceSql = rex_sql::factory();
        $sourceSql->setQuery('SELECT * FROM ' . rex::getTable('module') . ' WHERE id = :id LIMIT 1', ['id' => $sourceModuleId]);
        if (1 !== $sourceSql->getRows()) {
            return null;
        }
        /** @var array<string, mixed> $source */
        $source = $sourceSql->getArray()[0];

        $timestamp = date('Ymd_His');
        $suffix = substr(md5(microtime(true) . '-' . $sourceModuleId), 0, 6);
        $newKey = 'mfr_' . $timestamp . '_' . $suffix;
        $newName = 'mfr_' . $timestamp . ' ' . (string) $source['name'];

        $insert = rex_sql::factory();
        $insert->setTable(rex::getTable('module'));
        foreach ($source as $column => $value) {
            if ('id' === $column) {
                continue;
            }
            $insert->setValue($column, $value);
        }
        $insert->setValue('name', $newName);
        if (array_key_exists('key', $source)) {
            $insert->setValue('key', $newKey);
        }
        if ('' !== trim($input)) {
            $insert->setValue('input', $input);
        }
        if ('' !== trim($output)) {
            $insert->setValue('output', $output);
        }
        $insert->setValue('createdate', date('Y-m-d H:i:s'));
        $insert->setValue('updatedate', date('Y-m-d H:i:s'));
        $insert->setValue('createuser', self::currentUser());
        $insert->setValue('updateuser', self::currentUser());
        $insert->insert();

        return ['id' => (int) $insert->getLastId(), 'key' => array_key_exists('key', $source) ? $newKey : '', 'name' => $newName];
    }

    /**
     * @return list<int>
     */
    public function sliceIdsOfModule(int $moduleId): array
    {
        $ids = [];
        foreach (rex_sql::factory()->getArray('SELECT id FROM ' . rex::getTable('article_slice') . ' WHERE module_id = :m ORDER BY id', ['m' => $moduleId]) as $row) {
            $ids[] = (int) $row['id'];
        }

        return $ids;
    }

    public static function newToken(int $length = 16): string
    {
        return substr(bin2hex(random_bytes(16)), 0, max(8, min(32, $length)));
    }

    private static function currentUser(): string
    {
        $user = rex::getUser();

        return null !== $user ? $user->getLogin() : (PHP_SAPI === 'cli' ? 'console' : 'system');
    }

    /**
     * Normalisiert JSON fuer einen stabilen Vorher/Nachher-Vergleich.
     */
    private function normalizeJson(string $raw): string
    {
        $decoded = json_decode(trim($raw), true);
        if (null === $decoded) {
            return trim($raw);
        }

        return (string) json_encode($decoded, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    /**
     * Prueft, ob ein Slot ueberhaupt MBlock-Daten enthaelt (fuer das Inventar).
     *
     * @return array{slices: int, with_markers: int, empty: int}
     */
    public function probeSlot(int $moduleId, string $slotId, int $limit = 200): array
    {
        $column = self::slotColumn($slotId);
        if (null === $column) {
            return ['slices' => 0, 'with_markers' => 0, 'empty' => 0];
        }
        $rows = rex_sql::factory()->getArray(
            'SELECT ' . $column . ' AS v FROM ' . rex::getTable('article_slice') . ' WHERE module_id = :m ORDER BY id LIMIT ' . max(1, $limit),
            ['m' => $moduleId],
        );
        $withMarkers = 0;
        $empty = 0;
        foreach ($rows as $row) {
            $v = is_string($row['v']) ? $row['v'] : '';
            if ('' === trim($v)) {
                ++$empty;
                continue;
            }
            if (str_contains($v, 'checkbox_block_hold') || str_contains($v, 'mblock_offline') || in_array(substr(ltrim($v), 0, 5), ['{"GBS'], true)) {
                ++$withMarkers;
            }
        }

        return ['slices' => count($rows), 'with_markers' => $withMarkers, 'empty' => $empty];
    }
}
