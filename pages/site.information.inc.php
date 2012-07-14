<?php
/*
site.information.inc.php

@copyright Copyright (c) 2012 by Doerr Softwaredevelopment
@author mail[at]joachim-doerr[dot]com Joachim Doerr

@package redaxo4
@version 2.0.1
*/

// INFORMATION CONTENT
////////////////////////////////////////////////////////////////////////////////
echo '
<div class="rex-addon-output">
  <h2 class="rex-hl2">'. $I18N->msg($strAddonName.'_help_headline') .'</h2>
  <div class="rex-addon-content">
    <div class= "addon-template">
      <h3>'. $I18N->msg($strAddonName.'_help_subheadline_1') .'</h3>
      <p>'. $I18N->msg($strAddonName.'_help_infotext_1') .'</p>
      <p>'. a967_textileparser($I18N->msg($strAddonName.'_help_infotext_2')) .'</p>
      <h3>'. $I18N->msg($strAddonName.'_help_subheadline_2') .'</h3>
      <p>'. $I18N->msg($strAddonName.'_help_infotext_3') .'</p>
      <p>'. $I18N->msg($strAddonName.'_help_infotext_4') .'</p>
    </div>
  </div>
</div>';

?>
<div class="rex-addon-output">
  <h2 class="rex-hl2"><?php echo $I18N->msg($strAddonName.'_demo_modul'); ?></h2>

  <div class="rex-addon-content">
    <div><?php echo rex_highlight_string($strModulInputDemo); ?></div>
  </div>
</div>
