<?php
/**
 * @author Joachim Doerr
 * @package redaxo5
 * @license MIT
 */

namespace FriendsOfRedaxo\MForm\Utils;

use rex;
use rex_view;

use function count;

class MFormModuleHelper
{
    /** @var array<int, string> */
    public static array $msg = [];

    /**
     * @param array<string, mixed> $defaultConfig
     * @param array<string, mixed> $config
     * @return array<string, mixed>
     */
    public static function mergeInputConfig(array $defaultConfig = [], array $config = [], int $maxMergeDepth = PHP_INT_MAX, int $currentDepth = 0): array
    {
        foreach ($config as $key => $value) {
            if (isset($defaultConfig[$key])) {
                if (is_array($value) && $currentDepth < $maxMergeDepth) {
                    // Merge rekursiv nur wenn wir unter der maximalen Rekursionstiefe sind
                    $config[$key] = self::mergeInputConfig($defaultConfig[$key], $value, $maxMergeDepth, $currentDepth + 1);
                }
                // Sonst überschreiben wir einfach den Wert
                $defaultConfig[$key] = $config[$key];
            } else {
                $defaultConfig[$key] = $value;
            }
        }
        return $defaultConfig;
    }

    /**
     * @param array<string, mixed> $defaultConfig
     * @param array<string, mixed> $config
     * @return array<string, mixed>
     */
    public static function mergeOutputConfig(array $defaultConfig = [], array $config = []): array
    {
        foreach ($config as $key => $value) {
            if (!array_key_exists($key, $defaultConfig))
                $defaultConfig[$key] = $value;
        }
        foreach ($defaultConfig as $key => $value) {
            if (is_array($value) && isset($config[$key]) && is_array($config[$key])) $config[$key] = self::mergeOutputConfig($value, $config[$key]);
            if (isset($config[$key]) && $config[$key] != 'mfragment_default') $defaultConfig[$key] = $config[$key];
        }
        return $defaultConfig;
    }

    /** @param array<int, string> $images */
    public static function addBackendInfoImgList(array $images, string $message = '', string $mediaType = 'rex_mediapool_detail'): void
    {
        if ('' === $message) {
            $message = "<p>$message</p>";
        }

        $imgs = [];

        if (!empty($images)) {
            foreach ($images as $image) {
                $imgs[] = "<img src=\"/index.php?rex_media_type={$mediaType}&rex_media_file={$image}\" style=\"margin-bottom: 10px; margin-right: 10px;\">";
            }
        }

        if (!empty($message) && !empty($imgs)) {
            $message .= '<hr>';
        }

        self::$msg[] = $message . implode('', $imgs);
    }

    public static function addBackendInfoImgMsg(string $image, string $message = '', string $mediaType = 'rex_mediapool_detail'): void
    {
        if (empty($message)) {
            self::$msg[] = "<p><img src=\"/index.php?rex_media_type={$mediaType}&rex_media_file={$image}\"></p>";
        } else {
            self::$msg[] = '<p>' . sprintf($message, $image) . "<hr><img src=\"/index.php?rex_media_type={$mediaType}&rex_media_file={$image}\"></p>";
        }
    }

    public static function addBackendInfoMsg(string $message): void
    {
        self::$msg[] = "<p>$message</p>";
    }

    public static function exchangeBackendInfo(string $headline = 'Settings', string $viewType = 'content'): string
    {
        if (count(self::$msg) > 0 && rex::isBackend()) {
            $output = '<div class="mform-module-settings">' . rex_view::$viewType(implode('', self::$msg), $headline) . '</div>';
            self::$msg = [];
            return $output;
        }
        return '';
    }
}
