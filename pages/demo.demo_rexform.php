<?php

/**
 * @author Joachim Doerr
 * @package redaxo5
 * @license MIT
 */

if (!class_exists('rex_mform_demo_news_form', false)) {
    // Lokales Demo-Form: nutzt rex_form-Infrastruktur, schreibt aber nichts in DB/Config.
    class rex_mform_demo_news_form extends rex_config_form
    {
        protected function loadBackendConfig()
        {
            // Demo-Modus: keine Save-Buttons, keine Persistenz.
        }

        protected function save()
        {
            // Safety net: selbst wenn ein Submit erzwungen wird, nichts schreiben.
            return true;
        }

        protected function getValue($name)
        {
            return null;
        }
    }
}

// Kopfbereich der Demo-Seite mit kurzer Einordnung.
$fragment = new rex_fragment();
$fragment->setVar('title', rex_i18n::msg('mform_demo_rexform'), false);
$fragment->setVar('body', '<p>Demo einer News-Eingabemaske auf Basis von rex_form + MForm-Widgets. Die Maske speichert bewusst nicht.</p>', false);
echo $fragment->parse('core/page/section.php');

echo rex_view::info('Nur Vorschau: Diese Demo nutzt kein Tabellen-CRUD und schreibt keine Daten.');

// Demo-Form erzeugen (ohne Persistenz).
$form = rex_mform_demo_news_form::factory('mform_demo_news', 'News-Eingabe (ohne Speicherung)');

// Klassische News-Grundfelder.
$form->addTextField('headline', 'Sommerfestival 2026', ['label' => 'Headline']);
$form->addTextAreaField('teaser', 'Kurztext fuer die News-Anleser.', ['label' => 'Teaser', 'rows' => 4]);
$form->addInputField('date', 'publish_date', date('Y-m-d'), ['label' => 'Veroeffentlichen am', 'class' => 'form-control']);

// Aufmacherbilder (Imagelist-Widget).
/** @var rex_form_widget_mform_imglist_element $heroImages */
$heroImages = $form->addField('', 'hero_images', null, ['internal::fieldClass' => 'rex_form_widget_mform_imglist_element', 'label' => 'Aufmacherbilder'], true);
$heroImages->setTypes('jpg,jpeg,png,webp,avif');
$heroImages->setCategoryId(0);

// Anlagen/Downloads als Medialist mit fester Listenansicht.
/** @var rex_form_widget_mform_medialist_element $attachments */
$attachments = $form->addField('', 'attachments', null, ['internal::fieldClass' => 'rex_form_widget_mform_medialist_element', 'label' => 'Downloads / Anlagen'], true);
$attachments->setTypes('pdf,zip,docx,jpg,png');
$attachments->setView('list');
$attachments->setViews('list,grid,gallery');
$attachments->setToolbar('vertical');

// Verweise auf interne Artikel.
/** @var rex_form_widget_mform_linklist_element $related */
$related = $form->addField('', 'related_articles', null, ['internal::fieldClass' => 'rex_form_widget_mform_linklist_element', 'label' => 'Verwandte Artikel'], true);
$related->setToolbar('horizontal');

// Ein zentraler CTA-Link (intern/extern/media/mailto).
/** @var rex_form_widget_mform_customlink_element $cta */
$cta = $form->addField('', 'cta_link', null, ['internal::fieldClass' => 'rex_form_widget_mform_customlink_element', 'label' => 'Call-to-Action'], true);
$cta->setIntern(1);
$cta->setExternal(1);
$cta->setMedia(1);
$cta->setMailto(1);
$cta->setPhone(0);

// Mehrere Quellen/Referenzen als Custom-Link-Multi.
/** @var rex_form_widget_mform_custom_link_multi_element $sources */
$sources = $form->addField('', 'sources', null, ['internal::fieldClass' => 'rex_form_widget_mform_custom_link_multi_element', 'label' => 'Quellen / Referenzen'], true);
$sources->setIntern(1);
$sources->setExternal(1);
$sources->setMedia(1);
$sources->setMailto(1);
$sources->setPhone(0);
$sources->setBtnAdd('Quelle hinzufuegen');

// Formular rendern (Anzeige ohne Speicherung).
echo $form->get();

// Quellcode der Demo direkt darunter anzeigen.
$sourceCode = rex_file::get(__FILE__);
if (is_string($sourceCode) && '' !== $sourceCode) {
    $sourceFragment = new rex_fragment();
    $sourceFragment->setVar('title', 'Quellcode', false);
    $sourceFragment->setVar('body', '<pre class="rex-code">' . highlight_string($sourceCode, true) . '</pre>', false);
    echo $sourceFragment->parse('core/page/section.php');
}
