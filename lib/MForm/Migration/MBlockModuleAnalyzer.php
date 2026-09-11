<?php

/**
 * @author Friends Of REDAXO
 * @license MIT
 */

namespace FriendsOfRedaxo\MForm\Migration;

use function array_key_exists;
use function count;
use function in_array;
use function sprintf;
use function strlen;

/**
 * Statische Analyse eines MBlock-Moduls (Eingabe- und Ausgabe-Code).
 *
 * Liefert alle MBlock-Aufrufe mit Slot, Formular-Variable, Optionen und den
 * Feldern des Block-Formulars. Aus numerischen Widget-Aufrufen wird die
 * Legacy-Key-Map abgeleitet (`addMediaField(1)` -> `REX_MEDIA_1` => `media`,
 * `addMediaField(2)` -> `media_2`, Link/Medialist/Linklist analog, Custom-Link
 * mit numerischem Namen `"$id.0.1"` -> `1` => `link`).
 *
 * Reine Textanalyse ohne REDAXO-Abhaengigkeiten, damit sie in Unit-Tests und
 * in der Konsole nutzbar ist.
 *
 * @phpstan-type FieldInfo array{name: string, type: string, legacy_key: string|null, target: string|null, line: int}
 * @phpstan-type CallInfo array{
 *     slot: string,
 *     id_token: string,
 *     form_var: string|null,
 *     form_kind: 'mform'|'html'|'unknown',
 *     line: int,
 *     options: array<string, string>,
 *     unknown_options: list<string>,
 *     fields: list<FieldInfo>,
 *     key_map: array<string, string>,
 *     list_fields: array<string, 'media'|'link'>,
 *     nested_keys: list<string>,
 *     has_offline_field: bool
 * }
 * @phpstan-type Analysis array{
 *     has_mblock: bool,
 *     calls: list<CallInfo>,
 *     slots: list<string>,
 *     key_maps: array<string, array<string, string>>,
 *     list_fields: array<string, array<string, 'media'|'link'>>,
 *     nested: bool,
 *     gridblock: bool,
 *     html_mblock: bool,
 *     cke5: bool,
 *     output_toarray_slots: list<string>,
 *     risk: 'green'|'yellow'|'red'|'none',
 *     risk_reasons: list<string>,
 *     warnings: list<string>
 * }
 */
final class MBlockModuleAnalyzer
{
    /** Repeater-Optionen, die MBlock-Optionen 1:1 entsprechen. */
    public const OPTION_MAP = [
        'min' => 'min',
        'max' => 'max',
        'copy_paste' => 'copy_paste',
    ];

    /** MBlock-Optionen, die der Repeater immer mitbringt und die deshalb entfallen. */
    public const OPTIONS_BUILT_IN = ['online_offline', 'sortable', 'toggle', 'delete_confirm', 'initial_hidden'];

    /**
     * Widget-Methoden mit numerischer Id und ihre Legacy-Daten-Keys.
     *
     * @var array<string, array{prefix: string, target: string}>
     */
    private const NUMERIC_WIDGETS = [
        'addMediaField' => ['prefix' => 'REX_MEDIA_', 'target' => 'media'],
        'addMFormMediaField' => ['prefix' => 'REX_MEDIA_', 'target' => 'media'],
        'addMedialistField' => ['prefix' => 'REX_MEDIALIST_', 'target' => 'medialist'],
        'addMFormMedialistField' => ['prefix' => 'REX_MEDIALIST_', 'target' => 'medialist'],
        'addLinkField' => ['prefix' => 'REX_LINK_', 'target' => 'link'],
        'addMFormLinkField' => ['prefix' => 'REX_LINK_', 'target' => 'link'],
        'addLinklistField' => ['prefix' => 'REX_LINKLIST_', 'target' => 'linklist'],
        'addMFormLinklistField' => ['prefix' => 'REX_LINKLIST_', 'target' => 'linklist'],
    ];

