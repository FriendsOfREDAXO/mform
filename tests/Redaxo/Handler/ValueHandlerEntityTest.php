<?php

declare(strict_types=1);

namespace FriendsOfRedaxo\MForm\Tests\Redaxo\Handler;

use FriendsOfRedaxo\MForm;
use FriendsOfRedaxo\MForm\Handler\MFormValueHandler;
use PHPUnit\Framework\TestCase;
use rex;
use rex_sql;

/**
 * Die Eingabemaske liest einen Slot-Wert so, wie er gespeichert ist. Entities in den
 * Feldwerten duerfen beim Oeffnen eines Slices nicht zu Markup werden, sonst steht
 * als Text getipptes &lt;h2&gt; als Ueberschrift im Editor und wird so gespeichert.
 */
final class ValueHandlerEntityTest extends TestCase
{
    private const TEXT = '<p>Tag als Text: &lt;h2&gt;Titel&lt;/h2&gt; und &lt;script&gt;alert(1)&lt;/script&gt; Rest, Tom &amp; Jerry</p>';
    private const LINK = '<p><a href="https://example.org" title="Sagt &quot;Hallo&quot;">Link</a></p>';

    private int $sliceId = 0;

    /** @var array<string, mixed> */
    private array $request = [];

    protected function setUp(): void
    {
        if (!MFORM_TESTS_REDAXO) {
            self::markTestSkipped('MFORM_REDAXO_PATH / MFORM_REDAXO_BOOT nicht gesetzt.');
        }

        $sql = rex_sql::factory();
        $sql->setTable(rex::getTable('article_slice'));
        $sql->setValue('article_id', 0);
        $sql->setValue('clang_id', 1);
        $sql->setValue('ctype_id', 1);
        $sql->setValue('module_id', 0);
        $sql->setValue('priority', 1);
        $sql->setValue('status', 1);
        $sql->setValue('value1', (string) json_encode([['text' => self::TEXT], ['text' => self::LINK]], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        $sql->setValue('value2', (string) json_encode(['1' => self::TEXT], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        $sql->setValue('value3', 'einfacher Text mit &lt;b&gt;');
        $sql->setValue('createdate', date('Y-m-d H:i:s'));
        $sql->setValue('updatedate', date('Y-m-d H:i:s'));
        $sql->setValue('createuser', 'phpunit');
        $sql->setValue('updateuser', 'phpunit');
        $sql->insert();
        $this->sliceId = (int) $sql->getLastId();

        $this->request = $_REQUEST;
        $_REQUEST['slice_id'] = $this->sliceId;
        $_REQUEST['function'] = 'edit';
    }

    protected function tearDown(): void
    {
        if (0 === $this->sliceId) {
            return;
        }
        $_REQUEST = $this->request;
        rex_sql::factory()->setQuery('DELETE FROM ' . rex::getTable('article_slice') . ' WHERE id = ?', [$this->sliceId]);
    }

    public function testStoredValuesAreLoadedUnchanged(): void
    {
        $result = MFormValueHandler::loadRexVars();

        self::assertSame([['text' => self::TEXT], ['text' => self::LINK]], $result['value'][1]);
        self::assertSame(['1' => self::TEXT], $result['value'][2]);
        self::assertSame('einfacher Text mit &lt;b&gt;', $result['value'][3]);
    }

    public function testRepeaterInputKeepsEntitiesAsText(): void
    {
        $html = MForm::factory()
            ->addFlexRepeaterElement(1, MForm::factory()->addTextAreaField('text', ['label' => 'Text']))
            ->show();

        // Der Wert steht einmal escaped im Hidden-Input: aus &lt; wird &amp;lt;, nie ein <.
        self::assertStringContainsString('&amp;lt;h2&amp;gt;Titel&amp;lt;/h2&amp;gt;', $html);
        self::assertStringContainsString('&amp;lt;script&amp;gt;alert(1)&amp;lt;/script&amp;gt; Rest', $html);
        self::assertStringNotContainsString('&lt;h2&gt;Titel', $html);
    }
}
