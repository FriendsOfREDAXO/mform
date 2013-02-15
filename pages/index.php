<?php
/*
index.php

@copyright Copyright (C) 2013 by Doerr Softwaredevelopment
@author mail[at]joachim-doerr[dot]com Joachim Doerr

@package redaxo5
@version 3.1
*/

// site content
$content = '';

// include subpages
include $this->getPath('pages/site.config.php');
include $this->getPath('pages/site.information.php');
include $this->getPath('pages/site.demo.php');

// echo content
echo rex_view::title($this->i18n('title'));
echo rex_view::contentBlock($strPageContent . rex_string::highlight($strModulInputDemo));
