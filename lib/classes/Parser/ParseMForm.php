<?php

/**
 * Class ParseMForm
 * @copyright Copyright (c) 2015 by Joachim Doerr
 * @author mail[at]joachim-doerr[dot]com Joachim Doerr
 *
 * @package redaxo5
 * @version 4.0.0
 * @license MIT
 */
class ParseMForm extends AbstractMFormParser
{
    private $elements = array();


    /**
     * @param $element
     * @return ParseMForm
     * @author Joachim Doerr
     */
    private function getCallbackElement(MFormItem $element)
    {
//        $strCallElement = call_user_func($element['callabel'], $element['parameter']);
//        return $this->parseElementToTemplate('<mform:element>' . $strCallElement . '</mform:element>', 'html');
    }


    /**
     * custom link
     * @param $element
     * @return ParseMForm
     * @author Joachim Doerr
     */
    private function generateCustomInputElement($element)
    {
//        $element['attributes'] = $this->parseAttributes($element['attributes']);
//        $element['label'] = $this->getLabel($element);
//        $varId = $this->setVarAndIds($element);
//
//        $messages = array(
//            'add_internlink' => rex_i18n::msg('mfrom_add_internlink'),
//            'add_externlink' => rex_i18n::msg('mfrom_add_externlink'),
//            'add_medialink' => rex_i18n::msg('mform_add_medialink'),
//            'remove' => rex_i18n::msg('mform_remove_link')
//        );
//
//        switch ($element['type']) {
//            case 'custom-link':
//            default:
//                $type = 'default';
//                $varId['sub-var-id-value'] = $varId['sub-var-id'];
//                $varId['sub-var-id'] = str_replace(array('[', ']'), '', $varId['sub-var-id']);
//                $varId['sub-var-id-for-id'] = ($element['sub-var-id'] != '') ? '_' . $element['sub-var-id'] : '';
//                $varId['hidden_value'] = $varId['value'];
//                $varId['show_value'] = $varId['value'];
//
//                if (is_numeric($varId['value'])) {
//                    $art = OOArticle:: getArticleById($varId['value']);
//                    if (OOArticle:: isValid($art)) {
//                        $varId['show_value'] = $art->getName();
//                    } else {
//                        $varId['hidden_value'] = '';
//                        $varId['show_value'] = '';
//                    }
//                }
//                break;
//        }
//
//        $elementOutput = <<<EOT
//
//      <mform:label><label for="rv{$varId['id']}">{$element['label']}</label></mform:label>
//      <mform:element>
//        <script>
//          /* <![CDATA[ */
//            jQuery(document).ready(function($) {
//              var this_hidden_element_{$element['var-id']}{$varId['sub-var-id-for-id']} = $('#VALUE_{$element['var-id']}{$varId['sub-var-id-for-id']}'),
//                  this_show_element_{$element['var-id']}{$varId['sub-var-id-for-id']} = $('#VALUE_{$element['var-id']}{$varId['sub-var-id-for-id']}_NAME'),
//                  this_media_{$element['var-id']}{$varId['sub-var-id-for-id']} = $('#VALUE{$element['var-id']}{$varId['sub-var-id-for-id']}_MEDIUM'),
//                  this_link_{$element['var-id']}{$varId['sub-var-id-for-id']} = $('#VALUE{$element['var-id']}{$varId['sub-var-id-for-id']}_LINK'),
//                  this_extern_{$element['var-id']}{$varId['sub-var-id-for-id']} = $('#VALUE{$element['var-id']}{$varId['sub-var-id-for-id']}_EXTERN'),
//                  this_remove_{$element['var-id']}{$varId['sub-var-id-for-id']} = $('#VALUE{$element['var-id']}{$varId['sub-var-id-for-id']}_REMOVE');
//
//              this_media_{$element['var-id']}{$varId['sub-var-id-for-id']}.bind('click',function(){
//                this_hidden_element_{$element['var-id']}{$varId['sub-var-id-for-id']}.attr('name','').attr('id','');
//                this_show_element_{$element['var-id']}{$varId['sub-varCc-id-for-id']}.attr('name','VALUE[{$element['var-id']}]{$varId['sub-var-id-value']}').attr('id','REX_MEDIA_{$element['var-id']}{$varId['sub-var-id-for-id']}');
//                openREXMedia({$element['var-id']},'');return false;
//              });
//
//              this_link_{$element['var-id']}{$varId['sub-var-id-for-id']}.bind('click',function(){
//                this_show_element_{$element['var-id']}{$varId['sub-var-id-for-id']}.attr('name','VALUE_NAME[{$element['var-id']}]{$varId['sub-var-id']}').attr('id','VALUE_{$element['var-id']}{$varId['sub-var-id-for-id']}_NAME');
//                this_hidden_element_{$element['var-id']}{$varId['sub-var-id-for-id']}.attr('name','VALUE[{$element['var-id']}]{$varId['sub-var-id-value']}').attr('id','VALUE_{$element['var-id']}{$varId['sub-var-id-for-id']}');
//                openLinkMap('VALUE_{$element['var-id']}{$varId['sub-var-id-for-id']}', '');return false;
//              });
//
//              this_extern_{$element['var-id']}{$varId['sub-var-id-for-id']}.bind('click',function(){
//                var extern_link = prompt('Link','http://');
//                this_hidden_element_{$element['var-id']}{$varId['sub-var-id-for-id']}.attr('name','').attr('id','');
//                this_show_element_{$element['var-id']}{$varId['sub-var-id-for-id']}.attr('name','VALUE[{$element['var-id']}]{$varId['sub-var-id-value']}').attr('id','VALUE_{$element['var-id']}{$varId['sub-var-id-for-id']}');
//                if (extern_link!="" && extern_link!=undefined) {
//                  this_show_element_{$element['var-id']}{$varId['sub-var-id-for-id']}.attr('value',extern_link);
//                  return false;
//                }
//              });
//              this_remove_{$element['var-id']}{$varId['sub-var-id-for-id']}.bind('click',function(){
//                this_hidden_element_{$element['var-id']}{$varId['sub-var-id-for-id']}.attr('value','');
//                this_show_element_{$element['var-id']}{$varId['sub-var-id-for-id']}.attr('value','');
//                return false;
//              });
//            });
//          /* ]]> */
//        </script>
//
//        <div class="rex-widget">
//          <div class="rex-widget-custom-link">
//            <p class="rex-widget-field">
//              <input type="hidden" name="VALUE[{$element['var-id']}]{$varId['sub-var-id-value']}" id="VALUE_{$element['var-id']}{$varId['sub-var-id-for-id']}" value="{$varId['hidden_value']}">
//              <input type="text" size="30" name="VALUE_NAME[{$element['var-id']}]{$varId['sub-var-id']}" id="VALUE_{$element['var-id']}{$varId['sub-var-id-for-id']}_NAME" value="{$varId['show_value']}" readonly="readonly">
//            </p>
//             <p class="rex-widget-icons rex-widget-1col">
//              <span class="rex-widget-column rex-widget-column-first">
//                <a href="#" class="mform-icon-internlink-open" title="{$messages['add_internlink']}" id="VALUE{$element['var-id']}{$varId['sub-var-id-for-id']}_LINK"></a>
//                <a href="#" class="mform-icon-externlink-open" title="{$messages['add_externlink']}" id="VALUE{$element['var-id']}{$varId['sub-var-id-for-id']}_EXTERN"></a>
//                <a href="#" class="mform-icon-media-open" title="{$messages['add_medialink']}" id="VALUE{$element['var-id']}{$varId['sub-var-id-for-id']}_MEDIUM"></a>
//                <a href="#" class="mform-remove-link" title="{$messages['remove']}" id="VALUE{$element['var-id']}{$varId['sub-var-id-for-id']}_REMOVE"></a>
//              </span>
//            </p>
//          </div>
//        </div>
//      </mform:element>
//
//EOT;
//        return $this->parseElementToTemplate($elementOutput, $type);
    }

