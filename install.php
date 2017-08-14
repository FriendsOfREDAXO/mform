<?php
/**
 * @author mail[at]doerr-softwaredevelopment[dot]com Joachim Doerr
 * @package redaxo5
 * @license MIT
 */

// set default template
if (!$this->hasConfig()) {
    $this->setConfig('mform_theme', 'default_theme');
}

// copy data directory
rex_dir::copy($this->getPath('data'), $this->getDataPath());