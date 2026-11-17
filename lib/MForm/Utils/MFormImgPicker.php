<?php

/**
 * @package redaxo5
 * @license MIT
 */

namespace FriendsOfRedaxo\MForm\Utils;

/**
 * Bild-Picker (MForm::addImgPickerField): eine normale Auswahlliste, die im Backend per
 * assets/js/img-picker.js zu einem kompakten Feld mit Vorschaubild wird. Ein Klick öffnet
 * ein Fenster mit allen Optionen als Bildkacheln.
 *
 * Optionen wie bei addRadioImgField():
 *   ['key' => ['label' => 'Text', 'img' => 'URL oder data-URI', 'description' => 'optional', 'icon' => 'optional, z. B. fa-solid fa-star']]
 * Ein reiner String als Option ist nur Text (ohne Bild).
 */
final class MFormImgPicker
{
    /**
     * Teilt die Optionen in die Texte für das <select> und die Bilddaten für das JavaScript.
     *
     * @param array<string|int, mixed> $options
     * @return array{0: array<string|int, string>, 1: array<string, array{img?: string, icon?: string, description?: string}>}
     */
    public static function split(array $options): array
    {
        $labels = [];
        $meta = [];
        foreach ($options as $key => $option) {
            if (!is_array($option)) {
                $labels[$key] = (string) $option;
                continue;
            }
            $labels[$key] = (string) ($option['label'] ?? $key);
            $entry = [];
            foreach (['img', 'icon', 'description'] as $field) {
                if (isset($option[$field]) && '' !== trim((string) $option[$field])) {
                    $entry[$field] = (string) $option[$field];
                }
            }
            if ([] !== $entry) {
                $meta[(string) $key] = $entry;
            }
        }

        return [$labels, $meta];
    }

    /**
     * Attribute für das <select>: Klasse für das JavaScript, Bilddaten als JSON, Klasse für die form-group.
     *
     * @param array<string, mixed> $attributes
     * @param array<string, array<string, string>> $meta
     * @return array<string, mixed>
     */
    public static function attributes(array $attributes, array $meta): array
    {
        $attributes['class'] = trim((string) ($attributes['class'] ?? '') . ' mform-img-picker');
        $attributes['data-mform-img-picker'] = (string) json_encode((object) $meta, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        $attributes['form-group-class'] = trim((string) ($attributes['form-group-class'] ?? '') . ' mform-img-picker-group');

        return $attributes;
    }
}
