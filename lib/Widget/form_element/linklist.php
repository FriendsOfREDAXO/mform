<?php

/**
 * @package redaxo\structure
 * usage:
 *  $field = $form->addField('', 'link_list', null, ['internal::fieldClass' => 'rex_form_widget_mform_linklist_element'], true);
 */
class rex_form_widget_mform_linklist_element extends rex_form_element
{
    /** @var array<string, mixed> */
    private array $args = [];

    // 1. Parameter nicht genutzt, muss aber hier stehen,
    // wg einheitlicher Konstruktorparameter
    public function __construct($tag = '', ?rex_form_base $table = null, array $attributes = [])
    {
        parent::__construct('', $table, $attributes);
    }

    public function setCategoryId(int $category_id): void
    {
        $this->args['category'] = $category_id;
    }

    /**
     * @param 'horizontal'|'vertical' $orientation
     */
    public function setToolbar(string $orientation): void
    {
        $this->args['toolbar'] = $orientation;
    }

    public function formatElement(): string
    {
        static $widget_counter = 1;

        $html = rex_var_custom_linklist::getWidget($widget_counter, $this->getAttribute('name'), (string) $this->getValue(), $this->args);

        ++$widget_counter;
        return $html;
    }
}
