<?php

switch ($this->type) {
    case 'wrapper':
        echo '<div class="mform form-horizontal">'.$this->output.'</div>';
        break;
    case 'hidden':
        echo '<div class="hidden" style="display:none">'.$this->label.$this->element.'</div>';
        break;
}