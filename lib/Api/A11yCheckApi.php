<?php

namespace FriendsOfREDAXO\MForm\Api;

use FriendsOfRedaxo\MForm\A11y\MediaMetaChecker;
use rex;
use rex_api_exception;
use rex_api_function;
use rex_api_result;
use rex_response;

use function count;
use function is_array;
use function is_string;

/**
 * API-Endpunkt der A11y-Metadaten-Prüfung (#397).
 *
 * Registrierung in boot.php: rex_api_function::register('mform_a11y_check', self::class)
 * Aufruf (POST/GET): index.php?rex-api-call=mform_a11y_check&files=["a.jpg","b.png"]&rules=[{"field":"med_alt","message":""}]
 * Antwort: {"results": [{filename, exists, is_image, edit_url, issues: [{field, message, languages, code}]}]}
 *
 * Nur für angemeldete Backend-Benutzer. Regeln werden serverseitig erneut normalisiert,
 * damit nur med_*- und mediaplace:-Felder geprüft werden.
 */
class A11yCheckApi extends rex_api_function
{
    /** @var bool Auch ausserhalb des Backends erreichbar (Aufruf per JS), Benutzer wird geprüft */
    protected $published = true;

    private const MAX_FILES = 200;

    public function execute(): rex_api_result
    {
        rex_response::cleanOutputBuffers();

        if (null === rex::getUser()) {
            throw new rex_api_exception('Login required.');
        }

        $files = json_decode(\rex_request('files', 'string', '[]'), true);
        $rules = json_decode(\rex_request('rules', 'string', '[]'), true);

        $filenames = [];
        if (is_array($files)) {
            foreach ($files as $file) {
                if (is_string($file) && '' !== trim($file) && count($filenames) < self::MAX_FILES) {
                    $filenames[] = trim($file);
                }
            }
        }

        $normalized = MediaMetaChecker::normalizeRules(is_array($rules) ? $rules : null);
        if (!MediaMetaChecker::isEnabled() || null === $normalized || [] === $filenames) {
            rex_response::sendJson(['results' => []]);
            exit;
        }

        $checker = new MediaMetaChecker();
        rex_response::sendJson(['results' => $checker->checkMany($filenames, $normalized['required_media_meta'])]);
        exit;
    }
}
