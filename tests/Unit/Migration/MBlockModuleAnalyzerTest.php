<?php

declare(strict_types=1);

namespace FriendsOfRedaxo\MForm\Tests\Unit\Migration;

use FriendsOfRedaxo\MForm\Migration\MBlockModuleAnalyzer;
use PHPUnit\Framework\TestCase;

final class MBlockModuleAnalyzerTest extends TestCase
{
    private MBlockModuleAnalyzer $analyzer;

    protected function setUp(): void
    {
        $this->analyzer = new MBlockModuleAnalyzer();
    }

    private static function fixture(string $name): string
    {
        return (string) file_get_contents(__DIR__ . '/../../Fixtures/' . $name);
    }

    public function testDerivesKeyMapFromNumericWidgets(): void
    {
        $analysis = $this->analyzer->analyze(self::fixture('mblock_cards_input.php.txt'), self::fixture('mblock_cards_output.php.txt'));

        self::assertTrue($analysis['has_mblock']);
        self::assertSame(['1'], $analysis['slots']);
        self::assertSame(['1' => 'link', 'REX_MEDIA_1' => 'media', 'REX_MEDIA_2' => 'media_2'], $analysis['key_maps']['1']);
        self::assertSame('yellow', $analysis['risk']);
        self::assertFalse($analysis['nested']);
        self::assertFalse($analysis['html_mblock']);
        self::assertTrue($analysis['cke5']);
        self::assertSame(['1', '2'], $analysis['output_toarray_slots']);
    }

    public function testIgnoresCommentedCallsAndFields(): void
    {
        $analysis = $this->analyzer->analyze(self::fixture('mblock_cards_input.php.txt'));

        self::assertCount(1, $analysis['calls']);
        $call = $analysis['calls'][0];
        self::assertSame('$mform', $call['form_var']);
        self::assertSame(['max' => '100', 'copy_paste' => 'true'], $call['options']);
        self::assertSame(['smooth_scroll'], $call['unknown_options']);
        self::assertTrue($call['has_offline_field']);

        $names = array_map(static fn (array $f): string => $f['name'], $call['fields']);
        self::assertNotContains('old', $names, 'auskommentiertes Feld darf nicht zaehlen');
        self::assertContains('header', $names);
    }

    public function testMainFormWidgetsAreNotPartOfBlockForm(): void
    {
        $analysis = $this->analyzer->analyze(self::fixture('mblock_cards_input.php.txt'));
        $mediaFields = array_values(array_filter($analysis['calls'][0]['fields'], static fn (array $f): bool => 'media' === $f['type']));

        // addMediaField(1) im Hauptformular ($main) darf nicht als Block-Feld auftauchen: nur 1 und 2 aus $mform.
        self::assertCount(2, $mediaFields);
    }

    public function testResolvesSlotFromVariableAndLiteral(): void
    {
        $code = '<?php $id = 3; $f = MForm::factory()->addTextField("$id.0.a"); echo MBlock::show($id, $f->show()); echo MBlock::show(7, $g);';
        $analysis = $this->analyzer->analyze($code);

        self::assertSame(['3', '7'], $analysis['slots']);
        self::assertSame('unknown', $analysis['calls'][1]['form_kind']);
    }

    public function testDetectsHtmlForm(): void
    {
        $code = "<?php \$id = 1; \$form = <<<EOT\n<input name=\"REX_INPUT_VALUE[\$id][0][x]\">\nEOT;\necho MBlock::show(\$id, \$form);";
        $analysis = $this->analyzer->analyze($code);

        self::assertTrue($analysis['html_mblock']);
        self::assertSame('red', $analysis['risk']);
    }

    public function testDetectsNestedFieldNames(): void
    {
        $code = '<?php $id = 1; $f = MForm::factory()->addTextField("$id.0.items.0.title"); echo MBlock::show($id, $f->show());';
        $analysis = $this->analyzer->analyze($code);

        self::assertTrue($analysis['nested']);
        self::assertSame(['items'], $analysis['calls'][0]['nested_keys']);
    }

