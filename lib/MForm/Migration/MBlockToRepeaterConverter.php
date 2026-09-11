<?php

/**
 * @author Friends Of REDAXO
 * @license MIT
 */

namespace FriendsOfRedaxo\MForm\Migration;

use function array_key_exists;
use function count;
use function in_array;
use function is_array;
use function sprintf;
use function strlen;

/**
 * Konvertiert MBlock-basierten Modul-Code (Eingabe + Ausgabe) und gespeicherte
 * MBlock-Daten in den MForm-Repeater.
 *
 * Das Werkzeug fuehrt ausschliesslich textbasierte, deterministische
 * Transformationen durch. Es erzeugt Vorschlags-Code, der vor dem Einsatz
 * geprueft werden sollte. Komplexe oder mehrdeutige Konstrukte werden nicht
 * automatisch umgeschrieben, sondern als Hinweis ausgegeben.
 *
 * Kern-Transformationen:
 * - Eingabe: Feldnamen-Praefix `1.0.` bzw. `$id.0.` -> sprechender Key (alle Slots)
 * - Eingabe: numerische Widgets (`addMediaField(1)`) -> Key aus der Legacy-Key-Map
 * - Eingabe: `MBlock::show($id, $form->show(), [...])` -> `addFlexRepeaterElement(...)`
 * - Eingabe: Hidden-Feld `mblock_offline` entfaellt (Repeater bringt Online/Offline mit)
 * - Ausgabe: `rex_var::toArray("REX_VALUE[n]")` -> `MFormRepeaterHelper::decode(n)`
 * - Daten: GBS-Wrapper aufloesen, Legacy-Keys mappen, `mblock_offline` -> `__disabled`,
 *   verschachtelte MBlock-Listen rekursiv
 *
 * @phpstan-import-type Analysis from MBlockModuleAnalyzer
 */
final class MBlockToRepeaterConverter
{
    /** @var list<string> */
    private array $notes = [];

    /** @var list<string> */
    private array $warnings = [];

    private MBlockModuleAnalyzer $analyzer;

    public function __construct(?MBlockModuleAnalyzer $analyzer = null)
    {
        $this->analyzer = $analyzer ?? new MBlockModuleAnalyzer();
    }

    public function getAnalyzer(): MBlockModuleAnalyzer
    {
        return $this->analyzer;
    }

    /**
     * Konvertiert den Eingabe-Code (input.php) eines MBlock-Moduls.
     *
     * Alle `MBlock::show()`-Aufrufe werden umgestellt. `$repeaterId` ist nur noch
     * ein Fallback, wenn der Slot nicht aus dem Code hervorgeht.
     *
     * @param Analysis|null $analysis Vorab berechnete Analyse (sonst intern)
     *
     * @return array{code: string, notes: list<string>, warnings: list<string>}
     */
    public function convertInput(string $code, ?string $repeaterId = null, ?array $analysis = null): array
    {
        $this->reset();

        if ('' === trim($code)) {
            $this->warn('Kein Eingabe-Code uebergeben.');

            return $this->result($code);
        }

        $analysis ??= $this->analyzer->analyze($code);

        if (!$analysis['has_mblock']) {
            $this->note('Kein `MBlock::show(...)` gefunden. Es werden nur Feldnamen-Praefixe bereinigt.');
        }

        foreach ($analysis['warnings'] as $warning) {
            $this->warn($warning);
        }

        // Slots/Id-Tokens, deren Praefixe entfernt werden.
        $pairs = [];
        foreach ($analysis['calls'] as $call) {
            $pairs[$call['id_token'] . '|' . $call['slot']] = [$call['id_token'], $call['slot']];
        }
        if ([] === $pairs) {
            [$idToken, $numericId] = $this->detectRepeaterId($code);
            if (null !== $repeaterId && '' !== trim($repeaterId)) {
                $numericId = trim($repeaterId);
            }
            $pairs[$idToken . '|' . $numericId] = [$idToken, $numericId];
        }

        // 1) Numerische Widget-Keys je Formular auf die abgeleiteten Zielnamen mappen (vor dem Praefix-Strip,
        //    damit "$id.0.1" noch als Custom-Link-Feld erkennbar ist).
        $code = $this->normalizeNumericWidgetKeys($code, $analysis);

        // 2) Praefixe "$id.0." / "1.0." entfernen (alle Slots).
        foreach ($pairs as [$idToken, $numericId]) {
            $code = $this->stripFieldPrefixes($code, $idToken, $numericId);
        }

        // 3) Hidden-Feld mblock_offline entfernen.
        $code = $this->removeOfflineHiddenField($code);

        // 4) $var = MBlock::show(...); ... ->addHtml($var) Pattern inlinen (alle Vorkommen).
        $code = $this->inlineMBlockHtmlPattern($code);

        // 5) Restliche MBlock::show(...) durch MForm::factory()->addFlexRepeaterElement(...)->show() ersetzen.
        $code = $this->replaceMBlockShow($code);

        // 6) use-Statements fuer MBlock entfernen.
        $this->removeMBlockUseStatements($code);

        // 7) Hinweise auf manuell zu pruefende Konstrukte sammeln.
        $this->collectInputWarnings($code, $analysis);

        return $this->result($code);
    }

