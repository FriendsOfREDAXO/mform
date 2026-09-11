<?php

namespace FriendsOfRedaxo\MForm\FieldType;

use FriendsOfRedaxo\MForm\DTO\MFormItem;

/**
 * Eigener Feldtyp fuer MForm (Registrierung ueber MForm::registerFieldType()).
 *
 * render() liefert nur das Formularelement (ohne Label und form-group);
 * Label, Notice und Wrapper baut MForm in beiden Renderpfaden selbst.
 */
interface FieldTypeInterface
{
    public function render(MFormItem $item, FieldRenderContext $context): string;
}
