<?php
/**
 * @author mail[at]doerr-softwaredevelopment[dot]com Joachim Doerr
 * @package redaxo5
 * @license MIT
 */

namespace MForm\Provider;


use rex_addon;
use rex_path;

class MFormTemplateFileProvider
{
    const DEFAULT_THEME = 'default_theme';
    const THEME_PATH = 'mform/templates/%s/';
    const ELEMENTS_PATH = 'elements/';

    /**
     * load specific theme template file
     * is the theme template file not exist load the file form the default theme
     * @param $templateType
     * @param string $subPath
     * @param string $theme
     * @param bool $stop
     * @return string
     * @author Joachim Doerr
     */
    public static function loadTemplate($templateType, $subPath = '', $theme = NULL, $stop = false)
    {
        if (is_null($theme)) {
            $theme = rex_addon::get('mform')->getConfig('mform_theme');
        }

        // set theme path to load type template file
        $path = rex_path::addonData(sprintf(self::THEME_PATH . $subPath, $theme));
        $file = "mform_$templateType.ini"; // create file name

        // to print without template
        $templateString = '<element:label/><element:element/><element:output/>';

        // is template file exist? and template type not html
        if ($templateType != 'html' && file_exists($path . $file)) {
            // load theme file
            $templateString = implode(file($path . $file, FILE_USE_INCLUDE_PATH));
        } else {
            // stop recursion is default theme not founding
            if (!$stop) return self::loadTemplate($templateType, $subPath, self::DEFAULT_THEME, true);
        }

        // exchange template string
        return $templateString;
    }
}
