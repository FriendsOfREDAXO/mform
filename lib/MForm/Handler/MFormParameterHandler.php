<?php
/**
 * @author Joachim Doerr
 * @package redaxo5
 * @license MIT
 */

namespace MForm\Handler;

use MForm\DTO\MFormItem;

class MFormParameterHandler
{
    public static function addParameter(MFormItem $item, mixed $name, mixed $value): void
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

    public static function addParameters(MFormItem $item, array $parameter): void
    {
        // is parameter an array
        foreach ($parameter as $name => $value) {
            // set parameter by setParameter method
            self::addParameter($item, $name, $value);
        }
    }
}
