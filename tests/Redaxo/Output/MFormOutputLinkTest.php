<?php

declare(strict_types=1);

namespace FriendsOfRedaxo\MForm\Tests\Redaxo\Output;

use FriendsOfRedaxo\MForm\Output\MFormOutput;
use PHPUnit\Framework\TestCase;

/**
 * Link-Aufloesung der Single-Value-Helfer gegen REDAXO (rex_getUrl).
 */
final class MFormOutputLinkTest extends TestCase
{
    protected function setUp(): void
    {
        if (!MFORM_TESTS_REDAXO) {
            self::markTestSkipped('MFORM_REDAXO_PATH / MFORM_REDAXO_BOOT nicht gesetzt.');
        }
    }

    public function testCustomLinkFormatsResolveLikeArticleIds(): void
    {
        $expected = MFormOutput::linkUrl('999999999');

        self::assertSame($expected, MFormOutput::linkUrl('redaxo://999999999'));
        self::assertSame($expected, MFormOutput::linkUrl('rex-article://999999999'));
        self::assertSame(\rex_getUrl(999999999, 1), MFormOutput::linkUrl('redaxo://999999999-1'));
        self::assertSame('mailto:a@b.de', MFormOutput::linkUrl('mailto:a@b.de'));
        self::assertSame('', MFormOutput::linkUrl('  '));
        self::assertStringContainsString('href="' . rex_escape($expected) . '"', MFormOutput::link('redaxo://999999999', 'Mehr'));
    }
}
