<?php

/**
 * @package redaxo\structure
 * usage:
 *  $field = $form->addField('', 'link', null, ['internal::fieldClass' => 'rex_form_widget_customlink_element'], true);
 */
class rex_form_widget_customlink_element extends rex_form_element
{
    private $args = [];

    // 1. Parameter nicht genutzt, muss aber hier stehen,
    // wg einheitlicher Konstruktorparameter
    public function __construct($tag = '', rex_form_base $table = null, array $attributes = [])
    {
        parent::__construct('', $table, $attributes);
    }

    public function setCategoryId($category_id)
    {
        $this->args['category'] = $category_id;
    }

    public function setMedia($media)
    {
        $this->args['media'] = $media;
    }

    public function setExternal($external)
    {
        $this->args['external'] = $external;
    }

    public function setMailto($mailto)
    {
        $this->args['mailto'] = $mailto;
    }

    public function setIntern($intern)
    {
        $this->args['intern'] = $intern;
    }

    public function setMediaCategoryId($category_id)
    {
        $this->args['media_category'] = $category_id;
    }

    public function setTypes($types)
    {
        $this->args['types'] = $types;
    }

    public function setPhone($phone)
    {
        $this->args['phone'] = $phone;
    }

    public function setYLink($ylink)
    {
        $this->args['ylink'] = $ylink;
    }

    public function formatElement()
    {
        static $widget_counter = 1;

        $html = rex_var_custom_link::getWidget($widget_counter, $this->getAttribute('name'), $this->getValue(), $this->args);

        ++$widget_counter;
        return $html;
    }
}
