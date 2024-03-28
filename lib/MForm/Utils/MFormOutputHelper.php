<?php
/**
 * @author Joachim Doerr
 * @package redaxo5
 * @license MIT
 */

namespace MForm\Utils;

use rex_article;
use rex_article_slice;
use rex_clang;
use rex_path;
use rex_url;

class MFormOutputHelper
{
    /**
     * @return bool
     * @author Joachim Doerr
     */
    public static function isFirstSlice($sliceId)
    {
        $first = rex_article_slice::getFirstSliceForArticle(rex_article::getCurrentId(), rex_clang::getCurrentId());
        if ($first instanceof rex_article_slice) {
            return $first->getId() == $sliceId;
        }

        return false;
    }

    /**
     * @param bool $externBlank
     * @return array
     * @author Joachim Doerr
     */
    public static function prepareCustomLink(array $item, $externBlank = true)
    {
        // set url
        if (!isset($item['link']) || empty($item['link'])) {
            return $item;
        }
        $item['customlink_text'] = (isset($item['text']) && !isset($item['customlink_text'])) ? $item['text'] : '';
        $item['customlink_url'] = $item['link'];
        $item['customlink_target'] = '';

        // media file?
        if (true === file_exists(rex_path::media($item['link']))) {
            $item['customlink_url'] = rex_url::media($item['link']);
            $item['customlink_class'] = ' media';
        } else {
            // no media and no url and is numeric it must be an rex article id
            if (false === filter_var($item['link'], FILTER_VALIDATE_URL) && is_numeric($item['link'])) {
                $item['customlink_url'] = rex_getUrl($item['link'], rex_clang::getCurrentId());
                $item['customlink_class'] = ' intern';

                if (empty($item['customlink_text'])) {
                    $art = rex_article::get($item['link'], rex_clang::getCurrentId());
                    if ($art) {
                        $item['customlink_text'] = $art->getName();
                    }
                }
            } else {
                $item['customlink_class'] = ' extern';

                if (strpos($item['customlink_url'], 'tel:') === 0) {
                    $item['customlink_class'] = 'tel';
                } elseif (strpos($item['customlink_url'], 'mailto:') === 0) {
                    $item['customlink_class'] = 'mail';
                }
                
                if ($externBlank) {
                    $item['customlink_target'] = ' target="_blank"';
                }
            }
        }

        // no link text?
        if (empty($item['customlink_text'])) {
            $item['customlink_text'] = str_replace(['http://', 'https://'], '', $item['customlink_url']);
        }

        return $item;
    }
}
