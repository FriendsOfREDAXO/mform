<?php
/**
 * uninstall.inc.php
 * @copyright Copyright (c) 2015 by Doerr Softwaredevelopment
 * @author mail[at]joachim-doerr[dot]com Joachim Doerr
 *
 * @package redaxo4.5
 * @version 3.0.0
 */

// addon identifier
$name = rex_request('addonname', 'string');

$REX['ADDON']['install'][$name] = 0;
