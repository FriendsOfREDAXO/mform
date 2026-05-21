<?php

namespace FriendsOfRedaxo\MForm\FlexRepeater;

use FriendsOfRedaxo\MForm;
use FriendsOfRedaxo\MForm\DTO\MFormItem;
use FriendsOfRedaxo\MForm\Utils\MFormGroupExtensionHelper;
use rex_var_custom_link;
use rex_var_custom_link_multi;
use rex_var_custom_medialist;

use function array_key_exists;
use function count;
use function in_array;
use function is_array;
use function is_bool;
use function is_string;
use function sprintf;

use const ENT_QUOTES;

class MFormFlexRepeaterRenderer
{
    public static function renderTemplate(MForm $form, int $level = 1): string
    {
        $items = array_values($form->getItems());
        if (self::needsTabAutoGrouping($items)) {
            $items = array_values(MFormGroupExtensionHelper::addTabGroupExtensionItems($items));
        }
        $html = '';
        $i = 0;

        while ($i < count($items)) {
            $item = $items[$i];

            if ($item instanceof MForm) {
                $html .= self::renderTemplate($item, $level);
                ++$i;
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
                    ++$skip;
                }
                if ($skip >= count($items)) {
                    $i = $skip;
                }
                continue;
            }

            if ('close-repeater' === $type) {
                ++$i;
                continue;
            }

            // MODAL: collect all items until close-modal and render as block
            if ('modal' === $type) {
                $modalLabel = self::getLabelString($item->getLabel());
                $attrs = $item->getAttributes();
                $btnClass = 'btn ' . (isset($attrs['data-modal-btn-class']) ? htmlspecialchars($attrs['data-modal-btn-class'], ENT_QUOTES) : 'btn-default');
                $align = $attrs['data-modal-align'] ?? 'left';
                $innerHtml = '';
                ++$i;
                while ($i < count($items)) {
                    $inner = $items[$i];
                    if ($inner instanceof MFormItem && 'close-modal' === $inner->getType()) {
                        ++$i;
                        break;
                    }
                    if ($inner instanceof MForm) {
                        $innerHtml .= self::renderTemplate($inner, $level);
                    } elseif ($inner instanceof MFormItem) {
                        $innerHtml .= self::renderField($inner);
                    }
                    ++$i;
                }
                $html .= self::renderModalBlock($modalLabel, $btnClass, $align, $innerHtml);
                continue;
            }

            if ('close-modal' === $type) {
                ++$i;
                continue;
            }

            // FIELDSET: <fieldset><legend>..</legend>..inner..</fieldset>
            if ('fieldset' === $type) {
                $attrs = $item->getAttributes();
                // Legend kommt aus addFieldsetArea(...) und wird vom AttributeHandler
                // ueber setLegend() auf das Item gesetzt (nicht in attributes['legend']).
                $legendStr = (string) $item->getLegend();
                $legend = '' !== $legendStr
                    ? '<legend>' . $legendStr . '</legend>' // Legend ist Entwickler-HTML
                    : '';
                $cls = htmlspecialchars($item->getClass(), ENT_QUOTES);
                $attrHtml = self::renderAttributes($attrs);
                $html .= sprintf('<fieldset class="%s"%s>%s', $cls, $attrHtml, $legend);
                ++$i;
                continue;
            }
            if ('close-fieldset' === $type) {
                $html .= '</fieldset>';
                ++$i;
                continue;
            }

            // COLLAPSE: Toggle-Button + .collapse-Wrapper (kompatibel mit assets/mform.js initMFormCollapses)
            if ('collapse' === $type) {
                $attrs = $item->getAttributes();
                $labelStr = self::getLabelString($item->getLabel()); // Entwickler-HTML, nicht escapen
                $hideToggleLinks = isset($attrs['data-group-hide-toggle-links']) && 'true' === (string) $attrs['data-group-hide-toggle-links'];
                $openCollapse = isset($attrs['data-group-open-collapse']) && (1 === (int) $attrs['data-group-open-collapse'] || true === $attrs['data-group-open-collapse']);
                $isAccordion = isset($attrs['data-group-accordion']) && 1 === (int) $attrs['data-group-accordion'];
                $btnAttrs = ' data-toggle="collapse"';
                if (!$isAccordion) {
                    $btnAttrs .= ' data-collapse-open="' . ($openCollapse ? 1 : 0) . '"';
                }
                $btnAttrs .= ' aria-expanded="' . ($openCollapse ? 'true' : 'false') . '"';
                $btnHidden = ('' === $labelStr || $hideToggleLinks) ? ' hidden' : '';
                $btnHtml = sprintf('<a class="btn btn-white btn-block%s"%s>%s</a>', $btnHidden, $btnAttrs, $labelStr);

                // Wrapper-Attribute bereinigen wie im Fragment
                $wrapperAttrs = $attrs;
                unset($wrapperAttrs['data-group-hide-toggle-links'], $wrapperAttrs['data-group-accordion'], $wrapperAttrs['data-group-open-collapse']);
                $cls = trim('collapse ' . $item->getClass() . ($openCollapse ? ' in' : ''));
                $wrapperAttrHtml = self::renderAttributes($wrapperAttrs);
                $html .= $btnHtml . sprintf('<div class="%s"%s>', htmlspecialchars($cls, ENT_QUOTES), $wrapperAttrHtml);
                ++$i;
                continue;
            }
            if ('close-collapse' === $type) {
                $html .= '</div>';
                ++$i;
                continue;
            }

            // COLLAPSE-GROUP: Wrapper fuer mehrere Collapses, ermoeglicht Standalone-Toggle
            // ueber initMFormLinkCollapse (Klick auf Button toggelt naechsten .collapse).
            if ('start-group-collapse' === $type) {
                $attrs = $item->getAttributes();
                if (!isset($attrs['data-group-accordion'])) {
                    $attrs['data-group-accordion'] = 0;
                }
                unset($attrs['data-group-collapse-id']);
                $cls = trim('collapse-group ' . $item->getClass());
                $html .= sprintf('<div class="%s"%s>', htmlspecialchars($cls, ENT_QUOTES), self::renderAttributes($attrs));
                ++$i;
                continue;
            }
            if ('close-group-collapse' === $type) {
                $html .= '</div>';
                ++$i;
                continue;
            }

            // COLUMN-GROUP: Bootstrap-3 row + col-* divs
            if ('start-group-column' === $type) {
                $cls = trim('row ' . $item->getClass());
                $html .= sprintf('<div class="%s"%s>', htmlspecialchars($cls, ENT_QUOTES), self::renderAttributes($item->getAttributes()));
                ++$i;
                continue;
            }
            if ('column' === $type) {
                $cls = $item->getClass();
                $html .= sprintf('<div class="%s"%s>', htmlspecialchars($cls, ENT_QUOTES), self::renderAttributes($item->getAttributes()));
                ++$i;
                continue;
            }
            if ('close-column' === $type || 'close-group-column' === $type) {
                $html .= '</div>';
                ++$i;
                continue;
            }

            // INLINE-GROUP: form-inline Wrapper
            if ('start-group-inline' === $type) {
                $cls = trim('form-inline ' . $item->getClass());
                $html .= sprintf('<div class="%s"%s>', htmlspecialchars($cls, ENT_QUOTES), self::renderAttributes($item->getAttributes()));
                ++$i;
                continue;
            }
            if ('inline' === $type) {
                $cls = trim('form-inline mfr-inline ' . $item->getClass());
                $html .= sprintf('<div class="%s"%s>', htmlspecialchars($cls, ENT_QUOTES), self::renderAttributes($item->getAttributes()));
                ++$i;
                continue;
            }
            if ('close-inline' === $type || 'close-group-inline' === $type) {
                $html .= '</div>';
                ++$i;
                continue;
            }

            // TAB-GROUP: ID-freie Tabs, damit verschachtelte/gekloente Kontexte
            // (z. B. Repeater) ohne eindeutige DOM-IDs stabil funktionieren.
            if ('start-group-tab' === $type) {
                $tabsMeta = self::collectTabsForGroup($items, $i);
                $navHtml = '';
                foreach ($tabsMeta as $idx => $meta) {
                    $tabIcon = isset($meta['attrs']['tab-icon']) ? '<i class="rex-icon ' . htmlspecialchars((string) $meta['attrs']['tab-icon'], ENT_QUOTES) . '"></i> ' : '';
                    $navClass = trim(
                        ((isset($meta['attrs']['nav-class'])) ? (string) $meta['attrs']['nav-class'] . ' ' : '')
                        . ((isset($meta['attrs']['pull-right']) && true === $meta['attrs']['pull-right']) ? 'pull-right ' : '')
                        . ((isset($meta['attrs']['data-group-open-tab']) && true === $meta['attrs']['data-group-open-tab']) ? 'active' : ''),
                    );
                    $navHtml .= sprintf(
                        '<li role="presentation" class="%s" data-tab-nav-item="%d"><a href="#" role="tab" aria-selected="false" data-mform-tab-toggle="1" data-tab-item="%d">%s%s</a></li>',
                        htmlspecialchars($navClass, ENT_QUOTES),
                        $idx,
                        $idx,
                        $tabIcon,
                        $meta['label'], // Label ist Entwickler-HTML
                    );
                }
                $groupAttributes = $item->getAttributes();
                $layout = strtolower(trim((string) ($groupAttributes['data-group-tab-layout'] ?? '')));
                $style = strtolower(trim((string) ($groupAttributes['data-group-tab-style'] ?? '')));

                $cls = trim('nav mform-tabs rex-page-nav ' . $item->getClass());
                if (in_array($layout, ['vertical', 'left', 'nav-left'], true)) {
                    $cls .= ' mform-tabs--vertical';
                }
                if ('modern' === $style) {
                    $cls .= ' mform-tabs--modern';
                }

                $html .= sprintf(
                    '<div class="%s" data-mform-tabs="1"%s><ul class="nav nav-tabs" role="tablist">%s</ul><div class="tab-content">',
                    htmlspecialchars($cls, ENT_QUOTES),
                    self::renderAttributes($item->getAttributes()),
                    $navHtml,
                );
                ++$i;
                continue;
            }
            if ('tab' === $type) {
                $tabIdx = self::tabIndexInGroup($items, $i);
                $attrs = $item->getAttributes();
                $isActive = isset($attrs['data-group-open-tab']) && true === $attrs['data-group-open-tab'];
                unset($attrs['tab-icon'], $attrs['nav-class'], $attrs['pull-right'], $attrs['data-group-open-tab'], $attrs['data-group-tab-layout'], $attrs['data-group-tab-style']);
                $cls = trim('tab-pane ' . $item->getClass() . ($isActive ? ' active' : ''));
                $html .= sprintf(
                    '<div role="tabpanel" class="%s" data-tab-group-nav-tab-id="%d"%s>',
                    htmlspecialchars($cls, ENT_QUOTES),
                    $tabIdx,
                    self::renderAttributes($attrs),
                );
                ++$i;
                continue;
            }
            if ('close-tab' === $type) {
                $html .= '</div>';
                ++$i;
                continue;
            }
            if ('close-group-tab' === $type) {
                $html .= '</div></div>';
                ++$i;
                continue;
            }

            $html .= self::renderField($item);
            ++$i;
        }

