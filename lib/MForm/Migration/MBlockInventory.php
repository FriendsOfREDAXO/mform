<?php

/**
 * @author Friends Of REDAXO
 * @license MIT
 */

namespace FriendsOfRedaxo\MForm\Migration;

use rex;
use rex_sql;

use function count;
use function is_string;

/**
 * Modul-Inventar: Welche Module nutzen MBlock, wie viele Slices haengen daran,
 * welche Slots und Feldtypen sind betroffen und wie riskant ist die Migration.
 *
 * @phpstan-import-type Analysis from MBlockModuleAnalyzer
 * @phpstan-type InventoryEntry array{
 *     id: int,
 *     name: string,
 *     key: string,
 *     slice_count: int,
 *     analysis: Analysis,
 *     field_types: array<string, int>,
 *     probes: array<string, array{slices: int, with_markers: int, empty: int}>,
 *     migrated_module_id: int|null
 * }
 */
final class MBlockInventory
{
    private MBlockModuleAnalyzer $analyzer;
    private MBlockToRepeaterMigrator $migrator;

    public function __construct(?MBlockModuleAnalyzer $analyzer = null, ?MBlockToRepeaterMigrator $migrator = null)
    {
        $this->analyzer = $analyzer ?? new MBlockModuleAnalyzer();
        $this->migrator = $migrator ?? new MBlockToRepeaterMigrator(new MBlockToRepeaterConverter($this->analyzer));
    }

    /**
     * Alle Module mit MBlock-Bezug im Code, sortiert nach Risiko (rot zuerst) und Name.
     *
     * @return list<InventoryEntry>
     */
    public function collect(bool $probeData = true): array
    {
        $sql = rex_sql::factory();
        $rows = $sql->getArray(
            'SELECT m.id, m.name, m.`key`, m.input, m.output,
                    (SELECT COUNT(*) FROM ' . rex::getTable('article_slice') . ' s WHERE s.module_id = m.id) AS slice_count
             FROM ' . rex::getTable('module') . ' m
             WHERE m.input LIKE :needle OR m.output LIKE :needle
             ORDER BY m.name ASC, m.id ASC',
            ['needle' => '%MBlock%'],
        );

        $entries = [];
        foreach ($rows as $row) {
            $input = is_string($row['input']) ? $row['input'] : '';
            $output = is_string($row['output']) ? $row['output'] : '';
            $analysis = $this->analyzer->analyze($input, $output);
            if (!$analysis['has_mblock']) {
                // Nur Erwaehnung im Kommentar/Output, kein echter Aufruf.
                continue;
            }

            $fieldTypes = [];
            foreach ($analysis['calls'] as $call) {
                foreach ($call['fields'] as $field) {
                    $fieldTypes[$field['type']] = ($fieldTypes[$field['type']] ?? 0) + 1;
                }
            }
            ksort($fieldTypes);

            $probes = [];
            if ($probeData) {
                foreach ($analysis['slots'] as $slot) {
                    $probes[$slot] = $this->migrator->probeSlot((int) $row['id'], $slot);
                }
            }

            $entries[] = [
                'id' => (int) $row['id'],
                'name' => is_string($row['name']) ? $row['name'] : '',
                'key' => is_string($row['key'] ?? null) ? (string) $row['key'] : '',
                'slice_count' => (int) $row['slice_count'],
                'analysis' => $analysis,
                'field_types' => $fieldTypes,
                'probes' => $probes,
                'migrated_module_id' => $this->findConvertedCopy((int) $row['id'], is_string($row['name']) ? $row['name'] : ''),
            ];
        }

        usort($entries, static function (array $a, array $b): int {
            $rank = ['red' => 0, 'yellow' => 1, 'green' => 2, 'none' => 3];

            return ($rank[$a['analysis']['risk']] <=> $rank[$b['analysis']['risk']]) ?: strcasecmp($a['name'], $b['name']);
        });

        return $entries;
    }

    /**
     * Ein Eintrag fuer ein einzelnes Modul (null, wenn es nicht existiert).
     *
     * @return InventoryEntry|null
     */
    public function entry(int $moduleId, bool $probeData = true): ?array
    {
        foreach ($this->collect($probeData) as $entry) {
            if ($entry['id'] === $moduleId) {
                return $entry;
            }
        }

        return null;
    }

    /**
     * @param list<InventoryEntry> $entries
     * @return array{total: int, red: int, yellow: int, green: int, slices: int}
     */
    public static function summary(array $entries): array
    {
        $summary = ['total' => count($entries), 'red' => 0, 'yellow' => 0, 'green' => 0, 'slices' => 0];
        foreach ($entries as $entry) {
            $risk = $entry['analysis']['risk'];
            if (isset($summary[$risk])) {
                ++$summary[$risk];
            }
            $summary['slices'] += $entry['slice_count'];
        }

        return $summary;
    }

    /**
     * Sucht eine bereits angelegte konvertierte Kopie (Name "mfr_<timestamp> <Originalname>").
     */
    private function findConvertedCopy(int $moduleId, string $name): ?int
    {
        if ('' === $name) {
            return null;
        }
        $rows = rex_sql::factory()->getArray(
            'SELECT id FROM ' . rex::getTable('module') . ' WHERE id <> :id AND name LIKE :pattern AND input NOT LIKE :mblock ORDER BY id DESC LIMIT 1',
            ['id' => $moduleId, 'pattern' => 'mfr\\_%' . $name, 'mblock' => '%MBlock::show%'],
        );

        return [] !== $rows ? (int) $rows[0]['id'] : null;
    }
}
