<?php
/*
help.inc.php

@copyright Copyright (c) 2012 by Doerr Softwaredevelopment
@author mail[at]joachim-doerr[dot]com Joachim Doerr

@package redaxo4
@version 2.1.2
*/

$mypage = 'mform';
require_once $REX['INCLUDE_PATH'].'/addons/' . $mypage . '/pages/site.demo.inc.php';

?>
<h2><?php echo $I18N->msg('mform_headline'); ?></h2>
<p><?php echo $I18N->msg('mform_description'); ?></p>
<br/>
<h3><?php echo $I18N->msg('mform_headline_example'); ?></h3>
<?php rex_highlight_string($mdl_im); ?><br/>

<p>
<?php
  $file = dirname( __FILE__) .'/_changelog.txt';
  if(is_readable($file))
    echo str_replace( '+', '&nbsp;&nbsp;+', nl2br(file_get_contents($file)));
?>
</p>