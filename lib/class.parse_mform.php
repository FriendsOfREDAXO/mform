<?php
/*
class.parse_mform.php

@copyright Copyright (C) 2013 by Doerr Softwaredevelopment
@author mail[at]joachim-doerr[dot]com Joachim Doerr

@package redaxo5
@version 3.3.0
*/

// MFROM PARSER CLASS
////////////////////////////////////////////////////////////////////////////////
class parseMForm
{
  /**/
  // define defaults
  /**/
  
  public $strOutput;
  public $boolFieldset = false;
  public $strTemplateThemeName;
  
  /**/
  // generate fields
  /**/
  
  /*
  fieldset
  */
  public function generateFieldset($arrElement)
  {
    $arrElement['attributes'] = $this->getAttributes($arrElement['attributes']);
    $arrElement['close_fieldset'] = '';
    
    if ($this->boolFieldset === true)
    {
      $arrElement['close_fieldset'] = '</fieldset>';
    }
    else
    {
      $this->boolFieldset = true;
    }
    
    $strElement = <<<EOT
      
      <mform:element>{$arrElement['close_fieldset']}<fieldset {$arrElement['attributes']}><legend>{$arrElement['value']}</legend></mform:element>
      
EOT;
    return $this->parseElementToTemplate($strElement,$strTemplate);
  }

  public function closeFieldset($arrElement = NULL)
  {
    if ($this->boolFieldset === true)
    {
      $this->boolFieldset = false;
      $strElement = <<<EOT
      
        <mform:element></fieldset></mform:element>
      
EOT;
      return $this->parseElementToTemplate($strElement,$strTemplate);
    }
  }
  
  /*
  html, headline, description
  */
  public function generateLineElement($arrElement)
  {
    switch ($arrElement['type'])
    {
      case 'headline':
      case 'description':
      default:
        $strTemplate = $arrElement['type'];
        break;
    }
    $strElement = <<<EOT
      
      <mform:element>{$arrElement['value']}</mform:element>
      
EOT;
    return $this->parseElementToTemplate($strElement,$strTemplate);
  }
  
  /*
  callback
  */
  public function getCallbackElement($arrElement)
  {
    $strCallElement = call_user_func($arrElement['callabel'], $arrElement['parameter']);

    $strElement = <<<EOT
      
      <mform:element>$strCallElement</mform:element>
      
EOT;
    return $this->parseElementToTemplate($strElement,'html');
  }
  
  /*
  hidden, text, password
  */
  public function generateInputElement($arrElement)
  {
    $arrElement['attributes'] = $this->getAttributes($arrElement['attributes']);
    $arrVarId = $this->getVarAndIds($arrElement);
    
    switch ($arrElement['type'])
    {
      case 'hidden':
        $strTemplate = 'hidden';
        $arrElement['label'] = '';
        break;
        
      case 'text-readonly':
        $arrElement['type'] = 'text';
        $arrElement['attributes'] .= ' readonly="readonly"';
        
      default:
        $strTemplate = 'default';
        break;
    }
    
    $strElement = <<<EOT
      
      <mform:label><label for="rv{$arrVarId['id']}">{$arrElement['label']}</label></mform:label>
      <mform:element><input id="rv{$arrVarId['id']}" type="{$arrElement['type']}" name="REX_INPUT_VALUE[{$arrElement['var-id']}]{$arrVarId['sub-var-id']}" value="{$arrVarId['value']}" {$arrElement['attributes']} /></mform:element>
      
EOT;
    return $this->parseElementToTemplate($strElement,$strTemplate);
  }
  
