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

/**
 * Returns a builder pre-configured with sane font sizes for module previews.
 */
$mkBuilder = static function (string $aspectRatio = '16:9'): LayoutPreviewBuilder {
    return (new LayoutPreviewBuilder())
        ->setAspectRatio($aspectRatio)
        ->setFontSizes(['elementTitle' => 40, 'description' => 36, 'lineHeight' => 18]);
};

// =====================================================================
// LayoutPreviewBuilder – realistic web design layouts
// =====================================================================

// ---- 1) Hero + 3 cards ---------------------------------------------------
$code1 = <<<'PHP'
$builder = (new LayoutPreviewBuilder())
    ->setAspectRatio('16:9')
    ->setFontSizes(['elementTitle' => 40, 'description' => 36]);

// Hero: vollflaechiges Bild
$builder->addSection()
    ->addColumn('full')->addElement('image', 'left', '4:1', ['description' => 'Hero']);

// 3 Cards
$builder->addSection()
    ->addColumn('1/3')->addElement('image', 'left', '3:2')
    ->addColumn('1/3')->addElement('image', 'left', '3:2')
    ->addColumn('1/3')->addElement('image', 'left', '3:2');

echo '<img src="' . $builder->render() . '" alt="Hero + Cards">';
PHP;

$builder1 = $mkBuilder('16:9');
$builder1->addSection()
    ->addColumn('full')->addElement('image', 'left', '4:1', ['description' => 'Hero']);
$builder1->addSection()
    ->addColumn('1/3')->addElement('image', 'left', '3:2')
    ->addColumn('1/3')->addElement('image', 'left', '3:2')
    ->addColumn('1/3')->addElement('image', 'left', '3:2');
$preview1 = '<img src="' . rex_escape($builder1->render(), 'html_attr') . '" alt="Hero + Cards" style="max-width:100%;border:1px solid #ddd;border-radius:4px;">';
$renderBlock('Layout: Hero + 3 Cards', $code1, $preview1);

// ---- 2) Sidebar layout (Main 2/3 + Sidebar 1/3) -------------------------
$code2 = <<<'PHP'
$builder = (new LayoutPreviewBuilder())
    ->setAspectRatio('4:3')
    ->setFontSizes(['elementTitle' => 40, 'description' => 36]);

$builder->addSection()
    ->addColumn('2/3')
        ->addElement('image', 'left', '16:9')
        ->addElement('text',  'left', '3:1')
    ->addColumn('1/3')
        ->addElement('list',  'left', '3:4');

echo '<img src="' . $builder->render() . '">';
PHP;

$builder2 = $mkBuilder('4:3');
$builder2->addSection()
    ->addColumn('2/3')
        ->addElement('image', 'left', '16:9')
        ->addElement('text',  'left', '3:1')
    ->addColumn('1/3')
        ->addElement('list',  'left', '3:4');
$preview2 = '<img src="' . rex_escape($builder2->render(), 'html_attr') . '" alt="Sidebar Layout" style="max-width:100%;border:1px solid #ddd;border-radius:4px;">';
$renderBlock('Layout: Main + Sidebar', $code2, $preview2);

// ---- 3) Magazine: 2/3 image + 1/3 stacked text/text --------------------
$code3 = <<<'PHP'
$builder = (new LayoutPreviewBuilder())
    ->setAspectRatio('2:1')
    ->setFontSizes(['elementTitle' => 40, 'description' => 36]);

$builder->addSection()
    ->addColumn('2/3')->addElement('image', 'left', '2:1')
    ->addColumn('1/3')
        ->startNestedSection()
            ->addColumn('full')->addElement('text', 'left', '3:1')
            ->addColumn('full')->addElement('text', 'left', '3:1')
        ->endNestedSection();

echo '<img src="' . $builder->render() . '">';
PHP;

$builder3 = $mkBuilder('2:1');
$builder3->addSection()
    ->addColumn('2/3')->addElement('image', 'left', '2:1')
    ->addColumn('1/3')
        ->startNestedSection()
            ->addColumn('full')->addElement('text', 'left', '3:1')
            ->addColumn('full')->addElement('text', 'left', '3:1')
        ->endNestedSection();
