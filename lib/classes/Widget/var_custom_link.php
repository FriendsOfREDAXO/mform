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
    protected function getOutput()
    {
        $id = $this->getArg('id', 0, true);
        if (!in_array($this->getContext(), ['module', 'action']) || !is_numeric($id) || $id < 1 || $id > 10) {
            return false;
        }

        $value = $this->getContextData()->getValue('link' . $id);

        if ($this->hasArg('isset') && $this->getArg('isset')) {
            return $value ? 'true' : 'false';
        }

        if ($this->hasArg('widget') && $this->getArg('widget')) {
            if (!$this->environmentIs(self::ENV_INPUT)) {
                return false;
            }
            $args = [];
            foreach (['category'] as $key) {
                if ($this->hasArg($key)) {
                    $args[$key] = $this->getArg($key);
                }
            }
            $value = self::getWidget($id, 'REX_INPUT_LINKLIST[' . $id . ']', $value, $args);
        } else {
            if ($value && $this->hasArg('output') && $this->getArg('output') != 'id') {
                $value = rex_getUrl($value);
            }
        }

        return self::quote($value);
    }

    public static function getWidget($id, $name, $value, array $args = [])
    {
        $valueName = $value;
        $category = '';
        if (is_int($value)) {
            $art = rex_article::get($value);

            if ($art instanceof rex_article) {
                $valueName = trim(sprintf('%s [%s]', $art->getName(), $art->getId()));
                $category = $art->getCategoryId();
            }
        }

        if (is_int($category) || isset($args['category']) && ($category = (int)$args['category'])) {
            $category = 'data-category=' . $category;
        }

        $class = (rex::getUser()->getComplexPerm('structure')->hasStructurePerm()) ? '' : ' rex-disabled';
        $mediaClass = (isset($args['media']) && $args['media'] == 0) ? ' hidden' : $class;
        $externalClass = (isset($args['external']) && $args['external'] == 0) ? ' hidden' : $class;
        $emailClass = (isset($args['mailto']) && $args['mailto'] == 0) ? ' hidden' : $class;
        $linkClass = (isset($args['link']) && $args['link'] == 0) ? ' hidden' : $class;

        $e = [];
        $e['field'] = '<input class="form-control" type="text" name="REX_LINKLIST_SELECT[' . $id . ']" value="' . rex_escape($valueName) . '" id="REX_CUSTOM_LINK_' . $id . '_NAME" readonly="readonly" /><input type="hidden" name="' . $name . '" id="REX_CUSTOM_LINK_' . $id . '" value="' . $value . '" />';
        $e['functionButtons'] = '
        <a href="#" class="btn btn-popup' . $mediaClass . '" id="mform_media_' . $id . '" title="' . rex_i18n::msg('var_media_open') . '"><i class="rex-icon fa-file-o"></i></a>
        <a href="#" class="btn btn-popup' . $externalClass . '" id="mform_extern_' . $id . '" title="' . rex_i18n::msg('var_extern_link') . '"><i class="rex-icon fa-external-link"></i></a>
        <a href="#" class="btn btn-popup' . $emailClass . '" id="mform_mailto_' . $id . '" title="' . rex_i18n::msg('var_mailto_link') . '"><i class="rex-icon fa-envelope-o"></i></a>
        <a href="#" class="btn btn-popup' . $linkClass . '" id="mform_link_' . $id . '" title="' . rex_i18n::msg('var_link_open') . '"><i class="rex-icon rex-icon-open-linkmap"></i></a>
        <a href="#" class="btn btn-popup' . $class . '" id="mform_delete_' . $id . '" title="' . rex_i18n::msg('var_link_delete') . '"><i class="rex-icon rex-icon-delete-link"></i></a>
        ';

        $fragment = new rex_fragment();
        $fragment->setVar('elements', [$e], false);
        return str_replace(
            '<div class="input-group">',
            '<div class="input-group custom-link" ' . $category . ' data-clang="' . rex_clang::getCurrentId() . '" data-id="' . $id . '">',
            $fragment->parse('core/form/widget.php')
        );
    }
}




/*
 *
 *

<div class="input-group custom-link" data-clang="1" data-id="2601customlinkf">
    <input class="form-control" id="REX_LINK_2601customlinkf_NAME" name="REX_LINK_NAME[2601customlinkf]" readonly="readonly" type="text" value="">
    <input id="REX_LINK_2601customlinkf" name="REX_INPUT_VALUE[1][customlinkf]" type="hidden" value="">
    <span class="input-group-btn">
        <a class="btn btn-popup" href="#" id="mform_media_2601customlinkf" title="Medium auswählen"><i class="rex-icon fa-file-o"></i></a>
        <a class="btn btn-popup" href="#" id="mform_extern_2601customlinkf" title="Externe URL verlinken"><i class="rex-icon fa-external-link"></i></a>
        <a class="btn btn-popup" href="#" id="mform_link_2601customlinkf" title="Link auswählen"><i class="rex-icon rex-icon-open-linkmap"></i></a>
        <a class="btn btn-popup" href="#" id="mform_delete_2601customlinkf" title="Ausgewählten Link löschen"><i class="rex-icon rex-icon-delete-link"></i></a>
    </span>
</div>

 *
 *
 */