  /*
  custum link
  */
  public function generateCustomInputElement($arrElement)
  {
    $arrElement['attributes'] = $this->getAttributes($arrElement['attributes']);
    $arrVarId = $this->getVarAndIds($arrElement);
    
    $arrI18N = array(
      'add_internlink' => rex_i18n::msg('mfrom_add_internlink'),
      'add_externlink' => rex_i18n::msg('mfrom_add_externlink'),
      'add_medialink' => rex_i18n::msg('mform_add_medialink'),
      'remove' => rex_i18n::msg('mform_remove_link')
    );
    
    switch ($arrElement['type'])
    {
      case 'custom-link':
      default:
        $strTemplate = 'default';
        $arrVarId['sub-var-id-value'] = $arrVarId['sub-var-id'];
        $arrVarId['sub-var-id'] = str_replace(array('[',']'), '', $arrVarId['sub-var-id']);
        $arrVarId['sub-var-id-for-id'] = ($arrElement['sub-var-id'] != '') ? '_'.$arrElement['sub-var-id'] : '';
        
        $arrVarId['hidden_value'] = $arrVarId['value'];
        $arrVarId['show_value'] = $arrVarId['value'];
        
        if (is_numeric($arrVarId['value']))
        {
          $art = rex_article::get($arrVarId['value']);
          if ($art instanceof rex_article)
          {
            $arrVarId['show_value'] = $art->getName();
          }
        }
        else
        {
          $arrVarId['show_value'] = $arrVarId['value'];
        }
        break;
    }
    
    $strElement = <<<EOT
      
      <mform:label><label for="rv{$arrVarId['id']}">{$arrElement['label']}</label></mform:label>
      <mform:element>
        <script>
          /* <![CDATA[ */
            jQuery(document).ready(function($) {
              var this_hidden_element_{$arrElement['var-id']}{$arrVarId['sub-var-id-for-id']} = $('#VALUE_{$arrElement['var-id']}{$arrVarId['sub-var-id-for-id']}'),
                  this_show_element_{$arrElement['var-id']}{$arrVarId['sub-var-id-for-id']} = $('#VALUE_{$arrElement['var-id']}{$arrVarId['sub-var-id-for-id']}_NAME'),
                  this_media_{$arrElement['var-id']}{$arrVarId['sub-var-id-for-id']} = $('#VALUE{$arrElement['var-id']}{$arrVarId['sub-var-id-for-id']}_MEDIUM'),
                  this_link_{$arrElement['var-id']}{$arrVarId['sub-var-id-for-id']} = $('#VALUE{$arrElement['var-id']}{$arrVarId['sub-var-id-for-id']}_LINK'),
                  this_extern_{$arrElement['var-id']}{$arrVarId['sub-var-id-for-id']} = $('#VALUE{$arrElement['var-id']}{$arrVarId['sub-var-id-for-id']}_EXTERN'),
                  this_remove_{$arrElement['var-id']}{$arrVarId['sub-var-id-for-id']} = $('#VALUE{$arrElement['var-id']}{$arrVarId['sub-var-id-for-id']}_REMOVE');
              
              this_media_{$arrElement['var-id']}{$arrVarId['sub-var-id-for-id']}.bind('click',function(){
                this_hidden_element_{$arrElement['var-id']}{$arrVarId['sub-var-id-for-id']}.attr('name','').attr('id','');
                this_show_element_{$arrElement['var-id']}{$arrVarId['sub-var-id-for-id']}.attr('name','REX_INPUT_VALUE[{$arrElement['var-id']}]{$arrVarId['sub-var-id-value']}').attr('id','REX_MEDIA_{$arrElement['var-id']}{$arrVarId['sub-var-id-for-id']}');
                openREXMedia({$arrElement['var-id']},'');return false;
              });
              
              this_link_{$arrElement['var-id']}{$arrVarId['sub-var-id-for-id']}.bind('click',function(){
                this_show_element_{$arrElement['var-id']}{$arrVarId['sub-var-id-for-id']}.attr('name','VALUE_NAME[{$arrElement['var-id']}]{$arrVarId['sub-var-id']}').attr('id','VALUE_{$arrElement['var-id']}{$arrVarId['sub-var-id-for-id']}_NAME');
                this_hidden_element_{$arrElement['var-id']}{$arrVarId['sub-var-id-for-id']}.attr('name','REX_INPUT_VALUE[{$arrElement['var-id']}]{$arrVarId['sub-var-id-value']}').attr('id','VALUE_{$arrElement['var-id']}{$arrVarId['sub-var-id-for-id']}');
                openLinkMap('VALUE_{$arrElement['var-id']}{$arrVarId['sub-var-id-for-id']}', '');return false;
              });
              
              this_extern_{$arrElement['var-id']}{$arrVarId['sub-var-id-for-id']}.bind('click',function(){
                var extern_link = prompt('Link','http://');
                this_hidden_element_{$arrElement['var-id']}{$arrVarId['sub-var-id-for-id']}.attr('name','').attr('id','');
                this_show_element_{$arrElement['var-id']}{$arrVarId['sub-var-id-for-id']}.attr('name','REX_INPUT_VALUE[{$arrElement['var-id']}]{$arrVarId['sub-var-id-value']}').attr('id','VALUE_{$arrElement['var-id']}{$arrVarId['sub-var-id-for-id']}');
                if (extern_link!="" && extern_link!=undefined) {
                  this_show_element_{$arrElement['var-id']}{$arrVarId['sub-var-id-for-id']}.attr('value',extern_link);
                  return false;
                }
              });
              this_remove_{$arrElement['var-id']}{$arrVarId['sub-var-id-for-id']}.bind('click',function(){
                this_hidden_element_{$arrElement['var-id']}{$arrVarId['sub-var-id-for-id']}.attr('value','');
                this_show_element_{$arrElement['var-id']}{$arrVarId['sub-var-id-for-id']}.attr('value','');
                return false;
              });
            });
          /* ]]> */
        </script>
        <div id="rex-widget-custom-link-{$arrElement['var-id']}{$arrVarId['sub-var-id']}" class="rex-widget">
          <input type="hidden" name="REX_INPUT_VALUE[{$arrElement['var-id']}]{$arrVarId['sub-var-id-value']}" id="VALUE_{$arrElement['var-id']}{$arrVarId['sub-var-id-for-id']}" value="{$arrVarId['hidden_value']}">
          <input type="text" size="30" name="VALUE_NAME[{$arrElement['var-id']}]{$arrVarId['sub-var-id']}" id="VALUE_{$arrElement['var-id']}{$arrVarId['sub-var-id-for-id']}_NAME" value="{$arrVarId['show_value']}" readonly="readonly">
          <span class="rex-button-group">
            <a href="#" class="rex-button" title="{$arrI18N['add_internlink']}" id="VALUE{$arrElement['var-id']}{$arrVarId['sub-var-id-for-id']}_LINK"><span class="rex-icon mform-icon-internlink-open"></span></a>
            <a href="#" class="rex-button" title="{$arrI18N['add_externlink']}" id="VALUE{$arrElement['var-id']}{$arrVarId['sub-var-id-for-id']}_EXTERN"><span class="rex-icon mform-icon-externlink-open"></span></a>
            <a href="#" class="rex-button" title="{$arrI18N['add_medialink']}" id="VALUE{$arrElement['var-id']}{$arrVarId['sub-var-id-for-id']}_MEDIUM"><span class="rex-icon mform-icon-media-open"></span></a>
            <a href="#" class="rex-button" title="{$arrI18N['remove']}" id="VALUE{$arrElement['var-id']}{$arrVarId['sub-var-id-for-id']}_REMOVE"><span class="rex-icon mform-remove-link"></span></a>
          </span>
        </div>
      </mform:element>
      
EOT;
    return $this->parseElementToTemplate($strElement,$strTemplate);
  }
  
