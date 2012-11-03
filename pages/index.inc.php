<?php
/*
index.inc.php

@copyright Copyright (c) 2012 by Doerr Softwaredevelopment
@author mail[at]joachim-doerr[dot]com Joachim Doerr

@package redaxo4
@version 2.1.4
*/

$content = '';

include $this->getBasePath('pages/site.config.inc.php');
include $this->getBasePath('pages/site.information.inc.php');
include $this->getBasePath('pages/site.demo.inc.php');

echo rex_view::title($this->i18n('title'));
echo rex_view::contentBlock($strContent . rex_string::highlight($strModulInputDemo));