    /**
     * @return Analysis
     */
    public function analyze(string $inputCode, string $outputCode = ''): array
    {
        $calls = [];
        $warnings = [];

        // Kommentare ausblenden (laengenerhaltend), damit auskommentierte Aufrufe und Felder nicht zaehlen.
        $inputCode = self::blankComments($inputCode);

        $hasMBlock = (bool) preg_match('/\bMBlock::show\s*\(/', $inputCode);

        if ($hasMBlock) {
            $calls = $this->collectCalls($inputCode, $warnings);
        }

        $slots = [];
        $keyMaps = [];
        $listFields = [];
        $nested = false;
        $htmlMBlock = false;
        foreach ($calls as $call) {
            if (!in_array($call['slot'], $slots, true)) {
                $slots[] = $call['slot'];
            }
            $keyMaps[$call['slot']] = ($keyMaps[$call['slot']] ?? []) + $call['key_map'];
            $listFields[$call['slot']] = ($listFields[$call['slot']] ?? []) + $call['list_fields'];
            if ([] !== $call['nested_keys']) {
                $nested = true;
            }
            if ('html' === $call['form_kind']) {
                $htmlMBlock = true;
            }
        }

        // MBlock in MBlock: ein Aufruf liegt textlich innerhalb des Formularbereichs eines anderen.
        if (count($calls) > 1) {
            foreach ($calls as $outer) {
                foreach ($calls as $inner) {
                    if ($outer === $inner || null === $outer['form_var']) {
                        continue;
                    }
                    if ($this->callIsInsideForm($inputCode, $outer, $inner['line'])) {
                        $nested = true;
                    }
                }
            }
        }

        $gridblock = (bool) preg_match('/gridblock|GBS[0-9a-f]{4,}/i', $inputCode . "\n" . $outputCode);
        $cke5 = str_contains($inputCode, 'cke5-editor');

        $outputSlots = [];
        if (preg_match_all('/rex_var::toArray\(\s*[\'"]REX_VALUE\[(\d+)\][\'"]\s*\)/', $outputCode, $m)) {
            foreach ($m[1] as $slot) {
                if (!in_array($slot, $outputSlots, true)) {
                    $outputSlots[] = $slot;
                }
            }
        }

        [$risk, $reasons] = $this->rateRisk($hasMBlock, $calls, $nested, $gridblock, $htmlMBlock, $slots, $outputSlots);

        return [
            'has_mblock' => $hasMBlock,
            'calls' => $calls,
            'slots' => $slots,
            'key_maps' => $keyMaps,
            'list_fields' => $listFields,
            'nested' => $nested,
            'gridblock' => $gridblock,
            'html_mblock' => $htmlMBlock,
            'cke5' => $cke5,
            'output_toarray_slots' => $outputSlots,
            'risk' => $risk,
            'risk_reasons' => $reasons,
            'warnings' => $warnings,
        ];
    }

    /**
     * Leitet die Legacy-Key-Map fuer einen Slot ab (Kurzform fuer Aufrufer,
     * die nur die Map brauchen).
     *
     * @return array<string, string>
     */
    public function deriveKeyMap(string $inputCode, string $slot = '1'): array
    {
        $analysis = $this->analyze($inputCode);

        return $analysis['key_maps'][$slot] ?? [];
    }