  /*
  textarea, markitup
  */
  public function generateAreaElement($arrElement)
  {
    $arrElement['attributes'] = $this->getAttributes($arrElement['attributes']);
    $arrVarId = $this->getVarAndIds($arrElement);
    
    if ($arrElement['type'] == 'area-readonly')
    {
      $arrElement['attributes'] .= ' readonly="readonly"';
    }
    
    $strElement = <<<EOT
      
      <mform:label><label for="rv{$arrVarId['id']}">{$arrElement['label']}</label></mform:label>
      <mform:element><textarea id="rv{$arrVarId['id']}" name="REX_INPUT_VALUE[{$arrElement['var-id']}]{$arrVarId['sub-var-id']}" {$arrElement['attributes']} >{$arrVarId['value']}</textarea></mform:element>
      
EOT;
    return $this->parseElementToTemplate($strElement,'default');
  }
  
  /*
  select, multiselect
  */
  public function generateOptionsElement($arrElement)
  {
    $arrElement['attributes'] = $this->getAttributes($arrElement['attributes']);
    $strSelectAttributes = ''; $strMultiselectJavascript = ''; $strMultiselectHidden = ''; $arrHiddenValue = array(); $strOptions = ''; $arrDefaultValue = array(); $strHiddenValue = '';
    $strSelectAttributes = (is_numeric($arrElement['size']) === true) ? 'size="' . $arrElement['size'] . '"' : '' ;
    $arrVarId = $this->getVarAndIds($arrElement);
    
    if ($arrElement['size'] == 'full')
    {
      $strSelectAttributes = 'size="' . sizeof($arrElement['options']) . '"';
    }
    if ($arrElement['multi'] === true)
    {
      $strSelectAttributes .= ' multiple="multiple"';
      $strMultiselectJavascript = <<<EOT
        <script type="text/javascript">
          /* <![CDATA[ */
            jQuery(document).ready(function($){
              $("#rv{$arrVarId['id']}").change(function() {
                $("#hidden_rv{$arrVarId['id']}").val($(this).val());
              });
            });
          /* ]]> */
        </script>
EOT;
      $strMultiselectHidden = <<<EOT
        <input id="hidden_rv{$arrVarId['id']}" type="hidden" name="REX_INPUT_VALUE[{$arrElement['var-id']}]{$arrVarId['sub-var-id']}" value="{$arrElement['value']}" />
EOT;
      if ($arrElement['value'] != '')
      {
        $arrHiddenValue = explode(',',$arrElement['value']);
      }
      if ($arrElement['default-value'] != '')
      {
        $arrDefaultValue = explode(',',$arrElement['default-value']);
      }
    }
    else
    {
      $arrHiddenValue = array($arrElement['value']);
    }
    if (array_key_exists('options',$arrElement) === true)
    {
      foreach ($arrElement['options'] as $intKey => $strValue)
      {
        $strOptions .= '<option value="' . $intKey . '" ';
        foreach ($arrDefaultValue as $strDefaultValue)
        {
          if ($intKey == $strDefaultValue)
          {
            $arrElement['default-value'] = $strDefaultValue;
          }
        }
        foreach ($arrHiddenValue as $strHdValue)
        {
          if ($intKey == $strHdValue)
          {
            $strHiddenValue = $strHdValue;
          }
        }
        if (strval($intKey) === strval($strHiddenValue) or ($arrElement['mode'] == 'add' && $intKey == $arrElement['default-value']))
        {
          $strOptions .= 'selected="selected" ';
        }
        $strOptions .= '>' . $strValue . '</option>';
      }
    }
    $strElement = <<<EOT
      
      <mform:label><label for="rv{$arrVarId['id']}">{$arrElement['label']}</label>$strMultiselectJavascript</mform:label>
      <mform:element><select id="rv{$arrVarId['id']}" name="REX_INPUT_VALUE[{$arrElement['var-id']}]{$arrVarId['sub-var-id']}" {$arrElement['attributes']} $strSelectAttributes>$strOptions</select>$strMultiselectHidden</mform:element>
      
EOT;
    return $this->parseElementToTemplate($strElement,'default');
  }
  
