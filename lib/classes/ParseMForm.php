<?php

/**
 * Class ParseMForm
 * @copyright Copyright (c) 2015 by Joachim Doerr
 * @author mail[at]joachim-doerr[dot]com Joachim Doerr
 *
 * @package redaxo4.6.x
 * @version 3.0.0
 * @license MIT
 */
class ParseMForm
{
    /**
     * @var string
     */
    private $output;

    /**
     * @var bool
     */
    private $fieldset = false;

    /**
     * @var string
     */
    private $template;

    /**
     * @param $element
     * @author Joachim Doerr
     * @return ParseMForm
     */
    private function generateFieldset($element)
    {
        $element['attributes'] = $this->getAttributes($element['attributes']);

        if ($this->fieldset === true) {
            $element['close_fieldset'] = '</fieldset>';
        } else {
            $this->fieldset = true;
        }

        // add close tag
        $elementOutput = (array_key_exists('close_fieldset', $element)) ? $element['close_fieldset'] : '';

        // add open fieldset attributes
        $elementOutput .= (array_key_exists('attributes', $element)) ? '<fieldset ' . $element['attributes'] . '>' : '<fieldset>';

        // add value
        $elementOutput .= (array_key_exists('attributes', $element)) ? '<legend>' . $element['value'] . '</legend>' : '';

        return $this->parseElementToTemplate('<mform:element>' . $elementOutput . '</mform:element>', NULL);
    }

    /**
     * @return ParseMForm
     * @author Joachim Doerr
     */
    private function closeFieldset()
    {
        if ($this->fieldset === true) {
            $this->fieldset = false;
            return $this->parseElementToTemplate('<mform:element></fieldset></mform:element>', NULL);
        }
    }

    /**
     * html, headline, description
     * @param $element
     * @return ParseMForm
     * @author Joachim Doerr
     */
    private function generateLineElement($element)
    {
        switch ($element['type']) {
            case 'headline':
            case 'description':
            default:
                $type = $element['type'];
                break;
        }

        return $this->parseElementToTemplate('<mform:element>' . $element['value'] . '</mform:element>', $type);
    }

    /**
     * @param $element
     * @return ParseMForm
     * @author Joachim Doerr
     */
    private function getCallbackElement($element)
    {
        $strCallElement = call_user_func($element['callabel'], $element['parameter']);
        return $this->parseElementToTemplate('<mform:element>' . $strCallElement . '</mform:element>', 'html');
    }

    /**
     * hidden, text, password
     * @param $element
     * @return ParseMForm
     * @author Joachim Doerr
     */
    private function generateInputElement($element)
    {
        $element['attributes'] = $this->getAttributes($element['attributes']);
        $element['label'] = $this->getLabel($element);
        $varId = $this->getVarAndIds($element);

        switch ($element['type']) {
            case 'hidden':
                $type = 'hidden';
                $element['label'] = '';
                break;

            case 'text-readonly':
                $type = 'default';
                $element['type'] = 'text';
                $element['attributes'] .= ' readonly="readonly"';
                break;

            default:
                $type = 'default';
                break;
        }

        $strElement = <<<EOT

      <mform:label><label for="rv{$varId['id']}">{$element['label']}</label></mform:label>
      <mform:element><input id="rv{$varId['id']}" type="{$element['type']}" name="VALUE[{$element['var-id']}]{$varId['sub-var-id']}" value="{$varId['value']}" {$element['attributes']} /></mform:element>

EOT;
        return $this->parseElementToTemplate($strElement, $type);
    }