    /**
     * @param list<string> $warnings
     * @return list<CallInfo>
     */
    private function collectCalls(string $code, array &$warnings): array
    {
        $calls = [];

        // MBlock::show($id, $form->show(), [...]) | MBlock::show($id, $form, [...]) | MBlock::show(1, $form)
        $pattern = '/MBlock::show\(\s*(\$\w+|\d+)\s*,\s*(\$\w+|[\'"][^\'"]*[\'"]|<<<)(?:->show\(\))?\s*(?:,\s*(\[.*?\]|array\s*\(.*?\)))?\s*\)/s';
        if (!preg_match_all($pattern, $code, $matches, PREG_OFFSET_CAPTURE | PREG_SET_ORDER)) {
            $warnings[] = 'MBlock::show() gefunden, aber die Signatur konnte nicht gelesen werden (erwartet: MBlock::show($id, $form->show(), [...])).';

            return [];
        }

        foreach ($matches as $m) {
            $idToken = $m[1][0];
            $formArg = $m[2][0];
            $rawOptions = $m[3][0] ?? '';
            $offset = (int) $m[0][1];
            $line = $this->lineAt($code, $offset);

            $slot = $this->resolveSlot($code, $idToken, $offset);
            $formVar = str_starts_with($formArg, '$') ? $formArg : null;

            $formKind = 'unknown';
            $regionStart = 0;
            if (null !== $formVar) {
                $regionStart = $this->findFormStart($code, $formVar, $offset);
                if ($regionStart >= 0) {
                    $formKind = 'mform';
                } else {
                    // Variable ohne MForm-Zuweisung: vermutlich Heredoc/HTML-String.
                    $formKind = preg_match('/' . preg_quote($formVar, '/') . '\s*=\s*(<<<|[\'"])/', $code) ? 'html' : 'unknown';
                    $regionStart = 0;
                }
            } else {
                $formKind = 'html';
            }

            [$options, $unknown] = $this->parseOptions($rawOptions);

            $fields = [];
            $keyMap = [];
            $listFields = [];
            $nestedKeys = [];
            $hasOffline = false;
            if ('mform' === $formKind) {
                $region = self::formRegion($code, $formVar ?? '', $offset) ?? ['offset' => $regionStart, 'code' => substr($code, $regionStart, $offset - $regionStart)];
                $regionStart = $region['offset'];
                $fields = $this->collectFields($region['code'], $idToken, $slot, $regionStart, $code);
                $keyMap = $this->buildKeyMap($fields);
                $listFields = $this->buildListFields($fields);
                foreach ($fields as $field) {
                    if ('nested' === $field['type']) {
                        $nestedKeys[] = $field['name'];
                    }
                    if ('mblock_offline' === $field['name']) {
                        $hasOffline = true;
                    }
                }
                $nestedKeys = array_values(array_unique($nestedKeys));
            } elseif (preg_match('/\[mblock_offline\]|name=["\']mblock_offline["\']/', $code)) {
                $hasOffline = true;
            }

            $calls[] = [
                'slot' => $slot,
                'id_token' => $idToken,
                'form_var' => $formVar,
                'form_kind' => $formKind,
                'line' => $line,
                'options' => $options,
                'unknown_options' => $unknown,
                'fields' => $fields,
                'key_map' => $keyMap,
                'list_fields' => $listFields,
                'nested_keys' => $nestedKeys,
                'has_offline_field' => $hasOffline,
            ];
        }

        return $calls;
    }

    /**
     * Loest den Slot eines Aufrufs auf: Literal oder letzte Zuweisung `$id = N;` vor dem Aufruf.
     */
    private function resolveSlot(string $code, string $idToken, int $callOffset): string
    {
        if (!str_starts_with($idToken, '$')) {
            return $idToken;
        }
        $var = substr($idToken, 1);
        $before = substr($code, 0, $callOffset);
        if (preg_match_all('/\$' . preg_quote($var, '/') . '\s*=\s*(\d+)\s*;/', $before, $m)) {
            return (string) end($m[1]);
        }

        return '1';
    }

    /**
     * Anfang des Formularbereichs: letzte Zuweisung `$form = MForm::factory()` / `new MForm` vor dem Aufruf.
     * Liefert -1, wenn keine gefunden wurde.
     */
    private function findFormStart(string $code, string $formVar, int $callOffset): int
    {
        $before = substr($code, 0, $callOffset);
        $pattern = '/' . preg_quote($formVar, '/') . '\s*=\s*(?:MForm::factory\(|new\s+\\\\?(?:FriendsOfRedaxo\\\\)?MForm\b|\\\\?FriendsOfRedaxo\\\\MForm::factory\()/';
        if (!preg_match_all($pattern, $before, $m, PREG_OFFSET_CAPTURE)) {
            return -1;
        }
        $last = end($m[0]);

        return false === $last ? -1 : (int) $last[1];
    }

