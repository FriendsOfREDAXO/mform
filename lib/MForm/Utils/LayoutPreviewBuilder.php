<?php
namespace FriendsOfRedaxo\MForm\Utils;

class LayoutPreviewBuilder {
    private $svgWidth = 800;
    private $svgHeight = 800;
    private $elements = [];
    private $backgroundColor = '#f8f9fa';
    private $aspectRatio = '4:1';
    private $columnGap = 30;
    private $sectionGap = 40;
    private $currentContext = ['main'];  // Stack für den Kontext (main, nested)
    private $nestedSectionGap = 10; // Kleinerer Abstand für verschachtelte Sections

    public function setAspectRatio($aspectRatio) {
        $this->aspectRatio = $aspectRatio;
        list($w, $h) = explode(':', $aspectRatio);
        $this->svgHeight = ($this->svgWidth / $w) * $h;
        return $this;
    }

    public function setBackgroundColor($color) {
        $this->backgroundColor = $color;
        return $this;
    }

    public function addSection() {
        $this->elements[] = [
            'type' => 'section',
            'columns' => []
        ];
        return $this;
    }

    public function startNestedSection() {
        if (empty($this->elements) || empty(end($this->elements)['columns'])) {
            throw new Exception("Cannot start nested section: No active column");
        }

        $currentSection = &$this->elements[count($this->elements) - 1];
        $currentColumn = &$currentSection['columns'][count($currentSection['columns']) - 1];
        $currentColumn['content'][] = [
            'type' => 'nested_section',
            'columns' => []
        ];

        $this->currentContext[] = 'nested';
        return $this;
    }

    public function endNestedSection() {
        if (end($this->currentContext) !== 'nested') {
            throw new Exception("No nested section to end");
        }
        array_pop($this->currentContext);
        return $this;
    }

public function addColumn($width) {
        if (end($this->currentContext) === 'nested') {
            // Füge Spalte zur verschachtelten Section hinzu
            $currentSection = &$this->elements[count($this->elements) - 1];
            $currentColumn = &$currentSection['columns'][count($currentSection['columns']) - 1];
            $nestedSection = &$currentColumn['content'][count($currentColumn['content']) - 1];

            $nestedSection['columns'][] = [
                'width' => $width,
                'content' => []
            ];
        } else {
            // Normales Verhalten für Hauptspalten
            if (empty($this->elements) || end($this->elements)['type'] !== 'section') {
                $this->addSection();
            }
            $currentSection = &$this->elements[count($this->elements) - 1];
            $currentSection['columns'][] = [
                'width' => $width,
                'content' => []
            ];
        }
        return $this;
    }

    public function addElement($type, $position = 'left', $aspectRatio = '1:1') {
        if (empty($this->elements) || empty(end($this->elements)['columns'])) {
            throw new Exception("Add a column before adding an element.");
        }

        if (end($this->currentContext) === 'nested') {
            $currentSection = &$this->elements[count($this->elements) - 1];
            $parentColumn = &$currentSection['columns'][count($currentSection['columns']) - 1];
            $nestedSection = &$parentColumn['content'][count($parentColumn['content']) - 1];
            $currentColumn = &$nestedSection['columns'][count($nestedSection['columns']) - 1];
        } else {
            $currentSection = &$this->elements[count($this->elements) - 1];
            $currentColumn = &$currentSection['columns'][count($currentSection['columns']) - 1];
        }

        $currentColumn['content'][] = [
            'type' => $type,
            'position' => $position,
            'aspectRatio' => $aspectRatio
        ];
        return $this;
    }

     private function getWidth($width, $parentWidth = null) {
        $actualWidth = $parentWidth ?? $this->svgWidth;
        $gap = $this->columnGap;

        switch ($width) {
            case '1/4': return ($actualWidth - 3 * $gap) / 4;
            case '1/3': return ($actualWidth - 2 * $gap) / 3;
            case '1/2': return ($actualWidth - $gap) / 2;
            case '2/3': return (($actualWidth - $gap) * 2) / 3;
            case '3/4': return (($actualWidth - $gap) * 3) / 4;
            case 'full': return $actualWidth;
            default: return ($actualWidth - $gap) / 2;
        }
    }

    private function getHeight($aspectRatio, $width) {
        list($w, $h) = explode(':', $aspectRatio);
        return ($width / $w) * $h;
    }

