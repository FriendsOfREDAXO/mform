<?php
/**
 * Author: Joachim Doerr
 * Date: 2019-02-26
 * Time: 10:27
 */

class rex_yform_value_custom_link extends rex_yform_value_abstract
{
    public function enterObject()
    {
        static $counter = 0;
        ++$counter;

        if ($this->needsOutput()) {
            $this->params['form_output'][$this->getId()] = $this->parse('value.custom_link.tpl.php', compact('counter'));
        }

        $this->params['value_pool']['email'][$this->getName()] = $this->getValue();
        $this->params['value_pool']['sql'][$this->getName()] = $this->getValue();
    }

    public function getDefinitions()
    {
        return [
            'type' => 'value',
            'name' => 'custom_link',
            'values' => [
                'name' => ['type' => 'name',   'label' => rex_i18n::msg('yform_values_defaults_name')],
                'label' => ['type' => 'text',   'label' => rex_i18n::msg('yform_values_defaults_label')],
                'media' => ['type' => 'checkbox',   'label' => rex_i18n::msg('yform_values_custom_link_media')],
                'extern' => ['type' => 'checkbox',   'label' => rex_i18n::msg('yform_values_custom_link_extern')],
                'mailto' => ['type' => 'checkbox',   'label' => rex_i18n::msg('yform_values_custom_link_mailto')],
                'intern' => ['type' => 'checkbox',   'label' => rex_i18n::msg('yform_values_custom_link_intern')],
                'notice' => ['type' => 'text',    'label' => rex_i18n::msg('yform_values_defaults_notice')],
            ],
            'description' => rex_i18n::msg('yform_values_custom_link_description'),
            'formbuilder' => false,
            'db_type' => ['text']
        ];
    }

    public static function getListValue($params)
    {
        if ($params['value'] == '') {
            return '-';
        }
        return rex_var_custom_link::getCustomLinkText($params['value']);
    }
}