<?php
/**
 * @author Joachim Doerr
 * @package redaxo5
 * @license MIT
 */

namespace FriendsOfRedaxo\MForm\Utils;

use rex_article;
use rex_article_slice;
use rex_clang;
use rex_path;
use rex_url;

class MFormOutputHelper
{
    public static function isFirstSlice($sliceId): bool
    {
        $first = rex_article_slice::getFirstSliceForArticle(rex_article::getCurrentId(), rex_clang::getCurrentId());
        if ($first instanceof rex_article_slice) {
            return $first->getId() == $sliceId;
        }

        return false;
    }

    public static function prepareCustomLink(array $item, bool $externBlank = true): array
    {
        // Set URL
        if (!isset($item['link']) || empty($item['link'])) {
            return $item;
        }
        $item['customlink_text'] = (isset($item['text']) && !isset($item['customlink_text'])) ? $item['text'] : '';
        $item['customlink_url'] = $item['link'];
        $item['customlink_target'] = '';

        // Media file?
        if (file_exists(rex_path::media($item['link']))) {
            $item['customlink_url'] = rex_url::media($item['link']);
            $item['customlink_class'] = ' media';
        } else {
            // Check for rex:// URL
            if (str_starts_with($item['link'], 'rex://')) {
                $articleId = (int) substr($item['link'], 6);
                $item['customlink_url'] = rex_getUrl($articleId, rex_clang::getCurrentId());
                $item['customlink_class'] = ' intern';

                if (empty($item['customlink_text'])) {
                    $art = rex_article::get($articleId, rex_clang::getCurrentId());
                    if ($art) {
                        $item['customlink_text'] = $art->getName();
                    }
                }
            }
            // No media and no URL and is numeric, it must be a rex article id
            elseif (!filter_var($item['link'], FILTER_VALIDATE_URL) && is_numeric($item['link'])) {
                $item['customlink_url'] = rex_getUrl($item['link'], rex_clang::getCurrentId());
                $item['customlink_class'] = ' internal';

                if (empty($item['customlink_text'])) {
                    $art = rex_article::get($item['link'], rex_clang::getCurrentId());
                    if ($art) {
                        $item['customlink_text'] = $art->getName();
                    }
                }
            } else {
                $item['customlink_class'] = ' external';

                if (str_starts_with($item['customlink_url'], 'tel:')) {
                    $item['customlink_class'] = 'tel';
                } elseif (str_starts_with($item['customlink_url'], 'mailto:')) {
                    $item['customlink_class'] = 'mail';
                }

                if ($externBlank) {
                    $item['customlink_target'] = ' target="_blank" rel="noopener noreferrer"';
                }
            }
        }

        // No link text?
        if (empty($item['customlink_text'])) {
            $item['customlink_text'] = str_replace(['http://', 'https://'], '', $item['customlink_url']);
        }

        return $item;
    }


    public static function getCustomUrl(mixed $value = null, ?string $lang = null): string
    {
        // Check if the value is null or empty
        if (is_null($value) || $value === '') {
            return '';
        }

        // If the value is an array, use the 'id' key for processing
        if (is_array($value) && isset($value['id'])) {
            $value = $value['id'];
        }

        // Determine the language to use (current language if none provided)
        $lang = $lang ?? rex_clang::getCurrentId();

        // Check if the value is a REDAXO article (starts with rex://)
        if (is_string($value) && str_starts_with($value, 'rexaxo://')) {
            $articleId = (int) substr($value, 9); // Remove 'rex://' and convert the rest to an integer
            return rex_getUrl($articleId, $lang);
        }

        // Check if the value is numeric
        if (is_numeric($value)) {
            $articleId = (int) $value;
            return rex_getUrl($articleId, $lang);
        }

        // If the value is neither a REDAXO URL nor numeric, return the value
        return $value;
    }
}
