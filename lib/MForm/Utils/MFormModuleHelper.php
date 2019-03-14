<?php
/**
 * @author mail[at]doerr-softwaredevelopment[dot]com Joachim Doerr
 * @package redaxo5
 * @license MIT
 */

namespace MForm\Utils;


use rex;
use rex_view;

class MFormModuleHelper
{
    /**
     * @var array
     */
    public static $msg = array();

    /**
     * @param $images
     * @param string $message
     * @param string $mediaType
     * @author Joachim Doerr
     */
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
     * @author Joachim Doerr
     * @return string
     */
    public static function exchangeBackendInfo($headline = 'Settings', $viewType = 'content')
    {
        if (sizeof(self::$msg) > 0 && rex::isBackend()) {
            $output = '<div class="mform-module-settings">' . rex_view::$viewType(implode('', self::$msg), $headline) . '</div>';
            self::$msg = array();
            return $output;
        }
        return '';
    }
}