<?php
/** @var rex_fragment $this */

echo '<textarea id="' . $this->getVar('id') . '" name="REX_INPUT_VALUE' . $this->getVar('varId') . '" class="' . $this->getVar('class') . '" ' . $this->getVar('attributes') . '>' . $this->getVar('value') . '</textarea>';