    /**
     * select, multiselect
     * @param $element
     * @return ParseMForm
     * @author Joachim Doerr
     */
    private function generateOptionsElement($element)
    {
        if ($element['multi']) {
            $element['attributes'] = $this->getDefaultClass($element['attributes'], 'select-multiple');
        } else {
            $element['attributes'] = $this->getDefaultClass($element['attributes'], 'select');
        }

        $element['attributes'] = $this->parseAttributes($element['attributes']);
        $element['label'] = $this->getLabel($element);
        $varId = $this->setVarAndIds($element);

        $multiselectJavascript = '';
        $multiselectHidden = '';
        $hiddenValue = array();
        $options = '';
        $defaultValue = array();
        $hiddenValueOutput = '';
        $selectAttributes = (is_numeric($element['size']) === true) ? 'size="' . $element['size'] . '"' : '';

        if ($element['size'] == 'full') {
            $selectAttributes = 'size="' . sizeof($element['options']) . '"';
        }
        if ($element['multi'] === true) {
            $selectAttributes .= ' multiple="multiple"';
            $multiselectJavascript = <<<EOT
        <script type="text/javascript">
          /* <![CDATA[ */
            jQuery(document).ready(function($){
              $("#rv{$varId['id']}").change(function() {
                $("#hidden_rv{$varId['id']}").val($(this).val());
              });
            });
          /* ]]> */
        </script>
EOT;
            $multiselectHidden = <<<EOT
        <input id="hidden_rv{$varId['id']}" type="hidden" name="REX_INPUT_VALUE[{$element['var-id']}]{$varId['sub-var-id']}" value="{$element['value']}" />
EOT;
            if ($element['value'] != '') {
                $hiddenValue = explode(',', $element['value']);
            }
            if ($element['default-value'] != '') {
                $defaultValue = explode(',', $element['default-value']);
            }
        } else {
            $hiddenValue = array($element['value']);
        }
        if (array_key_exists('options', $element) === true) {
            foreach ($element['options'] as $key => $value) {
                $options .= '<option value="' . $key . '" ';
                foreach ($defaultValue as $strDefaultValue) {
                    if ($key == $strDefaultValue) {
                        $element['default-value'] = $strDefaultValue;
                    }
                }
                foreach ($hiddenValue as $hdValue) {
                    if ($key == $hdValue) {
                        $hiddenValueOutput = $hdValue;
                    }
                }
                if ($key == $hiddenValueOutput or ($element['mode'] == 'add' && $key == $element['default-value'])) {
                    $options .= 'selected="selected" ';
                }
                $options .= '>' . $value . '</option>';
            }
        }
        $elementOutput = <<<EOT

      <mform:label><label for="rv{$varId['id']}">{$element['label']}</label>$multiselectJavascript</mform:label>
      <mform:element><select id="rv{$varId['id']}" name="REX_INPUT_VALUE[{$element['var-id']}]{$varId['sub-var-id']}" {$element['attributes']} $selectAttributes>$options</select>$multiselectHidden</mform:element>

EOT;
        return $this->parseElementToTemplate($elementOutput, 'default');
    }

