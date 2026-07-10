<?php

namespace FriendsOfRedaxo\MForm\Utils;

use FriendsOfRedaxo\MForm\DTO\MFormItem;

class MFormFormGroupHelper
{
    public static function getExtraClass(MFormItem $item): string
    {
        $formGroupClass = $item->getAttributes()['form-group-class'] ?? '';
        if (!is_string($formGroupClass)) {
            return '';
        }

        return trim($formGroupClass);
    }

    /**
     * @return array<string, mixed>
     */
    public static function getAttributes(MFormItem $item): array
    {
        $formGroupAttributes = $item->getAttributes()['form-group-attributes'] ?? [];
        if (!is_array($formGroupAttributes)) {
            return [];
        }

        return $formGroupAttributes;
    }
}