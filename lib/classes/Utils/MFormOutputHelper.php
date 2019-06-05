<?php
/**
 * @author mail[at]doerr-softwaredevelopment[dot]com Joachim Doerr
 * @package redaxo5
 * @license MIT
 */

class MFormOutputHelper
{
    /**
     * @param $sliceId
     * @return bool
     * @author Joachim Doerr
     */
    public static function isFirstSlice($sliceId)
    {
        $first = rex_article_slice::getFirstSliceForArticle(rex_article::getCurrentId(), rex_clang::getCurrentId());
        if ($first instanceof rex_article_slice)
            return ($first->getId() == $sliceId);
        else
            return false;
    }

    /**
     * @param array $item
     * @param bool $externBlank
     * @return array
     * @author Joachim Doerr
     */
    public static function prepareCustomLink(array $item, $externBlank = true)
    {
        // set url
        if (!isset($item[1]) or empty($item[1])) return $item;
        $item['customlink_url'] = $item[1];
        $item['customlink_target'] = '';

        // media file?
        if (file_exists(rex_path::media($item[1])) === true) {
            $item['customlink_url'] = rex_url::media($item[1]);
            $item['customlink_class'] = ' media';
        } else {
            // no media and no url and is numeric it must be an rex article id
            if (filter_var($item[1], FILTER_VALIDATE_URL) === FALSE && is_numeric($item[1])) {
                $item['customlink_url'] = rex_getUrl($item[1], rex_clang::getCurrentId());
                $item['customlink_class'] = ' intern';

                if (empty($item['customlink_text'])) {
                    $art = rex_article::get($item[1], rex_clang::getCurrentId());
                    if($art)
                    {    
                    $item['customlink_text'] = $art->getName();
                    }
                }
            } else {
                $item['customlink_class'] = ' extern';
                if ($externBlank) {
                    $item['customlink_target'] = ' target="_blank"';
                }
            }
        }

        // no link text?
        if (empty($item['customlink_text'])) {
            $item['customlink_text'] = str_replace(array('http://', 'https://'), '', $item['customlink_url']);
        }

        return $item;
    }

}
