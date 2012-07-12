<?php
/*
index.inc.php

@copyright Copyright (c) 2012 by Doerr Softwaredevelopment
@author mail[at]joachim-doerr[dot]com Joachim Doerr

@package redaxo4
@version 1.2
*/

$mypage = 'mform';
require_once $REX['INCLUDE_PATH'].'/addons/' . $mypage . '/pages/site.demo.inc.php';

require $REX['INCLUDE_PATH'].'/layout/top.php';

rex_title('MForm');
?>
<div class="rex-addon-output">
  <h2 class="rex-hl2"><?php echo $I18N->msg('mform_headline'); ?></h2>

  <div class="rex-addon-content">
    <p class="rex-tx1"><?php echo $I18N->msg('mform_description'); ?></p>
  </div>
</div>

<div class="rex-addon-output">
  <h2 class="rex-hl2"><?php echo $I18N->msg('mform_headline_example'); ?></h2>

  <div class="rex-addon-content">
    <?php rex_highlight_string($mdl_im); ?>
    <p class="rex-tx1"><?php echo $I18N->msg('mform_example_description'); ?></p>
  </div>
</div>

<div class="rex-addon-output">
  <h2 class="rex-hl2"><?php echo $I18N->msg('mform_schema'); ?></h2>

  <div class="rex-addon-content">
    <?php rex_highlight_string(str_replace('&#36;','$',$mformschema)); ?>
  </div>
</div>

<div class="rex-addon-output">
  <h2 class="rex-hl2"><?php echo $I18N->msg('mform_phpcodemarkitup'); ?></h2>

  <div class="rex-addon-content">
    <?php rex_highlight_string(str_replace(array('&#92;',"'**"),array("\'**",''),$phpmarkitup)); ?>
  </div>
</div>

<?php
require $REX['INCLUDE_PATH'].'/layout/bottom.php';