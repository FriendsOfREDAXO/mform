<?php
/**
 * @author Joachim Doerr
 * @package redaxo5
 * @license MIT
 */


$mdFiles = [];
foreach (glob(rex_addon::get('mform')->getPath('docs') . '/*.md') ?: [] as $file) {
    $mdFiles[mb_substr(basename($file), 0, -3)] = $file;
}

$currenMDFile = rex_request('mdfile', 'string', '02_basics');
if (!array_key_exists($currenMDFile, $mdFiles)) {
    $currenMDFile = '02_basics';
}

$page = rex_be_controller::getPageObject('mform');

if (null !== $page) {
    foreach ($mdFiles as $key => $mdFile) {
        $keyWithoudPrio = mb_substr($key, 3);
        $currenMDFileWithoudPrio = mb_substr($currenMDFile, 3);
        $page->addSubpage(
            (new rex_be_page($key, rex_i18n::msg('mform_docs_' . $keyWithoudPrio)))
            ->setSubPath($mdFile)
            ->setHref('index.php?page=mform/docs&mdfile=' . $key)
        );
    }
}

if(rex_be_controller::getCurrentPagePart(2) == "changelog" || rex_be_controller::getCurrentPagePart(2) == "demo") {
    echo rex_view::title(rex_i18n::msg('mform_title') . ' ' . rex_i18n::msg('mform_'.rex_be_controller::getCurrentPagePart(2)));
}

rex_be_controller::includeCurrentPageSubPath();
