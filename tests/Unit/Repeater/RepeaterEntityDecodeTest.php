<?php

declare(strict_types=1);

namespace FriendsOfRedaxo\MForm\Tests\Unit\Repeater;

use FriendsOfRedaxo\MForm\Repeater\MFormRepeaterHelper;
use FriendsOfRedaxo\MForm\Utils\MFormOutputHelper;
use PHPUnit\Framework\TestCase;

/**
 * Entities in Feldwerten gehoeren zum Inhalt: Ein Slot-Wert wird zuerst so gelesen, wie er
 * gespeichert ist. Entities werden nur umgewandelt, wenn das scheitert - das ist der Aufruf
 * mit dem escapten Platzhalter (decode('REX_VALUE[1]')).
 */
final class RepeaterEntityDecodeTest extends TestCase
{
    /** Editor-HTML mit Anfuehrungszeichen im Attribut, so schreibt es TinyMCE. */
    private const LINK = '<p><a href="https://example.org" title="Sagt &quot;Hallo&quot;">Link</a></p>';

    /** Als Text getippte Tags. */
    private const TEXT = '<p>Tag als Text: &lt;h2&gt;Titel&lt;/h2&gt;, a&lt;b, M&uuml;ller &amp; S&ouml;hne, &amp;amp;</p>';

    /** @param array<mixed> $value */
    private static function json(array $value): string
    {
        return (string) json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    /** So setzt REDAXO REX_VALUE[n] ohne output=html ein (rex_escape() + nl2br()). */
    private static function placeholder(string $raw): string
    {
        return nl2br(htmlspecialchars($raw, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'));
    }

    public function testQuoteEntityInValueDoesNotEmptyTheRepeater(): void
    {
        $raw = self::json([['title' => 'A', 'text' => self::LINK], ['title' => 'B']]);
        $rows = MFormRepeaterHelper::decode($raw);

        self::assertCount(2, $rows);
        self::assertSame(self::LINK, $rows[0]['text']);
    }

    public function testEntitiesInValuesStayAsStored(): void
    {
        $raw = self::json([['title' => 'Tippt &quot; und &lt;b&gt;', 'text' => self::TEXT]]);
        $rows = MFormRepeaterHelper::decode($raw);

        self::assertSame(self::TEXT, $rows[0]['text']);
        self::assertSame('Tippt &quot; und &lt;b&gt;', $rows[0]['title']);
    }

    public function testEnvelopeWithQuoteEntity(): void
    {
        $raw = self::json(['__v' => 2, 'items' => [['text' => self::LINK], ['text' => 'aus', '__disabled' => 1]]]);

        self::assertSame([['text' => self::LINK]], MFormRepeaterHelper::decode($raw));
        self::assertSame(2, MFormRepeaterHelper::dataVersion($raw));
        self::assertTrue(MFormOutputHelper::isRepeater($raw));
    }

    public function testDotNotationValuesStayAsStored(): void
    {
        $raw = self::json(['1' => self::LINK, '2' => self::TEXT]);

        self::assertSame(['1' => self::LINK, '2' => self::TEXT], MFormOutputHelper::values($raw));
        self::assertSame(self::TEXT, MFormOutputHelper::value($raw, '2'));
    }

    public function testEscapedPlaceholderStillWorks(): void
    {
        // decode('REX_VALUE[1]'): Der ganze Wert ist einmal escaped, eine Umwandlung stellt ihn wieder her.
        $raw = self::json([['title' => 'Tom & Jerry', 'text' => self::LINK], ['text' => self::TEXT]]);
        $rows = MFormRepeaterHelper::decode(self::placeholder($raw));

        self::assertCount(2, $rows);
        self::assertSame('Tom & Jerry', $rows[0]['title']);
        self::assertSame(self::LINK, $rows[0]['text']);
        self::assertSame(self::TEXT, $rows[1]['text']);
        self::assertSame(2, MFormRepeaterHelper::dataVersion(self::placeholder(self::json(['__v' => 2, 'items' => [['text' => self::LINK]]]))));
    }

    public function testEscapedPlaceholderWithLineBreaksStillWorks(): void
    {
        // nl2br() setzt <br /> zwischen die Zeilen eines mehrzeilig gespeicherten JSON.
        $raw = (string) json_encode([['title' => 'A', 'text' => 'Zeile']], JSON_PRETTY_PRINT);

        self::assertSame([['title' => 'A', 'text' => 'Zeile']], MFormRepeaterHelper::decode(self::placeholder($raw)));
    }
}
