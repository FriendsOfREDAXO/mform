<?php
/*
mform function.mform.inc.php

@author mail[at]joachim-doerr[dot]com Joachim Doerr
@author <a href="http://joachim-doerr.com">joachim-doerr.com</a>

@package redaxo4
@version 1.2
*/

if (!function_exists('mform')) {
  function mform($params) {
    global $REX;
    $strOutput = $params['subject'];
    $strPath = '../files/addons/mform';
    
    $strScript = <<<EOT
    <link rel="stylesheet" type="text/css" href="$strPath/css4backend.css" />
EOT;

    $strOutput = str_replace('</head>',$strScript.'</head>',$strOutput);
    return $strOutput;
  }
}