$preview3 = '<img src="' . rex_escape($builder3->render(), 'html_attr') . '" alt="Magazine Layout" style="max-width:100%;border:1px solid #ddd;border-radius:4px;">';
$renderBlock('Layout: Magazine (Bild + Text-Stack)', $code3, $preview3);

// ---- 4) Contact: form + image ------------------------------------------
$code4 = <<<'PHP'
$builder = (new LayoutPreviewBuilder())
    ->setAspectRatio('3:2')
    ->setFontSizes(['elementTitle' => 40, 'description' => 36]);

$builder->addSection()
    ->addColumn('1/2')->addElement('form',  'left', '1:1')
    ->addColumn('1/2')->addElement('image', 'left', '1:1');

echo '<img src="' . $builder->render() . '">';
PHP;

$builder4 = $mkBuilder('3:2');
$builder4->addSection()
    ->addColumn('1/2')->addElement('form',  'left', '1:1')
    ->addColumn('1/2')->addElement('image', 'left', '1:1');
$preview4 = '<img src="' . rex_escape($builder4->render(), 'html_attr') . '" alt="Contact Layout" style="max-width:100%;border:1px solid #ddd;border-radius:4px;">';
$renderBlock('Layout: Kontakt (Form + Bild)', $code4, $preview4);

// ---- 5) Gallery + accordion ---------------------------------------------
$code5 = <<<'PHP'
$builder = (new LayoutPreviewBuilder())
    ->setAspectRatio('16:9')
    ->setFontSizes(['elementTitle' => 40, 'description' => 36]);

$builder->addSection()
    ->addColumn('full')->addElement('gallery', 'left', '3:1');

$builder->addSection()
    ->addColumn('full')->addElement('accordion', 'left', '4:1');

echo '<img src="' . $builder->render() . '">';
PHP;

$builder5 = $mkBuilder('16:9');
$builder5->addSection()
    ->addColumn('full')->addElement('gallery', 'left', '3:1');
$builder5->addSection()
    ->addColumn('full')->addElement('accordion', 'left', '4:1');
$preview5 = '<img src="' . rex_escape($builder5->render(), 'html_attr') . '" alt="Gallery + Accordion" style="max-width:100%;border:1px solid #ddd;border-radius:4px;">';
$renderBlock('Layout: Gallery + Accordion', $code5, $preview5);

// =====================================================================
// HtmlToSvgConverter – module thumbnails
// =====================================================================

$svgRenderBlock = static function (string $title, string $code, string $svgFragment, array $attrs): void {
    $converter = new HtmlToSvgConverter();
    $finalAttrs = array_merge(['style' => 'max-width:100%;border:1px solid #ddd;border-radius:4px;'], $attrs);
    $preview = $converter->convertToImgTag($svgFragment, $finalAttrs);

    $body = '<div class="row">'
        . '<div class="col-md-7"><pre><code class="language-php">' . rex_escape($code) . '</code></pre></div>'
        . '<div class="col-md-5">' . $preview . '</div>'
        . '</div>';

    $fragment = new rex_fragment();
    $fragment->setVar('title', $title, false);
    $fragment->setVar('body', $body, false);
    echo $fragment->parse('core/page/section.php');
};