  /*
  radio
  */
  public function generateRadioElement($arrElement)
  {
    $intCount = 0; $strOptions = '';
    $arrVarId = $this->getVarAndIds($arrElement);
    
    if (array_key_exists('options',$arrElement) === true)
    {
      foreach ($arrElement['options'] as $intKey => $strValue)
      {
        $intCount++;
        $strRadioAttributes = $this->getAttributes($arrElement['attributes']['radio-attr'][$intKey]);
        $strOptions .= '<div class="radio_element"><input id="rv' . $arrVarId['id'] . $intCount . '" type="radio" name="REX_INPUT_VALUE[' . $arrElement['var-id'] . ']' . $arrVarId['sub-var-id'] . '" value="' . $intKey . '" ' . $strRadioAttributes;
        if ($intKey == $arrElement['value'] or ($arrElement['mode'] == 'add' && $intKey == $arrElement['default-value']))
        {
          $strOptions .= ' checked="checked" ';
        }
        $strOptions .= ' /><span class="radio_description"><label class="description" for="rv' . $arrVarId['id'] . $intCount . '">' . $strValue . '</label></span></div>';
      }
    }
    $strElement = <<<EOT
      
      <mform:label><label for="rv{$arrVarId['id']}">{$arrElement['label']}</label></mform:label>
      <mform:element>$strOptions</mform:element>
      
EOT;
    return $this->parseElementToTemplate($strElement,'default');
  }
  
  /*
  checkbox
  */
  public function generateCheckboxElement($arrElement)
  {
    if (array_key_exists('options',$arrElement) === true)
    {
      $arrElement['attributes'] = $this->getAttributes($arrElement['attributes']);
      $array_key = array_keys($arrElement['options']);
      $arrElement['options'] = array(end($array_key) => end($arrElement['options']));
      $strOptions = '';
      
      $arrVarId = $this->getVarAndIds($arrElement);
      
      foreach ($arrElement['options'] as $intKey => $strValue)
      {
        $strOptions .= '<div class="radio_element"><input id="rv' . $arrVarId['id'] . '" type="checkbox" name="REX_INPUT_VALUE[' . $arrElement['var-id'] . ']' . $arrVarId['sub-var-id'] . '" value="' . $intKey . '" '. $arrElement['attributes'];
        if ($intKey == $arrElement['value'] or ($arrElement['mode'] == 'add' && $intKey == $arrElement['default-value']))
        {
          $strOptions .= ' checked="checked" ';
        }
        $strOptions .= ' /><span class="radio_description"><label class="description" for="rv' . $arrVarId['id'] . '">' . $strValue . '</label></span></div>';
      }
    }
    $strElement = <<<EOT
      
      <mform:label><label for="rv{$arrVarId['id']}">{$arrElement['label']}</label></mform:label>
      <mform:element>$strOptions</mform:element>
      
EOT;
    return $this->parseElementToTemplate($strElement,'default');
  }
  
