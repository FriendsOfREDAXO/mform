<?php

/**
 * @author Friends Of REDAXO
 * @license MIT
 */

namespace FriendsOfRedaxo\MForm\Lint;

use FriendsOfRedaxo\MForm\Migration\MBlockModuleAnalyzer;
use rex;
use rex_addon;
use rex_sql;

use function in_array;
use function is_string;
use function sprintf;
use function strlen;

/**
 * Modul-Linter: findet MBlock-Reste und typische Repeater-Fehler im Modul-Code.
 *
 * Regeln:
 * - mblock_show               MBlock::show() (Migration noetig)
 * - mblock_html_form          MBlock mit HTML-/Heredoc-Formular (manuell)
 * - mblock_use                use-Statement fuer MBlock
 * - mblock_offline_field      Hidden-Feld mblock_offline
 * - repeater_numeric_widget   numerische Widgets (addMediaField(1)) in einem Repeater-Formular
 * - repeater_prefixed_field   Praefix-Feldnamen ("$id.0.x") in einem Repeater-Formular
 * - output_toarray_repeater   rex_var::toArray("REX_VALUE[n]") fuer einen Repeater-Slot
 * - yform_mblock_field        YForm-Feld vom Typ mblock
 *
 * Die Code-Regeln arbeiten rein textbasiert (Unit-Test-faehig); nur lintAll()
 * und lintYForm() greifen auf die Datenbank zu.
 *
 * @phpstan-type Finding array{
 *     module_id: int,
 *     module_name: string,
 *     rule: string,
 *     severity: 'error'|'warning'|'info',
 *     file: 'input'|'output'|'yform',
 *     line: int,
 *     message: string,
 *     recommendation: string
 * }
 */
final class ModuleLinter
{
    public const SEVERITY_ERROR = 'error';
    public const SEVERITY_WARNING = 'warning';
    public const SEVERITY_INFO = 'info';

    private const NUMERIC_WIDGETS = 'addMediaField|addMFormMediaField|addMedialistField|addMFormMedialistField|addLinkField|addMFormLinkField|addLinklistField|addMFormLinklistField|addImagelistField';

    private MBlockModuleAnalyzer $analyzer;

    public function __construct(?MBlockModuleAnalyzer $analyzer = null)
    {
        $this->analyzer = $analyzer ?? new MBlockModuleAnalyzer();
    }

    /**
     * Lintet alle Module der Datenbank (optional nur eines).
     *
     * @return list<Finding>
     */
    public function lintAll(?int $moduleId = null): array
    {
        $where = null !== $moduleId ? ' WHERE id = :id' : '';
        $params = null !== $moduleId ? ['id' => $moduleId] : [];
        $rows = rex_sql::factory()->getArray('SELECT id, name, input, output FROM ' . rex::getTable('module') . $where . ' ORDER BY name ASC, id ASC', $params);

        $findings = [];
        foreach ($rows as $row) {
            foreach ($this->lintModule((int) $row['id'], is_string($row['name']) ? $row['name'] : '', is_string($row['input']) ? $row['input'] : '', is_string($row['output']) ? $row['output'] : '') as $finding) {
                $findings[] = $finding;
            }
        }

        return $findings;
    }

