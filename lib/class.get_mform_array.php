<?php
/*
class.get_mform_array.php

@copyright Copyright (c) 2013 by Doerr Softwaredevelopment
@author mail[at]joachim-doerr[dot]com Joachim Doerr

@package redaxo4
@version 2.2.0
*/

// MFROM ARRAY GENERATOR CLASS
////////////////////////////////////////////////////////////////////////////////
class getMFormArray
{
  /**/
  // define defaults
  /**/
  public $strOutput;
  public $attributes = NULL;
  public $options = NULL;
  public $parameter = NULL;
  public $arrElements = array();
  public $arrResult = NULL;
  public $id = NULL;
  public $count = 0;
  public $validations = NULL;
  public $REX;
  
  /**/
  // generate element array - add fields
  /**/
  
  /*
  add field
  */
  public function addElement($strTyp, $intId, $strValue = NULL, $arrAttributes = array(), $arrOptions = array(), $arrParameter = array(), $intCatId = NULL, $arrValidation = array(), $strDefaultValue = NULL)
  {
    $this->id = $this->count++;
    $intSubId = false;
    $strMode = rex_request('function', 'string');
    
    if (is_array( $arrId = explode('.', str_replace(',','.',$intId) ) ) === true)
    {
      $intId = $arrId[0];
      
      if (sizeof($arrId) > 1)
      {
        $intSubId = $arrId[1];
      }
      if (method_exists ('rex_var', 'toArray') === false)
      {
        $intSubId = '';
      }
    }
    
    if (is_array($this->arrResult) === false && $strMode == 'edit')
    {
      $this->getRexVars();
      
      /*
      echo '<pre>';
      print_r($this->arrResult);
      echo '</pre>';
      */
    }
    
    if ($strValue === NULL)
    {
      if (is_array($this->arrResult) === true)
      {
        switch ($strTyp)
        {
          case 'linklist':
            $strValue = $this->arrResult['linklist'][$intId];
            break;
          case 'medialist':
            $strValue = $this->arrResult['filelist'][$intId];
            break;
          case 'link':
            $strValue = $this->arrResult['link'][$intId];
            break;
          case 'media':
            $strValue = $this->arrResult['file'][$intId];
            break;
          default:
            $strValue = $this->arrResult['value'][$intId];
            if (is_array($strValue) === true)
            {
              $strValue = $this->arrResult['value'][$intId][$intSubId];
            }
            break;
        }
      }
    }
    else
    {
      $strValue = $this->getLangData($strValue);
    }
    
    if ($strDefaultValue != NULL)
    {
      $strDefaultValue = $this->getLangData($strDefaultValue);
    }
    
    $this->arrElements[$this->id] = array(
      'type'          => $strTyp,
      'id'            => $this->id,
      'var-id'        => $intId,
      'sub-var-id'    => $intSubId,
      'value'         => $strValue,
      'default-value' => $strDefaultValue,
      'mode'          => $strMode,
      'cat-id'        => (is_numeric($intCatId) === true) ? $intCatId : 0,
      'size'          => '',
      'attributes'    => array(),
      'multi'         => '',
      'validation'    => array()
    );
    
    // unset attributes
    $this->attributes = NULL;
    
    if (sizeof($arrAttributes) > 0 )
    {
      $this->setAttributes($arrAttributes);
    }
    
    // unset options
    $this->options = NULL;
    
    if (sizeof($arrOptions) > 0 )
    {
      $this->addOptions($arrOptions);
    }
    
    // unset parameters
    $this->parameter = NULL;
    
    if (sizeof($arrParameter) > 0 )
    {
      $this->setParameters($arrParameter);
    }
    
    // unset validations
    $this->validations = NULL;
    
    if (sizeof($arrValidation) > 0 )
    {
      $this->setValidations($arrValidation);
    }
  }
  
  /*
  add element
  */
  public function addHtml($strValue)
  {
    return $this->addElement('html', NULL, $strValue);
  }
  
  public function addHeadline($strValue)
  {
    return $this->addElement('headline', NULL, $strValue);
  }
  
  public function addDescription($strValue)
  {
    return $this->addElement('description', NULL, $strValue);
  }
  