    /**
     * custom link
     * @param $element
     * @return ParseMForm
     * @author Joachim Doerr
     */
    private function generateCustomInputElement($element)
    {
        global $I18N;

        $element['attributes'] = $this->getAttributes($element['attributes']);
        $element['label'] = $this->getLabel($element);
        $varId = $this->getVarAndIds($element);

        $messages = array(
            'add_internlink' => $I18N->msg('mfrom_add_internlink'),
            'add_externlink' => $I18N->msg('mfrom_add_externlink'),
            'add_medialink' => $I18N->msg('mform_add_medialink'),
            'remove' => $I18N->msg('mform_remove_link')
        );

        switch ($element['type']) {
            case 'custom-link':
            default:
                $type = 'default';
                $varId['sub-var-id-value'] = $varId['sub-var-id'];
                $varId['sub-var-id'] = str_replace(array('[', ']'), '', $varId['sub-var-id']);
                $varId['sub-var-id-for-id'] = ($element['sub-var-id'] != '') ? '_' . $element['sub-var-id'] : '';
                $varId['hidden_value'] = $varId['value'];
                $varId['show_value'] = $varId['value'];

                if (is_numeric($varId['value'])) {
                    $art = OOArticle:: getArticleById($varId['value']);
                    if (OOArticle:: isValid($art)) {
                        $varId['show_value'] = $art->getName();
                    } else {
                        $varId['hidden_value'] = '';
                        $varId['show_value'] = '';
                    }
                }
                break;
        }

        $elementOutput = <<<EOT

      <mform:label><label for="rv{$varId['id']}">{$element['label']}</label></mform:label>
      <mform:element>
        <script>
          /* <![CDATA[ */
            jQuery(document).ready(function($) {
              var this_hidden_element_{$element['var-id']}{$varId['sub-var-id-for-id']} = $('#VALUE_{$element['var-id']}{$varId['sub-var-id-for-id']}'),
                  this_show_element_{$element['var-id']}{$varId['sub-var-id-for-id']} = $('#VALUE_{$element['var-id']}{$varId['sub-var-id-for-id']}_NAME'),
                  this_media_{$element['var-id']}{$varId['sub-var-id-for-id']} = $('#VALUE{$element['var-id']}{$varId['sub-var-id-for-id']}_MEDIUM'),
                  this_link_{$element['var-id']}{$varId['sub-var-id-for-id']} = $('#VALUE{$element['var-id']}{$varId['sub-var-id-for-id']}_LINK'),
                  this_extern_{$element['var-id']}{$varId['sub-var-id-for-id']} = $('#VALUE{$element['var-id']}{$varId['sub-var-id-for-id']}_EXTERN'),
                  this_remove_{$element['var-id']}{$varId['sub-var-id-for-id']} = $('#VALUE{$element['var-id']}{$varId['sub-var-id-for-id']}_REMOVE');

              this_media_{$element['var-id']}{$varId['sub-var-id-for-id']}.bind('click',function(){
                this_hidden_element_{$element['var-id']}{$varId['sub-var-id-for-id']}.attr('name','').attr('id','');
                this_show_element_{$element['var-id']}{$varId['sub-var-id-for-id']}.attr('name','VALUE[{$element['var-id']}]{$varId['sub-var-id-value']}').attr('id','REX_MEDIA_{$element['var-id']}{$varId['sub-var-id-for-id']}');
                openREXMedia({$element['var-id']},'');return false;
              });

              this_link_{$element['var-id']}{$varId['sub-var-id-for-id']}.bind('click',function(){
                this_show_element_{$element['var-id']}{$varId['sub-var-id-for-id']}.attr('name','VALUE_NAME[{$element['var-id']}]{$varId['sub-var-id']}').attr('id','VALUE_{$element['var-id']}{$varId['sub-var-id-for-id']}_NAME');
                this_hidden_element_{$element['var-id']}{$varId['sub-var-id-for-id']}.attr('name','VALUE[{$element['var-id']}]{$varId['sub-var-id-value']}').attr('id','VALUE_{$element['var-id']}{$varId['sub-var-id-for-id']}');
                openLinkMap('VALUE_{$element['var-id']}{$varId['sub-var-id-for-id']}', '');return false;
              });

              this_extern_{$element['var-id']}{$varId['sub-var-id-for-id']}.bind('click',function(){
                var extern_link = prompt('Link','http://');
                this_hidden_element_{$element['var-id']}{$varId['sub-var-id-for-id']}.attr('name','').attr('id','');
                this_show_element_{$element['var-id']}{$varId['sub-var-id-for-id']}.attr('name','VALUE[{$element['var-id']}]{$varId['sub-var-id-value']}').attr('id','VALUE_{$element['var-id']}{$varId['sub-var-id-for-id']}');
                if (extern_link!="" && extern_link!=undefined) {
                  this_show_element_{$element['var-id']}{$varId['sub-var-id-for-id']}.attr('value',extern_link);
                  return false;
                }
              });
              this_remove_{$element['var-id']}{$varId['sub-var-id-for-id']}.bind('click',function(){
                this_hidden_element_{$element['var-id']}{$varId['sub-var-id-for-id']}.attr('value','');
                this_show_element_{$element['var-id']}{$varId['sub-var-id-for-id']}.attr('value','');
                return false;
              });
            });
          /* ]]> */
        </script>

        <div class="rex-widget">
          <div class="rex-widget-custom-link">
            <p class="rex-widget-field">
              <input type="hidden" name="VALUE[{$element['var-id']}]{$varId['sub-var-id-value']}" id="VALUE_{$element['var-id']}{$varId['sub-var-id-for-id']}" value="{$varId['hidden_value']}">
              <input type="text" size="30" name="VALUE_NAME[{$element['var-id']}]{$varId['sub-var-id']}" id="VALUE_{$element['var-id']}{$varId['sub-var-id-for-id']}_NAME" value="{$varId['show_value']}" readonly="readonly">
            </p>
             <p class="rex-widget-icons rex-widget-1col">
              <span class="rex-widget-column rex-widget-column-first">
                <a href="#" class="mform-icon-internlink-open" title="{$messages['add_internlink']}" id="VALUE{$element['var-id']}{$varId['sub-var-id-for-id']}_LINK"></a>
                <a href="#" class="mform-icon-externlink-open" title="{$messages['add_externlink']}" id="VALUE{$element['var-id']}{$varId['sub-var-id-for-id']}_EXTERN"></a>
                <a href="#" class="mform-icon-media-open" title="{$messages['add_medialink']}" id="VALUE{$element['var-id']}{$varId['sub-var-id-for-id']}_MEDIUM"></a>
                <a href="#" class="mform-remove-link" title="{$messages['remove']}" id="VALUE{$element['var-id']}{$varId['sub-var-id-for-id']}_REMOVE"></a>
              </span>
            </p>
          </div>
        </div>
      </mform:element>

EOT;
        return $this->parseElementToTemplate($elementOutput, $type);
    }

