<?php
/**
 * @author mail[at]doerr-softwaredevelopment[dot]com Joachim Doerr
 * @package redaxo5
 * @license MIT
 */

echo rex_view::title(rex_i18n::msg('mform_title') . ' ' . rex_i18n::msg('mform_'.rex_be_controller::getCurrentPagePart(2)));

include rex_be_controller::getCurrentPageObject()->getSubPath();
