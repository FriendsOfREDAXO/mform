<?php
/**
 * User: joachimdoerr
 * Date: 2019-03-13
 * Time: 22:10
 */

class rex_yform_value_imagelist extends rex_yform_value_be_media
{
    public function enterObject()
    {
        static $counter = 0;
        ++$counter;

        if ($this->needsOutput()) {
            $this->params['form_output'][$this->getId()] = $this->parse('value.imagelist.tpl.php', compact('counter'));
        }

        $this->params['value_pool']['email'][$this->getElement(1)] = $this->getValue();
        $this->params['value_pool']['sql'][$this->getElement(1)] = $this->getValue();
    }

    public function getDefinitions()
    {
        return [
            'type' => 'value',
            'name' => 'imagelist',
            'values' => [
                'name' => ['type' => 'name',   'label' => rex_i18n::msg('yform_values_defaults_name')],
                'label' => ['type' => 'text',    'label' => rex_i18n::msg('yform_values_defaults_label')],
                'category' => ['type' => 'text',   'label' => rex_i18n::msg('yform_values_be_media_category')],
                'types' => ['type' => 'text',   'label' => rex_i18n::msg('yform_values_be_media_types'),   'notice' => rex_i18n::msg('yform_values_be_media_types_notice')],
                'notice' => ['type' => 'text',    'label' => rex_i18n::msg('yform_values_defaults_notice')],
            ],
            'description' => rex_i18n::msg('yform_values_imagelist_description'),
            'formbuilder' => false,
            'db_type' => ['text'],
        ];
    }

}