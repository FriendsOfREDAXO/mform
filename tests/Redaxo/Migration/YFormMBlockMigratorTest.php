<?php

declare(strict_types=1);

namespace FriendsOfRedaxo\MForm\Tests\Redaxo\Migration;

use FriendsOfRedaxo\MForm\Migration\MBlockToRepeaterMigrator;
use FriendsOfRedaxo\MForm\Migration\YFormMBlockMigrator;
use PHPUnit\Framework\TestCase;
use rex;
use rex_sql;

/**
 * M7: legt eine temporaere YForm-Tabelle mit einem mblock-Feld an, migriert die
 * Werte, prueft Rollback und Typumstellung. Braucht REDAXO mit YForm.
 */
final class YFormMBlockMigratorTest extends TestCase
{
    private const TABLE = 'rex_phpunit_mblock';

    private YFormMBlockMigrator $yform;

    protected function setUp(): void
    {
        if (!MFORM_TESTS_REDAXO) {
            self::markTestSkipped('MFORM_REDAXO_PATH / MFORM_REDAXO_BOOT nicht gesetzt.');
        }
        if (!YFormMBlockMigrator::available()) {
            self::markTestSkipped('YForm nicht verfuegbar.');
        }
        $this->yform = new YFormMBlockMigrator();
        $sql = rex_sql::factory();
        $sql->setQuery('CREATE TABLE IF NOT EXISTS `' . self::TABLE . '` (id int unsigned NOT NULL AUTO_INCREMENT, blocks text NULL, PRIMARY KEY (id)) ENGINE=InnoDB');
        $sql->setQuery('DELETE FROM ' . rex::getTable('yform_field') . ' WHERE table_name = ?', [self::TABLE]);
        $sql->setQuery(
            'INSERT INTO ' . rex::getTable('yform_field') . " (table_name, prio, type_id, type_name, name, label) VALUES (?, 1, 'value', 'mblock', 'blocks', 'Blocks')",
            [self::TABLE],
        );
        $items = json_encode([
            ['checkbox_block_hold' => 'hold_block', 'title' => 'A', 'REX_MEDIA_1' => 'a.jpg', 'mblock_offline' => '1'],
            ['checkbox_block_hold' => 'hold_block', 'title' => 'B'],
        ]);
        $sql->setQuery('INSERT INTO `' . self::TABLE . '` (blocks) VALUES (?), (?), (?)', [(string) $items, '', '[{"title":"already"}]']);
    }

    protected function tearDown(): void
    {
        $sql = rex_sql::factory();
        $sql->setQuery('DROP TABLE IF EXISTS `' . self::TABLE . '`');
        $sql->setQuery('DELETE FROM ' . rex::getTable('yform_field') . ' WHERE table_name = ?', [self::TABLE]);
        $sql->setQuery('DELETE FROM ' . rex::getTable(MBlockToRepeaterMigrator::TABLE_BACKUP) . ' WHERE slot_column = ?', [YFormMBlockMigrator::COLUMN_PREFIX . self::TABLE . '.blocks']);
        if (class_exists(\rex_yform_manager_table::class)) {
            \rex_yform_manager_table::deleteCache();
        }
    }

    public function testCandidatesDryRunApplyRollbackAndSwitch(): void
    {
        $candidates = array_values(array_filter($this->yform->findCandidates(), static fn (array $c): bool => self::TABLE === $c['table']));
        self::assertCount(1, $candidates);
        self::assertSame('blocks', $candidates[0]['field']);
        self::assertSame('mblock', $candidates[0]['type_name']);
        self::assertSame(3, $candidates[0]['rows']);
        self::assertSame(1, $candidates[0]['with_markers']);

        $dry = $this->yform->dryRun(self::TABLE, 'blocks', ['REX_MEDIA_1' => 'media']);
        self::assertSame(3, $dry['total']);
        self::assertSame(1, $dry['changed']);
        self::assertTrue($dry['rows'][1]['skipped']);
        self::assertFalse($dry['rows'][2]['changed'], 'bereits im Repeater-Format');

        $before = (string) rex_sql::factory()->getArray('SELECT blocks FROM `' . self::TABLE . '` WHERE id = 1')[0]['blocks'];
        $apply = $this->yform->apply(self::TABLE, 'blocks', ['REX_MEDIA_1' => 'media']);
        self::assertSame(1, $apply['updated']);
        self::assertSame(2, $apply['skipped']);
        self::assertSame([], $apply['errors']);

        $migrated = json_decode((string) rex_sql::factory()->getArray('SELECT blocks FROM `' . self::TABLE . '` WHERE id = 1')[0]['blocks'], true);
        self::assertEquals(['title' => 'A', 'media' => 'a.jpg', '__disabled' => true], $migrated[0]);
        self::assertSame(['title' => 'B'], $migrated[1]);

        $rollback = (new MBlockToRepeaterMigrator())->rollback($apply['token']);
        self::assertSame(1, $rollback['restored']);
        self::assertSame($before, (string) rex_sql::factory()->getArray('SELECT blocks FROM `' . self::TABLE . '` WHERE id = 1')[0]['blocks']);

        $switch = $this->yform->switchFieldToTextarea(self::TABLE, 'blocks');
        self::assertTrue($switch['changed']);
        $type = (string) rex_sql::factory()->getArray('SELECT type_name FROM ' . rex::getTable('yform_field') . ' WHERE table_name = ? AND name = ?', [self::TABLE, 'blocks'])[0]['type_name'];
        self::assertSame('textarea', $type);
        self::assertFalse($this->yform->switchFieldToTextarea(self::TABLE, 'blocks')['changed'], 'zweiter Aufruf aendert nichts');
    }

    public function testRejectsInvalidNames(): void
    {
        self::assertSame(0, $this->yform->dryRun('rex_user; DROP', 'x')['total']);
        self::assertSame(0, $this->yform->dryRun(self::TABLE, 'nope')['total']);
        self::assertNotSame([], $this->yform->apply('bad name', 'blocks')['errors']);
    }
}