    /**
     * @return list<FieldInfo>
     */
    private function collectFields(string $region, string $idToken, string $slot, int $regionStart, string $fullCode): array
    {
        $fields = [];

        // Praefix-Felder: "$id.0.name" | '1.0.name' | "$id.0.inner.0.name" (verschachtelt)
        $prefixes = [preg_quote($slot, '/') . '\.0\.'];
        if (str_starts_with($idToken, '$')) {
            $prefixes[] = preg_quote($idToken, '/') . '\.0\.';
        }
        $prefixPattern = '(?:' . implode('|', $prefixes) . ')';

        if (preg_match_all('/->(add\w+)\(\s*([\'"])' . $prefixPattern . '([^\'"]+)\2/', $region, $m, PREG_OFFSET_CAPTURE | PREG_SET_ORDER)) {
            foreach ($m as $match) {
                $method = $match[1][0];
                $name = $match[3][0];
                $line = $this->lineAt($fullCode, $regionStart + (int) $match[0][1]);
                if (preg_match('/^(\w+)\.0\.(\w+)$/', $name, $nm)) {
                    $fields[] = ['name' => $nm[1], 'type' => 'nested', 'legacy_key' => null, 'target' => null, 'line' => $line];
                    continue;
                }
                $type = $this->typeFromMethod($method);
                $legacy = null;
                $target = null;
                if (preg_match('/^\d+$/', $name)) {
                    // Numerischer Feldname (typisch addCustomLinkField("$id.0.1")): Daten-Key ist die Zahl.
                    $legacy = $name;
                    $target = '1' === $name ? 'link' : 'link_' . $name;
                }
                $fields[] = ['name' => $name, 'type' => $type, 'legacy_key' => $legacy, 'target' => $target, 'line' => $line];
            }
        }

        // Numerische Widgets: addMediaField(1, ...), addLinkField(2), ...
        $methods = implode('|', array_keys(self::NUMERIC_WIDGETS));
        if (preg_match_all('/->(' . $methods . ')\(\s*(?:(\d+)|[\'"](\d+)[\'"])\s*[,)]/', $region, $m, PREG_OFFSET_CAPTURE | PREG_SET_ORDER)) {
            foreach ($m as $match) {
                $method = $match[1][0];
                $n = ($match[2][1] ?? -1) >= 0 ? $match[2][0] : ($match[3][0] ?? '1');
                $def = self::NUMERIC_WIDGETS[$method];
                $line = $this->lineAt($fullCode, $regionStart + (int) $match[0][1]);
                $fields[] = [
                    'name' => $n,
                    'type' => $def['target'],
                    'legacy_key' => $def['prefix'] . $n,
                    'target' => '1' === $n ? $def['target'] : $def['target'] . '_' . $n,
                    'line' => $line,
                ];
            }
        }

        // Kollisionen aufloesen: Zielname darf weder doppelt noch gleich einem sprechenden Feld sein.
        $taken = [];
        foreach ($fields as $field) {
            if (null === $field['legacy_key']) {
                $taken[$field['name']] = true;
            }
        }
        foreach ($fields as $i => $field) {
            if (null === $field['target']) {
                continue;
            }
            $target = $field['target'];
            $base = $target;
            $suffix = 2;
            while (array_key_exists($target, $taken)) {
                $target = $base . '_' . $suffix;
                ++$suffix;
            }
            $taken[$target] = true;
            $fields[$i]['target'] = $target;
        }

        return $fields;
    }

    /**
     * @param list<FieldInfo> $fields
     * @return array<string, string>
     */
    private function buildKeyMap(array $fields): array
    {
        $map = [];
        foreach ($fields as $field) {
            if (null !== $field['legacy_key'] && null !== $field['target']) {
                $map[$field['legacy_key']] = $field['target'];
            }
        }

        return $map;
    }

    /**
     * Listenfelder (Medialist, Bildliste, Linklist) mit ihrem Zielnamen: fuer die
     * Normalisierung kommaseparierter Werte und die Existenzpruefung (M5).
     *
     * @param list<FieldInfo> $fields
     * @return array<string, 'media'|'link'>
     */
    private function buildListFields(array $fields): array
    {
        $list = [];
        foreach ($fields as $field) {
            $kind = match ($field['type']) {
                'medialist', 'imagelist' => 'media',
                'linklist' => 'link',
                default => null,
            };
            if (null === $kind) {
                continue;
            }
            $name = $field['target'] ?? $field['name'];
            if (preg_match('/^\d+$/', $name)) {
                continue;
            }
            $list[$name] = $kind;
        }

        return $list;
    }

