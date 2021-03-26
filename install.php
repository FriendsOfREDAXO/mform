<?php
/**
 * @author mail[at]doerr-softwaredevelopment[dot]com Joachim Doerr
 * @package redaxo5
 * @license MIT
 */

// copy data directory
rex_dir::copy($this->getPath('data'), $this->getDataPath());
