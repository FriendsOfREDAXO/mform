<?php
/*
mform install.inc.php

@author mail[at]joachim-doerr[dot]com Joachim Doerr
@author <a href="http://joachim-doerr.com">joachim-doerr.com</a>

@package redaxo4
@version 1.2
*/

if($error != '')
  $REX['ADDON']['installmsg']['mform'] = $error;
else
  $REX['ADDON']['install']['mform'] = true;