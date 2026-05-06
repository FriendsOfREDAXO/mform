<?php

namespace FriendsOfRedaxo\MForm\FlexRepeater;

use FriendsOfRedaxo\MForm;
use FriendsOfRedaxo\MForm\DTO\MFormItem;

class MFormFlexRepeaterRenderer
{
    public static function renderTemplate(MForm $form, int $level = 1): string
    {
        $items = array_values($form->getItems());
        $html = '';
        $i = 0;

        while ($i < count($items)) {
            $item = $items[$i];

            if ($item instanceof MForm) {
                $html .= self::renderTemplate($item, $level);
                $i++;
                continue;
            }

            if (!($item instanceof MFormItem)) {
                $i++;
                continue;
            }

            $type = $item->getType();

            if ('repeater' === $type && 1 === $level) {
                $innerForm = (isset($items[$i + 1]) && $items[$i + 1] instanceof MForm) ? $items[$i + 1] : null;
                $fieldKey = self::extractFieldKey($item->getVarId());
                $attrs = $item->getAttributes();
                $btnText = (string) ($attrs['btn_text'] ?? 'Hinzufügen');
                $label = self::getLabelString($item->getLabel());

                $html .= self::renderNestedRepeaterContainer($fieldKey, $label, $btnText, $innerForm);

                $skip = $i + 1;
                while ($skip < count($items)) {
                    if ($items[$skip] instanceof MFormItem && 'close-repeater' === $items[$skip]->getType()) {
                        $i = $skip + 1;
                        break;
                    }
                    $skip++;
                }
                if ($skip >= count($items)) {
                    $i = $skip;
                }
                continue;
            }

            if ('close-repeater' === $type) {
                $i++;
                continue;
            }

            $html .= self::renderField($item);
            $i++;
        }

        return $html;
    }

    private static function renderField(MFormItem $item): string
    {
        $type = $item->getType();
        $fieldKey = self::extractFieldKey($item->getVarId());
        $label = self::renderLabel($item);
        $attrs = self::renderAttributes($item->getAttributes());
        $class = htmlspecialchars($item->getClass(), ENT_QUOTES);
        $key = htmlspecialchars($fieldKey, ENT_QUOTES);

        switch ($type) {
            case 'text':
            case 'email':
            case 'url':
            case 'tel':
            case 'number':
            case 'color':
            case 'date':
            case 'time':
            case 'datetime-local':
            case 'month':
            case 'week':
            case 'search':
            case 'range':
                return self::wrapFormGroup(
                    $label,
                    sprintf('<input type="%s" class="form-control %s" data-mfr-field="%s" value=""%s>', htmlspecialchars($type, ENT_QUOTES), $class, $key, $attrs),
                    $item
                );

            case 'textarea':
                return self::wrapFormGroup(
                    $label,
                    sprintf('<textarea class="form-control %s" data-mfr-field="%s" rows="3"%s></textarea>', $class, $key, $attrs),
                    $item
                );

            case 'hidden':
                return sprintf('<input type="hidden" data-mfr-field="%s" value="">', $key);

            case 'select':
                return self::wrapFormGroup(
                    $label,
                    sprintf('<select class="form-control %s" data-mfr-field="%s"%s>%s</select>', $class, $key, $attrs, self::renderSelectOptions($item->getOptions())),
                    $item
                );

            case 'multiselect':
                return self::wrapFormGroup(
                    $label,
                    sprintf('<select class="form-control %s" data-mfr-field="%s" multiple="multiple"%s>%s</select>', $class, $key, $attrs, self::renderSelectOptions($item->getOptions())),
                    $item
                );

            case 'radio':
                return self::wrapFormGroup($label, self::renderRadioGroup($item, $fieldKey), $item);

            case 'checkbox':
            case 'multicheckbox':
                return self::wrapFormGroup($label, self::renderCheckboxGroup($item, $fieldKey), $item);

            case 'link':
            case 'custom-link':
            case 'media':
            case 'imagelist':
            case 'custom-link-multi':
                return self::wrapFormGroup($label, self::renderUnsupportedWidgetPlaceholder($type), $item);

                return self::wrapFormGroup(
                    $label,
                    sprintf('<input type="text" class="form-control %s" data-mfr-field="%s" value=""%s>', $class, $key, $attrs),
                    $item
                );

            case 'medialist':
                return self::wrapFormGroup($label, self::renderListWidget('medialist', $key, $item), $item);

            case 'linklist':
                return self::wrapFormGroup($label, self::renderListWidget('linklist', $key, $item), $item);

            case 'headline':
                $val = is_string($item->getValue()) ? $item->getValue() : '';
                return sprintf('<div class="mfr-template-headline"><h4>%s</h4></div>', htmlspecialchars($val, ENT_QUOTES));

            case 'description':
                $val = is_string($item->getValue()) ? $item->getValue() : '';
                return sprintf('<div class="mfr-template-description"><p>%s</p></div>', htmlspecialchars($val, ENT_QUOTES));

            case 'html':
                return is_string($item->getValue()) ? $item->getValue() : '';

            default:
                return '';
        }
    }

