<?php
/**
 * @copyright Copyright (c) 2015 by Joachim Doerr
 * @author mail[at]joachim-doerr[dot]com Joachim Doerr
 *
 * @package redaxo5
 * @version 4.0.0
 * @license MIT
 */

$fileType = 'community';

$demo_referenz = '
        <p>Later.</p>
    ';
// parse info fragment
$fragment = new rex_fragment();
$fragment->setVar('title', $this->i18n('info'), false);
$fragment->setVar('body', $demo_referenz, false);
echo $fragment->parse('core/page/section.php');

$return = '';
foreach (scandir(rex_path::addon('mform', 'pages/examples')) as $file) {
    if (is_dir($file)) {
        continue;
    }
    if (strpos($file, $fileType) !== false && strpos($file, 'output') === false) {

        // add input
        $content = '<h3>Modul Eingabe</h3>' . rex_string::highlight(file_get_contents(rex_path::addon('mform', 'pages/examples/' . $file)));

        if (file_exists(rex_path::addon('mform', 'pages/examples/' . pathinfo($file, PATHINFO_FILENAME) . '_output.ini'))) {
            // add output
            $content .= '<h3>Modul Ausgabe</h3>' . rex_string::highlight(file_get_contents(rex_path::addon('mform', 'pages/examples/' . pathinfo($file, PATHINFO_FILENAME) . '_output.ini')));
        }

        // parse info fragment
        $fragment = new rex_fragment();
        $fragment->setVar('title', $this->i18n('example_' . preg_replace('/\d+/u', '', pathinfo($file, PATHINFO_FILENAME))));
        $fragment->setVar('content', '<div class="span" style="padding: 0 20px 10px 20px">' . $content . '</div>', false);
        $fragment->setVar('collapse', true);
        $fragment->setVar('collapsed', true);
        $content = $fragment->parse('core/page/section.php');
        $return .= $content;
    }
}

// parse info fragment
$fragment = new rex_fragment();
$fragment->setVar('title', $this->i18n('demo_' . $fileType), false);
$fragment->setVar('body', $return, false);
echo $fragment->parse('core/page/section.php');
