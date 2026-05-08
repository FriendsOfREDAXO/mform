<?php

/**
 * @package redaxo\mediapool
 * usage:
 *  $field = $form->addField('', 'image_list', null, ['internal::fieldClass' => 'rex_form_widget_mform_imglist_element'], true);
 */
class rex_form_widget_mform_imglist_element extends rex_form_element
{
    /** @var array<string, mixed> */
    private array $args = [];

    // 1. Parameter nicht genutzt, muss aber hier stehen,
    // wg einheitlicher Konstrukturparameter
    public function __construct(string $tag = '', ?rex_form_base $table = null, array $attributes = [])
    {
        parent::__construct($tag, $table, $attributes);
    }

    public function setCategoryId(mixed $categoryId): void
    {
        $this->args['category'] = $categoryId;
    }

    public function setTypes(mixed $types): void
    {
        $this->args['types'] = $types;
    }

    public function setPreview(mixed $preview = true): void
    {
        $this->args['preview'] = $preview;
    }

    public function formatElement(): string
    {
        static $widgetCounter = 1;

        $html = rex_var_imglist::getWidget($widgetCounter, $this->getAttribute('name'), $this->getValue(), $this->args);

        ++$widgetCounter;
        return $html;
    }
}
