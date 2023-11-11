<?php
/**
 * @author Joachim Doerr
 * @package redaxo5
 * @license MIT
 */

use MForm\Inputs\MFormInputsAbstract;
use MForm\Inputs\MFormInputsInterface;

class parallax_setup extends MFormInputsAbstract implements MFormInputsInterface
{
    // DEFAULT CONFIG
    protected array $config = [
        'id' => 1,
        'show' => [
            'parallax' => true,
            'parallax_sticky' => true,
            'parallax_easing' => true,
            'parallax_media' => true,
            'parallax_x' => true,
            'parallax_y' => true,
            'parallax_bgx' => true,
            'parallax_bgy' => true,
            'parallax_start' => true,
            'parallax_end' => true,
        ],
        'fieldset' => [
            'parallax' => 'Parallax Einstellung',
            'parallax_easing' => '',
        ],
        'label' => [
            'parallax_sticky' => 'Sticky',
            'parallax_easing' => 'Easing',
            'parallax_media' => 'Responsive Device (optional)',
            'parallax_x' => 'X-Animation [number]',
            'parallax_y' => 'Y-Animation [number]',
            'parallax_bgx' => 'BG-X-Animation [number]',
            'parallax_bgy' => 'BG-Y-Animation [number]',
            'parallax_start' => 'Parallax Start [number]',
            'parallax_end' => 'Parallax End [number]',
        ],
        'description' => [
            'parallax_sticky' => 'Aktiviert Top-Sticky-Effekt, deaktiviert Parallax Start und End'
        ],
        'inputs' => [
            'parallax' => [
                'mfragment_default' => 'default',
                '' => 'ohne',
                'true' => 'custom'
            ],
            'parallax_sticky' => [
                '' => 'ohne',
                'true' => 'Top-Sticky-Effekt',
            ],
            'parallax_easing' => [
                0 => '0',
                '0.5' => '0.5',
                '1' => '1',
                '2' => '2',
                '-0.5' => '-0.5',
                '-1' => '-1',
                '-2' => '-2',
            ],
            'parallax_media' => [
                '@s' => '@s small (640px)',
                '@m' => '@m medium (960px)',
                '@l' => '@l large (1200px)',
                '@xl' => '@xl extra large (1600px)'
            ],
        ],
        'default_value' => [
            'parallax' => 'mfragment_default',
            'parallax_sticky' => null,
            'parallax_easing' => 0,
            'parallax_media' => null,
            'parallax_x' => null,
            'parallax_y' => null,
            'parallax_bgx' => null,
            'parallax_bgy' => null,
            'parallax_start' => null,
            'parallax_end' => null,
        ]
    ];

    public function generateInputs(): MForm
    {
        if (!isset($this->config['show']['parallax']) || !$this->config['show']['parallax']) {
            return $this->mform;
        }

        // MFORMS
        $this->mform = new MForm();
        $parallaxMForm = new MForm();

        if ($this->config['show']['parallax_sticky'])
            $parallaxMForm->addSelectField("{$this->config['id']}.parallax-sticky", $this->config['inputs']['parallax_sticky'], ['label' => $this->config['label']['parallax_sticky']/*, 'data-toggle-item' => 'collapse_parallax_start_end' */], 1, $this->config['default_value']['parallax_sticky']);
        if ($this->config['show']['parallax_easing'])
            $parallaxMForm->addSelectField("{$this->config['id']}.uk-parallax.easing", $this->config['inputs']['parallax_easing'], ['label' => $this->config['label']['parallax_easing']], 1, $this->config['default_value']['parallax_easing']);
        if ($this->config['show']['parallax_media'])
            $parallaxMForm->addMultiSelectField("{$this->config['id']}.uk-parallax.media", $this->config['inputs']['parallax_media'], ['label' => $this->config['label']['parallax_media']], 1, $this->config['default_value']['parallax_media']);
        if ($this->config['show']['parallax_x'])
            $parallaxMForm->addTextField("{$this->config['id']}.uk-parallax.x", ['label' => $this->config['label']['parallax_x']], $this->config['default_value']['parallax_x']);
        if ($this->config['show']['parallax_y'])
            $parallaxMForm->addTextField("{$this->config['id']}.uk-parallax.y", ['label' => $this->config['label']['parallax_y']], $this->config['default_value']['parallax_y']);
        if ($this->config['show']['parallax_bgx'])
            $parallaxMForm->addTextField("{$this->config['id']}.uk-parallax.bgx", ['label' => $this->config['label']['parallax_bgx']], $this->config['default_value']['parallax_bgx']);
        if ($this->config['show']['parallax_bgy'])
            $parallaxMForm->addTextField("{$this->config['id']}.uk-parallax.bgy", ['label' => $this->config['label']['parallax_bgy']], $this->config['default_value']['parallax_bgy']);
        if ($this->config['show']['parallax_start'])
            $parallaxMForm->addTextField("{$this->config['id']}.uk-parallax.start", ['label' => $this->config['label']['parallax_start']], $this->config['default_value']['parallax_start']);
        if ($this->config['show']['parallax_end'])
            $parallaxMForm->addTextField("{$this->config['id']}.uk-parallax.end", ['label' => $this->config['label']['parallax_end']], $this->config['default_value']['parallax_end']);

        // TODO create mform reverse toggle for start and end collapse in case of sticky i will hide that area
        //$parallaxMForm
        //    ->addForm(MForm::factory()
        //        ->addCollapseElement('', MForm::factory()
        //            ->addTextField("{$this->config['id']}.uk-parallax.start", ['label' => 'Parallax Start [number]'], $this->config['default_value']['parallax_start'])
        //            ->addTextField("{$this->config['id']}.uk-parallax.end", ['label' => 'Parallax End [number]'], $this->config['default_value']['parallax_end'])
        //            , true, true, ['data-group-collapse-id' => 'collapse_parallax_start_end'])
        //    );

        if ($this->config['show']['parallax']) {
            $this->mform->addSelectField("{$this->config['id']}.parallax", $this->config['inputs']['parallax'], ['label' => 'Parallax'], 1, $this->config['default_value']['parallax'])
                ->setToggleOptions(['mfragment_default' => 'collapse_parallax_empty', 'true' => 'collapse_parallax', '' => 'collapse_parallax_empty']);
        }

        if (count($parallaxMForm->getItems())) {
            $this->mform->addForm(MForm::factory()->addCollapseElement('', $parallaxMForm, false, true, ['data-group-collapse-id' => 'collapse_parallax'])
                ->addCollapseElement('', '', false, true, ['data-group-collapse-id' => 'collapse_parallax_empty']));
        }

        return $this->mform;
    }
}
