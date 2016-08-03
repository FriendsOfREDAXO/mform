<?php
/**
 * Author: Joachim Doerr
 * Date: 13.07.16
 * Time: 18:32
 */

// copy data directory
rex_dir::copy($this->getPath('data'), $this->getDataPath());