    private static function renderNestedRepeaterContainer(string $fieldKey, string $label, string $btnText, ?MForm $innerForm): string
    {
        $innerTemplate = '';
        if (null !== $innerForm) {
            $innerTemplate = self::renderTemplate($innerForm, 2);
        }

        $labelHtml = '';
        if ('' !== $label) {
            $labelHtml = sprintf('<div class="mfr-nested-label">%s</div>', htmlspecialchars($label, ENT_QUOTES));
        }

        return sprintf(
            '<div class="mfr-nested-repeater" data-mfr-field="%s" data-mfr-level="2">%s'
            . '<div class="mfr-nested-items"></div>'
            . '<button type="button" class="btn btn-default btn-sm mfr-btn-add-nested"><i class="rex-icon fa-plus-circle"></i> %s</button>'
            . '<template class="mfr-nested-template">'
            . '<div class="mfr-nested-item">'
            . '<div class="mfr-nested-header">'
            . '<span class="mfr-nested-drag" title="Verschieben"><i class="rex-icon fa-bars"></i></span>'
            . '<span class="mfr-nested-title"></span>'
            . '<div class="mfr-nested-actions">'
            . '<button type="button" class="btn btn-xs mfr-btn-nested-up" title="Nach oben"><i class="rex-icon fa-chevron-up"></i></button>'
            . '<button type="button" class="btn btn-xs mfr-btn-nested-down" title="Nach unten"><i class="rex-icon fa-chevron-down"></i></button>'
            . '<button type="button" class="btn btn-xs mfr-btn-nested-add-after" title="Hinzufuegen nach diesem Element"><i class="rex-icon fa-plus"></i></button>'
            . '<button type="button" class="btn btn-xs mfr-btn-nested-toggle" title="Aufklappen/Zuklappen"><i class="rex-icon fa-square-o"></i></button>'
            . '<button type="button" class="btn btn-xs btn-danger mfr-btn-nested-remove" title="Entfernen"><i class="rex-icon fa-trash"></i></button>'
            . '</div></div>'
            . '<div class="mfr-nested-body" style="display:none">%s</div>'
            . '</div></template></div>',
            htmlspecialchars($fieldKey, ENT_QUOTES),
            $labelHtml,
            htmlspecialchars($btnText, ENT_QUOTES),
            $innerTemplate
        );
    }

    private static function getLabelString(mixed $label): string
    {
        if (is_array($label)) {
            return (string) (reset($label) ?: '');
        }
        return (string) ($label ?? '');
    }

    private static function renderLabel(MFormItem $item): string
    {
        $label = self::getLabelString($item->getLabel());
        if ('' === $label) {
            return '';
        }
        return sprintf('<label class="control-label">%s</label>', htmlspecialchars($label, ENT_QUOTES));
    }

    private static function wrapFormGroup(string $label, string $field, MFormItem $item): string
    {
        $notice = $item->getNotice();
        $noticeHtml = '';
        if ('' !== $notice) {
            $noticeHtml = sprintf('<p class="help-block">%s</p>', htmlspecialchars($notice, ENT_QUOTES));
        }
        return sprintf('<div class="form-group mfr-field-group">%s%s%s</div>', $label, $field, $noticeHtml);
    }

    private static function renderSelectOptions(array $options): string
    {
        $html = '';
        foreach ($options as $key => $value) {
            if (is_array($value)) {
                $groupOptions = '';
                foreach ($value as $vKey => $vValue) {
                    $groupOptions .= sprintf('<option value="%s">%s</option>', htmlspecialchars((string) $vKey, ENT_QUOTES), htmlspecialchars((string) $vValue, ENT_QUOTES));
                }
                $html .= sprintf('<optgroup label="%s">%s</optgroup>', htmlspecialchars((string) $key, ENT_QUOTES), $groupOptions);
            } else {
                $html .= sprintf('<option value="%s">%s</option>', htmlspecialchars((string) $key, ENT_QUOTES), htmlspecialchars((string) $value, ENT_QUOTES));
            }
        }
        return $html;
    }

    private static function renderUnsupportedWidgetPlaceholder(string $type): string
    {
        return sprintf(
            '<div class="alert alert-warning">%s</div>',
            htmlspecialchars('Widget-Typ "' . $type . '" wird im Flex-Repeater derzeit nicht unterstuetzt.', ENT_QUOTES)
        );
    }

    private static function renderRadioGroup(MFormItem $item, string $fieldKey): string
    {
        $html = '';
        foreach ($item->getOptions() as $key => $value) {
            $html .= sprintf('<label class="radio-inline"><input type="radio" data-mfr-field="%s" value="%s"> %s</label>', htmlspecialchars($fieldKey, ENT_QUOTES), htmlspecialchars((string) $key, ENT_QUOTES), htmlspecialchars((string) $value, ENT_QUOTES));
        }
        return $html;
    }

