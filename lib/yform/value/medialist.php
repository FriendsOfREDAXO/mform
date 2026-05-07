<?php
/**
 * @author Joachim Doerr
 * @package redaxo5
 * @license MIT
 */

class rex_yform_value_medialist extends rex_yform_value_abstract
{
    public function enterObject(): void
    {
        static $counter = 0;
        ++$counter;

        if ($this->needsOutput()) {
            $this->params['form_output'][$this->getId()] = $this->parse('value.medialist.tpl.php', compact('counter'));
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
            'name' => 'medialist',
            'values' => [
                'name' => ['type' => 'name', 'label' => rex_i18n::msg('yform_values_defaults_name')],
                'label' => ['type' => 'text', 'label' => rex_i18n::msg('yform_values_defaults_label')],
                'category' => ['type' => 'text', 'label' => rex_i18n::msg('yform_values_be_media_category')],
                'types' => ['type' => 'text', 'label' => rex_i18n::msg('yform_values_be_media_types'), 'notice' => rex_i18n::msg('yform_values_be_media_types_notice')],
                'view' => ['type' => 'select', 'label' => rex_i18n::msg('yform_values_medialist_view'), 'options' => 'list=list,grid=grid,gallery=gallery'],
                'views' => ['type' => 'text', 'label' => rex_i18n::msg('yform_values_medialist_views')],
                'view_switch' => ['type' => 'checkbox', 'label' => rex_i18n::msg('yform_values_medialist_view_switch')],
                'toolbar' => ['type' => 'select', 'label' => rex_i18n::msg('yform_values_medialist_toolbar'), 'options' => 'horizontal=horizontal,vertical=vertical'],
                'hide_label' => ['type' => 'checkbox', 'label' => rex_i18n::msg('yform_values_medialist_hide_label')],
                'notice' => ['type' => 'text', 'label' => rex_i18n::msg('yform_values_defaults_notice')],
            ],
            'description' => rex_i18n::msg('yform_values_medialist_description'),
            'formbuilder' => false,
            'db_type' => ['text'],
        ];
    }

    /**
     * @param array<string, mixed> $params
     */
    public static function getListValue($params): string
    {
        $files = array_filter(array_map('trim', explode(',', (string) $params['subject'])));
        if ([] === $files) {
            return '-';
        }

        $return = [];
        foreach ($files as $file) {
            $filename = $file;
            if (mb_strlen($file) > 24) {
                $filename = mb_substr($file, 0, 10) . ' ... ' . mb_substr($file, -10);
            }
            $return[] = '<span style="white-space:nowrap;" title="' . rex_escape($file) . '">' . rex_escape($filename) . '</span>';
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