  /*
  link, linklist
  */
  public function generateLinkElement($arrElement)
  {
    if (sizeof($arrElement['parameter']) >= 0)
    {
      $arrElement['parameter'] = array();
    }
    if ($arrElement['type'] == 'link')
    {
      $strOptions = rex_var_link::getWidget($arrElement['var-id'], 'REX_INPUT_LINK[' . $arrElement['var-id'] . ']', $arrElement['value'], $arrElement['parameter']);
    }
    if ($arrElement['type'] == 'linklist')
    {
      $strOptions = rex_var_linklist::getWidget($arrElement['var-id'], 'REX_INPUT_LINKLIST[' . $arrElement['var-id'] . ']', $arrElement['value'], $arrElement['parameter']);
    }
    $strElement = <<<EOT
      
      <mform:label><label>{$arrElement['label']}</label></mform:label>
      <mform:element>$strOptions</mform:element>
      
EOT;
    return $this->parseElementToTemplate($strElement,'default');
  }
  
  /*
  media, medialist
  */
  public function generateMediaElement($arrElement)
  {
    if (sizeof($arrElement['parameter']) >= 0)
    {
      $arrElement['parameter'] = array();
    }
    if ($arrElement['type'] == 'media')
    {
      $strOptions = rex_var_media::getWidget($arrElement['var-id'], 'REX_INPUT_MEDIA[' . $arrElement['var-id'] . ']', $arrElement['value'], $arrElement['parameter']);
    }
    if ($arrElement['type'] == 'medialist')
    {
      $strOptions = rex_var_medialist::getWidget($arrElement['var-id'], 'REX_INPUT_MEDIALIST[' . $arrElement['var-id'] . ']', $arrElement['value'], $arrElement['parameter']);        
    }
    $strElement = <<<EOT
      
      <mform:label><label>{$arrElement['label']}</label></mform:label>
      <mform:element>$strOptions</mform:element>
      
EOT;
    return $this->parseElementToTemplate($strElement,'default');
  }
  
  /**/
  // get varAndIds
  /**/
  
  public function getVarAndIds($arrElement)
  {
    $arrResult = array();
    
    $arrResult['value'] = $arrElement['value'];
    
    if ($arrElement['mode'] == 'add' && $arrElement['default-value'] != '')
    {
      $arrResult['value'] = $arrElement['default-value'];
    }
    
    $arrResult['id'] = $arrElement['id'] . $arrElement['var-id'];
    
    if ($arrElement['sub-var-id'] != false)
    {
      $arrResult['id'] = $arrResult['id'] . $arrElement['sub-var-id'];
      $arrResult['sub-var-id'] = '['.$arrElement['sub-var-id'].']';
    }
    else
    {
      $arrResult['sub-var-id'] = '';
    }
    
    return $arrResult;
  }
  
  
  /**/
  // get attributes
  /**/
  
  public function getAttributes($arrAttributes)
  {
    $strAttributes = NULL;
    if (sizeof($arrAttributes) > 0)
    {
      foreach ($arrAttributes as $strKey => $strValue)
      {
        if (!in_array($strKey, array('id', 'name', 'type', 'value', 'checked', 'selected')))
        {
          $strAttributes .= ' '.$strKey.'="'.$strValue.'"';
        }
      }
    }
    return $strAttributes;
  }
  
  /**/
  // parse form fields by types
  /**/
  
