<?php

/**
 * @package redaxo\structure
 * usage:
 *  $field = $form->addField('', 'link', null, ['internal::fieldClass' => 'rex_form_widget_mform_customlink_element'], true);
 */
class rex_form_widget_mform_customlink_element extends rex_form_element
{
    /** @var array<string, mixed> */
    private array $args = [];

    // 1. Parameter nicht genutzt, muss aber hier stehen,
    // wg einheitlicher Konstruktorparameter
    public function __construct(string $tag = '', ?rex_form_base $table = null, array $attributes = [])
    {
        parent::__construct($tag, $table, $attributes);
    }

    public function setCategoryId(mixed $categoryId): void
    {
        $this->args['category'] = $categoryId;
    }

    public function setMedia(mixed $media): void
    {
        $this->args['media'] = $media;
    }

    public function setExternal(mixed $external): void
    {
        $this->args['external'] = $external;
    }

    public function setMailto(mixed $mailto): void
    {
        $this->args['mailto'] = $mailto;
    }

    public function setIntern(mixed $intern): void
    {
        $this->args['intern'] = $intern;
    }

    public function setMediaCategoryId(mixed $categoryId): void
    {
        $this->args['media_category'] = $categoryId;
    }

    public function setTypes(mixed $types): void
    {
        $this->args['types'] = $types;
    }

    public function setPhone(mixed $phone): void
    {
        $this->args['phone'] = $phone;
    }

    public function setYLink(mixed $ylink): void
    {
        $this->args['ylink'] = $ylink;
    }

    public function formatElement(): string
    {
        static $widgetCounter = 1;

        $html = rex_var_custom_link::getWidget($widgetCounter, (string) $this->getAttribute('name'), $this->getValue(), $this->args);

        ++$widgetCounter;
        return $html;
    }
}
