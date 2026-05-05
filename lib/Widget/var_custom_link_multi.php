<?php

/**
 * Custom Link Multi widget for MForm.
 *
 * Renders a repeatable list of custom link widgets.
 * Data is stored as JSON array: ["redaxo://1", "mailto:a@b.com", "https://..."]
 * The single addCustomLinkField() format stays UNCHANGED.
 */
class rex_var_custom_link_multi extends rex_var
{
    protected function getOutput()
    {
        return false;
    }

    /**
     * @param string|int $id
     * @param string $name
     * @param string $value JSON-encoded array of link strings
     * @param array<string, mixed> $args
     */
    public static function getWidget($id, string $name, string $value, array $args = []): string
    {
        // Decode existing links – REDAXO returns HTML-encoded values (e.g. &quot; instead of ")
        $links = [];
        if ($value !== '') {
            $rawValue = html_entity_decode($value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
            $decoded = json_decode($rawValue, true);
            if (is_array($decoded)) {
                $links = $decoded;
            } else {
                $links = [$rawValue];
            }
        }

        // Build template HTML (empty item) using placeholder ID and empty name
        // btnIdUniq=false so we control the ID ourselves
        $templateHtml = rex_var_custom_link::getWidget('CMLIDX', '', '', $args, false);

        // Build existing items HTML
        $itemsHtml = '';
        foreach ($links as $i => $linkValue) {
            $itemId = 'cm_' . uniqid('', false);
            $itemHtml = rex_var_custom_link::getWidget($itemId, '', (string) $linkValue, $args, false);
            $itemsHtml .= self::wrapItem($itemHtml, (string) $linkValue);
        }

        $addLabel = $args['btn_add'] ?? rex_i18n::msg('mform_cl_multi_add');

        return '<div class="mform-cl-multi rex-js-cl-multi"'
            . ' data-name="' . rex_escape($name) . '"'
            . ' data-template="' . rex_escape($templateHtml) . '">'
            . '<div class="mform-cl-multi-list">' . $itemsHtml . '</div>'
            . '<input type="hidden" class="mform-cl-multi-value" name="' . rex_escape($name) . '" value="' . rex_escape($value) . '">'
            . '<a href="#" class="btn btn-default mform-cl-multi-add">'
            . '<i class="rex-icon fa-plus"></i> ' . rex_escape($addLabel)
            . '</a>'
            . '</div>';
    }

    private static function wrapItem(string $widgetHtml, string $value = ''): string
    {
        return '<div class="mform-cl-multi-item" data-value="' . rex_escape($value) . '">'
            . '<span class="mform-cl-multi-handle" title="' . rex_i18n::msg('mform_cl_multi_move') . '">'
            . '<i class="rex-icon fa-bars"></i>'
            . '</span>'
            . $widgetHtml
            . '<a href="#" class="btn btn-popup mform-cl-multi-remove" title="' . rex_i18n::msg('mform_cl_multi_remove') . '">'
            . '<i class="rex-icon rex-icon-delete-link"></i>'
            . '</a>'
            . '</div>';
    }
}
