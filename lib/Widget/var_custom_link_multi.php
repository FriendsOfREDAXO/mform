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
    /**
     * REX_CUSTOM_LINK_MULTI[id=1]
     * REX_CUSTOM_LINK_MULTI[id=1 widget=1]
     * REX_CUSTOM_LINK_MULTI[id=1 widget=1 intern=1 extern=1 media=1 mailto=1]
     *
     * Storage: value column (JSON array of link strings).
     */
    protected function getOutput(): bool|string
    {
        $id = $this->getArg('id', 0, true);
        if (!in_array($this->getContext(), ['module', 'action'], true) || !is_numeric($id) || $id < 1 || $id > 10) {
            return false;
        }

        $value = $this->getContextData()->getValue('value' . $id);

        if ($this->hasArg('isset') && $this->getArg('isset')) {
            return $value ? 'true' : 'false';
        }

        if ($this->hasArg('widget') && $this->getArg('widget')) {
            if (!$this->environmentIs(self::ENV_INPUT)) {
                return false;
            }
            $args = [];
            foreach (['intern', 'extern', 'media', 'mailto', 'phone', 'anchor', 'btn_add', 'category', 'media_category', 'types', 'external_prefix', 'ylink'] as $key) {
                if ($this->hasArg($key)) {
                    $args[$key] = $this->getArg($key);
                }
            }
            $value = self::getWidget($id, 'REX_INPUT_VALUE[' . $id . ']', (string) $value, $args);
        }

        return self::quote($value);
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
