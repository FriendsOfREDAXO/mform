<?php

declare(strict_types=1);

namespace FriendsOfRedaxo\MForm\Tests\Unit\Lint;

use FriendsOfRedaxo\MForm\Lint\ModuleLinter;
use PHPUnit\Framework\TestCase;

final class ModuleLinterTest extends TestCase
{
    private ModuleLinter $linter;

    protected function setUp(): void
    {
        $this->linter = new ModuleLinter();
    }

    /**
     * @return list<string>
     */
    private static function rules(array $findings): array
    {
        return array_values(array_unique(array_map(static fn (array $f): string => $f['rule'], $findings)));
    }

    public function testMBlockModule(): void
    {
        $input = (string) file_get_contents(__DIR__ . '/../../Fixtures/mblock_cards_input.php.txt');
        $findings = $this->linter->lintModule(5, 'Cards', $input, '');
        $rules = self::rules($findings);

        self::assertContains('mblock_show', $rules);
        self::assertContains('mblock_use', $rules);
        self::assertContains('mblock_offline_field', $rules);
        self::assertNotContains('repeater_numeric_widget', $rules, 'MBlock-Formular ist kein Repeater-Formular');

        $show = array_values(array_filter($findings, static fn (array $f): bool => 'mblock_show' === $f['rule']))[0];
        self::assertSame('error', $show['severity']);
        self::assertSame(5, $show['module_id']);
        self::assertSame(24, $show['line']);
        self::assertStringContainsString('--module=5', $show['recommendation']);
    }

    public function testRepeaterWithNumericWidgetsAndPrefixedFields(): void
    {
        $input = '<?php
$id = 1;
$item = MForm::factory()
    ->addTextField("$id.0.title")
    ->addMediaField(1);
$main = MForm::factory()->addMediaField(2)->addFlexRepeaterElement($id, $item);
echo $main->show();';
        $output = '<?php $rows = rex_var::toArray("REX_VALUE[1]"); $other = rex_var::toArray("REX_VALUE[2]");';
        $findings = $this->linter->lintModule(1, 'Repeater', $input, $output);
        $rules = self::rules($findings);

        self::assertSame(['repeater_numeric_widget', 'repeater_prefixed_field', 'output_toarray_repeater'], $rules);
        self::assertNotContains('mblock_show', $rules);

        $numeric = array_values(array_filter($findings, static fn (array $f): bool => 'repeater_numeric_widget' === $f['rule']));
        self::assertCount(1, $numeric, 'addMediaField(2) im Hauptformular ist erlaubt');
        self::assertSame(5, $numeric[0]['line']);

        $toArray = array_values(array_filter($findings, static fn (array $f): bool => 'output_toarray_repeater' === $f['rule']));
        self::assertCount(1, $toArray, 'nur der Repeater-Slot 1, nicht Slot 2');
        self::assertSame('output', $toArray[0]['file']);
    }

    public function testInlineRepeaterForm(): void
    {
        $input = '<?php echo MForm::factory()->addFlexRepeaterElement(2, MForm::factory()->addTextField("title")->addLinkField(1))->addLinkField(3)->show();';
        $findings = $this->linter->lintModule(1, 'Inline', $input, '');

        self::assertSame(['repeater_numeric_widget'], self::rules($findings));
        self::assertCount(1, $findings, 'addLinkField(3) ausserhalb des Repeaters ist erlaubt');
    }

    public function testCleanModuleHasNoFindings(): void
    {
        $input = '<?php echo MForm::factory()->addTextField(1)->addFlexRepeaterElement(2, MForm::factory()->addTextField("title")->addMediaField("image"))->show();';
        $output = '<?php $rows = FriendsOfRedaxo\MForm\Repeater\MFormRepeaterHelper::decode(2);';

        self::assertSame([], $this->linter->lintModule(1, 'Clean', $input, $output));
    }

    public function testCountBySeverity(): void
    {
        $findings = $this->linter->lintModule(1, 'X', "<?php\nuse FriendsOfRedaxo\\MBlock\\MBlock;\n\$f = MForm::factory()->addTextField('1.0.a');\necho MBlock::show(1, \$f->show());", '');
        $counts = ModuleLinter::countBySeverity($findings);

        self::assertSame(1, $counts['error']);
        self::assertSame(1, $counts['warning']);
        self::assertSame(0, $counts['info']);
    }
}
