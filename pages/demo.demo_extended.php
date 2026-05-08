<?php

/**
 * @author Joachim Doerr
 * @package redaxo5
 * @license MIT
 */

use FriendsOfRedaxo\MForm\Utils\MFormPageHelper;

$addon = rex_addon::get('mform');

// parse info fragment
$fragment = new rex_fragment();
$fragment->setVar('title', $addon->i18n('info'), false);
$fragment->setVar('body', '<p>' . $addon->i18n('example_description_extended') . '</p>', false);
echo $fragment->parse('core/page/section.php');

echo MFormPageHelper::exchangeExamples('extended');