        return $html;
    }

    /**
     * @param array<int, MFormItem|MForm> $items
     */
    private static function needsTabAutoGrouping(array $items): bool
    {
        $hasTab = false;
        foreach ($items as $item) {
            if (!$item instanceof MFormItem) {
                continue;
            }

            $type = $item->getType();
            if ('start-group-tab' === $type) {
                return false;
            }
            if ('tab' === $type) {
                $hasTab = true;
            }
        }

        return $hasTab;
    }

    /**
     * Sammelt fuer ein <start-group-tab> alle direkt darin enthaltenen <tab>-Items
     * inkl. Label und Attributen, um die Tab-Navigation aufzubauen.
     *
     * @param array<int, MFormItem|MForm> $items
     * @return list<array{label: string, attrs: array<string, mixed>}>
     */
    private static function collectTabsForGroup(array $items, int $startIdx): array
    {
        $tabs = [];
        $depth = 0;
        $count = count($items);
        for ($j = $startIdx + 1; $j < $count; ++$j) {
            $it = $items[$j];
            if (!$it instanceof MFormItem) {
                continue;
            }
            $t = $it->getType();
            if ('start-group-tab' === $t) {
                ++$depth;
                continue;
            }
            if ('close-group-tab' === $t) {
                if (0 === $depth) {
                    return $tabs;
                }
                --$depth;
                continue;
            }
            if (0 === $depth && 'tab' === $t) {
                $tabs[] = [
                    'label' => self::getLabelString($it->getLabel()),
                    'attrs' => $it->getAttributes(),
                ];
            }
        }
        return $tabs;
    }

    /**
     * Findet den 0-basierten Index eines tab-Items innerhalb seiner Tab-Gruppe.
     *
     * @param array<int, MFormItem|MForm> $items
     */
    private static function tabIndexInGroup(array $items, int $tabIdx): int
    {
        // Rueckwaerts zum start-group-tab laufen (auf gleicher Verschachtelungsebene)
        $depth = 0;
        $start = -1;
        for ($j = $tabIdx - 1; $j >= 0; --$j) {
            $it = $items[$j];
            if (!$it instanceof MFormItem) {
                continue;
            }
            $t = $it->getType();
            if ('close-group-tab' === $t) {
                ++$depth;
            } elseif ('start-group-tab' === $t) {
                if (0 === $depth) {
                    $start = $j;
                    break;
                }
                --$depth;
            }
        }
        if ($start < 0) {
            return 0;
        }
        $idx = 0;
        $depth = 0;
        for ($j = $start + 1; $j < $tabIdx; ++$j) {
            $it = $items[$j];
            if (!$it instanceof MFormItem) {
                continue;
            }
            $t = $it->getType();
            if ('start-group-tab' === $t) {
                ++$depth;
            } elseif ('close-group-tab' === $t) {
                --$depth;
            } elseif (0 === $depth && 'tab' === $t) {
                ++$idx;
            }
        }
        return $idx;
    }

    private static function renderField(MFormItem $item): string
    {
        $type = $item->getType();
        $fieldKey = self::extractFieldKey($item->getVarId());
        $label = self::renderLabel($item);
        $itemAttributes = $item->getAttributes();
        $toggleOptions = $item->getToggleOptions();
        // Wenn ToggleOptions definiert sind, automatisch data-toggle="collapse" auf select setzen
        // (analog zum klassischen MFormParser-Pfad ausserhalb des Repeaters).
        if (count($toggleOptions) > 0 && in_array($type, ['select', 'multiselect'], true) && !isset($itemAttributes['data-toggle'])) {
            $itemAttributes = array_merge(['data-toggle' => 'collapse'], $itemAttributes);
        }
        $attrs = self::renderAttributes($itemAttributes);
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
                    $item,
                );

            case 'textarea':
                return self::wrapFormGroup(
                    $label,
                    sprintf('<textarea class="form-control %s" data-mfr-field="%s" rows="3"%s></textarea>', $class, $key, $attrs),
                    $item,
                );

            case 'hidden':
                return sprintf('<input type="hidden" data-mfr-field="%s" value="">', $key);

            case 'select':
                return self::wrapFormGroup(
                    $label,
                    sprintf('<select class="form-control %s" data-mfr-field="%s"%s>%s</select>', $class, $key, $attrs, self::renderSelectOptions($item->getOptions(), $toggleOptions)),
                    $item,
                );

            case 'multiselect':
                return self::wrapFormGroup(
                    $label,
                    sprintf('<select class="form-control %s" data-mfr-field="%s" multiple="multiple"%s>%s</select>', $class, $key, $attrs, self::renderSelectOptions($item->getOptions(), $toggleOptions)),
                    $item,
                );

            case 'radio':
                return self::wrapFormGroup($label, self::renderRadioGroup($item, $fieldKey), $item);

            case 'checkbox':
            case 'multicheckbox':
                return self::wrapFormGroup($label, self::renderCheckboxGroup($item, $fieldKey), $item);

            case 'checkbox-group':
                return self::wrapFormGroup($label, self::renderCheckboxGroupWidget($item, $fieldKey), $item);

            case 'color-swatch':
                return self::wrapFormGroup($label, self::renderColorSwatchWidget($item, $fieldKey), $item);

            case 'link':
            case 'custom-link':
            case 'media':
            case 'mform-link':
            case 'mform-media':
                return self::wrapFormGroup($label, self::renderCustomLinkWidget($type, $fieldKey, $item), $item);

            case 'custom-link-multi':
                return self::wrapFormGroup($label, self::renderCustomLinkMultiWidget($fieldKey, $item), $item);

            case 'medialist':
                return self::wrapFormGroup($label, self::renderListWidget('medialist', $key, $item), $item);

            case 'imglist':
            case 'imagelist':
                return self::wrapFormGroup($label, self::renderListWidget('imglist', $key, $item), $item);

            case 'linklist':
                return self::wrapFormGroup($label, self::renderListWidget('linklist', $key, $item), $item);

            case 'text-readonly':
                return self::wrapFormGroup(
                    $label,
                    sprintf('<input type="text" class="form-control %s" data-mfr-field="%s" value=""%s readonly>', $class, $key, $attrs),
                    $item,
                );

            case 'textarea-readonly':
                return self::wrapFormGroup(
                    $label,
                    sprintf('<textarea class="form-control %s" data-mfr-field="%s" rows="3"%s readonly></textarea>', $class, $key, $attrs),
                    $item,
                );

            case 'headline':
                $val = is_string($item->getValue()) ? $item->getValue() : '';
                return sprintf('<div class="mfr-template-headline"><h4>%s</h4></div>', htmlspecialchars($val, ENT_QUOTES));

            case 'description':
                $val = is_string($item->getValue()) ? $item->getValue() : '';
                return sprintf('<div class="mfr-template-description"><p>%s</p></div>', htmlspecialchars($val, ENT_QUOTES));

            case 'html':
                return is_string($item->getValue()) ? $item->getValue() : '';

            case 'alert':
                $val = is_string($item->getValue()) ? $item->getValue() : '';
                $attrClass = $item->getAttributes()['class'] ?? ($item->getClass() ?: 'alert-info');
                return sprintf('<div class="alert %s" style="margin-bottom:4px">%s</div>', htmlspecialchars($attrClass, ENT_QUOTES), $val);

            default:
                return '';
        }
    }

    private static function renderModalBlock(string $label, string $btnClass, string $align, string $innerHtml): string
    {
        $alignClass = match ($align) {
            'center' => 'text-center',
            'right' => 'text-right',
            default => 'text-left',
        };
        // __MFRID__ is replaced by a unique ID in JS (_renderItem) when the template is cloned
        return '<div class="form-group mfr-modal-wrapper">' .
            '<div class="col-sm-12 ' . $alignClass . '">' .
            '<button type="button" class="' . htmlspecialchars($btnClass, ENT_QUOTES) . ' mfr-modal-btn"' .
            ' data-toggle="modal" data-target="#__MFRID__">' .
            '<i class="fa fa-cog"></i> ' . $label . '</button>' .
            '</div></div>' .
            '<div class="modal fade mfr-modal" id="__MFRID__" tabindex="-1" role="dialog">' .
            '<div class="modal-dialog" role="document"><div class="modal-content">' .
            '<div class="modal-header">' .
            '<button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>' .
            '<h4 class="modal-title">' . $label . '</h4>' .
            '</div>' .
            '<div class="modal-body" style="padding: 15px 30px"><div class="mform form-horizontal">' . $innerHtml . '</div></div>' .
            '<div class="modal-footer">' .
            '<button type="button" class="btn btn-primary" data-dismiss="modal">Übernehmen</button>' .
            '</div>' .
            '</div></div></div>';
    }

    private static function renderNestedRepeaterContainer(string $fieldKey, string $label, string $btnText, ?MForm $innerForm): string
    {
        $innerTemplate = '';
        if (null !== $innerForm) {
            $innerTemplate = self::renderTemplate($innerForm, 2);
        }

        $labelHtml = '';
        if ('' !== $label) {
            // Labels sind Entwickler-kontrolliert und duerfen HTML enthalten (z. B. Icons).
            $labelHtml = sprintf('<div class="mfr-nested-label">%s</div>', $label);
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
            . '<div class="mfr-nested-body mform form-horizontal" style="display:none">%s</div>'
            . '</div></template></div>',
            htmlspecialchars($fieldKey, ENT_QUOTES),
            $labelHtml,
            htmlspecialchars($btnText, ENT_QUOTES),
            $innerTemplate,
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
        // Labels sind Entwickler-kontrolliert und duerfen HTML enthalten (z. B. FontAwesome-Icons),
        // wie auch im klassischen MFormParser-Pfad ausserhalb des Repeaters.
        return $label;
    }

    private static function wrapFormGroup(string $label, string $field, MFormItem $item): string
    {
        $notice = $item->getNotice();
        $noticeHtml = '';
        if ('' !== $notice) {
            $noticeHtml = sprintf('<p class="help-block">%s</p>', htmlspecialchars($notice, ENT_QUOTES));
        }
        // Bootstrap-3 form-horizontal Markup (col-sm-3 / col-sm-9). Die Layout-Variante
        // (vertical/inline) wird per CSS via [data-mfr-layout] auf dem .mfr-container
        // ueberschrieben.
        if ('' === $label) {
            return sprintf(
                '<div class="form-group mfr-field-group"><div class="col-sm-12 mfr-field-col">%s%s</div></div>',
                $field,
                $noticeHtml,
            );
        }
        return sprintf(
            '<div class="form-group mfr-field-group"><label class="control-label col-sm-3 mfr-field-label">%s</label><div class="col-sm-9 mfr-field-col">%s%s</div></div>',
            $label,
            $field,
            $noticeHtml,
        );
    }

    /**
     * @param array<mixed> $options
     * @param array<mixed> $toggleOptions Map optionKey => collapseId (oder [collapseId, alpineExpr])
     */
    private static function renderSelectOptions(array $options, array $toggleOptions = []): string
    {
        $html = '';
        foreach ($options as $key => $value) {
            if (is_array($value)) {
                $groupOptions = '';
                foreach ($value as $vKey => $vValue) {
                    $toggleAttr = self::buildToggleItemAttr($vKey, $toggleOptions);
                    $groupOptions .= sprintf('<option value="%s"%s>%s</option>', htmlspecialchars((string) $vKey, ENT_QUOTES), $toggleAttr, htmlspecialchars((string) $vValue, ENT_QUOTES));
                }
                $html .= sprintf('<optgroup label="%s">%s</optgroup>', htmlspecialchars((string) $key, ENT_QUOTES), $groupOptions);
            } else {
                $toggleAttr = self::buildToggleItemAttr($key, $toggleOptions);
                $html .= sprintf('<option value="%s"%s>%s</option>', htmlspecialchars((string) $key, ENT_QUOTES), $toggleAttr, htmlspecialchars((string) $value, ENT_QUOTES));
            }
        }
        return $html;
    }

    /**
     * @param array<mixed> $toggleOptions
     */
    private static function buildToggleItemAttr(int|string $key, array $toggleOptions): string
    {
        if (!array_key_exists($key, $toggleOptions)) {
            return '';
        }
        $val = $toggleOptions[$key];
        if (is_array($val)) {
            $val = isset($val[0]) ? (string) $val[0] : '';
        } else {
            $val = (string) $val;
        }
        if ('' === $val) {
            return '';
        }
        return ' data-toggle-item="' . htmlspecialchars($val, ENT_QUOTES) . '"';
    }

    private static function renderRadioGroup(MFormItem $item, string $fieldKey): string
    {
        $html = '<div class="mfr-radio-group">';
        foreach ($item->getOptions() as $key => $value) {
            $html .= sprintf('<div class="radio"><label><input type="radio" data-mfr-field="%s" value="%s"> %s</label></div>', htmlspecialchars($fieldKey, ENT_QUOTES), htmlspecialchars((string) $key, ENT_QUOTES), htmlspecialchars((string) $value, ENT_QUOTES));
        }
        $html .= '</div>';
        return $html;
    }

    private static function renderCheckboxGroup(MFormItem $item, string $fieldKey): string
    {
        $options = $item->getOptions();
        if (1 === count($options)) {
            $key = (string) array_key_first($options);
            $value = reset($options);
            return sprintf('<div class="checkbox"><label><input type="checkbox" data-mfr-field="%s" value="%s"> %s</label></div>', htmlspecialchars($fieldKey, ENT_QUOTES), htmlspecialchars($key, ENT_QUOTES), htmlspecialchars((string) $value, ENT_QUOTES));
        }

        $html = '<div class="mfr-checkbox-inline-group">';
        foreach ($options as $key => $value) {
            $html .= sprintf('<label class="checkbox-inline"><input type="checkbox" data-mfr-field="%s" value="%s"> %s</label>', htmlspecialchars($fieldKey, ENT_QUOTES), htmlspecialchars((string) $key, ENT_QUOTES), htmlspecialchars((string) $value, ENT_QUOTES));
        }
        $html .= '</div>';
        return $html;
    }

    private static function renderCheckboxGroupWidget(MFormItem $item, string $fieldKey): string
    {
        $attrs = $item->getAttributes();
        $layout = isset($attrs['layout']) && 'vertical' === $attrs['layout'] ? ' mform-cbg--vertical' : '';
        $modeAttr = isset($attrs['mode']) && 'radio' === $attrs['mode'] ? ' data-mode="radio"' : '';
        $uid = 'mfr-cbg-' . preg_replace('/[^a-z0-9]/i', '-', $fieldKey) . '-' . substr(md5($fieldKey), 0, 6);

        $html = sprintf(
            '<div class="mform-checkbox-group%s"%s data-cbg-id="%s">',
            $layout,
            $modeAttr,
            htmlspecialchars($uid, ENT_QUOTES),
        );
        // hidden input mit data-mfr-field – wird vom Flex-Repeater gelesen/geschrieben
        $html .= sprintf(
            '<input type="hidden" id="%s" data-mfr-field="%s" value="" class="mform-cbg-value">',
            htmlspecialchars($uid, ENT_QUOTES),
            htmlspecialchars($fieldKey, ENT_QUOTES),
        );
        foreach ($item->getOptions() as $key => $label) {
            $html .= sprintf(
                '<label class="mform-cbg-option" data-value="%s"><span class="mform-cbg-indicator"></span>%s</label>',
                htmlspecialchars((string) $key, ENT_QUOTES),
                htmlspecialchars((string) $label, ENT_QUOTES),
            );
        }
        $html .= '</div>';

        return $html;
    }

    private static function renderColorSwatchWidget(MFormItem $item, string $fieldKey): string
    {
        $uid = 'mfr-cs-' . preg_replace('/[^a-z0-9]/i', '-', $fieldKey) . '-' . substr(md5($fieldKey), 0, 6);

        $swatchHtml = '';
        foreach ($item->getOptions() as $value => $label) {
            $strVal = (string) $value;
            $labelStr = is_array($label) ? (string) ($label['label'] ?? $strVal) : (string) $label;
            $previewColor = is_array($label) ? (string) ($label['preview'] ?? '') : '';
            if (str_starts_with($strVal, '.')) {
                $styleAttr = '' !== $previewColor ? ' style="background-color:' . htmlspecialchars($previewColor, ENT_QUOTES) . '"' : '';
                $dataPreview = '' !== $previewColor ? ' data-preview-color="' . htmlspecialchars($previewColor, ENT_QUOTES) . '"' : '';
                $swatchHtml .= sprintf(
                    '<button type="button" class="mform-cs-swatch mform-cs-swatch--class" data-value="%s"%s%s title="%s"></button>',
                    htmlspecialchars($strVal, ENT_QUOTES),
                    $styleAttr,
                    $dataPreview,
                    htmlspecialchars($labelStr, ENT_QUOTES),
                );
            } else {
                $swatchHtml .= sprintf(
                    '<button type="button" class="mform-cs-swatch" data-value="%s" style="background-color:%s" title="%s"></button>',
                    htmlspecialchars($strVal, ENT_QUOTES),
                    htmlspecialchars($strVal, ENT_QUOTES),
                    htmlspecialchars($labelStr, ENT_QUOTES),
                );
            }
        }

        return sprintf(
            '<div class="mform-color-swatch" data-cs-id="%s">'
            . '<div class="input-group">'
            . '<span class="input-group-addon"><span class="mform-cs-preview"></span></span>'
            . '<input type="text" class="form-control mform-cs-input" data-mfr-field="%s" value="">'
            . '<span class="input-group-btn">'
            . '<button type="button" class="btn btn-default mform-cs-btn" tabindex="-1">'
            . '<i class="rex-icon fa-tint"></i>'
            . '</button>'
            . '</span>'
            . '</div>'
            . '<div class="mform-cs-popup">%s</div>'
            . '</div>',
            htmlspecialchars($uid, ENT_QUOTES),
            htmlspecialchars($fieldKey, ENT_QUOTES),
            $swatchHtml,
        );
    }

    /**
     * @param array<string, mixed> $attributes
     */
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
        // imglist uses rex_var_custom_medialist::getWidget() directly to get the full
        // gallery/grid/list view with preview, view toggle and vertical toolbar –
        // identical to the normal (non-repeater) imglist widget.
        if ('imglist' === $type) {
            return self::renderImglistWidget($fieldKey, $item);
        }

        // medialist and linklist use a lightweight skeleton initialised by list-widget.js
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

    private static function renderImglistWidget(string $fieldKey, MFormItem $item): string
    {
        $attrs = $item->getAttributes();
        $widgetId = '__MFRID__-' . preg_replace('/[^a-z0-9_-]/i', '-', $fieldKey);

        $args = [
            'view' => 'gallery',
            'views' => 'gallery,grid,list',
            'toolbar' => 'vertical',
        ];

        if (isset($attrs['data-media-category']) && '' !== (string) $attrs['data-media-category']) {
            $args['category'] = (string) $attrs['data-media-category'];
        }
        if (isset($attrs['data-media-type']) && '' !== (string) $attrs['data-media-type']) {
            $args['types'] = (string) $attrs['data-media-type'];
        }

        $html = rex_var_custom_medialist::getWidget(
            $widgetId,
            'mfr_imglist[' . $fieldKey . ']',
            '',
            $args,
        );

        // Add rex-js-widget-imglist class (mirrors what rex_var_imglist::getWidget() does)
        $html = str_replace(
            'mform-list-widget mform-list-widget-medialist',
            'mform-list-widget mform-list-widget-medialist rex-js-widget-imglist',
            $html,
        );

        // Mark the hidden value input so the Flex-Repeater JS reads/writes it
        $html = preg_replace(
            '/(<input\s+type="hidden"[^>]*class="[^"]*mform-list-value[^"]*"[^>]*)(>)/i',
            '$1 data-mfr-field="' . htmlspecialchars($fieldKey, ENT_QUOTES) . '"$2',
            $html,
            1,
        ) ?? $html;

        return $html;
    }

    private static function renderCustomLinkWidget(string $type, string $fieldKey, MFormItem $item): string
    {
        $attrs = $item->getAttributes();
        $widgetId = '__MFRID__-' . preg_replace('/[^a-z0-9_-]/i', '-', $fieldKey);

        $args = self::mapCustomLinkArgs($attrs, $type);

        $html = rex_var_custom_link::getWidget(
            $widgetId,
            'mfr_custom_link[' . $fieldKey . ']',
            '',
            $args,
            false,
        );

        // Flex-Repeater liest/schreibt nur Felder mit data-mfr-field.
        // Daher das eigentliche Value-Hidden des Widgets markieren.
        $html = preg_replace(
            '/(<input\s+type="hidden"[^>]*id="REX_LINK_[^"]+"[^>]*)(>)/i',
            '$1 data-mfr-field="' . htmlspecialchars($fieldKey, ENT_QUOTES) . '"$2',
            $html,
            1,
        ) ?? $html;

        return $html;
    }

    private static function renderCustomLinkMultiWidget(string $fieldKey, MFormItem $item): string
    {
        $attrs = $item->getAttributes();
        $args = self::mapCustomLinkMultiArgs($attrs);

        $html = rex_var_custom_link_multi::getWidget(
            '__MFRID__-' . preg_replace('/[^a-z0-9_-]/i', '-', $fieldKey),
            'mfr_custom_link_multi[' . $fieldKey . ']',
            '',
            $args,
        );

        // Flex-Repeater liest/schreibt nur Felder mit data-mfr-field.
        $html = preg_replace(
            '/(<input\s+type="hidden"[^>]*class="[^"]*mform-cl-multi-value[^"]*"[^>]*)(>)/i',
            '$1 data-mfr-field="' . htmlspecialchars($fieldKey, ENT_QUOTES) . '"$2',
            $html,
            1,
        ) ?? $html;

        return $html;
    }

    /**
     * @param array<string, mixed> $attrs
     * @return array<string, mixed>
     */
    private static function mapCustomLinkArgs(array $attrs, string $type): array
    {
        $args = [];

        $map = [
            'intern' => ['intern', 'data-intern'],
            'external' => ['external', 'data-extern'],
            'media' => ['media', 'data-media'],
            'mailto' => ['mailto', 'data-mailto'],
            'phone' => ['phone', 'data-tel'],
            'anchor' => ['anchor', 'data-anchor'],
        ];

        foreach ($map as $target => $sourceKeys) {
            foreach ($sourceKeys as $sourceKey) {
                if (!array_key_exists($sourceKey, $attrs)) {
                    continue;
                }
                $args[$target] = self::normalizeEnableDisableValue($attrs[$sourceKey]);
                break;
            }
        }

        if (isset($attrs['data-link-category'])) {
            $args['category'] = $attrs['data-link-category'];
        } elseif (isset($attrs['catId'])) {
            $args['category'] = $attrs['catId'];
        }

        if (isset($attrs['data-media-category'])) {
            $args['media_category'] = $attrs['data-media-category'];
        }
        if (isset($attrs['data-media-type'])) {
            $args['types'] = $attrs['data-media-type'];
        } elseif (isset($attrs['data-types'])) {
            $args['types'] = $attrs['data-types'];
        }
        if (isset($attrs['data-extern-link-prefix'])) {
            $args['external_prefix'] = $attrs['data-extern-link-prefix'];
        }
        if (isset($attrs['ylink'])) {
            $args['ylink'] = $attrs['ylink'];
        }

        // link/media im Flex-Repeater auf custom-link Widget einschränken
        if ('link' === $type || 'mform-link' === $type) {
            $args = array_merge([
                'intern' => 1,
                'external' => 0,
                'media' => 0,
                'mailto' => 0,
                'phone' => 0,
            ], $args);
        } elseif ('media' === $type || 'mform-media' === $type) {
            $args = array_merge([
                'intern' => 0,
                'external' => 0,
                'media' => 1,
                'mailto' => 0,
                'phone' => 0,
            ], $args);
        }

        return $args;
    }

    /**
     * @param array<string, mixed> $attrs
     * @return array<string, mixed>
     */
    private static function mapCustomLinkMultiArgs(array $attrs): array
    {
        $args = [];

        $map = [
            'intern' => ['intern', 'data-intern'],
            'extern' => ['extern', 'external', 'data-extern'],
            'media' => ['media', 'data-media'],
            'mailto' => ['mailto', 'data-mailto'],
            'phone' => ['phone', 'data-tel'],
            'anchor' => ['anchor', 'data-anchor'],
        ];

        foreach ($map as $target => $sourceKeys) {
            foreach ($sourceKeys as $sourceKey) {
                if (!array_key_exists($sourceKey, $attrs)) {
                    continue;
                }
                $args[$target] = self::normalizeEnableDisableValue($attrs[$sourceKey]);
                break;
            }
        }

        if (isset($attrs['data-link-category'])) {
            $args['category'] = $attrs['data-link-category'];
        } elseif (isset($attrs['catId'])) {
            $args['category'] = $attrs['catId'];
        }

        if (isset($attrs['data-media-category'])) {
            $args['media_category'] = $attrs['data-media-category'];
        }
        if (isset($attrs['data-media-type'])) {
            $args['types'] = $attrs['data-media-type'];
        } elseif (isset($attrs['data-types'])) {
            $args['types'] = $attrs['data-types'];
        }
        if (isset($attrs['data-extern-link-prefix'])) {
            $args['external_prefix'] = $attrs['data-extern-link-prefix'];
        }
        if (isset($attrs['ylink'])) {
            $args['ylink'] = $attrs['ylink'];
        }
        if (isset($attrs['btn_add'])) {
            $args['btn_add'] = $attrs['btn_add'];
        }

        return $args;
    }

    private static function normalizeEnableDisableValue(mixed $value): int
    {
        if (is_bool($value)) {
            return $value ? 1 : 0;
        }

        $normalized = strtolower(trim((string) $value));
        if (in_array($normalized, ['enable', '1', 'true', 'yes', 'on'], true)) {
            return 1;
        }
        if (in_array($normalized, ['disable', '0', 'false', 'no', 'off'], true)) {
            return 0;
        }

        return '' === $normalized ? 0 : 1;
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
