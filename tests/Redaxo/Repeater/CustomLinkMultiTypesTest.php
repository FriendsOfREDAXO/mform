<?php

declare(strict_types=1);

namespace FriendsOfRedaxo\MForm\Tests\Redaxo\Repeater;

use FriendsOfRedaxo\MForm;
use PHPUnit\Framework\TestCase;
use rex_var_custom_link_multi;

use function preg_match_all;

/**
 * Abgeschaltete Link-Typen von addCustomLinkMultipleField() bleiben im Repeater abgeschaltet (#459).
 */
final class CustomLinkMultiTypesTest extends TestCase
{
    protected function setUp(): void
    {
        if (!MFORM_TESTS_REDAXO) {
            self::markTestSkipped('MFORM_REDAXO_PATH / MFORM_REDAXO_BOOT nicht gesetzt.');
        }
    }

    public function testDisabledExternStaysHiddenInRepeater(): void
    {
        $html = MForm::factory()->addRepeaterElement(1, self::form(), true, true, ['label' => 'Abschnitte'])->show();

        self::assertExternHidden($html);
    }

    public function testDisabledExternStaysHiddenInClassicForm(): void
    {
        self::assertExternHidden(self::form()->show());
    }

    public function testExternAliasIsAcceptedByWidget(): void
    {
        self::assertExternHidden(rex_var_custom_link_multi::getWidget(1, 'x', '', ['extern' => 0]));
        self::assertExternHidden(rex_var_custom_link_multi::getWidget(1, 'x', '', ['external' => 0]));
    }

    private static function form(): MForm
    {
        return MForm::factory()->addCustomLinkMultipleField('team_links', [
            'label' => 'Team',
            'data-intern' => 'false',
            'data-extern' => 'false',
            'data-media' => 'false',
            'data-mailto' => 'false',
            'data-anchor' => 'false',
            'data-tel' => 'false',
        ]);
    }

    private static function assertExternHidden(string $html): void
    {
        $html = html_entity_decode($html, ENT_QUOTES | ENT_HTML5);
        $count = preg_match_all('/class="([^"]*\bexternal_link\b[^"]*)"/', $html, $matches);
        self::assertGreaterThan(0, $count, 'Kein Button für externe Links im Markup gefunden');
        foreach ($matches[1] as $class) {
            self::assertStringContainsString('hidden', $class);
        }
    }
}