    private function typeFromMethod(string $method): string
    {
        $map = [
            'addTextField' => 'text',
            'addTextAreaField' => 'textarea',
            'addSelectField' => 'select',
            'addMultiSelectField' => 'multiselect',
            'addCheckboxField' => 'checkbox',
            'addRadioField' => 'radio',
            'addHiddenField' => 'hidden',
            'addCustomLinkField' => 'custom_link',
            'addMediaField' => 'media',
            'addMFormMediaField' => 'media',
            'addLinkField' => 'link',
            'addMFormLinkField' => 'link',
            'addImagelistField' => 'imagelist',
            'addMedialistField' => 'medialist',
            'addLinklistField' => 'linklist',
            'addRadioColorField' => 'radio_color',
            'addRadioImgField' => 'radio_img',
            'addRadioIconField' => 'radio_icon',
            'addColorSwatchField' => 'color_swatch',
            'addCheckboxGroupField' => 'checkbox_group',
        ];

        return $map[$method] ?? strtolower((string) preg_replace('/^add|Field$/', '', $method));
    }

    /**
     * @return array{0: array<string, string>, 1: list<string>}
     */
    private function parseOptions(string $rawOptions): array
    {
        $options = [];
        $unknown = [];
        if ('' === trim($rawOptions)) {
            return [$options, $unknown];
        }
        if (preg_match_all('/[\'"]([a-zA-Z_]\w*)[\'"]\s*=>\s*([^,\]\)]+)/', $rawOptions, $m, PREG_SET_ORDER)) {
            foreach ($m as $match) {
                $key = $match[1];
                $value = trim($match[2]);
                if (array_key_exists($key, self::OPTION_MAP)) {
                    $options[self::OPTION_MAP[$key]] = $value;
                } elseif (!in_array($key, self::OPTIONS_BUILT_IN, true)) {
                    $unknown[] = $key;
                }
            }
        }

        return [$options, $unknown];
    }

    /**
     * @param CallInfo $outer
     */
    private function callIsInsideForm(string $code, array $outer, int $innerLine): bool
    {
        if (null === $outer['form_var']) {
            return false;
        }
        $callOffset = $this->offsetOfLine($code, $outer['line']);
        $start = $this->findFormStart($code, $outer['form_var'], $callOffset);
        if ($start < 0) {
            return false;
        }
        $startLine = $this->lineAt($code, $start);

        return $innerLine > $startLine && $innerLine < $outer['line'];
    }

    /**
     * @param list<CallInfo> $calls
     * @param list<string> $slots
     * @param list<string> $outputSlots
     * @return array{0: 'green'|'yellow'|'red'|'none', 1: list<string>}
     */
    private function rateRisk(bool $hasMBlock, array $calls, bool $nested, bool $gridblock, bool $htmlMBlock, array $slots, array $outputSlots): array
    {
        if (!$hasMBlock) {
            return ['none', []];
        }

        $reasons = [];
        $level = 'green';

        if ($htmlMBlock) {
            $level = 'red';
            $reasons[] = 'MBlock mit HTML-/Heredoc-Formular statt MForm: Eingabe muss von Hand auf den Repeater umgestellt werden.';
        }
        if ($nested) {
            $level = 'red';
            $reasons[] = 'Verschachteltes MBlock erkannt: Code und Daten werden als verschachtelte Repeater konvertiert, Ergebnis unbedingt pruefen.';
        }
        if ($gridblock) {
            $level = 'red';
            $reasons[] = 'Gridblock-Bezug erkannt: gespeicherte Werte liegen vermutlich in GBS-Wrappern, Spalten werden zusammengefuehrt.';
        }
        if ([] === $calls) {
            $level = 'red';
            $reasons[] = 'MBlock::show() konnte nicht geparst werden.';
        }

        foreach ($calls as $call) {
            if ([] !== $call['key_map']) {
                if ('green' === $level) {
                    $level = 'yellow';
                }
                $reasons[] = sprintf('Slot %s: %d Legacy-Key(s) werden auf sprechende Namen gemappt (%s).', $call['slot'], count($call['key_map']), implode(', ', array_map(static fn (string $k, string $v): string => $k . ' -> ' . $v, array_keys($call['key_map']), $call['key_map'])));
            }
            if ([] !== $call['unknown_options']) {
                if ('green' === $level) {
                    $level = 'yellow';
                }
                $reasons[] = sprintf('Slot %s: MBlock-Option(en) ohne Entsprechung: %s.', $call['slot'], implode(', ', $call['unknown_options']));
            }
        }

        $missing = array_diff($slots, $outputSlots);
        if ([] !== $missing && [] !== $outputSlots) {
            if ('green' === $level) {
                $level = 'yellow';
            }
            $reasons[] = sprintf('Ausgabe liest Slot(s) %s nicht ueber rex_var::toArray(); Ausgabe-Konvertierung pruefen.', implode(', ', $missing));
        }

        return [$level, $reasons];
    }

