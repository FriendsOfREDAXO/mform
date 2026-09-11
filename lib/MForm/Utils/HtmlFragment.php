<?php

namespace FriendsOfRedaxo\MForm\Utils;

use Dom\HTMLDocument;
use Dom\Node;

/**
 * HTML-Fragmente parsen und serialisieren -- ueber den HTML5-Parser von
 * PHP 8.4 (\Dom\HTMLDocument). Ersetzt die frueheren libxml-Workarounds
 * (<?xml encoding="utf-8" ?>-Praefix, utf8_decode(), @-Suppressor, C14N).
 */
final class HtmlFragment
{
    /**
     * Parst ein HTML-Fragment in ein vollstaendiges Dokument; der Inhalt
     * landet im <body>.
     */
    public static function parse(string $html): HTMLDocument
    {
        return HTMLDocument::createFromString(
            '<!DOCTYPE html><html><head><meta charset="utf-8"></head><body>' . $html . '</body></html>',
            LIBXML_NOERROR,
            'UTF-8',
        );
    }

    /**
     * Serialisiert das Fragment wieder: bei einem Dokument der Inhalt des
     * <body>, bei einem Element das Element selbst (inkl. Tag).
     */
    public static function inner(Node $node): string
    {
        if ($node instanceof HTMLDocument) {
            return null !== $node->body ? $node->body->innerHTML : '';
        }
        // Element inkl. Tag -- ueber saveHtml(), outerHTML ist in Dom\Element (8.4) nicht verfuegbar
        $document = $node->ownerDocument;

        return $document instanceof HTMLDocument ? $document->saveHtml($node) : '';
    }
}
