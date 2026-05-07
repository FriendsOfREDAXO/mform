<?php

/**
 * @package redaxo\mediapool
 * usage:
 *  $field = $form->addField('', 'media_list', null, ['internal::fieldClass' => 'rex_form_widget_mform_medialist_element'], true);
 */
class rex_form_widget_mform_medialist_element extends rex_form_element
{
    /** @var array<string, mixed> */
    private array $args = [];

    // 1. Parameter nicht genutzt, muss aber hier stehen,
    // wg einheitlicher Konstruktorparameter
    public function __construct($tag = '', ?rex_form_base $table = null, array $attributes = [])
    {
        parent::__construct('', $table, $attributes);
    }

    public function setCategoryId(int $categoryId): void
    {
        $this->args['category'] = $categoryId;
    }

    public function setTypes(string $types): void
    {
        $this->args['types'] = $types;
    }

    public function setView(string $view): void
    {
        $this->args['view'] = $view;
    }

    public function setViews(string $views): void
    {
        $this->args['views'] = $views;
    }

    public function setViewSwitch(bool $enabled): void
    {
        $this->args['view_switch'] = $enabled;
    }

    /**
     * @param 'horizontal'|'vertical' $orientation
     */
    public function setToolbar(string $orientation): void
    {
        $this->args['toolbar'] = $orientation;
    }

    public function setHideLabel(bool $hideLabel): void
    {
        $this->args['hide_label'] = $hideLabel;
    }

    public function formatElement(): string
    {
        static $widgetCounter = 1;

        $html = rex_var_custom_medialist::getWidget(
            $widgetCounter,
            (string) $this->getAttribute('name'),
            (string) $this->getValue(),
            $this->args,
        );

        ++$widgetCounter;
        return $html;
    }
}