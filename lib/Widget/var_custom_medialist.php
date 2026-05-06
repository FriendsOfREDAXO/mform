<?php

/**
 * Custom Medialist widget for MForm.
 *
 * Keeps REDAXO storage format (comma-separated filenames),
 * but provides a modern list UI that also works in repeater contexts.
 */
class rex_var_custom_medialist extends rex_var
{
    protected function getOutput()
    {
        return false;
    }

    public static function getWidget($id, $name, $value, array $args = [])
    {
        $openParams = '';
        if (isset($args['category']) && ($category = (int) $args['category'])) {
            $openParams .= '&rex_file_category=' . $category;
        }

        if (isset($args['types']) && is_string($args['types']) && '' !== trim($args['types'])) {
            $openParams .= '&args[types]=' . urlencode(trim($args['types']));
        }

        $values = [];
        if (is_string($value) && '' !== trim($value)) {
            foreach (explode(',', $value) as $file) {
                $file = trim($file);
                if ('' !== $file) {
                    $values[] = $file;
                }
            }
        }

        $options = '';
        foreach ($values as $file) {
            $escaped = rex_escape($file);
            $ext = strtolower((string) rex_file::extension($file));
            $isRasterImage = in_array($ext, ['jpg', 'jpeg', 'png', 'gif'], true);
            $isModernImage = in_array($ext, ['webp', 'avif'], true);
            $isSvg = 'svg' === $ext;
            $isVideo = in_array($ext, ['mp4', 'webm', 'ogg'], true);
            $isImage = $isRasterImage || $isModernImage || $isSvg;

            $mediaKind = 'file';
            if ($isVideo) {
                $mediaKind = 'video';
            } elseif ($isImage) {
                $mediaKind = 'image';
            }

            // Modern image formats (avif, webp) and SVG/video go directly via /media/
            // since rex_medialistbutton_preview may not support them
            $previewUrl = '';
            if ($isVideo || $isSvg || $isModernImage) {
                $previewUrl = rex_url::media($file);
            } elseif ($isRasterImage) {
                $previewUrl = html_entity_decode(
                    rex_url::backendController([
                        'rex_media_type' => 'rex_medialistbutton_preview',
                        'rex_media_file' => $file,
                    ]),
                    ENT_QUOTES | ENT_HTML5,
                    'UTF-8'
                );
            }

            $options .= '<option value="' . $escaped . '" data-preview="' . rex_escape($previewUrl) . '" data-ext="' . rex_escape($ext) . '" data-media-kind="' . rex_escape($mediaKind) . '" data-is-image="' . ($isImage ? '1' : '0') . '">' . $escaped . '</option>';
        }

        $disabled = '';
        if (!rex::requireUser()->getComplexPerm('media')->hasMediaPerm()) {
            $disabled = ' disabled';
        }

        $initialView = 'list';
        if (isset($args['view']) && is_string($args['view'])) {
            $candidate = strtolower(trim($args['view']));
            if (in_array($candidate, ['list', 'grid', 'gallery'], true)) {
                $initialView = $candidate;
            }
        }

        $availableViews = ['list', 'gallery', 'grid'];
        if (isset($args['views']) && is_string($args['views']) && '' !== trim($args['views'])) {
            $views = array_filter(array_map(static function (string $view): string {
                return strtolower(trim($view));
            }, explode(',', $args['views'])), static function (string $view): bool {
                return in_array($view, ['list', 'grid', 'gallery'], true);
            });
            $views = array_values(array_unique($views));
            if ([] !== $views) {
                $availableViews = $views;
            }
        }

        if (!in_array($initialView, $availableViews, true)) {
            $initialView = $availableViews[0];
        }

        $viewSwitch = true;
        if (isset($args['view_switch'])) {
            $viewSwitch = (bool) $args['view_switch'];
        }

        $toolbarOrientation = 'horizontal';
        if (isset($args['toolbar']) && is_string($args['toolbar'])) {
            $candidate = strtolower(trim($args['toolbar']));
            if (in_array($candidate, ['horizontal', 'vertical'], true)) {
                $toolbarOrientation = $candidate;
            }
        }

        $hideLabel = false;
        if (isset($args['hide_label'])) {
            $hideLabel = (bool) $args['hide_label'];
        }

        $id = (string) $id;
        $previewBase = rex_url::backendController(['rex_media_type' => 'rex_medialistbutton_preview']);
        $previewBase = html_entity_decode($previewBase, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $previewBase .= (str_contains($previewBase, '?') ? '&' : '?') . 'rex_media_file=';

        $viewButton = '';
        if ($viewSwitch && count($availableViews) > 1) {
            $viewButton = '<button type="button" class="btn btn-popup mform-list-btn mform-list-btn-view-toggle" data-action="toggle-view" data-title-list="' . rex_escape(rex_i18n::msg('mform_list_widget_view_list')) . '" data-title-grid="' . rex_escape(rex_i18n::msg('mform_list_widget_view_grid')) . '" data-title-gallery="' . rex_escape('Gallery view') . '" title="' . rex_escape(rex_i18n::msg('mform_list_widget_view_grid')) . '"><i class="rex-icon fa-th-large"></i></button>';
        }

        return '<div class="rex-js-widget mform-list-widget mform-list-widget-medialist" data-widget-type="medialist" data-widget-id="' . rex_escape($id) . '" data-view="' . rex_escape($initialView) . '" data-views="' . rex_escape(implode(',', $availableViews)) . '" data-toolbar="' . rex_escape($toolbarOrientation) . '" data-hide-label="' . ($hideLabel ? '1' : '0') . '" data-preview-base="' . rex_escape($previewBase) . '" data-params="' . rex_escape($openParams) . '">'
            . '<div class="mform-list-shell">'
            . '<ul class="mform-list-items" data-empty="' . rex_escape(rex_i18n::msg('mform_widget_empty_entries')) . '"></ul>'
            . '<select class="form-control mform-list-select" name="REX_MEDIALIST_SELECT[' . rex_escape($id) . ']" id="REX_MEDIALIST_SELECT_' . rex_escape($id) . '" size="10">' . $options . '</select>'
            . '<input type="hidden" class="mform-list-value" name="' . rex_escape($name) . '" id="REX_MEDIALIST_' . rex_escape($id) . '" value="' . rex_escape((string) $value) . '">'
            . '</div>'
            . '<div class="mform-list-toolbar">'
            . '<button type="button" class="btn btn-popup mform-list-btn" data-action="open" title="' . rex_i18n::msg('var_media_open') . '"' . $disabled . '><i class="rex-icon rex-icon-open-mediapool"></i></button>'
            . '<button type="button" class="btn btn-popup mform-list-btn" data-action="add" title="' . rex_i18n::msg('var_media_new') . '"' . $disabled . '><i class="rex-icon rex-icon-add-media"></i></button>'
            . $viewButton
            . '<button type="button" class="btn btn-popup mform-list-btn" data-action="view" title="' . rex_i18n::msg('var_media_view') . '"' . $disabled . '><i class="rex-icon rex-icon-view-media"></i></button>'
            . '<button type="button" class="btn btn-popup mform-list-btn" data-action="up" title="' . rex_i18n::msg('var_medialist_move_up') . '"><i class="rex-icon rex-icon-up"></i></button>'
            . '<button type="button" class="btn btn-popup mform-list-btn" data-action="down" title="' . rex_i18n::msg('var_medialist_move_down') . '"><i class="rex-icon rex-icon-down"></i></button>'
            . '<button type="button" class="btn btn-popup mform-list-btn" data-action="delete" title="' . rex_i18n::msg('var_media_remove') . '"' . $disabled . '><i class="rex-icon rex-icon-delete-media"></i></button>'
            . '</div>'
            . '</div>';
    }
}
