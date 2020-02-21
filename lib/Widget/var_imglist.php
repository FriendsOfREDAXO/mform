<?php

/**
 * REX_MEDIALIST[1].
 *
 * Attribute:
 *   - category  => Kategorie in die beim oeffnen des Medienpools gesprungen werden soll
 *   - types     => Filter für Dateiendungen die im Medienpool zur Auswahl stehen sollen
 *   - preview   => Bei Bildertypen ein Vorschaubild einblenden
 *
 * @package redaxo\mediapool
 */
class rex_var_imglist extends rex_var
{
    protected function getOutput()
    {
        $id = $this->getArg('id', 0, true);
        if (!in_array($this->getContext(), ['module', 'action']) || !is_numeric($id) || $id < 1 || $id > 10) {
            return false;
        }

        $value = $this->getContextData()->getValue('medialist' . $id);

        if ($this->hasArg('isset') && $this->getArg('isset')) {
            return $value ? 'true' : 'false';
        }

        if ($this->hasArg('widget') && $this->getArg('widget')) {
            if (!$this->environmentIs(self::ENV_INPUT)) {
                return false;
            }
            $args = [];
            foreach (['category', 'preview', 'types'] as $key) {
                if ($this->hasArg($key)) {
                    $args[$key] = $this->getArg($key);
                }
            }
            $value = self::getWidget($id, 'REX_INPUT_MEDIALIST[' . $id . ']', $value, $args);
        }

        return self::quote($value);
    }

    public static function getWidget($id, $name, $value, array $args = [])
    {
        $open_params = '';
        if (isset($args['category']) && ($category = (int) $args['category'])) {
            $open_params .= '&amp;rex_file_category=' . $category;
        }

        foreach ($args as $aname => $avalue) {
            $open_params .= '&amp;args[' . $aname . ']=' . urlencode($avalue);
        }

        $wdgtClass = ' rex-js-widget-imglist';
        if (isset($args['preview']) && $args['preview']) {
            $wdgtClass .= ' rex-js-widget-preview';
            if (rex_addon::get('media_manager')->isAvailable()) {
                $wdgtClass .= ' rex-js-widget-preview-media-manager';
            }
        }

        // todo tooltip option
        // add to $wdgtClass class rex-js-widget-tooltip

        $thumbnails = '';
        $options = '';
        $medialistarray = explode(',', $value);
        if (is_array($medialistarray)) {
            foreach ($medialistarray as $key => $file) {
                if ($file != '') {

                    $url = rex_url::backendController(['rex_media_type' => 'rex_medialistbutton_preview', 'rex_media_file' => $file]);
                    if (pathinfo($file, PATHINFO_EXTENSION) === 'svg') {
                        $url = rex_url::media($file);
                    }
                    $thumbnails .= '<li data-key="' . $key . '" value="' . $file . '" data-value="' . $file . '"><img class="thumbnail" src="' . $url . '" /></li>';

                    $options .= '<option data-key="' . $key . '" value="' . $file . '">' . $file . '</option>';
                }
            }
        }

        $disabled = ' disabled';
        if (rex::getUser()->getComplexPerm('media')->hasMediaPerm()) {
            $disabled = '';
        }

        $id = str_replace(array('][', '[', ']'), '', $id);

        $e = [];
        $e['before'] = '<div class="rex-js-widget custom-imglist ' . $wdgtClass . '" data-params="' . $open_params . '" data-widget-id="' . $id . '">';
        $e['field'] = '<ul class="form-control thumbnail-list" id="REX_IMGLIST_' . $id . '">' . $thumbnails . '</ul><select class="form-control" name="REX_MEDIALIST_SELECT[' . $id . ']" id="REX_MEDIALIST_SELECT_' . $id . '" size="10">' . $options . '</select><input type="hidden" name="' . $name . '" id="REX_MEDIALIST_' . $id . '" value="' . $value . '" />';
        $e['functionButtons'] = '
                <a href="#" class="btn btn-popup open" title="' . rex_i18n::msg('var_media_open') . '"' . $disabled . '><i class="rex-icon rex-icon-open-mediapool"></i></a>
                <a href="#" class="btn btn-popup add" title="' . rex_i18n::msg('var_media_new') . '"' . $disabled . '><i class="rex-icon rex-icon-add-media"></i></a>
                <a href="#" class="btn btn-popup delete" title="' . rex_i18n::msg('var_media_remove') . '"' . $disabled . '><i class="rex-icon rex-icon-delete-media"></i></a>
                <a href="#" class="btn btn-popup view" title="' . rex_i18n::msg('var_media_view') . '"' . $disabled . '><i class="rex-icon rex-icon-view-media"></i></a>';
        $e['after'] = '<div class="rex-js-media-preview"></div></div>';

        $fragment = new rex_fragment();
        $fragment->setVar('elements', [$e], false);
        $media = $fragment->parse('core/form/widget_list.php');

        return $media;
    }
}
