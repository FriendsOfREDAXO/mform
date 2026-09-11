<?php
/**
 * Eigener Feldtyp ueber die Field-Type-Registry (ab 10.0, #399).
 * In echten Addons gehoert MForm::registerFieldType() in die boot.php.
 */
use FriendsOfRedaxo\MForm;
use FriendsOfRedaxo\MForm\DTO\MFormItem;
use FriendsOfRedaxo\MForm\FieldType\FieldRenderContext;
use FriendsOfRedaxo\MForm\FieldType\FieldTypeInterface;

// Eigener Feldtyp "rating" über die Registry (in echten Addons in boot.php registrieren)
if (!MForm::hasFieldType('rating')) {
    MForm::registerFieldType('rating', new class implements FieldTypeInterface {
        public function render(MFormItem $item, FieldRenderContext $context): string
        {
            $max = (int) ($item->getAttributes()['max'] ?? 5) ?: 5;
            $html = '<select class="form-control ' . htmlspecialchars($context->class) . '"' . $context->controlAttributes() . '>';
            for ($i = 0; $i <= $max; ++$i) {
                $selected = (!$context->isRepeater() && (string) $i === $context->value) ? ' selected' : '';
                $html .= '<option value="' . $i . '"' . $selected . '>' . str_repeat('★', $i) . str_repeat('☆', $max - $i) . ' (' . $i . ')</option>';
            }
            return $html . '</select>';
        }
    });
}

echo MForm::factory()
    ->addCustomField('rating', '1.0.rating', ['label' => 'Bewertung', 'max' => 5, 'notice' => 'Eigener Feldtyp: derselbe Renderer im Formular und im Repeater'], '3')
    ->addRepeaterElement(2, MForm::factory()
        ->addTextField('title', ['label' => 'Titel'])
        ->addCustomField('rating', 'rating', ['label' => 'Bewertung', 'max' => 5])
    , true, true, ['label' => 'Bewertungen', 'btn_text' => 'Bewertung hinzufuegen', 'show_add_button' => false])
    ->show();
