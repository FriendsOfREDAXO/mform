<?php
/**
 * @author mail[at]doerr-softwaredevelopment[dot]com Joachim Doerr
 * @package redaxo5
 * @license MIT
 */

namespace MForm\Parser;


use DOMDocument;
use DOMElement;
use DOMNodeList;
use MForm\DTO\MFormElement;
use MForm\DTO\MFormItem;
use MForm\Handler\MFormAttributeHandler;
use MForm\Utils\MFormGroupExtensionHelper;
use MForm\Utils\MFormItemManipulator;
use rex_addon;
use rex_clang;
use rex_fragment;
use rex_var_custom_link;
use rex_var_imglist;
use rex_var_link;
use rex_var_linklist;
use rex_var_media;
use rex_var_medialist;

class MFormParser
{
    /**
     * @var array
     */
    protected array $elements = array();

    /**
     * @var string
     */
    protected string $theme;

    /**
     * @var bool
     */
    protected bool $acc = false;

    /**
     * @param MFormItem $item
     * @return $this
     * @author Joachim Doerr
     */
    private function generateFieldset(MFormItem $item): self
    {
        // set default class for r5 mform default theme
        MFormItemManipulator::setDefaultClass($item);

        // create fieldset open element
        $fieldsetElement = new MFormElement();
        $fieldsetElement
            ->setClass($item->getClass()) // set fieldset default and custom class
            ->setAttributes($this->parseAttributes($item->getAttributes())) // add attributes to fieldset element
            ->setType('fieldset-open');

        // create legend
        if (!empty($item->getValue())) {
            $legendElement = new MFormElement();
            $legendElement
                ->setValue($item->getValue())
                ->setType('legend');
            $fieldsetElement->setLegend($this->parseElement($legendElement, 'fieldset', true)); // add parsed legend to fieldset element
        }

        // add fieldset open element to elements list
        $this->elements[] = $this->parseElement($fieldsetElement, 'fieldset', true);
        return $this;
    }

    /**
     * @return $this
     * @author Joachim Doerr
     */
    private function closeFieldset()
    {
        $element = new MFormElement();
        $element->setType('fieldset-close');
        $this->elements[] = $this->parseElement($element, 'fieldset', true); // use parse element to load template file
        return $this;
    }

    /**
     * @param MFormItem $item
     * @param int $key
     * @param array $items
     * @return MFormParser
     * @author Joachim Doerr
     */
    private function generateTabGroup(MFormItem $item, $key, array $items)
    {
        $nav = array();

        /** @var MFormItem $itm */
        foreach ($items as $k => $itm) {
            if ($k > $key && ($itm->getGroup() == $item->getGroup() && $itm->getType() == 'tab')) {
                // add navigation item
                $class = $itm->getClass();
                $value = '';
                $element = new MFormElement();
                $element->setId('tabgr' . $itm->getGroup() . 'tabid' . $itm->getGroupCount() . '_' . rex_session('mform_count'));

                if (array_key_exists('tab-icon', $itm->getAttributes()))
                    $value = '<i class="rex-icon ' . $itm->getAttributes()['tab-icon'] . '"></i> ';

                $element->setValue($value . $itm->getValue());

                if (array_key_exists('pull-right', $itm->getAttributes()))
                    $class .= ' pull-right';

                if ($itm->getGroupCount() == 1)
                    $class .= ' active';

                $element->setClass($class)
                    ->setType('tabnavli');

                $nav[] = $this->parseElement($element, 'tab', true); // use parse element to load template file
            }
        }

        $element = new MFormElement();
        $element->setElement(implode('', $nav))
            ->setAttributes($this->parseAttributes($item->getAttributes()))
            ->setType('tabgroup-open');

        $this->elements[] = $this->parseElement($element, 'tab', true); // use parse element to load template file
        return $this;
    }

    /**
     * @param MFormItem $item
     * @return MFormParser
     * @author Joachim Doerr
     */
    private function generateTab($item)
    {
        $element = new MFormElement();
        $element->setId('tabgr' . $item->getGroup() . 'tabid' . $item->getGroupCount() . '_' . rex_session('mform_count'))
            ->setType('tab-open');

        if ($item->getGroupCount() == 1)
            $element->setClass('active');

        $this->elements[] = $this->parseElement($element, 'tab', true); // use parse element to load template file
        return $this;
    }

