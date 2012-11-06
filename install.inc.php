<?php
/*
install.inc.php

@copyright Copyright (c) 2012 by Doerr Softwaredevelopment
@author mail[at]joachim-doerr[dot]com Joachim Doerr

@package redaxo5
@version 3.0
*/

// set default template
if (!$this->hasConfig())
{
  $this->setConfig('mform_template', 'default');
}