// ---- 6) Branded module thumbnail (hero with sidebar) ---------------------
$code6 = <<<'PHP'
$converter = new HtmlToSvgConverter();
echo $converter->convertToImgTag('
    <rect width="800" height="450" fill="#0f172a"/>
    <rect x="40"  y="60"  width="320" height="40"  fill="#fbbf24" rx="4"/>
    <rect x="40"  y="120" width="540" height="14"  fill="#475569" rx="2"/>
    <rect x="40"  y="148" width="500" height="14"  fill="#475569" rx="2"/>
    <rect x="40"  y="176" width="420" height="14"  fill="#475569" rx="2"/>
    <rect x="40"  y="240" width="160" height="44"  fill="#fbbf24" rx="22"/>
    <rect x="600" y="60"  width="160" height="330" fill="#1d4ed8" rx="8"/>
', ['viewBox' => '0 0 800 450']);
PHP;

$svgRenderBlock(
    'HtmlToSvgConverter – Module Thumbnail',
    $code6,
    '<rect width="800" height="450" fill="#0f172a"/>'
    . '<rect x="40" y="60" width="320" height="40" fill="#fbbf24" rx="4"/>'
    . '<rect x="40" y="120" width="540" height="14" fill="#475569" rx="2"/>'
    . '<rect x="40" y="148" width="500" height="14" fill="#475569" rx="2"/>'
    . '<rect x="40" y="176" width="420" height="14" fill="#475569" rx="2"/>'
    . '<rect x="40" y="240" width="160" height="44" fill="#fbbf24" rx="22"/>'
    . '<rect x="600" y="60" width="160" height="330" fill="#1d4ed8" rx="8"/>',
    ['viewBox' => '0 0 800 450']
);

// ---- 7) Card with badge ------------------------------------------------
$code7 = <<<'PHP'
$converter = new HtmlToSvgConverter();
echo $converter->convertToImgTag('
    <rect x="40"  y="60"  width="520" height="400" fill="#ffffff"
          stroke="#cbd5e1" stroke-width="3" rx="12"/>
    <rect x="80"  y="100" width="320" height="20"  fill="#1e293b" rx="2"/>
    <rect x="80"  y="140" width="440" height="10"  fill="#cbd5e1" rx="2"/>
    <rect x="80"  y="160" width="400" height="10"  fill="#cbd5e1" rx="2"/>
    <rect x="80"  y="180" width="380" height="10"  fill="#cbd5e1" rx="2"/>
    <circle cx="500" cy="420" r="50" fill="#10b981"/>
', ['viewBox' => '0 0 600 480']);
PHP;

$svgRenderBlock(
    'HtmlToSvgConverter – Card mit Badge',
    $code7,
    '<rect x="40" y="60" width="520" height="400" fill="#ffffff" stroke="#cbd5e1" stroke-width="3" rx="12"/>'
    . '<rect x="80" y="100" width="320" height="20" fill="#1e293b" rx="2"/>'
    . '<rect x="80" y="140" width="440" height="10" fill="#cbd5e1" rx="2"/>'
    . '<rect x="80" y="160" width="400" height="10" fill="#cbd5e1" rx="2"/>'
    . '<rect x="80" y="180" width="380" height="10" fill="#cbd5e1" rx="2"/>'
    . '<circle cx="500" cy="420" r="50" fill="#10b981"/>',
    ['viewBox' => '0 0 600 480']
);

// ---- 8) Base64 data URL direct usage -----------------------------------
$code8 = <<<'PHP'
$converter = new HtmlToSvgConverter();
$dataUrl = $converter->convertToBase64('
    <rect width="800" height="600" fill="#0f172a"/>
    <text x="400" y="320" text-anchor="middle" fill="#fbbf24"
          style="font-size:60px;font-weight:bold">SVG aus HTML</text>
', ['viewBox' => '0 0 800 600']);

// als CSS background-image, img src oder direkt als URL nutzen
echo '<div style="background-image:url(' . $dataUrl . ')"></div>';
PHP;

$converter8 = new HtmlToSvgConverter();
$dataUrl8 = $converter8->convertToBase64(
    '<rect width="800" height="600" fill="#0f172a"/>'
    . '<text x="400" y="320" text-anchor="middle" fill="#fbbf24"'
    . ' style="font-size:60px;font-weight:bold">SVG aus HTML</text>',
    ['viewBox' => '0 0 800 600']
);
$preview8 = '<img src="' . rex_escape($dataUrl8, 'html_attr') . '" alt="Base64 SVG" style="max-width:100%;border:1px solid #ddd;border-radius:4px;">';
$renderBlock('HtmlToSvgConverter – convertToBase64()', $code8, $preview8);
