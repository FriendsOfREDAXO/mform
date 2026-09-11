<?php

declare(strict_types=1);

namespace FriendsOfRedaxo\MForm\Tests\Redaxo\Repeater;

use FriendsOfRedaxo\MForm\Repeater\MFormRepeaterItem;
use PHPUnit\Framework\TestCase;
use rex;
use rex_media_cache;
use rex_sql;

/**
 * Aufloesung von Medien-, Artikel- und Datensatz-Werten gegen REDAXO.
 */
final class MFormRepeaterItemTest extends TestCase
{
    private const FILE = 'phpunit_item_media.jpg';

    protected function setUp(): void
    {
        if (!MFORM_TESTS_REDAXO) {
            self::markTestSkipped('MFORM_REDAXO_PATH / MFORM_REDAXO_BOOT nicht gesetzt.');
        }
        $sql = rex_sql::factory();
        $sql->setTable(rex::getTable('media'));
        $sql->setValue('category_id', 0);
        $sql->setValue('filename', self::FILE);
        $sql->setValue('originalname', self::FILE);
        $sql->setValue('filetype', 'image/jpeg');
        $sql->setValue('filesize', 1);
        $sql->setValue('width', 10);
        $sql->setValue('height', 10);
        $sql->setValue('title', 'PHPUnit');
        $sql->setValue('createdate', date('Y-m-d H:i:s'));
        $sql->setValue('updatedate', date('Y-m-d H:i:s'));
        $sql->setValue('createuser', 'phpunit');
        $sql->setValue('updateuser', 'phpunit');
        $sql->insert();
        rex_media_cache::delete(self::FILE);
    }

    protected function tearDown(): void
    {
        rex_sql::factory()->setQuery('DELETE FROM ' . rex::getTable('media') . ' WHERE filename = ?', [self::FILE]);
        rex_media_cache::delete(self::FILE);
    }

    public function testMediaResolution(): void
    {
        $item = new MFormRepeaterItem(['image' => self::FILE, 'gallery' => self::FILE . ',gibtsnicht.jpg', 'none' => '']);

        self::assertSame('PHPUnit', $item->media('image')?->getTitle());
        self::assertNull($item->media('none'));
        self::assertCount(1, $item->medialist('gallery'));
        self::assertStringEndsWith(self::FILE, $item->url('image'));
    }

    public function testArticleAndDatasetFallbacks(): void
    {
        $item = new MFormRepeaterItem(['link' => '999999999', 'rel' => 'rex-rex-phpunit-nope://1']);

        self::assertNull($item->article('link'));
        self::assertNull($item->dataset('rel'));
        self::assertSame('dataset', $item->linkType('rel'));
        self::assertSame('', $item->url('rel'));
    }
}
