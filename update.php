<?php 
$addon = rex_addon::get('mform');
$pluginDocs = $addon->getPath().'plugins/docs';
if (file_exists($pluginDocs)) {
    rex_dir::delete($pluginDocs);
}
$addon->setProperty('successmsg', '<br><strong>' . rex_i18n::msg("mform_change_message") . '</strong>');
