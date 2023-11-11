<?php
/**
 * @author Joachim Doerr
 * @package redaxo5
 * @license MIT
 */

use MForm\DTO\MFormInputsConfig;
use MForm\Inputs\MFormInputsAbstract;
use MForm\Inputs\MFormInputsInterface;

class section_setup extends MFormInputsAbstract implements MFormInputsInterface
{
    protected array $config = [
        'id' => 1,
        'show' => [
            'section_class_padding' => true,
            'section_class_ukcolor' => true,
            'section_parallax' => true,
            'container_class_default_container_padding' => true,
            'container_class_container' => true,
            'container_bg_image' => true,
        ],
        'fieldset' => [
            'section' => 'Sektionseinstellung',
            'container' => 'Container Optionen',
            'parallax' => 'Parallax Einstellung',
        ],
        'label' => [
            'section_class_padding' => 'Abstand',
            'section_class_ukcolor' => 'Hintergrund',
            'container_class_default_container_padding' => 'Container Abstand',
            'container_class_container' => 'Breite',
        ],
        'description' => [
            'section_class_padding' => 'Abstand zu dem Sektionsrand verändern',
        ],
        'inputs' => [
            'section_class_padding' => [
                'mfragment_default' => 'default',
                '' => 'ohne',
                'uk-padding' => 'normal',
                'uk-padding-small' => 'small',
                'uk-padding-large' => 'large'
            ],
            'section_class_ukcolor' => [
                'mfragment_default' => 'default',
                '' => 'transparent',
                'bg-image' => 'BG-Image',
                'uk-section-default' => 'Standard',
                'uk-section-primary' => 'Primär',
                'uk-section-secondary' => 'Sekundär',
                'uk-section-muted' => 'Muted',
            ],
            'container_class_container' => [
                'mfragment_default' => 'default',
                'uk-container-xsmall' => 'xsmall',
                'uk-container-small' => 'small',
                'uk-container-large' => 'large',
                'uk-container-xlarge' => 'xlarge',
                'uk-container-expand' => 'expand (100%)'
            ],
            'container_class_default_container_padding' => [
                'mfragment_default' => 'default',
                '' => 'ohne',
                'uk-container' => 'Standard Container Abstand'
            ],
        ],
        'default_value' => [
            'section_class_padding' => 'mfragment_default',         // '', 'mfragment_default', 'uk-padding-small', ...
            'section_class_ukcolor' => 'mfragment_default',         // '', 'mfragment_default', 'uk-section-default', ...
            'container_class_default_container_padding' => 'mfragment_default',      // '', 'mfragment_default', 'uk-container'
            'container_class_container' => 'mfragment_default',     // '', 'mfragment_default', 'uk-container-xsmall', ...
        ]
    ];

    public function generateInputs(): \MForm
    {
        $MForm = new MForm();
        $sectionMForm = new MForm();
        $containerMForm = new MForm();

        if (!$this->config['show']['container_class_container']) {
            unset($this->config['inputs']['section_class_ukcolor']['bg-image']);
        }

        // SECTION SETUP
        if ($this->config['show']['section_class_padding'])
            $sectionMForm->addSelectField("{$this->config['id']}.section.class.padding", $this->config['inputs']['section_class_padding'], ['label' => $this->config['label']['section_class_padding']], 1, $this->config['default_value']['section_class_padding'])
                ->addDescription($this->config['description']['section_class_padding']);

        if ($this->config['show']['section_class_ukcolor']) {
            $sectionMForm->addSelectField("{$this->config['id']}.section.class.ukcolor", $this->config['inputs']['section_class_ukcolor'], ['label' => $this->config['label']['section_class_ukcolor']], 1, $this->config['default_value']['section_class_ukcolor']);
            $toggle = [];
            foreach ($this->config['inputs']['section_class_ukcolor'] as $key => $input) {
                if ($key == 'bg-image') {
                    $toggle[$key] = 'collapse_bg_image';
                } else {
                    $toggle[$key] = 'collapse_bg_none';
                }
            }
            $sectionMForm->setToggleOptions($toggle);
        }

        // show bg image
        if ($this->config['show']['container_bg_image']) {
            // add background img
            $backgroundImageMForm = MForm::factory()->addMediaField("{$this->config['id']}.section.bg_image", ['label' => 'Bild']);
            // get parallax input for bg img
            $backgroundImageMForm->addForm(MForm::factory()->addInputs('uikit/parallax_setup', new MFormInputsConfig($this->config['section_id']. '_bg', [
                'parallax_x' => false,
                'parallax_y' => false,
                'parallax_bgx' => false,
            ])));
            // add bg img parallax form
            $sectionMForm->addForm(MForm::factory()
                ->addCollapseElement('', $backgroundImageMForm, false, true, ['data-group-collapse-id' => 'collapse_bg_image'])
                ->addCollapseElement('', '', false, true, ['data-group-collapse-id' => 'collapse_bg_none'])
            );
        }

        // show container padding
        if ($this->config['show']['container_class_default_container_padding'])
            $containerMForm->addSelectField("{$this->config['id']}.section.class.default", $this->config['inputs']['container_class_default_container_padding'], ['label' => $this->config['label']['container_class_default_container_padding']], 1, $this->config['default_value']['container_class_default_container_padding']);

        // show container class select
        if ($this->config['show']['container_class_container'])
            $containerMForm->addSelectField("{$this->config['id']}.section.class.container", $this->config['inputs']['container_class_container'], ['label' => $this->config['label']['container_class_container']], 1, $this->config['default_value']['container_class_container']);

        // SETUP MFORM FORM
        if (count($sectionMForm->getItems()) > 0) $MForm->addFieldsetArea($this->config['fieldset']['section'], $sectionMForm);
        if (count($containerMForm->getItems()) > 0) $MForm->addFieldsetArea($this->config['fieldset']['container'], $containerMForm);

        if ($this->config['show']['section_parallax']) {
            // add parallax section form
            $MForm->addFieldsetArea($this->config['fieldset']['parallax'], MForm::factory()->addInputs('uikit/parallax_setup', new MFormInputsConfig($this->config['id'], [
                'parallax_x' => false,
                'parallax_bgx' => false,
                'parallax_bgy' => false,
            ])));
        }

        return $MForm;
    }

}
