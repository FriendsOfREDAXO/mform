<?php
/**
 * @author Joachim Doerr
 * @package redaxo5
 * @license MIT
 */

namespace MForm\Utils;


use rex_exception;
use rex_fragment;
use rex_i18n;
use rex_logger;
use rex_module;
use rex_module_cache;
use rex_path;
use rex_sql;
use rex_sql_exception;
use rex_string;
use rex_url;
use rex_view;

use function count;
use function is_array;

class MFormPageHelper
{
    public static function exchangeExamples($type): string
    {
        $return = '';
        $modulesDirectories = glob(rex_path::addon('mform', 'pages/module/' . $type) . '/*', GLOB_ONLYDIR);

        if (is_array($modulesDirectories) && count($modulesDirectories) > 0) {
            foreach ($modulesDirectories as $modulesDirectory) {
                $installKey = rex_request('install', 'string', null);
                $installMsg = '';
                $content = '';
                if (!is_dir($modulesDirectory)) {
                    continue;
                }
                if (file_exists($modulesDirectory . '/input.inc')) {
                    $content .= '<h3>' . rex_i18n::msg('mform_modul_input') . '</h3>' . rex_string::highlight(file_get_contents($modulesDirectory . '/input.inc'));
                }
                if (file_exists($modulesDirectory . '/output.inc')) {
                    $content .= '<h3>' . rex_i18n::msg('mform_modul_output') . '</h3>' . rex_string::highlight(file_get_contents($modulesDirectory . '/output.inc'));
                }

                $path = explode('/', $modulesDirectory);
                $module = array_pop($path);
                $key = $type . '_' . $module;
                $moduleId = (rex_module::forKey($key) instanceof rex_module) ? rex_module::forKey($key)->getId() : null;

                if (!empty($content)) {
                    if ($installKey == $key) {
                        // install
                        $sql = rex_sql::factory();
                        $sql->setTable('rex_module')
                            ->setValue('input', file_get_contents($modulesDirectory . '/input.inc'))
                            ->setValue('output', (file_exists($modulesDirectory . '/output.inc')) ? file_get_contents($modulesDirectory . '/output.inc') : '')
                            ->setValue('name', rex_i18n::msg('mform_example_' . $key))
                            ->setValue('key', $key);

                        try {
                            if (rex_module::forKey($key) instanceof rex_module) {
                                $moduleId = rex_module::forKey($key)->getId();
                                // update by module id
                                $sql->setWhere('id=:id', ['id' => $moduleId])
                                    ->update();
                                // create msg
                                $installMsg = rex_view::success(sprintf(rex_i18n::msg('mform_module_updated'), rex_i18n::msg('mform_example_' . $key)));
                            } else {
                                // insert module
                                $sql->insert();
                                rex_module_cache::generateKeyMapping();
                                $moduleId = (int) $sql->getLastId();
                                // create msg
                                $installMsg = rex_view::success(sprintf(rex_i18n::msg('mform_module_created'), rex_i18n::msg('mform_example_' . $key)));
                            }
                        } catch (rex_sql_exception | rex_exception $e) {
                            rex_logger::logException($e);
                            $installMsg = rex_view::error($e->getMessage());
                        }
                    }

                    // install or reset/update button
                    if (rex_module::forKey($key) instanceof rex_module || !is_null($moduleId)) {
                        $content .= '<h3>' . rex_i18n::msg('mform_update') . ' <i style="font-weight:200">' . rex_i18n::msg('mform_example_' . $key) . ' [id: ' . $moduleId . ']</i></h3>' . $installMsg . '<a href="' . rex_url::currentBackendPage(['install' => $key]) . '" class="btn btn-primary">' . rex_i18n::msg('mform_module_update') . '</a>';
                    } else {
                        $content .= '<h3>' . rex_i18n::msg('mform_install') . ' <i style="font-weight:200">' . rex_i18n::msg('mform_example_' . $key) . '</i></h3>' . $installMsg . '<a href="' . rex_url::currentBackendPage(['install' => $key]) . '" class="btn btn-primary">' . rex_i18n::msg('mform_module_install') . '</a>';
                    }

                    // parse info fragment
                    $fragment = new rex_fragment();
                    $fragment->setVar('title', rex_i18n::msg('mform_example_' . $key));
                    $fragment->setVar('content', '<div class="span" style="padding: 0 20px 10px 20px">' . $content . '</div>', false);
                    $fragment->setVar('collapse', true);
                    $fragment->setVar('collapsed', $installKey != $key);
                    $content = $fragment->parse('core/page/section.php');
                    $return .= $content;
                }
            }
        }

        /*
        foreach (scandir(rex_path::addon('mform', 'pages/examples')) as $file) {
            if (is_dir($file)) {
                continue;
            }

            if (strpos($file, $type) !== false && strpos($file, 'output') === false) {

                // add input
                $content = '<h3>'.rex_i18n::msg('mform_modul_input').'</h3>' . rex_string::highlight(file_get_contents(rex_path::addon('mform', 'pages/examples/' . $file)));

                if (file_exists(rex_path::addon('mform', 'pages/examples/' . pathinfo($file, PATHINFO_FILENAME) . '_output.ini'))) {
                    // add output
                    $content .= '<h3>'.rex_i18n::msg('mform_modul_output').'</h3>' . rex_string::highlight(file_get_contents(rex_path::addon('mform', 'pages/examples/' . pathinfo($file, PATHINFO_FILENAME) . '_output.ini')));
                }

                // parse info fragment
                $fragment = new rex_fragment();
                $fragment->setVar('title', rex_i18n::msg('mform_example_' . preg_replace('/\d+/u', '', pathinfo($file, PATHINFO_FILENAME))));
                $fragment->setVar('content', '<div class="span" style="padding: 0 20px 10px 20px">' . $content . '</div>', false);
                $fragment->setVar('collapse', true);
                $fragment->setVar('collapsed', true);
                $content = $fragment->parse('core/page/section.php');
                $return .= $content;
            }
        }
        */
        return $return;
    }
}