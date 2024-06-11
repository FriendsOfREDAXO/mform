<?php
/**
 * @author Joachim Doerr
 * @package redaxo5
 * @license MIT
 */

namespace FriendsOfRedaxo\MForm\Utils;

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
                    $content .= '<h3>' . rex_i18n::msg('mform_modul_input') . '</h3><pre class="rex-code">' . highlight_string(file_get_contents($modulesDirectory . '/input.inc'), true) . '</pre>';
                }
                if (file_exists($modulesDirectory . '/output.inc')) {
                    $content .= '<h3>' . rex_i18n::msg('mform_modul_output') . '</h3><pre class="rex-code">' . highlight_string(file_get_contents($modulesDirectory . '/output.inc'), true) . '</pre>';
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

                    $installLink = '';
                    // install or reset/update button
                    if (rex_module::forKey($key) instanceof rex_module || !is_null($moduleId)) {
                        $installLink = '<a href="' . rex_url::currentBackendPage(['install' => $key]) . '" class="btn btn-primary">' . rex_i18n::msg('mform_module_update') . '</a>';
                        $content .= '<h3>' . rex_i18n::msg('mform_update') . ' <i style="font-weight:200">' . rex_i18n::msg('mform_example_' . $key) . ' [id: ' . $moduleId . ']</i></h3>' . $installMsg . $installLink;
                    } else {
                        $installLink = '<a href="' . rex_url::currentBackendPage(['install' => $key]) . '" class="btn btn-primary">' . rex_i18n::msg('mform_module_install') . '</a>';
                        $content .= '<h3>' . rex_i18n::msg('mform_install') . ' <i style="font-weight:200">' . rex_i18n::msg('mform_example_' . $key) . '</i></h3>' . $installMsg . $installLink;
                    }

                    // parse info fragment
                    $fragment = new rex_fragment();
                    $fragment->setVar('title', rex_i18n::msg('mform_example_' . $key) . '<span class="btn-group-xs pull-right">' . str_replace('btn-primary', 'btn-default', $installLink) . '</span>', false);
                    $fragment->setVar('content', '<div class="span" style="padding: 0 20px 10px 20px">' . $content . '</div>', false);
                    $fragment->setVar('collapse', true);
                    $fragment->setVar('collapsed', $installKey != $key);
                    $content = $fragment->parse('core/page/section.php');
                    $return .= $content;
                }
            }
        }
        return $return;
    }
}
