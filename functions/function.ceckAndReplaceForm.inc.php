<?php
/*
mform function.ceckAndReplaceForm.inc.php

@author mail[at]joachim-doerr[dot]com Joachim Doerr
@author <a href="http://joachim-doerr.com">joachim-doerr.com</a>

@package redaxo4
@version 1.2
*/

if (!function_exists('ceckAndReplaceForm')) {
  function ceckAndReplaceForm ($arrLine) {
    
    foreach($arrLine as $intKey => $strLine) {
      $arrLine[$intKey] = str_replace("<mform:n/>", chr(10), $strLine);
    }
    
    switch ($arrLine[0]) {
      
      /*****************************************
      html, headline, description
      *****************************************/
      case 'html':
      case 'single_line':
      case 'headline':
      case 'description':
      
        $strTemplate = 'single_line';
        
        if ($arrLine[0] == 'headline') { $strTemplate = 'single_headline'; }
        if ($arrLine[0] == 'description') { $strTemplate = 'description_line'; }
        
        $strOutput = <<<EOT
        
        <mform:output>{$arrLine[1]}</mform:output>
        
EOT;
        $strOutput = parseMFormTemplate($strOutput,$strTemplate);
        break;
        
        
        
      /*****************************************
      hidden, text
      *****************************************/
      case 'hidden':
      case 'text':
      
        if (isset($arrLine[4]) === true) { $arrLine[4] = 'class="' . $arrLine[4] . '"'; }
        if (isset($arrLine[5]) === true) { $arrLine[5] = 'style="' . $arrLine[5] . '"'; }
        
        $strOutput = <<<EOT
        
        <mform:label><label for="rv{$arrLine[1]}">$arrLine[2]</label></mform:label>
        <mform:element><input id="rv{$arrLine[1]}" type="{$arrLine[0]}" name="VALUE[{$arrLine[1]}]" value="{$arrLine[3]}" {$arrLine[4]} {$arrLine[5]} /></mform:element>
        
EOT;
        $strOutput = parseMFormTemplate($strOutput,'default_line');
        break;
        
        
        
      /*****************************************
      textarea, markitup
      *****************************************/
      case 'textarea':
      case 'markitup':
      
        if (isset($arrLine[4]) === true) { $arrLine[4] = 'class="' . $arrLine[4] . '"'; }
        if (isset($arrLine[5]) === true) { $arrLine[5] = 'style="' . $arrLine[5] . '"'; }
        
        $strOutput = <<<EOT
        
        <mform:label><label for="rv{$arrLine[1]}">$arrLine[2]</label></mform:label>
        <mform:element><textarea id="rv{$arrLine[1]}" name="VALUE[{$arrLine[1]}]" {$arrLine[4]} {$arrLine[5]} >{$arrLine[3]}</textarea></mform:element>
        
EOT;
        $strOutput = parseMFormTemplate($strOutput,'default_line');
        break;
        
        
        
      /*****************************************
      select, multiselect
      *****************************************/
      case 'select':
      case 'multiselect':

        if (isset($arrLine[5]) === true) { $arrLine[5] = 'class="' . $arrLine[5] . '"'; }
        if (isset($arrLine[6]) === true) { $arrLine[6] = 'style="' . $arrLine[6] . '"'; }
        
        $strMultiselect = '';
        $arrOptions = explode(';',trim($arrLine[4]));
        
        if ($arrLine[0] == 'multiselect') {
          // multiselect use jquery
          $strMultiselect = (is_numeric($arrLine[7]) === true) ? 'size="' . $arrLine[7] . '" multiple="multiple"' : 'size="5" multiple="multiple"' ;
          $strMultiselectJavascript = <<<EOT
          <script type="text/javascript">
            /* <![CDATA[ */
              jQuery(document).ready(function($){
                $("#rv{$arrLine[1]}").change(function() {
                  $("#hidden_rv{$arrLine[1]}").val($(this).val());
                });
              });
            /* ]]> */
          </script>
EOT;
          $strMultiselectHidden = <<<EOT
          <input id="hidden_rv{$arrLine[1]}" type="hidden" name="VALUE[{$arrLine[1]}]" value="{$arrLine[3]}" />
EOT;
          if ($arrLine[3] != '') { $arrHiddenValue = explode(',',$arrLine[3]); } else { $arrHiddenValue = array(); }
          
          foreach ($arrOptions as $strValue) {
            $arrValue = explode('=',trim($strValue));
            $strOutput .= '<option value="' . $arrValue[0] . '" ';
              foreach ($arrHiddenValue as $strHiddenValue) {
                if ($arrValue[0] == $strHiddenValue) { $strOutput .= 'selected="selected" '; }
              }
            $strOutput .= '>'.$arrValue[1].'</option>';
          }
        } else {
          // default select easy
          foreach ($arrOptions as $strValue) {
            $arrValue = explode('=',trim($strValue));
            $strOutput .= '<option value="' . $arrValue[0] . '" ';
             if ($arrValue[0] == $arrLine[3]) { $strOutput .= 'selected="selected" '; }
            $strOutput .= '>'.$arrValue[1].'</option>';
          }
        }
        
        $strOutput = <<<EOT
        
        <mform:label><label for="rv{$arrLine[1]}">$arrLine[2]</label>$strMultiselectJavascript</mform:label>
        <mform:element><select id="rv{$arrLine[1]}" name="VALUE[{$arrLine[1]}]" {$arrLine[5]} {$arrLine[6]} $strMultiselect>$strOutput</select>$strMultiselectHidden</mform:element>
        
EOT;
        $strOutput = parseMFormTemplate($strOutput,'default_line');
        break;
        
        
        
      /*****************************************
      radio
      *****************************************/
      case 'radio':
        
        $intCount = 0;
        $arrOptions = explode(';',trim($arrLine[4]));
        
        foreach ($arrOptions as $strValue) {
          
          $intCount++;
          $arrValue = explode('=',trim($strValue));
          
          $strOutput .= '<div class="radio_element"><input id="rv' . $arrLine[1].$intCount . '" type="radio" name="VALUE[' . $arrLine[1] . ']" value="' . $arrValue[0] . '" ';
           if ($arrValue[0] == $arrLine[3]) { $strOutput .= 'checked="checked" '; }
          $strOutput .= ' /><span for="rv' . $arrLine[1] . '" class="radio_description"><label for="rv' . $arrLine[1].$intCount . '">' . $arrValue[1] . '</label></span></div>';
        }
        
        $strOutput = <<<EOT
        
        <mform:label><label for="rv{$arrLine[1]}">$arrLine[2]</label></mform:label>
        <mform:element>$strOutput</mform:element>
        
EOT;
        $strOutput = parseMFormTemplate($strOutput,'default_line');
        break;
        
        
        
      /*****************************************
      checkbox
      *****************************************/
      case 'checkbox':
        
        $strDescription = '';
        $strValue = 1;
        
        if (isset($arrLine[4]) === true) {
          $arrValue = explode('=',trim($arrLine[4]));
          $strValue = $arrValue[0];
          if (isset($arrValue[1]) === true) { $strDescription = '<span for="rv' . $arrLine[1] . '" class="check_description"><label for="rv' . $arrLine[1]. '">' . $arrValue[1] . '</label></span>'; }
        }
        if ($arrLine[3] == $arrLine[4]) { $strChecked = 'checked="checked"'; }

        $strOutput = <<<EOT
        
        <mform:label><label for="rv{$arrLine[1]}">$arrLine[2]</label></mform:label>
        <mform:element><input id="rv{$arrLine[1]}" type="checkbox" name="VALUE[{$arrLine[1]}]" value="$strValue" {$arrLine[5]} $strChecked />{$strDescription}</mform:element>

EOT;
        $strOutput = parseMFormTemplate($strOutput,'default_line');
        break;
        
        
        
      /*****************************************
      link, linklist
      *****************************************/
      case 'link':
      case 'linklist':
        
        $intCategoryId = (is_numeric($arrLine[4]) === true) ? $arrLine[4] : 0 ;
        if ($arrLine[0] == 'link') { $strOutput = rex_var_link::getLinkButton($arrLine[1], $arrLine[3], $intCategoryId); }
        if ($arrLine[0] == 'linklist') { $strOutput = rex_var_link::getLinkListButton($arrLine[1], $arrLine[3], $intCategoryId); }
        $strOutput = <<<EOT
        
        <mform:label><label>$arrLine[2]</label></mform:label>
        <mform:element>$strOutput</mform:element>
        
EOT;
        $strOutput = parseMFormTemplate($strOutput,'default_line');
        break;
        
        
        
      /*****************************************
      media
      *****************************************/
      case 'media':
      
        $arrArg = array();
        $arrArgs = explode(';',trim($arrLine[5]));
        
        if (sizeof($arrArgs) > 1) {
          foreach ($arrArgs as $value) {
            $arrValue = explode('=', $value);
            $arrArg[$arrValue[0]] = $arrValue[1];
          }
        }
        
        $intCategoryId = (is_numeric($arrLine[4]) === true) ? $arrLine[4] : 0 ;
        $strOutput = rex_var_media::getMediaButton($arrLine[1], $intCategoryId, $arrArg);
        $strOutput = str_replace('REX_MEDIA['. $arrLine[1] .']', $arrLine[3], $strOutput);
        
        $strOutput = <<<EOT
        
        <mform:label><label>$arrLine[2]</label></mform:label>
        <mform:element>$strOutput</mform:element>
        
EOT;
        $strOutput = parseMFormTemplate($strOutput,'default_line');
        break;
        
        
        
      /*****************************************
      medialist
      *****************************************/
      case 'medialist':
      
        $arrArg = array();
        $arrArgs = explode(';',trim($arrLine[5]));
        
        if (sizeof($arrArgs) > 1) {
          foreach ($arrArgs as $value) {
            $arrValue = explode('=', $value);
            $arrArg[$arrValue[0]] = $arrValue[1];
          }
        }
        
        $intCategoryId = (is_numeric($arrLine[4]) === true) ? $arrLine[4] : 0 ;
        $strOutput = rex_var_media::getMediaListButton($arrLine[1], $arrLine[3], $intCategoryId, $arrArg);        
        
        $strOutput = <<<EOT
        
        <mform:label><label>$arrLine[2]</label></mform:label>
        <mform:element>$strOutput</mform:element>
        
EOT;
        $strOutput = parseMFormTemplate($strOutput,'default_line');
        break;
    }
        
    return $strOutput;
  }
}