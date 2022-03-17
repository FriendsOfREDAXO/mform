<?php 
// old plugin docs still exists ? -> delete
$pluginDocs = __DIR__.'/plugins/docs';
if (file_exists($pluginDocs)) {
    rex_dir::delete($pluginDocs);
}
$addon = rex_addon::get("mform");
$addon->setProperty('successmsg', '<br><strong>' . rex_i18n::msg("mform_change_message") . '</strong>');
