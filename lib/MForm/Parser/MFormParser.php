<?php
/**
 * @author Joachim Doerr
 * @package redaxo5
 * @license MIT
 */

namespace FriendsOfRedaxo\MForm\Parser;

use DOMDocument;
use DOMElement;
use DOMNodeList;
use Exception;
use FriendsOfRedaxo\MForm;
use FriendsOfRedaxo\MForm\DTO\MFormElement;
use FriendsOfRedaxo\MForm\DTO\MFormItem;
use FriendsOfRedaxo\MForm\FlexRepeater\MFormFlexRepeaterRenderer;
use FriendsOfRedaxo\MForm\Handler\MFormAttributeHandler;
use FriendsOfRedaxo\MForm\Repeater\MFormRepeaterHelper;
use FriendsOfRedaxo\MForm\Utils\MFormGroupExtensionHelper;
use FriendsOfRedaxo\MForm\Utils\MFormItemManipulator;
use rex_addon;
use rex_clang;
use rex_extension;
use rex_extension_point;
use rex_fragment;
use rex_i18n;
use rex_logger;
use rex_var;
use rex_var_custom_link;
use rex_var_imglist;
use rex_var_link;
use rex_var_linklist;
use rex_var_media;
use rex_var_medialist;
use rex_view;

use function array_key_exists;
use function count;
use function in_array;
use function is_array;
use function is_int;
use function is_string;
use function strlen;

class MFormParser
{
    protected array $elements = [];
    protected array $values = [];
    protected array $obj = [];
    protected bool $debug = false;

    /**
     * @var string
     */
    protected string $theme = 'mform';

    private function openRepeaterElement(MFormItem $item, string $key, array $items, array &$skipKeys = []): void
    {
        $this->openFlexRepeaterElement($item, $key, $items, $skipKeys);
    }

    private function closeRepeaterElement(MFormItem $item): void
    {
        $this->closeFlexRepeaterElement($item);
    }