    public function testResolvesTargetCollisions(): void
    {
        $code = '<?php $id = 1; $f = MForm::factory()->addTextField("$id.0.media")->addMediaField(1)->addLinkField(1)->addCustomLinkField("$id.0.1"); echo MBlock::show($id, $f->show());';
        $analysis = $this->analyzer->analyze($code);
        $map = $analysis['key_maps']['1'];

        self::assertSame('media_2', $map['REX_MEDIA_1'], 'sprechendes Feld "media" existiert bereits');
        self::assertSame('link', $map['1']);
        self::assertSame('link_2', $map['REX_LINK_1']);
    }

    public function testCollectsListFields(): void
    {
        $code = '<?php $id = 1; $f = MForm::factory()->addMedialistField(1)->addLinklistField("$id.0.links")->addImagelistField("$id.0.gallery")->addTextField("$id.0.t"); echo MBlock::show($id, $f->show());';
        $analysis = $this->analyzer->analyze($code);

        self::assertEquals(['medialist' => 'media', 'links' => 'link', 'gallery' => 'media'], $analysis['list_fields']['1']);
    }

    /** Modul aus #466: zwei Medienfelder im Block-Formular. */
    private const ISSUE_466 = '<?php $id = 1; $mform = MForm::factory(); $mform->addFieldsetArea("Video"); $mform->addMediaField(1, ["label" => "Poster"]); $mform->addMediaField(2, ["label" => "Video", "types" => "mp4,mov,webm"]); echo MBlock::show($id, $mform->show(), []);';

    public function testApplyKeyMapsUsesEditedTargets(): void
    {
        $analysis = $this->analyzer->analyze(self::ISSUE_466);
        self::assertSame(['REX_MEDIA_1' => 'media', 'REX_MEDIA_2' => 'media_2'], $analysis['key_maps']['1']);

        $edited = $this->analyzer->applyKeyMaps($analysis, ['1' => ['REX_MEDIA_1' => 'poster', 'REX_MEDIA_2' => 'video']]);

        self::assertSame(['REX_MEDIA_1' => 'poster', 'REX_MEDIA_2' => 'video'], $edited['key_maps']['1']);
        self::assertSame(['REX_MEDIA_1' => 'poster', 'REX_MEDIA_2' => 'video'], $edited['calls'][0]['key_map']);
        $targets = array_column($edited['calls'][0]['fields'], 'target', 'legacy_key');
        self::assertSame('poster', $targets['REX_MEDIA_1']);
        self::assertSame('video', $targets['REX_MEDIA_2']);
        self::assertStringContainsString('REX_MEDIA_1 -> poster', implode("\n", $edited['risk_reasons']));
    }

    public function testApplyKeyMapsWildcardSlotForConsole(): void
    {
        $edited = $this->analyzer->applyKeyMaps($this->analyzer->analyze(self::ISSUE_466), ['*' => ['REX_MEDIA_2' => 'video']]);

        self::assertSame(['REX_MEDIA_1' => 'media', 'REX_MEDIA_2' => 'video'], $edited['key_maps']['1']);
    }

    public function testApplyKeyMapsRejectsInvalidAndDuplicateNames(): void
    {
        $analysis = $this->analyzer->analyze(self::ISSUE_466);

        $invalid = $this->analyzer->applyKeyMaps($analysis, ['1' => ['REX_MEDIA_1' => 'mein Bild', 'REX_MEDIA_2' => '2video']]);
        self::assertSame(['REX_MEDIA_1' => 'media', 'REX_MEDIA_2' => 'media_2'], $invalid['key_maps']['1']);
        self::assertCount(2, array_filter($invalid['warnings'], static fn (string $w): bool => str_contains($w, 'ungueltig')));

        $duplicate = $this->analyzer->applyKeyMaps($analysis, ['1' => ['REX_MEDIA_1' => 'clip', 'REX_MEDIA_2' => 'clip']]);
        self::assertSame(['REX_MEDIA_1' => 'media', 'REX_MEDIA_2' => 'media_2'], $duplicate['key_maps']['1']);
        self::assertNotEmpty(array_filter($duplicate['warnings'], static fn (string $w): bool => str_contains($w, 'doppelt')));

        // Tausch der Namen ist erlaubt.
        $swap = $this->analyzer->applyKeyMaps($analysis, ['1' => ['REX_MEDIA_1' => 'media_2', 'REX_MEDIA_2' => 'media']]);
        self::assertSame(['REX_MEDIA_1' => 'media_2', 'REX_MEDIA_2' => 'media'], $swap['key_maps']['1']);
    }

