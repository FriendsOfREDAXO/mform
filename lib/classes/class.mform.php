<?php
/*
class.mform.php

@copyright Copyright (c) 2013 by Doerr Softwaredevelopment
@author mail[at]joachim-doerr[dot]com Joachim Doerr

@package redaxo4.5
@version 2.2.1
*/

// MFROM BASE CLASS
////////////////////////////////////////////////////////////////////////////////
class mform extends getMFormArray
{
  /**/
  // define defaults
  /**/
  
  public $strTemplateThemeName;
  public $boolCheckMode = false;
  
  /**/
  // set template theme and checkmode
  /**/
  
  /*
  set template theme
  */
  public function setTheme($strNewTemplateThemeName)
  {
  	$this->strTemplateThemeName = $strNewTemplateThemeName;
  }
  
  /*
  set checkmode
  */
  public function setCheckmode($boolSetCheckMode)
  {
    if ($boolSetCheckMode === true)
    {
      $this->boolCheckMode = true;
    }
  }
  
  /**/
  // generate element array - add fields
  /**/
  
  public function show_mform()
  {
    /*
    init parse class
    */
    $objOutput = new parseMForm();
    
    /*
    is checkmode true show output array
    */
    if ($this->boolCheckMode === true)
    {
      echo PHP_EOL.'<pre>'.PHP_EOL;
      print_r($this->getArray());
      echo PHP_EOL.'</pre>'.PHP_EOL;
    }
    
    /*
    parce output through array
    */
    return $objOutput->parse_mform($this->getArray(), $this->strTemplateThemeName);
  }
  
  /**/
  // show - call show_mform methode
  /**/

  public function show()
  {
      return $this->show_mform();
  }
}
