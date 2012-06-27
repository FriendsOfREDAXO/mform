<?php
/*
uninstall.inc.php

@author mail[at]joachim-doerr[dot]com Joachim Doerr
@author <a href="http://joachim-doerr.com">joachim-doerr.com</a>

@package redaxo4
@version 2.1.2
*/

// ADDON IDENTIFIER AUS GET PARAMS
////////////////////////////////////////////////////////////////////////////////
$mypage = rex_request('addonname','string');


$REX['ADDON']['install'][$mypage] = 0;
