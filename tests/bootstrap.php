<?php

/**
 * PHPUnit-Bootstrap.
 *
 * Zwei Betriebsarten:
 * - Unit (Standard): laedt nur die MForm-Klassen per PSR-4 aus lib/. Damit laufen
 *   die Tests in tests/Unit ohne REDAXO (Analyzer, Konverter, Linter-Regeln).
 * - REDAXO: Ist MFORM_REDAXO_PATH gesetzt (Ordner mit redaxo/src/core/boot.php,
 *   klassisches Layout) oder MFORM_REDAXO_BOOT (eigene PHP-Datei, die REDAXO
 *   bootet), wird REDAXO wie von bin/console geladen. Dann laufen auch die Tests
 *   in tests/Redaxo (Parser, Flex-Repeater, Migrator gegen die Datenbank).
 */

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

$redaxoPath = getenv('MFORM_REDAXO_PATH');
$redaxoBoot = getenv('MFORM_REDAXO_BOOT');

if (is_string($redaxoBoot) && '' !== $redaxoBoot && is_file($redaxoBoot)) {
    require $redaxoBoot;
    define('MFORM_TESTS_REDAXO', true);
} elseif (is_string($redaxoPath) && '' !== $redaxoPath && is_file(rtrim($redaxoPath, '/') . '/redaxo/src/core/boot.php')) {
    $root = rtrim($redaxoPath, '/') . '/redaxo';
    chdir($root);
    unset($REX);
    $REX['REDAXO'] = true;
    $REX['HTDOCS_PATH'] = '../';
    $REX['BACKEND_FOLDER'] = 'redaxo';
    $REX['LOAD_PAGE'] = false;
    require $root . '/src/core/boot.php';
    require_once rex_path::core('packages.php');
    define('MFORM_TESTS_REDAXO', true);
} else {
    define('MFORM_TESTS_REDAXO', false);

    spl_autoload_register(static function (string $class): void {
        if ('FriendsOfRedaxo\\MForm' === $class) {
            require __DIR__ . '/../lib/MForm.php';

            return;
        }
        $prefix = 'FriendsOfRedaxo\\MForm\\';
        if (!str_starts_with($class, $prefix)) {
            return;
        }
        $file = __DIR__ . '/../lib/MForm/' . str_replace('\\', '/', substr($class, strlen($prefix))) . '.php';
        if (is_file($file)) {
            require $file;
        }
    });
}