    /**
     * radio
     * @param $element
     * @return ParseMForm
     * @author Joachim Doerr
     */
    private function generateRadioElement($element)
    {
        $element['label'] = $this->getLabel($element);
        $varId = $this->setVarAndIds($element);

        $options = '';
        $count = 0;

        if (array_key_exists('options', $element) === true) {
            foreach ($element['options'] as $key => $value) {
                $count++;
                $radioAttributes = '';
                if (isset($element['attributes']['radio-attr'][$key]) === true) {
                    $radioAttributes = $this->parseAttributes($element['attributes']['radio-attr'][$key]);
                }
                $options .= '<div class="radio_element"><input id="rv' . $varId['id'] . $count . '" type="radio" name="REX_INPUT_VALUE[' . $element['var-id'] . ']' . $varId['sub-var-id'] . '" value="' . $key . '" ' . $radioAttributes;
                if ($key == $element['value'] or ($element['mode'] == 'add' && $key == $element['default-value'])) {
                    $options .= ' checked="checked" ';
                }
                $options .= ' /><span class="radio_description"><label class="description" for="rv' . $varId['id'] . $count . '">' . $value . '</label></span></div>';
            }
        }
        $elementOutput = <<<EOT

      <mform:label><label for="rv{$varId['id']}">{$element['label']}</label></mform:label>
      <mform:element>$options</mform:element>

EOT;
        return $this->parseElementToTemplate($elementOutput, 'default');
    }

