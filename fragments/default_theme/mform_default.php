<?php

switch ($this->type) {
    case 'default':
        $this->labelColClass = 'col-sm-2';
        $this->formItemColClass = 'col-sm-10';
        break;
    case 'default_full':
        $this->labelColClass = 'col-sm-12';
        $this->formItemColClass = 'col-sm-12';
        break;
}

echo '<div class="form-group">
        <div class="' . $this->labelColClass . ' control-label">' . $this->label . $this->infoTooltip . $this->infoCollapseButton . '</div>
        <div class="' . $this->formItemColClass . '">' . $this->element . '</div>
      </div>' . $this->infoCollapse;
