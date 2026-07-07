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
     * Ensures selectpicker fields have a stable default container.
     *
     * @param array<string, mixed> $attributes
     */
    public static function ensureSelectpickerContainer(array &$attributes, string $itemClass = ''): void
    {
        $classParts = [];
        if (isset($attributes['class'])) {
            $classParts[] = trim((string) $attributes['class']);
        }
        if ('' !== trim($itemClass)) {
            $classParts[] = trim($itemClass);
        }

        $classAttr = trim(implode(' ', $classParts));
        if ('' === $classAttr) {
            return;
        }

        $classes = preg_split('/\s+/', $classAttr) ?: [];
        if (!in_array('selectpicker', $classes, true)) {
            return;
        }

        $containerAttr = isset($attributes['data-container']) ? trim((string) $attributes['data-container']) : '';
        if ('' === $containerAttr) {
            $attributes['data-container'] = 'body';
        }
    }

    /**
     * @param array<string, mixed> $attributes
     */
    public static function isTabActive(array $attributes): bool
    {
        return isset($attributes['data-group-open-tab']) && self::isTruthyFlag($attributes['data-group-open-tab']);
    }

    /**
     * @param array<string, mixed> $attributes
     */
    public static function isTabPullRight(array $attributes): bool
    {
        return isset($attributes['pull-right']) && self::isTruthyFlag($attributes['pull-right']);
    }

    /**
     * @param array<string, mixed> $attributes
     */
    public static function buildTabNavClass(array $attributes): string
    {
        $class = '';

        if (array_key_exists('nav-class', $attributes) && is_string($attributes['nav-class']) && '' !== trim($attributes['nav-class'])) {
            $class = trim($class . ' ' . $attributes['nav-class']);
        }

        if (self::isTabPullRight($attributes)) {
            $class = trim($class . ' pull-right');
        }

        if (self::isTabActive($attributes)) {
            $class = trim($class . ' active');
        }

        return $class;
    }

    /**
     * @param array<string, mixed> $attributes
     */
    public static function isTabLayoutVertical(array $attributes): bool
    {
        $layout = strtolower(trim((string) ($attributes['data-group-tab-layout'] ?? '')));
        return in_array($layout, ['vertical', 'left', 'nav-left'], true);
    }

    /**
     * @param array<string, mixed> $attributes
     */
    public static function isTabStyleModern(array $attributes): bool
    {
        $style = strtolower(trim((string) ($attributes['data-group-tab-style'] ?? '')));
        return 'modern' === $style;
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

    private static function isTruthyFlag(mixed $value): bool
    {
        if (true === $value) {
            return true;
        }

        if (is_string($value)) {
            $normalized = strtolower(trim($value));
            return '1' === $normalized || 'true' === $normalized;
        }

        return 1 === (int) $value;
    }
}