  public function addFieldset($strValue, $arrAttributes = array())
  {
    return $this->addElement('fieldset', NULL, $strValue, $arrAttributes);
  }
  
  /*
  add callback
  */
  public function callback($callable = NULL, $arrParameter = array())
  {
    if ((is_string($callable) === true or is_array($callable) === true) && is_callable($callable, true) === true)
    {
      $intId = $this->count++;
      $this->arrElements[$intId] = array(
        'type'       => 'callback',
        'id'         => $intId,
        'callable'   => $callable,
        'parameter'  => $arrParameter
      );
    }
  }
  
  /*
  add input fields
  */
  public function addInputField($strTyp, $intId, $strValue = NULL, $arrAttributes = array(), $arrValidations = array(), $strDefaultValue = NULL)
  {
    return $this->addElement($strTyp, $intId, NULL, $arrAttributes, NULL, NULL, NULL, $arrValidations, $strDefaultValue);
  }
  
  public function addHiddenField($intId, $strValue = NULL, $arrAttributes = array())
  {
    return $this->addInputField('hidden', $intId, $strValue, $arrAttributes);
  }
  
  public function addTextField($intId, $arrAttributes = array(), $arrValidations = array(), $strDefaultValue = NULL)
  {
    return $this->addInputField('text', $intId, NULL, $arrAttributes, $arrValidations, $strDefaultValue);
  }
  
  public function addTextAreaField($intId, $arrAttributes = array(), $arrValidations = array(), $strDefaultValue = NULL)
  {
    return $this->addInputField('textarea', $intId, NULL, $arrAttributes, $arrValidations, $strDefaultValue);
  }
  
  public function addTextReadOnlyField($intId, $strValue = NULL, $arrAttributes = array())
  {
    return $this->addInputField('text-readonly', $intId, $strValue, $arrAttributes);
  }
  
  public function addTextAreaReadOnlyField($intId, $strValue = NULL, $arrAttributes = array())
  {
    return $this->addInputField('area-readonly', $intId, $strValue, $arrAttributes);
  }
  
  /*
  add select fields
  */
  public function addOptionField($strTyp, $intId, $arrAttributes = array(), $arrOptions = array(), $strDefaultValue = NULL)
  {
    return $this->addElement($strTyp, $intId, NULL, $arrAttributes, $arrOptions, NULL, NULL, array(), $strDefaultValue);
  }
  
  public function addSelectField($intId, $arrOptions = array(), $arrAttributes = array(), $strSize = 1, $strDefaultValue = NULL)
  {
    return $this->addOptionField('select', $intId, $arrAttributes, $arrOptions, $strDefaultValue);
    $this->setSize($strSize);
  }
  
  public function addMultiSelectField($intId, $arrOptions = array(), $arrAttributes = array(), $strSize = 3, $strDefaultValue = NULL)
  {
    $this->addOptionField('multiselect', $intId, $arrAttributes, $arrOptions, $strDefaultValue);
    $this->setMultiple(true);
    $this->setSize($strSize);
  }
  
  /*
  add checkboxes
  */
  public function addCheckboxField($intId, $arrOptions = array(), $arrAttributes = array(), $strDefaultValue = NULL)
  {
    return $this->addOptionField('checkbox', $intId, $arrAttributes, $arrOptions, $strDefaultValue);
  }
  
  /*
  add radiobutton
  */
  public function addRadioField($intId, $arrOptions = array(), $arrAttributes = array(), $strDefaultValue = NULL)
  {
    return $this->addOptionField('radiobutton', $intId, $arrAttributes, $arrOptions, $strDefaultValue);
  }
    
  /*
  add rex link fields
  */
  public function addLinkField($intId, $arrParameter = array(), $intCatId = NULL, $arrAttributes = array())
  {
    return $this->addElement('link', $intId, NULL, $arrAttributes, array(), $arrParameter, $intCatId);
  }
  
  public function addLinklistField($intId, $arrParameter = array(), $intCatId = NULL, $arrAttributes = array())
  {
    return $this->addElement('linklist', $intId, NULL, $arrAttributes, array(), $arrParameter, $intCatId);
  }
  