    /**
     * textarea, markitup
     * @param $element
     * @return ParseMForm
     * @author Joachim Doerr
     */
    private function generateAreaElement($element)
    {
        $element['attributes'] = $this->getAttributes($element['attributes']);
        $element['label'] = $this->getLabel($element);
        $varId = $this->getVarAndIds($element);

        if ($element['type'] == 'area-readonly') {
            $element['attributes'] .= ' readonly="readonly"';
        }

        foreach (array('label') as $key) {
            if (!array_key_exists($key, $element)) {
                $element[$key] = null;
            }
        }

        $elementOutput = <<<EOT

      <mform:label><label for="rv{$varId['id']}">{$element['label']}</label></mform:label>
      <mform:element><textarea id="rv{$varId['id']}" name="VALUE[{$element['var-id']}]{$varId['sub-var-id']}" {$element['attributes']} >{$varId['value']}</textarea></mform:element>

EOT;
        return $this->parseElementToTemplate($elementOutput, 'default');
    }

    /**
     * select, multiselect
     * @param $element
     * @return ParseMForm
     * @author Joachim Doerr
     */
    private function generateOptionsElement($element)
    {
        $element['attributes'] = $this->getAttributes($element['attributes']);
        $element['label'] = $this->getLabel($element);
        $varId = $this->getVarAndIds($element);

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
        <input id="hidden_rv{$varId['id']}" type="hidden" name="VALUE[{$element['var-id']}]{$varId['sub-var-id']}" value="{$element['value']}" />
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
      <mform:element><select id="rv{$varId['id']}" name="VALUE[{$element['var-id']}]{$varId['sub-var-id']}" {$element['attributes']} $selectAttributes>$options</select>$multiselectHidden</mform:element>

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
        $varId = $this->getVarAndIds($element);

        $options = '';
        $count = 0;

        if (array_key_exists('options', $element) === true) {
            foreach ($element['options'] as $key => $value) {
                $count++;
                $radioAttributes = '';
                if (isset($element['attributes']['radio-attr'][$key]) === true) {
                    $radioAttributes = $this->getAttributes($element['attributes']['radio-attr'][$key]);
                }
                $options .= '<div class="radio_element"><input id="rv' . $varId['id'] . $count . '" type="radio" name="VALUE[' . $element['var-id'] . ']' . $varId['sub-var-id'] . '" value="' . $key . '" ' . $radioAttributes;
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

        $element['attributes'] = $this->getAttributes($element['attributes']);
        $element['label'] = $this->getLabel($element);

        $arrayKeys = array_keys($element['options']);
        $addEndArrayKeys = end($arrayKeys);
        $addEnd = end($element['options']);

        $element['options'] = array($addEndArrayKeys => $addEnd);
        $options = '';
        $varId = $this->getVarAndIds($element);

        foreach ($element['options'] as $key => $value) {
            $options .= '<div class="radio_element"><input id="rv' . $varId['id'] . '" type="checkbox" name="VALUE[' . $element['var-id'] . ']' . $varId['sub-var-id'] . '" value="' . $key . '" ' . $element['attributes'];
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

        switch ($element['type']) {
            default:
            case 'link':
                $options = rex_var_link::getLinkButton($element['var-id'], $element['value'], $element['cat-id']);
                break;
            case 'linklist':
                $options = rex_var_link::getLinkListButton($element['var-id'], $element['value'], $element['cat-id']);
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

        if (isset($element['parameter']) === false) {
            $element['parameter'] = array();
        }

        switch ($element['type']) {
            default:
            case 'media':
                $options = rex_var_media::getMediaButton($element['var-id'], $element['cat-id'], $element['parameter']);
                $options = str_replace('REX_MEDIA[' . $element['var-id'] . ']', $element['value'], $options);
                break;
            case 'medialist':
                $options = rex_var_media::getMediaListButton($element['var-id'], $element['value'], $element['cat-id'], $element['parameter']);
                break;
        }

        $elementOutput = <<<EOT

      <mform:label><label>{$element['label']}</label></mform:label>
      <mform:element>$options</mform:element>

EOT;
        return $this->parseElementToTemplate($elementOutput, 'default');
    }

    /**
     * @param $element
     * @return array
     * @author Joachim Doerr
     */
    private function getVarAndIds($element)
    {
        $result = array();

        $result['value'] = htmlspecialchars($element['value']);

        if ($element['mode'] == 'add' && $element['default-value'] != '') {
            $result['value'] = htmlspecialchars($element['default-value']);
        }

        $result['id'] = $element['id'] . $element['var-id'];

        if ($element['sub-var-id'] != false) {
            $result['id'] = $result['id'] . $element['sub-var-id'];
            $result['sub-var-id'] = '[' . $element['sub-var-id'] . ']';
        } else {
            $result['sub-var-id'] = '';
        }

        return $result;
    }

    /**
     * @param array $element
     * @return mixed
     * @author Joachim Doerr
     */
    public function getLabel($element) {

        if(!array_key_exists('label',$element)) {
            $element['label'] = '';
        }

        return $element['label'];
    }

    /**
     * @param $attributes
     * @return null|string
     * @author Joachim Doerr
     */
    private function getAttributes($attributes)
    {
        $inlineAttributes = NULL;

        if (sizeof($attributes) > 0) {
            foreach ($attributes as $key => $value) {
                if (!in_array($key, array('id', 'name', 'type', 'value', 'checked', 'selected'))) {
                    $inlineAttributes .= ' ' . $key . '="' . $value . '"';
                }
            }
        }

        return $inlineAttributes;
    }

    /**
     * @param $elements
     * @author Joachim Doerr
     */
    private function parseFormFields($elements)
    {
        if (sizeof($elements) > 0) {
            foreach ($elements as $key => $element) {
                switch ($element['type']) {
                    case 'close-fieldset':
                        $this->closeFieldset();
                        break;

                    case 'fieldset':
                        $this->generateFieldset($element);
                        break;

                    case 'html':
                    case 'headline':
                    case 'description':
                        $this->generateLineElement($element);
                        break;

                    case 'callback':
                        $this->getCallbackElement($element);
                        break;

                    case 'text':
                    case 'hidden':
                    case 'text-readonly':
                        $this->generateInputElement($element);
                        break;

                    case 'custom-link':
                        $this->generateCustomInputElement($element);
                        break;

                    case 'textarea':
                    case 'markitup':
                    case 'area-readonly':
                        $this->generateAreaElement($element);
                        break;

                    case 'select':
                    case 'multiselect':
                        $this->generateOptionsElement($element);
                        break;

                    case 'radio':
                    case 'radiobutton':
                        $this->generateRadioElement($element);
                        break;

                    case 'checkbox':
                        $this->generateCheckboxElement($element);
                        break;

                    case 'link':
                    case 'linklist':
                        $this->generateLinkElement($element);
                        break;

                    case 'media':
                    case 'medialist':
                        $this->generateMediaElement($element);
                        break;
                }
            }
        }
    }

    /**
     * @param $template
     * @return string
     * @author Joachim Doerr
     */
    public function setTheme($template)
    {
        global $path;
        global $defaultTemplate;

        if (is_dir($path . "/templates/" . $template . "_theme/") === true && $template != $defaultTemplate) {
            $this->template = $template;
            return
                PHP_EOL . '<!-- mform -->' .
                PHP_EOL . '  <link rel="stylesheet" type="text/css" href="?&mform_theme=' . $this->template . '" media="all" />' .
                PHP_EOL . '<!-- mform -->' . PHP_EOL;
        }
    }

    /**
     * parse form to template
     * @param $element
     * @param $type
     * @param bool|false $parseFinal
     * @return $this
     * @author Joachim Doerr
     */
    private function parseElementToTemplate($element, $type, $parseFinal = false)
    {
        global $path;
        global $defaultTemplate;

        $template = $defaultTemplate;
        if ($this->template != '') {
            $template = $this->template;
        }

        $templateString = '';

        if ($type != '' && $type != 'html') {
            $templateString = implode(file($path . "/templates/" . $template . "_theme/mform_" . $type . ".ini", FILE_USE_INCLUDE_PATH));
        }

        preg_match('|<mform:label>(.*?)</mform:label>|ism', $element, $arrLabel);
        preg_match('|<mform:element>(.*?)</mform:element>|ism', $element, $arrElement);

        switch ($type) {
            case 'default':
            case 'hidden':
                if ($templateString != '') {
                    $element = str_replace(array(' />', '<mform:label/>', '<mform:element/>'), array('/>', $arrLabel[1], $arrElement[1]), $templateString);
                }
                break;

            case 'html':
            case 'fieldset':
                $templateString = '<mform:output/>';

            case 'wrapper':
            default:
                if (isset($arrLabel[1]) === true or isset($arrElement[1]) === true) {
                    if (sizeof($arrLabel) > 0 && sizeof($arrElement) > 0) {
                        $element = $arrLabel[1] . $arrElement[1];
                    }
                }
                if ($templateString != '') {
                    $element = str_replace(array(' />', '<mform:output/>'), array('/>', $element), $templateString);
                }
                break;
        }
        if ($element != '') {
            $element = str_replace(array('<mform:element>', '<mform:element/>', '<mform:element />', '</mform:element>', '</ mform:element>'), '', $element);
        }
        if ($parseFinal === true) {
            if ($this->fieldset === true) {
                $element = $element . '</fieldset>';
            }
            $this->output = $element;
        } else {
            $this->output .= $element;
        }

        return $this;
    }

    /**
     * final parsing
     * @param $elements
     * @param bool|false $template
     * @return string
     * @author Joachim Doerr
     */
    public function parse($elements, $template = false)
    {
        if ($template != false) {
            $this->output .= $this->setTheme($template);
        }

        $this->parseFormFields($elements);
        $this->parseElementToTemplate($this->output, 'wrapper', true);

        return $this->output;
    }
}
