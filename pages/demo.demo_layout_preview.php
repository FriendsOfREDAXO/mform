<?php
/**
 * Demo: HtmlToSvgConverter + LayoutPreviewBuilder.
 *
 * @author Friends Of REDAXO
 * @package redaxo5
 * @license MIT
 */

use FriendsOfRedaxo\MForm\Utils\HtmlToSvgConverter;
use FriendsOfRedaxo\MForm\Utils\LayoutPreviewBuilder;

$fragment = new rex_fragment();
$fragment->setVar('title', rex_i18n::msg('mform_info'), false);
$fragment->setVar('body', '<p>' . rex_i18n::msg('mform_example_description_layout_preview') . '</p>', false);
echo $fragment->parse('core/page/section.php');

/**
 * Renders a single demo block: title + code + preview.
 */
$renderBlock = static function (string $title, string $code, string $preview): void {
    $body = '<div class="row">'
        . '<div class="col-md-7"><pre><code class="language-php">' . rex_escape($code) . '</code></pre></div>'
        . '<div class="col-md-5">' . $preview . '</div>'
        . '</div>';

    $fragment = new rex_fragment();
    $fragment->setVar('title', $title, false);
    $fragment->setVar('body', $body, false);
    echo $fragment->parse('core/page/section.php');
};

// =====================================================================
// LayoutPreviewBuilder
// =====================================================================

// Beispiel 1: Drei gleiche Spalten
$code1 = <<<'PHP'
$builder = new LayoutPreviewBuilder();
$builder->setAspectRatio('3:1')
    ->addSection()
        ->addColumn('1/3')->addElement('image', 'left')
        ->addColumn('1/3')->addElement('image', 'left')
        ->addColumn('1/3')->addElement('image', 'left');

echo '<img src="' . $builder->render() . '" alt="Layout">';
PHP;

$builder1 = new LayoutPreviewBuilder();
$builder1->setAspectRatio('3:1')
    ->addSection()
        ->addColumn('1/3')->addElement('image', 'left')
        ->addColumn('1/3')->addElement('image', 'left')
        ->addColumn('1/3')->addElement('image', 'left');
$preview1 = '<img src="' . rex_escape($builder1->render(), 'html_attr') . '" alt="Layout 3 Spalten" style="max-width:100%;border:1px solid #ddd;border-radius:4px;">';
$renderBlock('LayoutPreviewBuilder – 3 gleiche Spalten', $code1, $preview1);

// Beispiel 2: 2/3 + 1/3 mit Text & Bild
$code2 = <<<'PHP'
$builder = new LayoutPreviewBuilder();
$builder->setAspectRatio('2:1')
    ->addSection()
        ->addColumn('2/3')->addElement('text', 'left', '4:1', ['description' => 'Headline + Text'])
        ->addColumn('1/3')->addElement('image', 'left', '1:1');

echo '<img src="' . $builder->render() . '">';
PHP;

$builder2 = new LayoutPreviewBuilder();
$builder2->setAspectRatio('2:1')
    ->addSection()
        ->addColumn('2/3')->addElement('text', 'left', '4:1', ['description' => 'Headline + Text'])
        ->addColumn('1/3')->addElement('image', 'left', '1:1');
$preview2 = '<img src="' . rex_escape($builder2->render(), 'html_attr') . '" alt="Layout 2/3 + 1/3" style="max-width:100%;border:1px solid #ddd;border-radius:4px;">';
$renderBlock('LayoutPreviewBuilder – 2/3 Text + 1/3 Bild', $code2, $preview2);

// Beispiel 3: Verschachtelte Section
$code3 = <<<'PHP'
$builder = new LayoutPreviewBuilder();
$builder->setAspectRatio('2:1')
    ->addSection()
        ->addColumn('1/2')->addElement('image', 'left')
        ->addColumn('1/2')
            ->startNestedSection()
                ->addColumn('1/2')->addElement('text', 'left', '2:1')
                ->addColumn('1/2')->addElement('text', 'left', '2:1')
            ->endNestedSection();

echo '<img src="' . $builder->render() . '">';
PHP;

