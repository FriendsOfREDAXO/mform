<?php

/**
 * Template-API-Demo: MForm::registerTemplate() / fromTemplate() / applyTemplate()
 *
 * Zeigt, wie wiederverwendbare Formular-Templates als Klassen definiert,
 * global registriert und an verschiedenen Stellen instanziiert werden.
 *
 * WICHTIG: Die Klassen müssen vor dem Aufruf bekannt sein – in einem echten
 * Projekt sollten sie in lib/ liegen und per REDAXO-Autoloader geladen werden.
 * In diesem Beispiel-Modul werden sie inline definiert.
 */

use FriendsOfRedaxo\MForm;
use FriendsOfRedaxo\MFormTemplate\TemplateInterface;

// ---------------------------------------------------------------------------
// 1. Template-Klassen definieren
// ---------------------------------------------------------------------------

/**
 * Wiederverwendbares Teaser-Formular:
 * Titel, Untertitel, Bild, Link, Hintergrundfarbe
 */
if (!class_exists('TeaserCardTemplate')) {
    class TeaserCardTemplate implements TemplateInterface
    {
        /** @param array<string, mixed> $context */
        public function apply(MForm $form, array $context = []): MForm
        {
            $idPrefix  = (string) ($context['id_prefix'] ?? '');
            $withColor = (bool)   ($context['with_color'] ?? true);

            $form
                ->addTextField($idPrefix . 'title', ['label' => 'Überschrift'])
                ->addTextField($idPrefix . 'subtitle', ['label' => 'Untertitel'])
                ->addMediaField($idPrefix . 'image', ['label' => 'Bild', 'types' => 'jpg,png,webp'])
                ->addCustomLinkField($idPrefix . 'link', ['label' => 'Link'])
            ;

            if ($withColor) {
                $form->addColorSwatchField($idPrefix . 'bg', [
                    '#ffffff'       => 'Weiß',
                    '#f8f9fa'       => 'Hellgrau',
                    '#2f77bc'       => 'Blau',
                    '#27ae60'       => 'Grün',
                    '.bg-primary'   => ['label' => 'Primär',   'preview' => '#0d6efd'],
                    '.bg-dark'      => ['label' => 'Dunkel',   'preview' => '#212529'],
                ], ['label' => 'Hintergrundfarbe'], '#ffffff');
            }

            return $form;
        }
    }
}

/**
 * Wiederverwendbares SEO-Meta-Formular:
 * Title-Tag, Meta-Description, OG-Image, Canonical URL
 */
if (!class_exists('SeoMetaTemplate')) {
    class SeoMetaTemplate implements TemplateInterface
    {
        /** @param array<string, mixed> $context */
        public function apply(MForm $form, array $context = []): MForm
        {
            $prefix = (string) ($context['id_prefix'] ?? 'seo_');

            $form
                ->addTextField($prefix . 'title', [
                    'label'  => 'SEO-Titel',
                    'notice' => 'Empfohlen: max. 60 Zeichen. Leer = Seitentitel wird verwendet.',
                ])
                ->addTextAreaField($prefix . 'description', [
                    'label'  => 'Meta-Description',
                    'notice' => 'Empfohlen: 120–160 Zeichen.',
                ])
                ->addMediaField($prefix . 'og_image', [
                    'label'  => 'Open-Graph-Bild',
                    'types'  => 'jpg,png,webp',
                    'notice' => 'Empfohlen: 1200×630 px',
                ])
                ->addTextField($prefix . 'canonical', [
                    'label'  => 'Canonical URL',
                    'notice' => 'Nur ausfüllen wenn abweichend von der aktuellen URL.',
                ])
            ;

            return $form;
        }
    }
}

// ---------------------------------------------------------------------------
// 2. Templates registrieren
//    (In echten Projekten: einmalig in boot.php oder einem Addon-Setup)
// ---------------------------------------------------------------------------

MForm::registerTemplate('teaser_card', TeaserCardTemplate::class);
MForm::registerTemplate('seo_meta',    SeoMetaTemplate::class);

// ---------------------------------------------------------------------------
// 3. Formulare mit Templates zusammenstellen
// ---------------------------------------------------------------------------

$mform = MForm::factory()

    ->addTabElement('Teaser', MForm::factory()

        // Variante A: fromTemplate() erzeugt direkt eine neue MForm-Instanz
        ->addFieldsetArea('Haupt-Teaser', MForm::fromTemplate('teaser_card', [
            'id_prefix'  => 'main_',
            'with_color' => true,
        ]))

        // Variante B: applyTemplate() auf bestehende Instanz anwenden
        ->addFieldsetArea('Neben-Teaser (ohne Farbe)', MForm::factory()->applyTemplate('teaser_card', [
            'id_prefix'  => 'side_',
            'with_color' => false,
        ]))
    )

    ->addTabElement('SEO', MForm::factory()

        // SEO-Template mit angepasstem Key-Prefix
        ->addFieldsetArea('Suchmaschinen-Optimierung', MForm::fromTemplate('seo_meta', [
            'id_prefix' => 'seo_',
        ]))
    )

    ->addTabElement('Repeater + Template', MForm::factory()

        // Template auch als Repeater-Zeile nutzbar
        ->addRepeaterElement(99, MForm::fromTemplate('teaser_card', [
            'id_prefix'  => '',
            'with_color' => true,
        ]), true, true, [
            'label'      => 'Teaser-Kacheln',
            'btn_text'   => 'Kachel hinzufügen',
            'collapsed'  => true,
            'first_open' => true,
            'min'        => 1,
            'max'        => 6,
        ])
    )
;

echo $mform->show();