   private function calculateSectionHeight($section, $isNested = false) {
        $maxHeight = 0;
        $gap = $isNested ? $this->nestedSectionGap : $this->sectionGap;

        foreach ($section['columns'] as $column) {
            $columnHeight = 0; // Kein Initial padding für verschachtelte Sections
            foreach ($column['content'] as $element) {
                if ($element['type'] === 'nested_section') {
                    $columnHeight += $this->calculateSectionHeight($element, true);
                } else {
                    $columnWidth = $this->getWidth($column['width']);
                    $elementHeight = $this->getHeight($element['aspectRatio'], $columnWidth);
                    $columnHeight += $elementHeight + ($isNested ? 5 : 10);
                }
            }
            $maxHeight = max($maxHeight, $columnHeight);
        }

        return $maxHeight + ($isNested ? 0 : 10); // Extra padding nur für Haupt-Sections
    }

public function render() {
        // Berechne die maximale Höhe basierend auf dem Seitenverhältnis
        list($w, $h) = explode(':', $this->aspectRatio);
        $maxHeight = ($this->svgWidth / $w) * $h;

        // Berechne tatsächliche Content-Höhe
        $contentHeight = 0;
        foreach ($this->elements as $section) {
            $contentHeight += $this->calculateSectionHeight($section) + $this->sectionGap;
        }

        // Verwende die vorgegebene Höhe aus dem Seitenverhältnis
        $this->svgHeight = $maxHeight;

        // Skalierungsfaktor berechnen, falls der Content zu hoch ist
        $scale = $contentHeight > $maxHeight ? $maxHeight / $contentHeight : 1;

        $svg = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 ' . $this->svgWidth . ' ' .
               $this->svgHeight . '" style="max-width:100%; height:auto;">';
        $svg .= '<rect width="100%" height="100%" fill="' . $this->backgroundColor . '"/>';

        // Wenn nötig, SVG-Transformation für Skalierung hinzufügen
        if ($scale < 1) {
            $svg .= '<g transform="scale(' . 1 . ',' . $scale . ')">';
        }

        $currentY = 0;
        foreach ($this->elements as $section) {
            $currentY += $this->renderSection($svg, $section, 0, $currentY);
            $currentY += $this->sectionGap;
        }

        if ($scale < 1) {
            $svg .= '</g>';
        }

        $svg .= '</svg>';
        $this->reset();
        return 'data:image/svg+xml;base64,' . base64_encode($svg);
    }

    private function renderSection(&$svg, $section, $offsetX, $offsetY, $isNested = false, $parentWidth = null) {
        $currentX = $offsetX;
        $maxHeight = 0;
        $gap = $isNested ? $this->nestedSectionGap : $this->sectionGap;

        foreach ($section['columns'] as $column) {
            $columnWidth = $this->getWidth($column['width'], $parentWidth);
            $elementY = $offsetY + ($isNested ? 5 : 10);

            foreach ($column['content'] as $element) {
                if ($element['type'] === 'nested_section') {
                    $nestedHeight = $this->renderSection(
                        $svg,
                        $element,
                        $currentX,
                        $elementY,
                        true,
                        $columnWidth
                    );
                    $elementY += $nestedHeight + $this->nestedSectionGap;
                } else {
                    $elementHeight = $this->getHeight($element['aspectRatio'], $columnWidth);
                    $svg .= $this->generateElement(
                        $element['type'],
                        $currentX,
                        $elementY,
                        $columnWidth,
                        $elementHeight
                    );
                    $elementY += $elementHeight + ($isNested ? 5 : 10);
                }
            }

            $maxHeight = max($maxHeight, $elementY - $offsetY);
            $currentX += $columnWidth + $this->columnGap;
        }

        return $maxHeight;
    }
    private function reset() {
        $this->elements = [];
        $this->backgroundColor = '#f8f9fa';
        $this->currentContext = ['main'];
    }

