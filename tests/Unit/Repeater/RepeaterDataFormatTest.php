<?php

declare(strict_types=1);

namespace FriendsOfRedaxo\MForm\Tests\Unit\Repeater;

use FriendsOfRedaxo\MForm\Output\MFormOutput;
use FriendsOfRedaxo\MForm\Repeater\MFormRepeaterHelper;
use FriendsOfRedaxo\MForm\Repeater\MFormRepeaterItem;
use FriendsOfRedaxo\MForm\Utils\MFormOutputHelper;
use PHPUnit\Framework\TestCase;

final class RepeaterDataFormatTest extends TestCase
{
    public function testDecodeReadsListAndEnvelope(): void
    {
        $items = [['title' => 'A'], ['title' => 'B', '__disabled' => true], ['title' => 'C']];
        $list = (string) json_encode($items);
        $envelope = (string) json_encode(['__v' => 2, 'items' => $items]);

        self::assertSame([['title' => 'A'], ['title' => 'C']], MFormRepeaterHelper::decode($list));
        self::assertSame([['title' => 'A'], ['title' => 'C']], MFormRepeaterHelper::decode($envelope));
        self::assertSame(1, MFormRepeaterHelper::dataVersion($list));
        self::assertSame(2, MFormRepeaterHelper::dataVersion($envelope));
        self::assertSame(1, MFormRepeaterHelper::dataVersion('kein json'));
    }

    public function testEncodeProducesBothFormats(): void
    {
        $items = [['title' => 'A']];
        self::assertSame('[{"title":"A"}]', MFormRepeaterHelper::encode($items));
        self::assertSame('{"__v":2,"items":[{"title":"A"}]}', MFormRepeaterHelper::encode($items, 2));
        self::assertSame($items, MFormRepeaterHelper::decode(MFormRepeaterHelper::encode($items, 2)));
    }

    public function testNestedEnvelopeIsUnwrapped(): void
    {
        $raw = (string) json_encode([['title' => 'A', 'tags' => ['__v' => 2, 'items' => [['name' => 't1'], ['name' => 't2', '__disabled' => '1']]]]]);
        $decoded = MFormRepeaterHelper::decode($raw);

        self::assertSame([['name' => 't1']], $decoded[0]['tags']);
    }

    public function testTypedItems(): void
    {
        $output = MFormOutput::from([['title' => 'A', 'count' => '3', 'active' => '1', 'tags' => [['name' => 'x']]], ['title' => 'B']]);
        $items = $output->items();

        self::assertCount(2, $items);
        self::assertInstanceOf(MFormRepeaterItem::class, $items[0]);
        self::assertSame('A', $items[0]->string('title'));
        self::assertSame(3, $items[0]->int('count'));
        self::assertTrue($items[0]->bool('active'));
        self::assertFalse($items[1]->bool('active'));
        self::assertSame(1, $items[0]->items('tags')->count());
        self::assertSame('x', $items[0]->items('tags')->item(0)?->string('name'));
        self::assertNull($output->item(5));
        self::assertSame(['title' => 'B'], $output->item(1)?->raw());
    }

    public function testLinkValueClassification(): void
    {
        self::assertSame('article', MFormRepeaterItem::linkTypeOf('12'));
        self::assertSame('article', MFormRepeaterItem::linkTypeOf('redaxo://12'));
        self::assertSame(12, MFormRepeaterItem::articleIdOf('redaxo://12'));
        self::assertSame('dataset', MFormRepeaterItem::linkTypeOf('rex-rex-news://5'));
        self::assertSame(['table' => 'rex_news', 'id' => 5], MFormRepeaterItem::datasetRefOf('rex-rex-news://5'));
        self::assertSame(['table' => 'rex_news', 'id' => 7], MFormRepeaterItem::datasetRefOf('yform://rex_news/7?scheme=url'));
        self::assertSame('media', MFormRepeaterItem::linkTypeOf('bild.jpg'));
        self::assertSame('url', MFormRepeaterItem::linkTypeOf('https://example.org/x.jpg'));
        self::assertSame('mailto', MFormRepeaterItem::linkTypeOf('mailto:a@b.de'));
        self::assertSame('tel', MFormRepeaterItem::linkTypeOf('tel:+49'));
        self::assertSame('anchor', MFormRepeaterItem::linkTypeOf('#top'));
        self::assertSame('', MFormRepeaterItem::linkTypeOf(''));

        $item = new MFormRepeaterItem(['files' => ' a.jpg, b.jpg,,', 'url' => 'https://example.org', 'mail' => 'mailto:a@b.de']);
        self::assertSame(['a.jpg', 'b.jpg'], $item->list('files'));
        self::assertSame('https://example.org', $item->url('url'));
        self::assertSame('mailto:a@b.de', $item->url('mail'));
        self::assertSame('', $item->url('missing'));
    }

    public function testDecodeIgnoresNonRepeaterPayloads(): void
    {
        // Punkt-Notation, Multiselect-Gruppen und skalare Slots sind keine Repeater-Daten.
        self::assertSame([], MFormRepeaterHelper::decode('{"1":"Hallo","2":"Welt"}'));
        self::assertSame([], MFormRepeaterHelper::decode('{"multiselect":["a","b"]}'));
        self::assertSame([], MFormRepeaterHelper::decode('einfacher Text'));
        self::assertSame([], MFormRepeaterHelper::decode(''));
    }