  /*
  add rex media fields
  */
  public function addMediaField($intId, $arrParameter = array(), $intCatId = NULL, $arrAttributes = array())
  {
    return $this->addElement('media', $intId, NULL, $arrAttributes, array(), $arrParameter, $intCatId);
  }
  
  public function addMedialistField($intId, $arrParameter = array(), $intCatId = NULL, $arrAttributes = array())
  {
    return $this->addElement('medialist', $intId, NULL, $arrAttributes, array(), $arrParameter, $intCatId);
  }
  
  /**/
  // set label and attributes
  /**/
  
  /*
  add label
  */
  public function setLabel($strLabel)
  {
    $this->arrElements[$this->id]['label'] = $this->getLangData($strLabel);
  }
  
  /*
  add attribute s
  */
  public function setAttribute($strName, $strValue)
  {
    switch ($strName)
    {
      case 'label':
        $this->setLabel($strValue);
        break;
        
      case 'size':
        $this->setSize($strValue);
        break;
        
      case 'validation':
        if (is_array($strValue))
        {
          $arrValidation = $strValue;
          $this->setValidations($arrValidation);
        }
        break;
      
      case 'default-value':
        $this->setDefaultValue($strValue);
        break;
        
      default:
        $this->attributes[$strName] = $strValue;
        $this->arrElements[$this->id]['attributes'] = $this->attributes;
        break;
    }
  }
  
  public function setAttributes($arrAttributes)
  {
    $this->attributes = array();
    foreach ($arrAttributes as $strName => $strValue)
    {
      $this->setAttribute($strName, $strValue);
    }
  }
  
  /**/
  // set validation
  /**/
  
  /*
  add default validation
  */
  public function setValidation($strKey,$strValue)
  {
    switch ($strKey)
    {
      case 'empty':
          $this->setAttribute('data-required', 'true');
      break;
      case 'integer':
          $this->setAttribute('data-type', 'digits');
      break;
      case 'float':
          $this->setAttribute('data-type', 'number');
      break;
      case 'alphanum':
          $this->setAttribute('data-type', 'alphanum');
      break;
      case 'dateIso':
          $this->setAtttribute('data-type', 'dateIso');
      break;
      case 'compare':
      case 'email':
          $this->setAttribute('data-type', 'email');
      break;    
      case 'minlength':
          $this->setAttribute('data-minlength', $strValue);
      break;
      case 'maxlength':
          $this->setAttribute('data-maxlength', $strValue);
      break;
      case 'min':
          $this->setAttribute('data-min', $strValue);
      break;
      case 'max':
          $this->setAttribute('data-max', $strValue);
      break;
      case 'url':
          $this->setAttribute('data-type', 'url');
      break;    
      case 'regexp':
          $this->setAttribute('data-regexp', $strValue);
      break;
      case 'min':
          $this->setAttribute('data-mincheck', $strValue);
      break;
      case 'maxcheck':
          $this->setAttribute('data-maxcheck', $strValue);
      break;
      case 'custom':
        $this->validations[$strKey] = $strValue;
        $this->arrElements[$this->id]['validation'] = $this->validations;
        break;
    }
  }
  
  public function setValidations($arrValidations)
  {
    $this->validations = array();
    foreach ($arrValidations as $strKey => $strValue)
    {
      if (is_numeric ($strKey) === true)
      {
        $this->setValidation($strValue, '');
      }
      else
      {
        $this->setValidation($strKey, $strValue);
      }
    }
  }
  
  /*
  add custom validation
  */
  public function setCustomValidation($arrCustomValidation)
  {
  }
  
  public function setCustomValidations($arrCustomValidations)
  {
  }
  
  /**/
  // set default value
  /**/
  
  /*
  set defaut value
  */
  public function setDefaultValue($strValue)
  {
    $this->arrElements[$this->id]['default-value'] = $this->getLangData($strValue);
  }
  
  /**/
  // set options, multiple and size
  /**/
  
  /*
  add options
  */
  public function addOption($strValue,$intKey)
  {
    $this->options[$intKey] = $this->getLangData($strValue);
    $this->arrElements[$this->id]['options'] = $this->options;
  }
  
  public function addOptions($arrOptions)
  {
    $this->options = array();
    foreach ($arrOptions as $intKey => $strValue)
    {
      $this->addOption($strValue, $intKey);
    }
  }
  