  public function parseFormFields($arrElements)
  {
    if (sizeof($arrElements) > 0)
    {
      foreach ($arrElements as $intKey => $arrElement)
      {
        switch ($arrElement['type'])
        {
          case 'close-fieldset':
            $this->closeFieldset($arrElement);
            break;
            
          case 'fieldset':
            $this->generateFieldset($arrElement);
            break;
            
          case 'html':
          case 'headline':
          case 'description':
            $this->generateLineElement($arrElement);
            break;
          
          case 'callback':
            $this->getCallbackElement($arrElement);
            break;
          
          case 'text':
          case 'hidden':
          case 'text-readonly':
            $this->generateInputElement($arrElement);
            break;
          
          case 'custom-link':
            $this->generateCustomInputElement($arrElement);
            break;
          
          case 'textarea':
          case 'markitup':
          case 'area-readonly':
            $this->generateAreaElement($arrElement);
            break;
          
          case 'select':
          case 'multiselect':
            $this->generateOptionsElement($arrElement);
            break;
          
          case 'radio':
          case 'radiobutton':
            $this->generateRadioElement($arrElement);
            break;
          
          case 'checkbox':
            $this->generateCheckboxElement($arrElement);
            break;
          
          case 'link':
          case 'linklist':
            $this->generateLinkElement($arrElement);
            break;
          
          case 'media':
          case 'medialist':
            $this->generateMediaElement($arrElement);
            break;
        }
      }
    }
  }
  
  /**/
  // set theme
  /**/
  
  public function setTheme($strNewTemplateThemeName)
  {
    $strMformAddonPath = rex_path::addon('mform');
    $strDefaultTemplateThemeName = rex_addon::get('mform')->getConfig('mform_template');
    
    if (is_dir($strMformAddonPath . "/templates/" . $strNewTemplateThemeName . "_theme/") === true && $strNewTemplateThemeName != $strDefaultTemplateThemeName)
    {
      $this->strTemplateThemeName = $strNewTemplateThemeName;
      return 
        PHP_EOL.'<!-- mform -->'.
        PHP_EOL.'  <link rel="stylesheet" type="text/css" href="?&mform_theme=' . $this->strTemplateThemeName . '" media="all" />'.
        PHP_EOL.'<!-- mform -->'.PHP_EOL;
    }
  }
  
  /**/
  // parse form to template
  /**/
  
  public function parseElementToTemplate($strElement, $strTemplateKey, $boolParseFinal = false)
  {
    $strMformAddonPath = rex_path::addon('mform');
    $strDefaultTemplateThemeName = rex_addon::get('mform')->getConfig('mform_template');
    
    $strTemplateThemeName = $strDefaultTemplateThemeName;
    if ($this->strTemplateThemeName != '')
    {
      $strTemplateThemeName = $this->strTemplateThemeName;
    }
    
    if ($strTemplateKey != '' && $strTemplateKey != 'html')
    {
      $strTemplate = implode(file($strMformAddonPath . "/templates/" . $strTemplateThemeName . "_theme/mform_" . $strTemplateKey . ".ini", FILE_USE_INCLUDE_PATH));
    }
    
    preg_match('|<mform:label>(.*?)</mform:label>|ism', $strElement, $arrLabel);
    preg_match('|<mform:element>(.*?)</mform:element>|ism', $strElement, $arrElement);
    
    switch ($strTemplateKey)
    {
      case 'default':
      case 'hidden':
        if ($strTemplate != '')
        {
          $strElement = str_replace(array(' />','<mform:label/>','<mform:element/>'), array('/>',$arrLabel[1],$arrElement[1]), $strTemplate);
        }
        break;
        
      case 'html':
      case 'fieldset':
        $strTemplate = '<mform:output/>';
        
      case 'wrapper':
      default:
        if (isset($arrLabel[1]) === true or isset($arrElement[1]) === true)
        {
          if (sizeof($arrLabel) > 0 && sizeof($arrElement) > 0)
          {
            $strElement = $arrLabel[1].$arrElement[1];
          }
        }
        if ($strTemplate != '')
        {
          $strElement = str_replace(array(' />','<mform:output/>'), array('/>',$strElement), $strTemplate);
        }
        break;
    }
    if ($strElement != '')
    {
      $strElement = str_replace(array('<mform:element>','<mform:element/>','<mform:element />', '</mform:element>', '</ mform:element>'), '', $strElement);
    }
    if ($boolParseFinal === true)
    {
      $this->strOutput = $strElement;
      
      if ($this->boolFieldset === true)
      {
        $strElement = $strElement.'</fieldset>';
      }
    }
    else
    {
      $this->strOutput .= $strElement;
    }
  }
  
  /*
  final parseing
  */
  public function parse_mform($arrElements, $strNewTemplateThemeName = false)
  {
    if ($strNewTemplateThemeName != false)
    {
      $this->strOutput .= $this->setTheme($strNewTemplateThemeName);
    }
    $this->parseFormFields($arrElements);
    $this->parseElementToTemplate($this->strOutput,'wrapper',true);
    return $this->strOutput;
  }
}
