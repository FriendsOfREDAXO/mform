<?php
/*
class.a967_get_mform_array.inc.php

@copyright Copyright (c) 2012 by Doerr Softwaredevelopment
@author mail[at]joachim-doerr[dot]com Joachim Doerr

@package redaxo4
@version 2.1.3
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
  
  
  /**/
  // generate element array - add fields
  /**/
  
  /*
  add field
  */
  public function addElement($strTyp, $intId, $strValue = NULL, $arrAttributes = array(), $arrOptions = array(), $arrParameter = array(), $intCatId = NULL)
  {
    $this->id = $intId;
    $this->arrElements[$this->id] = array(
      'type'       => $strTyp,
      'id'         => $intId,
      'default'    => $strValue,
      'cid'        => (is_numeric($intCatId) === true) ? $intCatId : 0,
      'size'       => '',
      'attributes' => array(),
      'multi'      => ''
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
  
  /*
  add input fields
  */
  public function addInputField($strTyp, $intId, $strValue = NULL, $arrAttributes = array())
  {
    return $this->addElement($strTyp, $intId, $strValue, $arrAttributes);
  }
  
  public function addHiddenField($intId, $strValue = NULL, $arrAttributes = array())
  {
    return $this->addInputField('hidden', $intId, $strValue, $arrAttributes);
  }
  
  public function addTextField($intId, $strValue = NULL, $arrAttributes = array())
  {
    return $this->addInputField('text', $intId, $strValue, $arrAttributes);
  }
  
  public function addTextAreaField($intId, $strValue = NULL, $arrAttributes = array())
  {
    return $this->addInputField('textarea', $intId, $strValue, $arrAttributes);
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
  
  public function setMultiple($boolMultiple)
  {
    if ($boolMultiple === true)
    {
      $this->arrElements[$this->id]['multi'] = true;
    }
  }

  public function setSize($strSize)
  {
    if ((is_numeric($strSize) === true &&  $strSize > 0) or $strSize == 'full')
    {
      $this->arrElements[$this->id]['size'] = $strSize;
    }
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
    foreach($arrOptions as $intKey => $strValue)
    {
      $this->addOption($strValue, $intKey);
    }
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
    if ($strName == 'category')
    {
      $this->setCategory($strValue);
    }
    else if ($strName == 'label')
    {
      $this->setLabel($strValue);
    }
    else
    {
      $this->parameter[$strName] = $strValue;
      $this->arrElements[$this->id]['parameter'] = $this->parameter;
    }
  }
  
  public function setParameters($arrParameter)
  {
    $this->parameter = array();
    foreach($arrParameter as $strName => $strValue)
    {
      $this->setParameter($strName, $strValue);
    }
  }
  
  
  /**/
  // set label and attributes
  /**/
  
  public function setLabel($strLabel)
  {
    $this->arrElements[$this->id]['label'] = $strLabel;
  }
  
  public function setAttribute($strName, $strValue)
  {
    if ($strName == 'label')
    {
      $this->setLabel($strValue);
    }
    else if ($strName == 'size')
    {
      $this->setSize($strValue);
    }
    else
    {
      $this->attributes[$strName] = $strValue;
      $this->arrElements[$this->id]['attributes'] = $this->attributes;
    }
  }
  
  public function setAttributes($arrAttributes)
  {
    $this->attributes = array();
    foreach($arrAttributes as $strName => $strValue)
    {
      $this->setAttribute($strName, $strValue);
    }
  }
  
  
  /**/
  // set label and attributes
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
