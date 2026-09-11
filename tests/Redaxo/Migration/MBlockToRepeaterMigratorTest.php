<?php

declare(strict_types=1);

namespace FriendsOfRedaxo\MForm\Tests\Redaxo\Migration;

use FriendsOfRedaxo\MForm\Migration\MBlockInventory;
use FriendsOfRedaxo\MForm\Migration\MBlockToRepeaterMigrator;
use PHPUnit\Framework\TestCase;
use rex;
use rex_sql;

/**
 * Datenmigration gegen die Datenbank: Dry-Run, Anwenden mit Backup, Rollback,
 * Umhaengen. Legt ein temporaeres Modul mit zwei Slices an und raeumt es
 * wieder weg. Braucht eine gebootete REDAXO-Instanz.
 */
final class MBlockToRepeaterMigratorTest extends TestCase
{
    private int $moduleId = 0;
    private int $targetModuleId = 0;

    /** @var list<int> */
    private array $sliceIds = [];

    private MBlockToRepeaterMigrator $migrator;

    protected function setUp(): void
    {
        if (!MFORM_TESTS_REDAXO) {
            self::markTestSkipped('MFORM_REDAXO_PATH / MFORM_REDAXO_BOOT nicht gesetzt.');
        }
        MBlockToRepeaterMigrator::ensureTables();
        $this->migrator = new MBlockToRepeaterMigrator();

        $input = (string) file_get_contents(__DIR__ . '/../../Fixtures/mblock_cards_input.php.txt');
        $this->moduleId = $this->insertModule('phpunit mblock source', $input);
        $this->targetModuleId = $this->insertModule('phpunit mblock target', '<?php echo MForm::factory()->addTextField(1)->show();');

        $items = [
            ['checkbox_block_hold' => 'hold_block', 'header' => 'A', 'REX_MEDIA_1' => 'a.jpg', 'REX_MEDIA_2' => '', '1' => '', 'mblock_offline' => '1'],
            ['checkbox_block_hold' => 'hold_block', 'header' => 'B', 'REX_MEDIA_1' => '', 'REX_MEDIA_2' => 'b.jpg', '1' => 'https://example.org', 'mblock_offline' => '0'],
        ];
        $this->sliceIds[] = $this->insertSlice($this->moduleId, (string) json_encode($items));
        $this->sliceIds[] = $this->insertSlice($this->moduleId, '');
    }

    protected function tearDown(): void
    {
        if (0 === $this->moduleId) {
            return;
        }
        $sql = rex_sql::factory();
        foreach ($this->sliceIds as $id) {
            $sql->setQuery('DELETE FROM ' . rex::getTable('article_slice') . ' WHERE id = ?', [$id]);
        }
        $sql->setQuery('DELETE FROM ' . rex::getTable('module') . ' WHERE id IN (?, ?)', [$this->moduleId, $this->targetModuleId]);
        $sql->setQuery('DELETE FROM ' . rex::getTable(MBlockToRepeaterMigrator::TABLE_BACKUP) . ' WHERE module_id = ?', [$this->moduleId]);
        $sql->setQuery('DELETE FROM ' . rex::getTable(MBlockToRepeaterMigrator::TABLE_REASSIGN) . ' WHERE old_module_id = ? OR new_module_id = ?', [$this->moduleId, $this->targetModuleId]);
    }

    private function insertModule(string $name, string $input): int
    {
        $sql = rex_sql::factory();
        $sql->setTable(rex::getTable('module'));
        $sql->setValue('name', $name);
        $sql->setValue('input', $input);
        $sql->setValue('output', '');
        $sql->setValue('createdate', date('Y-m-d H:i:s'));
        $sql->setValue('updatedate', date('Y-m-d H:i:s'));
        $sql->setValue('createuser', 'phpunit');
        $sql->setValue('updateuser', 'phpunit');
        $sql->insert();

        return (int) $sql->getLastId();
    }

    private function insertSlice(int $moduleId, string $value1): int
    {
        $sql = rex_sql::factory();
        $sql->setTable(rex::getTable('article_slice'));
        $sql->setValue('article_id', 0);
        $sql->setValue('clang_id', 1);
        $sql->setValue('ctype_id', 1);
        $sql->setValue('module_id', $moduleId);
        $sql->setValue('priority', 1);
        $sql->setValue('status', 1);
        $sql->setValue('value1', $value1);
        $sql->setValue('createdate', date('Y-m-d H:i:s'));
        $sql->setValue('updatedate', date('Y-m-d H:i:s'));
        $sql->setValue('createuser', 'phpunit');
        $sql->setValue('updateuser', 'phpunit');
        $sql->insert();

        return (int) $sql->getLastId();
    }

