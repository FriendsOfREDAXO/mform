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
include rex_path::addon('mform', 'pages/site.config.php');
include rex_path::addon('mform', 'pages/site.information.php');
include rex_path::addon('mform', 'pages/site.demo.php');

// echo content
echo rex_view::title($this->i18n('title'));
echo rex_view::contentBlock($strPageContent . rex_string::highlight($strModulInputDemo));
