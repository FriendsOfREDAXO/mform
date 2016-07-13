<?php
/**
 * Author: Joachim Doerr
 * Date: 13.07.16
 * Time: 18:32
 */

rex_extension::register('OUTPUT_FILTER', function () {
    rex_dir::copy($this->getPath('data'), $this->getDataPath());
});
