<?php

namespace FriendsOfRedaxo\MForm\Utils;

use DOMDocument;

class HtmlToSvgConverter {
    private $dom;
    private $svg;
    private $svgNS = 'http://www.w3.org/2000/svg';
    private $viewBoxWidth = 800;  // Standard-Wert
    private $viewBoxHeight = 600; // Standard-Wert

    public function __construct() {
        $this->dom = new DOMDocument();
        $this->svg = new DOMDocument();
        libxml_use_internal_errors(true);
    }

    public function convertToBase64($html, $attributes = []) {
        $svg = $this->convert($html, $attributes);
        return 'data:image/svg+xml;base64,' . base64_encode($svg);
    }

    public function convertToImgTag($html, $attributes = []) {
        $dataUrl = $this->convertToBase64($html, array_merge([
            'viewBox' => '0 0 2780 2780'
        ], $attributes));

        $defaultAttributes = [
            'alt' => 'Generated SVG',
            'class' => 'svg-image'
        ];

        $imgAttributes = array_merge($defaultAttributes, $attributes);

        $htmlAttributes = '';
        foreach ($imgAttributes as $key => $value) {
            if (!in_array($key, ['viewBox', 'preserveAspectRatio', 'xmlns', 'style'])) {
                $htmlAttributes .= sprintf(' %s="%s"', htmlspecialchars($key), htmlspecialchars($value));
            }
        }

        return sprintf('<img src="%s"%s />', $dataUrl, $htmlAttributes);
    }

    public function convert($html, $attributes = []) {
        $this->svg = new DOMDocument('1.0', 'UTF-8');
        $this->svg->formatOutput = true;

        // Extrahiere ViewBox-Dimensionen aus den Attributen
        if (isset($attributes['viewBox'])) {
            list(, , $this->viewBoxWidth, $this->viewBoxHeight) = explode(' ', $attributes['viewBox']);
        }

        $svg = $this->svg->createElementNS($this->svgNS, 'svg');
        $svg = $this->svg->appendChild($svg);

        $defaultAttributes = [
            'xmlns' => $this->svgNS,
            'version' => '1.1',
            'viewBox' => "0 0 {$this->viewBoxWidth} {$this->viewBoxHeight}",
            'preserveAspectRatio' => 'xMidYMid meet'
        ];

        $finalAttributes = array_merge($defaultAttributes, $attributes);
        foreach ($finalAttributes as $key => $value) {
            $svg->setAttribute($key, $value);
        }

        $defs = $this->svg->createElementNS($this->svgNS, 'defs');
        $svg->appendChild($defs);

        $group = $this->svg->createElementNS($this->svgNS, 'g');
        $svg->appendChild($group);

        $this->processHTML($html, $group);

        return $this->svg->saveXML();
    }

    private function processHTML($html, $parent) {
        $elementTypes = 'rect|circle|ellipse|line|polyline|polygon|path|text|g|image';
        $pattern = "/<($elementTypes)([^>]*)(?:>(.*?)<\/\\1>|\/?>)/s";

        preg_match_all($pattern, $html, $matches, PREG_SET_ORDER);

        foreach ($matches as $match) {
            $tagName = $match[1];
            $attributes = $match[2];
            $content = isset($match[3]) ? $match[3] : '';

            $element = $this->createSvgElement($tagName, $attributes, $content);
            if ($element) {
                $parent->appendChild($element);
            }
        }
    }

    private function createSvgElement($tagName, $attributeString, $content = '') {
        $element = $this->svg->createElementNS($this->svgNS, $tagName);

        // Wenn es sich um ein Text-Element handelt, setze die Standard-Schriftart
        if ($tagName === 'text') {
            $element->setAttribute('font-family', 'ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, Liberation Mono, Courier New, monospace');
        }

        // Extrahiere Attribute
        preg_match_all('/(\w+(?:-\w+)*)\s*=\s*["\']([^"\']*)["\']/', $attributeString, $matches, PREG_SET_ORDER);

        $styles = [];
        foreach ($matches as $match) {
            $attrName = $match[1];
            $attrValue = $match[2];

            if ($attrName === 'style') {
                $styles = $this->parseStyles($attrValue);
                // Füge die Monospace-Schriftart zu den bestehenden Styles hinzu
                if ($tagName === 'text' && !isset($styles['font-family'])) {
//                    $styles['font-family'] = 'ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, Liberation Mono, Courier New, monospace';
                    $styles['font-family'] = '"Helvetica Neue", Helvetica, Arial, "Lucida Grande", sans-serif';
                }
            } else {
                $attrValue = preg_replace('/^(\d+)px$/', '$1', $attrValue);
                $element->setAttribute($attrName, $attrValue);
            }
        }

        // Für Text-Elemente: Skaliere die Schriftgröße relativ zur ViewBox
        if ($tagName === 'text') {
            $styles = $this->adjustFontSize($styles);
        }

        $this->applyStylesToElement($element, $styles);

        if ($tagName === 'text' && !empty(trim($content))) {
            $textContent = html_entity_decode($content, ENT_QUOTES, 'UTF-8');
            $textNode = $this->svg->createTextNode($textContent);
            $element->appendChild($textNode);

            if (!$element->hasAttribute('dominant-baseline')) {
                $element->setAttribute('dominant-baseline', 'middle');
            }
            if (!$element->hasAttribute('text-anchor')) {
                $element->setAttribute('text-anchor', 'start');
            }
        }

        return $element;
    }

