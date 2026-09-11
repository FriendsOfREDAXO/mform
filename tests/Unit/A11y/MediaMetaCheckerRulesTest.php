<?php

declare(strict_types=1);

namespace FriendsOfRedaxo\MForm\Tests\Unit\A11y;

use FriendsOfRedaxo\MForm\A11y\MediaMetaChecker;
use FriendsOfRedaxo\MForm\DTO\MFormItem;
use PHPUnit\Framework\TestCase;

final class MediaMetaCheckerRulesTest extends TestCase
{
    public function testNormalizesShortForms(): void
    {
        self::assertSame(
            ['required_media_meta' => [['field' => 'med_alt', 'message' => '']], 'strict' => false],
            MediaMetaChecker::normalizeRules('med_alt'),
        );
        self::assertSame(
            ['required_media_meta' => [['field' => 'med_alt', 'message' => ''], ['field' => 'med_title', 'message' => '']], 'strict' => false],
            MediaMetaChecker::normalizeRules(['med_alt', 'med_title']),
        );
    }

    public function testNormalizesFullFormWithMessagesAndStrict(): void
    {
        $rules = MediaMetaChecker::normalizeRules([
            'required_media_meta' => [
                ['field' => ' med_alt ', 'message' => 'ALT fehlt'],
                'med_copyright',
                ['field' => 'mediaplace:alt'],
                ['field' => 'bad field'],
                ['message' => 'ohne Feld'],
                42,
            ],
            'strict' => 1,
        ]);

        self::assertNotNull($rules);
        self::assertTrue($rules['strict']);
        self::assertSame([
            ['field' => 'med_alt', 'message' => 'ALT fehlt'],
            ['field' => 'med_copyright', 'message' => ''],
            ['field' => 'mediaplace:alt', 'message' => ''],
        ], $rules['required_media_meta']);
    }

    public function testRejectsInvalidInput(): void
    {
        self::assertNull(MediaMetaChecker::normalizeRules(null));
        self::assertNull(MediaMetaChecker::normalizeRules(true));
        self::assertNull(MediaMetaChecker::normalizeRules([]));
        self::assertNull(MediaMetaChecker::normalizeRules(['art_title']), 'nur med_* und mediaplace: sind erlaubt');
        self::assertNull(MediaMetaChecker::normalizeRules(['required_media_meta' => 'med_alt']));
    }

    public function testApplyToItemMovesOptionIntoFormGroupAttributes(): void
    {
        $item = new MFormItem();
        $item->setType('media');
        $item->setParameter(['label' => 'Bild', 'a11y' => ['med_alt', ['field' => 'med_title', 'message' => 'Titel']], 'preview' => 1]);
        $item->setAttributes(['class' => 'x']);

        MediaMetaChecker::applyToItem($item);

        self::assertSame(['label' => 'Bild', 'preview' => 1], $item->getParameter(), 'a11y darf nicht ans Widget gehen');
        $formGroup = $item->getAttributes()['form-group-attributes'];
        self::assertSame('[{"field":"med_alt","message":""},{"field":"med_title","message":"Titel"}]', $formGroup['data-mform-a11y']);
        self::assertArrayNotHasKey('data-mform-a11y-strict', $formGroup);

        // Idempotent: zweiter Aufruf aendert nichts.
        MediaMetaChecker::applyToItem($item);
        self::assertSame($formGroup, $item->getAttributes()['form-group-attributes']);
    }

    public function testApplyToItemStrictAndAttributesSource(): void
    {
        $item = new MFormItem();
        $item->setType('custom-link');
        $item->setAttributes(['a11y' => ['required_media_meta' => ['med_alt'], 'strict' => true], 'data-media' => 'enable']);

        MediaMetaChecker::applyToItem($item);

        $attributes = $item->getAttributes();
        self::assertArrayNotHasKey('a11y', $attributes);
        self::assertSame('1', $attributes['form-group-attributes']['data-mform-a11y-strict']);
        self::assertSame('enable', $attributes['data-media']);
    }

    public function testApplyToItemIgnoresNonMediaTypes(): void
    {
        $item = new MFormItem();
        $item->setType('text');
        $item->setAttributes(['a11y' => ['med_alt']]);

        MediaMetaChecker::applyToItem($item);

        self::assertSame([], $item->getAttributes());
    }
}
