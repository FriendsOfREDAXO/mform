<?php

namespace FriendsOfRedaxo\MForm\Output;

use FriendsOfRedaxo\MForm\Content\MFormContentBlocks;
use FriendsOfRedaxo\MForm\Repeater\MFormRepeaterHelper;
use rex_url;

final class MFormContentBlocksOutput
{
    /** @var array<string, array<string, callable(array<string, mixed>): string>> */
    private static array $customRenderers = [];

    /** @var array<int, array<string, mixed>> */
    private array $items;

    /**
     * Registriert einen Renderer fuer einen Blocktyp und ein Framework.
     *
     * @param callable(array<string, mixed>): string $renderer
     */
    public static function registerRenderer(string $framework, string $blockType, callable $renderer): void
    {
        $framework = strtolower(trim($framework));
        $blockType = trim($blockType);

        if ('' === $framework || '' === $blockType) {
            throw new \InvalidArgumentException('Framework and block type must not be empty.');
        }

        if (!isset(self::$customRenderers[$framework])) {
            self::$customRenderers[$framework] = [];
        }

        self::$customRenderers[$framework][$blockType] = $renderer;
    }

    public static function unregisterRenderer(string $framework, string $blockType): void
    {
        $framework = strtolower(trim($framework));
        unset(self::$customRenderers[$framework][$blockType]);
    }

    /**
     * @param array<int, array<string, mixed>> $items
     */
    private function __construct(array $items)
    {
        $this->items = $items;
    }

    /**
     * @param int|string|array<int, array<string, mixed>> $source
     */
    public static function from(int|string|array $source): self
    {
        if (is_int($source) || is_string($source)) {
            return new self(MFormRepeaterHelper::decode($source));
        }

        return new self($source);
    }

    public function renderBootstrap5(): string
    {
        return $this->render('bootstrap5');
    }

    public function renderUIKit3(): string
    {
        return $this->render('uikit3');
    }

    public function renderBulma(): string
    {
        return $this->render('bulma');
    }

    private function render(string $framework): string
    {
        $html = '';

        foreach ($this->items as $item) {
            $type = isset($item['block_type']) && is_string($item['block_type']) ? $item['block_type'] : '';

            $customHtml = $this->renderCustomBlock($framework, $type, $item);
            if (null !== $customHtml) {
                $html .= $customHtml;
                continue;
            }

            if (MFormContentBlocks::BLOCK_HEADLINE === $type) {
                $html .= $this->renderHeadline($item, $framework);
                continue;
            }

            if (MFormContentBlocks::BLOCK_TEXT === $type) {
                $html .= $this->renderText($item, $framework);
                continue;
            }

            if (MFormContentBlocks::BLOCK_TEXT_IMAGE === $type) {
                $html .= $this->renderTextImage($item, $framework);
                continue;
            }
        }

        return $html;
    }

    /**
     * @param array<string, mixed> $item
     */
    private function renderCustomBlock(string $framework, string $type, array $item): ?string
    {
        $framework = strtolower($framework);

        if (!isset(self::$customRenderers[$framework][$type])) {
            return null;
        }

        $html = self::$customRenderers[$framework][$type]($item);

        return $html;
    }

    /**
     * @param array<string, mixed> $item
     */
    private function renderHeadline(array $item, string $framework): string
    {
        $headline = isset($item['headline']) && is_string($item['headline']) ? trim($item['headline']) : '';
        if ('' === $headline) {
            return '';
        }

        $tag = isset($item['headline_tag']) && is_string($item['headline_tag']) ? strtolower($item['headline_tag']) : 'h2';
        if (!in_array($tag, ['h1', 'h2', 'h3', 'h4', 'h5', 'h6'], true)) {
            $tag = 'h2';
        }

        $class = '';
        if ('bootstrap5' === $framework) {
            $class = ' class="mb-4"';
        } elseif ('uikit3' === $framework) {
            $class = ' class="uk-margin-medium-bottom"';
        } elseif ('bulma' === $framework) {
            $class = ' class="mb-5"';
        }

        return '<section' . $class . '><' . $tag . '>' . rex_escape($headline) . '</' . $tag . '></section>';
    }

    /**
     * @param array<string, mixed> $item
     */
    private function renderText(array $item, string $framework): string
    {
        $title = isset($item['title']) && is_string($item['title']) ? trim($item['title']) : '';
        $text = isset($item['text']) && is_string($item['text']) ? $item['text'] : '';

        if ('' === $title && '' === trim(strip_tags($text))) {
            return '';
        }

        if ('bootstrap5' === $framework) {
            $titleHtml = '' !== $title ? '<h3 class="h4 mb-3">' . rex_escape($title) . '</h3>' : '';
            return '<section class="mb-4">' . $titleHtml . MFormOutput::richtext($text) . '</section>';
        }

        if ('uikit3' === $framework) {
            $titleHtml = '' !== $title ? '<h3 class="uk-h4 uk-margin-small-bottom">' . rex_escape($title) . '</h3>' : '';
            return '<section class="uk-margin-medium-bottom">' . $titleHtml . MFormOutput::richtext($text) . '</section>';
        }

        $titleHtml = '' !== $title ? '<h3 class="title is-4">' . rex_escape($title) . '</h3>' : '';
        return '<section class="mb-5">' . $titleHtml . '<div class="content">' . MFormOutput::richtext($text) . '</div></section>';
    }