    /**
     * Konvertiert den Ausgabe-Code (output.php) eines MBlock-Moduls.
     *
     * Die angegebenen Repeater-Slots werden auf `MFormRepeaterHelper::decode()`
     * umgestellt. Weitere `rex_var::toArray("REX_VALUE[n]")`-Aufrufe (z. B. fuer
     * gruppierte Einstellungsfelder) bleiben unangetastet.
     *
     * @param string|null $repeaterId Repeater-Slot-Id (Standard "1"), auch als Liste "1,3"
     * @param array<string, array<string, string>> $keyMaps Legacy-Key-Map je Slot fuer Output-Fallbacks
     *
     * @return array{code: string, notes: list<string>, warnings: list<string>}
     */
    public function convertOutput(string $code, ?string $repeaterId = null, array $keyMaps = []): array
    {
        $this->reset();

        if ('' === trim($code)) {
            $this->warn('Kein Ausgabe-Code uebergeben.');

            return $this->result($code);
        }

        $targets = $this->parseSlotList($repeaterId);

        $replaced = 0;
        $skipped = [];

        $code = (string) preg_replace_callback(
            '/rex_var::toArray\(\s*([\'"])REX_VALUE\[(\d+)\]\1\s*\)/',
            static function (array $m) use (&$replaced, &$skipped, $targets): string {
                if (in_array($m[2], $targets, true)) {
                    ++$replaced;

                    return 'MFormRepeaterHelper::decode(' . $m[2] . ')';
                }
                $skipped[$m[2]] = true;

                return $m[0];
            },
            $code,
        );

        if ($replaced > 0) {
            $this->note(sprintf('%d Aufruf(e) von `rex_var::toArray("REX_VALUE[%s]")` auf `MFormRepeaterHelper::decode()` umgestellt.', $replaced, implode('|', $targets)));
            $this->removeMBlockUseStatements($code);
            $this->ensureRepeaterHelperUse($code);
            $code = $this->addOutputKeyFallbacks($code, $keyMaps);
            $this->note('Die Datenstruktur ist identisch (`[$index => [feldname => wert]]`), Zugriffe wie `$item[\'header\']` bleiben gueltig.');
        } else {
            $this->note(sprintf('Kein `rex_var::toArray("REX_VALUE[%s]")` gefunden. Pruefe die Repeater-Slot-Id.', implode('|', $targets)));
        }

        if ([] !== $skipped) {
            $ids = implode(', ', array_keys($skipped));
            $this->note(sprintf('Unveraendert gelassen: `REX_VALUE[%s]` (vermutlich gruppierte Einzel-Einstellungen, keine Repeater).', $ids));
        }

        if (preg_match('/REX_(MEDIA|LINK|MEDIALIST|LINKLIST)_\d+/', $code)) {
            $this->warn('Zugriff auf `REX_MEDIA_n`/`REX_LINK_n` gefunden: Numerische Felder bekommen im Repeater einen sprechenden Namen. Fallbacks wurden ergaenzt, bitte die Keys bereinigen.');
        }

        return $this->result($code);
    }