    private function generateElement($type, $x, $y, $width, $height) {
        switch ($type) {
            case 'image':
                return '<rect x="' . $x . '" y="' . $y . '" width="' . $width .
                       '" height="' . $height . '" fill="#007bff"/>';
            case 'text':
                return '<rect x="' . $x . '" y="' . $y . '" width="' . $width .
                       '" height="' . $height . '" fill="#e9ecef"/>' .
                       $this->generateTextLines($x + 10, $y + 10, $width);
            case 'button':
                return '<rect x="' . $x . '" y="' . ($y + $height - 40) .
                       '" width="' . ($width * 0.6) . '" height="40" fill="#28a745" rx="5"/>';
            case 'accordion':
                return $this->generateAccordion($x, $y, $width, $height);
            case 'tabs':
                return $this->generateTabs($x, $y, $width, $height);
            case 'list':
                return $this->generateList($x, $y, $width);
            case 'form':
                return $this->generateForm($x, $y, $width);
            case 'gallery':
                return $this->generateGallery($x, $y, $width, $height);
            default:
                return '';
        }
    }

private function generateAccordion($x, $y, $width, $height) {
        $itemCount = 4;
        $itemHeight = ($height - (($itemCount - 1) * 10)) / $itemCount;
        $headerHeight = $itemHeight * 0.3;
        $accordion = '';

        for ($i = 0; $i < $itemCount; $i++) {
            $itemY = $y + ($i * ($itemHeight + 10));

            // Header background
            $accordion .= '<rect x="' . $x . '" y="' . $itemY .
                         '" width="' . $width . '" height="' . $headerHeight .
                         '" fill="' . ($i === 0 ? '#007bff' : '#e9ecef') . '" rx="3"/>';

            // Header text placeholder
            $accordion .= '<rect x="' . ($x + 15) . '" y="' . ($itemY + $headerHeight/4) .
                         '" width="' . ($width * 0.7) . '" height="' . ($headerHeight/2) .
                         '" fill="' . ($i === 0 ? '#ffffff' : '#6c757d') . '" rx="2"/>';

            // Plus/Minus icon
            if ($i === 0) {
                // Minus icon für geöffnetes Panel
                $accordion .= '<rect x="' . ($x + $width - 35) . '" y="' . ($itemY + $headerHeight/2 - 1) .
                             '" width="20" height="2" fill="#ffffff"/>';
            } else {
                // Plus icon für geschlossene Panels
                $accordion .= '<rect x="' . ($x + $width - 35) . '" y="' . ($itemY + $headerHeight/2 - 1) .
                             '" width="20" height="2" fill="#6c757d"/>';
                $accordion .= '<rect x="' . ($x + $width - 26) . '" y="' . ($itemY + $headerHeight/2 - 10) .
                             '" width="2" height="20" fill="#6c757d"/>';
            }

            // Content für das erste (geöffnete) Panel
            if ($i === 0) {
                $contentY = $itemY + $headerHeight;
                $contentHeight = $itemHeight - $headerHeight;

                // Content background
                $accordion .= '<rect x="' . $x . '" y="' . $contentY .
                             '" width="' . $width . '" height="' . $contentHeight .
                             '" fill="#f8f9fa" rx="3"/>';

                // Content text lines
                $lineSpacing = $contentHeight / 4;
                for ($j = 0; $j < 3; $j++) {
                    $lineY = $contentY + $lineSpacing * ($j + 0.5);
                    $lineWidth = ($width - 30) * (0.9 - $j * 0.15); // Abnehmende Linienbreite
                    $accordion .= '<rect x="' . ($x + 15) . '" y="' . $lineY .
                                '" width="' . $lineWidth . '" height="2" fill="#dee2e6" rx="1"/>';
                }
            }
        }

        return $accordion;
    }

private function generateForm($x, $y, $width) {
        $inputHeight = 40;
        $spacing = 10;
        $form = '';

        // Label für das erste Eingabefeld
        $form .= '<rect x="' . $x . '" y="' . $y .
                '" width="' . ($width * 0.3) . '" height="' . ($inputHeight * 0.5) .
                '" fill="#6c757d" rx="2"/>';

        // Erstes Eingabefeld
        $form .= '<rect x="' . $x . '" y="' . ($y + $inputHeight * 0.6) .
                '" width="' . $width . '" height="' . $inputHeight .
                '" fill="#e9ecef" rx="3"/>';

        // Label für das zweite Eingabefeld
        $form .= '<rect x="' . $x . '" y="' . ($y + $inputHeight * 1.8) .
                '" width="' . ($width * 0.4) . '" height="' . ($inputHeight * 0.5) .
                '" fill="#6c757d" rx="2"/>';

        // Zweites Eingabefeld
        $form .= '<rect x="' . $x . '" y="' . ($y + $inputHeight * 2.4) .
                '" width="' . $width . '" height="' . $inputHeight .
                '" fill="#e9ecef" rx="3"/>';

        // Submit Button
        $form .= '<rect x="' . $x . '" y="' . ($y + $inputHeight * 3.6) .
                '" width="' . ($width * 0.4) . '" height="' . $inputHeight .
                '" fill="#007bff" rx="3"/>';

        return $form;
    }