    /**
     * Lintet Eingabe- und Ausgabe-Code eines Moduls.
     *
     * @return list<Finding>
     */
    public function lintModule(int $moduleId, string $moduleName, string $input, string $output): array
    {
        $findings = [];
        $add = static function (string $rule, string $severity, string $file, int $line, string $message, string $recommendation) use (&$findings, $moduleId, $moduleName): void {
            $findings[] = self::finding($moduleId, $moduleName, $rule, $severity, $file, $line, $message, $recommendation);
        };

        $scanInput = MBlockModuleAnalyzer::blankComments($input);
        $scanOutput = MBlockModuleAnalyzer::blankComments($output);

        // --- MBlock -------------------------------------------------------
        $analysis = $this->analyzer->analyze($input, $output);
        foreach ($analysis['calls'] as $call) {
            if ('html' === $call['form_kind']) {
                $add('mblock_html_form', self::SEVERITY_ERROR, 'input', $call['line'], sprintf('MBlock::show() fuer Slot %s mit HTML-/Heredoc-Formular.', $call['slot']), 'Formular mit MForm-Feldern nachbauen, dann addFlexRepeaterElement() nutzen.');
            } else {
                $add('mblock_show', self::SEVERITY_ERROR, 'input', $call['line'], sprintf('MBlock::show() fuer Slot %s (Formular %s).', $call['slot'], $call['form_var'] ?? '?'), 'Migrationsassistent (MForm > Migration) oder mform:migrate --module=' . $moduleId . ' nutzen.');
            }
            if ($call['has_offline_field']) {
                $add('mblock_offline_field', self::SEVERITY_INFO, 'input', $call['line'], 'Hidden-Feld mblock_offline im Block-Formular.', 'Entfernen, der Repeater hat Online/Offline je Item (__disabled).');
            }
        }
        foreach ([['input', $scanInput], ['output', $scanOutput]] as [$file, $scan]) {
            if (preg_match_all('/^\s*use\s+[^;]*\bMBlock\b[^;]*;/m', $scan, $m, PREG_OFFSET_CAPTURE)) {
                foreach ($m[0] as $match) {
                    $add('mblock_use', self::SEVERITY_WARNING, $file, $this->lineAt($scan, (int) $match[1]), 'use-Statement fuer MBlock.', 'Entfernen, sobald MBlock::show() migriert ist.');
                }
            }
        }

        // --- Repeater-Formulare -------------------------------------------
        $repeaterSlots = [];
        foreach ($this->repeaterRegions($scanInput) as $region) {
            if (null !== $region['slot']) {
                $repeaterSlots[] = $region['slot'];
            }
            $text = $region['code'];
            $base = $region['offset'];

            if (preg_match_all('/->(' . self::NUMERIC_WIDGETS . ')\(\s*(?:\d+|[\'"]\d+[\'"])\s*[,)]/', $text, $m, PREG_OFFSET_CAPTURE)) {
                foreach ($m[0] as $i => $match) {
                    $add('repeater_numeric_widget', self::SEVERITY_ERROR, 'input', $this->lineAt($scanInput, $base + (int) $match[1]), sprintf('%s mit numerischer Id im Repeater-Formular.', $m[1][$i][0]), 'Sprechenden Feldnamen vergeben, z. B. addMediaField(\'image\').');
                }
            }
            if (preg_match_all('/->add\w+\(\s*[\'"](?:\$\w+|\d+)\.0\.[^\'"]+[\'"]/', $text, $m, PREG_OFFSET_CAPTURE)) {
                foreach ($m[0] as $match) {
                    $add('repeater_prefixed_field', self::SEVERITY_WARNING, 'input', $this->lineAt($scanInput, $base + (int) $match[1]), 'Feldname mit MBlock-Praefix ("$id.0.feld") im Repeater-Formular.', 'Im Repeater nur den Feldnamen verwenden, z. B. addTextField(\'feld\').');
                }
            }
        }

        // --- Ausgabe -------------------------------------------------------
        if ([] !== $repeaterSlots && preg_match_all('/rex_var::toArray\(\s*[\'"]REX_VALUE\[(\d+)\][\'"]\s*\)/', $scanOutput, $m, PREG_OFFSET_CAPTURE | PREG_SET_ORDER)) {
            foreach ($m as $match) {
                $slot = $match[1][0];
                if (in_array($slot, $repeaterSlots, true)) {
                    $add('output_toarray_repeater', self::SEVERITY_WARNING, 'output', $this->lineAt($scanOutput, (int) $match[0][1]), sprintf('rex_var::toArray("REX_VALUE[%s]") fuer einen Repeater-Slot.', $slot), sprintf('MFormRepeaterHelper::decode(%s) verwenden (filtert __disabled-Items).', $slot));
                }
            }
        }

        return $findings;
    }

    /**
     * @return Finding
     */
    private static function finding(int $moduleId, string $moduleName, string $rule, string $severity, string $file, int $line, string $message, string $recommendation): array
    {
        $severity = match ($severity) {
            self::SEVERITY_ERROR => 'error',
            self::SEVERITY_WARNING => 'warning',
            default => 'info',
        };
        $file = match ($file) {
            'input' => 'input',
            'output' => 'output',
            default => 'yform',
        };

        return [
            'module_id' => $moduleId,
            'module_name' => $moduleName,
            'rule' => $rule,
            'severity' => $severity,
            'file' => $file,
            'line' => $line,
            'message' => $message,
            'recommendation' => $recommendation,
        ];
    }

