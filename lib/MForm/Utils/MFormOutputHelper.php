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
    /**
     * Unified entry point for link normalization.
     *
     * Accepts legacy single-link values, repeater values (array with `id`/`name`),
     * and already prepared CustomLink arrays.
     *
     * @param mixed $input
     * @param array<string, mixed> $options
     * @return array<string, mixed>
     */
    public static function createLinkData(mixed $input, array $options = []): array
    {
        return self::normalizeLinkData($input, $options);
    }

    /**
     * Normalizes any supported link input into a consistent frontend structure.
     *
     * Supported inputs:
     * - string link value (e.g. redaxo://10, https://..., mailto:...)
     * - array with `link`
     * - repeater-like array with `id` / `name`
     * - already prepared arrays containing `customlink_url`
     *
     * Options:
     * - extern_blank (bool, default true)
     * - mode (string: frontend|raw|strict, default frontend)
     *
     * @param mixed $input
     * @param array<string, mixed> $options
     * @return array<string, mixed>
     */
    public static function normalizeLinkData(mixed $input, array $options = []): array
    {
        $externBlank = true;
        if (isset($options['extern_blank'])) {
            $externBlank = (bool) $options['extern_blank'];
        }

        $mode = 'frontend';
        if (isset($options['mode']) && is_string($options['mode'])) {
            $mode = strtolower($options['mode']);
        }

        // If data is already prepared, keep it as source of truth.
        if (is_array($input) && isset($input['customlink_url'])) {
            /** @var array<string, mixed> $prepared */
            $prepared = $input;
            if (!isset($prepared['customlink_url']) || !is_string($prepared['customlink_url'])) {
                $prepared['customlink_url'] = '';
            }
            if (!isset($prepared['customlink_text']) || !is_string($prepared['customlink_text'])) {
                $prepared['customlink_text'] = '';
            }
            if (!isset($prepared['customlink_target']) || !is_string($prepared['customlink_target'])) {
                $prepared['customlink_target'] = '';
            }
            if (!isset($prepared['customlink_class']) || !is_string($prepared['customlink_class'])) {
                $prepared['customlink_class'] = '';
            }

            return $prepared;
        }

        $linkItem = self::buildLinkItemFromMixedInput($input);
        $normalized = self::prepareCustomLink($linkItem, $externBlank);

        // Keep backend helper labels only in explicit raw mode.
        if ('raw' === $mode && is_array($input) && isset($input['name']) && is_string($input['name']) && !isset($normalized['text'])) {
            $normalized['name'] = $input['name'];
        }

        if ('strict' === $mode) {
            if (!isset($normalized['customlink_url']) || !is_string($normalized['customlink_url'])) {
                $normalized['customlink_url'] = '';
            }
            if (!isset($normalized['customlink_text']) || !is_string($normalized['customlink_text'])) {
                $normalized['customlink_text'] = '';
            }
        }

        return $normalized;
    }

    /**
     * Normalizes configured link fields of repeater rows.
     *
     * By default a new key `<field>_normalized` is added.
     * Set option `replace` => true to overwrite the original field.
     *
     * @param array<int, array<string, mixed>> $items
     * @param array<int, string> $linkFields
     * @param array<string, mixed> $options
     * @return array<int, array<string, mixed>>
     */
    public static function normalizeRepeaterItems(array $items, array $linkFields, array $options = []): array
    {
        $replace = false;
        if (isset($options['replace'])) {
            $replace = (bool) $options['replace'];
        }

        foreach ($items as $index => $item) {
            foreach ($linkFields as $fieldName) {
                if (!isset($item[$fieldName])) {
                    continue;
                }

                $normalized = self::normalizeLinkData($item[$fieldName], $options);
                if ($replace) {
                    $items[$index][$fieldName] = $normalized;
                } else {
                    $items[$index][$fieldName . '_normalized'] = $normalized;
                }
            }
        }

        return $items;
    }

    /**
     * @param mixed $input
     * @return array<string, mixed>
     */
    private static function buildLinkItemFromMixedInput(mixed $input): array
    {
        if (is_string($input) || is_numeric($input)) {
            return ['link' => (string) $input, 'text' => ''];
        }

        if (!is_array($input)) {
            return ['link' => '', 'text' => ''];
        }

        $linkValue = '';
        $textValue = '';

        if (isset($input['link']) && (is_string($input['link']) || is_numeric($input['link']))) {
            $linkValue = (string) $input['link'];
        } elseif (isset($input['id']) && (is_string($input['id']) || is_numeric($input['id']))) {
            // Repeater values can come as ['id' => 'redaxo://12', 'name' => 'Artikel [12]'].
            $linkValue = (string) $input['id'];
        } elseif (isset($input['customlink_url']) && is_string($input['customlink_url'])) {
            $linkValue = $input['customlink_url'];
        }

        if (isset($input['text']) && is_string($input['text'])) {
            $textValue = $input['text'];
        } elseif (isset($input['customlink_text']) && is_string($input['customlink_text'])) {
            $textValue = $input['customlink_text'];
        }

        return [
            'link' => $linkValue,
            'text' => $textValue,
        ];
    }

    public static function isFirstSlice(int $sliceId): bool
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
     * @param array<string, mixed> $item The input link item
     * @param bool $externBlank Whether external links should open in new tab
     * @return array<string, mixed> Enhanced link information
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

        // Set initial URL
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
                
                // Use media title if no text is explicitly set
                if (!empty($media->getTitle()) && empty($item['text'])) {
                    $item['customlink_text'] = $media->getTitle();
                }
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

                // Use article name if no text is explicitly set
                if (empty($item['text'])) {
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

                // Use article name if no text is explicitly set
                if (empty($item['text'])) {
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
                        if (empty($item['text'])) {
                            $item['customlink_text'] = $item['metadata']['phone_number'];
                        }
                    } elseif ($urlComponents['scheme'] === 'mailto') {
                        $item['type'] = 'email';
                        $item['customlink_class'] = ' mail';
                        $item['metadata']['email'] = str_replace('mailto:', '', $item['customlink_url']);
                        if (empty($item['text'])) {
                            $item['customlink_text'] = $item['metadata']['email'];
                        }
                    }
                }
            }

            if ($externBlank && $item['type'] === 'external') {
                $item['customlink_target'] = ' target="_blank" rel="noopener noreferrer"';
            }
        }

        // Set text based on priority:
        // 1. Explicitly provided text
        // 2. Type-specific defaults (already set above)
        // 3. URL as fallback
        if (!empty($item['text'])) {
            $item['customlink_text'] = $item['text'];
        } elseif (empty($item['customlink_text'])) {
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
     * @param array<string, mixed>|string $item The CustomLink array or link string
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

            $normalized = self::normalizeLinkData($item, ['extern_blank' => $externBlank]);
            return (string) ($normalized['customlink_url'] ?? '');
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
