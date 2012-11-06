<?php
/*
config.inc.php

@copyright Copyright (c) 2012 by Doerr Softwaredevelopment
@author mail[at]joachim-doerr[dot]com Joachim Doerr

@package redaxo5
@version 3.0
*/

// rex request
$strMode = rex_request('mode', 'string', 'none');

// set css by edit
if ($strMode == 'edit')
{
  include $this->getBasePath('extensions/extension.a967_outputfilter.inc.php');
  rex_extension::register('OUTPUT_FILTER', 'a967_backend_css');
}
