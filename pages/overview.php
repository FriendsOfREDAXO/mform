<?php
/**
 * @author Joachim Doerr
 * @package redaxo5
 * @license MIT
 */

// include info page
include rex_path::addon('mform', 'pages/info.php');

//////////////////////////////////////////////////////////
// parse info fragment
$fragment = new rex_fragment();
$fragment->setVar('title', rex_i18n::msg('mform_help_subheadline_1'), false);
$fragment->setVar('body', $content, false);
echo $fragment->parse('core/page/section.php');
