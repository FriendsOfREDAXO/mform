<?php

namespace FriendsOfREDAXO\MForm\Api;

/**
 * API-Endpunkt zum Auflösen eines Custom-Link-Wertes in einen Anzeigenamen.
 *
 * Registrierung in boot.php: rex_api_function::register('mform_resolve_link', self::class)
 * Aufruf: index.php?rex-api-call=mform_resolve_link&value=8
 *
 * @package FriendsOfREDAXO\MForm
 */
class ResolveLinkApi extends \rex_api_function
{
    /** @var bool Auch im Frontend verfügbar (wird von JS aus dem Backend-Kontext aufgerufen) */
    protected $published = true;

    public function execute(): \rex_api_result
    {
        \rex_response::cleanOutputBuffers();

        $value = \rex_request('value', 'string', '');

        if ('' === $value) {
            \rex_response::sendJson(['text' => '']);
            exit;
        }

        $text = \rex_var_custom_link::getCustomLinkText($value);

        \rex_response::sendJson(['text' => $text]);
        exit;
    }
}
