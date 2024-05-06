<?php
/**
 * @author Joachim Doerr
 * @package redaxo5
 * @license MIT
 */

use MForm\Utils\MFormPageHelper;

echo rex_view::title(rex_i18n::msg('mform_title') . ' ' . rex_i18n::msg('mform_' . rex_be_controller::getCurrentPagePart(2)));

// parse info fragment
$fragment = new rex_fragment();
$fragment->setVar('title', rex_i18n::msg('mform_info'), false);
$fragment->setVar('body', '<p>' . rex_i18n::msg('mform_example_description_base') . '</p>', false);
echo $fragment->parse('core/page/section.php');

// parse info fragment
$fragment = new rex_fragment();
$fragment->setVar('title', rex_i18n::msg('mform_demo_base'), false);
$fragment->setVar('body', MFormPageHelper::exchangeExamples('base'), false);
echo $fragment->parse('core/page/section.php');
