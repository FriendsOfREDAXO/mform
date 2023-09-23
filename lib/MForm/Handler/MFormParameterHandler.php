<?php
/**
 * @author Joachim Doerr
 * @package redaxo5
 * @license MIT
 */

namespace MForm\Handler;

use MForm\DTO\MFormItem;

use function is_array;

class MFormParameterHandler
{
    /**
     * @param mixed $name
     * @param mixed $value
     * @author Joachim Doerr
     */
    public static function addParameter(MFormItem $item, $name, $value): void
    {
        switch ($name) {
            case 'label':
                $item->setLabel($value);
                break;
            case 'full':
                $item->setFull(true);
                break;
            default:
                $item->parameter[$name] = $value;
                break;
        }
    }

    /**
     * @author Joachim Doerr
     */
    public static function addParameters(MFormItem $item, array $parameter): void
    {
        // is parameter an array
        if (is_array($parameter)) {
            foreach ($parameter as $name => $value) {
                // set parameter by setParameter method
                self::addParameter($item, $name, $value);
            }
        }
    }
}
