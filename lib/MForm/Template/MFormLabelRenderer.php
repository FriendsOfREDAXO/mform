<?php

namespace FriendsOfRedaxo\MForm\Template;

use FriendsOfRedaxo\MForm\DTO\MFormItem;
use rex_i18n;

class MFormLabelRenderer
{
    public static function resolveLabelValue(mixed $label): string
    {
        if (!is_array($label)) {
            return (string) ($label ?? '');
        }

        foreach ($label as $key => $itemLabel) {
            if (is_string($key) && str_contains(rex_i18n::getLocale(), $key)) {
                return is_array($itemLabel)
                    ? (string) (array_values($itemLabel)[0] ?? '')
                    : (string) $itemLabel;
            }
        }

        $first = array_values($label)[0] ?? '';
        return is_array($first) ? (string) (array_values($first)[0] ?? '') : (string) $first;
    }

    public static function resolveLabelString(MFormItem $item): string
    {
        return self::resolveLabelValue($item->getLabel());
    }

    public static function renderTooltipHtml(MFormItem $item): string
    {
        if ('' === $item->getInfoTooltip()) {
            return '';
        }

        $icon = '' !== $item->getInfoTooltipIcon() ? $item->getInfoTooltipIcon() : 'fa-info-circle';
        $iconEsc = htmlspecialchars($icon, ENT_QUOTES);
        $tooltipEsc = htmlspecialchars($item->getInfoTooltip(), ENT_QUOTES);

        return '<a href="#" class="mblock-info-tooltip" data-toggle="tooltip" title="'
            . $tooltipEsc
            . '"><i class="fa '
            . $iconEsc
            . '"></i></a>';
    }

    public static function renderLabelHtml(MFormItem $item): string
    {
        return self::resolveLabelString($item) . self::renderTooltipHtml($item);
    }
}
