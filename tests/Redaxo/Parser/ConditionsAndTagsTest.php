<?php

declare(strict_types=1);

namespace FriendsOfRedaxo\MForm\Tests\Redaxo\Parser;

use FriendsOfRedaxo\MForm;
use FriendsOfRedaxo\MForm\FlexRepeater\MFormFlexRepeaterRenderer;
use FriendsOfRedaxo\MForm\Template\MFormTagsWidget;
use PHPUnit\Framework\TestCase;

/**
 * Mehrere Sichtbarkeits-Bedingungen (#417) und das Tags-Feld (#412) in beiden Renderpfaden.
 */
final class ConditionsAndTagsTest extends TestCase
{
    protected function setUp(): void
    {
        if (!MFORM_TESTS_REDAXO) {
            self::markTestSkipped('MFORM_REDAXO_PATH / MFORM_REDAXO_BOOT nicht gesetzt.');
        }
    }

    private static function form(): MForm
    {
        return MForm::factory()
            ->addSelectField('1.0.type', ['text' => 'Text', 'image' => 'Bild'], ['label' => 'Typ'])
            ->addTextField('1.0.headline', ['label' => 'Headline'])
                ->setVisibleIf('1.0.type', '=', 'text')
                ->addVisibleIf('1.0.tags', '!empty')
                ->setVisibleIfLogic('any')
            ->addTextField('1.0.hidden', ['label' => 'Versteckt'])
                ->setHiddenIf([['1.0.type', '=', 'image'], ['1.0.tags', 'contains', 'x']])
            ->addTagsField('1.0.tags', ['News', 'Blog'], ['label' => 'Tags', 'max' => 3, 'allow_new' => false], 'News,Extra')
            ->addConditionalFieldsetArea([['1.0.type', '=', 'image'], ['1.0.tags', '!empty']], 'any', '', 'Bild-Optionen', MForm::factory()
                ->addTextField('1.0.alt', ['label' => 'Alt']));
    }

    public function testConditionsAreRenderedInBothPaths(): void
    {
        foreach (['parser' => self::form()->show(), 'flex' => MFormFlexRepeaterRenderer::renderTemplate(self::form(), 1)] as $path => $html) {
            self::assertStringContainsString('data-mform-condition-logic="any"', $html, $path);
            self::assertSame(3, substr_count($html, '&quot;field&quot;:&quot;1.0.tags&quot;'), $path . ': Bedingungen auf 1.0.tags in allen drei Zielen');
            self::assertStringNotContainsString('data-mform-condition="[{"', $html, $path . ': JSON muss escaped sein');
            self::assertStringContainsString('&quot;op&quot;:&quot;!empty&quot;', $html, $path);
            self::assertStringContainsString('&quot;action&quot;:&quot;hide&quot;', $html, $path);
            self::assertStringContainsString('&quot;op&quot;:&quot;contains&quot;', $html, $path);
            self::assertStringContainsString('Bild-Optionen', $html, $path);
        }
    }

    public function testTagsWidgetInBothPaths(): void
    {
        $parser = self::form()->show();
        self::assertStringContainsString('class="mform-tags" data-tags-id="mform-tags--1--0--tags-" data-allow-new="0" data-max="3"', $parser);
        // Ohne Slice-Kontext gibt es keinen "add"-Modus, der Default greift daher erst im Modul
        self::assertStringContainsString('name="REX_INPUT_VALUE[1][0][tags]" value="" class="mform-tags-value"', $parser);
        self::assertStringContainsString('<option value="Blog"></option>', $parser);

        $widget = MFormTagsWidget::render('t1', ' name="x"', ' News, Extra,,News ', ['News'], ['max' => 2]);
        self::assertStringContainsString('value="News,Extra"', $widget);
        self::assertStringContainsString('<span class="mform-tags-tag" role="listitem">Extra<button type="button" class="mform-tags-remove" data-tag="Extra"', $widget);
        self::assertStringContainsString('data-allow-new="1" data-max="2"', $widget);

        $flex = MFormFlexRepeaterRenderer::renderTemplate(self::form(), 1);
        self::assertStringContainsString('data-mfr-field="1_0_tags" value="" class="mform-tags-value"', $flex);
        self::assertStringNotContainsString('form-group-class=', $flex, 'Steuerattribute duerfen nicht am Element landen');
        self::assertStringContainsString('data-allow-new="0" data-max="3"', $flex);
        self::assertStringContainsString('<option value="News"></option>', $flex);
    }
}
