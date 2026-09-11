<?php

namespace FriendsOfRedaxo\MForm\FieldType;

use InvalidArgumentException;

use function is_string;

/**
 * Registry fuer eigene Feldtypen (#399). Fremd-Addons registrieren in ihrer
 * boot.php einen Typnamen samt Renderer-Klasse; MForm rendert den Typ dann im
 * klassischen Parser und im Flex-Repeater ueber dieselbe Schnittstelle.
 */
final class FieldTypeRegistry
{
    /** @var array<string, class-string<FieldTypeInterface>|FieldTypeInterface> */
    private static array $types = [];

    /**
     * @param class-string<FieldTypeInterface>|FieldTypeInterface $renderer
     */
    public static function register(string $type, string|FieldTypeInterface $renderer): void
    {
        $type = self::normalize($type);
        if ('' === $type) {
            throw new InvalidArgumentException('Field type name must not be empty.');
        }
        if (is_string($renderer) && !is_subclass_of($renderer, FieldTypeInterface::class)) {
            throw new InvalidArgumentException(sprintf('"%s" must implement %s.', $renderer, FieldTypeInterface::class));
        }
        self::$types[$type] = $renderer;
    }

    public static function unregister(string $type): void
    {
        unset(self::$types[self::normalize($type)]);
    }

    public static function has(string $type): bool
    {
        return isset(self::$types[self::normalize($type)]);
    }

    public static function get(string $type): ?FieldTypeInterface
    {
        $type = self::normalize($type);
        if (!isset(self::$types[$type])) {
            return null;
        }
        $renderer = self::$types[$type];
        if (is_string($renderer)) {
            $renderer = new $renderer();
            self::$types[$type] = $renderer;
        }
        return $renderer;
    }

    /** @return list<string> */
    public static function all(): array
    {
        return array_keys(self::$types);
    }

    private static function normalize(string $type): string
    {
        return strtolower(trim($type));
    }
}