    /**
     * checkbox
     * @param $element
     * @return ParseMForm
     * @author Joachim Doerr
     */
    private function generateCheckboxElement($element)
    {
        if (!array_key_exists('options', $element)) {
            return $this;
        }

        $element['attributes'] = $this->parseAttributes($element['attributes']);
        $element['label'] = $this->getLabel($element);

        $arrayKeys = array_keys($element['options']);
        $addEndArrayKeys = end($arrayKeys);
        $addEnd = end($element['options']);

        $element['options'] = array($addEndArrayKeys => $addEnd);
        $options = '';
        $varId = $this->setVarAndIds($element);

        foreach ($element['options'] as $key => $value) {
            $options .= '<div class="radio_element"><input id="rv' . $varId['id'] . '" type="checkbox" name="REX_INPUT_VALUE[' . $element['var-id'] . ']' . $varId['sub-var-id'] . '" value="' . $key . '" ' . $element['attributes'];
            if ($key == $element['value'] or ($element['mode'] == 'add' && $key == $element['default-value'])) {
                $options .= ' checked="checked" ';
            }
            $options .= ' /><span class="radio_description"><label class="description" for="rv' . $varId['id'] . '">' . $value . '</label></span></div>';
        }

        $elementOutput = <<<EOT

      <mform:label><label for="rv{$varId['id']}">{$element['label']}</label></mform:label>
      <mform:element>$options</mform:element>

EOT;
        return $this->parseElementToTemplate($elementOutput, 'default');
    }

    /**
     * link, linklist
     * @param $element
     * @return ParseMForm
     * @author Joachim Doerr
     */
    private function generateLinkElement($element)
    {
        $element['label'] = $this->getLabel($element);

        if (!array_key_exists('parameter', $element) or !is_array($element['parameter'])) {
            $element['parameter'] = array();
        }

        switch ($element['type']) {
            default:
            case 'link':
                $options = rex_var_link::getWidget($element['var-id'], 'REX_INPUT_LINK[' . $element['var-id'] . ']', $element['value'], $element['parameter']);
                break;
            case 'linklist':
                $options = rex_var_linklist::getWidget($element['var-id'], 'REX_INPUT_LINKLIST[' . $element['var-id'] . ']', $element['value'], $element['parameter']);
                break;
        }

        $elementOutput = <<<EOT

      <mform:label><label>{$element['label']}</label></mform:label>
      <mform:element>$options</mform:element>

EOT;
        return $this->parseElementToTemplate($elementOutput, 'default');
    }

    /**
     * media, medialist
     * @param $element
     * @return ParseMForm
     * @author Joachim Doerr
     */
    private function generateMediaElement($element)
    {
        $element['label'] = $this->getLabel($element);

        if (!array_key_exists('parameter', $element) or !is_array($element['parameter'])) {
            $element['parameter'] = array();
        }

        switch ($element['type']) {
            default:
            case 'media':
                $options = rex_var_media::getWidget($element['var-id'], 'REX_INPUT_MEDIA[' . $element['var-id'] . ']', $element['value'], $element['parameter']);
                break;
            case 'medialist':
                $options = rex_var_medialist::getWidget($element['var-id'], 'REX_INPUT_MEDIALIST[' . $element['var-id'] . ']', $element['value'], $element['parameter']);
                break;
        }

        $elementOutput = <<<EOT

      <mform:label><label>{$element['label']}</label></mform:label>
      <mform:element>$options</mform:element>

EOT;
        return $this->parseElement($elementOutput, 'default');
    }


