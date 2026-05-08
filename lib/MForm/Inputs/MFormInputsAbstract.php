<?php
/**
 * @author Joachim Doerr
 * @package redaxo5
 * @license MIT
 */

namespace FriendsOfRedaxo\MForm\Inputs;

use FriendsOfRedaxo\MForm;
use FriendsOfRedaxo\MForm\Utils\MFormModuleHelper;

abstract class MFormInputsAbstract
{
    /** @var array<string, mixed> */
    protected array $config = [];
    protected MForm $mform;

    /**
     * @param array<string, mixed> $inputsConfig
     */
    public function __construct(MForm $mform, array $inputsConfig = [])
    {
        $this->mform = $mform;
        $this->mergeConfig($inputsConfig);
    }

    protected function getContentFrom(int|string|null $id = null): MForm
    {
        if (isset($this->config['contentMForm']) && $this->config['contentMForm'] instanceof MForm) {
            return $this->config['contentMForm']->setShowWrapper(false);
        } else {
            $contentForm = MForm::factory()->setShowWrapper(false)
                ->addInputs($id, 'bootstrap/card', $this->config);

            return $contentForm;
        }
    }

    protected function getConfigForm(int|string|null $id = null): MForm
    {
        $idPrefix = (null === $id) ? '' : (string) $id;
        $configForm = MForm::factory()->setShowWrapper(false);
        if (!isset($this->config['configKeys']) || !is_array($this->config['configKeys'])) return $configForm;
        $keys = $this->config['configKeys'];
        if (in_array('bgImg', $keys) && isset($this->config['bgImg']) && $this->config['bgImg'] === true) {
            $configForm->addMediaField($idPrefix . 'bgImg', ['preview' => 1], null, ['label' => $this->config['bgImgLabel']]);
            $keys = array_diff($keys, ['bgImg']);
        }
        if (in_array('bgClass', $keys) && isset($this->config['bgClass']) && is_array($this->config['bgClass']) && count($this->config['bgClass']) > 0) {
            $configForm->addSelectField($idPrefix . 'bgClass', $this->config['bgClass'], ['label' => $this->config['bgClassLabel']], 1, $this->config['bgClassDefaultValue']);
            $keys = array_diff($keys, ['bgClass']);
        }
        foreach ($keys as $key) {
            if (isset($this->config[$key]) && is_array($this->config[$key]) && [] !== $this->config[$key]) {

                if (isset($this->config[$key . 'Toggle']) && $this->config[$key . 'Toggle'] === true) {
                    $configForm->addToggleCheckboxField($idPrefix . $key . 'Custom', [1 => ('' !== (string) ($this->config[$key . 'ToggleLabel'] ?? '')) ? $this->config[$key . 'ToggleLabel'] : 'Custom "' . $key . '"'], ['label' => $this->config[$key . 'Label'], 'data-toggle-item' => 'collapse' . $key]);
                    $configForm->addForm(MForm::factory()
                        ->addCollapseElement('link', MForm::factory()
                            ->addRadioImgField($idPrefix . $key, $this->config[$key], ['label' => ''], $this->config[$key . 'DefaultValue'])
                            , false, true, ['data-group-collapse-id' => 'collapse' . $key]
                        )
                    );
                } else {
                    $configForm
                        ->addRadioImgField($idPrefix . $key, $this->config[$key], ['label' => $this->config[$key . 'Label']], $this->config[$key . 'DefaultValue'])
                    ;
                }
            }
        }
        return $configForm;
    }

    /**
     * @param array<string, mixed> $inputsConfig
     */
    protected function mergeConfig(array $inputsConfig = []): void
    {
        $this->config = MFormModuleHelper::mergeInputConfig($this->config, $inputsConfig, 0);
    }
}