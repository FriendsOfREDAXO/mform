<?php

namespace FriendsOfRedaxo\MForm\Content;

use FriendsOfRedaxo\MForm;

final class MFormContentBlocks
{
    public const BLOCK_HEADLINE = 'headline';
    public const BLOCK_TEXT = 'text';
    public const BLOCK_TEXT_IMAGE = 'text_image';

    /** @var array<string, array{label: string, form_factory: callable(array<string, mixed>): MForm}> */
    private static array $blockDefinitions = [];

    private static bool $defaultsRegistered = false;

    /**
     * Registriert einen eigenen Blocktyp fuer den Content-Blocks-Builder.
     *
     * @param callable(array<string, mixed>): MForm $formFactory
     */
    public static function registerBlock(string $type, string $label, callable $formFactory): void
    {
        $type = trim($type);
        if ('' === $type) {
            throw new \InvalidArgumentException('Block type must not be empty.');
        }

        self::$blockDefinitions[$type] = [
            'label' => $label,
            'form_factory' => $formFactory,
        ];
    }

    public static function unregisterBlock(string $type): void
    {
        unset(self::$blockDefinitions[$type]);
    }

    public static function hasBlock(string $type): bool
    {
        self::registerDefaultBlocks();
        return isset(self::$blockDefinitions[$type]);
    }

    /**
     * @return array<string, string>
     */
    public static function getBlockTypeOptions(): array
    {
        self::registerDefaultBlocks();

        $options = [];
        foreach (self::$blockDefinitions as $type => $definition) {
            $options[$type] = $definition['label'];
        }

        return $options;
    }

    /**
     * @param array<string, mixed> $options
     */
    public static function createForm(array $options = []): MForm
    {
        self::registerDefaultBlocks();

        $typeLabel = 'Blocktyp';
        if (isset($options['type_label']) && is_string($options['type_label']) && '' !== trim($options['type_label'])) {
            $typeLabel = trim($options['type_label']);
        }

        $form = MForm::factory()->addSelectField('block_type', self::getBlockTypeOptions(), [
            'label' => $typeLabel,
            'data-mform-content-block-selector' => '1',
        ]);

        foreach (self::$blockDefinitions as $type => $definition) {
            $subForm = ($definition['form_factory'])($options);
            $form->addFieldsetArea($definition['label'], $subForm, [
                'class' => 'mform-content-block-pane',
                'data-mform-content-block-type' => $type,
            ]);
        }

        return $form;
    }

    private static function registerDefaultBlocks(): void
    {
        if (self::$defaultsRegistered) {
            return;
        }
        self::$defaultsRegistered = true;

        if (!isset(self::$blockDefinitions[self::BLOCK_HEADLINE])) {
            self::registerBlock(self::BLOCK_HEADLINE, 'Ueberschrift', static function (array $options): MForm {
                return MForm::factory()
                    ->addTextField('headline', ['label' => 'Ueberschrift'])
                    ->addSelectField('headline_tag', [
                        'h1' => 'H1',
                        'h2' => 'H2',
                        'h3' => 'H3',
                        'h4' => 'H4',
                        'h5' => 'H5',
                        'h6' => 'H6',
                    ], ['label' => 'HTML-Tag'], 1, 'h2');
            });
        }

        if (!isset(self::$blockDefinitions[self::BLOCK_TEXT])) {
            self::registerBlock(self::BLOCK_TEXT, 'Text', static function (array $options): MForm {
                return MForm::factory()
                    ->addTextField('title', ['label' => 'Titel'])
                    ->addTextAreaField('text', [
                        'label' => 'Text',
                        'class' => 'mform-lazy-tiny-editor',
                        'data-profile' => self::resolveTinyProfile($options),
                    ]);
            });
        }

        if (!isset(self::$blockDefinitions[self::BLOCK_TEXT_IMAGE])) {
            self::registerBlock(self::BLOCK_TEXT_IMAGE, 'Text und Bild', static function (array $options): MForm {
                return MForm::factory()
                    ->addTextField('title', ['label' => 'Titel'])
                    ->addMFormMediaField('image', null, null, ['label' => 'Bild'])
                    ->addTextField('image_alt', ['label' => 'Alt-Text'])
                    ->addSelectField('image_position', [
                        'left' => 'Bild links',
                        'right' => 'Bild rechts',
                    ], ['label' => 'Bildposition'], 1, 'left')
                    ->addTextAreaField('text', [
                        'label' => 'Text',
                        'class' => 'mform-lazy-tiny-editor',
                        'data-profile' => self::resolveTinyProfile($options),
                    ]);
            });
        }
    }

    /** @param array<string, mixed> $options */
    private static function resolveTinyProfile(array $options): string
    {
        if (isset($options['tiny_profile']) && is_string($options['tiny_profile']) && '' !== trim($options['tiny_profile'])) {
            return trim($options['tiny_profile']);
        }

        return 'default';
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
