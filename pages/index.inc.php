<?php
/*
index.inc.php

@copyright Copyright (c) 2012 by Doerr Softwaredevelopment
@author mail[at]joachim-doerr[dot]com Joachim Doerr

@package redaxo5
@version 3.0
*/

// site content
$content = '';

// include subpages
include $this->getBasePath('pages/site.config.inc.php');
include $this->getBasePath('pages/site.information.inc.php');
include $this->getBasePath('pages/site.demo.inc.php');

// echo content
echo rex_view::title($this->i18n('title'));
echo rex_view::contentBlock($strContent . rex_string::highlight($strModulInputDemo));
