<?php
/**
 * @author Joachim Doerr
 * @package redaxo5
 * @license MIT
 */

class rex_yform_value_linklist extends rex_yform_value_abstract
{
    public function enterObject(): void
    {
        static $counter = 0;
        ++$counter;

        if ($this->needsOutput()) {
            $this->params['form_output'][$this->getId()] = $this->parse('value.linklist.tpl.php', compact('counter'));
        }

        $this->params['value_pool']['email'][$this->getElement(1)] = $this->getValue();
        $this->params['value_pool']['sql'][$this->getElement(1)] = $this->getValue();
    }

    /**
     * @return array<string, mixed>
     */
    public function getDefinitions(): array
    {
        return [
            'type' => 'value',
            'name' => 'linklist',
            'values' => [
                'name' => ['type' => 'name', 'label' => rex_i18n::msg('yform_values_defaults_name')],
                'label' => ['type' => 'text', 'label' => rex_i18n::msg('yform_values_defaults_label')],
                'category' => ['type' => 'be_link', 'label' => rex_i18n::msg('yform_values_custom_link_link_category')],
                'toolbar' => ['type' => 'select', 'label' => rex_i18n::msg('yform_values_linklist_toolbar'), 'options' => 'horizontal=horizontal,vertical=vertical'],
                'notice' => ['type' => 'text', 'label' => rex_i18n::msg('yform_values_defaults_notice')],
            ],
            'description' => rex_i18n::msg('yform_values_linklist_description'),
            'formbuilder' => false,
            'db_type' => ['text'],
        ];
    }

    /**
     * @param array<string, mixed> $params
     */
    public static function getListValue($params): string
    {
        $ids = array_filter(array_map('trim', explode(',', (string) $params['value'])));
        if ([] === $ids) {
            return '-';
        }

        $return = [];
        foreach ($ids as $id) {
            $label = $id;
            $article = rex_article::get((int) $id);
            if ($article instanceof rex_article) {
                $label = trim($article->getName() . ' [' . $article->getId() . ']');
            }
            $return[] = rex_escape($label);
        }

        return implode('<br />', $return);
    }

    /**
     * @param array<string, mixed> $params
     */
    public static function getSearchField($params): void
    {
        $params['searchForm']->setValueField('text', ['name' => $params['field']->getName(), 'label' => $params['field']->getLabel()]);
    }

    /**
     * @param array<string, mixed> $params
     */
    public static function getSearchFilter($params): string
    {
        return rex_yform_value_imagelist::getSearchFilter($params);
    }
}