<?php
/*
index.inc.php

@copyright Copyright (c) 2013 by Doerr Softwaredevelopment
@author mail[at]joachim-doerr[dot]com Joachim Doerr

@package redaxo4
@version 2.2.0
*/

// ADDON IDENTIFIER
////////////////////////////////////////////////////////////////////////////////
$strAddonName = 'mform';
$strAddonPath = $REX['INCLUDE_PATH'].'/addons/'.$strAddonName.'/';


// GET PARAMS
////////////////////////////////////////////////////////////////////////////////
$strPage      = rex_request('page', 'string', $strAddonName);
$strFunc      = rex_request('func', 'string');
$id           = rex_request('id', 'int');


// REX BACKEND LAYOUT TOP
//////////////////////////////////////////////////////////////////////////////
include_once( $REX['INCLUDE_PATH'].'/layout/top.php' );


// TITLE & SUBPAGE NAVIGATION
//////////////////////////////////////////////////////////////////////////////
rex_title($I18N->msg($strAddonName.'_title'), $REX['ADDON']['pages'][$strAddonName]);


// INCLUDE SUBPAGE
/////////////////////////////////////////////////////////////////////////////
require_once( $strAddonPath . '/pages/site.demo.php' );
require_once( $strAddonPath . '/pages/site.form.php' );
require_once( $strAddonPath . '/pages/site.information.php' );


// REX BACKEND LAYOUT BOTTOM
//////////////////////////////////////////////////////////////////////////////
include_once( $REX['INCLUDE_PATH'].'/layout/bottom.php' );

