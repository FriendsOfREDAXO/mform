<?php

declare(strict_types=1);

namespace FriendsOfRedaxo\MForm\Tests\Redaxo\Parser;

use FriendsOfRedaxo\MForm;
use FriendsOfRedaxo\MForm\FlexRepeater\MFormFlexRepeaterRenderer;
use PHPUnit\Framework\TestCase;

/**
 * Paritaet der Renderpfade (#437): dieselben Wrapper-Kombinationen muessen im
 * klassischen Parser und im Flex-Repeater-Template die gleichen Strukturmerkmale
 * ergeben. Dazu ein Golden-Snapshot des Parser-HTML fuer die Wrapper-Kombination
 * (Aktualisieren mit MFORM_UPDATE_SNAPSHOTS=1). Braucht eine gebootete REDAXO-Instanz.
 */
final class RenderParityTest extends TestCase
{
    protected function setUp(): void
    {
        if (!MFORM_TESTS_REDAXO) {
            self::markTestSkipped('MFORM_REDAXO_PATH / MFORM_REDAXO_BOOT nicht gesetzt.');
        }
    }

    private static function buildForm(): MForm
    {
        return MForm::factory()
            ->addTextField('1.0.title', ['label' => 'Titel'])
                ->setTooltipInfo('Tooltip-Test')
            ->addColumnElement(6, MForm::factory()->addTextField('1.0.a', ['label' => 'Spalte A']), [
                'data-group-column-row-class' => 'parity-column-row',
                'data-group-row-class' => 'parity-row',
            ])
            ->addColumnElement(6, MForm::factory()->addTextField('1.0.b', ['label' => 'Spalte B']))
            ->addModalElement('Modal', MForm::factory()->addTextField('1.0.m', ['label' => 'Im Modal']), 'btn-info', 'right', [
                'data-modal-row-class' => 'parity-modal-row',
                'data-group-row-class' => 'parity-modal-group-row',
            ])
            ->addTextField('1.0.full', ['label' => 'Volle Breite'])
                ->setFull()
            ->addTabElement('Tab Inhalt', MForm::factory()->addTextField('1.0.t1', ['label' => 'Tab Feld A']), true, false, [
                'tab-icon' => 'fa-file-text-o',
                'nav-class' => 'parity-tab-nav',
                'data-group-tab-style' => 'modern',
                'data-group-tab-layout' => 'vertical',
            ])
            ->addTabElement('Tab Meta', MForm::factory()->addTextField('1.0.t2', ['label' => 'Tab Feld B']), false, true, [
                'tab-icon' => 'fa-cog',
            ])
            ->addCollapseElement('Zugeklappt', MForm::factory()->addTextField('1.0.c', ['label' => 'Im Collapse']), false);
    }

    /**
     * @return array{parser: string, flex: string}
     */
    private static function render(): array
    {
        return [
            'parser' => self::buildForm()->show(),
            'flex' => MFormFlexRepeaterRenderer::renderTemplate(self::buildForm(), 1),
        ];
    }

    /**
     * @return list<string>
     */
    public static function needles(): array
    {
        return [
            'mform-info-tooltip',
            'fa-info-circle',
            'parity-column-row',
            'parity-row',
            'parity-modal-row',
            'parity-modal-group-row',
            'col-sm-12',
            'parity-tab-nav',
            'mform-tabs--vertical',
            'mform-tabs--modern',
            'pull-right',
            'rex-icon fa-file-text-o',
            'rex-icon fa-cog',
            'tab-pane',
            'Im Collapse',
        ];
    }

    public function testBothPathsContainTheSameStructuralMarkers(): void
    {
        $html = self::render();
        foreach (self::needles() as $needle) {
            self::assertStringContainsString($needle, $html['parser'], 'Parser: ' . $needle);
            self::assertStringContainsString($needle, $html['flex'], 'Flex-Repeater: ' . $needle);
        }
    }

    public function testTabMetaAttributesDoNotLeakIntoPanes(): void
    {
        $html = self::render();
        foreach ($html as $path => $out) {
            self::assertSame(2, preg_match_all('/<div[^>]*role="tabpanel"[^>]*>/', $out, $panes), $path . ': Tab-Panes');
            foreach ($panes[0] as $pane) {
                foreach (['tab-icon=', 'nav-class=', 'pull-right=', 'data-group-open-tab=', 'data-group-tab-layout=', 'data-group-tab-style='] as $leak) {
                    self::assertStringNotContainsString($leak, $pane, $path . ': ' . $leak);
                }
            }
        }
    }

    public function testSameTabCountAndActiveTab(): void
    {
        $html = self::render();
        foreach ($html as $path => $out) {
            self::assertSame(2, substr_count($out, 'role="tabpanel"'), $path . ': Tab-Panes');
            self::assertSame(2, preg_match_all('/<li[^>]*role="presentation"/', $out), $path . ': Tab-Nav-Eintraege');
            self::assertSame(1, preg_match_all('/<li[^>]*class="[^"]*\bactive\b[^"]*"[^>]*role="presentation"|<li[^>]*role="presentation"[^>]*class="[^"]*\bactive\b/', $out), $path . ': genau ein aktiver Tab');
        }
    }

    public function testModalButtonClassInBothPaths(): void
    {
        foreach (self::render() as $path => $out) {
            self::assertStringContainsString('class="btn btn-info"', $out, $path . ': Modal-Button-Klasse aus addModalElement()');
        }
    }

    public function testParserWrapperSnapshot(): void
    {
        $this->assertSnapshot('parser_wrappers.html', self::render()['parser']);
    }

    public function testFlexWrapperSnapshot(): void
    {
        $this->assertSnapshot('flex_wrappers.html', self::render()['flex']);
    }

    private function assertSnapshot(string $name, string $html): void
    {
        // uniqid()-Tokens (13 Hex), Zufallszahlen und 6-stellige Widget-Ids neutralisieren.
        $normalized = (string) preg_replace('/[0-9a-f]{13,}/i', 'U', $html);
        $normalized = (string) preg_replace('/\d{6,}/', 'N', $normalized);
        $normalized = (string) preg_replace('/(?<![0-9a-z])[0-9a-f]{6}(?![0-9a-z])/i', 'H', $normalized);
        $normalized = trim((string) preg_replace('/[ \t]+\n/', "\n", $normalized)) . "\n";

        $file = __DIR__ . '/../../__snapshots__/' . $name;
        if ('1' === getenv('MFORM_UPDATE_SNAPSHOTS') || !is_file($file)) {
            @mkdir(dirname($file), 0775, true);
            file_put_contents($file, $normalized);
            self::markTestIncomplete('Snapshot geschrieben: ' . $name);
        }

        self::assertSame((string) file_get_contents($file), $normalized, 'HTML weicht vom Snapshot ' . $name . ' ab. Absichtliche Aenderung? MFORM_UPDATE_SNAPSHOTS=1 setzt ihn neu.');
    }
}