    /**
     * @return $this
     * @author Joachim Doerr
     */
    private function closeTab()
    {
        $element = new MFormElement();
        $element->setType('tab-close');
        $this->elements[] = $this->parseElement($element, 'tab', true); // use parse element to load template file
        return $this;
    }

    /**
     * @return $this
     * @author Joachim Doerr
     */
    private function closeTabGroup()
    {
        $element = new MFormElement();
        $element->setType('tabgroup-close');
        $this->elements[] = $this->parseElement($element, 'tab', true); // use parse element to load template file
        return $this;
    }

    /**
     * @param MFormItem $item
     * @return $this
     * @author Joachim Doerr
     */
    private function generateCollapseGroup(MFormItem $item)
    {
        if (isset($item->getAttributes()['data-group-accordion']) && $item->getAttributes()['data-group-accordion'] == 1) {
            $this->acc = true;
            $element = new MFormElement();
            $element->setAttributes($this->parseAttributes($item->getAttributes()))
                ->setId('accgr' . $item->getGroup() . '_' . rex_session('mform_count'))
                ->setType('accordion-open');
            $this->elements[] = $this->parseElement($element, 'collapse', true); // use parse element to load template file
        }
        return $this;
    }

    /**
     * @param MFormItem $item
     * @return $this
     * @author Joachim Doerr
     */
    private function generateCollapse(MFormItem $item)
    {
        // is id in attr not set set an unique id
        if (!isset($item->getAttributes()['id'])) {
            $item->attributes['id'] = 'colgr' . $item->getGroup() . 'colid' . $item->getGroupCount() . '_' . rex_session('mform_count');
        }

        // create collapse open element
        $collapseElement = new MFormElement();
        $collapseElement->setAttributes($this->parseAttributes($item->getAttributes())) // add attributes to collapse element
        ->setId($item->getAttributes()['id']) // set collapse id
        ->setType(($this->acc && isset($item->getAttributes()['data-group-accordion']) && $item->getAttributes()['data-group-accordion'] == 1) ? 'accordion-collapse-open' : 'collapse-open');

        if (array_key_exists('data-group-collapse', $item->getAttributes())) {
            $collapseElement->setClass($item->getAttributes()['data-group-collapse']);
        }

        // not class given set default button class
        if (empty($item->getClass())) {
            $item->setClass('btn btn-white btn-block');
        }

        if (empty($item->getValue()) or (array_key_exists('data-group-hide-toggle-links', $item->getAttributes()) && $item->getAttributes()['data-group-hide-toggle-links'] == 'true')) {
            $item->setClass($item->getClass() . ' hidden');
        }

        $target = ($this->acc && isset($item->getAttributes()['data-group-accordion']) && $item->getAttributes()['data-group-accordion'] == 1) ? ' data-parent="#accgr' . $item->getGroup() . '_' . rex_session('mform_count') . '"' : '';
        $attr = ($this->acc && isset($item->getAttributes()['data-select-collapse-id'])) ? ' data-select-collapse-id="' . $item->getAttributes()['data-select-collapse-id'] . '"' : '';
        $collapseButton = new MFormElement();
        $collapseButton->setClass($item->getClass())
            ->setAttributes('data-toggle="collapse" data-target="#' . $item->getAttributes()['id'] . '"' . $target . $attr)
            ->setValue($item->getValue())
            ->setType('collapse-button');

        $collapseElement->setLegend($this->parseElement($collapseButton, 'collapse', true)); // add parsed legend to collapse element

        // add collapse open element to elements list
        $this->elements[] = $this->parseElement($collapseElement, 'collapse', true);
        return $this;
    }

    /**
     * @param MFormItem $item
     * @return $this
     * @author Joachim Doerr
     */
    private function closeCollapse(MFormItem $item)
    {
        $element = new MFormElement();
        $element->setType(($this->acc) ? 'accordion-collapse-close' : 'collapse-close');
        $this->elements[] = $this->parseElement($element, 'collapse', true);
        return $this;
    }

