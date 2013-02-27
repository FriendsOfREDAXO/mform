<?php
/*
class.a967_get_mform_array.inc.php

@copyright Copyright (c) 2012 by Doerr Softwaredevelopment
@author mail[at]joachim-doerr[dot]com Joachim Doerr

@package redaxo4
@version 2.1.4
*/

// MFROM ARRAY GENERATOR CLASS
////////////////////////////////////////////////////////////////////////////////
class a967_getmformArray
{
  /**/
  // define defaults
  /**/
  public $strOutput;
  public $attributes = NULL;
  public $options = NULL;
  public $parameter = NULL;
  public $arrElements = array();
  public $id = NULL;
  public $count = 967;
  public $validations = NULL;
  
  /**/
  // generate element array - add fields
  /**/
  
  /*
  add field
  */
  public function addElement($strTyp, $intId, $strValue = NULL, $arrAttributes = array(), $arrOptions = array(), $arrParameter = array(), $intCatId = NULL, $arrValidation = array())
  {
    $this->id = $intId;
    $this->arrElements[$this->id] = array(
      'type'       => $strTyp,
      'id'         => $intId,
      'default'    => $strValue,
      'cid'        => (is_numeric($intCatId) === true) ? $intCatId : 0,
      'size'       => '',
      'attributes' => array(),
      'multi'      => '',
      'validation' => array()
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
    return $this->addElement('html', $this->count++, $strValue);
  }
  
  public function addHeadline($strValue)
  {
    return $this->addElement('headline', $this->count++, $strValue);
  }
  
  public function addDescription($strValue)
  {
    return $this->addElement('description', $this->count++, $strValue);
  }
  public function addFieldset($strValue, $arrAttributes = array())
  {
    return $this->addElement('fieldset', $this->count++, $strValue, $arrAttributes);
  }
  
  /*
  add callback
  */
  public function callback($callable = NULL, $arrParameter = array())
  {
    if ((is_string($callable) === true or is_array($callable) === true) && is_callable($callable, true) === true)
    {
      $this->id = $this->count++;
      $this->arrElements[$this->id] = array(
        'type'       => 'callback',
        'id'         => $this->id,
        'callable'   => $callable,
        'parameter'  => $arrParameter
      );
    }
  }
  
  /*
  add input fields
  */
  public function addInputField($strTyp, $intId, $strValue = NULL, $arrAttributes = array(), $arrValidations = array())
  {
    return $this->addElement($strTyp, $intId, $strValue, $arrAttributes, NULL, NULL, NULL, $arrValidations);
  }
  
  public function addHiddenField($intId, $strValue = NULL, $arrAttributes = array())
  {
    return $this->addInputField('hidden', $intId, $strValue, $arrAttributes);
  }
  
  public function addTextField($intId, $strValue = NULL, $arrAttributes = array(),$arrValidations = array())
  {
    return $this->addInputField('text', $intId, $strValue, $arrAttributes, $arrValidations);
  }
  
  public function addTextAreaField($intId, $strValue = NULL, $arrAttributes = array(),$arrValidations = array())
  {
    return $this->addInputField('textarea', $intId, $strValue, $arrAttributes,$arrValidations);
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
  public function addOptionField($strTyp, $intId, $strValue = NULL, $arrAttributes = array(), $arrOptions = array())
  {
    return $this->addElement($strTyp, $intId, $strValue, $arrAttributes, $arrOptions);
  }
  
  public function addSelectField($intId, $strValue = NULL, $arrOptions = array(), $arrAttributes = array(), $strSize = 1)
  {
    return $this->addOptionField('select', $intId, $strValue, $arrAttributes, $arrOptions);
    $this->setSize($strSize);
  }
  
  public function addMultiSelectField($intId, $strValue = NULL, $arrOptions = array(), $arrAttributes = array(), $strSize = 3)
  {
    $this->addOptionField('multiselect', $intId, $strValue, $arrAttributes, $arrOptions);
    $this->setMultiple(true);
    $this->setSize($strSize);
  }
  
  /*
  add checkboxes
  */
  public function addCheckboxField($intId, $strValue = NULL, $arrOptions = array(), $arrAttributes = array())
  {
    return $this->addOptionField('checkbox', $intId, $strValue, $arrAttributes, $arrOptions);
  }
  
  /*
  add radiobutton
  */
  public function addRadioField($intId, $strValue = NULL, $arrOptions = array(), $arrAttributes = array())
  {
    return $this->addOptionField('radiobutton', $intId, $strValue, $arrAttributes, $arrOptions);
  }
    
  /*
  add rex link fields
  */
  public function addLinkField($intId, $strValue = NULL, $arrParameter = array(), $intCatId = NULL, $arrAttributes = array())
  {
    return $this->addElement('link', 'link-' . $intId, $strValue, $arrAttributes, array(), $arrParameter, $intCatId);
  }
  
  public function addLinklistField($intId, $strValue = NULL, $arrParameter = array(), $intCatId = NULL, $arrAttributes = array())
  {
    return $this->addElement('linklist', 'linklist-' . $intId, $strValue, $arrAttributes, array(), $arrParameter, $intCatId);
  }
  
  /*
  add rex media fields
  */
  public function addMediaField($intId, $strValue = NULL, $arrParameter = array(), $intCatId = NULL, $arrAttributes = array())
  {
    return $this->addElement('media', 'media-' . $intId, $strValue, $arrAttributes, array(), $arrParameter, $intCatId);
  }
  
  public function addMedialistField($intId, $strValue = NULL, $arrParameter = array(), $intCatId = NULL, $arrAttributes = array())
  {
    return $this->addElement('medialist', 'medialist-' . $intId, $strValue, $arrAttributes, array(), $arrParameter, $intCatId);
  }
  
  /**/
  // set label and attributes
  /**/
  
  /*
  add label
  */
  public function setLabel($strLabel)
  {
    $this->arrElements[$this->id]['label'] = $strLabel;
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
  	      $this->setValidation($arrValidation);
  	    }
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
  // set options, multiple and size
  /**/
  
  /*
  add options
  */
  public function addOption($strValue,$intKey)
  {
    $this->options[$intKey] = $strValue;
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
      $this->arrElements[$this->id]['cid'] = $intCatId;
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
  // final output
  /**/
  
  /*
  generate Output
  */
  public function arrFormElements() {
    $this->strOutput = $this->arrElements;
    return $this->strOutput;
  }
  
  public function getArray() {
    return $this->arrFormElements($this->arrElements);
  }

}
