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
$fragment->setVar('title', $this->i18n('info'), false);
$fragment->setVar('body', '<p>' . $this->i18n('example_description_expert') . '</p>', false);
echo $fragment->parse('core/page/section.php');

// parse info fragment
$fragment = new rex_fragment();
$fragment->setVar('title', $this->i18n('demo_expert'), false);
$fragment->setVar('body', MFormPageHelper::exchangeExamples('expert'), false);
echo $fragment->parse('core/page/section.php');