    /**
     * @param array $element
     * @return mixed
     * @author Joachim Doerr
     */
    public function getLabel($element)
    {

        if (!array_key_exists('label', $element)) {
            $element['label'] = '';
        }

        return $element['label'];
    }


    /**
     * @param MFormItem[] $items
     * @author Joachim Doerr
     * @return $this
     */
    private function parseFormFields(array $items)
    {
        if (sizeof($items) > 0) {
            foreach ($items as $key => $item) {

                // set default class
//                $this->attributeHandler->setItem($item)
//                    ->setDefaultClass();

                switch ($item->getType()) {
                    case 'close-fieldset':
                        $this->closeFieldset();
                        break;
                    case 'fieldset':
                        $this->generateFieldset($item);
                        break;
                    case 'html':
                    case 'headline':
                    case 'description':
                        $this->generateLineElement($item);
                        break;
//                    case 'callback':
//                        $this->getCallbackElement($item);
//                        break;
                    case 'text':
                    case 'hidden':
                    case 'text-readonly':
                        $this->generateInputElement($item);
                        break;
                    case 'textarea':
                    case 'markitup':
                    case 'area-readonly':
                        $this->generateAreaElement($item);
                        break;
                    case 'select':
                    case 'multiselect':
                        $this->generateOptionsElement($item);
                        break;
                    case 'radio':
                    case 'radiobutton':
                        $this->generateRadioElement($item);
                        break;
                    case 'checkbox':
                        $this->generateCheckboxElement($item);
                        break;
                    case 'link':
                    case 'linklist':
                        $this->generateLinkElement($item);
                        break;
                    case 'media':
                    case 'medialist':
                        $this->generateMediaElement($item);
                        break;
                }
            }
        }
        return $this;
    }

    /**
     * @param $template
     * @return string
     * @author Joachim Doerr
     */
    public function setTheme($template)
    {
        if (is_dir(rex_path::addonData(sprintf(self::THEME_PATH, $template))) === true) {
            $this->template = $template;
            return
                PHP_EOL . '<!-- mform -->' .
                PHP_EOL . '  <link rel="stylesheet" type="text/css" href="?&mform_theme=' . $this->template . '" media="all" />' .
                PHP_EOL . '<!-- mform -->' . PHP_EOL;
        }
    }

    /**
     * @param $attributes
     * @param $type
     * @return mixed
     * @author Joachim Doerr
     */
    public function getDefaultClass($attributes, $type)
    {
        $notFound = true;

        if (sizeof($attributes) > 0) {
            foreach ($attributes as $key => $value) {
                if ($key == 'class') {
                    if (array_key_exists($type, $this->defaultClass)) {
                        $attributes[$key] = $this->defaultClass[$type] . ' ' . $value;
                    }
                    $notFound = false;
                }
            }
        }
        if ($notFound) {
            if (array_key_exists($type, $this->defaultClass)) {
                $attributes['class'] = $this->defaultClass[$type];
            }
        }

        return $attributes;
    }

    /**
     * @param $attributes
     * @return null
     * @author Joachim Doerr
     */
    private function getCustomId($attributes)
    {
        $id = null;

        if (sizeof($attributes) > 0) {
            foreach ($attributes as $key => $value) {
                if ($key == 'id') {
                    return $value;
                }
            }
        }

        return $id;
    }


    /**
     * final parsing
     * @param MFormItem[] $items
     * @param boolean $template
     * @return string
     * @author Joachim Doerr
     */
    public function parse(array $items, $template = false)
    {
        if ($template != false) {
            $this->output .= $this->setTheme($template);
        }
        $this->parseFormFields($items);
//        $this->parseElementToTemplate($this->output, 'wrapper', true);

//        return $this->output;
        return implode($this->elements);
    }
}
