<?php

/**
 * @author Friends Of REDAXO
 * @license MIT
 */

namespace FriendsOfRedaxo\MForm\Template;

use Exception;
use rex_fragment;
use rex_logger;
use rex_view;

use function in_array;
use function is_array;
use function sprintf;

/**
 * Eine Quelle fuer Wrapper-Markup (Fieldset, Collapse, Spalten, Tabs, Modal):
 * das Theme-Fragment `<theme>/mform_wrapper.php`. Der klassische Parser und der
 * Flex-Repeater rufen beide hierueber, damit Layout-Aenderungen nur an einer
 * Stelle passieren und eigene Themes in beiden Pfaden greifen (#437).
 */
final class MFormWrapperRenderer
{
    public const DEFAULT_THEME = 'mform';

    /**
     * Attribut-Schluessel, die an Wrapper-Elementen nie als HTML-Attribut landen
     * (Steuerwerte von MForm, Repeater-Optionen, Feld-Marker).
     *
     * @var list<string>
     */
    public const SKIP_ATTRIBUTES = [
        'id', 'name', 'type', 'value', 'checked', 'selected',
        'form-group-class', 'form-group-attributes', 'visible_if', 'hidden_if',
        'data-mfr-field', 'label', 'open', 'collapsed', 'first_open', 'show_toggle_all',
        'btn_text', 'btn_class', 'confirm_delete', 'confirm_delete_msg', 'min', 'max',
        'default_count', 'groups', 'group', 'repeater_id', 'parent_id', 'show_add_button', 'show_add_buttons',
    ];

    /**
     * Serialisiert Wrapper-Attribute zu ` key="value" ...` (Werte HTML-escaped, Arrays uebersprungen).
     *
     * @param array<string, mixed> $attributes
     * @param list<string> $skip
     */
    public static function attributes(array $attributes, array $skip = self::SKIP_ATTRIBUTES): string
    {
        $html = '';
        foreach ($attributes as $key => $value) {
            if (in_array($key, $skip, true) || is_array($value)) {
                continue;
            }
            $html .= sprintf(' %s="%s"', htmlspecialchars((string) $key, ENT_QUOTES), htmlspecialchars((string) $value, ENT_QUOTES));
        }

        return $html;
    }

    /**
     * Rendert einen Wrapper-Typ ueber das Theme-Fragment.
     *
     * @param array<string, mixed> $vars Fragment-Variablen (class, attributes, label, legend, element, value, id, ...)
     */
    public static function fragment(string $type, array $vars = [], ?string $theme = null): string
    {
        $fragment = new rex_fragment();
        $fragment->setVar('type', $type, false);
        foreach ($vars as $key => $value) {
            $fragment->setVar($key, $value, false);
        }

        try {
            return $fragment->parse(($theme ?? self::DEFAULT_THEME) . '/mform_wrapper.php');
        } catch (Exception $e) {
            rex_logger::logException($e);

            return rex_view::error($e->getMessage());
        }
    }

    /**
     * Feld-Zeile (form-group mit Label- und Feldspalte) ueber das Theme-Fragment `mform_default.php`,
     * dasselbe, das der klassische Parser fuer jedes Feld nutzt.
     *
     * @param array<string, mixed> $vars class, formGroupAttributes, labelColClass, formItemColClass, label, element, notice, infoTooltip, infoCollapseButton, infoCollapse
     * @param string $type default | default_custom | default_full | default_custom_full
     */
    public static function formGroup(array $vars, string $type = 'default_custom', ?string $theme = null): string
    {
        $fragment = new rex_fragment();
        $fragment->setVar('type', $type, false);
        foreach (['formGroupAttributes', 'label', 'element', 'notice', 'infoTooltip', 'infoCollapseButton', 'infoCollapse', 'class'] as $key) {
            $fragment->setVar($key, $vars[$key] ?? '', false);
        }
        foreach ($vars as $key => $value) {
            $fragment->setVar($key, $value, false);
        }

        try {
            return $fragment->parse(($theme ?? self::DEFAULT_THEME) . '/mform_default.php');
        } catch (Exception $e) {
            rex_logger::logException($e);

            return rex_view::error($e->getMessage());
        }
    }

    /**
     * Oeffnendes Wrapper-Element mit Klasse und Attributen (start-group-column, column, tab, start-group-collapse, ...).
     *
     * @param array<string, mixed> $attributes
     * @param array<string, mixed> $extraVars
     */
    public static function open(string $type, string $class, array $attributes = [], array $extraVars = [], ?string $theme = null): string
    {
        return self::fragment($type, ['class' => trim($class), 'attributes' => self::attributes($attributes)] + $extraVars, $theme);
    }

    /**
     * Schliessendes Element (close-column, close-tab, close-fieldset, close-modal, ...).
     */
    public static function close(string $type, ?string $theme = null): string
    {
        return self::fragment($type, [], $theme);
    }

    public static function legend(string $legendHtml, ?string $theme = null): string
    {
        return '' === $legendHtml ? '' : self::fragment('legend', ['legend' => $legendHtml], $theme);
    }

    /**
     * Toggle-Button eines Collapse-Elements.
     *
     * @param array<string, mixed> $buttonAttributes data-toggle, data-collapse-open, aria-expanded
     */
    public static function collapseButton(string $labelHtml, array $buttonAttributes, bool $hidden, ?string $theme = null): string
    {
        return self::fragment('collapse-button', [
            'class' => $hidden ? 'hidden' : '',
            'attributes' => self::attributes($buttonAttributes, []),
            'value' => $labelHtml,
        ], $theme);
    }

    /**
     * Button-Attribute eines Collapse-Toggles aus den Layout-Regeln (offen, Akkordeon).
     *
     * @param array<string, mixed> $attributes Attribute des Collapse-Items
     * @return array<string, mixed>
     */
    public static function collapseButtonAttributes(array $attributes): array
    {
        $open = MFormLayoutCore::isCollapseOpen($attributes);
        $buttonAttributes = [
            'data-toggle' => 'collapse',
            'data-collapse-open' => $open ? 1 : 0,
            'aria-expanded' => $open ? 'true' : 'false',
        ];
        if (MFormLayoutCore::isCollapseAccordion($attributes)) {
            unset($buttonAttributes['data-collapse-open']);
        }

        return $buttonAttributes;
    }

    /**
     * Navigationseintrag eines Tabs.
     */
    public static function tabNavItem(string $labelHtml, string $navClass, string $tabId, ?string $theme = null): string
    {
        return self::fragment('tabnavli', ['class' => $navClass, 'value' => $tabId, 'label' => $labelHtml], $theme);
    }

    /**
     * Modal-Kopf: Button-Zeile und geoeffnetes Modal bis einschliesslich `.mform`-Body.
     * Das Fragment liest Ausrichtung und Row-Klassen aus den Attributen.
     *
     * @param array<string, mixed> $attributes Attribute des Modal-Items (data-modal-align, data-modal-row-class, data-group-row-class)
     */
    public static function modalOpen(string $labelHtml, string $btnClass, string $modalId, array $attributes, ?string $theme = null): string
    {
        return self::fragment('modal', [
            'id' => $modalId,
            'label' => $labelHtml,
            'class' => $btnClass,
            'attributes' => self::attributes($attributes, self::SKIP_ATTRIBUTES),
        ], $theme);
    }
}
