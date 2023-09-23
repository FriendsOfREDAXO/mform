<?php

use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
class rex_diff_detect_url_test extends TestCase
{
    public function testSettings()
    {
        $id = 999999;
        rex_sql::factory()->setQuery('delete from `' . rex::getTable('diff_detect_url') . '` where id=:id', [
            'id' => $id,
        ]);

        rex_sql::factory()->setQuery('insert into `' . rex::getTable('diff_detect_url') . '` set id=:id, name=:name, url=:url, type=:type, status=:status', [
            'id' => $id,
            'name' => 'REDAXO ' . random_int(1000, 9999),
            'url' => 'https://redaxo.org',
            'type' => 'html',
            'status' => 1,
        ]);

        $url = FriendsOfRedaxo\DiffDetect\Url::get($id);

        static::assertNotNull($url);

        rex_sql::factory()->setQuery('delete from `' . rex::getTable('diff_detect_url') . '` where id=:id', [
            'id' => $id,
        ]);

    }
}