    /**
     * @param MFormItem $item
     * @return $this
     * @author Joachim Doerr
     */
    private function closeCollapseGroup(MFormItem $item)
    {
        if ($this->acc) {
            $this->acc = false;
            $element = new MFormElement();
            $element->setType('accordion-close');
            $this->elements[] = $this->parseElement($element, 'collapse', true); // use parse element to load template file
        }

        return $this;
    }

    /**
     * create any no input inline element
     * html, headline, description
     * @param MFormItem $item
     * @return $this
     * @author Joachim Doerr
     */
    private function generateLineElement(MFormItem $item)
    {
        // create templateElement object
        $element = new MFormElement();
        $element->setOutput($item->getValue())
            ->setAttributes($this->parseAttributes($item->getAttributes()))
            ->setClass($item->getClass()) // set output to replace in template
            ->setType($item->getType());

        // add to output element array
        $this->elements[] = $this->parseElement($element, 'base');
        return $this;
    }

    /**
     * create input text element
     * hidden, text, password
     * @param MFormItem $item
     * @return $this
     * @author Joachim Doerr
     */
    private function generateInputElement(MFormItem $item)
    {
        $datalist = '';

        // set typ specific vars
        switch ($item->getType()) {
            case 'hidden': // is type hidden set template hidden
                $templateType = 'hidden';
                $item->setLabel(''); // and unset label
                break;
            case 'text-readonly': // is readonly
                MFormAttributeHandler::addAttribute($item, 'readonly', 'readonly'); // add attribute readonly
                break;
        }

        // datalist?
        if ($item->getOptions()) {
            $item->setAttributes(array_merge($item->getAttributes(), array('list' => 'list' . $item->getId())));

            $optionElements = '';
            foreach ($item->getOptions() as $key => $value) {
                $optionElements .= $this->createOptionElement($item, $value, (!is_integer($key)) ? "label=\"$key\"" : '', 'datalist-option', false);
            }
            $element = new MFormElement();
            $element->setOptions($optionElements)
                ->setId('list' . $item->getId())
                ->setType('datalist');
            $datalist = $this->parseElement($element, 'input', true);
        }

        // default manipulations
        MFormItemManipulator::setVarAndIds($item); // transform ids for template usage
        MFormItemManipulator::setCustomId($item); // set optional custom id
        MFormItemManipulator::setDefaultClass($item); // set default class for r5 mform default theme

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
        $templateElement->setLabel($this->parseElement($this->createLabelElement($item), 'base', true))
            ->setElement($this->parseElement($element, 'input', true))
            ->setType($this->getDefaultTemplateType($item, $templateElement));

        // add to output element array
        $this->elements[] = $this->parseElement($templateElement, 'default');
        return $this;
    }

    /**
     * create textarea element
     * textarea
     * @param $item
     * @return $this
     * @author Joachim Doerr
     */
    private function generateAreaElement(MFormItem $item)
    {
        // set typ specific vars
        switch ($item->getType()) {
            case 'textarea-readonly':
                $item->setType('textarea'); // type is textarea
                MFormAttributeHandler::addAttribute($item, 'readonly', 'readonly'); // add attribute readonly
                break;
        }

        // default manipulations
        MFormItemManipulator::setVarAndIds($item); // transform ids for template usage
        MFormItemManipulator::setCustomId($item); // set optional custom id
        MFormItemManipulator::setDefaultClass($item); // set default class for r5 mform default theme

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
        $templateElement->setLabel($this->parseElement($this->createLabelElement($item), 'base', true))
            ->setElement($this->parseElement($element, 'textarea', true))
            ->setType($this->getDefaultTemplateType($item, $templateElement));

        // add to output element array
        $this->elements[] = $this->parseElement($templateElement, 'default');
        return $this;
    }

