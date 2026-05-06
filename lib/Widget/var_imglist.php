<?php

/**
 * REX_MEDIALIST[1].
 *
 * Attribute:
 *   - category  => Kategorie in die beim oeffnen des Medienpools gesprungen werden soll
 *   - types     => Filter für Dateiendungen die im Medienpool zur Auswahl stehen sollen
 *   - preview   => Bei Bildertypen ein Vorschaubild einblenden
 *
 * @package redaxo\mediapool
 */
class rex_var_imglist extends rex_var
{
    protected function getOutput()
    {
        $id = $this->getArg('id', 0, true);
        if (!in_array($this->getContext(), ['module', 'action']) || !is_numeric($id) || $id < 1 || $id > 10) {
            return false;
        }

        $value = $this->getContextData()->getValue('medialist' . $id);

        if ($this->hasArg('isset') && $this->getArg('isset')) {
            return $value ? 'true' : 'false';
        }

        if ($this->hasArg('widget') && $this->getArg('widget')) {
            if (!$this->environmentIs(self::ENV_INPUT)) {
                return false;
            }
            $args = [];
            foreach (['category', 'preview', 'types'] as $key) {
                if ($this->hasArg($key)) {
                    $args[$key] = $this->getArg($key);
                }
            }
            $value = self::getWidget($id, 'REX_INPUT_MEDIALIST[' . $id . ']', (string) $value, $args);
        }

        return self::quote($value);
    }

    /**
     * @param int|string $id
     * @param array<string, mixed> $args
     */
    public static function getWidget($id, int|string|null $name, int|string|null $value, array $args = []): string
    {
        $name = (string) $name;
        $value = (string) $value;

        $id = str_replace(['][', '[', ']'], '', (string) $id);

        $wrapperArgs = $args;
        if (!isset($wrapperArgs['view'])) {
            $wrapperArgs['view'] = 'gallery';
        }
        if (!isset($wrapperArgs['views'])) {
            $wrapperArgs['views'] = 'gallery,grid,list';
        }
        if (!isset($wrapperArgs['toolbar'])) {
            $wrapperArgs['toolbar'] = 'vertical';
        }

        $html = rex_var_custom_medialist::getWidget($id, $name, $value, $wrapperArgs);

        return str_replace(
            'mform-list-widget mform-list-widget-medialist',
            'mform-list-widget mform-list-widget-medialist rex-js-widget-imglist',
            $html,
        );
    }
}