    private function adjustFontSize($styles) {
        if (isset($styles['font-size'])) {
            // Extrahiere numerischen Wert und einheit
            preg_match('/(\d+)(px|pt|em|rem)?/', $styles['font-size'], $matches);
            if (isset($matches[1])) {
                $size = floatval($matches[1]);
                // Berechne die Schriftgröße relativ zur ViewBox-Höhe
                // Ein typischer Wert ist 1-5% der ViewBox-Höhe
                $viewBoxRelativeSize = ($size / 16) * ($this->viewBoxHeight / 100);
                $styles['font-size'] = $viewBoxRelativeSize;
            }
        }
        return $styles;
    }

    private function applyStylesToElement($element, $styles) {
        $styleMap = [
            'fill' => 'fill',
            'stroke' => 'stroke',
            'stroke-width' => 'stroke-width',
            'opacity' => 'opacity',
            'fill-opacity' => 'fill-opacity',
            'stroke-opacity' => 'stroke-opacity',
            'background-color' => 'fill',
            'background' => 'fill',
            'color' => 'fill',
            'font-family' => 'font-family',
            'font-size' => 'font-size',
            'font-weight' => 'font-weight',
            'text-align' => 'text-anchor',
            'dominant-baseline' => 'dominant-baseline',
            'width' => 'width',
            'height' => 'height'
        ];

        foreach ($styles as $key => $value) {
            if ($key === 'background-color' || $key === 'background') {
                $element->setAttribute('fill', $value);
            } elseif ($key === 'border') {
                $this->processBorderShorthand($element, $value);
            } elseif (isset($styleMap[$key])) {
                $svgAttr = $styleMap[$key];
                if ($key === 'text-align') {
                    $value = $this->convertTextAlignToAnchor($value);
                }
                // Entferne px nur bei nicht-Font-Size Werten
                if ($key !== 'font-size') {
                    $value = preg_replace('/^(\d+)px$/', '$1', $value);
                }
                $element->setAttribute($svgAttr, $value);
            }
        }
    }

    private function processBorderShorthand($element, $border) {
        $parts = preg_split('/\s+/', trim($border));
        foreach ($parts as $part) {
            if (preg_match('/^[\d.]+(?:px|em|rem|pt)?$/', $part)) {
                $width = preg_replace('/[^\d.]/', '', $part);
                $element->setAttribute('stroke-width', $width);
            } elseif (preg_match('/^#|rgb|rgba|hsl|hsla/', $part)) {
                $element->setAttribute('stroke', $part);
            } elseif (in_array($part, ['solid', 'dashed', 'dotted'])) {
                $element->setAttribute('stroke-dasharray', $part === 'dashed' ? '5,5' : ($part === 'dotted' ? '1,3' : 'none'));
            }
        }
    }

    private function convertTextAlignToAnchor($align) {
        $map = [
            'left' => 'start',
            'center' => 'middle',
            'right' => 'end',
            'justify' => 'start'
        ];
        return isset($map[$align]) ? $map[$align] : 'start';
    }

    private function parseStyles($styleString) {
        $styles = [];
        $declarations = explode(';', trim($styleString));

        foreach ($declarations as $declaration) {
            $declaration = trim($declaration);
            if (empty($declaration)) continue;

            $parts = explode(':', $declaration, 2);
            if (count($parts) == 2) {
                $key = trim($parts[0]);
                $value = trim($parts[1]);
                $styles[$key] = $value;
            }
        }

        return $styles;
    }
}