    /**
     * create select or multiselect element
     * select, multiselect
     * @param MFormItem $item
     * @return $this
     * @author Joachim Doerr
     */
    private function generateOptionsElement(MFormItem $item)
    {
        // default manipulations0
        MFormItemManipulator::setVarAndIds($item); // transform ids for template usage
        MFormItemManipulator::setCustomId($item); // set optional custom id
        MFormItemManipulator::setDefaultClass($item); // set default class for r5 mform default theme

        // init option element string
        $optionElements = '';
        $itemAttributes = $this->parseAttributes($item->getAttributes()); // parse attributes for output

        if ($item->isMultiple() && is_array($item->getValue()) &&
            sizeof($item->getValue()) == count($item->getValue(), COUNT_RECURSIVE)) {
            $item->setValue(implode(',', $item->getValue()));
        }

        // options must te be given
        if (sizeof($item->getOptions()) > 0) {
            // size count
            $count = 0;
            foreach ($item->getOptions() as $key => $value) {
                // is value label we have a opt group
                if (is_array($value)) {
                    // optGroup set
                    $optGroupLabel = $key;
                    $optElements = '';
                    $count++; // + for group label

                    // create options
                    foreach ($value as $vKey => $vValue) {
                        $count++;
                        $disabled = false;
                        $toggle = [];
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

                    $optionElements .= $this->parseElement($groupElement, 'select', true);
                } else {
                    $count++;
                    $disabled = false;
                    $toggle = [];
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
            if ($item->getSize() == 'full') {
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
        $templateElement->setLabel($this->parseElement($this->createLabelElement($item), 'base', true))
            ->setElement($this->parseElement($element, 'select', true))
            ->setType($this->getDefaultTemplateType($item, $templateElement));

        // add to output element array
        $this->elements[] = $this->parseElement($templateElement, 'default');
        return $this;
    }

    /**
     * helper method to create option elements
     * @param MFormItem $item
     * @param $key
     * @param $value
     * @param string $templateType
     * @param bool $selected
     * @param bool $disabled
     * @return mixed
     * @author Joachim Doerr
     */
    private function createOptionElement(MFormItem $item, $key, $value, string $templateType = 'option', bool $selected = true, bool $disabled = false, $toggle = '')
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
        if ($item->getMode() == 'edit' && $item->isMultiple()) {
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
        if ($item->multiple) {
            $items_selected = [];
            $items_selected = json_decode($item->stringValue, true);

            $current = explode('][', trim($item->varId, '[]'));

            // JSON Values 1.x
            if (isset($current[1]) && isset($items_selected[$current[1]]) && is_array($items_selected[$current[1]]) && in_array((string)$key, $items_selected[$current[1]])) {

                $element->setAttributes($element->attributes . ' selected');

                // JSON Values 1.x.x
            } else if (isset($current[2]) && isset($items_selected[$current[1]][$current[2]]) && is_array($items_selected[$current[1]][$current[2]]) && in_array((string)$key, $items_selected[$current[1]][$current[2]])) {

                $element->setAttributes($element->attributes . ' selected');

                // REX_VAL
            } elseif (!isset($current[1]) && isset($items_selected) && is_array($items_selected) && in_array((string)$key, $items_selected)) {
                $element->setAttributes($element->attributes . ' selected');
            }
        } else {
            // set default value or selected
            if ($selected && ((string)$key == (string)$itemValue or ($item->getMode() == 'add' && (string)$key == (string)$item->getDefaultValue()))) {
                $element->setAttributes($element->attributes . ' selected'); // add attribute selected
            }
        }

        if ($disabled) {
            $element->setAttributes($element->attributes . ' disabled');
        }

        // parse element
        return $this->parseElement($element, ($templateType == 'datalist-option') ? 'input' : 'select', true);
    }

    /**
     * create checkbox element
     * checkbox
     * @param MFormItem $item
     * @return $this
     * @author Joachim Doerr
     */
    private function generateCheckboxElement(MFormItem $item)
    {
        // default manipulations
        MFormItemManipulator::setVarAndIds($item); // transform ids for template usage
        MFormItemManipulator::setCustomId($item); // set optional custom id
        MFormItemManipulator::setDefaultClass($item); // set default class for r5 mform default theme

        $checkboxElements = '';

        // options must te be given
        if (sizeof($item->getOptions()) > 0) {
            // is multiple flag true
            // if ($item->isMultiple()) {
            // TODO add hidden field and javascript and so fare
            // TODO add text element
            // } else {
            foreach ($item->getOptions() as $key => $value) {
                $checkboxElements .= $this->createCheckElement($item, $key, $value); // create element by helper
                break;
            }
            //}
        }

        // create templateElement object
        $templateElement = new MFormElement();
        $templateElement->setLabel($this->parseElement($this->createLabelElement($item), 'base', true))
            ->setElement($checkboxElements)
            ->setType($this->getDefaultTemplateType($item, $templateElement));

        // add to output element array
        $this->elements[] = $this->parseElement($templateElement, 'default');
        return $this;
    }

    /**
     * helper method to create checkbox and radiobutton elements
     * checkbox, radiobutton
     * @param MFormItem $item
     * @param $key
     * @param $value
     * @param null|int $count
     * @return mixed
     * @author Joachim Doerr
     */
    private function createCheckElement(MFormItem $item, $key, $value, $count = null)
    {
        // create element
        $element = new MFormElement();
        $element->setValue($key)
            ->setId($item->getId())
            ->setVarId($item->getVarId())
            ->setType($item->getType())
            ->setClass($item->getClass())
            ->setLabel($value);

        // add count to id
        if (is_numeric($count)) {
            $element->setId($item->getId() . $count);
        }
        // set checked by value or default value
        if ($key == $item->getValue() or ($item->getMode() == 'add' && $key == $item->getDefaultValue())) {
            $element->setAttributes(' checked="checked" ' . $this->parseAttributes($item->getAttributes()));
        } else {
            $element->setAttributes($this->parseAttributes($item->getAttributes()));
        }
        // parse element
        return $this->parseElement($element, 'input', true);
    }

    /**
     * create radiobutton element
     * radiobutton
     * @param MFormItem $item
     * @return $this
     * @author Joachim Doerr
     */
    private function generateRadioElement(MFormItem $item)
    {
        // default manipulations
        MFormItemManipulator::setVarAndIds($item); // transform ids for template usage
        MFormItemManipulator::setCustomId($item); // set optional custom id
        MFormItemManipulator::setDefaultClass($item); // set default class for r5 mform default theme

        $radioElements = '';
        // options must te be given
        if (sizeof($item->getOptions()) > 0) {
            $count = 0; // init count
            foreach ($item->getOptions() as $key => $value) {
                $count++; // + count
                $radioElements .= $this->createCheckElement($item, $key, $value, $count); // create element by helper
            }
        }

        // create templateElement object
        $templateElement = new MFormElement();
        $templateElement->setLabel($this->parseElement($this->createLabelElement($item), 'base', true))
            ->setElement($radioElements)
            ->setType($this->getDefaultTemplateType($item, $templateElement));

        // add to output element array
        $this->elements[] = $this->parseElement($templateElement, 'default');
        return $this;
    }

    /**
     * media, medialist
     * @param MFormItem $item
     * @return $this
     * @author Joachim Doerr
     */
    private function generateMediaElement(MFormItem $item)
    {
        $inputValue = false;

        if (is_array($item->getVarId()) && sizeof($item->getVarId()) > 0) {
            if (sizeof($item->getVarId()) > 1) {
                $inputValue = true;
            }
            MFormItemManipulator::setVarAndIds($item); // transform ids for template usage
        }

        // create templateElement object
        $templateElement = new MFormElement();
        $templateElement->setLabel($this->parseElement($this->createLabelElement($item), 'base', true));
        $parameter = $item->getParameter();

        if (is_array($parameter) && isset($parameter['types'])) {
            $parameter['types'] = str_replace(' ', '', strtolower($parameter['types']));
        }

        switch ($item->getType()) {
            default:
            case 'media':
                $inputValue = ($inputValue) ? 'REX_INPUT_VALUE' : 'REX_INPUT_MEDIA';
                $id = $this->getWidgetId($item);
                $html = rex_var_media::getWidget((int)$id, $inputValue . '[' . $item->getVarId() . ']', $item->getValue(), $parameter);

                $dom = new DOMDocument();
                @$dom->loadHTML(utf8_decode($html));
                $inputs = $dom->getElementsByTagName('input');

                if ($inputs instanceof DOMNodeList) $this->processNodeFormElement($inputs, $item, 'REX_MEDIA_' . (int)$id);
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

                if ($selects instanceof DOMNodeList) $this->processNodeFormElement($selects, $item, 'REX_MEDIALIST_SELECT_' . $id);
                if ($inputs instanceof DOMNodeList) $this->processNodeFormElement($inputs, $item, 'REX_MEDIALIST_' . $id);
                break;
        }

        // get body inner
        $templateElement->setElement($this->getBodyInner($dom))
            ->setType($this->getDefaultTemplateType($item, $templateElement));

        // add to output element array
        $this->elements[] = $this->parseElement($templateElement, 'default');
        return $this;
    }

    /**
     * link, linklist
     * @param MFormItem $item
     * @return $this
     * @author Joachim Doerr
     */
    private function generateLinkElement(MFormItem $item)
    {
        $inputValue = false;

        if (is_array($item->getVarId()) && sizeof($item->getVarId()) > 0) {
            if (sizeof($item->getVarId()) > 1) {
                $inputValue = true;
            }
            MFormItemManipulator::setVarAndIds($item); // transform ids for template usage
        }

        // create templateElement object
        $templateElement = new MFormElement();
        $templateElement->setLabel($this->parseElement($this->createLabelElement($item), 'base', true));
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

                if ($inputs instanceof DOMNodeList) $this->processNodeFormElement($inputs, $item, 'REX_LINK_' . (int)$id);
                if ($inputs instanceof DOMNodeList) {
                    foreach ($inputs as $input) {
                        if ($input instanceof DOMElement) {
                            if ($input->getAttribute('type') == 'text') {
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

                if ($selects instanceof DOMNodeList) $this->processNodeFormElement($selects, $item, 'REX_LINKLIST_SELECT_' . $id);
                if ($inputs instanceof DOMNodeList) $this->processNodeFormElement($inputs, $item, 'REX_LINKLIST_' . $id);
                break;
        }

        // get body inner
        $templateElement->setElement($this->getBodyInner($dom))
            ->setType($this->getDefaultTemplateType($item, $templateElement));

        // add to output element array
        $this->elements[] = $this->parseElement($templateElement, 'default');
        return $this;
    }

    /**
     * @param DOMNodeList $elements
     * @param MFormItem $item
     * @param null $id
     * @return MFormParser
     * @author Joachim Doerr
     */
    private function processNodeFormElement(DOMNodeList $elements, MFormItem $item, $id = null)
    {
        foreach ($elements as $element) {
            if ($element instanceof DOMElement) {
                if (is_array($item->getAttributes()) && sizeof($item->getAttributes()) > 0) {
                    foreach ($item->getAttributes() as $key => $value) {
                        $element->setAttribute($key, $value);
                    }
                }
                if (!is_null($id)) {
                    $element->setAttribute('id', $id);
                }
            }
        }
        return $this;
    }

    /**
     * @param MFormItem $item
     * @return string
     * @author Joachim Doerr
     */
    private function getWidgetId(MFormItem $item)
    {
        $item->setVarId(substr($item->getVarId(), 1, -1));
        $varId = explode('][', $item->getVarId());

        foreach ($varId as $key => $val) {
            if (!is_numeric($val)) {
                $varId[$key] = rand(0, (strlen($val) * rand()));
            }
        }

        return implode('', $varId);
    }

    /**
     * link, linklist
     * @param MFormItem $item
     * @return $this
     * @throws \rex_exception
     * @author Joachim Doerr
     */
    private function generateCustomLinkElement(MFormItem $item)
    {
        // default manipulations
        MFormItemManipulator::setVarAndIds($item); // transform ids for template usage

        foreach (array('intern' => 'enable', 'extern' => 'enable', 'media' => 'enable', 'mailto' => 'enable', 'tel' => 'disable') as $key => $value) {
            $value = (((isset($item->getAttributes()['data-' . $key])) ? $item->getAttributes()['data-' . $key] : $value) == 'enable');
            $key = ($key == 'extern') ? 'external' : $key;
            $key = ($key == 'tel') ? 'phone' : $key;
            $item->setParameter(array_merge($item->getParameter(), array($key => $value)));
        }
        foreach (array('data-media-type' => 'types', 'data-extern-link-prefix' => 'external_prefix', 'data-link-category' => 'category', 'data-media-category' => 'media_category') as $data => $key) {
            if (isset($item->getAttributes()[$data])) {
                $item->setParameter(array_merge($item->getParameter(), array($key => $item->getAttributes()[$data])));
            }
        }

        $item->setId(str_replace(array('_', ']', '['), '', rand(100, 999) . $item->getVarId()));

        // create templateElement object
        $templateElement = new MFormElement();
        $templateElement->setLabel($this->parseElement($this->createLabelElement($item), 'base', true));

        $parameter = $item->getParameter();
        if (!isset($parameter['ylink']) && isset($item->getAttributes()['ylink'])) {
            $parameter['ylink'] = $item->getAttributes()['ylink'];
        }
        $parameter['class'] = $item->class;

        $html = rex_var_custom_link::getWidget($item->getId(), 'REX_INPUT_VALUE' . $item->getVarId(), $item->getValue(), $parameter, false);

        $dom = new DOMDocument('1.0', 'utf-8');
        @$dom->loadHTML('<?xml encoding="utf-8" ?>' . $html); // utf8_decode($html)

        $div = $dom->getElementsByTagName('div');

        if ($div instanceof DOMNodeList) {
            foreach ($div as $divItem) {
                if ($divItem instanceof DOMElement && $divItem->hasChildNodes()) {
                    $divItem->setAttribute('data-id', $item->getId());
                    $divItem->setAttribute('data-clang', rex_clang::getCurrentId());
                    $divItem->setAttribute('class', $divItem->getAttribute('class') . ' custom-link');
                    /** @var DOMElement $childNode */
                    foreach ($divItem->childNodes as $childNode) {
                        if (($childNode->hasAttribute('class')
                                && $childNode->getAttribute('class') == 'form-control')
                            && ($childNode->hasAttribute('value')
                                && $childNode->getAttribute('value') == '')) {
                            $childNode->setAttribute('value', $item->getValue());
                            if (is_array($item->getAttributes()) && sizeof($item->getAttributes()) > 0) {
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

        // add to output element array
        $this->elements[] = $this->parseElement($templateElement, 'default');
        return $this;
    }

    /**
     * @param DOMDocument|DOMElement $dom
     * @return mixed|string
     * @author Joachim Doerr
     */
    private function getBodyInner($dom)
    {
        $html = $dom->C14N(false, true);
        if (strpos($html, '<body') !== false) {
            preg_match("/<body>(.*)<\/body>/ism", $html, $matches);
            if (isset($matches[1])) {
                $html = $matches[1];
            }
        }
        return $html;
    }


    /**
     * @param MFormItem[] $items
     * @return $this
     * @author Joachim Doerr
     */
    private function parseFormFields(array $items)
    {
        if (sizeof($items) > 0) {
            foreach ($items as $key => $item) {

                switch ($item->getType()) {
                    // FIELDSET
                    case 'fieldset':
                        $this->generateFieldset($item);
                        break;
                    case 'close-fieldset':
                        $this->closeFieldset();
                        break;

                    // TODO
                    case 'start-group-fieldset':
                    case 'close-group-fieldset':
                        break;

                    // TABS
                    case 'start-group-tab':
                        $this->generateTabGroup($item, $key, $items);
                        break;
                    case 'tab':
                        $this->generateTab($item);
                        break;
                    case 'close-tab':
                        $this->closeTab();
                        break;
                    case 'close-group-tab':
                        $this->closeTabGroup();
                        break;

                    // COLLAPSE
                    case 'start-group-collapse':
                        $this->generateCollapseGroup($item);
                        break;
                    case 'collapse':
                        $this->generateCollapse($item);
                        break;
                    case 'close-collapse':
                        $this->closeCollapse($item);
                        break;
                    case 'close-group-collapse':
                        $this->closeCollapseGroup($item);
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
                    case 'hidden':
                    case 'text-readonly':
                        $this->generateInputElement($item);
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
        return $this;
    }

    /**
     * @param MFormItem $item
     * @return MFormElement
     * @author Joachim Doerr
     */
    private function createLabelElement(MFormItem $item)
    {
        $this->createTooltipElement($item);

        $label = new MFormElement();
        $label->setId($item->getId())
            ->setValue($item->getLabel())
            ->setType('label');

        return $label;
    }

    /**
     * @param $item
     * @author Joachim Doerr
     */
    private function createTooltipElement(MFormItem $item)
    {
        // set tooltip
        if ($item->getInfoTooltip()) {
            // parse tooltip
            if (empty($item->getInfoTooltipIcon()))
                $item->setInfoTooltipIcon('fa-exclamation');

            $tooltip = new MFormElement();
            $tooltip->setValue($item->getInfoTooltip())
                ->setInfoTooltipIcon($item->getInfoTooltipIcon())
                ->setType('tooltip-info');

            $item->setLabel($item->getLabel() . $this->parseElement($tooltip, 'base', true));
        }
    }

    /**
     * final parsing
     * @param MFormItem[] $items
     * @param null|string $theme
     * @param bool $debug
     * @return string
     * @author Joachim Doerr
     */
    public function parse(array $items, $theme = NULL, $debug = false)
    {
        $this->theme = rex_addon::get('mform')->getConfig('mform_theme');
        if (!is_null($theme) && $theme != $this->theme) {
            $this->theme = $theme;
        }

        $items = MFormGroupExtensionHelper::addTabGroupExtensionItems($items);
        $items = MFormGroupExtensionHelper::addCollapseGroupExtensionItems($items);
        $items = MFormGroupExtensionHelper::addAccordionGroupExtensionItems($items);
        $items = MFormGroupExtensionHelper::addFieldsetGroupExtensionItems($items);

        // show for debug items
        if ($debug) {
            dump($items);
        }

        $this->parseFormFields($items);

        // wrap elements
        $element = new MFormElement();
        $element->setOutput(implode($this->elements))
            ->setType('wrapper');

        // return output
        return $this->parseElement($element, 'wrapper');
    }

    /**
     * @param MFormItem $item
     * @param MFormElement $templateElement
     * @return string
     * @author Joachim Doerr
     */
    private function getDefaultTemplateType(MFormItem $item, MFormElement $templateElement)
    {
        $templateType = 'default';

        // set default template
        if (!empty($item->getLabelColClass()) && !empty($item->getFormItemColClass())) {
            $templateType = $templateType . '_custom'; // add _custom to template type
            $templateElement->setLabelColClass($item->getLabelColClass())
                ->setFormItemColClass($item->getFormItemColClass());
        }

        // is full flag true and template type default
        if ($item->isFull()) {
            $templateType = $templateType . '_full'; // add _full to template type
        }

        return $templateType;
    }

    /**
     * @param MFormElement $element
     * @param string $fragmentType
     * @param bool $subPath
     * @return string
     * @author Joachim Doerr
     */
    private function parseElement(MFormElement $element, string $fragmentType, bool $subPath = false): string
    {
        if (is_array($element->value)) {
            $element->value = '';
        }

        $keys = $element->getKeys(false);
        $vals = $element->getValues();

        $fragment = new rex_fragment();
        foreach ($keys as $index => $key) {
            $fragment->setVar($key, $vals[$index], false);
        }
        try {
            return $fragment->parse($this->theme . '/mform_' . $fragmentType . '.php');
        } catch (\rex_exception $e) {
            \rex_logger::logException($e);
            return \rex_view::error($e->getMessage());
        }
    }

    /**
     * @param array $attributes
     * @return string
     * @author Joachim Doerr
     */
    private function parseAttributes($attributes)
    {
        $inlineAttributes = '';
        if (sizeof($attributes) > 0) {
            foreach ($attributes as $key => $value) {
                if (!in_array($key, array('id', 'name', 'type', 'value', 'checked', 'selected'))) {
                    $inlineAttributes .= ' ' . $key . '="' . $value . '"';
                }
            }
        }
        return $inlineAttributes;
    }
}
