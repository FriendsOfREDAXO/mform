<?php

namespace FriendsOfRedaxo\MForm\FieldType;

/**
 * Was ein registrierter Feldtyp zum Rendern braucht -- unabhaengig davon,
 * ob das Feld im klassischen Formular oder als Repeater-Template gerendert wird.
 */
final class FieldRenderContext
{
    public const MODE_FORM = 'form';
    public const MODE_REPEATER = 'repeater';

    /**
     * @param string $mode      MODE_FORM: Feld mit name/value ausgeben. MODE_REPEATER: Template-
     *                          Feld fuer den Flex-Repeater, Werte setzt das JS -- das Formular-
     *                          element braucht data-mfr-field="{fieldKey}" und keinen name.
     * @param string $name      Formularname (MODE_FORM), z.B. REX_INPUT_VALUE[1][0][rating]
     * @param string $id        Element-Id (MODE_FORM), leer im Repeater
     * @param string $fieldKey  Feldschluessel im Repeater (MODE_REPEATER), sonst leer
     * @param string $value     Aktueller Wert (bereits HTML-escaped), leer im Repeater
     * @param string $class     CSS-Klassen des Items (inkl. Standardklasse)
     * @param string $attributes Gerenderte Zusatzattribute (" data-x=\"1\" placeholder=\"..\"")
     */
    public function __construct(
        public readonly string $mode,
        public readonly string $name,
        public readonly string $id,
        public readonly string $fieldKey,
        public readonly string $value,
        public readonly string $class,
        public readonly string $attributes,
    ) {
    }

    public function isRepeater(): bool
    {
        return self::MODE_REPEATER === $this->mode;
    }

    /**
     * Die Attribute, die das eigentliche Formularelement in jedem Fall braucht:
     * im Formular name/id/value, im Repeater data-mfr-field. Fuer Renderer, die
     * ein einzelnes <input>/<select> ausgeben.
     */
    public function controlAttributes(): string
    {
        if ($this->isRepeater()) {
            return ' data-mfr-field="' . htmlspecialchars($this->fieldKey, ENT_QUOTES) . '"';
        }
        return ' name="' . htmlspecialchars($this->name, ENT_QUOTES) . '" id="' . htmlspecialchars($this->id, ENT_QUOTES) . '"';
    }
}
