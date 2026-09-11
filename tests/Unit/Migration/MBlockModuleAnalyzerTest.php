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
