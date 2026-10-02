<?php

declare(strict_types=1);

namespace FriendsOfRedaxo\MForm\Tests\Redaxo\Parser;

use FriendsOfRedaxo\MForm;
use PHPUnit\Framework\TestCase;

/**
 * Schluessel und Beschriftungen von Optionen sind Text: Der klassische Parser gibt sie
 * escaped aus (Select, Optionsgruppe, Datalist), wie es der Flex-Repeater schon tut.
 */
final class OptionEscapingTest extends TestCase
{
    private const PAYLOAD = '</option></select><img src=x onerror=alert(1)>';

    protected function setUp(): void
    {
        if (!MFORM_TESTS_REDAXO) {
            self::markTestSkipped('MFORM_REDAXO_PATH / MFORM_REDAXO_BOOT nicht gesetzt.');
        }
    }

    public function testSelectOptionLabelAndValueAreEscaped(): void
    {
        $html = MForm::factory()
            ->addSelectField('1.0.a', [1 => self::PAYLOAD, '"><b>' => 'Schluessel'], ['label' => 'Auswahl'])
            ->show();

        self::assertStringNotContainsString('<img', $html);
        self::assertStringNotContainsString('<b>', $html);
        self::assertStringContainsString('<option value="1">&lt;/option&gt;&lt;/select&gt;&lt;img src=x onerror=alert(1)&gt;</option>', $html);
        self::assertStringContainsString('<option value="&quot;&gt;&lt;b&gt;">Schluessel</option>', $html);
    }

    public function testOptgroupLabelIsEscaped(): void
    {
        $html = MForm::factory()
            ->addSelectField('1.0.a', ['"><img src=x onerror=alert(1)>' => [1 => 'Eins', 2 => 'Zwei <b>']], ['label' => 'Auswahl'])
            ->show();

        self::assertStringNotContainsString('<img', $html);
        self::assertStringNotContainsString('<b>', $html);
        self::assertStringContainsString('<optgroup label="&quot;&gt;&lt;img src=x onerror=alert(1)&gt;">', $html);
        self::assertStringContainsString('<option value="2">Zwei &lt;b&gt;</option>', $html);
    }

    public function testMultiSelectOptionLabelIsEscaped(): void
    {
        $html = MForm::factory()
            ->addMultiSelectField('1.0.a', [1 => self::PAYLOAD], ['label' => 'Auswahl'])
            ->show();

        self::assertStringNotContainsString('<img', $html);
        self::assertStringContainsString('&lt;img src=x onerror=alert(1)&gt;</option>', $html);
    }

    public function testExistingEntitiesAreNotDoubleEncoded(): void
    {
        // Einruecken per &nbsp; und Module, die ihre Beschriftungen schon selbst escapen
        $html = MForm::factory()
            ->addSelectField('1.0.a', [1 => '&nbsp;&nbsp;Unterkategorie', 2 => 'Tom &amp; Jerry &lt;b&gt;', 3 => 'Müller & Söhne'], ['label' => 'Auswahl'])
            ->show();

        self::assertStringContainsString('<option value="1">&nbsp;&nbsp;Unterkategorie</option>', $html);
        self::assertStringContainsString('<option value="2">Tom &amp; Jerry &lt;b&gt;</option>', $html);
        self::assertStringContainsString('<option value="3">Müller &amp; Söhne</option>', $html);
        self::assertStringNotContainsString('&amp;amp;', $html);
        self::assertStringNotContainsString('&amp;nbsp;', $html);
    }

    public function testOptionValueWithAmpersandIsEscaped(): void
    {
        $html = MForm::factory()
            ->addSelectField('1.0.a', ['a&b' => 'Erste', 'c' => 'Zweite'], ['label' => 'Auswahl'])
            ->show();

        self::assertStringContainsString('<option value="a&amp;b">Erste</option>', $html);
        self::assertStringContainsString('<option value="c">Zweite</option>', $html);
    }

    public function testDatalistOptionIsEscaped(): void
    {
        $html = MForm::factory()
            ->addTextField('1.0.a', ['label' => 'Text'])
            ->setOptions(['"><i>' => '</option></datalist><img src=x onerror=alert(1)>', 'Blog'])
            ->show();

        self::assertStringNotContainsString('<img', $html);
        self::assertStringNotContainsString('<i>', $html);
        self::assertStringContainsString('&lt;/option&gt;&lt;/datalist&gt;&lt;img src=x onerror=alert(1)&gt;</option>', $html);
        self::assertStringContainsString('>Blog</option>', $html);
    }
}
