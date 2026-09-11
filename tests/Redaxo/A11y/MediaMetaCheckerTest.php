<?php

declare(strict_types=1);

namespace FriendsOfRedaxo\MForm\Tests\Redaxo\A11y;

use FriendsOfRedaxo\MForm\A11y\MediaMetaChecker;
use PHPUnit\Framework\TestCase;
use rex;
use rex_addon;
use rex_media_cache;
use rex_sql;

/**
 * Prüfung gegen den Medienpool: legt temporaere Medien-Datensaetze an (ohne Datei)
 * und prueft ALT, dekorativ, unbekannte Felder und fehlende Dateien. Braucht eine
 * gebootete REDAXO-Instanz mit installiertem Metainfo-Addon.
 */
final class MediaMetaCheckerTest extends TestCase
{
    /** @var list<string> */
    private array $filenames = [];

    private bool $hasAlt = false;
    private bool $hasDecorative = false;

    protected function setUp(): void
    {
        if (!MFORM_TESTS_REDAXO) {
            self::markTestSkipped('MFORM_REDAXO_PATH / MFORM_REDAXO_BOOT nicht gesetzt.');
        }
        if (!rex_addon::get('metainfo')->isAvailable()) {
            self::markTestSkipped('Metainfo nicht verfuegbar.');
        }
        $columns = rex_sql::factory()->getArray('SHOW COLUMNS FROM ' . rex::getTable('media'));
        $names = array_map(static fn (array $c): string => (string) $c['Field'], $columns);
        $this->hasAlt = in_array('med_alt', $names, true);
        $this->hasDecorative = in_array('med_alt_decorative', $names, true);
        MediaMetaChecker::resetCache();
    }

    protected function tearDown(): void
    {
        foreach ($this->filenames as $filename) {
            rex_sql::factory()->setQuery('DELETE FROM ' . rex::getTable('media') . ' WHERE filename = ?', [$filename]);
            rex_media_cache::delete($filename);
        }
    }

    /**
     * @param array<string, mixed> $extra
     */
    private function insertMedia(string $filename, string $filetype, array $extra = []): void
    {
        $sql = rex_sql::factory();
        $sql->setTable(rex::getTable('media'));
        $sql->setValue('category_id', 0);
        $sql->setValue('filename', $filename);
        $sql->setValue('originalname', $filename);
        $sql->setValue('filetype', $filetype);
        $sql->setValue('filesize', 1);
        $sql->setValue('width', 'image/jpeg' === $filetype ? 10 : 0);
        $sql->setValue('height', 'image/jpeg' === $filetype ? 10 : 0);
        $sql->setValue('title', '');
        $sql->setValue('createdate', date('Y-m-d H:i:s'));
        $sql->setValue('updatedate', date('Y-m-d H:i:s'));
        $sql->setValue('createuser', 'phpunit');
        $sql->setValue('updateuser', 'phpunit');
        foreach ($extra as $column => $value) {
            $sql->setValue($column, $value);
        }
        $sql->insert();
        $this->filenames[] = $filename;
        rex_media_cache::delete($filename);
    }

    public function testMissingFile(): void
    {
        $result = (new MediaMetaChecker())->check('phpunit_does_not_exist.jpg', [['field' => 'med_alt', 'message' => '']]);

        self::assertFalse($result['exists']);
        self::assertSame('file_missing', $result['issues'][0]['code']);
    }

    public function testAltMissingAndPresent(): void
    {
        if (!$this->hasAlt) {
            self::markTestSkipped('med_alt nicht vorhanden.');
        }
        $this->insertMedia('phpunit_noalt.jpg', 'image/jpeg', ['med_alt' => '']);
        $this->insertMedia('phpunit_alt.jpg', 'image/jpeg', ['med_alt' => 'Beschreibung']);
        $rules = [['field' => 'med_alt', 'message' => 'Eigene Meldung']];
        $checker = new MediaMetaChecker();

        $missing = $checker->check('phpunit_noalt.jpg', $rules);
        self::assertTrue($missing['exists']);
        self::assertTrue($missing['is_image']);
        self::assertCount(1, $missing['issues']);
        self::assertSame('Eigene Meldung', $missing['issues'][0]['message']);
        self::assertStringContainsString('file_id=', $missing['edit_url']);
        self::assertStringNotContainsString('&amp;', $missing['edit_url']);

        $present = $checker->check('phpunit_alt.jpg', $rules);
        self::assertSame([], $present['issues']);
    }

    public function testDecorativeImageNeedsNoAlt(): void
    {
        if (!$this->hasAlt || !$this->hasDecorative) {
            self::markTestSkipped('med_alt_decorative nicht vorhanden.');
        }
        $this->insertMedia('phpunit_decorative.jpg', 'image/jpeg', ['med_alt' => '', 'med_alt_decorative' => 1]);

        $result = (new MediaMetaChecker())->check('phpunit_decorative.jpg', [['field' => 'med_alt', 'message' => '']]);
        self::assertSame([], $result['issues']);
    }

    public function testNonImageNeedsNoAlt(): void
    {
        if (!$this->hasAlt) {
            self::markTestSkipped('med_alt nicht vorhanden.');
        }
        $this->insertMedia('phpunit_doc.pdf', 'application/pdf', ['med_alt' => '']);

        $result = (new MediaMetaChecker())->check('phpunit_doc.pdf', [['field' => 'med_alt', 'message' => '']]);
        self::assertFalse($result['is_image']);
        self::assertSame([], $result['issues']);
    }

    public function testUnknownFieldIsReportedAsConfigError(): void
    {
        $this->insertMedia('phpunit_unknown.jpg', 'image/jpeg');

        $result = (new MediaMetaChecker())->check('phpunit_unknown.jpg', [['field' => 'med_phpunit_nope', 'message' => 'Modul-Meldung']]);
        self::assertCount(1, $result['issues']);
        self::assertSame('unknown_field', $result['issues'][0]['code']);
        self::assertStringNotContainsString('Modul-Meldung', $result['issues'][0]['message']);
    }

    public function testCheckManyDeduplicates(): void
    {
        $results = (new MediaMetaChecker())->checkMany(['a.jpg', 'a.jpg', 'b.jpg'], [['field' => 'med_alt', 'message' => '']]);
        self::assertCount(2, $results);
    }
}