$builder3 = new LayoutPreviewBuilder();
$builder3->setAspectRatio('2:1')
    ->addSection()
        ->addColumn('1/2')->addElement('image', 'left')
        ->addColumn('1/2')
            ->startNestedSection()
                ->addColumn('1/2')->addElement('text', 'left', '2:1')
                ->addColumn('1/2')->addElement('text', 'left', '2:1')
            ->endNestedSection();
$preview3 = '<img src="' . rex_escape($builder3->render(), 'html_attr') . '" alt="Layout verschachtelt" style="max-width:100%;border:1px solid #ddd;border-radius:4px;">';
$renderBlock('LayoutPreviewBuilder – Verschachtelte Section', $code3, $preview3);

// =====================================================================
// HtmlToSvgConverter
// =====================================================================

// Beispiel 4: Einfaches Rect + Text
$code4 = <<<'PHP'
$converter = new HtmlToSvgConverter();
$svg = $converter->convertToImgTag('
    <rect x="10" y="10" width="780" height="580" fill="#1d4ed8" rx="20"/>
    <text x="400" y="320" text-anchor="middle" fill="white" style="font-size:80px">MForm</text>
', ['viewBox' => '0 0 800 600', 'class' => 'demo-svg']);

echo $svg;
PHP;

$converter4 = new HtmlToSvgConverter();
$preview4 = $converter4->convertToImgTag(
    '<rect x="10" y="10" width="780" height="580" fill="#1d4ed8" rx="20"/>'
    . '<text x="400" y="320" text-anchor="middle" fill="white" style="font-size:80px">MForm</text>',
    ['viewBox' => '0 0 800 600', 'style' => 'max-width:100%;border:1px solid #ddd;border-radius:4px;']
);
$renderBlock('HtmlToSvgConverter – Rect + Text', $code4, $preview4);

// Beispiel 5: Mehrere Shapes
$code5 = <<<'PHP'
$converter = new HtmlToSvgConverter();
echo $converter->convertToImgTag('
    <circle cx="200" cy="300" r="120" fill="#10b981"/>
    <rect x="380" y="180" width="240" height="240" fill="#f59e0b" rx="20"/>
    <polygon points="700,180 820,420 580,420" fill="#ef4444"/>
', ['viewBox' => '0 0 900 600']);
PHP;

$converter5 = new HtmlToSvgConverter();
$preview5 = $converter5->convertToImgTag(
    '<circle cx="200" cy="300" r="120" fill="#10b981"/>'
    . '<rect x="380" y="180" width="240" height="240" fill="#f59e0b" rx="20"/>'
    . '<polygon points="700,180 820,420 580,420" fill="#ef4444"/>',
    ['viewBox' => '0 0 900 600', 'style' => 'max-width:100%;border:1px solid #ddd;border-radius:4px;']
);
$renderBlock('HtmlToSvgConverter – Mehrere Shapes', $code5, $preview5);

// Beispiel 6: Base64-Datenurl direkt verwenden
$code6 = <<<'PHP'
$converter = new HtmlToSvgConverter();
$dataUrl = $converter->convertToBase64('
    <rect width="800" height="600" fill="#0f172a"/>
    <text x="50%" y="50%" text-anchor="middle" dominant-baseline="middle"
          fill="#fbbf24" style="font-size:60px;font-weight:bold">SVG aus HTML</text>
', ['viewBox' => '0 0 800 600']);

// als CSS background-image, img src oder direkt als URL nutzen
echo '<div style="background-image:url(' . $dataUrl . ')"></div>';
PHP;

$converter6 = new HtmlToSvgConverter();
$dataUrl6 = $converter6->convertToBase64(
    '<rect width="800" height="600" fill="#0f172a"/>'
    . '<text x="50%" y="50%" text-anchor="middle" dominant-baseline="middle"'
    . ' fill="#fbbf24" style="font-size:60px;font-weight:bold">SVG aus HTML</text>',
    ['viewBox' => '0 0 800 600']
);
$preview6 = '<img src="' . rex_escape($dataUrl6, 'html_attr') . '" alt="Base64 SVG" style="max-width:100%;border:1px solid #ddd;border-radius:4px;">';
$renderBlock('HtmlToSvgConverter – convertToBase64()', $code6, $preview6);
