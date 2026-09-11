<?php

/**
 * @author Friends Of REDAXO
 * @license MIT
 */

namespace FriendsOfRedaxo\MForm\Console;

use FriendsOfRedaxo\MForm\Lint\ModuleLinter;
use rex_console_command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

use function count;
use function sprintf;

/**
 * `mform:lint`: durchsucht Module (optional YForm-Felder) nach MBlock-Resten,
 * numerischen Widget-Ids in Repeatern und veralteten Ausgabe-Aufrufen.
 */
final class LintCommand extends rex_console_command
{
    protected function configure(): void
    {
        $this
            ->setName('mform:lint')
            ->setDescription('Prueft Module auf MBlock-Reste, numerische Widget-Ids in Repeatern und veraltete Aufrufe')
            ->addOption('module', 'm', InputOption::VALUE_REQUIRED, 'Nur dieses Modul (ID) pruefen')
            ->addOption('yform', null, InputOption::VALUE_NONE, 'Zusaetzlich YForm-Felder vom Typ mblock auflisten')
            ->addOption('severity', 's', InputOption::VALUE_REQUIRED, 'Mindest-Schweregrad: error, warning, info', 'info')
            ->addOption('json', null, InputOption::VALUE_NONE, 'Ausgabe als JSON');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = $this->getStyle($input, $output);

        $moduleId = $input->getOption('module');
        $moduleId = null !== $moduleId && '' !== (string) $moduleId ? (int) $moduleId : null;
        $minSeverity = (string) $input->getOption('severity');
        $rank = ['error' => 0, 'warning' => 1, 'info' => 2];
        $minRank = $rank[$minSeverity] ?? 2;

        $linter = new ModuleLinter();
        $findings = $linter->lintAll($moduleId);
        if ((bool) $input->getOption('yform')) {
            foreach ($linter->lintYForm() as $finding) {
                $findings[] = $finding;
            }
        }
        $findings = array_values(array_filter($findings, static fn (array $f): bool => $rank[$f['severity']] <= $minRank));

        if ((bool) $input->getOption('json')) {
            $output->writeln((string) json_encode($findings, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));

            return [] === array_filter($findings, static fn (array $f): bool => 'error' === $f['severity']) ? self::SUCCESS : self::FAILURE;
        }

        $io->title('MForm Modul-Linter');

        if ([] === $findings) {
            $io->success(null !== $moduleId ? sprintf('Modul %d: keine Befunde.', $moduleId) : 'Keine Befunde in den Modulen.');

            return self::SUCCESS;
        }

        $rows = [];
        foreach ($findings as $f) {
            $rows[] = [
                0 !== $f['module_id'] ? $f['module_id'] : '-',
                $f['module_name'],
                strtoupper($f['severity']),
                $f['rule'],
                $f['file'] . ':' . $f['line'],
                $f['message'],
                $f['recommendation'],
            ];
        }
        $io->table(['Modul', 'Name', 'Stufe', 'Regel', 'Stelle', 'Befund', 'Empfehlung'], $rows);

        $counts = ModuleLinter::countBySeverity($findings);
        $io->writeln(sprintf('%d Befund(e): %d Fehler, %d Warnung(en), %d Hinweis(e).', count($findings), $counts['error'], $counts['warning'], $counts['info']));

        return $counts['error'] > 0 ? self::FAILURE : self::SUCCESS;
    }
}