    /**
     * Konvertiert gespeicherte MBlock-Daten in das flache Repeater-JSON.
     *
     * MBlock speichert je Slice-Wert entweder ein flaches Array `[{item}, {item}]`
     * oder (mit Gridblock) ein Wrapper-Objekt `{"GBS<hash>":{"VALUE":{"<id>":[...]}}}`.
     * Der Repeater erwartet ein flaches Array, in dem der Aktiv/Inaktiv-Status pro
     * Item ueber den Key `__disabled` abgebildet wird.
     *
     * Transformation pro Item:
     * - technisches Halte-Feld `checkbox_block_hold` wird entfernt
     * - `mblock_offline == '1'` wird zu `__disabled = true`, sonst kein Flag
     * - Legacy-Keys werden ueber `$legacyKeyMap` (und Standard-Heuristiken) gemappt
     * - Listen von Item-Arrays innerhalb eines Items (verschachteltes MBlock)
     *   werden rekursiv nach denselben Regeln konvertiert
     *
     * @param string|null $repeaterId Repeater-Slot-Id (Standard "1")
     * @param array<int|string, string> $legacyKeyMap Mapping alter Keys auf neue Repeater-Feldnamen, z. B. ['REX_MEDIA_1' => 'media', '1' => 'link']
     * @param array{merge_columns?: bool, nested?: bool, list_fields?: array<string, string>, check_existence?: bool} $options
     *        merge_columns: mehrere GBS-Wrapper (Gridblock-Spalten) der Reihe nach zusammenfuehren statt nur die erste zu nehmen;
     *        nested: verschachtelte Listen konvertieren (Standard true);
     *        list_fields: Feldname => 'media'|'link' fuer Medialist-/Linklist-Werte (M5: normalisieren, Existenz pruefen);
     *        check_existence: Dateien/Artikel gegen Medienpool und Struktur pruefen (Standard true, braucht REDAXO)
     *
     * Mehrsprachige Werte (M6): ein Objekt, dessen Schluessel Sprach-Ids sind und dessen Werte
     * MBlock-Listen, wird je Sprache konvertiert; die Struktur bleibt erhalten.
     *
     * @return array{json: string, count: int, notes: list<string>, warnings: list<string>}
     */
    public function convertData(string $rawValue, ?string $repeaterId = null, array $legacyKeyMap = [], array $options = []): array
    {
        $this->reset();

        $targetId = (null !== $repeaterId && '' !== trim($repeaterId)) ? trim($repeaterId) : '1';
        $mergeColumns = (bool) ($options['merge_columns'] ?? false);
        $nested = (bool) ($options['nested'] ?? true);
        $listFields = [];
        foreach ($options['list_fields'] ?? [] as $name => $kind) {
            if (in_array($kind, ['media', 'link'], true)) {
                $listFields[(string) $name] = $kind;
            }
        }
        $checkExistence = (bool) ($options['check_existence'] ?? true);

        if ('' === trim($rawValue)) {
            $this->warn('Keine Daten uebergeben.');

            return $this->dataResult('', 0);
        }

        $normalized = html_entity_decode($rawValue, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $decoded = json_decode($normalized, true);

        if (!is_array($decoded)) {
            $this->warn('Daten sind kein gueltiges JSON. Pruefe den Slice-Wert.');

            return $this->dataResult('', 0);
        }

        // M6: Sprach-Arrays {clangId: [items]} je Sprache konvertieren, Struktur behalten.
        if ($this->isLanguageArray($decoded)) {
            $stats = ['offline' => 0, 'hold' => 0, 'mapped' => [], 'nested' => 0, 'lists' => 0, 'missing' => []];
            $perLanguage = [];
            $total = 0;
            foreach ($decoded as $clangId => $languageItems) {
                $items = is_array($languageItems) ? ($this->extractMBlockItems($languageItems, $targetId) ?? []) : [];
                $perLanguage[(string) $clangId] = $this->convertItems($items, $legacyKeyMap, $nested, $stats, 0, $listFields, $checkExistence);
                $total += count($perLanguage[(string) $clangId]);
            }
            $this->note(sprintf('Mehrsprachiger Wert: %d Sprache(n) je Sprache konvertiert (%d Items).', count($perLanguage), $total));
            $this->reportStats($stats);

            return $this->dataResult((string) json_encode($perLanguage, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), $total);
        }

        $wrapperCount = $this->countWrappersWithValue($decoded, $targetId);
        if ($wrapperCount > 1 && $mergeColumns) {
            $items = $this->extractAllWrapperItems($decoded, $targetId);
            $this->note(sprintf('%d GBS-Wrapper (Gridblock-Spalten) der Reihe nach zusammengefuehrt.', $wrapperCount));
        } else {
            $items = $this->extractMBlockItems($decoded, $targetId);
            if ($wrapperCount > 1) {
                $this->warn(sprintf('%d separate Daten-Spalten gefunden (GBS-Wrapper). Es wurde nur die erste Spalte konvertiert. Mit der Option "Spalten zusammenfuehren" werden alle Spalten in einen Repeater uebernommen.', $wrapperCount));
            }
        }

        if (null === $items) {
            $this->warn(sprintf('Konnte keine MBlock-Items fuer Slot %s finden. Erwartet wird ein Item-Array oder `{"GBS<hash>":{"VALUE":{"%s":[...]}}}`.', $targetId, $targetId));

            return $this->dataResult('', 0);
        }

        $stats = ['offline' => 0, 'hold' => 0, 'mapped' => [], 'nested' => 0, 'lists' => 0, 'missing' => []];
        $migrated = $this->convertItems($items, $legacyKeyMap, $nested, $stats, 0, $listFields, $checkExistence);

        $json = (string) json_encode($migrated, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        $this->note(sprintf('%d Item(s) aus Slot %s konvertiert.', count($migrated), $targetId));
        $this->reportStats($stats);
        if (0 === $stats['hold'] && 0 === $stats['offline'] && $this->isListOfArrays($decoded)) {
            $this->note('Keine MBlock-Marker (`checkbox_block_hold`/`mblock_offline`) gefunden. Die Daten sind vermutlich bereits im Repeater-Format.');
        }

        // Problematische Daten-Keys melden (z. B. REX_MEDIA_1, rein numerische Keys).
        $problemKeys = [];
        foreach ($migrated as $item) {
            foreach (array_keys($item) as $key) {
                $keyStr = (string) $key;
                if (preg_match('/^REX_(MEDIA|LINK|MEDIALIST|LINKLIST)_\d+$|^\d+$/', $keyStr)) {
                    $problemKeys[$keyStr] = true;
                }
            }
        }
        if ([] !== $problemKeys) {
            $this->warn(sprintf(
                'Daten-Keys ohne sprechenden Namen gefunden: `%s`. Diese muessen auf die neuen Repeater-Feldnamen gemappt werden, sonst landen die Werte nicht im richtigen Feld.',
                implode('`, `', array_keys($problemKeys)),
            ));
        }

        return $this->dataResult($json, count($migrated));
    }

    /**
     * @param array{offline: int, hold: int, mapped: array<int|string, string>, nested: int, lists: int, missing: list<string>} $stats
     */
    private function reportStats(array $stats): void
    {
        foreach ($stats['mapped'] as $old => $new) {
            $this->note(sprintf('Legacy-Key `%s` auf `%s` gemappt.', $old, $new));
        }
        if ($stats['offline'] > 0) {
            $this->note(sprintf('%d Item(s) waren offline (`mblock_offline`) und wurden als `__disabled` markiert.', $stats['offline']));
        }
        if ($stats['nested'] > 0) {
            $this->note(sprintf('%d verschachtelte Item-Liste(n) rekursiv konvertiert.', $stats['nested']));
        }
        if ($stats['lists'] > 0) {
            $this->note(sprintf('%d Listenwert(e) (Medialist/Linklist) normalisiert: getrimmt, Leereintraege und Dubletten entfernt.', $stats['lists']));
        }
        foreach (array_unique($stats['missing']) as $missing) {
            $this->warn($missing);
        }
    }

    /**
     * Sprach-Array: alle Schluessel numerisch (Sprach-Ids), alle Werte Arrays, keine Item-Liste.
     *
     * @param array<mixed> $decoded
     */
    private function isLanguageArray(array $decoded): bool
    {
        if ([] === $decoded || array_is_list($decoded)) {
            return false;
        }
        foreach ($decoded as $key => $value) {
            if (!preg_match('/^\d+$/', (string) $key) || !is_array($value)) {
                return false;
            }
            if ([] !== $value && !$this->isListOfArrays($value) && !isset($value['VALUE']) && null === $this->extractMBlockItems($value, '1')) {
                return false;
            }
        }

        return true;
    }

    /**
     * M5: kommaseparierte Listenwerte normalisieren und optional auf Existenz pruefen.
     *
     * @param array{offline: int, hold: int, mapped: array<int|string, string>, nested: int, lists: int, missing: list<string>} $stats
     */
    private function normalizeListValue(mixed $value, string $field, string $kind, bool $checkExistence, array &$stats): string
    {
        $parts = is_array($value) ? array_map(static fn (mixed $v): string => (string) (is_scalar($v) ? $v : ''), $value) : explode(',', (string) $value);
        $normalized = [];
        foreach ($parts as $part) {
            $part = trim($part);
            if ('' === $part || in_array($part, $normalized, true)) {
                continue;
            }
            $normalized[] = $part;
            if (!$checkExistence) {
                continue;
            }
            if ('media' === $kind && class_exists(\rex_media::class) && null === \rex_media::get($part)) {
                $stats['missing'][] = sprintf('Feld `%s`: Datei `%s` liegt nicht im Medienpool.', $field, $part);
            } elseif ('link' === $kind && class_exists(\rex_article::class) && preg_match('/^\d+$/', $part) && null === \rex_article::get((int) $part)) {
                $stats['missing'][] = sprintf('Feld `%s`: Artikel %s existiert nicht.', $field, $part);
            }
        }
        $result = implode(',', $normalized);
        if ($result !== (is_array($value) ? implode(',', $parts) : (string) $value)) {
            ++$stats['lists'];
        }

        return $result;
    }

    /**
     * @param array<int, mixed> $items
     * @param array<int|string, string> $legacyKeyMap
     * @param array{offline: int, hold: int, mapped: array<int|string, string>, nested: int, lists: int, missing: list<string>} $stats
     * @param array<string, string> $listFields
     * @return list<array<string, mixed>>
     */
    private function convertItems(array $items, array $legacyKeyMap, bool $nested, array &$stats, int $depth, array $listFields = [], bool $checkExistence = true): array
    {
        $migrated = [];
        foreach ($items as $item) {
            if (!is_array($item)) {
                continue;
            }

            // Explizite Map zuerst, dann Standard-Heuristiken fuer nicht abgedeckte Keys.
            foreach ($legacyKeyMap as $oldKey => $newKey) {
                $oldKey = trim((string) $oldKey);
                $newKey = trim((string) $newKey);
                if ('' === $oldKey || '' === $newKey || $oldKey === $newKey) {
                    continue;
                }
                if (array_key_exists($oldKey, $item) && !array_key_exists($newKey, $item)) {
                    $item[$newKey] = $item[$oldKey];
                    unset($item[$oldKey]);
                    $stats['mapped'][$oldKey] = $newKey;
                }
            }

            if (array_key_exists('REX_MEDIA_1', $item) && !array_key_exists('media', $item)) {
                $item['media'] = $item['REX_MEDIA_1'];
                unset($item['REX_MEDIA_1']);
                $stats['mapped']['REX_MEDIA_1'] = 'media';
            }
            if (array_key_exists('REX_LINK_1', $item) && !array_key_exists('link', $item)) {
                $item['link'] = $item['REX_LINK_1'];
                unset($item['REX_LINK_1']);
                $stats['mapped']['REX_LINK_1'] = 'link';
            }
            // Plausibler Default: numerischer Key `1` ist bei Legacy-Blocks meist das Custom-Link-Feld.
            // Leer -> weg; gefuellt und `link` frei -> umbenennen; sonst bleibt er und wird als Problem-Key gemeldet.
            if (array_key_exists('1', $item)) {
                if ('' === trim((string) $item['1'])) {
                    unset($item['1']);
                } elseif (!array_key_exists('link', $item)) {
                    $item['link'] = $item['1'];
                    unset($item['1']);
                    $stats['mapped']['1'] = 'link';
                }
            }

            if (array_key_exists('checkbox_block_hold', $item)) {
                unset($item['checkbox_block_hold']);
                ++$stats['hold'];
            }

            $disabled = false;
            if (array_key_exists('mblock_offline', $item)) {
                $disabled = ('1' === (string) $item['mblock_offline']);
                unset($item['mblock_offline']);
            }
            if ($disabled) {
                $item['__disabled'] = true;
                ++$stats['offline'];
            }

            // M5: Medialist-/Linklist-Werte normalisieren.
            foreach ($listFields as $field => $kind) {
                if (array_key_exists($field, $item) && (is_string($item[$field]) || is_array($item[$field]))) {
                    $item[$field] = $this->normalizeListValue($item[$field], $field, $kind, $checkExistence, $stats);
                }
            }

            // Verschachtelte MBlock-Listen rekursiv konvertieren.
            if ($nested && $depth < 5) {
                foreach ($item as $key => $value) {
                    if (is_array($value) && $this->isListOfArrays($value) && $this->hasMBlockMarkers($value)) {
                        ++$stats['nested'];
                        $item[$key] = $this->convertItems($value, $legacyKeyMap, true, $stats, $depth + 1, $listFields, $checkExistence);
                    }
                }
            }

            $migrated[] = $item;
        }

        return $migrated;
    }

    /**
     * @param array<mixed> $list
     */
    private function hasMBlockMarkers(array $list): bool
    {
        foreach ($list as $item) {
            if (is_array($item) && (array_key_exists('checkbox_block_hold', $item) || array_key_exists('mblock_offline', $item))) {
                return true;
            }
        }

        return false;
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
        if ($this->isListOfArrays($decoded)) {
            return array_values($decoded);
        }

        if (isset($decoded['VALUE']) && is_array($decoded['VALUE'])) {
            $value = $decoded['VALUE'];
            if (isset($value[$targetId]) && is_array($value[$targetId])) {
                return array_values($value[$targetId]);
            }
        }

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
     * Items aller GBS-Wrapper (Gridblock-Spalten) der Reihe nach.
     *
     * @param array<mixed> $decoded
     * @return array<int, mixed>
     */
    private function extractAllWrapperItems(array $decoded, string $targetId): array
    {
        $items = [];
        foreach ($decoded as $wrapper) {
            if (is_array($wrapper) && isset($wrapper['VALUE']) && is_array($wrapper['VALUE'])) {
                $value = $wrapper['VALUE'];
                if (isset($value[$targetId]) && is_array($value[$targetId])) {
                    foreach ($value[$targetId] as $item) {
                        $items[] = $item;
                    }
                }
            }
        }

        return $items;
    }

    /**
     * Zaehlt, wie viele GBS-Wrapper einen VALUE-Eintrag fuer die Slot-Id enthalten.
     *
     * @param array<mixed> $decoded
     */
    private function countWrappersWithValue(array $decoded, string $targetId): int
    {
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
     * @return list<string>
     */
    private function parseSlotList(?string $repeaterId): array
    {
        $targets = [];
        foreach (explode(',', (string) $repeaterId) as $part) {
            $part = trim($part);
            if (preg_match('/^\d+$/', $part)) {
                $targets[] = $part;
            }
        }

        return [] === $targets ? ['1'] : array_values(array_unique($targets));
    }

    /**
     * Ermittelt den Repeater-Identifier aus dem Code (Fallback ohne Analyse).
     *
     * @return array{0: string, 1: string}
     */
    private function detectRepeaterId(string $code): array
    {
        $idToken = '$id';
        $numericId = '1';

        if (preg_match('/MBlock::show\(\s*(\$\w+|\d+)\s*,/', $code, $m)) {
            $idToken = $m[1];
        }

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
     * Einstellungsfelder "2.0.gutterWidth") bleiben unangetastet. Verschachtelte
     * Namen ("$id.0.inner.0.field") werden auf den inneren Feldnamen reduziert.
     */
    private function stripFieldPrefixes(string $code, string $idToken, string $numericId): string
    {
        $alternatives = [];
        if (str_starts_with($idToken, '$')) {
            $alternatives[] = preg_quote($idToken, '/') . '\.0\.';
        }
        $alternatives[] = preg_quote($numericId, '/') . '\.0\.';
        $alternatives = array_values(array_unique($alternatives));

        $prefixPattern = '(?:' . implode('|', $alternatives) . ')';

        $stripped = 0;
        $nestedStripped = 0;
        $code = (string) preg_replace_callback(
            '/([\'"])' . $prefixPattern . '([^\'"]+)\1/',
            static function (array $m) use (&$stripped, &$nestedStripped): string {
                ++$stripped;
                $name = $m[2];
                if (preg_match('/^\w+\.0\.(\w+)$/', $name, $nm)) {
                    ++$nestedStripped;
                    $name = $nm[1];
                }

                return "'" . $name . "'";
            },
            $code,
        );

        if ($stripped > 0) {
            $this->note(sprintf('%d Feldnamen-Praefix(e) (`%s.0.` / `%s.0.`) entfernt.', $stripped, $idToken, $numericId));
        }
        if ($nestedStripped > 0) {
            $this->warn(sprintf('%d verschachtelte(r) Feldname(n) (`%s.0.<block>.0.<feld>`) auf den inneren Feldnamen reduziert. Der innere Block muss ein eigener `addFlexRepeaterElement(\'<block>\', ...)` innerhalb des aeusseren Formulars sein.', $nestedStripped, $idToken));
        }

        return $code;
    }

    /**
     * Entfernt `->addHiddenField('mblock_offline', ...)` (vor oder nach dem Praefix-Strip).
     */
    private function removeOfflineHiddenField(string $code): string
    {
        $count = 0;
        $code = (string) preg_replace(
            '/^[ \t]*(?:\$\w+)?->addHiddenField\(\s*[\'"](?:[\w$]+\.0\.)?mblock_offline[\'"]\s*(?:,[^;\n]*)?\)\s*;?[ \t]*\n?/m',
            '',
            $code,
            -1,
            $count,
        );
        // Chained inside a factory chain (kein Zeilenanfang / ohne Semikolon).
        $code = (string) preg_replace(
            '/->addHiddenField\(\s*[\'"](?:[\w$]+\.0\.)?mblock_offline[\'"]\s*(?:,[^)]*)?\)/',
            '',
            $code,
            -1,
            $count2,
        );
        $total = (int) $count + (int) $count2;
        if ($total > 0) {
            $this->note(sprintf('%d Hidden-Feld(er) `mblock_offline` entfernt: Der Repeater bringt Online/Offline je Item mit (`__disabled`).', $total));
        }

        return $code;
    }

    /**
     * Behandelt das Muster (alle Vorkommen):
     *   $mm = MBlock::show($id, $MBlock->show(), [...]);
     *   ...->addHtml($mm)...
     *
     * `->addHtml($mm)` wird zu `->addFlexRepeaterElement($id, $form, [...])`, die
     * Zuweisung entfaellt. Ohne diese Transformation wuerde der Repeater als
     * ->show()-String in addHtml() enden.
     */
    private function inlineMBlockHtmlPattern(string $code): string
    {
        $scan = MBlockModuleAnalyzer::blankComments($code);
        $pattern = '/(\$(\w+))\s*=\s*MBlock::show\(\s*(\$\w+|\d+)\s*,\s*(\$\w+)(?:->show\(\))?\s*(?:,\s*(\[.*?\]|array\s*\(.*?\)))?\s*\)\s*;/s';
        if (!preg_match_all($pattern, $scan, $all, PREG_SET_ORDER | PREG_OFFSET_CAPTURE)) {
            return $code;
        }

        // Von hinten nach vorn ersetzen, damit Offsets gueltig bleiben.
        foreach (array_reverse($all) as $m) {
            $assignVar = $m[1][0];
            $id = $m[3][0];
            $formVar = $m[4][0];
            $rawOpts = $m[5][0] ?? '';
            $options = $this->mapMBlockOptions($rawOpts);

            $repeaterCall = 'addFlexRepeaterElement(' . $id . ', ' . $formVar
                . ('' !== $options ? ', ' . $options : '') . ')';

            $escapedVar = preg_quote($assignVar, '/');
            $replaceCount = 0;
            $code = (string) preg_replace(
                '/->addHtml\(\s*' . $escapedVar . '\s*\)/i',
                '->' . $repeaterCall,
                $code,
                -1,
                $replaceCount,
            );

            if ((int) $replaceCount > 0) {
                $offset = (int) $m[0][1];
                $length = strlen($m[0][0]);
                $code = substr($code, 0, $offset)
                    . '// [mform-migration] ' . $assignVar . ' = MBlock::show(...) ist jetzt ->addFlexRepeaterElement(...) im Formular.'
                    . substr($code, $offset + $length);
                $this->note(sprintf(
                    '`%s = MBlock::show(...)` + `->addHtml(%s)` erkannt: Repeater direkt in den Formular-Baum eingebettet.',
                    $assignVar,
                    $assignVar,
                ));
            }
        }

        return $code;
    }

    /**
     * Ersetzt `MBlock::show($id, $form->show(), [...])` bzw. `MBlock::show($id, $form, [...])`
     * durch `MForm::factory()->addFlexRepeaterElement($id, $form, [...])->show()`.
     */
    private function replaceMBlockShow(string $code): string
    {
        $count = 0;
        $code = (string) preg_replace_callback(
            '/MBlock::show\(\s*(\$\w+|\d+)\s*,\s*(\$\w+)(?:->show\(\))?\s*(?:,\s*(\[.*?\]|array\s*\(.*?\)))?\s*\)/s',
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
            $this->note(sprintf('%d `MBlock::show(...)`-Aufruf(e) auf `addFlexRepeaterElement(...)` umgestellt (`$form->show()` -> `$form`).', $count));
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
        $seen = [];

        if (preg_match_all('/[\'"]([a-zA-Z_]\w*)[\'"]\s*=>\s*([^,\]\)]+)/', $rawOptions, $mm, PREG_SET_ORDER)) {
            foreach ($mm as $match) {
                $key = $match[1];
                $value = trim($match[2]);
                if (isset($seen[$key])) {
                    continue;
                }
                $seen[$key] = true;

                if (array_key_exists($key, MBlockModuleAnalyzer::OPTION_MAP)) {
                    $options[] = "'" . MBlockModuleAnalyzer::OPTION_MAP[$key] . "' => " . $value;
                } elseif (in_array($key, MBlockModuleAnalyzer::OPTIONS_BUILT_IN, true)) {
                    $this->note(sprintf('MBlock-Option `%s` entfaellt: Der Repeater bringt diese Funktion immer mit.', $key));
                } else {
                    $this->warn(sprintf('MBlock-Option `%s` hat keine direkte Repeater-Entsprechung und wurde verworfen. Bitte pruefen.', $key));
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
     *
     * @param Analysis $analysis
     */
    private function collectInputWarnings(string $code, array $analysis): void
    {
        $numericWidgets = implode('|', ['addMediaField', 'addMFormMediaField', 'addMedialistField', 'addMFormMedialistField', 'addLinkField', 'addMFormLinkField', 'addLinklistField', 'addMFormLinklistField', 'addCustomLinkField']);
        $scan = MBlockModuleAnalyzer::blankComments($code);
        foreach ($analysis['calls'] as $call) {
            if (null === $call['form_var'] || 'mform' !== $call['form_kind']) {
                continue;
            }
            if (!preg_match('/addFlexRepeaterElement\(\s*(?:\$\w+|\d+)\s*,\s*' . preg_quote($call['form_var'], '/') . '\b/', $scan, $m, PREG_OFFSET_CAPTURE)) {
                continue;
            }
            $region = MBlockModuleAnalyzer::formRegion($scan, $call['form_var'], (int) $m[0][1]);
            if (null !== $region && preg_match('/->(' . $numericWidgets . ')\(\s*(?:\$\w+|\d+|[\'"]\d+[\'"])\s*[,)]/', $region['code'])) {
                $this->warn(sprintf('Slot %s: Im Block-Formular sind noch numerische Widget-Aufrufe (`addMediaField(n, ...)`, `addLinkField(n)`, ...) uebrig. Im Repeater brauchen Felder sprechende Namen.', $call['slot']));
            }
        }

        if ($analysis['html_mblock']) {
            $this->warn('MBlock mit HTML-/Heredoc-Formular: Der Repeater braucht ein MForm-Objekt. Das Formular muss von Hand mit MForm-Feldern nachgebaut werden.');
        }
        if ($analysis['nested']) {
            $this->warn('Verschachteltes MBlock erkannt: Innere Bloecke werden zu `addFlexRepeaterElement(\'<block>\', MForm::factory()...)` innerhalb des aeusseren Formulars. Daten werden rekursiv konvertiert.');
        }

        if (str_contains($code, 'useCustomLinkForClassicWidgets')) {
            $this->note('`MForm::useCustomLinkForClassicWidgets()` erkannt: Im Repeater empfehlenswert, damit klassische Media-/Link-Widgets robust klonen.');
        }

        if (str_contains($code, 'cke5-editor')) {
            $this->note('CKE5-Editor-Felder erkannt: Der Repeater initialisiert CKE5 beim Klonen automatisch (keine Anpassung noetig).');
        }

        $this->ensureMFormUse($code);
    }

    /**
     * Mapped numerische Widget-Keys in den Block-Formularen auf die aus der
     * Analyse abgeleiteten Zielnamen (`addMediaField(1)` -> `'media'`,
     * `addMediaField(2)` -> `'media_2'`, `addCustomLinkField("$id.0.1")` -> `'link'`).
     *
     * @param Analysis $analysis
     */
    private function normalizeNumericWidgetKeys(string $code, array $analysis): string
    {
        $mapped = [];

        foreach ($analysis['calls'] as $call) {
            if (null === $call['form_var'] || 'mform' !== $call['form_kind']) {
                continue;
            }
            $formVar = $call['form_var'];

            // Aufruf im (kommentarbereinigten) Code finden, dann den Formularbereich bestimmen.
            $scan = MBlockModuleAnalyzer::blankComments($code);
            $callOffset = null;
            if (preg_match_all('/MBlock::show\(\s*(?:\$\w+|\d+)\s*,\s*' . preg_quote($formVar, '/') . '\b/', $scan, $mm, PREG_OFFSET_CAPTURE)) {
                foreach ($mm[0] as $match) {
                    $callOffset = (int) $match[1];
                    break;
                }
            }
            if (null === $callOffset) {
                continue;
            }
            $region = MBlockModuleAnalyzer::formRegion($scan, $formVar, $callOffset);
            if (null === $region) {
                continue;
            }

            foreach ($call['fields'] as $field) {
                if (null === $field['legacy_key'] || null === $field['target']) {
                    continue;
                }
                $target = $field['target'];
                $n = preg_quote($field['name'], '/');

                if ('custom_link' === $field['type'] || preg_match('/^\d+$/', $field['legacy_key'])) {
                    $pattern = '/(->addCustomLinkField\(\s*)([\'"])(?:[\w$]+\.0\.)' . $n . '\2/';
                    $replacement = '$1\'' . $target . '\'';
                } else {
                    $method = $this->methodForLegacyKey($field['legacy_key']);
                    $pattern = '/(->(?:' . $method . ')\(\s*)(?:' . $n . '|[\'"]' . $n . '[\'"])(\s*[,)])/';
                    $replacement = '$1\'' . $target . '\'$2';
                }

                $count = 0;
                $code = $this->replaceInRegion($code, $region['offset'], $region['code'], $pattern, $replacement, $count);
                if ($count > 0) {
                    $mapped[$field['legacy_key']] = $target;
                    // Region nach der Aenderung neu bestimmen (Laengen haben sich geaendert).
                    $scan = MBlockModuleAnalyzer::blankComments($code);
                    if (preg_match('/MBlock::show\(\s*(?:\$\w+|\d+)\s*,\s*' . preg_quote($formVar, '/') . '\b/', $scan, $m2, PREG_OFFSET_CAPTURE)) {
                        $region = MBlockModuleAnalyzer::formRegion($scan, $formVar, (int) $m2[0][1]) ?? $region;
                    }
                }
            }
        }

        foreach ($mapped as $legacy => $target) {
            $this->note(sprintf('Numerisches Widget `%s` im Block-Formular auf sprechenden Key `%s` umgestellt.', $legacy, $target));
        }

        return $code;
    }

    /**
     * Ersetzt Treffer eines Musters nur innerhalb eines (offset-treuen) Bereichs
     * des Codes. Der Bereichstext darf ausgeblendete Luecken enthalten, seine
     * Offsets muessen aber zu `$code` ab `$regionOffset` passen.
     */
    private function replaceInRegion(string $code, int $regionOffset, string $region, string $pattern, string $replacement, int &$count): string
    {
        $count = 0;
        if (!preg_match_all($pattern, $region, $matches, PREG_OFFSET_CAPTURE)) {
            return $code;
        }
        foreach (array_reverse($matches[0]) as $match) {
            $text = $match[0];
            $offset = $regionOffset + (int) $match[1];
            $new = (string) preg_replace($pattern, $replacement, $text, 1);
            $code = substr($code, 0, $offset) . $new . substr($code, $offset + strlen($text));
            ++$count;
        }

        return $code;
    }

    private function methodForLegacyKey(string $legacyKey): string
    {
        if (str_starts_with($legacyKey, 'REX_MEDIALIST_')) {
            return 'addMedialistField|addMFormMedialistField';
        }
        if (str_starts_with($legacyKey, 'REX_MEDIA_')) {
            return 'addMediaField|addMFormMediaField';
        }
        if (str_starts_with($legacyKey, 'REX_LINKLIST_')) {
            return 'addLinklistField|addMFormLinklistField';
        }

        return 'addLinkField|addMFormLinkField';
    }

    /**
     * Macht Output-Zugriffe auf Legacy-Keys rueckwaertskompatibel.
     *
     * @param array<string, array<string, string>> $keyMaps
     */
    private function addOutputKeyFallbacks(string $code, array $keyMaps = []): string
    {
        $fallbacks = 0;

        // Zielnamen je Legacy-Key: explizite Maps, sonst Standard (REX_MEDIA_1 -> media, REX_LINK_1 -> link, ...).
        $targets = [];
        foreach ($keyMaps as $map) {
            foreach ($map as $old => $new) {
                $targets[(string) $old] = $new;
            }
        }

        $code = (string) preg_replace_callback(
            '/(\$\w+)\[\s*([\'"])(REX_(?:MEDIA|LINK|MEDIALIST|LINKLIST)_(\d+))\2\s*\]/',
            static function (array $m) use (&$fallbacks, $targets): string {
                ++$fallbacks;
                $var = $m[1];
                $legacy = $m[3];
                $n = $m[4];
                $target = $targets[$legacy] ?? null;
                if (null === $target) {
                    $base = strtolower((string) preg_replace('/^REX_|_\d+$/', '', $legacy));
                    $target = '1' === $n ? $base : $base . '_' . $n;
                }

                return '(' . $var . '[\'' . $target . '\'] ?? (' . $var . '[\'' . $legacy . '\'] ?? \'\'))';
            },
            $code,
        );

        if ($fallbacks > 0) {
            $this->note(sprintf('%d Output-Zugriff(e) auf `REX_MEDIA_n`/`REX_LINK_n` mit Fallback auf den neuen Key versehen.', $fallbacks));
        }

        // Numerischer Legacy-Link-Key (z. B. $item[1] / $item['1']) -> sprechender Key.
        $itemVars = [];
        if (preg_match_all('/foreach\s*\(\s*[^)]*?\sas\s+(?:\$\w+\s*=>\s*)?(\$\w+)\s*\)/', $code, $foreachMatches)) {
            foreach ($foreachMatches[1] as $itemVar) {
                $itemVars[$itemVar] = true;
            }
        }

        $numericFallbacks = 0;
        foreach (array_keys($itemVars) as $itemVar) {
            $code = (string) preg_replace_callback(
                '/' . preg_quote($itemVar, '/') . '\[\s*(?:(\d+)|[\'"](\d+)[\'"])\s*\]/',
                static function (array $m) use ($itemVar, $targets, &$numericFallbacks): string {
                    $n = '' !== $m[1] ? $m[1] : $m[2];
                    $target = $targets[$n] ?? ('1' === $n ? 'link' : 'link_' . $n);
                    ++$numericFallbacks;

                    return '(' . $itemVar . '[\'' . $target . '\'] ?? (' . $itemVar . '[\'' . $n . '\'] ?? \'\'))';
                },
                $code,
            );
        }

        if ($numericFallbacks > 0) {
            $this->note(sprintf('%d numerische Output-Zugriff(e) (`[1]`) mit Fallback auf den neuen Key versehen.', $numericFallbacks));
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
            return;
        }
        $useMatchCount = preg_match_all('/^\s*use\s+[^;]+;\s*$/m', $code, $matches, PREG_OFFSET_CAPTURE);
        if ($useMatchCount > 0) {
            $last = $matches[0][count($matches[0]) - 1];
            $insertPos = $last[1] + strlen($last[0]);
            $code = substr($code, 0, $insertPos) . "\n" . $useStatement . substr($code, $insertPos);
        } elseif (($phpPos = strpos($code, '<?php')) !== false) {
            $afterPhp = $phpPos + 5;
            $code = substr($code, 0, $afterPhp) . "\n" . $useStatement . substr($code, $afterPhp);
        }
        $this->note('`use FriendsOfRedaxo\\MForm\\Repeater\\MFormRepeaterHelper;` automatisch ergaenzt.');
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
            $this->note(sprintf('%d MBlock-`use`-Statement(s) entfernt.', (int) $removed));
        }
    }

    private function ensureMFormUse(string $code): void
    {
        if (str_contains($code, 'MForm::factory') && !preg_match('/use\s+FriendsOfRedaxo\\\\MForm\s*;/', $code)) {
            $this->note('Stelle sicher, dass `use FriendsOfRedaxo\\MForm;` im Code vorhanden ist.');
        }
    }

    private function note(string $text): void
    {
        if (!in_array($text, $this->notes, true)) {
            $this->notes[] = $text;
        }
    }

    private function warn(string $text): void
    {
        if (!in_array($text, $this->warnings, true)) {
            $this->warnings[] = $text;
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
