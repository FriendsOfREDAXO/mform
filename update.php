<?php 
// old plugin docs still exists ? -> delete
$pluginDocs = __DIR__.'/plugins/docs';
if (file_exists($pluginDocs)) {
    rex_dir::delete($pluginDocs);
}
