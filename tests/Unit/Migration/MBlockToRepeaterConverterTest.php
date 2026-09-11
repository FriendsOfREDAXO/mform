<?php

declare(strict_types=1);

namespace FriendsOfRedaxo\MForm\Tests\Unit\Migration;

use FriendsOfRedaxo\MForm\Migration\MBlockToRepeaterConverter;
use PHPUnit\Framework\TestCase;

final class MBlockToRepeaterConverterTest extends TestCase
{
    private MBlockToRepeaterConverter $converter;

    protected function setUp(): void
    {
        $this->converter = new MBlockToRepeaterConverter();
    }

    private static function fixture(string $name): string
    {
        return (string) file_get_contents(__DIR__ . '/../../Fixtures/' . $name);
    }

    public function testConvertInputCardsModule(): void
    {
        $result = $this->converter->convertInput(self::fixture('mblock_cards_input.php.txt'));
        $code = $result['code'];

        // Block-Formular: Praefixe weg, numerische Widgets gemappt, Offline-Feld weg.
        self::assertStringContainsString("->addTextField('header'", $code);
        self::assertStringContainsString("->addMediaField('media', ['label' => 'Image'", $code);
        self::assertStringContainsString("->addMediaField('media_2'", $code);
        self::assertStringContainsString("->addCustomLinkField('link'", $code);
        self::assertStringNotContainsString('mblock_offline', preg_replace('~^\s*//.*$~m', '', $code) ?? '');
        self::assertStringNotContainsString('$id.0.', $code);

        // Hauptformular bleibt: Slot 2 und Media-Slot 1.
        self::assertStringContainsString('"2.0.gutter"', $code);
        self::assertStringContainsString("->addMediaField(1, ['label' => 'Background Image'])", $code);

        // addHtml($blocks)-Muster inlined, Optionen gemappt, unbekannte gemeldet.
        self::assertStringContainsString("->addFlexRepeaterElement(\$id, \$mform, ['max' => 100, 'copy_paste' => true])", $code);
        self::assertStringNotContainsString('addHtml($blocks)', $code);
        self::assertStringNotContainsString('use FriendsOfRedaxo\MBlock\MBlock;', $code);
        self::assertStringContainsString('smooth_scroll', implode("\n", $result['warnings']));
        self::assertStringContainsString('online_offline', implode("\n", $result['notes']));
    }

    public function testConvertInputWithoutShowCall(): void
    {
        $code = '<?php $id = 1; $f = MForm::factory()->addTextField("$id.0.a"); echo MBlock::show($id, $f, array("min" => 1));';
        $result = $this->converter->convertInput($code);

        self::assertStringContainsString("echo MForm::factory()->addFlexRepeaterElement(\$id, \$f, ['min' => 1])->show();", $result['code']);
    }

    public function testConvertInputTwoSlots(): void
    {
        $code = '<?php $a = MForm::factory()->addTextField("1.0.x"); $b = MForm::factory()->addTextField("3.0.y")->addMediaField(1); echo MBlock::show(1, $a->show()); echo MBlock::show(3, $b->show());';
        $result = $this->converter->convertInput($code);

        self::assertStringContainsString("addTextField('x')", $result['code']);
        self::assertStringContainsString("addTextField('y')", $result['code']);
        self::assertStringContainsString("addMediaField('media')", $result['code']);
        self::assertSame(2, substr_count($result['code'], 'addFlexRepeaterElement('));
    }

    public function testConvertOutputWithKeyMapFallbacks(): void
    {
        $result = $this->converter->convertOutput(self::fixture('mblock_cards_output.php.txt'), '1', ['1' => ['1' => 'link', 'REX_MEDIA_1' => 'media', 'REX_MEDIA_2' => 'media_2']]);
        $code = $result['code'];

        self::assertStringContainsString('MFormRepeaterHelper::decode(1)', $code);
        self::assertStringContainsString('rex_var::toArray("REX_VALUE[2]")', $code, 'fremder Slot bleibt');
        self::assertStringContainsString("(\$item['media'] ?? (\$item['REX_MEDIA_1'] ?? ''))", $code);
        self::assertStringContainsString("(\$item['media_2'] ?? (\$item['REX_MEDIA_2'] ?? ''))", $code);
        self::assertStringContainsString("(\$item['link'] ?? (\$item['1'] ?? ''))", $code);
        self::assertStringContainsString('use FriendsOfRedaxo\MForm\Repeater\MFormRepeaterHelper;', $code);
        self::assertStringNotContainsString('use FriendsOfRedaxo\MBlock\MBlock;', $code);
    }

    public function testConvertOutputMultipleSlots(): void
    {
        $result = $this->converter->convertOutput('<?php $a = rex_var::toArray("REX_VALUE[1]"); $b = rex_var::toArray("REX_VALUE[3]"); $c = rex_var::toArray("REX_VALUE[2]");', '1,3');

        self::assertSame(2, substr_count($result['code'], 'MFormRepeaterHelper::decode('));
        self::assertStringContainsString('REX_VALUE[2]', $result['code']);
    }

