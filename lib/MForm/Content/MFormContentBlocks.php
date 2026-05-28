<?php

namespace FriendsOfRedaxo\MForm\Content;

use FriendsOfRedaxo\MForm;

final class MFormContentBlocks
{
    public const BLOCK_HEADLINE = 'headline';
    public const BLOCK_TEXT = 'text';
    public const BLOCK_TEXT_IMAGE = 'text_image';

    /**
     * @param array<string, mixed> $options
     */
    public static function createForm(array $options = []): MForm
    {
        $tinyProfile = 'default';
        if (isset($options['tiny_profile']) && is_string($options['tiny_profile']) && '' !== trim($options['tiny_profile'])) {
            $tinyProfile = trim($options['tiny_profile']);
        }

        $typeLabel = 'Blocktyp';
        if (isset($options['type_label']) && is_string($options['type_label']) && '' !== trim($options['type_label'])) {
            $typeLabel = trim($options['type_label']);
        }

        $blockTypes = [
            self::BLOCK_HEADLINE => 'Ueberschrift',
            self::BLOCK_TEXT => 'Text',
            self::BLOCK_TEXT_IMAGE => 'Text und Bild',
        ];

        return MForm::factory()
            ->addSelectField('block_type', $blockTypes, ['label' => $typeLabel])
            ->addConditionalFieldsetArea('block_type', '=', self::BLOCK_HEADLINE, 'Ueberschrift', MForm::factory()
                ->addTextField('headline', ['label' => 'Ueberschrift'])
                ->addSelectField('headline_tag', [
                    'h1' => 'H1',
                    'h2' => 'H2',
                    'h3' => 'H3',
                    'h4' => 'H4',
                    'h5' => 'H5',
                    'h6' => 'H6',
                ], ['label' => 'HTML-Tag'], 1, 'h2')
            )
            ->addConditionalFieldsetArea('block_type', '=', self::BLOCK_TEXT, 'Text', MForm::factory()
                ->addTextField('title', ['label' => 'Titel'])
                ->addTextAreaField('text', [
                    'label' => 'Text',
                    'class' => 'tiny-editor',
                    'data-profile' => $tinyProfile,
                ])
            )
            ->addConditionalFieldsetArea('block_type', '=', self::BLOCK_TEXT_IMAGE, 'Text und Bild', MForm::factory()
                ->addTextField('title', ['label' => 'Titel'])
                ->addMFormMediaField('image', null, null, ['label' => 'Bild'])
                ->addTextField('image_alt', ['label' => 'Alt-Text'])
                ->addSelectField('image_position', [
                    'left' => 'Bild links',
                    'right' => 'Bild rechts',
                ], ['label' => 'Bildposition'], 1, 'left')
                ->addTextAreaField('text', [
                    'label' => 'Text',
                    'class' => 'tiny-editor',
                    'data-profile' => $tinyProfile,
                ])
            );
    }

    /**
     * @param array<string, mixed> $options
     * @return array<string, mixed>
     */
    public static function normalizeRepeaterOptions(array $options): array
    {
        $repeaterOptions = $options;

        unset(
            $repeaterOptions['tiny_profile'],
            $repeaterOptions['type_label']
        );

        if (!isset($repeaterOptions['btn_text'])) {
            $repeaterOptions['btn_text'] = 'Block hinzufuegen';
        }

        if (!isset($repeaterOptions['confirm_delete_msg'])) {
            $repeaterOptions['confirm_delete_msg'] = 'Diesen Block wirklich entfernen?';
        }

        if (!isset($repeaterOptions['collapsed'])) {
            $repeaterOptions['collapsed'] = false;
        }

        if (!isset($repeaterOptions['copy_paste'])) {
            $repeaterOptions['copy_paste'] = true;
        }

        if (!isset($repeaterOptions['confirm_delete'])) {
            $repeaterOptions['confirm_delete'] = true;
        }

        return $repeaterOptions;
    }
}