    /**
     * Textbereich eines Block-Formulars: die Zuweisung `$var = MForm::factory()...;`
     * (bzw. `new MForm`) und alle folgenden `$var->...;`-Statements bis zum Aufruf
     * an `$callOffset`. Dazwischenliegende fremde Statements (z. B. das Hauptformular)
     * werden durch Leerzeichen ersetzt, damit Offsets im Ergebnis denen des
     * Originals ab `offset` entsprechen.
     *
     * @return array{offset: int, code: string}|null null, wenn keine Zuweisung gefunden wurde
     */
    public static function formRegion(string $code, string $formVar, int $callOffset): ?array
    {
        if ('' === $formVar) {
            return null;
        }
        $before = substr($code, 0, $callOffset);
        $pattern = '/' . preg_quote($formVar, '/') . '\s*=\s*(?:MForm::factory\(|new\s+\\\\?(?:FriendsOfRedaxo\\\\)?MForm\b|\\\\?FriendsOfRedaxo\\\\MForm::factory\()/';
        if (!preg_match_all($pattern, $before, $m, PREG_OFFSET_CAPTURE)) {
            return null;
        }
        $last = end($m[0]);
        if (false === $last) {
            return null;
        }
        $start = (int) $last[1];

        $result = '';
        $pos = $start;
        $first = true;
        $ownPattern = '/^\s*' . preg_quote($formVar, '/') . '\s*(?:->|=)/';
        while ($pos < $callOffset) {
            $end = self::scanStatementEnd($code, $pos, $callOffset);
            $statement = substr($code, $pos, $end - $pos);
            $keep = $first || preg_match($ownPattern, $statement);
            $first = false;
            $result .= $keep ? $statement : (string) preg_replace('/[^\n]/', ' ', $statement);
            $pos = $end;
        }

        return ['offset' => $start, 'code' => $result];
    }

    /**
     * Ende des Statements ab `$pos` (Position hinter dem `;` auf Klammertiefe 0,
     * Strings werden uebersprungen), maximal `$limit`.
     */
    private static function scanStatementEnd(string $code, int $pos, int $limit): int
    {
        $depth = 0;
        $quote = null;
        $len = min(strlen($code), $limit);
        for ($i = $pos; $i < $len; ++$i) {
            $ch = $code[$i];
            if (null !== $quote) {
                if ('\\' === $ch) {
                    ++$i;
                } elseif ($ch === $quote) {
                    $quote = null;
                }
                continue;
            }
            if ('\'' === $ch || '"' === $ch) {
                $quote = $ch;
            } elseif ('(' === $ch || '[' === $ch || '{' === $ch) {
                ++$depth;
            } elseif (')' === $ch || ']' === $ch || '}' === $ch) {
                $depth = max(0, $depth - 1);
            } elseif (';' === $ch && 0 === $depth) {
                return $i + 1;
            }
        }

        return $len;
    }

    /**
     * Ersetzt Kommentare (`// ...`, `# ...`, `/* ... *\/`) durch Leerzeichen, Zeilenumbrueche
     * und damit Offsets und Zeilennummern bleiben erhalten. Strings werden nicht
     * geparst; ein `//` innerhalb eines Strings (z. B. URLs) wird deshalb nur am
     * Zeilenanfang als Kommentar gewertet.
     */
    public static function blankComments(string $code): string
    {
        $blank = static function (array $m): string {
            return (string) preg_replace('/[^\n]/', ' ', $m[0]);
        };
        $code = (string) preg_replace_callback('~/\*.*?\*/~s', $blank, $code);

        return (string) preg_replace_callback('~^[ \t]*(?://|#(?!\[)).*$~m', $blank, $code);
    }

    private function lineAt(string $code, int $offset): int
    {
        return substr_count(substr($code, 0, max(0, $offset)), "\n") + 1;
    }

    private function offsetOfLine(string $code, int $line): int
    {
        $offset = 0;
        $current = 1;
        $len = strlen($code);
        while ($current < $line && $offset < $len) {
            $next = strpos($code, "\n", $offset);
            if (false === $next) {
                break;
            }
            $offset = $next + 1;
            ++$current;
        }

        return $offset;
    }
}
