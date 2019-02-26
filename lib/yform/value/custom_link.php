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
                'multiple' => ['type' => 'checkbox',   'label' => rex_i18n::msg('yform_values_custom_link_multiple')],
                'notice' => ['type' => 'text',    'label' => rex_i18n::msg('yform_values_defaults_notice')],
            ],
            'description' => rex_i18n::msg('yform_values_custom_link_description'),
            'formbuilder' => false,
            'db_type' => ['text'],
            'famous' => true
        ];
    }

    public static function getListValue($params)
    {
        if ($params['value'] == '') {
            return '-';
        }
        $ids = explode(',', $params['value']);

        foreach ($ids as $article_id) {
            $article = $article = rex_article::get($article_id);
            if ($article) {
                $names[] = $article->getValue('name');
            }
        }

        if ($names) {
            if (count($names) > 4) {
                $names = array_slice($names, 0, 4);
                $names[] = '...';
            }
            return implode('<br />', $names);
        }

        return '-';
    }
}