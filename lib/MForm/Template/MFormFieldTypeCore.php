<?php

namespace FriendsOfRedaxo\MForm\Template;

final class MFormFieldTypeCore
{
    /**
     * @var list<string>
     */
    private const SIMPLE_INPUT_TYPES = [
        'text',
        'email',
        'url',
        'tel',
        'number',
        'color',
        'date',
        'datetime',
        'time',
        'datetime-local',
        'month',
        'week',
        'search',
        'password',
        'range',
        'text-readonly',
    ];

    public static function isSimpleInputType(string $type): bool
    {
        return in_array($type, self::SIMPLE_INPUT_TYPES, true);
    }

    public static function normalizeSimpleInputType(string $type): ?string
    {
        if (!self::isSimpleInputType($type)) {
            return null;
        }

        if ('text-readonly' === $type) {
            return 'text';
        }

        return $type;
    }

    public static function isReadonlySimpleInputType(string $type): bool
    {
        return 'text-readonly' === $type;
    }

    public static function isTextareaLikeType(string $type): bool
    {
        return in_array($type, ['textarea', 'textarea-readonly', 'markitup'], true);
    }

    public static function normalizeTextareaType(string $type): ?string
    {
        if (!self::isTextareaLikeType($type)) {
            return null;
        }

        return 'textarea';
    }

    public static function isReadonlyTextareaType(string $type): bool
    {
        return 'textarea-readonly' === $type;
    }
}
