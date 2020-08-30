<?php

/**
 * REX_CUSTOM_LINK.
 *
 * Attribute:
 *   - category  => Kategorie in die beim oeffnen der Linkmapw gesprungen werden soll
 *
 * @package redaxo\structure
 */
class rex_var_custom_link extends rex_var
{
    /**
     * @param $value
     * @return string
     * @author Joachim Doerr
     */
    public static function getCustomLinkText($value)
    {
        $valueName = $value;
        if (file_exists(rex_path::media($value)) === true) {
            // do nothing
        } else if (filter_var($value, FILTER_VALIDATE_URL) === FALSE && is_numeric($value)) {
            // article!
            $art = rex_article::get((int)$value);
            if ($art instanceof rex_article) {
                $valueName = trim(sprintf('%s [%s]', $art->getName(), $art->getId()));
            }
        }
        return $valueName;
    }

    /**
     * @return bool|string
     * @author Joachim Doerr
     */
    protected function getOutput()
    {
        $id = $this->getArg('id', 0, true);
        if (!in_array($this->getContext(), ['module', 'action']) || !is_numeric($id) || $id < 1 || $id > 10) {
            return false;
        }

        $value = $this->getContextData()->getValue('value' . $id);

        if ($this->hasArg('isset') && $this->getArg('isset')) {
            return $value ? 'true' : 'false';
        }

        if ($this->hasArg('widget') && $this->getArg('widget')) {
            if (!$this->environmentIs(self::ENV_INPUT)) return false;

            $args = [];
            foreach (['category', 'media', 'media_category', 'types', 'external', 'mailto', 'intern', 'phone', 'external_prefix', 'ylink'] as $key) {
                if ($this->hasArg($key)) {
                    $args[$key] = $this->getArg($key);
                }
            }

            if (isset($args['ylink'])) {
                $ylinks = array_filter(explode(',', $args['ylink']));
                $args['ylink'] = [];
                foreach ($ylinks as $ylink) {
                    $link = array_filter(explode('::', $ylink));
                    $args['ylink'][] = [
                        'name' => $link[0],
                        'table' => $link[1],
                        'column' => $link[2],
                    ];
                }
            }

            $value = self::getWidget($id, 'REX_INPUT_VALUE[' . $id . ']', $value, $args);
        } else {
            if ($value && $this->hasArg('output') && $this->getArg('output') != 'id') {
                $value = rex_getUrl($value);
            }
        }

        return self::quote($value);
    }