    public function testConvertDataMapsKeysAndOffline(): void
    {
        $raw = json_encode([
            ['checkbox_block_hold' => 'hold_block', 'header' => 'A', 'REX_MEDIA_1' => 'a.jpg', 'REX_MEDIA_2' => 'b.jpg', '1' => 'https://example.org', 'mblock_offline' => '1'],
            ['checkbox_block_hold' => 'hold_block', 'header' => 'B', 'REX_MEDIA_1' => '', 'REX_MEDIA_2' => '', '1' => '', 'mblock_offline' => '0'],
        ]);
        $result = $this->converter->convertData((string) $raw, '1', ['REX_MEDIA_1' => 'media', 'REX_MEDIA_2' => 'media_2', '1' => 'link']);
        $items = json_decode($result['json'], true);

        self::assertSame(2, $result['count']);
        self::assertSame(['header' => 'A', 'media' => 'a.jpg', 'media_2' => 'b.jpg', 'link' => 'https://example.org', '__disabled' => true], $items[0]);
        self::assertSame(['header' => 'B', 'media' => '', 'media_2' => '', 'link' => ''], $items[1]);
        self::assertSame([], $result['warnings']);
    }

    public function testConvertDataDefaultHeuristicsWithoutMap(): void
    {
        $raw = json_encode([['checkbox_block_hold' => 'x', 'REX_MEDIA_1' => 'a.jpg', 'REX_LINK_1' => '5', '1' => '']]);
        $items = json_decode($this->converter->convertData((string) $raw)['json'], true);

        self::assertSame(['media' => 'a.jpg', 'link' => '5'], $items[0]);
    }

    public function testConvertDataNestedLists(): void
    {
        $raw = json_encode([[
            'checkbox_block_hold' => 'x',
            'title' => 'Outer',
            'items' => [
                ['checkbox_block_hold' => 'x', 'name' => 'i1', 'mblock_offline' => '1'],
                ['checkbox_block_hold' => 'x', 'name' => 'i2'],
            ],
            'plain' => ['a', 'b'],
        ]]);
        $result = $this->converter->convertData((string) $raw);
        $items = json_decode($result['json'], true);

        self::assertSame([['name' => 'i1', '__disabled' => true], ['name' => 'i2']], $items[0]['items']);
        self::assertSame(['a', 'b'], $items[0]['plain'], 'gewoehnliche Arrays bleiben unangetastet');
        self::assertStringContainsString('verschachtelte', implode("\n", $result['notes']));
    }

    public function testConvertDataGridblockWrappers(): void
    {
        $raw = json_encode([
            'GBSaaaa' => ['VALUE' => ['1' => [['checkbox_block_hold' => 'x', 'a' => 1]]]],
            'GBSbbbb' => ['VALUE' => ['1' => [['checkbox_block_hold' => 'x', 'a' => 2]]]],
        ]);

        $first = $this->converter->convertData((string) $raw, '1');
        self::assertSame(1, $first['count']);
        self::assertNotSame([], $first['warnings']);

        $merged = $this->converter->convertData((string) $raw, '1', [], ['merge_columns' => true]);
        self::assertSame(2, $merged['count']);
        self::assertSame([['a' => 1], ['a' => 2]], json_decode($merged['json'], true));
    }

    public function testConvertDataRejectsInvalidJson(): void
    {
        $result = $this->converter->convertData('not json');

        self::assertSame(0, $result['count']);
        self::assertSame('', $result['json']);
        self::assertNotSame([], $result['warnings']);
    }

    public function testConvertDataNormalizesListFields(): void
    {
        $raw = json_encode([['checkbox_block_hold' => 'x', 'REX_MEDIALIST_1' => ' a.jpg, b.jpg,,a.jpg ', 'links' => ['5', ' 7', '']]]);
        $result = $this->converter->convertData((string) $raw, '1', ['REX_MEDIALIST_1' => 'medialist'], ['list_fields' => ['medialist' => 'media', 'links' => 'link'], 'check_existence' => false]);
        $items = json_decode($result['json'], true);

        self::assertSame('a.jpg,b.jpg', $items[0]['medialist']);
        self::assertSame('5,7', $items[0]['links']);
        self::assertStringContainsString('Listenwert', implode("\n", $result['notes']));
    }

    public function testConvertDataLanguageArray(): void
    {
        $raw = json_encode([
            '1' => [['checkbox_block_hold' => 'x', 'title' => 'de', 'mblock_offline' => '1']],
            '2' => [['checkbox_block_hold' => 'x', 'title' => 'en']],
        ]);
        $result = $this->converter->convertData((string) $raw, '1');
        $decoded = json_decode($result['json'], true);

        self::assertSame(2, $result['count']);
        self::assertSame(['1', '2'], array_map('strval', array_keys($decoded)));
        self::assertSame(['title' => 'de', '__disabled' => true], $decoded['1'][0]);
        self::assertSame(['title' => 'en'], $decoded['2'][0]);
        self::assertStringContainsString('Mehrsprachiger Wert', implode("\n", $result['notes']));
    }

    public function testConvertDataIsIdempotent(): void
    {
        $raw = json_encode([['checkbox_block_hold' => 'x', 'header' => 'A', 'mblock_offline' => '1']]);
        $once = $this->converter->convertData((string) $raw)['json'];
        $twice = $this->converter->convertData($once)['json'];

        self::assertSame($once, $twice);
    }
}
