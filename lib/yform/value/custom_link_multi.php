<?php
/**
 * @author Joachim Doerr
 * @package redaxo5
 * @license MIT
 */

class rex_yform_value_custom_link_multi extends rex_yform_value_abstract
{
    public function enterObject()
    {
        static $counter = 0;
        ++$counter;

        if ($this->needsOutput()) {
            $this->params['form_output'][$this->getId()] = $this->parse('value.custom_link_multi.tpl.php', compact('counter'));
        }

        $this->params['value_pool']['email'][$this->getName()] = $this->getValue();
        $this->params['value_pool']['sql'][$this->getName()] = $this->getValue();
    }

    public function getDefinitions(): array
    {
        return [
            'type' => 'value',
            'name' => 'custom_link_multi',
            'values' => [
                'name'           => ['type' => 'name',     'label' => rex_i18n::msg('yform_values_defaults_name')],
                'label'          => ['type' => 'text',     'label' => rex_i18n::msg('yform_values_defaults_label')],
                'media'          => ['type' => 'checkbox', 'label' => rex_i18n::msg('yform_values_custom_link_media')],
                'external'       => ['type' => 'checkbox', 'label' => rex_i18n::msg('yform_values_custom_link_external')],
                'mailto'         => ['type' => 'checkbox', 'label' => rex_i18n::msg('yform_values_custom_link_mailto')],
                'intern'         => ['type' => 'checkbox', 'label' => rex_i18n::msg('yform_values_custom_link_intern')],
                'phone'          => ['type' => 'checkbox', 'label' => rex_i18n::msg('yform_values_custom_link_phone')],
                'anchor'         => ['type' => 'checkbox', 'label' => rex_i18n::msg('yform_values_custom_link_anchor')],
                'types'          => ['type' => 'text',     'label' => rex_i18n::msg('yform_values_custom_link_media_types')],
                'media_category' => ['type' => 'text',     'label' => rex_i18n::msg('yform_values_custom_link_media_category')],
                'category'       => ['type' => 'be_link',  'label' => rex_i18n::msg('yform_values_custom_link_link_category')],
                'ylink'          => ['type' => 'text',     'label' => rex_i18n::msg('yform_values_custom_link_ylink')],
                'btn_add'        => ['type' => 'text',     'label' => rex_i18n::msg('yform_values_custom_link_multi_btn_add')],
                'notice'         => ['type' => 'text',     'label' => rex_i18n::msg('yform_values_defaults_notice')],
            ],
            'description' => rex_i18n::msg('yform_values_custom_link_multi_description'),
            'formbuilder' => false,
            'db_type' => ['text'],
        ];
    }

    public static function getListValue($params)
    {
        if ('' === $params['value']) {
            return '-';
        }
        $rawValue = html_entity_decode($params['value'], ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $links = json_decode($rawValue, true);
        if (!is_array($links)) {
            return rex_escape($params['value']);
        }
        $out = [];
        foreach ($links as $link) {
            $out[] = rex_var_custom_link::getCustomLinkText((string) $link);
        }
        return implode(', ', $out);
    }
}