    private function value1(int $sliceId): string
    {
        return (string) rex_sql::factory()->getArray('SELECT value1 FROM ' . rex::getTable('article_slice') . ' WHERE id = ?', [$sliceId])[0]['value1'];
    }

    public function testInventoryListsTheModuleWithKeyMap(): void
    {
        $entry = (new MBlockInventory())->entry($this->moduleId);

        self::assertNotNull($entry);
        self::assertSame(2, $entry['slice_count']);
        self::assertSame('yellow', $entry['analysis']['risk']);
        self::assertSame(['1' => 'link', 'REX_MEDIA_1' => 'media', 'REX_MEDIA_2' => 'media_2'], $entry['analysis']['key_maps']['1']);
        self::assertSame(1, $entry['probes']['1']['with_markers']);
        self::assertSame(1, $entry['probes']['1']['empty']);
    }

    public function testDryRunApplyAndRollback(): void
    {
        $keyMap = ['1' => 'link', 'REX_MEDIA_1' => 'media', 'REX_MEDIA_2' => 'media_2'];
        $slots = ['1' => ['key_map' => $keyMap, 'options' => []]];

        $dry = $this->migrator->dryRunSlots($this->moduleId, $slots);
        self::assertSame('value1', $dry['1']['column']);
        self::assertSame(2, $dry['1']['total']);
        self::assertSame(1, $dry['1']['changed']);
        self::assertTrue($dry['1']['rows'][1]['skipped'], 'leerer Slot wird uebersprungen');

        $original = $this->value1($this->sliceIds[0]);
        $apply = $this->migrator->applySlots($this->moduleId, $slots);
        self::assertSame(1, $apply['updated']);
        self::assertSame(1, $apply['skipped']);
        self::assertSame([], $apply['errors']);
        self::assertNotSame('', $apply['token']);

        $migrated = json_decode($this->value1($this->sliceIds[0]), true);
        self::assertEquals(['header' => 'A', 'media' => 'a.jpg', 'media_2' => '', 'link' => '', '__disabled' => true], $migrated[0]);
        self::assertEquals(['header' => 'B', 'media' => '', 'media_2' => 'b.jpg', 'link' => 'https://example.org'], $migrated[1]);

        // Zweiter Lauf: nichts mehr zu tun.
        $again = $this->migrator->dryRunSlots($this->moduleId, $slots);
        self::assertSame(0, $again['1']['changed']);

        $runs = array_values(array_filter($this->migrator->getRuns(50), fn (array $r): bool => $r['token'] === $apply['token']));
        self::assertCount(1, $runs);
        self::assertSame(1, $runs[0]['open']);

        $rollback = $this->migrator->rollback($apply['token']);
        self::assertSame(1, $rollback['restored']);
        self::assertSame($original, $this->value1($this->sliceIds[0]));

        $rollbackAgain = $this->migrator->rollback($apply['token']);
        self::assertSame(0, $rollbackAgain['restored']);
    }

    public function testReassignAndRevert(): void
    {
        $result = $this->migrator->reassign($this->sliceIds, $this->targetModuleId);
        self::assertSame(2, $result['moved']);
        self::assertSame([], $result['errors']);

        $moduleIds = rex_sql::factory()->getArray('SELECT DISTINCT module_id FROM ' . rex::getTable('article_slice') . ' WHERE id IN (?, ?)', $this->sliceIds);
        self::assertSame([(string) $this->targetModuleId], array_map(static fn (array $r): string => (string) $r['module_id'], $moduleIds));

        $revert = $this->migrator->revertReassign($result['token']);
        self::assertSame(2, $revert['restored']);

        $moduleIds = rex_sql::factory()->getArray('SELECT DISTINCT module_id FROM ' . rex::getTable('article_slice') . ' WHERE id IN (?, ?)', $this->sliceIds);
        self::assertSame([(string) $this->moduleId], array_map(static fn (array $r): string => (string) $r['module_id'], $moduleIds));
    }

    public function testCreateConvertedModule(): void
    {
        $created = $this->migrator->createConvertedModule($this->moduleId, '<?php // input', '<?php // output');
        self::assertNotNull($created);
        try {
            self::assertStringStartsWith('mfr_', $created['name']);
            self::assertStringEndsWith(' phpunit mblock source', $created['name']);
            $row = rex_sql::factory()->getArray('SELECT input, output FROM ' . rex::getTable('module') . ' WHERE id = ?', [$created['id']])[0];
            self::assertSame('<?php // input', $row['input']);
            self::assertSame('<?php // output', $row['output']);
        } finally {
            rex_sql::factory()->setQuery('DELETE FROM ' . rex::getTable('module') . ' WHERE id = ?', [$created['id']]);
        }
    }
}
