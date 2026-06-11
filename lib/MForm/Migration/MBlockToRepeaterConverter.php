<?php

/**
 * @author Friends Of REDAXO
 * @license MIT
 */

namespace FriendsOfRedaxo\MForm\Migration;

/**
 * Konvertiert MBlock-basierten Modul-Code (Eingabe + Ausgabe) in den
 * MForm-9-Repeater.
 *
 * Das Werkzeug fuehrt ausschliesslich textbasierte, deterministische
 * Transformationen durch. Es erzeugt Vorschlags-Code, der vor dem Einsatz
 * geprueft werden sollte. Komplexe oder mehrdeutige Konstrukte werden nicht
 * automatisch umgeschrieben, sondern als Hinweis ausgegeben.
 *
 * Kern-Transformationen:
 * - Eingabe: Feldnamen-Praefix `1.0.` bzw. `$id.0.` -> sprechender Key
 * - Eingabe: `MBlock::show($id, $form->show(), [...])` -> `addRepeaterElement(...)`
 * - Ausgabe: `rex_var::toArray("REX_VALUE[n]")` -> `MFormRepeaterHelper::decode(n)`
 */
final class MBlockToRepeaterConverter
{
    /** @var list<string> */
    private array $notes = [];

    /** @var list<string> */
    private array $warnings = [];

    /**
     * Konvertiert den Eingabe-Code (input.php) eines MBlock-Moduls.
     *
     * @param string|null $repeaterId Optionale Repeater-Slot-Id (z. B. "1").
     *                                Ohne Angabe wird sie aus dem Code erkannt.
     *
     * @return array{code: string, notes: list<string>, warnings: list<string>}
     */
    public function convertInput(string $code, ?string $repeaterId = null): array
    {
        $this->reset();

        if ('' === trim($code)) {
            $this->warnings[] = 'Kein Eingabe-Code uebergeben.';

            return $this->result($code);
        }

        if (!str_contains($code, 'MBlock')) {
            $this->notes[] = 'Kein `MBlock::show(...)` gefunden. Es werden nur Feldnamen-Praefixe bereinigt.';
        }

        [$idToken, $numericId] = $this->detectRepeaterId($code);
        if (null !== $repeaterId && '' !== trim($repeaterId)) {
            $numericId = trim($repeaterId);
        }

        // 1) Feldnamen-Praefix entfernen ("1.0.header" / "$id.0.header" -> "header").
        $code = $this->stripFieldPrefixes($code, $idToken, $numericId);

        // 2a) Numerische Media-/Link-Felder im Repeater-Formular auf sprechende Keys mappen.
        $code = $this->normalizeNumericRepeaterWidgetKeys($code);

        // 2a.1) $var = MBlock::show(...); ... ->addHTML($var) Pattern inlinen.
        $code = $this->inlineMBlockHtmlPattern($code);

        // 2b) MBlock::show(...) durch addFlexRepeaterElement(...) ersetzen.
        $code = $this->replaceMBlockShow($code, $idToken);

        // 3) Hinweise auf manuell zu pruefende Konstrukte sammeln.
        $this->collectInputWarnings($code);

        return $this->result($code);
    }