    /**
     * @param array<string, mixed> $item
     */
    private function renderTextImage(array $item, string $framework): string
    {
        $title = isset($item['title']) && is_string($item['title']) ? trim($item['title']) : '';
        $text = isset($item['text']) && is_string($item['text']) ? $item['text'] : '';
        $position = isset($item['image_position']) && is_string($item['image_position']) ? $item['image_position'] : 'left';
        $image = $this->resolveImageFilename($item['image'] ?? '');
        $imageAlt = isset($item['image_alt']) && is_string($item['image_alt']) ? trim($item['image_alt']) : '';

        if ('' === $title && '' === trim(strip_tags($text)) && '' === $image) {
            return '';
        }

        if ('bootstrap5' === $framework) {
            return $this->renderTextImageBootstrap($title, $text, $image, $imageAlt, $position);
        }

        if ('uikit3' === $framework) {
            return $this->renderTextImageUIKit($title, $text, $image, $imageAlt, $position);
        }

        return $this->renderTextImageBulma($title, $text, $image, $imageAlt, $position);
    }

    private function renderTextImageBootstrap(string $title, string $text, string $image, string $imageAlt, string $position): string
    {
        $imageColClass = 'col-md-5';
        $textColClass = 'col-md-7';

        if ('right' === $position) {
            $imageColClass .= ' order-md-2';
            $textColClass .= ' order-md-1';
        }

        $imageHtml = '';
        if ('' !== $image) {
            $imageHtml = '<div class="' . $imageColClass . '"><img class="img-fluid rounded" src="' . rex_escape(rex_url::media($image)) . '" alt="' . rex_escape($imageAlt) . '"></div>';
        }

        $titleHtml = '' !== $title ? '<h3 class="h4 mb-3">' . rex_escape($title) . '</h3>' : '';
        $textHtml = '<div class="' . $textColClass . '">' . $titleHtml . MFormOutput::richtext($text) . '</div>';

        return '<section class="mb-5"><div class="row g-4 align-items-start">' . $imageHtml . $textHtml . '</div></section>';
    }

    private function renderTextImageUIKit(string $title, string $text, string $image, string $imageAlt, string $position): string
    {
        $imageOrder = 'left' === $position ? '' : ' uk-flex-last@m';

        $imageHtml = '';
        if ('' !== $image) {
            $imageHtml = '<div class="uk-width-2-5@m' . $imageOrder . '"><img src="' . rex_escape(rex_url::media($image)) . '" alt="' . rex_escape($imageAlt) . '" loading="lazy"></div>';
        }

        $titleHtml = '' !== $title ? '<h3 class="uk-h4 uk-margin-small-bottom">' . rex_escape($title) . '</h3>' : '';
        $textHtml = '<div class="uk-width-expand">' . $titleHtml . MFormOutput::richtext($text) . '</div>';

        return '<section class="uk-margin-large-bottom"><div class="uk-grid-medium uk-flex-top" uk-grid>' . $imageHtml . $textHtml . '</div></section>';
    }

    private function renderTextImageBulma(string $title, string $text, string $image, string $imageAlt, string $position): string
    {
        $imageClass = 'column is-5';
        $textClass = 'column is-7';

        if ('right' === $position) {
            $imageClass .= ' is-order-2-desktop';
            $textClass .= ' is-order-1-desktop';
        }

        $imageHtml = '';
        if ('' !== $image) {
            $imageHtml = '<div class="' . $imageClass . '"><figure class="image"><img src="' . rex_escape(rex_url::media($image)) . '" alt="' . rex_escape($imageAlt) . '"></figure></div>';
        }

        $titleHtml = '' !== $title ? '<h3 class="title is-4">' . rex_escape($title) . '</h3>' : '';
        $textHtml = '<div class="' . $textClass . '">' . $titleHtml . '<div class="content">' . MFormOutput::richtext($text) . '</div></div>';

        return '<section class="mb-6"><div class="columns is-variable is-5">' . $imageHtml . $textHtml . '</div></section>';
    }

    private function resolveImageFilename(mixed $value): string
    {
        if (is_string($value)) {
            return trim($value);
        }

        if (!is_array($value)) {
            return '';
        }

        if (isset($value['link']) && is_string($value['link'])) {
            return trim($value['link']);
        }

        if (isset($value['id']) && is_string($value['id'])) {
            return trim($value['id']);
        }

        return '';
    }
}