    public function testApplyKeyMapsResolvesFollowUpCollisions(): void
    {
        // REX_MEDIA_1/2 kollidieren auf "clip" und fallen auf media/media_2 zurueck;
        // REX_MEDIA_3 -> "media" kollidiert danach mit dem zurueckgesetzten REX_MEDIA_1.
        $code = '<?php $id = 1; $f = MForm::factory()->addMediaField(1)->addMediaField(2)->addMediaField(3); echo MBlock::show($id, $f->show());';
        $edited = $this->analyzer->applyKeyMaps($this->analyzer->analyze($code), ['1' => ['REX_MEDIA_1' => 'clip', 'REX_MEDIA_2' => 'clip', 'REX_MEDIA_3' => 'media']]);

        $targets = array_values($edited['key_maps']['1']);
        self::assertSame($targets, array_values(array_unique($targets)), 'Zielnamen muessen eindeutig bleiben');
        self::assertSame(['REX_MEDIA_1' => 'media', 'REX_MEDIA_2' => 'media_2', 'REX_MEDIA_3' => 'media_3'], $edited['key_maps']['1']);
    }

    public function testApplyKeyMapsRenamesListFields(): void
    {
        $code = '<?php $id = 1; $f = MForm::factory()->addMedialistField(1)->addTextField("$id.0.t"); echo MBlock::show($id, $f->show());';
        $edited = $this->analyzer->applyKeyMaps($this->analyzer->analyze($code), ['1' => ['REX_MEDIALIST_1' => 'downloads']]);

        self::assertEquals(['downloads' => 'media'], $edited['list_fields']['1']);
    }

    public function testApplyKeyMapsWithoutOverridesKeepsAnalysis(): void
    {
        $analysis = $this->analyzer->analyze(self::ISSUE_466);

        self::assertSame($analysis, $this->analyzer->applyKeyMaps($analysis, []));
    }

    public function testNoMBlock(): void
    {
        $analysis = $this->analyzer->analyze('<?php echo MForm::factory()->addTextField(1)->show();');

        self::assertFalse($analysis['has_mblock']);
        self::assertSame('none', $analysis['risk']);
        self::assertSame([], $analysis['calls']);
    }

    public function testBlankCommentsKeepsOffsets(): void
    {
        $code = "a\n// x\n/* y\nz */ b";
        $blank = MBlockModuleAnalyzer::blankComments($code);

        self::assertSame(strlen($code), strlen($blank));
        self::assertSame(substr_count($code, "\n"), substr_count($blank, "\n"));
        self::assertStringNotContainsString('x', $blank);
        self::assertStringEndsWith(' b', $blank);
    }

    public function testFormRegionBlanksForeignStatements(): void
    {
        $code = '<?php $f = MForm::factory()->addTextField("1.0.a"); $other = MForm::factory()->addMediaField(1); $f->addTextField("1.0.b"); echo MBlock::show(1, $f->show());';
        $offset = (int) strpos($code, 'MBlock::show');
        $region = MBlockModuleAnalyzer::formRegion($code, '$f', $offset);

        self::assertNotNull($region);
        self::assertStringContainsString('1.0.a', $region['code']);
        self::assertStringContainsString('1.0.b', $region['code']);
        self::assertStringNotContainsString('addMediaField', $region['code']);
        self::assertSame($offset - $region['offset'], strlen($region['code']));
    }
}
