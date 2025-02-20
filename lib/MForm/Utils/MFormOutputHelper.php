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
use rex_media;
use rex_media_manager;

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

   /**
    * Prepares and enriches link information with additional metadata
    * while maintaining backward compatibility
    *
    * @param array $item The input link item
    * @param bool $externBlank Whether external links should open in new tab
    * @return array Enhanced link information
    */
   public static function prepareCustomLink(array $item, bool $externBlank = true): array
   {
       // Initialize with backward compatible default values
       $item = array_merge([
           'link' => '',
           'text' => '',
           'customlink_text' => '',
           'customlink_url' => '',
           'customlink_target' => '',
           'customlink_class' => '',
           // New properties (won't affect existing implementations)
           'type' => 'undefined',
           'article_id' => null,
           'clang_id' => null,
           'filename' => null,
           'extension' => null,
           'protocol' => null,
           'domain' => null,
           'metadata' => []
       ], $item);

       // Return early if no link provided
       if (empty($item['link'])) {
           return $item;
       }

       // Set initial URL and text (backward compatible)
       $item['customlink_text'] = (isset($item['text']) && !isset($item['customlink_text'])) ? $item['text'] : '';
       $item['customlink_url'] = $item['link'];

       // Handle media files
       if (file_exists(rex_path::media($item['link']))) {
           $item['type'] = 'media';
           $item['customlink_url'] = rex_url::media($item['link']);
           $item['customlink_class'] = ' media';
           $item['filename'] = $item['link'];
           $item['extension'] = pathinfo($item['link'], PATHINFO_EXTENSION);
           
           // Add media manager type if exists
           if (class_exists('rex_media_manager') && ($media = rex_media::get($item['link']))) {
               $item['metadata'] = [
                   'title' => $media->getTitle(),
                   'filesize' => $media->getSize(),
                   'width' => $media->getWidth(),
                   'height' => $media->getHeight(),
                   'mimetype' => $media->getType()
               ];
           }
       } 
       // Handle internal REDAXO links (rex:// or redaxo://)
       elseif (str_starts_with($item['link'], 'rex://') || str_starts_with($item['link'], 'redaxo://')) {
           $item['type'] = 'internal';
           $prefix = str_starts_with($item['link'], 'rex://') ? 'rex://' : 'redaxo://';
           $articleId = (int) substr($item['link'], strlen($prefix));
           $clangId = rex_clang::getCurrentId();
           
           $item['article_id'] = $articleId;
           $item['clang_id'] = $clangId;
           $item['customlink_url'] = rex_getUrl($articleId, $clangId);
           $item['customlink_class'] = ' intern';

           // Get article metadata
           if ($art = rex_article::get($articleId, $clangId)) {
               $item['metadata'] = [
                   'article_name' => $art->getName(),
                   'template_id' => $art->getTemplateId(),
                   'priority' => $art->getPriority(),
                   'parent_id' => $art->getParentId(),
                   'category_id' => $art->getCategoryId(),
                   'createdate' => $art->getCreateDate(),
                   'updatedate' => $art->getUpdateDate()
               ];

               if (empty($item['customlink_text'])) {
                   $item['customlink_text'] = $art->getName();
               }
           }
       }
       // Handle numeric article IDs
       elseif (!filter_var($item['link'], FILTER_VALIDATE_URL) && is_numeric($item['link'])) {
           $item['type'] = 'internal';
           $articleId = (int) $item['link'];
           $clangId = rex_clang::getCurrentId();
           
           $item['article_id'] = $articleId;
           $item['clang_id'] = $clangId;
           $item['customlink_url'] = rex_getUrl($articleId, $clangId);
           $item['customlink_class'] = ' intern';

           // Get article metadata
           if ($art = rex_article::get($articleId, $clangId)) {
               $item['metadata'] = [
                   'article_name' => $art->getName(),
                   'template_id' => $art->getTemplateId(),
                   'priority' => $art->getPriority(),
                   'parent_id' => $art->getParentId(),
                   'category_id' => $art->getCategoryId(),
                   'createdate' => $art->getCreateDate(),
                   'updatedate' => $art->getUpdateDate()
               ];

               if (empty($item['customlink_text'])) {
                   $item['customlink_text'] = $art->getName();
               }
           }
       }
       // Handle external links
       else {
           $item['type'] = 'external';
           $item['customlink_class'] = ' external';

           // Parse URL components
           $urlComponents = parse_url($item['customlink_url']);
           if ($urlComponents) {
               $item['protocol'] = $urlComponents['scheme'] ?? null;
               $item['domain'] = $urlComponents['host'] ?? null;
               
               // Special handling for tel: and mailto: links
               if (isset($urlComponents['scheme'])) {
                   if ($urlComponents['scheme'] === 'tel') {
                       $item['type'] = 'telephone';
                       $item['customlink_class'] = ' tel';
                       $item['metadata']['phone_number'] = str_replace('tel:', '', $item['customlink_url']);
                   } elseif ($urlComponents['scheme'] === 'mailto') {
                       $item['type'] = 'email';
                       $item['customlink_class'] = ' mail';
                       $item['metadata']['email'] = str_replace('mailto:', '', $item['customlink_url']);
                   }
               }
           }

           if ($externBlank && $item['type'] === 'external') {
               $item['customlink_target'] = ' target="_blank" rel="noopener noreferrer"';
           }
       }

       // Set default link text if none provided (backward compatible)
       if (empty($item['customlink_text'])) {
           $item['customlink_text'] = str_replace(['http://', 'https://'], '', $item['customlink_url']);
       }

       // Clean up class name
       $item['customlink_class'] = trim($item['customlink_class']);

       return $item;
   }

   /**
    * Returns only the URL for a given link value
    * Accepts either a CustomLink array or any link string
    * 
    * @param array|string $item The CustomLink array or link string
    * @param bool $externBlank Whether external links should open in new tab
    * @return string The processed URL
    */
   public static function getCustomLinkUrl(array|string $item, bool $externBlank = true): string 
   {
       // If we get an array, check for different possible keys
       if (is_array($item)) {
           // CustomLink array from prepareCustomLink
           if (isset($item['customlink_url'])) {
               return $item['customlink_url'];
           }
           // Regular link value
           if (isset($item['link'])) {
               $prepared = self::prepareCustomLink($item, $externBlank);
               return $prepared['customlink_url'];
           }
           // Article array with id
           if (isset($item['id'])) {
               return self::getCustomUrl($item['id']);
           }
           return '';
       }

       // If we get a string, process it directly
       return self::getCustomUrl($item);
   }

   /**
    * Gets a custom URL for the given value
    * Maintains backward compatibility with existing implementation
    *
    * @param mixed $value
    * @param string|null $lang
    * @return string
    */
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

       // Check if the value is a REDAXO article (starts with redaxo://)
       if (is_string($value) && str_starts_with($value, 'redaxo://')) {
           $articleId = (int) substr($value, 9);
           return rex_getUrl($articleId, $lang);
       }

       // Check if the value is a REDAXO article (starts with rex://)
       if (is_string($value) && str_starts_with($value, 'rex://')) {
           $articleId = (int) substr($value, 6);
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
