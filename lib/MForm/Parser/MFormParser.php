<?php
/**
 * @author Joachim Doerr
 * @package redaxo5
 * @license MIT
 */

namespace MForm\Parser;

use DOMDocument;
use DOMElement;
use DOMNodeList;
use Exception;
use MForm\DTO\MFormElement;
use MForm\DTO\MFormItem;
use MForm\Handler\MFormAttributeHandler;
use MForm\Utils\MFormGroupExtensionHelper;
use MForm\Utils\MFormItemManipulator;
use rex_clang;
use rex_exception;
use rex_extension;
use rex_extension_point;
use rex_fragment;
use rex_i18n;
use rex_logger;
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

    protected string $theme = 'default_theme';

    protected bool $acc = false;

    private bool $inline = false;

    /**
     * @author Joachim Doerr
     */
    private function openWrapperElement(MFormItem $item, string $key, array $items): void
    {
        $element = new MFormElement();
        $attributes = $item->getAttributes();
        $removeAttributes = [];
        $mformCount = '';

        try {
            $mformCount = rex_session('mform_count', 'int', '0');
        } catch (rex_exception $e) {
            rex_logger::logException($e);
        }

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
                ->setValue($item->getLabel());
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
                if ($k > $key && ($itm->getGroup() == $item->getGroup() && 'tab' == $itm->getType())) {
                    // add navigation item
                    $element = new MFormElement();
                    $element->setType('tabnavli')
                        ->setValue($itm->getGroup() . $itm->getGroupCount() . '_' . $mformCount)
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
            $attributes['data-tab-group-nav-tab-id'] = $item->getGroup() . $item->getGroupCount() . '_' . $mformCount;
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

    /**
     * @author Joachim Doerr
     */
    private function closeWrapperElement(string $type): void
    {
        $element = new MFormElement();
        $element->setType($type);
        $this->elements[] = $this->parseElement($element, 'wrapper');
    }

    /**
     * create any no input inline element
     * html, headline, description.
     * @author Joachim Doerr
     */
    private function generateLineElement(MFormItem $item): void
    {
        // create templateElement object
        $element = new MFormElement();
        $element->setOutput($item->getValue())
            ->setAttributes($this->parseAttributes($item->getAttributes()))
            ->setClass($item->getClass()) // set output to replace in template
            ->setType($item->getType());

        // add to output element array
        $this->elements[] = $this->parseElement($element, 'base');
    }

    /**
     * create hidden input element.
     * @author Joachim Doerr
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
            ->setValue($item->getValue())
            ->setType($item->getType())
            ->setClass($item->getClass())
            ->setAttributes($this->parseAttributes($item->getAttributes())); // parse attributes for use in templates

        // add to output element array
        $this->elements[] = $this->parseElement($element, 'input');
    }

    /**
     * create input text element
     * text, password.
     * @author Joachim Doerr
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
            ->setValue($item->getValue())
            ->setType($item->getType())
            ->setClass($item->getClass())
            ->setDatalist($datalist)
            ->setAttributes($this->parseAttributes($item->getAttributes())); // parse attributes for use in templates

        // create templateElement object
        $templateElement = new MFormElement();
        $templateElement->setLabel($this->parseElement($this->createLabelElement($item), 'base'))
            ->setElement($this->parseElement($element, 'input'))
            ->setType($this->getDefaultTemplateType($item, $templateElement));

        // add to output element array
        $this->elements[] = $this->parseElement($templateElement, 'default');
    }

    /**
     * create textarea element
     * textarea.
     * @author Joachim Doerr
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
            ->setValue($item->getValue())
            ->setType($item->getType())
            ->setClass($item->getClass())
            ->setAttributes($this->parseAttributes($item->getAttributes()));

        // create templateElement object
        $templateElement = new MFormElement();
        $templateElement->setLabel($this->parseElement($this->createLabelElement($item), 'base'))
            ->setElement($this->parseElement($element, 'textarea'))
            ->setType($this->getDefaultTemplateType($item, $templateElement));

        // add to output element array
        $this->elements[] = $this->parseElement($templateElement, 'default');
    }

    /**
     * create select or multiselect element
     * select, multiselect.
     * @author Joachim Doerr
     */
    private function generateOptionsElement(MFormItem $item): void
    {
        // default manipulations
        $this->executeDefaultManipulations($item);

        // init option element string
        $optionElements = '';
        $attributes = $item->getAttributes();
        if (count($item->getToggleOptions()) >= 0) {
            $attributes = array_merge(['data-toggle' => 'collapse'], $attributes);
        }
        $itemAttributes = $this->parseAttributes($attributes); // parse attributes for output

        if ($item->isMultiple() && is_array($item->getValue()) &&
            count($item->getValue()) == count($item->getValue(), COUNT_RECURSIVE)) {
            $item->setValue(implode(',', $item->getValue()));
        }

        // options must te be given
        if (count($item->getOptions()) > 0) {
            // size count
            $count = 0;
            foreach ($item->getOptions() as $key => $value) {
                // is value label we have a opt group
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
            ->setValue($item->getValue())
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
            ->setType($this->getDefaultTemplateType($item, $templateElement));

        // add to output element array
        $this->elements[] = $this->parseElement($templateElement, 'default');
    }

    /**
     * helper method to create option elements.
     * @author Joachim Doerr
     */
    private function createOptionElement(MFormItem $item, $key, $value, string $templateType = 'option', bool $selected = true, bool $disabled = false, string $toggle = ''): string
    {
        // create element
        $element = new MFormElement();
        $element->setValue($key)// set option key
        ->setLabel($value) // set option label
        ->setType($templateType);

        if (!empty($toggle)) {
            $element->setAttributes($this->parseAttributes(['data-toggle-item' => $toggle]));
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

            $current = explode('][', trim($item->varId, '[]'));

            // JSON Values 1.x
            if (isset($current[1]) && isset($items_selected[$current[1]]) && is_array($items_selected[$current[1]]) && in_array((string) $key, $items_selected[$current[1]])) {

                $element->setAttributes($element->attributes . ' selected');

                // JSON Values 1.x.x
            } elseif (isset($current[2]) && isset($items_selected[$current[1]][$current[2]]) && is_array($items_selected[$current[1]][$current[2]]) && in_array((string) $key, $items_selected[$current[1]][$current[2]])) {

                $element->setAttributes($element->attributes . ' selected');

                // REX_VAL
            } elseif (!isset($current[1]) && isset($items_selected) && is_array($items_selected) && in_array((string) $key, $items_selected)) {
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
     * create checkbox element
     * checkbox.
     * @author Joachim Doerr
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
            ->setType($this->getDefaultTemplateType($item, $templateElement));

        // add to output element array
        $this->elements[] = $this->parseElement($templateElement, 'default');
    }

    /**
     * helper method to create checkbox and radiobutton elements
     * checkbox, radiobutton.
     * @author Joachim Doerr
     */
    private function createCheckElement(MFormItem $item, $key, $value, ?int $count = null): string
    {
        // create element
        $element = new MFormElement();
        $element->setValue($key)
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
        if (count($item->getToggleOptions()) > 0) {
            $attributes['data-toggle-item'] = (array_key_exists($key, $item->getToggleOptions())) ? $item->getToggleOptions()[$key] : '';
        }
        if (isset($attributes['data-toggle-item'])) {
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
     * create radiobutton element
     * radiobutton.
     * @author Joachim Doerr
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
            ->setType($this->getDefaultTemplateType($item, $templateElement));

        // add to output element array
        $this->elements[] = $this->parseElement($templateElement, 'default');
    }

    /**
     * media, medialist.
     * @author Joachim Doerr
     */
    private function generateMediaElement(MFormItem $item): void
    {
        $inputValue = false;

        if (is_array($item->getVarId()) && count($item->getVarId()) > 0) {
            if (count($item->getVarId()) > 1) {
                $inputValue = true;
            }
            $this->executeDefaultManipulations($item, true, false, false);
        }

        // create templateElement object
        $templateElement = new MFormElement();
        $templateElement->setLabel($this->parseElement($this->createLabelElement($item), 'base'));
        $parameter = $item->getParameter();

        if (is_array($parameter) && isset($parameter['types'])) {
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

                if ($inputs instanceof DOMNodeList) {
                    $this->processNodeFormElement($inputs, $item, 'REX_MEDIA_' . (int) $id);
                }
                break;
            case 'imglist':
            case 'medialist':
                $inputValue = ($inputValue) ? 'REX_INPUT_VALUE' : 'REX_INPUT_MEDIALIST';
                $id = $this->getWidgetId($item);
                /** @var rex_var_medialist|rex_var_imglist $class */
                $class = 'rex_var_' . $item->getType();
                $value = (!is_string($item->getValue())) ? '' : $item->getValue();
                $html = $class::getWidget($id, $inputValue . '[' . $item->getVarId() . ']', $value, $parameter);

                $dom = new DOMDocument();
                @$dom->loadHTML(utf8_decode($html));
                $selects = $dom->getElementsByTagName('select');
                $inputs = $dom->getElementsByTagName('input');

                if ($selects instanceof DOMNodeList) {
                    $this->processNodeFormElement($selects, $item, 'REX_MEDIALIST_SELECT_' . $id);
                }
                if ($inputs instanceof DOMNodeList) {
                    $this->processNodeFormElement($inputs, $item, 'REX_MEDIALIST_' . $id);
                }
                break;
        }

        // get body inner
        $templateElement->setElement($this->getBodyInner($dom))
            ->setType($this->getDefaultTemplateType($item, $templateElement));

        // add to output element array
        $this->elements[] = $this->parseElement($templateElement, 'default');
    }

    /**
     * link, linklist.
     * @author Joachim Doerr
     */
    private function generateLinkElement(MFormItem $item): void
    {
        $inputValue = false;

        if (is_array($item->getVarId()) && count($item->getVarId()) > 0) {
            if (count($item->getVarId()) > 1) {
                $inputValue = true;
            }
            $this->executeDefaultManipulations($item, true, false, false);
        }

        // create templateElement object
        $templateElement = new MFormElement();
        $templateElement->setLabel($this->parseElement($this->createLabelElement($item), 'base'));
        $parameter = $item->getParameter();

        if (is_array($parameter) && isset($parameter['types'])) {
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

                if ($inputs instanceof DOMNodeList) {
                    $this->processNodeFormElement($inputs, $item, 'REX_LINK_' . (int) $id);
                }
                if ($inputs instanceof DOMNodeList) {
                    foreach ($inputs as $input) {
                        if ($input instanceof DOMElement) {
                            if ('text' == $input->getAttribute('type')) {
                                $input->setAttribute('id', $input->getAttribute('id') . '_NAME');
                            }
                        }
                    }
                }

                break;
            case 'linklist':
                $inputValue = ($inputValue) ? 'REX_INPUT_VALUE' : 'REX_INPUT_LINKLIST';
                $id = $this->getWidgetId($item);
                $html = rex_var_linklist::getWidget($id, $inputValue . '[' . $item->getVarId() . ']', $item->getValue(), $parameter);

                $dom = new DOMDocument('1.0', 'utf-8');
                @$dom->loadHTML('<?xml encoding="utf-8" ?>' . $html); // utf8_decode($html)
                $selects = $dom->getElementsByTagName('select');
                $inputs = $dom->getElementsByTagName('input');

                if ($selects instanceof DOMNodeList) {
                    $this->processNodeFormElement($selects, $item, 'REX_LINKLIST_SELECT_' . $id);
                }
                if ($inputs instanceof DOMNodeList) {
                    $this->processNodeFormElement($inputs, $item, 'REX_LINKLIST_' . $id);
                }
                break;
        }

        // get body inner
        $templateElement->setElement($this->getBodyInner($dom))
            ->setType($this->getDefaultTemplateType($item, $templateElement));

        // add to output element array
        $this->elements[] = $this->parseElement($templateElement, 'default');
    }

    /**
     * @param null $id
     * @author Joachim Doerr
     */
    private function processNodeFormElement(DOMNodeList $elements, MFormItem $item, $id = null): void
    {
        foreach ($elements as $element) {
            if ($element instanceof DOMElement) {
                if (is_array($item->getAttributes()) && count($item->getAttributes()) > 0) {
                    foreach ($item->getAttributes() as $key => $value) {
                        $element->setAttribute($key, $value);
                    }
                }
                if (null !== $id) {
                    $element->setAttribute('id', $id);
                }
            }
        }
    }

    /**
     * @author Joachim Doerr
     */
    private function getWidgetId(MFormItem $item): string
    {
        $item->setVarId(substr($item->getVarId(), 1, -1));
        $varId = explode('][', $item->getVarId());

        foreach ($varId as $key => $val) {
            if (!is_numeric($val)) {
                $varId[$key] = random_int(0, strlen($val) * random_int(0, getrandmax()));
            }
        }

        return implode('', $varId);
    }

    /**
     * link, linklist.
     * @author Joachim Doerr
     */
    private function generateCustomLinkElement(MFormItem $item): void
    {
        // default manipulations
        $this->executeDefaultManipulations($item, true, false, false);

        foreach (['intern' => 'enable', 'extern' => 'enable', 'media' => 'enable', 'mailto' => 'enable', 'tel' => 'disable'] as $key => $value) {
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
        if (!isset($parameter['ylink']) && isset($item->getAttributes()['ylink'])) {
            $parameter['ylink'] = $item->getAttributes()['ylink'];
        }
        $parameter['class'] = $item->class;

        $div = null;

        try {
            $html = rex_var_custom_link::getWidget($item->getId(), 'REX_INPUT_VALUE' . $item->getVarId(), $item->getValue(), $parameter, false);
            $dom = new DOMDocument('1.0', 'utf-8');
            @$dom->loadHTML('<?xml encoding="utf-8" ?>' . $html); // utf8_decode($html)
            $div = $dom->getElementsByTagName('div');
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
            ->setType($this->getDefaultTemplateType($item, $templateElement));

        $templateElement = rex_extension::registerPoint(
            new rex_extension_point('mform/mformParser.generateCustomLinkElement', $templateElement, [
                'item' => $item,
            ]),
        );

        // add to output element array
        $this->elements[] = $this->parseElement($templateElement, 'default');
    }

    /**
     * @author Joachim Doerr
     */
    private function executeDefaultManipulations(MFormItem $item, bool $setVarAndIds = true, bool $setCustomId = true, bool $setDefaultClass = true): void
    {
        if ($setVarAndIds) {
            MFormItemManipulator::setVarAndIds($item);
        } // transform ids for template usage
        if ($setCustomId) {
            MFormItemManipulator::setCustomId($item);
        } // set optional custom id
        if ($setDefaultClass) {
            MFormItemManipulator::setDefaultClass($item);
        } // set default class for r5 mform default theme
    }

    /**
     * @param DOMDocument|DOMElement $dom
     * @return mixed
     * @author Joachim Doerr
     */
    private function getBodyInner($dom)
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
     * @author Joachim Doerr
     */
    private function parseFormFields(array $items): void
    {
        try {
            if (count($items) > 0) {
                foreach ($items as $key => $item) {
                    switch ($item->getType()) {
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
                        case 'customlink':
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
        } catch (Exception $e) {
            rex_logger::logException($e);
        }
    }

    /**
     * @author Joachim Doerr
     */
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
        $label->setId($item->getId())
            ->setValue($labelString)
            ->setType('label');

        return $label;
    }

    /**
     * @author Joachim Doerr
     */
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

    /**
     * final parsing.
     * @param MFormItem[] $items
     * @author Joachim Doerr
     */
    public function parse(array $items, ?string $theme = null, bool $debug = false, bool $inline = false): string
    {
        $this->inline = $inline;

        if (null !== $theme && $theme != $this->theme) {
            $this->theme = $theme;
        }

        $items = MFormGroupExtensionHelper::addInlineGroupExtensionItems($items);
        $items = MFormGroupExtensionHelper::addColumnGroupExtensionItems($items);
        $items = MFormGroupExtensionHelper::addTabGroupExtensionItems($items);
        $items = MFormGroupExtensionHelper::addCollapseGroupExtensionItems($items);
        $items = MFormGroupExtensionHelper::addAccordionGroupExtensionItems($items);

        // show for debug items
        if ($debug) {
            dump(['items' => $items, 'theme' => $this->theme, 'inline' => $this->inline, 'debug' => $debug]);
        }

        $this->parseFormFields($items);

        // wrap elements
        $element = new MFormElement();
        $element->setOutput(implode('', $this->elements))
            ->setType('wrapper');

        // return output
        return $this->parseElement($element, 'wrapper');
    }

    /**
     * @author Joachim Doerr
     */
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

    /**
     * @author Joachim Doerr
     */
    private function parseElement(MFormElement $element, string $fragmentType): string
    {
        $element->setValue((is_array($element->value)) ? '' : $element->value)
            ->setInline($this->inline);

        $fragment = new rex_fragment();

        $keys = $element->getKeys();
        $vals = $element->getValues();

        foreach ($keys as $index => $key) {
            $fragment->setVar($key, $vals[$index], false);
        }

        try {
            return $fragment->parse($this->theme . '/mform_' . $fragmentType . '.php');
        } catch (rex_exception $e) {
            rex_logger::logException($e);
            return rex_view::error($e->getMessage());
        }
    }

    /**
     * @author Joachim Doerr
     */
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
