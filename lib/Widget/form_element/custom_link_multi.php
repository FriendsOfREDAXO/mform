<?php

/**
 * @package redaxo\structure
 * usage:
 *  $field = $form->addField('', 'links', null, ['internal::fieldClass' => 'rex_form_widget_mform_custom_link_multi_element'], true);
 */
class rex_form_widget_mform_custom_link_multi_element extends rex_form_element
{
    /** @var array<string, mixed> */
    private array $args = [];

    // 1. Parameter nicht genutzt, muss aber hier stehen,
    // wg einheitlicher Konstruktorparameter
    public function __construct(string $tag = '', ?rex_form_base $table = null, array $attributes = [])
    {
        parent::__construct($tag, $table, $attributes);
    }

    public function setIntern(int $intern): void
    {
        $this->args['intern'] = $intern;
    }

    public function setExternal(int $external): void
    {
        $this->args['external'] = $external;
    }

    public function setMedia(int $media): void
    {
        $this->args['media'] = $media;
    }

    public function setMailto(int $mailto): void
    {
        $this->args['mailto'] = $mailto;
    }

    public function setPhone(int $phone): void
    {
        $this->args['phone'] = $phone;
    }

    public function setAnchor(int $anchor): void
    {
        $this->args['anchor'] = $anchor;
    }

    public function setBtnAdd(string $label): void
    {
        $this->args['btn_add'] = $label;
    }

    public function setCategoryId(int $categoryId): void
    {
        $this->args['category'] = $categoryId;
    }

    public function setMediaCategoryId(int $categoryId): void
    {
        $this->args['media_category'] = $categoryId;
    }

    public function setTypes(string $types): void
    {
        $this->args['types'] = $types;
    }

    public function formatElement(): string
    {
        return rex_var_custom_link_multi::getWidget(
            uniqid('cml_', false),
            (string) $this->getAttribute('name'),
            (string) $this->getValue(),
            $this->args,
        );
    }
}