    private function openFlexRepeaterElement(MFormItem $item, string $key, array $items, array &$skipKeys = []): void
    {
        // Wichtig: Keine Default-Manipulationen fuer den Flex-Repeater selbst.
        // setVarAndIds() escaped String-Werte per htmlspecialchars(); das wuerde
        // den JSON-String des Repeaters ungueltig machen (json_decode => [] beim Reload).

        $attrs = $item->getAttributes();
        $varId = $item->getVarId();

        $repeaterId = 'mfr_' . uniqid('', false);

        if (is_array($varId)) {
            $fieldName = 'REX_INPUT_VALUE[' . implode('][', $varId) . ']';
        } else {
            $varIdString = (string) $varId;
            if (0 === strpos($varIdString, '[')) {
                $fieldName = 'REX_INPUT_VALUE' . $varIdString;
            } else {
                $fieldName = 'REX_INPUT_VALUE[' . $varIdString . ']';
            }
        }

        $currentValue = $item->getValue();
        if (is_array($currentValue)) {
            $jsonValue = json_encode($currentValue, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        } elseif (is_string($currentValue) && '' !== $currentValue) {
            $decoded = json_decode($currentValue, true);
            $jsonValue = is_array($decoded) ? $currentValue : '[]';
        } else {
            $jsonValue = '[]';
        }

        $btnText = $attrs['btn_text'] ?? rex_i18n::msg('mform_flex_repeater_add');
        $btnClass = ' ' . ($attrs['btn_class'] ?? 'btn-primary');
        $label = '';
        if (!empty($item->getLabel())) {
            $itemLabel = $item->getLabel();
            if (is_array($itemLabel)) {
                $firstLabel = reset($itemLabel);
                $labelStr = (string) ($firstLabel ?? '');
            } else {
                $labelStr = (string) $itemLabel;
            }
            $label = '<label class="control-label mfr-label">' . htmlspecialchars($labelStr, ENT_QUOTES) . '</label>';
        }

        $min = (int) ($attrs['min'] ?? 0);
        $max = (int) ($attrs['max'] ?? 0);
        $collapsed = !isset($attrs['collapsed']) || $attrs['collapsed'] ? 'true' : 'false';
        $firstOpen = !isset($attrs['first_open']) || $attrs['first_open'] ? 'true' : 'false';
        $showToggleAll = !isset($attrs['show_toggle_all']) || $attrs['show_toggle_all'] ? 'true' : 'false';
        $open = isset($attrs['open']) && $attrs['open'] ? 'true' : 'false';
        $defaultCount = isset($attrs['default_count']) ? (int) $attrs['default_count'] : 0;
        $confirmDelete = isset($attrs['confirm_delete']) ? (int) ((bool) $attrs['confirm_delete']) : 0;
        $confirmDeleteMsg = isset($attrs['confirm_delete_msg'])
            ? (string) $attrs['confirm_delete_msg']
            : (string) rex_i18n::msg('mform_repeater_remove_group_confirm_msg');

        $addonDebug = (int) ((bool) rex_addon::get('mform')->getConfig('debug', false));

        $innerForm = null;
        foreach ($items as $k => $itm) {
            if ($k > $key && $itm instanceof MForm) {
                $innerForm = $itm;
                $skipKeys[] = $k;
                break;
            }
        }

        $templateHtml = '';
        if (null !== $innerForm) {
            $templateHtml = MFormFlexRepeaterRenderer::renderTemplate($innerForm, 1);
        }

        $this->elements[] = sprintf(
            '<div class="mfr-container" id="%s" data-mfr-field-name="%s" data-mfr-min="%d" data-mfr-max="%d" data-mfr-collapsed="%s" data-mfr-first-open="%s" data-mfr-show-toggle-all="%s" data-mfr-open="%s" data-mfr-default-count="%d" data-mfr-confirm-delete="%d" data-mfr-confirm-delete-msg="%s" data-mfr-debug="%d">%s',
            htmlspecialchars($repeaterId, ENT_QUOTES),
            htmlspecialchars($fieldName, ENT_QUOTES),
            $min,
            $max,
            $collapsed,
            $firstOpen,
            $showToggleAll,
            $open,
            $defaultCount,
            $confirmDelete,
            htmlspecialchars($confirmDeleteMsg, ENT_QUOTES),
            $addonDebug,
            $label
        );

        $toggleAllButton = sprintf(
            '<button type="button" class="btn btn-default mfr-btn-toggle-all" title="%s"><i class="rex-icon fa-square-o"></i> %s</button>',
            htmlspecialchars(rex_i18n::msg('mform_flex_repeater_toggle_all'), ENT_QUOTES),
            htmlspecialchars(rex_i18n::msg('mform_flex_repeater_toggle_all'), ENT_QUOTES)
        );

        $this->elements[] = sprintf(
            '<div class="mfr-toolbar mfr-toolbar-top">%s<button type="button" class="btn btn-mform-repeater%s mfr-btn-add"><i class="rex-icon fa-plus-circle"></i> %s</button></div>',
            ('true' === $showToggleAll) ? $toggleAllButton : '',
            htmlspecialchars($btnClass, ENT_QUOTES),
            htmlspecialchars($btnText, ENT_QUOTES)
        );

        $this->elements[] = '<div class="mfr-items-list"></div>';

        $this->elements[] = sprintf(
            '<template class="mfr-item-template"><div class="mfr-item"><div class="mfr-item-header"><span class="mfr-item-drag" title="Verschieben"><i class="rex-icon fa-bars"></i></span><span class="mfr-item-title"></span><div class="mfr-item-actions"><button type="button" class="btn btn-xs mfr-btn-up" title="%s"><i class="rex-icon fa-chevron-up"></i></button><button type="button" class="btn btn-xs mfr-btn-down" title="%s"><i class="rex-icon fa-chevron-down"></i></button><button type="button" class="btn btn-xs mfr-btn-add-after" title="%s"><i class="rex-icon fa-plus"></i></button><button type="button" class="btn btn-xs mfr-btn-visibility" title="%s"><i class="rex-icon fa-eye"></i></button><button type="button" class="btn btn-xs mfr-btn-collapse" title="%s"><i class="rex-icon fa-square-o"></i></button><button type="button" class="btn btn-xs btn-danger mfr-btn-remove" title="%s"><i class="rex-icon fa-trash"></i></button></div></div><div class="mfr-item-body">%s</div></div></template>',
            htmlspecialchars(rex_i18n::msg('mform_flex_repeater_move_up'), ENT_QUOTES),
            htmlspecialchars(rex_i18n::msg('mform_flex_repeater_move_down'), ENT_QUOTES),
            htmlspecialchars($btnText, ENT_QUOTES),
            htmlspecialchars(rex_i18n::msg('mform_flex_repeater_visibility'), ENT_QUOTES),
            htmlspecialchars(rex_i18n::msg('mform_flex_repeater_toggle'), ENT_QUOTES),
            htmlspecialchars(rex_i18n::msg('mform_flex_repeater_remove'), ENT_QUOTES),
            $templateHtml
        );

        $this->elements[] = sprintf(
            '<div class="mfr-toolbar mfr-toolbar-bottom">%s<button type="button" class="btn btn-mform-repeater%s mfr-btn-add"><i class="rex-icon fa-plus-circle"></i> %s</button></div>',
            ('true' === $showToggleAll) ? $toggleAllButton : '',
            htmlspecialchars($btnClass, ENT_QUOTES),
            htmlspecialchars($btnText, ENT_QUOTES)
        );

        if ($this->debug) {
            $this->elements[] = sprintf(
                '<textarea name="%s" class="mfr-value" style="width:100%%;height:150px;font-family:monospace;font-size:12px">%s</textarea>',
                htmlspecialchars($fieldName, ENT_QUOTES),
                htmlspecialchars((string) $jsonValue, ENT_QUOTES)
            );
        } else {
            $this->elements[] = sprintf(
                '<input type="hidden" name="%s" class="mfr-value" value="%s">',
                htmlspecialchars($fieldName, ENT_QUOTES),
                htmlspecialchars((string) $jsonValue, ENT_QUOTES)
            );
        }
    }

    private function closeFlexRepeaterElement(MFormItem $item): void
    {
        $this->elements[] = '</div><!-- /mfr-container -->';
    }


    private function openWrapperElement(MFormItem $item, string $key, array $items): void
    {
        $element = new MFormElement();
        $attributes = $item->getAttributes();
        $removeAttributes = [];

        if (!empty($item->getLabel())) {
            $element->setLabel($this->parseElement($this->createLabelElement($item->setId('uid_' . uniqid())), 'base'));
        }

        if (!empty($item->getLegend())) {
            $legendElement = new MFormElement();
            $legendElement->setType('legend')
                ->setLegend($item->getLegend());
            $element->setLegend($this->parseElement($legendElement, 'wrapper'));
        }

        // COLLAPSE MANIPULATIONS
        if ('collapse' == $item->getType()) {
            $removeAttributes = ['data-group-hide-toggle-links', 'data-group-accordion', 'data-group-open-collapse'];
            $buttonAttributes = [
                'data-toggle' => 'collapse',
                'data-collapse-open' => (int) $attributes['data-group-open-collapse'],
                'aria-expanded' => ((1 == (int) $attributes['data-group-open-collapse']) ? 'true' : 'false'),
            ];
            if (isset($attributes['data-group-accordion']) && 1 == (int) $attributes['data-group-accordion']) {
                unset($buttonAttributes['data-collapse-open']);
            }
            if ('true' == $buttonAttributes['aria-expanded']) {
                $item->setClass($item->getClass() . ' in');
            }
            $collapseButton = new MFormElement();
            $collapseButton->setType('collapse-button')
                ->setClass((empty($item->getLabel()) || (array_key_exists('data-group-hide-toggle-links', $attributes) && 'true' == $attributes['data-group-hide-toggle-links'])) ? ' hidden' : '')
                ->setAttributes($this->parseAttributes($buttonAttributes))
                ->setValue((string) $item->getLabel());
            $element->setLabel($this->parseElement($collapseButton, 'wrapper')); // add parsed legend to collapse element
        }

        if ('start-group-collapse' == $item->getType()) {
            $removeAttributes = ['data-group-collapse-id'];
        }

        // TAB MANIPULATIONS
        if ('start-group-tab' == $item->getType()) {
            $nav = [];
            /** @var MFormItem $itm */
            foreach ($items as $k => $itm) {
                if ($itm instanceof MFormItem && $k > $key && ($itm->getGroup() == $item->getGroup() && 'tab' == $itm->getType())) {
                    // add navigation item
                    $element = new MFormElement();
                    $element->setType('tabnavli')
                        ->setValue($itm->getGroup() . $itm->getGroupCount() . '_' . $item->getGroupKey())
                        ->setLabel(((array_key_exists('tab-icon', $itm->getAttributes())) ? '<i class="rex-icon ' . $itm->getAttributes()['tab-icon'] . '"></i> ' : '') . $itm->getLabel())
                        ->setClass(
                            ((array_key_exists('nav-class', $itm->getAttributes())) ? $itm->getAttributes()['nav-class'] . ' ' : '') .
                            ((array_key_exists('pull-right', $itm->getAttributes()) && true === $itm->getAttributes()['pull-right']) ? ' pull-right' : '') .
                            ((array_key_exists('data-group-open-tab', $itm->getAttributes()) && true === $itm->getAttributes()['data-group-open-tab']) ? ' active' : ''),
                        );
                    $nav[] = $this->parseElement($element, 'wrapper');
                }
            }
            $element->setElement(implode('', $nav));
        }
        if ('tab' == $item->getType()) {
            $attributes['data-tab-group-nav-tab-id'] = $item->getGroup() . $item->getGroupCount() . '_' . $item->getGroupKey();
            if (isset($attributes['data-group-open-tab']) && true === $attributes['data-group-open-tab']) {
                $item->setClass($item->getClass() . 'active');
            }
        }

        if (count($removeAttributes) > 0) {
            foreach ($removeAttributes as $key) {
                if (isset($attributes[$key])) {
                    unset($attributes[$key]);
                }
            }
        } // remove group data tags

        $element->setType($item->getType())
            ->setAttributes($this->parseAttributes($attributes))
            ->setClass($item->getClass());

        $this->elements[] = $this->parseElement($element, 'wrapper');
    }

    private function closeWrapperElement(string $type): void
    {
        $element = new MFormElement();
        $element->setType($type);
        $this->elements[] = $this->parseElement($element, 'wrapper');
    }

    /**
     * @description create any no input inline element [html|headline|description]
     */
    private function generateLineElement(MFormItem $item): void
    {
        // create templateElement object
        $element = new MFormElement();
        $element->setOutput((is_string($item->getValue())?$item->getValue():''))
            ->setAttributes($this->parseAttributes($item->getAttributes()))
            ->setClass($item->getClass()) // set output to replace in template
            ->setType($item->getType())
            ->setFull($item->isFull());

        // add to output element array
        $this->elements[] = $this->parseElement($element, 'base');
    }

    /**
     * @description helper method to create hidden input elements
     */
    private function generateHiddenInputElement(MFormItem $item): void
    {
        // default manipulations
        $this->executeDefaultManipulations($item);

        // create element
        $element = new MFormElement();
        // add all replacement elements for template parsing
        $element->setId($item->getId())
            ->setVarId($item->getVarId())
            ->setValue((string) ((is_array($item->getValue())) ? implode('', $item->getValue()) : $item->getValue()))
            ->setType($item->getType())
            ->setClass($item->getClass())
            ->setAttributes($this->parseAttributes($item->getAttributes())); // parse attributes for use in templates

        // add to output element array
        $this->elements[] = $this->parseElement($element, 'input');
    }

    /**
     * @description helper method to create input text elements
     */
    private function generateInputElement(MFormItem $item): void
    {
        $datalist = '';

        if ('text-readonly' == $item->getType()) { // is readonly
            MFormAttributeHandler::addAttribute($item, 'readonly', 'readonly'); // add attribute readonly
        }

        // datalist?
        if ($item->getOptions()) {
            $item->setAttributes(array_merge($item->getAttributes(), ['list' => 'list' . $item->getId()]));

            $optionElements = '';
            foreach ($item->getOptions() as $key => $value) {
                $optionElements .= $this->createOptionElement($item, $value, (!is_int($key)) ? "label=\"$key\"" : '', 'datalist-option', false);
            }
            $element = new MFormElement();
            $element->setOptions($optionElements)
                ->setId('list' . $item->getId())
                ->setType('datalist');
            $datalist = $this->parseElement($element, 'input');
        }

        // default manipulations
        $this->executeDefaultManipulations($item);

        // create element
        $element = new MFormElement();
        // add all replacement elements for template parsing
        $element->setId($item->getId())
            ->setVarId($item->getVarId())
            ->setValue((string) ((is_array($item->getValue())) ? implode('', $item->getValue()) : $item->getValue()))
            ->setType($item->getType())
            ->setClass($item->getClass())
            ->setDatalist($datalist)
            ->setAttributes($this->parseAttributes($item->getAttributes())); // parse attributes for use in templates

        // create templateElement object
        $templateElement = new MFormElement();
        $templateElement->setLabel($this->parseElement($this->createLabelElement($item), 'base'))
            ->setElement($this->parseElement($element, 'input'))
            ->setNotice($item->getNotice())
            ->setType($this->getDefaultTemplateType($item, $templateElement));

        // add to output element array
        $this->elements[] = $this->parseElement($templateElement, 'default');
    }

    /**
     * @description helper method to create textarea elements
     */
    private function generateAreaElement(MFormItem $item): void
    {
        // set typ specific vars
        if ('textarea-readonly' == $item->getType()) {
            $item->setType('textarea'); // type is textarea
            MFormAttributeHandler::addAttribute($item, 'readonly', 'readonly'); // add attribute readonly
        }

        // default manipulations
        $this->executeDefaultManipulations($item);

        // create element
        $element = new MFormElement();
        // add all replacement elements for template parsing
        $element->setId($item->getId())
            ->setVarId($item->getVarId())
            ->setValue((string) ((is_array($item->getValue())) ? implode('', $item->getValue()) : $item->getValue()))
            ->setType($item->getType())
            ->setClass($item->getClass())
            ->setAttributes($this->parseAttributes($item->getAttributes()));

        // create templateElement object
        $templateElement = new MFormElement();
        $templateElement->setLabel($this->parseElement($this->createLabelElement($item), 'base'))
            ->setElement($this->parseElement($element, 'textarea'))
            ->setNotice($item->getNotice())
            ->setType($this->getDefaultTemplateType($item, $templateElement));

        // add to output element array
        $this->elements[] = $this->parseElement($templateElement, 'default');
    }

    /**
     * @description helper method to create select or multiselect element
     */
    private function generateOptionsElement(MFormItem $item): void
    {
        // default manipulations
        $this->executeDefaultManipulations($item);

        // init option element string
        $optionElements = '';
        $attributes = $item->getAttributes();
        if (count($item->getToggleOptions()) > 0) {
            $attributes = array_merge(['data-toggle' => 'collapse'], $attributes);
        }
        $itemAttributes = $this->parseAttributes($attributes); // parse attributes for output

        if ($item->isMultiple() && is_array($item->getValue()) &&
            count($item->getValue()) == count($item->getValue(), COUNT_RECURSIVE)) {
            $item->setValue(implode(',', $item->getValue()));
        }

        // options must be given
        if (count($item->getOptions()) > 0) {
            // size count
            $count = 0;
            foreach ($item->getOptions() as $key => $value) {
                // is value label we have an opt group
                if (is_array($value)) {
                    // optGroup set
                    $optGroupLabel = $key;
                    $optElements = '';
                    ++$count; // + for group label
                    // create options
                    foreach ($value as $vKey => $vValue) {
                        ++$count;
                        $disabled = false;
                        $toggle = '';
                        if (in_array($vKey, $item->getDisabledOptions())) {
                            $disabled = true;
                        }
                        if (array_key_exists($vKey, $item->getToggleOptions())) {
                            $toggle = $item->getToggleOptions()[$vKey];
                        }
                        $optElements .= $this->createOptionElement($item, $vKey, $vValue, 'option', true, $disabled, $toggle);
                    }

                    // create opt group element
                    $groupElement = new MFormElement();
                    $groupElement->setOptions($optElements)
                        ->setLabel($optGroupLabel)
                        ->setType('optgroup');

                    $optionElements .= $this->parseElement($groupElement, 'select');
                } else {
                    ++$count;
                    $disabled = false;
                    $toggle = '';
                    if (in_array($key, $item->getDisabledOptions())) {
                        $disabled = true;
                    }
                    if (array_key_exists($key, $item->getToggleOptions())) {
                        $toggle = $item->getToggleOptions()[$key];
                    }
                    $optionElements .= $this->createOptionElement($item, $key, $value, 'option', true, $disabled, $toggle);
                }
            }
            // is size full
            if ('full' == $item->getSize()) {
                // use count to replace #sizefull# placeholder
                $itemAttributes = str_replace('#sizefull#', $count, $itemAttributes);
            }
        }

        // create element
        $element = new MFormElement();
        $element->setId($item->getId())
            ->setVarId($item->getVarId())
            ->setType($item->getType())
            ->setValue((string) ((is_array($item->getValue())) ? implode('', $item->getValue()) : $item->getValue()))
            ->setAttributes($itemAttributes)
            ->setClass($item->getClass())
            ->setOptions($optionElements);

        if ($item->isMultiple()) {
            $element->setVarId($item->getVarId() . '[]');
        }

        // create templateElement object
        $templateElement = new MFormElement();
        $templateElement->setLabel($this->parseElement($this->createLabelElement($item), 'base'))
            ->setElement($this->parseElement($element, 'select'))
            ->setNotice($item->getNotice())
            ->setType($this->getDefaultTemplateType($item, $templateElement));

        // add to output element array
        $this->elements[] = $this->parseElement($templateElement, 'default');
    }

    /**
     * @description helper method to create option elements
     */
    private function createOptionElement(MFormItem $item, $key, $value, string $templateType = 'option', bool $selected = true, bool $disabled = false, string|array $toggle = ''): string
    {
        // create element
        $element = new MFormElement();
        $element->setValue((string) $key)// set option key
        ->setLabel($value) // set option label
        ->setType($templateType);

        if (!empty($toggle)) {
            $attributes = [];
            if (is_array($toggle) && count($toggle) == 2) {
                $attributes[':data-toggle-item'] = $toggle[1];
                $toggle = null;
            }
            if (is_string($toggle)) {
                $attributes['data-toggle-item'] = $toggle;
            }
            if (count($attributes) > 0) {
                $element->setAttributes($this->parseAttributes($attributes));
            }
        }

        $itemValue = $item->getValue();

        // is mode edit and item multiple
        if ('edit' == $item->getMode() && $item->isMultiple()) {
            // explode the hidden value string
            if (is_string($itemValue)) {
                foreach (explode(',', $itemValue) as $iValue) {
                    if ($key == $iValue) { // check is the option key in the hidden string
                        $itemValue = $iValue; // set new item value
                    }
                }
            }
        }
        /* Selected fix @skerbis @dtpop @MC-PMOE */
        if ($item->multiple && null !== $item->stringValue) {
            $items_selected = json_decode($item->stringValue, true);
            $items_default_value = explode(',', $item->getDefaultValue());

            $current = explode('][', trim($item->varId, '[]'));

            // JSON Values 1.x
            if (isset($current[1]) && isset($items_selected[$current[1]]) && is_array($items_selected[$current[1]]) && (in_array((string) $key, $items_selected[$current[1]]) || ('add' == $item->getMode() && in_array((string) $key, $items_default_value)))) {

                $element->setAttributes($element->attributes . ' selected');

                // JSON Values 1.x.x
            } elseif (isset($current[2]) && isset($items_selected[$current[1]][$current[2]]) && is_array($items_selected[$current[1]][$current[2]]) && in_array((string) $key, $items_selected[$current[1]][$current[2]]) || ('add' == $item->getMode() && in_array((string) $key, $items_default_value))) {

                $element->setAttributes($element->attributes . ' selected');

                // REX_VAL
            } elseif (!isset($current[1]) && isset($items_selected) && is_array($items_selected) && in_array((string) $key, $items_selected) || ('add' == $item->getMode() && in_array((string) $key, $items_default_value))) {
                $element->setAttributes($element->attributes . ' selected');
            }
        } else {
            // set default value or selected
            if ($selected && ((string) $key == (string) $itemValue || ('add' == $item->getMode() && (string) $key == $item->getDefaultValue()))) {
                $element->setAttributes($element->attributes . ' selected'); // add attribute selected
            }
        }

        if ($disabled) {
            $element->setAttributes($element->attributes . ' disabled');
        }

        // parse element
        return $this->parseElement($element, ('datalist-option' == $templateType) ? 'input' : 'select');
    }

    /**
     * @description helper method to create checkboxes
     */
    private function generateCheckboxElement(MFormItem $item): void
    {
        // default manipulations
        $this->executeDefaultManipulations($item);

        $checkboxElements = '';

        // options must be given
        if (count($item->getOptions()) > 0) {
            foreach ($item->getOptions() as $key => $value) {
                $checkboxElements .= $this->createCheckElement($item, $key, $value); // create element by helper
                break;
            }
        }

        // create templateElement object
        $templateElement = new MFormElement();
        $templateElement->setLabel($this->parseElement($this->createLabelElement($item), 'base'))
            ->setElement($checkboxElements)
            ->setNotice($item->getNotice())
            ->setType($this->getDefaultTemplateType($item, $templateElement));

        // add to output element array
        $this->elements[] = $this->parseElement($templateElement, 'default');
    }

    /**
     * @description helper method to create check elements [checkbox|radiobutton]
     */
    private function createCheckElement(MFormItem $item, $key, $value, ?int $count = null): string
    {
        // create element
        $element = new MFormElement();
        $element->setValue((string) $key)
            ->setId($item->getId())
            ->setVarId($item->getVarId())
            ->setType($item->getType())
            ->setClass($item->getClass())
            ->setLabel($value);

        $attributes = $item->getAttributes();

        // add count to id
        if (is_numeric($count)) {
            $element->setId($item->getId() . $count);
        }
        // add data toggle
        if (count($item->getToggleOptions()) > 0 && array_key_exists($key, $item->getToggleOptions())) {
            $toggle = $item->getToggleOptions();
            if (is_array($item->getToggleOptions()[$key]) && count($item->getToggleOptions()[$key]) == 2) {
                $attributes[':data-toggle-item'] = $item->getToggleOptions()[$key][1];
                $toggle = null;
            }
            if (is_string($item->getToggleOptions()[$key])) {
                $attributes['data-toggle-item'] = $item->getToggleOptions()[$key];
            }
        }
        if (isset($attributes['data-toggle-item']) || isset($attributes[':data-toggle-item'])) {
            if ('checkbox' == $item->getType()) {
                $attributes = array_merge(['data-checkbox-toggle' => 'collapse'], $attributes);
            }
            if ('radio' == $item->getType()) {
                $attributes = array_merge(['data-radio-toggle' => 'collapse'], $attributes);
            }
        }
        // set checked by value or default value
        if ($key == $item->getValue() || ('add' == $item->getMode() && $key == $item->getDefaultValue())) {
            $element->setAttributes(' checked="checked" ' . $this->parseAttributes($attributes));
        } else {
            $element->setAttributes($this->parseAttributes($attributes));
        }

        // parse element
        return $this->parseElement($element, 'input');
    }

    /**
     * @description helper method to create radiobutton element
     */
    private function generateRadioElement(MFormItem $item): void
    {
        // default manipulations
        $this->executeDefaultManipulations($item);
        $radioElements = '';

        // options must be given
        if (count($item->getOptions()) > 0) {
            $count = 0; // init count
            foreach ($item->getOptions() as $key => $value) {
                ++$count; // + count
                $radioElements .= $this->createCheckElement($item, $key, $value, $count); // create element by helper
            }
        }

        // create templateElement object
        $templateElement = new MFormElement();
        $templateElement->setLabel($this->parseElement($this->createLabelElement($item), 'base'))
            ->setElement($radioElements)
            ->setNotice($item->getNotice())
            ->setType($this->getDefaultTemplateType($item, $templateElement));
        if (!empty($item->getAttributes()['form-group-class'])) {
            $templateElement->setClass($item->getAttributes()['form-group-class']);
            unset($item->getAttributes()['form-group-class']);
        }

        // add to output element array
        $this->elements[] = $this->parseElement($templateElement, 'default');
    }

    /**
     * @description helper method to create media input elements [media|medialist]
     */
    private function generateMediaElement(MFormItem $item): void
    {
        $dom = new DOMDocument();
        $inputValue = false;

        if (is_array($item->getVarId()) && count($item->getVarId()) > 0) {
            if (count($item->getVarId()) > 1) {
                $inputValue = true;
            }
            $this->executeDefaultManipulations($item, false, false);
        }

        // create templateElement object
        $templateElement = new MFormElement();
        $templateElement->setLabel($this->parseElement($this->createLabelElement($item), 'base'));
        $parameter = $item->getParameter();
        $attributes = $item->getAttributes();

        if (isset($parameter['types'])) {
            $parameter['types'] = str_replace(' ', '', strtolower($parameter['types']));
        }

        switch ($item->getType()) {
            default:
            case 'media':
                $inputValue = ($inputValue) ? 'REX_INPUT_VALUE' : 'REX_INPUT_MEDIA';
                $id = $this->getWidgetId($item);
                $html = rex_var_media::getWidget((int) $id, $inputValue . '[' . $item->getVarId() . ']', $item->getValue(), $parameter);

                $dom = new DOMDocument();
                @$dom->loadHTML(utf8_decode($html));
                $inputs = $dom->getElementsByTagName('input');
                $this->prepareLinkInput($dom, $inputs, $item, $attributes);

                if ($inputs instanceof DOMNodeList) {
                    $this->processNodeFormElements($inputs, $item, 'REX_MEDIA_' . (int) $id);
                }
                break;
            case 'imglist':
            case 'medialist':
                $inputValue = ($inputValue) ? 'REX_INPUT_VALUE' : 'REX_INPUT_MEDIALIST';
                $id = $this->getWidgetId($item);
                $value = (!is_string($item->getValue())) ? '' : $item->getValue();
                if ('medialist' === $item->getType()) {
                    $html = \rex_var_custom_medialist::getWidget($id, $inputValue . '[' . $item->getVarId() . ']', $value, $parameter);
                } else {
                    /** @var rex_var_imglist $class */
                    $class = 'rex_var_' . $item->getType();
                    $html = $class::getWidget($id, $inputValue . '[' . $item->getVarId() . ']', $value, $parameter);
                }

                $dom = new DOMDocument();
                @$dom->loadHTML(utf8_decode($html));
                $selects = $dom->getElementsByTagName('select');
                $inputs = $dom->getElementsByTagName('input');

                if ($selects instanceof DOMNodeList) {
                    $this->processNodeFormElements($selects, $item, 'REX_MEDIALIST_SELECT_' . $id);
                }
                if ($inputs instanceof DOMNodeList) {
                    $this->processNodeFormElements($inputs, $item, 'REX_MEDIALIST_' . $id);
                }

                $this->prepareLinkInput($dom, $inputs, $item, $attributes);
                break;
        }

        $body = $this->getBodyInner($dom);
        $body = $this->addClickPrevent($body, $attributes);

        // get body inner
        $templateElement->setElement($body)
            ->setNotice($item->getNotice())
            ->setType($this->getDefaultTemplateType($item, $templateElement));

        // add to output element array
        $this->elements[] = $this->parseElement($templateElement, 'default');
    }

    /**
     * @description link, linklist
     */
    private function generateLinkElement(MFormItem $item): void
    {
        $dom = new DOMDocument();
        $inputValue = false;

        if (is_array($item->getVarId()) && count($item->getVarId()) > 0) {
            if (count($item->getVarId()) > 1) {
                $inputValue = true;
            }
            $this->executeDefaultManipulations($item, false, false);
        }

        // create templateElement object
        $templateElement = new MFormElement();
        $templateElement->setLabel($this->parseElement($this->createLabelElement($item), 'base'));
        $parameter = $item->getParameter();
        $attributes = $item->getAttributes();

        if (isset($parameter['types'])) {
            $parameter['types'] = str_replace(' ', '', strtolower($parameter['types']));
        }

        switch ($item->getType()) {
            default:
            case 'link':
                $inputValue = ($inputValue) ? 'REX_INPUT_VALUE' : 'REX_INPUT_LINK';
                $id = $this->getWidgetId($item);
                $html = rex_var_link::getWidget($id, $inputValue . '[' . $item->getVarId() . ']', $item->getValue(), $parameter);

                $dom = new DOMDocument('1.0', 'utf-8');
                @$dom->loadHTML('<?xml encoding="utf-8" ?>' . $html); // utf8_decode($html)
                $inputs = $dom->getElementsByTagName('input');

                foreach ($inputs as $input) {
                    $this->processNodeFormElement($input, $item, 'REX_LINK_' . (int)$id);
                    if ($input instanceof DOMElement) {
                        if ($input->getAttribute('type') == 'text') {
                            $input->setAttribute('id', $input->getAttribute('id') . '_NAME');
                        }
                    }
                }

                $this->prepareLinkInput($dom, $inputs, $item, $attributes);
                break;
            case 'linklist':
                $inputValue = ($inputValue) ? 'REX_INPUT_VALUE' : 'REX_INPUT_LINKLIST';
                $id = $this->getWidgetId($item);
                $html = \rex_var_custom_linklist::getWidget($id, $inputValue . '[' . $item->getVarId() . ']', $item->getValue(), $parameter);

                $dom = new DOMDocument('1.0', 'utf-8');
                @$dom->loadHTML('<?xml encoding="utf-8" ?>' . $html); // utf8_decode($html)
                $selects = $dom->getElementsByTagName('select');
                $inputs = $dom->getElementsByTagName('input');

                if ($selects instanceof DOMNodeList) {
                    $this->processNodeFormElements($selects, $item, 'REX_LINKLIST_SELECT_' . $id);
                }
                if ($inputs instanceof DOMNodeList) {
                    $this->processNodeFormElements($inputs, $item, 'REX_LINKLIST_' . $id);
                }

                $this->prepareLinkInput($dom, $inputs, $item, $attributes);
                break;
        }

        $body = $this->getBodyInner($dom);
        $body = $this->addClickPrevent($body, $attributes);

        // get body inner
        $templateElement->setElement($body)
            ->setNotice($item->getNotice())
            ->setType($this->getDefaultTemplateType($item, $templateElement));

        // add to output element array
        $this->elements[] = $this->parseElement($templateElement, 'default');
    }

    private function prepareLinkInput(DOMDocument $dom, DOMNodeList $inputs, $item, $attributes): void
    {
        foreach ($inputs as $input) {
            if ($input instanceof DOMElement) {
                switch ($input->getAttribute('type')) {
                    case 'text':
                        if (isset($attributes['repeater_link']) && $attributes['repeater_link'] === true) {
                            if ($item->getType() == 'media') {
                                $item->addAttribute('x-model', $attributes['x-model']);
                            } else {
                                $item->addAttribute('x-model', $attributes['x-model'] . '.name');
                            }
                            $this->processNodeFormElement($input, $item);
                            $input->setAttribute(':id', "'".$attributes['item_name_key']."-'+".$attributes['repeaterId']."Index".((isset($attributes['parent_id']))?"+'-'+".$attributes['parent_id'].'Index':'')."+'_NAME'");
                        } else {
                            $this->processNodeFormElement($input, $item);
                        }
                        break;
                    case 'hidden':
                        if (isset($attributes['repeater_link']) && $attributes['repeater_link'] === true) {
                            $item->addAttribute('x-model', $attributes['x-model'] . '.id');
                            $this->processNodeFormElement($input, $item);
                            $input->setAttribute(':id', "'".$attributes['item_name_key']."-'+".$attributes['repeaterId']."Index".((isset($attributes['parent_id']))?"+'-'+".$attributes['parent_id'].'Index':''));
                        } else {
                            $this->processNodeFormElement($input, $item);
                        }
                        break;
                }
            }
        }

        if (isset($attributes['repeater_link']) && $attributes['repeater_link'] === true) {
            $links = $dom->getElementsByTagName('a');
            foreach ($links as $link) {
                if ($link instanceof DOMElement) {
                    $onclick = $link->getAttribute('onclick');
                    $groups = explode('.',$attributes['groups']);
                    $linkAttributes = '';
                    if (str_contains($onclick, 'openLinkMap')) {
                        $linkAttributes = "addLink('".$attributes['item_name_key']."-'+".$attributes['repeaterId']."Index, ".$attributes['repeaterId']."Index, '".$attributes['item_name_key']."')";
                        if (!is_null($attributes['parent_id'])) {
                            $linkAttributes = "addLink('".$attributes['item_name_key']."-'+".$attributes['repeaterId']."Index+'-'+".$attributes['parent_id']."Index, ".$attributes['parent_id']."Index, '".$attributes['item_name_key']."', '".((isset($groups[1])) ? $groups[1] : '')."', ".$attributes['repeaterId']."Index)";
                        }
                    }
                    if (str_contains($onclick, 'deleteREXLink')) {
                        $linkAttributes = "removeLink(".$attributes['repeaterId']."Index, '".$attributes['item_name_key']."')";
                        if (!is_null($attributes['parent_id'])) {
                            $linkAttributes = "removeLink(".$attributes['parent_id']."Index, '".$attributes['item_name_key']."', '".((isset($groups[1])) ? $groups[1] : '')."', ".$attributes['repeaterId']."Index)";
                        }
                    }
                    if (str_contains($onclick, 'Media')) {
                        $method = 'openMedia';
                        if (str_contains($onclick, 'addREXMedia(')) $method = 'addMedia';
                        if (str_contains($onclick, 'deleteREXMedia(')) $method = 'deleteMedia';
                        if (str_contains($onclick, 'viewREXMedia(')) $method = 'viewMedia';
                        if (str_contains($onclick, 'openREXMedialist(')) $method = 'openMedialist';
                        if (str_contains($onclick, 'addREXMedialist(')) $method = 'addMedialist';
                        if (str_contains($onclick, 'deleteREXMedialist(')) $method = 'deleteMedialist';
                        if (str_contains($onclick, 'viewREXMedialist(')) $method = 'viewMedialist';
                        $linkAttributes = "$method('".$attributes['item_name_key']."-".$attributes['repeaterId']."-'+".$attributes['repeaterId']."Index, ".$attributes['repeaterId']."Index, '".$attributes['item_name_key']."')";
                        if (!is_null($attributes['parent_id']) && $attributes['parent_id'] != $attributes['repeaterId']) {
                            $linkAttributes = "$method('".$attributes['item_name_key']."-".$attributes['repeaterId']."-'+".$attributes['repeaterId']."Index+'-".$attributes['parent_id']."-'+".$attributes['parent_id']."Index, ".$attributes['parent_id']."Index, '".$attributes['item_name_key']."', '".((isset($groups[1])) ? $groups[1] : '')."', ".$attributes['repeaterId']."Index)";
                        }
                    }
                    $link->setAttribute('click.prevent', $linkAttributes);
                    $link->removeAttribute('onclick');
                }
            }
        }
    }

    private function addClickPrevent(string $body, array $attributes): string
    {
        if (isset($attributes['repeater_link']) && $attributes['repeater_link'] === true) {
            $body = str_replace('click.prevent', '@click.prevent', $body);
        }
        return $body;
    }

    private function processNodeFormElements(DOMNodeList $elements, MFormItem $item, $id = null): void
    {
        foreach ($elements as $element) {
            if ($element instanceof DOMElement) {
                $this->processNodeFormElement($element, $item, $id);
            }
        }
    }

    private function processNodeFormElement(DOMElement $element, MFormItem $item, $id = null): void
    {
        if (is_array($item->getAttributes()) && sizeof($item->getAttributes()) > 0) {
            foreach ($item->getAttributes() as $key => $value) {
                if (is_array($value)) {
                    foreach ($value as $vKey => $vValue) {
                        if (!is_array($vValue)) {
                            $element->setAttribute($vKey, (string) $vValue);
                        }
                    }
                } else {
                    $element->setAttribute($key, (string) $value);
                }
            }
        }
        if (!is_null($id)) {
            $element->setAttribute('id', $id);
        }
    }

    private function getWidgetId(MFormItem $item): string
    {
        $item->setVarId(substr($item->getVarId(), 1, -1));
        $varId = explode('][', $item->getVarId());

        foreach ($varId as $key => $val) {
            if (!is_numeric($val)) {
                $varId[$key] = abs(crc32($val));
            }
        }

        return (string) abs(crc32(implode('', $varId)));
    }

    private function generateCustomLinkElement(MFormItem $item): void
    {
        // default manipulations
        $this->executeDefaultManipulations($item, false, false);

        foreach (['intern' => 'enable', 'extern' => 'enable', 'media' => 'enable', 'mailto' => 'enable', 'tel' => 'disable', 'anchor' => 'enable'] as $key => $value) {
            $value = (((isset($item->getAttributes()['data-' . $key])) ? $item->getAttributes()['data-' . $key] : $value) == 'enable');
            $key = ('extern' == $key) ? 'external' : $key;
            $key = ('tel' == $key) ? 'phone' : $key;
            $item->setParameter(array_merge($item->getParameter(), [$key => $value]));
        }
        foreach (['data-media-type' => 'types', 'data-extern-link-prefix' => 'external_prefix', 'data-link-category' => 'category', 'data-media-category' => 'media_category'] as $data => $key) {
            if (isset($item->getAttributes()[$data])) {
                $item->setParameter(array_merge($item->getParameter(), [$key => $item->getAttributes()[$data]]));
            }
        }

        $item->setId(str_replace(['_', ']', '['], '', random_int(100, 999) . $item->getVarId()));

        // create templateElement object
        $templateElement = new MFormElement();
        $templateElement->setLabel($this->parseElement($this->createLabelElement($item), 'base'));

        $parameter = $item->getParameter();
        $attributes = $item->getAttributes();

        if (!isset($parameter['ylink']) && isset($item->getAttributes()['ylink'])) {
            $parameter['ylink'] = $item->getAttributes()['ylink'];
        }
        $parameter['class'] = $item->class;

        $div = null;
        $html = '';

        try {
            $html = rex_var_custom_link::getWidget($item->getId(), 'REX_INPUT_VALUE' . $item->getVarId(), $item->getValue(), $parameter, false);
            $dom = new DOMDocument('1.0', 'utf-8');
            @$dom->loadHTML('<?xml encoding="utf-8" ?>' . $html); // utf8_decode($html)
            $div = $dom->getElementsByTagName('div');
            $inputs = $dom->getElementsByTagName('input');
            $this->prepareLinkInput($dom, $inputs, $item, $attributes);
        } catch (Exception $e) {
            rex_logger::logException($e);
        }

        if ($div instanceof DOMNodeList) {
            foreach ($div as $divItem) {
                if ($divItem instanceof DOMElement && $divItem->hasChildNodes()) {
                    $divItem->setAttribute('data-id', $item->getId());
                    $divItem->setAttribute('data-clang', rex_clang::getCurrentId());
                    $divItem->setAttribute('class', $divItem->getAttribute('class') . ' custom-link');
                    /** @var DOMElement $childNode */
                    foreach ($divItem->childNodes as $childNode) {
                        if (($childNode->hasAttribute('class')
                                && 'form-control' == $childNode->getAttribute('class'))
                            && ($childNode->hasAttribute('value')
                                && '' == $childNode->getAttribute('value'))) {
                            $childNode->setAttribute('value', $item->getValue());
                            if (is_array($item->getAttributes()) && count($item->getAttributes()) > 0) {
                                foreach ($item->getAttributes() as $key => $value) {
                                    $childNode->setAttribute($key, $value);
                                }
                            }
                        }
                    }
                    $html = $this->getBodyInner($divItem);
                    break;
                }
            }
        }
        $templateElement->setElement($html)
            ->setNotice($item->getNotice())
            ->setType($this->getDefaultTemplateType($item, $templateElement));

        $templateElement = rex_extension::registerPoint(
            new rex_extension_point('mform/mformParser.generateCustomLinkElement', $templateElement, [
                'item' => $item,
            ])
        );

        // add to output element array
        $this->elements[] = $this->parseElement($templateElement, 'default');
    }

    private function executeDefaultManipulations(MFormItem $item, bool $setCustomId = true, bool $setDefaultClass = true): void
    {
        MFormItemManipulator::setVarAndIds($item); // transform ids for template usage
        if ($setCustomId) MFormItemManipulator::setCustomId($item); // set optional custom id
        if ($setDefaultClass) MFormItemManipulator::setDefaultClass($item); // set default class for r5 mform default theme
    }

    private function getBodyInner(DOMDocument|DOMElement $dom): bool|string
    {
        $html = $dom->C14N(false, true);
        if (str_contains($html, '<body')) {
            preg_match('/<body>(.*)<\\/body>/ism', $html, $matches);
            if (isset($matches[1])) {
                $html = $matches[1];
            }
        }
        return $html;
    }

    /**
     * @param MFormItem[] $items
     */
    private function parseFormFields(array $items): void
    {
        try {
            if (count($items) > 0) {
                $skipKeys = [];
                foreach ($items as $key => $item) {

                    if (in_array($key, $skipKeys, true)) {
                        continue;
                    }

                    if ($item instanceof MForm) {
                        $mformItem = new MFormItem();
                        $mformItem->setType('html')
                            ->setValue($item->show());
                        $item = $mformItem;
                    }

                    if ($item instanceof MFormItem) {
                        switch ($item->getType()) {
                            // OPEN REPEATER
                            case 'repeater':
                                $this->openRepeaterElement($item, $key, $items, $skipKeys);
                                break;
                            // CLOSE REPEATER
                            case 'close-repeater':
                                $this->closeRepeaterElement($item);
                                break;

                            // OPEN WRAPPER ELEMENT
                            case 'tab':
                            case 'fieldset':
                            case 'collapse':
                            case 'inline':
                            case 'column':
                            case 'start-group-tab':
                            case 'start-group-collapse':
                            case 'start-group-inline':
                            case 'start-group-column':
                                $this->openWrapperElement($item, $key, $items);
                                break;
                            // CLOSE WRAPPER ELEMENT
                            case 'close-tab':
                            case 'close-fieldset':
                            case 'close-collapse':
                            case 'close-inline':
                            case 'close-column':
                            case 'close-group-tab':
                            case 'close-group-collapse':
                            case 'close-group-inline':
                            case 'close-group-column':
                                $this->closeWrapperElement($item->getType());
                                break;

                            // FORM ELEMENTS
                            case 'html':
                            case 'headline':
                            case 'description':
                            case 'alert':
                                $this->generateLineElement($item);
                                break;
                            case 'color':
                            case 'email':
                            case 'url':
                            case 'tel':
                            case 'search':
                            case 'number':
                            case 'range':
                            case 'date':
                            case 'time':
                            case 'datetime':
                            case 'datetime-local':
                            case 'month':
                            case 'week':
                            case 'text':
                            case 'text-readonly':
                                $this->generateInputElement($item);
                                break;
                            case 'hidden':
                                $this->generateHiddenInputElement($item);
                                break;
                            case 'markitup':
                            case 'textarea':
                            case 'textarea-readonly':
                                $this->generateAreaElement($item);
                                break;
                            case 'select':
                            case 'multiselect':
                                $this->generateOptionsElement($item);
                                break;
                            case 'radio':
                                $this->generateRadioElement($item);
                                break;
                            case 'checkbox':
                            case 'multicheckbox':
                                $this->generateCheckboxElement($item);
                                break;
                            case 'link':
                            case 'linklist':
                                $this->generateLinkElement($item);
                                break;
                            case 'custom-link':
                                $this->generateCustomLinkElement($item);
                                break;
                            case 'media':
                            case 'medialist':
                            case 'imglist':
                                $this->generateMediaElement($item);
                                break;
                        }
                    }
                }
            }
        } catch (Exception $e) {
            rex_logger::logException($e);
        }
    }

    private function createLabelElement(MFormItem $item): MFormElement
    {
        $this->createTooltipElement($item);

        $labelString = $item->getLabel();
        if (is_array($item->getLabel())) {
            foreach ($item->getLabel() as $key => $itemLabel) {
                if (str_contains(rex_i18n::getLocale(), $key)) {
                    $labelString = $itemLabel;
                }
            }
            if (is_array($labelString)) {
                $labelString = array_values($labelString)[0];
            }
        }

        $label = new MFormElement();
        $label->setId($item->getId());

        if (array_key_exists(':id', $item->getAttributes())) {
            $label->setId($item->getId() . '" :for="'.$item->getAttributes()[':id']);
        }

        $label->setValue($labelString)
            ->setType('label');

        return $label;
    }

    private function createTooltipElement(MFormItem $item): void
    {
        // set tooltip
        if ($item->getInfoTooltip()) {
            // parse tooltip
            if (empty($item->getInfoTooltipIcon())) {
                $item->setInfoTooltipIcon('fa-exclamation');
            }

            $tooltip = new MFormElement();
            $tooltip->setValue($item->getInfoTooltip())
                ->setInfoTooltipIcon($item->getInfoTooltipIcon())
                ->setType('tooltip-info');

            $item->setLabel($item->getLabel() . $this->parseElement($tooltip, 'base'));
        }
    }

    public function parse(array $items, ?string $theme = null, bool $showWrapper = true, bool $debug = false): string
    {
        $this->debug = $debug;
        if (!is_null($theme) && $theme != $this->theme) {
            $this->theme = $theme;
        }

        $items = MFormGroupExtensionHelper::addColumnGroupExtensionItems($items);
        $items = MFormGroupExtensionHelper::addTabGroupExtensionItems($items);
        $items = MFormGroupExtensionHelper::addCollapseGroupExtensionItems($items);
        $items = MFormGroupExtensionHelper::addAccordionGroupExtensionItems($items);

        // show for debug items
        if ($this->debug) {
            dump(['items' => $items, 'theme' => $this->theme]);
        }

        $this->parseFormFields($items);

        if ($showWrapper) {
            // wrap elements
            $element = new MFormElement();
            $element->setOutput(implode('', $this->elements))
                ->setType('wrapper');
            // return output
            $output = $this->parseElement($element, 'wrapper');
        } else {
            $output = implode('', $this->elements);
        }
        return $output;
    }

    private function getDefaultTemplateType(MFormItem $item, MFormElement $templateElement): string
    {
        $templateType = 'default';

        // set default template
        if (!empty($item->getLabelColClass()) && !empty($item->getFormItemColClass())) {
            $templateType .= '_custom'; // add _custom to template type
            $templateElement->setLabelColClass($item->getLabelColClass())
                ->setFormItemColClass($item->getFormItemColClass());
        }

        // is full flag true and template type default
        if ($item->isFull()) {
            $templateType .= '_full'; // add _full to template type
        }

        return $templateType;
    }

    private function parseElement(MFormElement $element, string $fragmentType): string
    {
        $element->setValue((is_array($element->value)) ? '' : $element->value);

        $fragment = new rex_fragment();

        $keys = $element->getKeys();
        $vals = $element->getValues();

        foreach ($keys as $index => $key) {
            $fragment->setVar($key, $vals[$index], false);
        }

        try {
            return $fragment->parse($this->theme . '/mform_' . $fragmentType . '.php');
        } catch (Exception $e) {
            rex_logger::logException($e);
            return rex_view::error($e->getMessage());
        }
    }

    private function parseAttributes(array $attributes): string
    {
        $inlineAttributes = '';
        if (count($attributes) > 0) {
            foreach ($attributes as $key => $value) {
                if (!in_array($key, ['id', 'name', 'type', 'value', 'checked', 'selected'])) {
                    $inlineAttributes .= ' ' . $key . '="' . $value . '"';
                }
            }
        }
        return $inlineAttributes;
    }
}