    /**
     * @param $id
     * @param $name
     * @param $value
     * @param array $args
     * @param bool $btnIdUniq
     * @return string|string[]
     * @throws rex_exception
     * @author Joachim Doerr
     */
    public static function getWidget($id, $name, $value, array $args = [], $btnIdUniq = true)
    {
        $valueName = self::getCustomLinkText($value);
        $category = '';
        $mediaCategory = '';
        $types = '';

        if (filter_var($value, FILTER_VALIDATE_URL) === FALSE && is_numeric($value)) {
            $art = rex_article::get((int)$value);
            if ($art instanceof rex_article) {
                $category = $art->getCategoryId();
            }
        }

        $wdgtClass = ' rex-js-widget-customlink';

        if (is_numeric($category) || isset($args['category']) && ($category = (int)$args['category'])) {
            $category = ' data-category="' . $category . '"';
        }
        if (isset($args['media_category']) && ($mediaCategory = (int)$args['media_category'])) {
            $mediaCategory = ' data-media_category="' . $mediaCategory . '"';
        }
        if (isset($args['types']) && ($types = $args['types'])) {
            $types = ' data-types="' . $types . '"';
        }

        $class = '';
        $mediaClass = (isset($args['media']) && $args['media'] == 0) ? ' hidden' : $class;
        $externalClass = (isset($args['external']) && $args['external'] == 0) ? ' hidden' : $class;
        $emailClass = (isset($args['mailto']) && $args['mailto'] == 0) ? ' hidden' : $class;
        $linkClass = (isset($args['intern']) && $args['intern'] == 0) ? ' hidden' : $class;
        $phoneClass = (isset($args['phone']) && $args['phone'] == 0) ? ' hidden' : $class;
        $externalPrefix = (isset($args['external_prefix']) && $args['external_prefix'] == 0) ? $args['external_prefix'] : 'https://';

        $ylinks = '';

        if (isset($args['ylink']) && is_array($args['ylink']) && sizeof($args['ylink']) > 0 && isset($args['ylink'][0]['name'])) {

            /*
  <a class="btn btn-popup" href="#" data-toggle="dropdown"><i class="rex-icon fa-database"></i></a>
  <ul class="dropdown-menu">
    <li><a href="#">Action</a></li>
    <li><a href="#">Another action</a></li>
    <li><a href="#">Something else here</a></li>
    <li role="separator" class="divider"></li>
    <li><a href="#">Separated link</a></li>
  </ul>
             */

            foreach ($args['ylink'] as $link) {
                if (is_array($link) && isset($link['name']) && isset($link['table']) && isset($link['column'])) {
                    $ylinks .= '<li><a href="#" data-table="' . $link['table'] . '" data-column="' . $link['column'] . '" data-name="' . $link['name'] . '">' . $link['name'] . '</a></li>';
                }
            }
            if (!empty($ylinks)) {
                $ylinks = '<a class="btn btn-popup" href="#" data-toggle="dropdown"><i class="rex-icon fa-database"></i></a><ul class="dropdown-menu">' . $ylinks . '</ul>';
            }
        }

        if ($btnIdUniq === true) {
            $id = uniqid($id);
        }

        $e = [];
        $e['field'] = '<input class="form-control" type="text" name="REX_LINK_NAME[' . $id . ']" value="' . rex_escape($valueName) . '" id="REX_LINK_' . $id . '_NAME" readonly="readonly" /><input type="hidden" name="' . $name . '" id="REX_LINK_' . $id . '" value="' . $value . '" />';
        $e['before'] = '<div class="rex-js-widget custom-link' . $wdgtClass . '" data-widget-id="' . $id . '">';
        $e['after'] = '</div>';
        $e['functionButtons'] = $ylinks . '
        <a href="#" class="btn btn-popup' . $mediaClass . '" id="mform_media_' . $id . '" title="' . rex_i18n::msg('var_media_open') . '"><i class="rex-icon fa-file-o"></i></a>
        <a href="#" class="btn btn-popup' . $externalClass . '" id="mform_extern_' . $id . '" title="' . rex_i18n::msg('var_extern_link') . '"><i class="rex-icon fa-external-link"></i></a>
        <a href="#" class="btn btn-popup' . $emailClass . '" id="mform_mailto_' . $id . '" title="' . rex_i18n::msg('var_mailto_link') . '"><i class="rex-icon fa-envelope-o"></i></a>
        <a href="#" class="btn btn-popup' . $phoneClass . '" id="mform_tel_' . $id . '" title="' . rex_i18n::msg('var_phone_link') . '"><i class="rex-icon fa-phone"></i></a>
        <a href="#" class="btn btn-popup' . $linkClass . '" id="mform_link_' . $id . '" title="' . rex_i18n::msg('var_link_open') . '"><i class="rex-icon rex-icon-open-linkmap"></i></a>
        <a href="#" class="btn btn-popup' . $class . '" id="mform_delete_' . $id . '" title="' . rex_i18n::msg('var_link_delete') . '"><i class="rex-icon rex-icon-delete-link"></i></a>
        ';

        $fragment = new rex_fragment();
        $fragment->setVar('elements', [$e], false);
        return str_replace(
            '<div class="input-group">',
            '<div class="input-group custom-link" ' . $category . $types . $mediaCategory . ' data-extern-link-prefix="' . $externalPrefix . '" data-clang="' . rex_clang::getCurrentId() . '" data-id="' . $id . '">',
            $fragment->parse('core/form/widget.php')
        );
    }
}
