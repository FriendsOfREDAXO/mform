<?php

/**
 * @author Friends Of REDAXO
 * @license MIT
 */

namespace FriendsOfRedaxo\MForm\Console;

use FriendsOfRedaxo\MForm\Migration\MBlockInventory;
use FriendsOfRedaxo\MForm\Migration\MBlockModuleAnalyzer;
use FriendsOfRedaxo\MForm\Migration\MBlockToRepeaterConverter;
use FriendsOfRedaxo\MForm\Migration\MBlockToRepeaterMigrator;
use rex;
use rex_console_command;
use rex_sql;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

use function is_array;
use function is_string;
use function sprintf;

/**
 * `mform:migrate`: MBlock -> Repeater von der Konsole.
 *
 *   mform:migrate                          Inventar aller MBlock-Module
 *   mform:migrate --module=ID              Analyse + Dry-Run aller Slots
 *   mform:migrate --module=ID --apply      Daten migrieren (mit Backup, Lauf-Token)
 *   mform:migrate --module=ID --create-module   Konvertierte Modul-Kopie anlegen
 *   mform:migrate --module=ID --reassign=NEWID  Slices auf das neue Modul umhaengen
 *   mform:migrate --rollback=TOKEN         Datenmigration zuruecknehmen
 *   mform:migrate --revert-reassign=TOKEN  Umhaengen zuruecknehmen
 */