    public function testValuesReadsDotNotationSlots(): void
    {
        self::assertSame(['1' => 'Hallo', '2' => 'Welt'], MFormRepeaterHelper::values('{"1":"Hallo","2":"Welt"}'));
        self::assertSame(['multiselect' => ['a', 'b']], MFormRepeaterHelper::values('{"multiselect":["a","b"]}'));

        // Entities und der <br>-Fallback gelten wie bei decode().
        self::assertSame(['1' => 'A&B'], MFormRepeaterHelper::values('{&quot;1&quot;:&quot;A&amp;B&quot;}'));
        self::assertSame(['1' => 'A'], MFormRepeaterHelper::values('{"1":"A"}<br />'));

        // Repeater-Daten gehoeren nicht hierher - in beiden Speicherformaten.
        self::assertSame([], MFormRepeaterHelper::values('[{"text1":"A"}]'));
        self::assertSame([], MFormRepeaterHelper::values('{"__v":2,"items":[{"t":"A"}]}'));
        self::assertSame([], MFormRepeaterHelper::values('einfacher Text'));
    }

    public function testValueReadsSingleFields(): void
    {
        self::assertSame('Welt', MFormRepeaterHelper::value('{"1":"Hallo","2":"Welt"}', '2'));
        self::assertSame('tief', MFormRepeaterHelper::value('{"a":{"b":"tief"}}', 'a.b'));
        self::assertSame(['a', 'b'], MFormRepeaterHelper::value('{"multiselect":["a","b"]}', 'multiselect'));

        // Skalarer Slot ohne Feldangabe.
        self::assertSame('einfacher Text', MFormRepeaterHelper::value('einfacher Text'));

        // Default greift bei fehlendem Feld, leerem Slot und falscher Struktur.
        self::assertSame('FB', MFormRepeaterHelper::value('{"1":"Hallo"}', '9', 'FB'));
        self::assertSame('FB', MFormRepeaterHelper::value('nur Text', '1', 'FB'));
        self::assertSame('FB', MFormRepeaterHelper::value('', null, 'FB'));
        self::assertNull(MFormRepeaterHelper::value('{"1":"Hallo"}'));
    }

    public function testIsRepeaterDistinguishesSlotTypes(): void
    {
        self::assertTrue(MFormRepeaterHelper::isRepeater('[{"text1":"A"}]'));
        self::assertTrue(MFormRepeaterHelper::isRepeater('{"__v":2,"items":[{"t":"A"}]}'));

        self::assertFalse(MFormRepeaterHelper::isRepeater('{"1":"Hallo"}'));
        self::assertFalse(MFormRepeaterHelper::isRepeater('einfacher Text'));
        self::assertFalse(MFormRepeaterHelper::isRepeater(''));
    }

    public function testOutputHelperIsCanonicalAndRepeaterHelperAliases(): void
    {
        $dotted = '{"1":"Hallo","2":"Welt"}';

        // MFormOutputHelper ist die fachliche Heimat fuer Nicht-Repeater-Slots ...
        self::assertSame(['1' => 'Hallo', '2' => 'Welt'], MFormOutputHelper::values($dotted));
        self::assertSame('Welt', MFormOutputHelper::value($dotted, '2'));
        self::assertFalse(MFormOutputHelper::isRepeater($dotted));
        self::assertTrue(MFormOutputHelper::isRepeater('[{"t":"A"}]'));

        // ... MFormRepeaterHelper delegiert nur, damit beide Einstiege gleich funktionieren.
        self::assertSame(MFormOutputHelper::values($dotted), MFormRepeaterHelper::values($dotted));
        self::assertSame(MFormOutputHelper::value($dotted, '2'), MFormRepeaterHelper::value($dotted, '2'));
        self::assertSame(MFormOutputHelper::isRepeater($dotted), MFormRepeaterHelper::isRepeater($dotted));
    }

    public function testDecodeStaysBackwardCompatible(): void
    {
        // Alle Formen, die 10.0.0 bereits verarbeitet hat, liefern unveraendert dasselbe.
        self::assertSame([['t' => 'A'], ['t' => 'B']], MFormRepeaterHelper::decode('[{"t":"A"},{"t":"B"}]'));
        self::assertSame([['t' => 'A']], MFormRepeaterHelper::decode('[{"t":"A"},{"t":"B","__disabled":"1"}]'));
        self::assertSame([['t' => 'A']], MFormRepeaterHelper::decode('{"__v":2,"items":[{"t":"A"}]}'));
        self::assertSame([['t' => 'A']], MFormRepeaterHelper::decode('[{&quot;t&quot;:&quot;A&quot;}]'));
        self::assertSame([['t' => 'A']], MFormRepeaterHelper::decode('[{"t":"A"}]<br />'));
        self::assertSame([], MFormRepeaterHelper::decode('{nicht json'));
        self::assertSame([], MFormRepeaterHelper::decode('[]'));
    }
}
