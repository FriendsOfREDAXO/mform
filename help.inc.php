<?php
/*
help.inc.php

@copyright Copyright (c) 2012 by Doerr Softwaredevelopment
@author mail[at]joachim-doerr[dot]com Joachim Doerr

@package redaxo5
@version 3.0
*/

// include information site
include $this->getBasePath('pages/site.information.inc.php');

// echo help
echo rex_view::contentBlock($strContent);