    private static function renderCheckboxGroup(MFormItem $item, string $fieldKey): string
    {
        $options = $item->getOptions();
        if (1 === count($options)) {
            $key = (string) array_key_first($options);
            $value = reset($options);
            return sprintf('<label><input type="checkbox" data-mfr-field="%s" value="%s"> %s</label>', htmlspecialchars($fieldKey, ENT_QUOTES), htmlspecialchars($key, ENT_QUOTES), htmlspecialchars((string) $value, ENT_QUOTES));
        }

        $html = '';
        foreach ($options as $key => $value) {
            $html .= sprintf('<label class="checkbox-inline"><input type="checkbox" data-mfr-field="%s" value="%s"> %s</label>', htmlspecialchars($fieldKey, ENT_QUOTES), htmlspecialchars((string) $key, ENT_QUOTES), htmlspecialchars((string) $value, ENT_QUOTES));
        }
        return $html;
    }

    private static function renderAttributes(array $attributes): string
    {
        static $skipKeys = ['id', 'name', 'type', 'value', 'checked', 'selected', 'data-mfr-field', 'label', 'open', 'collapsed', 'first_open', 'show_toggle_all', 'btn_text', 'btn_class', 'confirm_delete', 'confirm_delete_msg', 'min', 'max', 'default_count', 'groups', 'group', 'repeater_id', 'parent_id'];

        $html = '';
        foreach ($attributes as $key => $value) {
            if (in_array($key, $skipKeys, true)) {
                continue;
            }
            if (is_array($value)) {
                continue;
            }
            $html .= sprintf(' %s="%s"', htmlspecialchars((string) $key, ENT_QUOTES), htmlspecialchars((string) $value, ENT_QUOTES));
        }
        return $html;
    }

    private static function renderListWidget(string $type, string $fieldKey, MFormItem $item): string
    {
        $attrs = $item->getAttributes();
        $params = '';

        if ('medialist' === $type) {
            if (isset($attrs['data-media-category']) && '' !== (string) $attrs['data-media-category']) {
                $params .= '&rex_file_category=' . rawurlencode((string) $attrs['data-media-category']);
            }
            if (isset($attrs['data-media-type']) && '' !== (string) $attrs['data-media-type']) {
                $params .= '&args[types]=' . rawurlencode((string) $attrs['data-media-type']);
            }
        }

        if ('linklist' === $type) {
            if (isset($attrs['data-link-category']) && '' !== (string) $attrs['data-link-category']) {
                $params .= '&category_id=' . rawurlencode((string) $attrs['data-link-category']);
            }
        }

        $buttons = '';
        if ('medialist' === $type) {
            $buttons = '<a href="#" class="btn btn-popup mform-list-btn" data-action="open" title="Mediapool oeffnen"><i class="rex-icon rex-icon-open-mediapool"></i></a>'
                . '<a href="#" class="btn btn-popup mform-list-btn" data-action="add" title="Mediapool hinzufuegen"><i class="rex-icon rex-icon-add-media"></i></a>'
                . '<a href="#" class="btn btn-popup mform-list-btn" data-action="view" title="Ausgewaehlte Datei ansehen"><i class="rex-icon rex-icon-view-media"></i></a>'
                . '<a href="#" class="btn btn-popup mform-list-btn" data-action="up" title="Nach oben"><i class="rex-icon rex-icon-up"></i></a>'
                . '<a href="#" class="btn btn-popup mform-list-btn" data-action="down" title="Nach unten"><i class="rex-icon rex-icon-down"></i></a>'
                . '<a href="#" class="btn btn-popup mform-list-btn" data-action="delete" title="Entfernen"><i class="rex-icon rex-icon-delete-media"></i></a>';
        } else {
            $buttons = '<a href="#" class="btn btn-popup mform-list-btn" data-action="open" title="Linkmap oeffnen"><i class="rex-icon rex-icon-open-linkmap"></i></a>'
                . '<a href="#" class="btn btn-popup mform-list-btn" data-action="up" title="Nach oben"><i class="rex-icon rex-icon-up"></i></a>'
                . '<a href="#" class="btn btn-popup mform-list-btn" data-action="down" title="Nach unten"><i class="rex-icon rex-icon-down"></i></a>'
                . '<a href="#" class="btn btn-popup mform-list-btn" data-action="delete" title="Entfernen"><i class="rex-icon rex-icon-delete-link"></i></a>';
        }

        return '<div class="rex-js-widget mform-list-widget mform-list-widget-' . htmlspecialchars($type, ENT_QUOTES) . '" data-widget-type="' . htmlspecialchars($type, ENT_QUOTES) . '" data-params="' . htmlspecialchars($params, ENT_QUOTES) . '">'
            . '<div class="mform-list-shell">'
            . '<ul class="mform-list-items"></ul>'
            . '<select class="form-control mform-list-select" size="10"></select>'
            . '<input type="hidden" class="mform-list-value" data-mfr-field="' . htmlspecialchars($fieldKey, ENT_QUOTES) . '" value="">'
            . '</div>'
            . '<div class="mform-list-toolbar">' . $buttons . '</div>'
            . '</div>';
    }

    public static function extractFieldKey(mixed $varId): string
    {
        if (is_array($varId)) {
            return implode('_', $varId);
        }

        $varId = (string) $varId;
        $varId = trim($varId, '[]');
        $varId = str_replace(['][', '[', ']'], ['_', '_', ''], $varId);
        return trim($varId, '_');
    }
}