    /**
     * YForm-Felder vom Typ mblock (nur wenn YForm installiert ist).
     *
     * @return list<Finding>
     */
    public function lintYForm(): array
    {
        if (!rex_addon::get('yform')->isAvailable()) {
            return [];
        }
        $findings = [];
        $rows = rex_sql::factory()->getArray(
            'SELECT id, table_name, name, type_name FROM ' . rex::getTable('yform_field') . ' WHERE type_id = :t AND type_name = :n ORDER BY table_name, prio',
            ['t' => 'value', 'n' => 'mblock'],
        );
        foreach ($rows as $row) {
            $findings[] = [
                'module_id' => 0,
                'module_name' => 'YForm ' . (string) $row['table_name'],
                'rule' => 'yform_mblock_field',
                'severity' => self::SEVERITY_INFO,
                'file' => 'yform',
                'line' => (int) $row['id'],
                'message' => sprintf('YForm-Feld "%s" in Tabelle %s nutzt den Wert-Typ mblock.', (string) $row['name'], (string) $row['table_name']),
                'recommendation' => 'Migration auf das YForm-Value repeater_light folgt in MForm 10 Beta 2 (M7).',
            ];
        }

        return $findings;
    }

    /**
     * @param list<Finding> $findings
     * @return array{error: int, warning: int, info: int}
     */
    public static function countBySeverity(array $findings): array
    {
        $counts = ['error' => 0, 'warning' => 0, 'info' => 0];
        foreach ($findings as $finding) {
            ++$counts[$finding['severity']];
        }

        return $counts;
    }

    /**
     * Textbereiche der Repeater-Formulare: `$var = MForm::factory()...` bis
     * `addFlexRepeaterElement(x, $var` sowie inline `addFlexRepeaterElement(x, MForm::factory()->...)`.
     *
     * @return list<array{slot: string|null, offset: int, code: string}>
     */
    private function repeaterRegions(string $code): array
    {
        $regions = [];

        if (preg_match_all('/->add(?:Flex)?RepeaterElement\(\s*([^,]+?)\s*,\s*(\$\w+|MForm::factory\(\))/', $code, $m, PREG_OFFSET_CAPTURE | PREG_SET_ORDER)) {
            foreach ($m as $match) {
                $slot = $this->literalSlot($code, trim($match[1][0]));
                $arg = $match[2][0];
                $callOffset = (int) $match[0][1];

                if (str_starts_with($arg, '$')) {
                    $region = MBlockModuleAnalyzer::formRegion($code, $arg, $callOffset);
                    if (null !== $region) {
                        $regions[] = ['slot' => $slot, 'offset' => $region['offset'], 'code' => $region['code']];
                    }
                } else {
                    // Inline-Formular: bis zur schliessenden Klammer des Repeater-Aufrufs.
                    $open = strpos($code, '(', $callOffset);
                    if (false !== $open) {
                        $close = $this->matchingParen($code, $open);
                        $regions[] = ['slot' => $slot, 'offset' => $open, 'code' => substr($code, $open, $close - $open)];
                    }
                }
            }
        }

        return $regions;
    }

    private function literalSlot(string $code, string $expr): ?string
    {
        if (preg_match('/^(\d+)$/', $expr, $m) || preg_match('/^[\'"](\d+)[\'"]$/', $expr, $m)) {
            return $m[1];
        }
        if (preg_match('/^\$(\w+)$/', $expr, $m) && preg_match_all('/\$' . preg_quote($m[1], '/') . '\s*=\s*(\d+)\s*;/', $code, $mm)) {
            return (string) end($mm[1]);
        }

        return null;
    }

    /**
     * Position der zur oeffnenden Klammer passenden schliessenden (Strings werden uebersprungen).
     */
    private function matchingParen(string $code, int $open): int
    {
        $depth = 0;
        $len = strlen($code);
        $quote = null;
        for ($i = $open; $i < $len; ++$i) {
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
            } elseif ('(' === $ch) {
                ++$depth;
            } elseif (')' === $ch) {
                --$depth;
                if (0 === $depth) {
                    return $i;
                }
            }
        }

        return $len;
    }

    private function lineAt(string $code, int $offset): int
    {
        return substr_count(substr($code, 0, max(0, $offset)), "\n") + 1;
    }
}
