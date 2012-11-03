<?php
/*
site.information.inc.php

@copyright Copyright (c) 2012 by Doerr Softwaredevelopment
@author mail[at]joachim-doerr[dot]com Joachim Doerr

@package redaxo4
@version 2.1.4
*/

// INFORMATION CONTENT
////////////////////////////////////////////////////////////////////////////////
$strContent .= '
  <h2 class="rex-hl2">'. $this->i18n('help_headline') .'</h2>
  <div class="rex-addon-content">
    <div class= "addon-template">
      <h3>'. $this->i18n('help_subheadline_1') .'</h3>
      <p>'. rex_textile::parse($this->i18n('help_infotext_1')) .'</p>
      <p>'. rex_textile::parse($this->i18n('help_infotext_2')) .'</p>
      <h3>'. $this->i18n('help_subheadline_2') .'</h3>
      <p>'. $this->i18n('help_infotext_3') .'</p>
      <p>'. $this->i18n('help_infotext_4') .'</p>
    </div>
  </div>
';