    /**
     * Konvertiert den Ausgabe-Code (output.php) eines MBlock-Moduls.
     *
     * Nur der angegebene Repeater-Slot wird auf `MFormRepeaterHelper::decode()`
     * umgestellt. Weitere `rex_var::toArray("REX_VALUE[n]")`-Aufrufe (z. B. fuer
     * gruppierte Einstellungsfelder) bleiben unangetastet.
     *
     * @param string|null $repeaterId Repeater-Slot-Id (Standard "1").
     *
     * @return array{code: string, notes: list<string>, warnings: list<string>}
     */
    public function convertOutput(string $code, ?string $repeaterId = null): array
    {
        $this->reset();

        if ('' === trim($code)) {
            $this->warnings[] = 'Kein Ausgabe-Code uebergeben.';

            return $this->result($code);
        }

        $targetId = (null !== $repeaterId && '' !== trim($repeaterId)) ? trim($repeaterId) : '1';

        $replaced = 0;
        $skipped = [];

        // Nur rex_var::toArray("REX_VALUE[<targetId>]") -> decode(<targetId>).
        $code = (string) preg_replace_callback(
            '/rex_var::toArray\(\s*([\'"])REX_VALUE\[(\d+)\]\1\s*\)/',
            static function (array $m) use (&$replaced, &$skipped, $targetId): string {
                if ($m[2] === $targetId) {
                    ++$replaced;

                    return 'MFormRepeaterHelper::decode(' . $m[2] . ')';
                }
                $skipped[$m[2]] = true;

                return $m[0];
            },
            $code,
        );

        if ($replaced > 0) {
            $this->notes[] = sprintf('%d Aufruf(e) von `rex_var::toArray("REX_VALUE[%s]")` auf `MFormRepeaterHelper::decode(%s)` umgestellt.', $replaced, $targetId, $targetId);
            $this->removeMBlockUseStatements($code);
            $this->ensureRepeaterHelperUse($code);
            $code = $this->addOutputKeyFallbacks($code);
            $this->notes[] = 'Die Datenstruktur ist identisch (`[$index => [feldname => wert]]`), Zugriffe wie `$item[\'header\']` bleiben gueltig.';
        } else {
            $this->notes[] = sprintf('Kein `rex_var::toArray("REX_VALUE[%s]")` gefunden. Pruefe die Repeater-Slot-Id.', $targetId);
        }

        if ([] !== $skipped) {
            $ids = implode(', ', array_keys($skipped));
            $this->notes[] = sprintf('Unveraendert gelassen: `REX_VALUE[%s]` (vermutlich gruppierte Einzel-Einstellungen, keine Repeater).', $ids);
        }

        // Hinweise zu Spezial-Keys im Ausgabe-Code.
        if (preg_match('/\[\s*([\'"])REX_MEDIA_\d+\1\s*\]/', $code) || str_contains($code, 'REX_MEDIA_')) {
            $this->warnings[] = 'Zugriff auf `REX_MEDIA_n` gefunden: Numerische Media-Felder bekommen im Repeater einen sprechenden Namen. Passe den Key entsprechend an (z. B. `$item[\'media\']`).';
        }

        return $this->result($code);
    }