  /*
  add multiple
  */
  public function setMultiple($boolMultiple)
  {
    if ($boolMultiple === true)
    {
      $this->arrElements[$this->id]['multi'] = true;
    }
  }
  
  /*
  add size
  */
  public function setSize($strSize)
  {
    if ((is_numeric($strSize) === true &&  $strSize > 0) or $strSize == 'full')
    {
      $this->arrElements[$this->id]['size'] = $strSize;
    }
  }
  
  /**/
  // set category and parameter
  /**/
  
  /*
  add category id
  */
  public function setCategory($intCatId)
  {
    if ($intCatId > 0)
    {
      $this->arrElements[$this->id]['cat-id'] = $intCatId;
    }
  }
  
  /*
  add parameter
  */
  public function setParameter($strName,$strValue)
  {
    switch ($strName)
    {
      case 'category':
        $this->setCategory($strValue);
        break;
      
      case 'label':
        $this->setLabel($strValue);
        break;
        
      default:
        $this->parameter[$strName] = $strValue;
        $this->arrElements[$this->id]['parameter'] = $this->parameter;
        break;  
    }
  }
  
  public function setParameters($arrParameter)
  {
    $this->parameter = array();
    foreach ($arrParameter as $strName => $strValue)
    {
      $this->setParameter($strName, $strValue);
    }
  }
  
  /**/
  // get global REX
  /**/
  
  public function getGlobalRex()
  {
    if (is_array($this->REX) === false)
    {
      global $REX;
      $this->REX = $REX;
    }
  }
  
  /**/
  // use user lang
  /**/
  
  public function getLangData($arrLangData)
  {
    if (is_array($arrLangData) === true)
    {
      $this->getGlobalRex();
      foreach ($arrLangData as $strKey => $strValue)
      {
        if ($strKey == $this->REX['LOGIN']->getLanguage() or $strKey . '_utf8' == $this->REX['LOGIN']->getLanguage())
        {
          $strLangData = $strValue;
        }
      }
      if ($strLangData == '')
      {
        $strLangData = reset($arrLangData);
      }
    }
    else
    {
      $strLangData = $arrLangData;
    }
    return $strLangData;
  }
  
  /**/
  // get rex values and vars
  /**/
  
  /*
  get rex var
  */
  public function getRexVars()
  {
    $intSliceId = rex_request('slice_id', 'int', false);
    
    if ($intSliceId != false)
    {
      $strTable = 'rex_article_slice';
      $strFields = '*';
      $strWhere = 'id="'.$_REQUEST['slice_id'].'"';
      
      $objSql = rex_sql::factory();
      $strQuery = '
        SELECT '. $strFields .'
        FROM '. $strTable .'
        WHERE '. $strWhere;
      
      $objSql->setQuery($strQuery);
      $rows = $objSql->getRows();
      
      if ($rows > 0)
      {
        $this->arrResult = array();
        
        for ($i = 1; $i <= 20; $i++)
        {
          $this->arrResult['value'][$i] = $objSql->getValue('value' . $i);
          
          if ($i <= 10)
          {
            $this->arrResult['filelist'][$i] = $objSql->getValue('filelist' . $i);
            $this->arrResult['linklist'][$i] = $objSql->getValue('linklist' . $i);
            $this->arrResult['file'][$i] = $objSql->getValue('file' . $i);
            $this->arrResult['link'][$i] = $objSql->getValue('link' . $i);
          }
          
          if (method_exists ('rex_var', 'toArray') === true)
          {
            if ($this->isSerial($this->arrResult['value'][$i]))
            {
              $result = rex_var::toArray($this->arrResult['value'][$i]);
              
              if (is_array($result) === true)
              {
                $this->arrResult['value'][$i] = $result;
              }
            }
          }
        }
      }
    }
    return $this->arrResult;
  }
  
  /*
  check serialize
  */
  public static function isSerial($string) {
    return (@unserialize($string) !== false);
  }
  
  /**/
  // final output
  /**/
  
  /*
  generate Output
  */
  public function arrFormElements()
  {
    $this->strOutput = $this->arrElements;
    return $this->strOutput;
  }
  
  public function getArray()
  {
    return $this->arrFormElements($this->arrElements);
  }

}
