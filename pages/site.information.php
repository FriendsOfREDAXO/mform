<?php
/**
 * site.information.php
 * @copyright Copyright (c) 2015 by Doerr Softwaredevelopment
 * @author mail[at]joachim-doerr[dot]com Joachim Doerr
 *
 * @package redaxo4.5
 * @version 3.0.0
 */

echo '
<div class="rex-addon-output">
  <h2 class="rex-hl2">' . $I18N->msg($name . '_help_headline') . '</h2>
  <div class="rex-addon-content">
    <div class= "addon-template">
      <h3>' . $I18N->msg($name . '_help_subheadline_1') . '</h3>
      <p>' . $I18N->msg($name . '_help_infotext_1') . '</p>
      <p>' . mfrom_textileparser($I18N->msg($name . '_help_infotext_2')) . '</p>
      <h3>' . $I18N->msg($name . '_help_subheadline_2') . '</h3>
      <p>' . $I18N->msg($name . '_help_infotext_3') . '</p>
      <p>' . $I18N->msg($name . '_help_infotext_4') . '</p>
    </div>
  </div>
</div>';
?>
<div class="rex-addon-output">
    <h2 class="rex-hl2"><?php echo $I18N->msg($name . '_demo_modul'); ?></h2>

    <div class="rex-addon-content">
        <div><?php echo rex_highlight_string($inputDemo); ?></div>
    </div>
</div>