    /**
     * Konvertiert gespeicherte MBlock-Daten in das flache Repeater-JSON.
     *
     * MBlock speichert je Slice-Wert ein Wrapper-Objekt der Form
     * `{"GBS<hash>":{"VALUE":{"<id>":[ {item}, {item} ]}}}`. Der Repeater
     * erwartet hingegen ein flaches Array `[{item}, {item}]`, in dem der
     * Aktiv/Inaktiv-Status pro Item ueber den Key `__disabled` abgebildet wird.
     *
     * Transformation pro Item:
     * - technisches Halte-Feld `checkbox_block_hold` wird entfernt
     * - `mblock_offline == '1'` wird zu `__disabled = true`, sonst kein Flag
     * - alle uebrigen (sprechenden) Keys bleiben unveraendert erhalten
     *
    * @param string|null $repeaterId Repeater-Slot-Id (Standard "1").
    * @param array<int|string, string> $legacyKeyMap Optionales Mapping alter Keys auf neue Repeater-Feldnamen,
    *                                            z. B. ['1' => 'link'].
     *
     * @return array{json: string, count: int, notes: list<string>, warnings: list<string>}
     */
    public function convertData(string $rawValue, ?string $repeaterId = null, array $legacyKeyMap = []): array
    {
        $this->reset();

        $targetId = (null !== $repeaterId && '' !== trim($repeaterId)) ? trim($repeaterId) : '1';

        if ('' === trim($rawValue)) {
            $this->warnings[] = 'Keine Daten uebergeben.';

            return $this->dataResult('', 0);
        }

        $normalized = html_entity_decode($rawValue, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $decoded = json_decode($normalized, true);

        if (!is_array($decoded)) {
            $this->warnings[] = 'Daten sind kein gueltiges JSON. Pruefe den Slice-Wert.';

            return $this->dataResult('', 0);
        }

        $items = $this->extractMBlockItems($decoded, $targetId);
        if (null === $items) {
            $this->warnings[] = sprintf('Konnte keine MBlock-Items fuer Slot %s finden. Erwartet wird `{"GBS<hash>":{"VALUE":{"%s":[...]}}}`.', $targetId, $targetId);

            return $this->dataResult('', 0);
        }

        // Gridblock-Verschachtelung erkennen (mehrere GBS-Wrapper).
        $wrapperCount = $this->countWrappersWithValue($decoded, $targetId);
        if ($wrapperCount > 1) {
            $this->warnings[] = sprintf('%d separate Daten-Spalten gefunden (GBS-Wrapper). Das deutet auf Gridblock-verschachtelte Daten hin. Es wurde nur die erste Spalte konvertiert; verschachtelte Gridblock-Daten muessen separat behandelt werden.', $wrapperCount);
        }

        $migrated = [];
        $offlineCount = 0;
        $holdCount = 0;
        foreach ($items as $item) {
            if (!is_array($item)) {
                continue;
            }

            // Legacy-Media-Key aus numerischen addMediaField()-Definitionen auf sprechenden Key mappen.
            if (array_key_exists('REX_MEDIA_1', $item) && !array_key_exists('media', $item)) {
                $item['media'] = $item['REX_MEDIA_1'];
                unset($item['REX_MEDIA_1']);
                $this->notes[] = 'Legacy-Key `REX_MEDIA_1` auf `media` gemappt.';
            }

            // Legacy-Link-Key aus klassischen Widgets auf sprechenden Key mappen.
            if (array_key_exists('REX_LINK_1', $item) && !array_key_exists('link', $item)) {
                $item['link'] = $item['REX_LINK_1'];
                unset($item['REX_LINK_1']);
                $this->notes[] = 'Legacy-Key `REX_LINK_1` auf `link` gemappt.';
            }

            // Plausibler Default: numerischer Key `1` ist bei Legacy-Repeatern meist das Link-Feld.
            if (array_key_exists('1', $item) && !array_key_exists('link', $item)) {
                $legacyLink = trim((string) $item['1']);
                if ('' !== $legacyLink) {
                    $item['link'] = $item['1'];
                    unset($item['1']);
                    $this->notes[] = 'Plausibilitaetskorrektur: numerischen Legacy-Key `1` auf `link` gemappt.';
                }
            }

            // Leeren numerischen Legacy-Key entfernen (haeufig aus alten Widgets).
            if (array_key_exists('1', $item) && '' === trim((string) $item['1'])) {
                unset($item['1']);
            }

            foreach ($legacyKeyMap as $oldKey => $newKey) {
                $oldKey = trim((string) $oldKey);
                $newKey = trim((string) $newKey);
                if ('' === $oldKey || '' === $newKey) {
                    continue;
                }
                if (array_key_exists($oldKey, $item) && !array_key_exists($newKey, $item)) {
                    $item[$newKey] = $item[$oldKey];
                    unset($item[$oldKey]);
                    $this->notes[] = sprintf('Legacy-Key `%s` auf `%s` gemappt.', $oldKey, $newKey);
                }
            }

            if (array_key_exists('checkbox_block_hold', $item)) {
                unset($item['checkbox_block_hold']);
                ++$holdCount;
            }

            $disabled = false;
            if (array_key_exists('mblock_offline', $item)) {
                $disabled = ('1' === (string) $item['mblock_offline']);
                unset($item['mblock_offline']);
            }
            if ($disabled) {
                $item['__disabled'] = true;
                ++$offlineCount;
            }

            $migrated[] = $item;
        }

        $json = (string) json_encode($migrated, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        $this->notes[] = sprintf('%d Item(s) aus Slot %s konvertiert.', count($migrated), $targetId);
        if ($offlineCount > 0) {
            $this->notes[] = sprintf('%d Item(s) waren offline (`mblock_offline`) und wurden als `__disabled` markiert.', $offlineCount);
        }
        if (0 === $holdCount && 0 === $offlineCount && $this->isListOfArrays($decoded)) {
            $this->notes[] = 'Keine MBlock-Marker (`checkbox_block_hold`/`mblock_offline`) gefunden. Die Daten sind vermutlich bereits im Repeater-Format.';
        }

        // Problematische Daten-Keys melden (z. B. REX_MEDIA_1, rein numerische Keys).
        $problemKeys = [];
        $first = $migrated[0] ?? [];
        foreach (array_keys($first) as $key) {
            $keyStr = (string) $key;
            if (str_starts_with($keyStr, 'REX_MEDIA_') || str_starts_with($keyStr, 'REX_LINK_') || preg_match('/^\d+$/', $keyStr)) {
                $problemKeys[$keyStr] = true;
            }
        }
        if ([] !== $problemKeys) {
            $this->warnings[] = sprintf(
                'Daten-Keys ohne sprechenden Namen gefunden: `%s`. Diese muessen auf die neuen Repeater-Feldnamen gemappt werden, sonst landen die Werte nicht im richtigen Feld.',
                implode('`, `', array_keys($problemKeys)),
            );
        }

        $this->notes[] = 'Schreibe das Ergebnis in dieselbe `valueN`-Spalte des Slices (gleiche Slot-Id). Vorher immer ein DB-Backup erstellen.';

        return $this->dataResult($json, count($migrated));
    }

    /**
     * Extrahiert die Item-Liste aus einer dekodierten MBlock-Struktur.
     *
     * Unterstuetzt mehrere Auspraegungen:
     * - `{"GBS<hash>":{"VALUE":{"<id>":[...]}}}`
     * - `{"VALUE":{"<id>":[...]}}`
     * - bereits flaches Array `[...]` (gibt es unveraendert zurueck)
     *
     * @param array<mixed> $decoded
     * @return array<int, mixed>|null
     */
    private function extractMBlockItems(array $decoded, string $targetId): ?array
    {
        // Reines MBlock-Format: flaches Array von Items (Standardfall ohne Gridblock).
        if ($this->isListOfArrays($decoded)) {
            return array_values($decoded);
        }

        // Direktes VALUE-Objekt.
        if (isset($decoded['VALUE']) && is_array($decoded['VALUE'])) {
            $value = $decoded['VALUE'];
            if (isset($value[$targetId]) && is_array($value[$targetId])) {
                return array_values($value[$targetId]);
            }
        }

        // GBS-Wrapper: erstes Element mit VALUE-Schluessel.
        foreach ($decoded as $wrapper) {
            if (is_array($wrapper) && isset($wrapper['VALUE']) && is_array($wrapper['VALUE'])) {
                $value = $wrapper['VALUE'];
                if (isset($value[$targetId]) && is_array($value[$targetId])) {
                    return array_values($value[$targetId]);
                }
            }
        }

        return null;
    }

    /**
     * Zaehlt, wie viele GBS-Wrapper einen VALUE-Eintrag fuer die Slot-Id enthalten.
     *
     * @param array<mixed> $decoded
     */
    private function countWrappersWithValue(array $decoded, string $targetId): int
    {
        // Direktes VALUE-Objekt zaehlt als ein Wrapper.
        if (isset($decoded['VALUE']) && is_array($decoded['VALUE'])) {
            return isset($decoded['VALUE'][$targetId]) ? 1 : 0;
        }

        $count = 0;
        foreach ($decoded as $wrapper) {
            if (is_array($wrapper) && isset($wrapper['VALUE']) && is_array($wrapper['VALUE']) && isset($wrapper['VALUE'][$targetId])) {
                ++$count;
            }
        }

        return $count;
    }

    /**
     * Prueft, ob ein Array eine Liste von Item-Arrays ist (0-basiert, alle Werte Arrays).
     *
     * @param array<mixed> $value
     */
    private function isListOfArrays(array $value): bool
    {
        if ([] === $value || !array_is_list($value)) {
            return false;
        }
        foreach ($value as $item) {
            if (!is_array($item)) {
                return false;
            }
        }

        return true;
    }

    /**
     * @return array{json: string, count: int, notes: list<string>, warnings: list<string>}
     */
    private function dataResult(string $json, int $count): array
    {
        return [
            'json' => $json,
            'count' => $count,
            'notes' => $this->notes,
            'warnings' => $this->warnings,
        ];
    }

    /**
     * Ermittelt den Repeater-Identifier aus dem Code.
     *
     * Liefert [idToken, numericId], z. B. ['$id', '1'] oder ['1', '1'].
     *
     * @return array{0: string, 1: string}
     */
    private function detectRepeaterId(string $code): array
    {
        $idToken = '$id';
        $numericId = '1';

        // MBlock::show($id, ...) -> Token aus erstem Argument.
        if (preg_match('/MBlock::show\(\s*(\$\w+|\d+)\s*,/', $code, $m)) {
            $idToken = $m[1];
        }

        // $id = 1; -> numerischen Wert aufloesen, falls Token eine Variable ist.
        if (str_starts_with($idToken, '$')) {
            $var = substr($idToken, 1);
            if (preg_match('/\$' . preg_quote($var, '/') . '\s*=\s*(\d+)\s*;/', $code, $m)) {
                $numericId = $m[1];
            }
        } else {
            $numericId = $idToken;
        }

        return [$idToken, $numericId];
    }

    /**
     * Entfernt das Repeater-Praefix aus Feldnamen-Strings.
     *
     * Behandelt sowohl die Variablen-Schreibweise ("$id.0.header") als auch
     * die numerische Schreibweise ("1.0.header"). Andere Slot-Ids (z. B.
     * Einstellungsfelder "2.0.gutterWidth") bleiben unangetastet.
     */
    private function stripFieldPrefixes(string $code, string $idToken, string $numericId): string
    {
        $alternatives = [];
        if (str_starts_with($idToken, '$')) {
            // "$id.0." innerhalb doppelter Quotes (Interpolation).
            $alternatives[] = preg_quote($idToken, '/') . '\.0\.';
        }
        $alternatives[] = preg_quote($numericId, '/') . '\.0\.';
        $alternatives = array_values(array_unique($alternatives));

        $prefixPattern = '(?:' . implode('|', $alternatives) . ')';

        $stripped = 0;
        $code = (string) preg_replace_callback(
            '/([\'"])' . $prefixPattern . '([^\'"]+)\1/',
            static function (array $m) use (&$stripped): string {
                ++$stripped;

                // Einheitlich auf einfache Quotes normalisieren.
                return "'" . $m[2] . "'";
            },
            $code,
        );

        if ($stripped > 0) {
            $this->notes[] = sprintf('%d Feldnamen-Praefix(e) (`%s.0.` / `%s.0.`) entfernt.', $stripped, $idToken, $numericId);
        }

        return $code;
    }

    /**
     * Behandelt das Muster:
     *   $mm = MBlock::show($id, $MBlock->show(), [...]);
     *   ...->addHTML($mm)...
     *
     * Dabei wird:
     *   1. `$mm = MBlock::show(...)` durch einen Kommentar ersetzt (die Variable entfaellt)
     *   2. `->addHTML($mm)` durch `->addFlexRepeaterElement($id, $form, [...])` ersetzt
     *
     * Ohne diese Transformation wuerde der FlexRepeater als ->show()-String in addHTML() enden
     * und der aeussere MForm-Aufbau wuerde ihn nur als HTML-String einbetten.
     */
    private function inlineMBlockHtmlPattern(string $code): string
    {
        // Suche: $someVar = MBlock::show($id, $formVar->show(), [...]) oder array(...);
        $pattern = '/(\$(\w+))\s*=\s*MBlock::show\(\s*(\$\w+|\d+)\s*,\s*(\$\w+)->show\(\)\s*(?:,\s*(\[[^\]]*\]|array\s*\([^)]*\)))?\s*\)\s*;/s';
        if (!preg_match($pattern, $code, $m)) {
            return $code;
        }

        $assignVar = $m[1];  // z.B. $mm
        $id        = $m[3];  // z.B. $id oder 1
        $formVar   = $m[4];  // z.B. $MBlock
        $rawOpts   = $m[5] ?? '';
        $options   = $this->mapMBlockOptions($rawOpts);

        $repeaterCall = 'addFlexRepeaterElement(' . $id . ', ' . $formVar
            . ('' !== $options ? ', ' . $options : '') . ')';

        // addHTML($mm) -> addFlexRepeaterElement(...)
        $escapedVar = preg_quote($assignVar, '/');
        $codeNew = (string) preg_replace(
            '/->addHTML\(\s*' . $escapedVar . '\s*\)/',
            '->' . $repeaterCall,
            $code,
            -1,
            $replaceCount,
        );

        if ((int) $replaceCount > 0) {
            // Urspruengliche Zuweisung entfernen (durch Kommentar ersetzen, damit Kontext sichtbar bleibt)
            $codeNew = (string) preg_replace(
                '/' . $escapedVar . '\s*=\s*MBlock::show\([^;]+\)\s*;/s',
                '// [mform-migration] $mm-Variable inlined in ->addFlexRepeaterElement() oben',
                $codeNew,
            );
            $this->notes[] = sprintf(
                '`%s = MBlock::show(...)` + `->addHTML(%s)` erkannt: Repeater direkt in den Tab-Baum eingebettet (addHTML-Pattern).',
                $assignVar,
                $assignVar,
            );
        }

        return $codeNew;
    }

    /**
     * Ersetzt `MBlock::show($id, $form->show(), [...])` durch
     * `MForm::factory()->addFlexRepeaterElement($id, $form, [...])->show()`.
     *
     * Es wird `addFlexRepeaterElement()` verwendet, weil dort das
     * Options-Array der dritte Parameter ist. Bei `addRepeaterElement()`
     * waeren die ersten beiden zusaetzlichen Parameter `bool $open` und
     * `bool $confirmDelete`.
     */
    private function replaceMBlockShow(string $code, string $idToken): string
    {
        $count = 0;
        $code = (string) preg_replace_callback(
            '/MBlock::show\(\s*(\$\w+|\d+)\s*,\s*(\$\w+)->show\(\)\s*(?:,\s*(\[[^\]]*\]|array\s*\([^)]*\)))?\s*\)/s',
            function (array $m) use (&$count): string {
                ++$count;
                $id = $m[1];
                $formVar = $m[2];
                $rawOptions = $m[3] ?? '';

                $options = $this->mapMBlockOptions($rawOptions);

                $call = 'MForm::factory()->addFlexRepeaterElement(' . $id . ', ' . $formVar;
                if ('' !== $options) {
                    $call .= ', ' . $options;
                }
                $call .= ')->show()';

                return $call;
            },
            $code,
        );

        if ($count > 0) {
            $this->notes[] = sprintf('%d `MBlock::show(...)`-Aufruf(e) auf `addFlexRepeaterElement(...)` umgestellt (`$form->show()` -> `$form`).', $count);
        }

        return $code;
    }

    /**
     * Uebersetzt das MBlock-Options-Array in Repeater-Optionen.
     */
    private function mapMBlockOptions(string $rawOptions): string
    {
        $rawOptions = trim($rawOptions);
        if ('' === $rawOptions) {
            return '';
        }

        $options = [];

        if (preg_match('/[\'"]max[\'"]\s*=>\s*(\d+)/', $rawOptions, $m)) {
            $options[] = "'max' => " . $m[1];
        }
        if (preg_match('/[\'"]min[\'"]\s*=>\s*(\d+)/', $rawOptions, $m)) {
            $options[] = "'min' => " . $m[1];
        }

        // Unbekannte MBlock-Optionen melden (z. B. smooth_link_top, sortable etc.).
        if (preg_match_all('/[\'"]([a-zA-Z_][\w]*)[\'"]\s*=>/', $rawOptions, $mm)) {
            foreach ($mm[1] as $key) {
                if (!in_array($key, ['min', 'max'], true)) {
                    $this->warnings[] = sprintf('MBlock-Option `%s` hat keine direkte Repeater-Entsprechung und wurde verworfen. Bitte pruefen.', $key);
                }
            }
        }

        if ([] === $options) {
            return '';
        }

        return '[' . implode(', ', $options) . ']';
    }

    /**
     * Sammelt Hinweise zu Konstrukten, die im Repeater manuell angepasst werden muessen.
     */
    private function collectInputWarnings(string $code): void
    {
        // Numerische Media-/Link-Felder (REX_MEDIA[n] / REX_LINK[n]) im Repeater.
        if (preg_match('/addMediaField\(\s*(\$\w+|\d+)\s*,/', $code)) {
            $this->warnings[] = 'Numerisches `addMediaField(n, ...)` gefunden: Im Repeater muss das Feld einen sprechenden Namen erhalten (z. B. `addMediaField(\'media\', ...)`), da `REX_MEDIA[n]`-Slots dort nicht funktionieren.';
        }
        if (preg_match('/addLinkField\(\s*(\$\w+|\d+)\s*,/', $code) || preg_match('/addLinkField\(\s*(\$\w+|\d+)\s*\)/', $code)) {
            $this->warnings[] = 'Numerisches `addLinkField(n, ...)` gefunden: Im Repeater einen sprechenden Namen vergeben (z. B. `addLinkField(\'link\', ...)`).';
        }

        if (str_contains($code, 'useCustomLinkForClassicWidgets')) {
            $this->notes[] = '`MForm::useCustomLinkForClassicWidgets()` erkannt: Im Repeater empfehlenswert, damit klassische Media-/Link-Widgets robust klonen.';
        }

        if (str_contains($code, 'cke5-editor')) {
            $this->notes[] = 'CKE5-Editor-Felder erkannt: Der Repeater initialisiert CKE5 beim Klonen automatisch (keine Anpassung noetig).';
        }

        $this->ensureMFormUse($code);
    }

    /**
     * Mapped numerische Widget-Keys im eigentlichen Repeater-Formular auf
     * sprechende Namen. Aktuell bewusst konservativ fuer die gaengigsten
     * Legacy-Faelle.
     */
    private function normalizeNumericRepeaterWidgetKeys(string $code): string
    {
        $countMedia = 0;
        $countLink = 0;

        // Repeater-Form-Variable aus MBlock::show($id, $formVar->show(), ...) ermitteln.
        if (!preg_match('/MBlock::show\(\s*(\$\w+|\d+)\s*,\s*(\$\w+)->show\(\)/', $code, $m)) {
            return $code;
        }

        $formVar = $m[2];
        $pattern = '/' . preg_quote($formVar, '/') . '\s*=\s*MForm::factory\(\).*?;/s';

        $code = (string) preg_replace_callback(
            $pattern,
            static function (array $mm) use (&$countMedia, &$countLink): string {
                $block = $mm[0];
                $block = (string) preg_replace('/->addMediaField\(\s*1\s*,/m', '->addMediaField(\'media\',', $block, -1, $countMedia);
                $block = (string) preg_replace('/->addLinkField\(\s*1\s*(,|\))/m', '->addLinkField(\'link\'$1', $block, -1, $countLink);

                return $block;
            },
            $code,
            1,
        );

        if ($countMedia > 0) {
            $this->notes[] = sprintf('%d numerische(s) `addMediaField(1, ...)` auf `addMediaField(\'media\', ...)` umgestellt.', $countMedia);
        }
        if ($countLink > 0) {
            $this->notes[] = sprintf('%d numerische(s) `addLinkField(1, ...)` auf `addLinkField(\'link\', ...)` umgestellt.', $countLink);
        }

        return $code;
    }

    /**
     * Macht Output-Zugriffe auf Legacy-Keys rueckwaertskompatibel.
     */
    private function addOutputKeyFallbacks(string $code): string
    {
        $mediaFallbacks = 0;
        $linkFallbacks = 0;
        $numericLinkFallbacks = 0;

        // Legacy REX_MEDIA_1 -> bevorzugt neuer Key `media`.
        $code = (string) preg_replace_callback(
            '/(\$\w+)\[\s*([\'\"])REX_MEDIA_1\2\s*\]/',
            static function (array $m) use (&$mediaFallbacks): string {
                ++$mediaFallbacks;
                $var = $m[1];

                return '(' . $var . '[\'media\'] ?? (' . $var . '[\'REX_MEDIA_1\'] ?? \'\'))';
            },
            $code,
        );

        if ($mediaFallbacks > 0) {
            $this->notes[] = sprintf('%d Output-Zugriff(e) auf `REX_MEDIA_1` mit Fallback `media`/`REX_MEDIA_1` versehen.', $mediaFallbacks);
        }

        // Legacy REX_LINK_1 -> bevorzugt neuer Key `link`.
        $code = (string) preg_replace_callback(
            '/(\$\w+)\[\s*([\'\"])REX_LINK_1\2\s*\]/',
            static function (array $m) use (&$linkFallbacks): string {
                ++$linkFallbacks;
                $var = $m[1];

                return '(' . $var . '[\'link\'] ?? (' . $var . '[\'REX_LINK_1\'] ?? \'\'))';
            },
            $code,
        );

        if ($linkFallbacks > 0) {
            $this->notes[] = sprintf('%d Output-Zugriff(e) auf `REX_LINK_1` mit Fallback `link`/`REX_LINK_1` versehen.', $linkFallbacks);
        }

        // Numerischer Legacy-Link-Key (z. B. $item[1]) -> sprechender Key `link`.
        $itemVars = [];
        if (preg_match_all('/foreach\s*\(\s*[^)]*?\sas\s+(?:\$\w+\s*=>\s*)?(\$\w+)\s*\)/', $code, $foreachMatches)) {
            foreach ($foreachMatches[1] as $itemVar) {
                $itemVars[$itemVar] = true;
            }
        }

        foreach (array_keys($itemVars) as $itemVar) {
            $pattern = '/' . preg_quote($itemVar, '/') . '\[\s*1\s*\]/';
            $replacement = '(' . $itemVar . '[\'link\'] ?? (' . $itemVar . '[\'1\'] ?? \'\'))';
            $code = (string) preg_replace($pattern, $replacement, $code, -1, $count);
            $numericLinkFallbacks += (int) $count;
        }

        if ($numericLinkFallbacks > 0) {
            $this->notes[] = sprintf('%d numerische Output-Zugriff(e) auf `[1]` mit Fallback `link`/`1` versehen.', $numericLinkFallbacks);
        }

        return $code;
    }

    private function ensureRepeaterHelperUse(string &$code): void
    {
        if (!str_contains($code, 'MFormRepeaterHelper')) {
            return;
        }
        $useStatement = 'use FriendsOfRedaxo\\MForm\\Repeater\\MFormRepeaterHelper;';
        if (preg_match('/use\s+FriendsOfRedaxo\\\\MForm\\\\Repeater\\\\MFormRepeaterHelper\s*;/', $code)) {
            return; // bereits vorhanden
        }
        // Nach dem letzten use-Statement einfuegen; falls keins da ist: nach <?php.
        if (preg_match_all('/^\s*use\s+[^;]+;\s*$/m', $code, $matches, PREG_OFFSET_CAPTURE) && [] !== $matches[0]) {
            $last = $matches[0][count($matches[0]) - 1];
            $insertPos = $last[1] + strlen($last[0]);
            $code = substr($code, 0, $insertPos) . "\n" . $useStatement . substr($code, $insertPos);
        } elseif (($phpPos = strpos($code, '<?php')) !== false) {
            $afterPhp = $phpPos + 5;
            $code = substr($code, 0, $afterPhp) . "\n" . $useStatement . substr($code, $afterPhp);
        }
        $this->notes[] = '`use FriendsOfRedaxo\\MForm\\Repeater\\MFormRepeaterHelper;` automatisch ergaenzt.';
    }

    private function removeMBlockUseStatements(string &$code): void
    {
        $code = (string) preg_replace(
            '/^\s*use\s+[^;]*\\bMBlock\\b[^;]*;\s*$\n?/mi',
            '',
            $code,
            -1,
            $removed,
        );
        if ((int) $removed > 0) {
            $this->notes[] = sprintf('%d MBlock-`use`-Statement(s) entfernt.', (int) $removed);
        }
    }

    private function ensureMFormUse(string $code): void
    {
        if (str_contains($code, 'MForm::factory') && !preg_match('/use\s+FriendsOfRedaxo\\\\MForm\s*;/', $code)) {
            $this->notes[] = 'Stelle sicher, dass `use FriendsOfRedaxo\\MForm;` im Code vorhanden ist.';
        }
    }

    private function reset(): void
    {
        $this->notes = [];
        $this->warnings = [];
    }

    /**
     * @return array{code: string, notes: list<string>, warnings: list<string>}
     */
    private function result(string $code): array
    {
        return [
            'code' => $code,
            'notes' => $this->notes,
            'warnings' => $this->warnings,
        ];
    }
}
