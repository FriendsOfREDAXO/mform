<?php

require __DIR__ . '/update.php';

// Protokolltabellen des Migrationsassistenten (Backup/Rollback, Umhaengen).
FriendsOfRedaxo\MForm\Migration\MBlockToRepeaterMigrator::ensureTables();
