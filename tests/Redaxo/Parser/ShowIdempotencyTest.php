<?php

declare(strict_types=1);

namespace FriendsOfRedaxo\MForm\Tests\Redaxo\Parser;

use FriendsOfRedaxo\MForm;
use PHPUnit\Framework\TestCase;

/**
 * Regressionen aus 9.4.2 bis 9.5.1 (#443, #444): show() darf beliebig oft
 * aufgerufen werden, ohne dass Feldnamen schrumpfen, Id-Praefixe sich stapeln
 * oder Werte doppelt escaped werden. Braucht eine gebootete REDAXO-Instanz.
 */
final class ShowIdempotencyTest extends TestCase
{
    protected function setUp(): void
    {
        if (!MFORM_TESTS_REDAXO) {
            self::markTestSkipped('MFORM_REDAXO_PATH / MFORM_REDAXO_BOOT nicht gesetzt.');
        }
    }

    /**
     * Zufaellige Widget-Ids (REX_MEDIA_123456789, mform_link_a1b2c3) neutralisieren.
     */
    private static function normalize(string $html): string
    {
        $html = (string) preg_replace('/\d{6,}/', 'N', $html);

        return (string) preg_replace('/(?<![0-9a-z])[0-9a-f]{6}(?![0-9a-z])/i', 'H', $html);
    }

    private static function form(): MForm
    {
        return MForm::factory()
            ->addTextField('1.0.t', ['label' => 'T'], 'a & b')
            ->addTextAreaField('1.0.ta', ['label' => 'TA'])
            ->addSelectField('1.0.s', ['a' => 'A', 'b' => 'B'])
            ->addMediaField('1.0.m', ['label' => 'M'])
            ->addMedialistField('1.0.ml', ['label' => 'ML'])
            ->addLinkField('1.0.l', ['label' => 'L'])
            ->addLinklistField('1.0.ll', ['label' => 'LL'])
            ->addCustomLinkField('1.0.c', ['label' => 'C'])
            ->addMediaField(2, ['label' => 'Slot 2']);
    }

    public function testRepeatedShowIsStable(): void
    {
        $form = self::form();
        $first = self::normalize($form->show());
        $second = self::normalize($form->show());
        $third = self::normalize($form->show());

        self::assertSame($first, $second);
        self::assertSame($second, $third);
    }

    public function testMultiPartIdsKeepFullNames(): void
    {
        $form = self::form();
        $form->show();
        $form->show();
        $html = $form->show();

        foreach (['t', 'ta', 's', 'm', 'ml', 'l', 'll', 'c'] as $key) {
            self::assertStringContainsString('name="REX_INPUT_VALUE[1][0][' . $key . ']"', $html, 'Feld ' . $key);
        }
        self::assertStringContainsString('name="REX_INPUT_MEDIA[2]"', $html);
        self::assertStringNotContainsString('REX_INPUT_MEDIA[]', $html);
        self::assertStringNotContainsString('REX_INPUT_VALUE[]', $html);
    }

    public function testIdPrefixAndClassesAreAppliedOnce(): void
    {
        $form = self::form();
        $form->show();
        $form->show();
        $html = $form->show();

        self::assertStringNotContainsString('rvrv', $html);
        self::assertStringContainsString('id="rv1_1_0_t"', $html);
        self::assertSame(0, preg_match('/class="[^"]*form-control[^"]*form-control/', $html), 'Standardklasse darf nicht mehrfach anhaengen');
    }

    public function testValuesAreNotDoubleEscaped(): void
    {
        $form = self::form();
        $form->show();
        $html = $form->show();

        self::assertStringNotContainsString('&amp;amp;', $html);
        self::assertStringNotContainsString('&amp;quot;', $html);
    }
}
