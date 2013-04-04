<?php
/*
uninstall.inc.php

@copyright Copyright (c) 2013 by Doerr Softwaredevelopment
@author mail[at]joachim-doerr[dot]com Joachim Doerr

@package redaxo4.5
@version 2.2.1
*/

// ADDON IDENTIFIER AUS GET PARAMS
////////////////////////////////////////////////////////////////////////////////
$strAddonName = rex_request('addonname','string');


$REX['ADDON']['install'][$strAddonName] = 0;
