<?php
use FriendsOfRedaxo\MForm\Output\MFormOutput;

// === Headline + Intro =====================================================
echo MFormOutput::tag('h2', ['class' => 'display-5 mb-2'], rex_escape('REX_VALUE[1]'));
echo MFormOutput::tag('p',  ['class' => 'lead mb-5'], rex_escape('REX_VALUE[2]'));

// === Sektionen (Repeater) =================================================
echo MFormOutput::from('REX_VALUE[3]')
    ->whenEmpty('<div class="alert alert-info">Noch keine Sektionen gepflegt.</div>')
    ->renderGrid(
        cols: 2,
        framework: MFormOutput::GRID_BOOTSTRAP,
        rowAttrs: ['class' => 'g-4'],
        template: function (array $section): string {
            // Picture mit Responsive-Sources über media_manager (falls Types existieren)
            $image = '' !== ($section['image'] ?? '')
                ? MFormOutput::picture((string) $section['image'], [
                    '(min-width: 1200px)' => 'rex_mediapool_preview',
                    '(min-width: 768px)'  => 'rex_mediapool_preview',
                ], ['class' => 'img-fluid rounded mb-3'])
                : '';

            // Link-Button (auflöst rex-article://, externe URLs, mailto:/tel:)
            $linkBtn = '' !== ($section['link'] ?? '')
                ? MFormOutput::link((string) $section['link'], 'Mehr erfahren', [
                    'class' => 'btn btn-primary mt-3',
                ])
                : '';

            // Galerie als Thumb-Strip
            $gallery = '';
            $mediaItems = MFormOutput::mediaList((string) ($section['gallery'] ?? ''));
            if ([] !== $mediaItems) {
                $thumbs = '';
                foreach ($mediaItems as $media) {
                    $thumbs .= MFormOutput::tag('img', [
                        'src'   => rex_url::media($media->getFileName()),
                        'alt'   => $media->getValue('title') ?? '',
                        'class' => 'me-2 mb-2',
                        'style' => 'max-height:80px;border-radius:4px',
                    ]);
                }
                $gallery = MFormOutput::tag('div', ['class' => 'mt-3 d-flex flex-wrap'], $thumbs);
            }

            // Excerpt aus dem TinyMCE-Text als Vorschau, daneben Vollausgabe
            $teaser = MFormOutput::excerpt((string) ($section['text'] ?? ''), 30);
            $body   = MFormOutput::richtext((string) ($section['text'] ?? ''));

            return MFormOutput::tag('article', ['class' => 'card h-100'],
                $image .
                MFormOutput::tag('div', ['class' => 'card-body'],
                    MFormOutput::tag('h3', ['class' => 'card-title'], rex_escape($section['title'] ?? '')) .
                    ('' !== $teaser ? MFormOutput::tag('p', ['class' => 'text-muted small'], rex_escape($teaser)) : '') .
                    MFormOutput::tag('div', ['class' => 'card-text'], $body) .
                    $gallery .
                    $linkBtn
                )
            );
        }
    );
