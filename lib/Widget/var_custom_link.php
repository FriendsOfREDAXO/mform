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
		$valueName = rex_extension::registerPoint(
			new rex_extension_point('mform/varCustomLink.getCustomLinkText', $valueName, [
				'value' => $value,
			])
		);
        return $valueName;
    }

    /**
     * @param $value
     * @param $table
     * @param $column
     * @param null $name
     * @return string
     * @throws rex_sql_exception
     * @author Joachim Doerr
     */
    public static function getCustomLinkYFormLinkText($value, $table, $column, $name = null)
    {
        $valueName = $value;

        preg_match('@(rex-.*)://(\d+)@i', $value, $matches, PREG_OFFSET_CAPTURE, 0);

        if ((isset($matches[1][0]) && $matches[1][0] == str_replace('_', '-', $table)) && (isset($matches[2][0]) && is_numeric($matches[2][0]))) {
            $sql = rex_sql::factory();
            $result = $sql->getArray("select $column from $table where id=:id", ['id' => $matches[2][0]]);
            if (isset($result[0][$column])) {
                $valueName = trim($result[0][$column]) . ' [id=' . $matches[2][0] .']';
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

            $value = self::getWidget($id, 'REX_INPUT_VALUE[' . $id . ']', $value, $args);
        } else {
            if ($value && $this->hasArg('output') && $this->getArg('output') != 'id') {
                $value = rex_getUrl($value);
            }
        }

        return self::quote($value);
    }

    /**
     * @param $args
     * @return mixed
     * @author Joachim Doerr
     */
    public static function prepareYLinkArg($args)
    {
        if (isset($args['ylink']) && is_string($args['ylink']) && !empty($args['ylink'])) {
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
        return $args;
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
        if (isset($args['class']))
            { $class = $args['class'];
            } 
        $mediaClass = (isset($args['media']) && $args['media'] == 0) ? ' hidden' : $class;
        $externalClass = (isset($args['external']) && $args['external'] == 0) ? ' hidden' : $class;
        $emailClass = (isset($args['mailto']) && $args['mailto'] == 0) ? ' hidden' : $class;
        $linkClass = (isset($args['intern']) && $args['intern'] == 0) ? ' hidden' : $class;
        $phoneClass = (isset($args['phone']) && $args['phone'] == 0) ? ' hidden' : $class;
        $externalPrefix = (isset($args['external_prefix']) && $args['external_prefix'] == 0) ? $args['external_prefix'] : 'https://';
        $args = self::prepareYLinkArg($args);
        $ylinks = '';

        if ($btnIdUniq === true) {
            $id = uniqid($id);
        }

        if (isset($args['ylink']) && is_array($args['ylink']) && sizeof($args['ylink']) > 0 && isset($args['ylink'][0]['name'])) {
            foreach ($args['ylink'] as $link) {
                if (is_array($link) && isset($link['name']) && isset($link['table']) && isset($link['column'])) {
                    $ylinks .= '<li><a href="#" class="ylink" data-table="' . $link['table'] . '" data-column="' . $link['column'] . '" data-name="' . $link['name'] . '">' . $link['name'] . '</a></li>';

                    if (strpos($value, str_replace('_', '-', $link['table'])) !== false) {
                        $valueName = self::getCustomLinkYFormLinkText($value, $link['table'], $link['column']);
                    }
                }
            }
            if (!empty($ylinks)) {
                $ylinks = '<a class="btn btn-popup" href="#" data-toggle="dropdown"><i class="rex-icon fa-database"></i> <span class="caret"></span></a><ul id="mform_ylink_' . $id . '" class="dropdown-menu">' . $ylinks . '</ul>';
            }
        }

        $e = [];
        $e['field'] = '<input class="form-control" type="text" name="REX_LINK_NAME[' . $id . ']" value="' . rex_escape($valueName) . '" id="REX_LINK_' . $id . '_NAME" readonly="readonly" /><input type="hidden" name="' . $name . '" id="REX_LINK_' . $id . '" value="' . $value . '" />';
        $e['before'] = '<div class="rex-js-widget custom-link' . $wdgtClass . '" data-widget-id="' . $id . '">';
        $e['after'] = '</div>';
        $e['functionButtons'] = $ylinks . '
        <a href="#" class="btn btn-popup media_link ' . $mediaClass . '" id="mform_media_' . $id . '" title="' . rex_i18n::msg('var_media_open') . '"><i class="rex-icon fa-file-o"></i></a>
        <a href="#" class="btn btn-popup external_link ' . $externalClass . '" id="mform_extern_' . $id . '" title="' . rex_i18n::msg('var_extern_link') . '"><i class="rex-icon fa-external-link"></i></a>
        <a href="#" class="btn btn-popup email_link ' . $emailClass . '" id="mform_mailto_' . $id . '" title="' . rex_i18n::msg('var_mailto_link') . '"><i class="rex-icon fa-envelope-o"></i></a>
        <a href="#" class="btn btn-popup phone_link ' . $phoneClass . '" id="mform_tel_' . $id . '" title="' . rex_i18n::msg('var_phone_link') . '"><i class="rex-icon fa-phone"></i></a>
        <a href="#" class="btn btn-popup intern_link ' . $linkClass . '" id="mform_link_' . $id . '" title="' . rex_i18n::msg('var_link_open') . '"><i class="rex-icon rex-icon-open-linkmap"></i></a>
        <a href="#" class="btn btn-popup delete_link ' . $class . '" id="mform_delete_' . $id . '" title="' . rex_i18n::msg('var_link_delete') . '"><i class="rex-icon rex-icon-delete-link"></i></a>
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
