<?php
/**
 * @author Joachim Doerr
 * @package redaxo5
 * @license MIT
 */

use FriendsOfRedaxo\MForm\Utils\MFormPageHelper;

// parse info fragment
$fragment = new rex_fragment();
$fragment->setVar('title', $this->i18n('info'), false);
$fragment->setVar('body', '<p>' . $this->i18n('example_description_extended') . '</p>', false);
echo $fragment->parse('core/page/section.php');

echo MFormPageHelper::exchangeExamples('extended');