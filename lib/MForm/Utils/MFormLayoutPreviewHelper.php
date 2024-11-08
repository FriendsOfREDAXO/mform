<?php
namespace FriendsOfRedaxo\MForm\Utils;

class MFormLayoutPreviewHelper {
    private $builder;

    public function __construct() {
        $this->builder = new LayoutPreviewBuilder();
    }

    public function generateLayoutPreview($config) {
        $this->builder
            ->setAspectRatio($config['aspectRatio'] ?? '16:9')
            ->setBackgroundColor($config['backgroundColor'] ?? '#ffffff');

        if (isset($config['columns'])) {
            foreach ($config['columns'] as $column) {
                $this->builder->addColumn($column['width'] ?? 'full');

                if (isset($column['elements'])) {
                    foreach ($column['elements'] as $element) {
                        if ($element['type'] === 'nested') {
                            $this->builder->startNestedSection();
                            foreach ($element['columns'] as $nestedColumn) {
                                $this->builder->addColumn($nestedColumn['width'] ?? 'full');
                                foreach ($nestedColumn['elements'] as $nestedElement) {
                                    $this->builder->addElement(
                                        $nestedElement['type'],
                                        $nestedElement['position'] ?? 'left',
                                        $nestedElement['aspectRatio'] ?? '1:1'
                                    );
                                }
                            }
                            $this->builder->endNestedSection();
                        } else {
                            $this->builder->addElement(
                                $element['type'],
                                $element['position'] ?? 'left',
                                $element['aspectRatio'] ?? '1:1'
                            );
                        }
                    }
                }
            }
        }

        return $this->builder->render();
    }
}
