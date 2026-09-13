<?php

/**
 * @author Friends Of REDAXO
 * @license MIT
 */

namespace FriendsOfRedaxo\MForm\Template;

use rex_i18n;

use function array_key_exists;
use function is_bool;
use function is_string;

/**
 * Markup des Tags-Widgets (addTagsField): kommaseparierter Wert in einem Hidden-Input,
 * Pills fuer vorhandene Tags, Texteingabe mit Vorschlaegen (datalist).
 * Wird vom klassischen Parser und vom Flex-Repeater gleich genutzt, die Logik steckt
 * in assets/mform.js (initMFormTags).
 */
final class MFormTagsWidget
{
    /**
     * @param string $hiddenInputAttributes z. B. ` name="REX_INPUT_VALUE[1]"` oder ` data-mfr-field="tags"`
     * @param list<string> $suggestions
     * @param array<string, mixed> $attributes allow_new (bool, Standard true), max (int, 0 = unbegrenzt), placeholder
     */
    public static function render(string $uid, string $hiddenInputAttributes, string $value, array $suggestions, array $attributes): string
    {
        $allowNew = self::allowsNew($attributes);
        $max = max(0, (int) ($attributes['max'] ?? 0));
        $placeholder = isset($attributes['placeholder']) && is_string($attributes['placeholder'])
            ? $attributes['placeholder']
            : rex_i18n::msg('mform_tags_placeholder');

        $tags = '' === trim($value) ? [] : array_values(array_unique(array_filter(array_map('trim', explode(',', $value)), static fn (string $tag) => '' !== $tag)));

        $html = sprintf(
            '<div class="mform-tags" data-tags-id="%s" data-allow-new="%s" data-max="%d">',
            htmlspecialchars($uid, ENT_QUOTES),
            $allowNew ? '1' : '0',
            $max,
        );
        $html .= sprintf(
            '<input type="hidden" id="%s"%s value="%s" class="mform-tags-value">',
            htmlspecialchars($uid, ENT_QUOTES),
            $hiddenInputAttributes,
            htmlspecialchars(implode(',', $tags), ENT_QUOTES),
        );
        $html .= '<div class="mform-tags-list" role="list">';
        foreach ($tags as $tag) {
            $escaped = htmlspecialchars($tag, ENT_QUOTES);
            $html .= '<span class="mform-tags-tag" role="listitem">' . $escaped
                . '<button type="button" class="mform-tags-remove" data-tag="' . $escaped . '" aria-label="' . htmlspecialchars(rex_i18n::msg('mform_tags_remove'), ENT_QUOTES) . '">&times;</button></span>';
        }
        $html .= '</div>';
        $html .= sprintf(
            '<input type="text" class="mform-tags-input" placeholder="%s" list="%s-list" autocomplete="off" aria-label="%s">',
            htmlspecialchars($placeholder, ENT_QUOTES),
            htmlspecialchars($uid, ENT_QUOTES),
            htmlspecialchars($placeholder, ENT_QUOTES),
        );
        $html .= '<datalist id="' . htmlspecialchars($uid, ENT_QUOTES) . '-list">';
        foreach ($suggestions as $suggestion) {
            $html .= '<option value="' . htmlspecialchars($suggestion, ENT_QUOTES) . '"></option>';
        }
        $html .= '</datalist></div>';

        return $html;
    }

    /**
     * Vorschlaege aus dem Options-Array: Liste von Strings; bei key => label zaehlt das Label.
     *
     * @param array<int|string, mixed> $options
     * @return list<string>
     */
    public static function suggestions(array $options): array
    {
        $out = [];
        foreach ($options as $option) {
            $text = trim((string) (is_array($option) ? ($option['label'] ?? '') : $option));
            if ('' !== $text && !in_array($text, $out, true)) {
                $out[] = $text;
            }
        }

        return $out;
    }

    /** @param array<string, mixed> $attributes */
    private static function allowsNew(array $attributes): bool
    {
        if (!array_key_exists('allow_new', $attributes)) {
            return true;
        }
        $value = $attributes['allow_new'];
        if (is_bool($value)) {
            return $value;
        }

        return filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? (bool) $value;
    }
}
