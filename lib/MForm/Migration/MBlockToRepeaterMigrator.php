<?php

namespace FriendsOfRedaxo\MForm\Migration;

use rex;
use rex_sql;
use rex_sql_exception;

use function count;
use function is_string;
use function sprintf;
use function trim;

/**
 * Batch-Datenmigration MBlock -> Repeater auf Slice-Ebene.
 *
 * Liest die Slices eines Moduls aus `rex_article_slice`, konvertiert den
 * MBlock-Wert einer Slot-Spalte (`valueN`) und schreibt das Ergebnis bei
 * Bedarf in dieselbe Spalte zurueck. Dry-Run und selektives Anwenden sind
 * voneinander getrennt.
 */
final class MBlockToRepeaterMigrator
{
    private MBlockToRepeaterConverter $converter;

    public function __construct(?MBlockToRepeaterConverter $converter = null)
    {
        $this->converter = $converter ?? new MBlockToRepeaterConverter();
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
     * Dry-Run: konvertiert alle Slices eines Moduls in den Speicher und
     * liefert eine Vorschau, ohne etwas zu schreiben.
     *
        * @param array<int|string, string> $legacyKeyMap
        *
     * @return array{
     *     column: string,
     *     rows: list<array{
     *         slice_id: int,
     *         article_id: int,
    *         article_name: string,
     *         clang_id: int,
     *         count: int,
     *         changed: bool,
     *         skipped: bool,
     *         warnings: list<string>,
     *         notes: list<string>
     *     }>,
     *     total: int,
     *     changed: int,
     *     warnings: int
     * }
     */
    public function dryRun(int $moduleId, string $slotId, array $legacyKeyMap = []): array
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
            $result = $this->converter->convertData($original, $slotId, $legacyKeyMap);

            $newJson = (string) $result['json'];
            $hasItems = $result['count'] > 0 && '' !== $newJson;
            $changed = $hasItems && $this->normalizeJson($original) !== $this->normalizeJson($newJson);
            $skipped = !$hasItems;

            if ($changed) {
                ++$changedCount;
            }
            if (count($result['warnings']) > 0) {
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
                'warnings' => $result['warnings'],
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
     * Wendet die Migration auf die ausgewaehlten Slices an.
     *
     * @param list<int> $sliceIds
        * @param array<int|string, string> $legacyKeyMap
     *
     * @return array{updated: int, skipped: int, errors: list<string>}
     */
    public function apply(array $sliceIds, string $slotId, array $legacyKeyMap = []): array
    {
        $column = self::slotColumn($slotId);
        if (null === $column || [] === $sliceIds) {
            return ['updated' => 0, 'skipped' => 0, 'errors' => ['' === (string) $column ? 'Ungueltige Slot-Id.' : 'Keine Slices ausgewaehlt.']];
        }

        $updated = 0;
        $skipped = 0;
        $errors = [];

        foreach ($sliceIds as $sliceId) {
            $read = rex_sql::factory();
            $read->setQuery(
                'SELECT ' . $column . ' AS slot_value FROM ' . rex::getTable('article_slice') . ' WHERE id = :id',
                ['id' => $sliceId],
            );
            if (0 === $read->getRows()) {
                $errors[] = sprintf('Slice %d nicht gefunden.', $sliceId);
                continue;
            }

            $original = is_string($read->getValue('slot_value')) ? (string) $read->getValue('slot_value') : '';
            $result = $this->converter->convertData($original, $slotId, $legacyKeyMap);
            $newJson = (string) $result['json'];

            if ($result['count'] < 1 || '' === $newJson) {
                ++$skipped;
                continue;
            }
            if ($this->normalizeJson($original) === $this->normalizeJson($newJson)) {
                ++$skipped;
                continue;
            }

            $write = rex_sql::factory();
            $write->setTable(rex::getTable('article_slice'));
            $write->setWhere('id = :id', ['id' => $sliceId]);
            $write->setValue($column, $newJson);
            try {
                $write->update();
                ++$updated;
            } catch (rex_sql_exception $e) {
                $errors[] = sprintf('Slice %d: %s', $sliceId, $e->getMessage());
            }
        }

        return ['updated' => $updated, 'skipped' => $skipped, 'errors' => $errors];
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
}
