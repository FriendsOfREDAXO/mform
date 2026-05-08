<?php

/**
 * Custom Linklist widget for MForm.
 *
 * Keeps REDAXO storage format (comma-separated article ids),
 * but provides a modern list UI that also works in repeater contexts.
 */
class rex_var_custom_linklist extends rex_var
{
    /**
     * REX_CUSTOM_LINKLIST[id=1]
     * REX_CUSTOM_LINKLIST[id=1 widget=1]
     * REX_CUSTOM_LINKLIST[id=1 widget=1 category=5]
     *
     * Storage: same linklist column as REX_LINKLIST[id=1].
     */
    protected function getOutput(): bool|string
    {
        $id = $this->getArg('id', 0, true);
        if (!in_array($this->getContext(), ['module', 'action'], true) || !is_numeric($id) || $id < 1 || $id > 10) {
            return false;
        }

        $value = $this->getContextData()->getValue('linklist' . $id);

        if ($this->hasArg('isset') && $this->getArg('isset')) {
            return $value ? 'true' : 'false';
        }

        if ($this->hasArg('widget') && $this->getArg('widget')) {
            if (!$this->environmentIs(self::ENV_INPUT)) {
                return false;
            }
            $args = [];
            foreach (['category', 'toolbar'] as $key) {
                if ($this->hasArg($key)) {
                    $args[$key] = $this->getArg($key);
                }
            }
            $value = self::getWidget($id, 'REX_INPUT_LINKLIST[' . $id . ']', (string) $value, $args);
        }

        return self::quote($value);
    }

    /**
     * @param array<string, mixed> $args
     */
    public static function getWidget(int|string $id, string $name, string $value, array $args = []): string
    {
        $category = rex_category::getCurrent() ? rex_category::getCurrent()->getId() : 0;
        if (isset($args['category'])) {
            $category = (int) $args['category'];
        }

        $openParams = '&clang=' . rex_clang::getCurrentId() . '&category_id=' . $category;

        $values = [];
        if ('' !== trim($value)) {
            foreach (explode(',', $value) as $link) {
                $link = trim($link);
                if ('' !== $link) {
                    $values[] = $link;
                }
            }
        }

        $options = '';
        foreach ($values as $linkId) {
            $label = $linkId;
            if ($article = rex_article::get((int) $linkId)) {
                $label = trim(sprintf('%s [%s]', $article->getName(), $article->getId()));
            }
            $options .= '<option value="' . rex_escape($linkId) . '">' . rex_escape($label) . '</option>';
        }

        $disabled = '';
        $disabledClass = '';
        $disabledAria = '';
        if (!rex::requireUser()->getComplexPerm('structure')->hasStructurePerm()) {
            $disabled = ' disabled';
            $disabledClass = ' is-disabled';
            $disabledAria = ' aria-disabled="true"';
        }

        $toolbarOrientation = 'horizontal';
        if (isset($args['toolbar']) && is_string($args['toolbar'])) {
            $candidate = strtolower(trim($args['toolbar']));
            if (in_array($candidate, ['horizontal', 'vertical'], true)) {
                $toolbarOrientation = $candidate;
            }
        }

        $id = (string) $id;

        return '<div class="rex-js-widget mform-list-widget mform-list-widget-linklist" data-widget-type="linklist" data-widget-id="' . rex_escape($id) . '" data-toolbar="' . rex_escape($toolbarOrientation) . '" data-params="' . rex_escape($openParams) . '">' 
            . '<div class="mform-list-shell">'
            . '<ul class="mform-list-items" data-empty="' . rex_escape(rex_i18n::msg('mform_widget_empty_entries')) . '"></ul>'
            . '<select class="form-control mform-list-select" name="REX_LINKLIST_SELECT[' . rex_escape($id) . ']" id="REX_LINKLIST_SELECT_' . rex_escape($id) . '" size="10">' . $options . '</select>'
            . '<input type="hidden" class="mform-list-value" name="' . rex_escape($name) . '" id="REX_LINKLIST_' . rex_escape($id) . '" value="' . rex_escape((string) $value) . '">'
            . '</div>'
            . '<div class="mform-list-toolbar">'
            . '<button type="button" class="btn btn-popup mform-list-btn' . $disabledClass . '" data-action="open" title="' . rex_i18n::msg('var_link_open') . '"' . $disabled . $disabledAria . '><i class="rex-icon rex-icon-open-linkmap"></i></button>'
            . '<button type="button" class="btn btn-popup mform-list-btn" data-action="up" title="' . rex_i18n::msg('var_linklist_move_up') . '"><i class="rex-icon rex-icon-up"></i></button>'
            . '<button type="button" class="btn btn-popup mform-list-btn" data-action="down" title="' . rex_i18n::msg('var_linklist_move_down') . '"><i class="rex-icon rex-icon-down"></i></button>'
            . '<button type="button" class="btn btn-popup mform-list-btn' . $disabledClass . '" data-action="delete" title="' . rex_i18n::msg('var_link_delete') . '"' . $disabled . $disabledAria . '><i class="rex-icon rex-icon-delete-link"></i></button>'
            . '</div>'
            . '</div>';
    }
}
