<?php

namespace FriendsOfREDAXO\MForm\Api;

use rex;
use rex_api_exception;
use rex_api_function;
use rex_api_result;
use rex_csrf_token;
use rex_response;
use rex_url;
use rex_var;
use rex_view;
use Throwable;

use function strlen;

/**
 * Live-Vorschau des Form Builders (#407): nimmt den erzeugten Eingabe-Code,
 * laesst ihn wie ein Modul durch rex_var::parse() laufen, fuehrt ihn aus und
 * liefert ein eigenstaendiges HTML-Dokument fuer ein Iframe (Backend-CSS und
 * MForm-Assets, Theme-Klasse am body).
 *
 * Nur fuer Administratoren mit gueltigem CSRF-Token (mform_builder): der Code
 * ist derselbe, den ein Admin ohnehin in ein Modul einfuegen wuerde.
 *
 * Registrierung in boot.php: rex_api_function::register('mform_builder_preview', self::class)
 */
class BuilderPreviewApi extends rex_api_function
{
    protected $published = false;

    private const MAX_CODE_LENGTH = 200000;

    public function execute(): rex_api_result
    {
        rex_response::cleanOutputBuffers();

        $user = rex::getUser();
        if (null === $user || !$user->isAdmin()) {
            throw new rex_api_exception('Admin login required.');
        }
        if (!rex_csrf_token::factory('mform_builder')->isValid()) {
            throw new rex_api_exception('Invalid CSRF token.');
        }

        $code = \rex_request('code', 'string', '');
        $theme = \rex_request('theme', 'string', 'light');
        if ('' === trim($code) || strlen($code) > self::MAX_CODE_LENGTH) {
            rex_response::sendContent($this->document('<div class="alert alert-info">Kein Code.</div>', $theme), 'text/html');
            exit;
        }

        $html = '';
        try {
            $php = rex_var::parse($code, rex_var::ENV_INPUT, 'module', null);
            ob_start();
            try {
                eval('?>' . $php);
            } finally {
                $html = (string) ob_get_clean();
            }
        } catch (Throwable $e) {
            $html .= '<div class="alert alert-danger"><strong>' . rex_escape($e::class) . '</strong><br>' . rex_escape($e->getMessage()) . '</div>';
        }

        rex_response::sendContent($this->document($html, $theme), 'text/html');
        exit;
    }

    /**
     * Eigenstaendiges Dokument mit den im Backend registrierten Assets von Core, be_style und MForm.
     */
    private function document(string $body, string $theme): string
    {
        // Core, be_style, MForm und die Editor-Addons (TinyMCE, CKEditor 5, MarkdownEditor), damit
        // Editor-Textareas in der Vorschau initialisiert werden wie im Modul.
        $wanted = ['/be_style/', '/mform/', '/core/', '/tinymce/', '/cke5/', '/markdowneditor/'];
        $isWanted = static function (string $file) use ($wanted): bool {
            foreach ($wanted as $needle) {
                if (str_contains($file, $needle)) {
                    return true;
                }
            }

            return str_contains($file, 'jquery') || str_contains($file, 'bootstrap');
        };
        $css = '';
        foreach (rex_view::getCssFiles()['all'] ?? [] as $file) {
            if ($isWanted($file)) {
                $css .= '<link rel="stylesheet" href="' . rex_escape($file) . '">' . "\n";
            }
        }
        $js = '';
        foreach (rex_view::getJsFiles() as $file) {
            if ($isWanted($file)) {
                $js .= '<script src="' . rex_escape($file) . '"></script>' . "\n";
            }
        }
        $bodyClass = 'rex-has-theme rex-theme-' . ('dark' === $theme ? 'dark' : 'light') . ' mform-builder-preview';
        $jsProps = (string) json_encode(rex_view::getJsProperties(), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        $lang = substr(\rex_i18n::getLocale(), 0, 2);

        return '<!DOCTYPE html><html lang="' . rex_escape($lang) . '"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><base href="' . rex_escape(rex_url::backendController()) . '">'
            . '<script>var rex = ' . $jsProps . ';</script>' . $css
            . '<style>body{padding:16px;} .mform-builder-preview .rex-page-main-inner{max-width:none;}</style>'
            . '</head><body class="' . $bodyClass . '"><div class="rex-page-main"><div class="rex-page-main-inner"><section class="rex-page-section"><div class="panel panel-edit"><div class="panel-body"><form method="post" onsubmit="return false"><div class="rex-slice-edit">'
            . $body
            . '</div></form></div></div></section></div></div>' . $js
            . '<script>(function(){function h(){var el = document.querySelector(".rex-page-section"); var height = el ? Math.ceil(el.getBoundingClientRect().bottom + window.scrollY) : document.documentElement.scrollHeight; parent.postMessage({mformBuilderPreviewHeight: height}, "*");} if (window.jQuery) { jQuery(function(){ jQuery(document).trigger("rex:ready", [jQuery(document.body)]); setTimeout(h, 200); }); } window.addEventListener("load", h); window.addEventListener("resize", h); window.addEventListener("message", function(e){ if (e.data && e.data.mformBuilderMeasure) { h(); setTimeout(h, 100); } }); setInterval(h, 1500);})();</script>'
            . '</body></html>';
    }
}