final class MigrateCommand extends rex_console_command
{
    protected function configure(): void
    {
        $this
            ->setName('mform:migrate')
            ->setDescription('MBlock -> Repeater: Inventar, Dry-Run, Datenmigration mit Backup, Rollback')
            ->addOption('module', 'm', InputOption::VALUE_REQUIRED, 'Modul-ID')
            ->addOption('slot', null, InputOption::VALUE_REQUIRED, 'Nur diese Slots (kommagetrennt, Standard: alle erkannten)')
            ->addOption('map', null, InputOption::VALUE_REQUIRED, 'Legacy-Key-Map als JSON, ueberschreibt die automatische ({"REX_MEDIA_1":"media"})')
            ->addOption('merge-columns', null, InputOption::VALUE_NONE, 'Gridblock-Spalten (mehrere GBS-Wrapper) zusammenfuehren')
            ->addOption('dry-run', null, InputOption::VALUE_NONE, 'Nur Vorschau (Standard, wenn --apply fehlt)')
            ->addOption('apply', null, InputOption::VALUE_NONE, 'Daten wirklich schreiben (mit Backup)')
            ->addOption('create-module', null, InputOption::VALUE_NONE, 'Konvertierte Kopie des Moduls anlegen und ID ausgeben')
            ->addOption('show-code', null, InputOption::VALUE_NONE, 'Konvertierten Eingabe-/Ausgabe-Code ausgeben')
            ->addOption('reassign', null, InputOption::VALUE_REQUIRED, 'Alle Slices des Moduls auf diese Modul-ID umhaengen')
            ->addOption('rollback', null, InputOption::VALUE_REQUIRED, 'Datenmigration mit diesem Lauf-Token zuruecknehmen')
            ->addOption('revert-reassign', null, InputOption::VALUE_REQUIRED, 'Umhaengen mit diesem Token zuruecknehmen ("last" = letztes)')
            ->addOption('runs', null, InputOption::VALUE_NONE, 'Letzte Migrationslaeufe und Umhaengungen auflisten')
            ->addOption('json', null, InputOption::VALUE_NONE, 'Inventar/Dry-Run als JSON');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = $this->getStyle($input, $output);
        $analyzer = new MBlockModuleAnalyzer();
        $converter = new MBlockToRepeaterConverter($analyzer);
        $migrator = new MBlockToRepeaterMigrator($converter);
        $json = (bool) $input->getOption('json');

        $rollback = $input->getOption('rollback');
        if (is_string($rollback) && '' !== $rollback) {
            $result = $migrator->rollback($rollback);
            foreach ($result['errors'] as $error) {
                $io->error($error);
            }
            if ($result['restored'] > 0) {
                $io->success(sprintf('%d Slice-Wert(e) aus Lauf %s wiederhergestellt.', $result['restored'], $rollback));
            }

            return [] === $result['errors'] ? self::SUCCESS : self::FAILURE;
        }

        $revert = $input->getOption('revert-reassign');
        if (is_string($revert) && '' !== $revert) {
            $result = $migrator->revertReassign('last' === $revert ? '' : $revert);
            foreach ($result['errors'] as $error) {
                $io->error($error);
            }
            if ($result['restored'] > 0) {
                $io->success(sprintf('%d Slice(s) aus Umhaengung %s zurueckgesetzt.', $result['restored'], $result['token']));
            }

            return [] === $result['errors'] ? self::SUCCESS : self::FAILURE;
        }

        if ((bool) $input->getOption('runs')) {
            $this->printRuns($io, $migrator);

            return self::SUCCESS;
        }

        $moduleOpt = $input->getOption('module');
        $moduleId = is_string($moduleOpt) && '' !== $moduleOpt ? (int) $moduleOpt : 0;

        if ($moduleId <= 0) {
            return $this->printInventory($io, $output, $json);
        }

        $module = rex_sql::factory()->getArray('SELECT id, name, input, output FROM ' . rex::getTable('module') . ' WHERE id = :id', ['id' => $moduleId]);
        if ([] === $module) {
            $io->error(sprintf('Modul %d nicht gefunden.', $moduleId));

            return self::FAILURE;
        }
        $name = is_string($module[0]['name']) ? $module[0]['name'] : '';
        $inputCode = is_string($module[0]['input']) ? $module[0]['input'] : '';
        $outputCode = is_string($module[0]['output']) ? $module[0]['output'] : '';

        // Umhaengen braucht keine Analyse.
        $reassign = $input->getOption('reassign');
        if (is_string($reassign) && '' !== $reassign) {
            $result = $migrator->reassign($migrator->sliceIdsOfModule($moduleId), (int) $reassign);
            foreach ($result['errors'] as $error) {
                $io->error($error);
            }
            if ($result['moved'] > 0) {
                $io->success(sprintf('%d Slice(s) von Modul %d auf Modul %d umgehaengt. Token: %s', $result['moved'], $moduleId, (int) $reassign, $result['token']));
            }

            return [] === $result['errors'] ? self::SUCCESS : self::FAILURE;
        }

        $analysis = $analyzer->analyze($inputCode, $outputCode);
        if (!$json) {
            $io->title(sprintf('Modul %d: %s', $moduleId, $name));
            $this->printAnalysis($io, $analysis);
        }

        if (!$analysis['has_mblock']) {
            $io->warning('Kein MBlock::show() im Eingabe-Code, nichts zu migrieren.');

            return self::SUCCESS;
        }

        // Slot-Konfiguration: erkannte Slots (oder --slot), Key-Map automatisch (oder --map).
        $slots = $analysis['slots'];
        $slotOpt = $input->getOption('slot');
        if (is_string($slotOpt) && '' !== $slotOpt) {
            $slots = array_values(array_filter(array_map('trim', explode(',', $slotOpt)), static fn (string $s): bool => '' !== $s));
        }
        $mapOverride = null;
        $mapOpt = $input->getOption('map');
        if (is_string($mapOpt) && '' !== $mapOpt) {
            $decoded = json_decode($mapOpt, true);
            if (!is_array($decoded)) {
                $io->error('--map ist kein gueltiges JSON-Objekt.');

                return self::FAILURE;
            }
            $mapOverride = array_map('strval', $decoded);
        }
        $options = ['merge_columns' => (bool) $input->getOption('merge-columns')];
        $slotConfig = [];
        foreach ($slots as $slot) {
            $slotConfig[$slot] = ['key_map' => $mapOverride ?? ($analysis['key_maps'][$slot] ?? []), 'options' => $options];
        }

        // Code-Konvertierung.
        $inputResult = $converter->convertInput($inputCode, null, $analysis);
        $outputResult = $converter->convertOutput($outputCode, implode(',', $slots), $analysis['key_maps']);

        if ((bool) $input->getOption('show-code')) {
            $io->section('Eingabe (konvertiert)');
            $output->writeln($inputResult['code']);
            $io->section('Ausgabe (konvertiert)');
            $output->writeln($outputResult['code']);
        }
        if (!$json) {
            $this->printNotes($io, 'Eingabe', $inputResult);
            $this->printNotes($io, 'Ausgabe', $outputResult);
        }

        if ((bool) $input->getOption('create-module')) {
            $created = $migrator->createConvertedModule($moduleId, $inputResult['code'], $outputResult['code']);
            if (null === $created) {
                $io->error('Konvertierte Kopie konnte nicht angelegt werden.');

                return self::FAILURE;
            }
            $io->success(sprintf('Konvertierte Kopie angelegt: Modul %d (%s).', $created['id'], $created['name']));
        }

        // Daten: Dry-Run immer, Apply nur mit --apply.
        $dry = $migrator->dryRunSlots($moduleId, $slotConfig);
        if ($json) {
            $output->writeln((string) json_encode(['analysis' => $analysis, 'dry_run' => $dry], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        } else {
            $this->printDryRun($io, $dry);
        }

        if ((bool) $input->getOption('apply')) {
            $result = $migrator->applySlots($moduleId, $slotConfig);
            foreach ($result['errors'] as $error) {
                $io->error($error);
            }
            $io->success(sprintf('%d Slice-Wert(e) migriert, %d uebersprungen. Lauf-Token: %s (Rollback: mform:migrate --rollback=%s)', $result['updated'], $result['skipped'], $result['token'], $result['token']));

            return [] === $result['errors'] ? self::SUCCESS : self::FAILURE;
        }

        if (!$json) {
            $io->note('Vorschau. Mit --apply werden die Werte geschrieben (Backup je Lauf-Token), mit --create-module entsteht die konvertierte Modul-Kopie.');
        }

        return self::SUCCESS;
    }

    private function printInventory(SymfonyStyle $io, OutputInterface $output, bool $json): int
    {
        $inventory = new MBlockInventory();
        $entries = $inventory->collect();

        if ($json) {
            $output->writeln((string) json_encode($entries, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));

            return self::SUCCESS;
        }

        $io->title('MBlock-Inventar');
        if ([] === $entries) {
            $io->success('Kein Modul nutzt MBlock::show().');

            return self::SUCCESS;
        }

        $rows = [];
        foreach ($entries as $entry) {
            $a = $entry['analysis'];
            $keys = [];
            foreach ($a['key_maps'] as $slot => $map) {
                foreach ($map as $old => $new) {
                    $keys[] = $slot . ': ' . $old . ' -> ' . $new;
                }
            }
            $rows[] = [
                $entry['id'],
                $entry['name'],
                $entry['slice_count'],
                implode(', ', $a['slots']),
                implode(', ', array_map(static fn (string $t, int $n): string => $t . ' x' . $n, array_keys($entry['field_types']), $entry['field_types'])),
                strtoupper($a['risk']),
                implode("\n", array_merge($keys, $a['risk_reasons'])),
                null !== $entry['migrated_module_id'] ? (string) $entry['migrated_module_id'] : '-',
            ];
        }
        $io->table(['ID', 'Modul', 'Slices', 'Slots', 'Feldtypen', 'Risiko', 'Hinweise', 'Kopie'], $rows);

        $summary = MBlockInventory::summary($entries);
        $io->writeln(sprintf('%d Modul(e) mit MBlock, %d Slices. Risiko: %d rot, %d gelb, %d gruen.', $summary['total'], $summary['slices'], $summary['red'], $summary['yellow'], $summary['green']));
        $io->writeln('Naechster Schritt: mform:migrate --module=ID (Dry-Run), dann --create-module, --apply und --reassign=NEUE_ID.');

        return self::SUCCESS;
    }

    /**
     * @param array<string, mixed> $analysis
     */
    private function printAnalysis(SymfonyStyle $io, array $analysis): void
    {
        /** @var array{has_mblock: bool, risk: string, slots: list<string>, nested: bool, gridblock: bool, html_mblock: bool, cke5: bool, calls: list<array{slot: string, form_var: string|null, form_kind: string, line: int, options: array<string, string>, unknown_options: list<string>, fields: list<array{name: string, type: string, legacy_key: string|null, target: string|null}>, key_map: array<string, string>}>, risk_reasons: list<string>, warnings: list<string>} $analysis */
        $io->writeln(sprintf('MBlock: %s | Risiko: %s | Slots: %s | verschachtelt: %s | Gridblock: %s | HTML-Form: %s', $analysis['has_mblock'] ? 'ja' : 'nein', strtoupper($analysis['risk']), implode(', ', $analysis['slots']), $analysis['nested'] ? 'ja' : 'nein', $analysis['gridblock'] ? 'ja' : 'nein', $analysis['html_mblock'] ? 'ja' : 'nein'));
        foreach ($analysis['calls'] as $call) {
            $io->section(sprintf('Slot %s (Zeile %d, Formular %s, %s)', $call['slot'], $call['line'], $call['form_var'] ?? '-', $call['form_kind']));
            $rows = [];
            foreach ($call['fields'] as $field) {
                $rows[] = [$field['name'], $field['type'], $field['legacy_key'] ?? '', $field['target'] ?? ''];
            }
            if ([] !== $rows) {
                $io->table(['Feld', 'Typ', 'Legacy-Key', 'Neuer Key'], $rows);
            }
            if ([] !== $call['options']) {
                $io->writeln('Optionen: ' . implode(', ', array_map(static fn (string $k, string $v): string => $k . ' => ' . $v, array_keys($call['options']), $call['options'])));
            }
            if ([] !== $call['unknown_options']) {
                $io->writeln('Ohne Entsprechung: ' . implode(', ', $call['unknown_options']));
            }
        }
        foreach ($analysis['risk_reasons'] as $reason) {
            $io->writeln(' ! ' . $reason);
        }
        foreach ($analysis['warnings'] as $warning) {
            $io->warning($warning);
        }
    }

    /**
     * @param array{code: string, notes: list<string>, warnings: list<string>} $result
     */
    private function printNotes(SymfonyStyle $io, string $label, array $result): void
    {
        if ([] === $result['notes'] && [] === $result['warnings']) {
            return;
        }
        $io->section($label . ': Hinweise');
        foreach ($result['notes'] as $note) {
            $io->writeln(' - ' . $note);
        }
        foreach ($result['warnings'] as $warning) {
            $io->writeln(' ! ' . $warning);
        }
    }

    /**
     * @param array<string, array{column: string, rows: list<array{slice_id: int, article_id: int, article_name: string, clang_id: int, count: int, changed: bool, skipped: bool, warnings: list<string>}>, total: int, changed: int, warnings: int}> $dry
     */
    private function printDryRun(SymfonyStyle $io, array $dry): void
    {
        foreach ($dry as $slot => $result) {
            $io->section(sprintf('Dry-Run Slot %s (Spalte %s): %d Slices, %d mit Aenderung, %d mit Warnungen', $slot, $result['column'], $result['total'], $result['changed'], $result['warnings']));
            $rows = [];
            foreach ($result['rows'] as $row) {
                $state = $row['skipped'] ? 'uebersprungen' : ($row['changed'] ? 'AENDERUNG' : 'unveraendert');
                $rows[] = [$row['slice_id'], $row['article_id'] . ('' !== $row['article_name'] ? ' ' . $row['article_name'] : ''), $row['clang_id'], $row['count'], $state, implode("\n", $row['warnings'])];
            }
            if ([] !== $rows) {
                $io->table(['Slice', 'Artikel', 'Sprache', 'Items', 'Status', 'Warnungen'], $rows);
            }
        }
    }

    private function printRuns(SymfonyStyle $io, MBlockToRepeaterMigrator $migrator): void
    {
        $io->title('Migrationslaeufe');
        $rows = [];
        foreach ($migrator->getRuns() as $run) {
            $rows[] = [$run['token'], $run['module_id'] . ' ' . $run['module_name'], $run['slices'], $run['columns'], $run['createdate'], $run['createuser'], $run['open'] > 0 ? 'offen' : 'zurueckgenommen'];
        }
        if ([] === $rows) {
            $io->writeln('Keine Datenmigrationen protokolliert.');
        } else {
            $io->table(['Token', 'Modul', 'Slices', 'Spalten', 'Datum', 'Benutzer', 'Status'], $rows);
        }

        $io->title('Umhaengungen');
        $rows = [];
        foreach ($migrator->getReassignRuns() as $run) {
            $rows[] = [$run['token'], $run['slices'], $run['old_module_id'] . ' -> ' . $run['new_module_id'], $run['createdate'], $run['open'] > 0 ? 'offen' : 'zurueckgenommen'];
        }
        if ([] === $rows) {
            $io->writeln('Keine Umhaengungen protokolliert.');
        } else {
            $io->table(['Token', 'Slices', 'Modul', 'Datum', 'Status'], $rows);
        }
        $io->writeln('Zuruecknehmen: mform:migrate --rollback=TOKEN bzw. --revert-reassign=TOKEN');
    }
}
