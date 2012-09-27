<?php
/*
uninstall.inc.php

@copyright Copyright (c) 2012 by Doerr Softwaredevelopment
@author mail[at]joachim-doerr[dot]com Joachim Doerr

@package redaxo4
@version 2.1.4
*/

// ADDON IDENTIFIER AUS GET PARAMS
////////////////////////////////////////////////////////////////////////////////
$strAddonName = rex_request('addonname','string');


$REX['ADDON']['install'][$strAddonName] = 0;
