<?php

declare(strict_types=1);

namespace FriendsOfRedaxo\MForm\Tests\Redaxo\Repeater;

use FriendsOfRedaxo\MForm;
use FriendsOfRedaxo\MForm\FlexRepeater\MFormFlexRepeaterRenderer;
use PHPUnit\Framework\TestCase;

/**
 * Option item_title landet als data-mfr-item-title am Container (Haupt- und verschachtelter Repeater).
 */
final class ItemTitleTest extends TestCase
{
    protected function setUp(): void
    {
        if (!MFORM_TESTS_REDAXO) {
            self::markTestSkipped('MFORM_REDAXO_PATH / MFORM_REDAXO_BOOT nicht gesetzt.');
        }
    }

    public function testItemTitleIsPassedToBothLevels(): void
    {
        $inner = MForm::factory()
            ->addTextField('title', ['label' => 'Titel'])
            ->addRepeaterElement('tags', MForm::factory()->addTextField('name'), true, true, ['item_title' => 'Tag {n}: {name}']);
        $html = MForm::factory()->addRepeaterElement(1, $inner, true, true, ['item_title' => 'Abschnitt {n}: {title} "x"'])->show();

        self::assertStringContainsString('data-mfr-item-title="Abschnitt {n}: {title} &quot;x&quot;"', $html);
        self::assertStringContainsString('data-mfr-item-title="Tag {n}: {name}"', $html);
        self::assertStringNotContainsString('item_title="', $html, 'Option darf nicht als Attribut durchrutschen');

        $template = MFormFlexRepeaterRenderer::renderTemplate($inner, 1);
        self::assertStringContainsString('data-mfr-item-title="Tag {n}: {name}"', $template);
    }
}
