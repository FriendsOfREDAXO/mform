<?php

declare(strict_types=1);

namespace FriendsOfRedaxo\MForm\Tests\Unit\Repeater;

use FriendsOfRedaxo\MForm\Output\MFormOutput;
use FriendsOfRedaxo\MForm\Repeater\MFormRepeaterHelper;
use FriendsOfRedaxo\MForm\Repeater\MFormRepeaterItem;
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
}
