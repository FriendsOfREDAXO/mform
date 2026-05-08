<?php

/**
 * @author MForm Contributors
 * @package redaxo5
 * @license MIT
 */

class rex_yform_value_color_swatch extends rex_yform_value_abstract
{
    public function enterObject(): void
    {
        $this->setValue((string) $this->getValue());

        if ('' === $this->getValue() && !$this->params['send']) {
            $this->setValue($this->getElement('default'));
        }

        $this->params['value_pool']['email'][$this->getName()] = $this->getValue();

        if ($this->saveInDb()) {
            $this->params['value_pool']['sql'][$this->getName()] = $this->getValue();
        }

        if (!$this->needsOutput()) {
            return;
        }

        $swatchesRaw = trim((string) $this->getElement('swatches'));
        $swatches = [];
        if ('' !== $swatchesRaw) {
            $decoded = json_decode($swatchesRaw, true);
            if (is_array($decoded)) {
                $swatches = $decoded;
            }
        }

        $this->params['form_output'][$this->getId()] = $this->parse(
            'value.color_swatch.tpl.php',
            compact('swatches'),
        );
    }

    /** @return array<string, mixed> */
    public function getDefinitions(): array
    {
        return [
            'type'        => 'value',
            'name'        => 'color_swatch',
            'values'      => [
                'name'     => ['type' => 'name', 'label' => rex_i18n::msg('yform_values_defaults_name')],
                'label'    => ['type' => 'text', 'label' => rex_i18n::msg('yform_values_defaults_label')],
                'swatches' => [
                    'type'   => 'textarea',
                    'label'  => 'Swatches (JSON)',
                    'notice' => 'JSON-Objekt: Schlüssel = Farbwert oder CSS-Klasse, Wert = Label oder {"label":"...","preview":"#hex"}. '
                              . 'Beispiel: {"#2f77bc":"Blau","#e74c3c":"Rot",".bg-primary":{"label":"Primär","preview":"#2f77bc"}}',
                ],
                'default'  => ['type' => 'text',    'label' => rex_i18n::msg('yform_values_text_default')],
                'no_db'    => ['type' => 'no_db',   'label' => rex_i18n::msg('yform_values_defaults_table'), 'default' => 0],
                'notice'   => ['type' => 'text',    'label' => rex_i18n::msg('yform_values_defaults_notice')],
            ],
            'description' => 'Farbwähler mit Vorschau-Input und Swatches-Popup',
            'db_type'     => ['varchar(191)', 'text'],
            'famous'      => false,
        ];
    }

    /** @param array<string, mixed> $params */
    public static function getListValue(array $params): string
    {
        $value = (string) ($params['value'] ?? '');
        if ('' === $value) {
            return '-';
        }

        if (str_starts_with($value, '.')) {
            return '<span style="font-family:monospace">' . rex_escape($value) . '</span>';
        }

        $hex = rex_escape($value);
        return '<span style="display:inline-flex;align-items:center;gap:6px">'
            . '<span style="display:inline-block;width:14px;height:14px;border-radius:2px;background:' . $hex . ';border:1px solid rgba(0,0,0,.2)"></span>'
            . $hex
            . '</span>';
    }
}
