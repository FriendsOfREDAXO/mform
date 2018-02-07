<?php
/**
 * @author mail[at]doerr-softwaredevelopment[dot]com Joachim Doerr
 * @package redaxo5
 * @license MIT
 */

class MFormModuleHelper
{
    public static $msg = array();

    /**
     * @return string
     * @author Joachim Doerr
     */
    public static function createDefaultRedactor2Profiles()
    {
        $msg = '';
        $msg .= self::createRedactor2Profile('default_1', 'Accordion-Modul Text-config', 150, 250, 'relative',

            'paragraph,alignment,blockquote,bold,italic,underline,deleted,cleaner,fontsize[100%|120%|140%],grouplink[email|external|internal|media],orderedlist,unorderedlist'

        );
        $msg .= self::createRedactor2Profile('default_2', 'Accordion-Modul Text-config', 150, 250, 'relative',

            'groupheading[2|3|4],paragraph,alignment,blockquote,bold,cleaner,fullscreen,grouplink[email|external|internal|media],media,grouplist[unorderedlist|orderedlist|indent|outdent],horizontalrule,source'

        );
        $msg .= self::createRedactor2Profile('default_limit_1', 'Lead-Text Text-config', 100, 200, 'relative',

            'paragraph,alignment,bold,italic,underline,deleted,cleaner,grouplink[email|external|internal|media]'

        );
        $msg .= self::createRedactor2Profile('default_limit_2', 'Default-Limit-2 Text-config', 150, 250, 'relative',

            'paragraph,alignment,bold,italic,underline,deleted,cleaner,fontsize[100%|120%|140%],grouplink[email|external|internal|media]'

        );
        return $msg;
    }

    /**
     * @param $name
     * @param string $description
     * @param string $minheight
     * @param string $maxheight
     * @param string $urltype
     * @param $redactorPlugins
     * @return string
     * @author Joachim Doerr
     */
    public static function createRedactor2Profile($name, $description = '', $minheight = '300', $maxheight = '800', $urltype = 'relative', $redactorPlugins)
    {
        if (rex_addon::exists('redactor2') && array_key_exists('redactor2', rex_addon::getAvailableAddons())) {
            if (!redactor2::profileExists($name)) {
                redactor2::insertProfile($name, $description, $minheight, $maxheight, $urltype, $redactorPlugins);
                return rex_view::info(sprintf(rex_i18n::msg('mform_redactor_profile_create_success'), $name));
            }
        }
        return '';
    }

    public static function addBackendInfoImgList($images, $message = '', $mediaType = 'rex_mediapool_detail')
    {
        if (empty($message))
            $message = "<p>$message</p>";

        $imgs = array();

        if (!empty($images))
            foreach ($images as $image)
                $imgs[] = "<img src=\"/index.php?rex_media_type={$mediaType}&rex_media_file={$image}\" style=\"margin-bottom: 10px; margin-right: 10px;\">";

        if (!empty($message) && !empty($imgs))
            $message = $message . '<hr>';

        self::$msg[] = $message . implode('', $imgs);
    }

    /**
     * @param $image
     * @param string $message
     * @param string $mediaType
     * @author Joachim Doerr
     */
    public static function addBackendInfoImgMsg($image, $message = '', $mediaType = 'rex_mediapool_detail')
    {
        if (empty($message))
            self::$msg[] = "<p><img src=\"/index.php?rex_media_type={$mediaType}&rex_media_file={$image}\"></p>";
        else
            self::$msg[] = '<p>' . sprintf($message, $image) . "<hr><img src=\"/index.php?rex_media_type={$mediaType}&rex_media_file={$image}\"></p>";
    }

    /**
     * @param $message
     * @author Joachim Doerr
     */
    public static function addBackendInfoMsg($message)
    {
        self::$msg[] = "<p>$message</p>";
    }

    /**
     * @param $headline
     * @param string $viewType
     * @return mixed
     * @author Joachim Doerr
     */
    public static function exchangeBackendInfo($headline = 'Settings', $viewType = 'content')
    {
        if (sizeof(self::$msg) > 0) {
            $output = '<div class="mform-module-settings">' . rex_view::$viewType(implode('', self::$msg), $headline) . '</div>';
            self::$msg = array();
            return $output;
        }
        return '';
    }
}