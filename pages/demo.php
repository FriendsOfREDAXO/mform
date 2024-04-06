<?php
/**
 * @author Joachim Doerr
 * @package redaxo5
 * @license MIT
 */

use MForm\Utils\MFormPageHelper;

// parse info fragment
$fragment = new rex_fragment();
$fragment->setVar('title', 'MInputs', false);
$fragment->setVar('body', '<p>Hier werden für den Release v8.0 entsprechende Demos angeboten.</p>', false);
echo $fragment->parse('core/page/section.php');
