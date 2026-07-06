<?php

namespace FriendsOfRedaxo\MForm\Template;

class MFormLayoutCore
{
    /**
     * Consumes shared row-class attributes and returns merged class string.
     *
     * @param array<string, mixed> $attributes
     */
    public static function consumeColumnGroupRowClass(array &$attributes): string
    {
        return self::consumeClassAttributes($attributes, ['data-group-column-row-class', 'data-group-row-class']);
    }

    /**
     * Consumes modal row-class attributes and returns merged class string.
     *
     * @param array<string, mixed> $attributes
     */
    public static function consumeModalRowClass(array &$attributes): string
    {
        return self::consumeClassAttributes($attributes, ['data-modal-row-class', 'data-group-row-class']);
    }

    /**
     * @param array<string, mixed> $attributes
     */
    public static function isCollapseOpen(array $attributes): bool
    {
        return isset($attributes['data-group-open-collapse'])
            && (1 === (int) $attributes['data-group-open-collapse'] || true === $attributes['data-group-open-collapse']);
    }

    /**
     * @param array<string, mixed> $attributes
     */
    public static function isCollapseAccordion(array $attributes): bool
    {
        return isset($attributes['data-group-accordion']) && 1 === (int) $attributes['data-group-accordion'];
    }

    /**
     * @param array<string, mixed> $attributes
     */
    public static function shouldHideCollapseToggle(array $attributes, bool $hasLabel): bool
    {
        return !$hasLabel
            || (array_key_exists('data-group-hide-toggle-links', $attributes)
                && 'true' === (string) $attributes['data-group-hide-toggle-links']);
    }

    /**
     * @param array<string, mixed> $attributes
     */
    public static function consumeCollapseWrapperAttributes(array &$attributes): void
    {
        unset(
            $attributes['data-group-hide-toggle-links'],
            $attributes['data-group-accordion'],
            $attributes['data-group-open-collapse'],
        );
    }

    /**
     * @param array<string, mixed> $attributes
     * @param array<int, string> $keys
     */
    private static function consumeClassAttributes(array &$attributes, array $keys): string
    {
        $class = '';

        foreach ($keys as $key) {
            if (!array_key_exists($key, $attributes)) {
                continue;
            }

            $value = $attributes[$key];
            if (is_string($value) && '' !== trim($value)) {
                $class = trim($class . ' ' . $value);
            }

            unset($attributes[$key]);
        }

        return $class;
    }
}
