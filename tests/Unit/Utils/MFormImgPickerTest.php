<?php

declare(strict_types=1);

namespace FriendsOfRedaxo\MForm\Tests\Unit\Utils;

use FriendsOfRedaxo\MForm\Utils\MFormImgPicker;
use PHPUnit\Framework\TestCase;

final class MFormImgPickerTest extends TestCase
{
    public function testSplitSeparatesLabelsAndPreviewData(): void
    {
        [$labels, $meta] = MFormImgPicker::split([
            'text' => ['label' => 'Text', 'img' => 'data:image/svg+xml;base64,AAA', 'description' => 'Überschrift und Fließtext'],
            'video' => ['label' => 'Video', 'icon' => 'rex-icon fa-film'],
            'plain' => 'Nur Text',
            '' => ['label' => 'Leer', 'img' => '  '],
        ]);

        self::assertSame(['text' => 'Text', 'video' => 'Video', 'plain' => 'Nur Text', '' => 'Leer'], $labels);
        self::assertSame([
            'text' => ['img' => 'data:image/svg+xml;base64,AAA', 'description' => 'Überschrift und Fließtext'],
            'video' => ['icon' => 'rex-icon fa-film'],
        ], $meta);
    }

    public function testSplitFallsBackToKeyWithoutLabel(): void
    {
        [$labels] = MFormImgPicker::split(['a' => ['img' => 'x.svg']]);

        self::assertSame(['a' => 'a'], $labels);
    }

    public function testAttributesKeepExistingClassesAndEncodeMeta(): void
    {
        $attributes = MFormImgPicker::attributes(
            ['label' => 'Typ', 'class' => 'my-select', 'form-group-class' => 'my-group'],
            ['text' => ['img' => 'a.svg', 'description' => 'Ä "Zitat"']],
        );

        self::assertSame('Typ', $attributes['label']);
        self::assertSame('my-select mform-img-picker', $attributes['class']);
        self::assertSame('my-group mform-img-picker-group', $attributes['form-group-class']);
        self::assertSame(['text' => ['img' => 'a.svg', 'description' => 'Ä "Zitat"']], json_decode((string) $attributes['data-mform-img-picker'], true));
    }

    public function testAttributesEncodeEmptyMetaAsObject(): void
    {
        $attributes = MFormImgPicker::attributes([], []);

        self::assertSame('{}', $attributes['data-mform-img-picker']);
        self::assertSame('mform-img-picker', $attributes['class']);
    }
}
