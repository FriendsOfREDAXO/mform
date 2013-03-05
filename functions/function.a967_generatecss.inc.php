<?php

if (function_exists('mform_generate_css') !== true)
{
  function mform_generate_css ($strTemplateTheme)
  {
    global $REX;
    
    while (ob_get_level())
    {
      ob_end_clean(); 
    }
    
    header("Content-type: text/css");
    
    if ($REX['USER']->isAdmin() === true)
    {
      echo <<<EOT
        
        .admin-only,
        .admin-only *:not(:link):not(:visited) {
          display:block !important;
          color:red !important;
        }
        
EOT;
    }
    else
    {
      echo <<<EOT
        
        .admin-only {
          display:none !important;
        }
        
EOT;
    }
    
    if (file_exists( $REX['HTDOCS_PATH'] . 'redaxo/include/addons/mform/templates/' . $strTemplateTheme . '_theme/theme.css') === true)
    {
      echo file_get_contents('include/addons/mform/templates/' . $strTemplateTheme . '_theme/theme.css', FILE_USE_INCLUDE_PATH);
    }
    else
    {
      echo file_get_contents('include/addons/mform/templates/' . $REX["ADDON"]["mform"]["settings"]["default_template_theme_name"] . '_theme/theme.css', FILE_USE_INCLUDE_PATH);
    }
    
    die;
  }
}