    private function generateTextLines($x, $y, $columnWidth) {
        $line1Width = $columnWidth * 0.9;
        $line2Width = $columnWidth * 0.7;
        $line3Width = $columnWidth * 0.8;
        $lineHeight = 10;
        return '
            <rect x="' . $x . '" y="' . $y . '" width="' . $line1Width .
            '" height="' . $lineHeight . '" fill="#dee2e6"/>
            <rect x="' . $x . '" y="' . ($y + $lineHeight + 5) . '" width="' . $line2Width .
            '" height="' . $lineHeight . '" fill="#dee2e6"/>
            <rect x="' . $x . '" y="' . ($y + 2 * ($lineHeight + 5)) . '" width="' . $line3Width .
            '" height="' . $lineHeight . '" fill="#dee2e6"/>
        ';
    }
private function generateList($x, $y, $width) {
        $itemHeight = 30;
        $list = '';

        // Header
        $list .= '<rect x="' . $x . '" y="' . $y . '" width="' . ($width * 0.8) .
                '" height="' . ($itemHeight * 0.8) . '" fill="#212529" rx="3"/>';

        for ($i = 0; $i < 4; $i++) {
            $itemY = $y + 40 + ($i * ($itemHeight + 10));
            $list .= '<circle cx="' . ($x + 10) . '" cy="' . ($itemY + $itemHeight/2) .
                    '" r="4" fill="#007bff"/>';
            $list .= '<rect x="' . ($x + 25) . '" y="' . $itemY . '" width="' . ($width - 35) .
                    '" height="' . $itemHeight . '" fill="#f8f9fa" rx="3"/>';
            $list .= '<rect x="' . ($x + 35) . '" y="' . ($itemY + $itemHeight/3) .
                    '" width="' . ($width - 55) . '" height="' . ($itemHeight/3) .
                    '" fill="#dee2e6" rx="2"/>';
        }
        return $list;
    }

    private function generateGallery($x, $y, $width, $height) {
        $gallery = '';
        $columns = 3;
        $rows = 2;
        $gap = 10;

        $imageWidth = ($width - (($columns - 1) * $gap)) / $columns;
        $imageHeight = ($height - (($rows - 1) * $gap)) / $rows;

        $colors = ['#007bff', '#6610f2', '#6f42c1', '#e83e8c', '#dc3545', '#fd7e14'];

        for ($row = 0; $row < $rows; $row++) {
            for ($col = 0; $col < $columns; $col++) {
                $imageX = $x + ($col * ($imageWidth + $gap));
                $imageY = $y + ($row * ($imageHeight + $gap));
                $colorIndex = ($row * $columns + $col) % count($colors);

                $gallery .= '<rect x="' . $imageX . '" y="' . $imageY .
                           '" width="' . $imageWidth . '" height="' . $imageHeight .
                           '" fill="' . $colors[$colorIndex] . '" rx="3"/>';
            }
        }
        return $gallery;
    }

    private function generateTabs($x, $y, $width, $height) {
        $tabWidth = $width / 3;
        $tabHeight = 40;
        $tabs = '';

        for ($i = 0; $i < 3; $i++) {
            $isActive = $i === 0;
            $tabX = $x + ($i * $tabWidth);
            $tabs .= '<rect x="' . $tabX . '" y="' . $y . '" width="' . ($tabWidth - 2) .
                    '" height="' . $tabHeight . '" fill="' . ($isActive ? '#007bff' : '#e9ecef') .
                    '" rx="3 3 0 0"/>';
        }

        $tabs .= '<rect x="' . $x . '" y="' . ($y + $tabHeight) . '" width="' . $width .
                '" height="' . ($height - $tabHeight) . '" fill="#ffffff" stroke="#dee2e6" stroke-width="2"/>';

        $tabs .= $this->generateTextLines($x + 20, $y + $tabHeight + 20, $width - 40);
        return $tabs;
    }
}
