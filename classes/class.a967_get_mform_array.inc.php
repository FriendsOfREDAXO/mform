<?php
/*
class.a967_getmformArray.inc.php

@author mail[at]joachim-doerr[dot]com Joachim Doerr
@author <a href="http://joachim-doerr.com">joachim-doerr.com</a>

@package redaxo4
@version 2.1.2
*/

// MFROM ARRAY GENERATOR CLASS
////////////////////////////////////////////////////////////////////////////////
class a967_getmformArray
{
  /**/
  // define defaults
  /**/
  var $strOutput;
  var $attributes = NULL;
  var $options = NULL;
  var $parameter = NULL;
  var $arrElements = array();
  var $id = NULL;
  var $count = 967;
  
  
  /**/
  // generate element array - add fields
  /**/
  
  /*
  add field
  */
  function addElement($strTyp, $intId, $strValue = NULL, $arrAttributes = array(), $arrOptions = array(), $arrParameter = array(), $intCatId = NULL)
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
    
    // unset options
    $this->parameter = NULL;
    
    if (sizeof($arrParameter) > 0 )
    {
      $this->setParameters($arrParameter);
    }
  }
  
  /*
  add element
  */
  function addHtml($strValue)
  {
    return $this->addElement('html', $this->count++, $strValue);
  }
  
  function addHeadline($strValue)
  {
    return $this->addElement('headline', $this->count++, $strValue);
  }
  
  function addDescription($strValue)
  {
    return $this->addElement('description', $this->count++, $strValue);
  }
  
  /*
  add input fields
  */
  function addInputField($strTyp, $intId, $strValue = NULL, $arrAttributes = array())
  {
    return $this->addElement($strTyp, $intId, $strValue, $arrAttributes);
  }
  
  function addHiddenField($intId, $strValue = NULL, $arrAttributes = array())
  {
    return $this->addInputField('hidden', $intId, $strValue, $arrAttributes);
  }
  
  function addReadOnlyField($intId, $strValue = NULL, $arrAttributes = array())
  {
    return $this->addInputField('readonly', $intId, $strValue, $arrAttributes);
  }
  
  function addTextField($intId, $strValue = NULL, $arrAttributes = array())
  {
    return $this->addInputField('text', $intId, $strValue, $arrAttributes);
  }
  
  function addTextAreaField($intId, $strValue = NULL, $arrAttributes = array())
  {
    return $this->addInputField('textarea', $intId, $strValue, $arrAttributes);
  }
  
  /*
  add select fields
  */
  function addOptionField($strTyp, $intId, $strValue = NULL, $arrAttributes = array(), $arrOptions = array())
  {
    return $this->addElement($strTyp, $intId, $strValue, $arrAttributes, $arrOptions);
  }
  function addSelectField($intId, $strValue = NULL, $arrOptions = array(), $arrAttributes = array())
  {
    return $this->addOptionField('select', $intId, $strValue, $arrAttributes, $arrOptions);
  }
  
  function addMultiSelectField($intId, $strValue = NULL, $arrOptions = array(), $arrAttributes = array())
  {
    $this->addOptionField('multiselect', $intId, $strValue, $arrAttributes, $arrOptions);
    $this->setMultiple(true);
  }
  
  function setMultiple($boolMultiple)
  {
    if ($boolMultiple === true)
    {
      $this->arrElements[$this->id]['multi'] = true;
    }
  }

  function setSize($size)
  {
    if ((is_numeric($size) === true &&  $size > 0) or $size == 'full')
    {
      $this->arrElements[$this->id]['size'] = $size;
    }
  }
  
  /*
  add checkboxes
  typ|valueId|label|defaultValue|parameter|
  */
  function addCheckboxField($intId, $strValue = NULL, $arrOptions = array(), $arrAttributes = array())
  {
    return $this->addOptionField('checkbox', $intId, $strValue, $arrAttributes, $arrOptions);
  }
  
  /*
  add radiobutton
  */
  function addRadioField($intId, $strValue = NULL, $arrOptions = array(), $arrAttributes = array())
  {
    return $this->addOptionField('radiobutton', $intId, $strValue, $arrAttributes, $arrOptions);
  }
  
  /*
  add options
  */
  function addOption($strValue,$intKey)
  {
    $this->options[$intKey] = $strValue;
    $this->arrElements[$this->id]['options'] = $this->options;
  }
  
  function addOptions($arrOptions)
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
  function addLinkField($intId, $strValue = NULL, $arrParameter = array(), $intCatId, $arrAttributes = array())
  {
    return $this->addElement('link', 'link-' . $intId, $strValue, $arrAttributes, array(), $arrParameter, $intCatId);
  }
  
  function addLinklistField($intId, $strValue = NULL, $arrParameter = array(), $intCatId, $arrAttributes = array())
  {
    return $this->addElement('linklist', 'linklist-' . $intId, $strValue, $arrAttributes, array(), $arrParameter, $intCatId);
  }
  
  /*
  add rex media fields
  */
  function addMediaField($intId, $strValue = NULL, $arrParameter = array(), $intCatId, $arrAttributes = array())
  {
    return $this->addElement('media', 'media-' . $intId, $strValue, $arrAttributes, array(), $arrParameter, $intCatId);
  }
  
  function addMedialistField($intId, $strValue = NULL, $arrParameter = array(), $intCatId, $arrAttributes = array())
  {
    return $this->addElement('medialist', 'medialist-' . $intId, $strValue, $arrAttributes, array(), $arrParameter, $intCatId);
  }
  
  /*
  add category id
  */
  function setCategory($intCatId)
  {
    if ($intCatId > 0)
    {
      $this->arrElements[$this->id]['cid'] = $intCatId;
    }
  }
  
  /*
  add parameter
  */
  function setParameter($intKey,$strValue)
  {
    $this->parameter[$intKey] = $strValue;
    $this->arrElements[$this->id]['parameter'] = $this->options;
  }
  
  function setParameters($arrParameter)
  {
    $this->parameter = array();
    foreach($arrParameter as $intKey => $strValue)
    {
      $this->setParameter($intKey, $strValue);
    }
  }
  
  
  /**/
  // set label and attributes
  /**/
  
  function setLabel($strLabel)
  {
    $this->arrElements[$this->id]['label'] = $strLabel;
  }
  
  function setAttribute($strName, $strValue)
  {
    if ($strName == 'label')
    {
      $this->setLabel($strValue);
    }
    else
    {
      $this->attributes[$strName] = $strValue;
      $this->arrElements[$this->id]['attributes'] = $this->attributes;
    }
  }
  
  function setAttributes($arrAttributes)
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
  function arrFormElements() {
    $this->strOutput = $this->arrElements;
    return $this->strOutput;
  }
  
  function getArray() {
    return $this->arrFormElements($this->arrElements);
  }

}
