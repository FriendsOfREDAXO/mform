<?php
/**
 * @author mail[at]joachim-doerr[dot]com Joachim Doerr
 * @package redaxo5
 * @license MIT
 */

// rex request
$config = rex_post('config', array(
    array('mform_theme', 'string'),
    array('submit', 'boolean')
));

// include info page
include rex_path::addon('mform', 'pages/info.php');

//////////////////////////////////////////////////////////
// parse info fragment
$fragment = new rex_fragment();
$fragment->setVar('title', rex_i18n::msg('mform_help_subheadline_1'), false);
$fragment->setVar('body', $content, false);
echo $fragment->parse('core/page/section.php');


//////////////////////////////////////////////////////////
// init form
$form = '';

// if submit set config
if ($config['submit']) {
    // show is saved field
    $this->setConfig('mform_theme', $config['mform_theme']);
    $form .= rex_view::info(rex_i18n::msg('mform_config_saved'));
}

// read dir
$themes = MFormThemeHelper::getThemesInformation();

// open form
$form .= '
  <form action="' . rex_url::currentBackendPage() . '" method="post">
    <fieldset>
';

// set arrays
$formElements = array();
$elements = array();
$elements['label'] = '
  <label for="rex-mform-config-template">' . rex_i18n::msg('mform_config_label_template') . '</label>
';

// create select
$select = new rex_select;
$select->setId('rex-mform-config-template');
$select->setSize(1);
$select->setAttribute('class', 'form-control');
$select->setName('config[mform_theme]');
// add options
foreach ($themes as $theme) {
    $select->addOption($theme['theme_screen_name'], $theme['theme_path']);
}
$select->setSelected($this->getConfig('mform_theme'));
$elements['field'] = $select->get();
$formElements[] = $elements;
// parse select element
$fragment = new rex_fragment();
$fragment->setVar('elements', $formElements, false);
$form .= $fragment->parse('core/form/form.php');

// create submit button
$formElements = array();
$elements = array();
$elements['field'] = '
  <input type="submit" class="btn btn-save rex-form-aligned" name="config[submit]" value="' . rex_i18n::msg('mform_config_save') . '" ' . rex::getAccesskey(rex_i18n::msg('mform_config_save'), 'save') . ' />
';
$formElements[] = $elements;
// parse submit element
$fragment = new rex_fragment();
$fragment->setVar('elements', $formElements, false);
$form .= $fragment->parse('core/form/submit.php');

// close form
$form .= '
    </fieldset>
  </form>
';

//////////////////////////////////////////////////////////
// parse form fragment
$fragment = new rex_fragment();
$fragment->setVar('class', 'edit', false);
$fragment->setVar('title', rex_i18n::msg('mform_config'));
$fragment->setVar('body', $form, false);
echo $fragment->parse('core/page/section.php');