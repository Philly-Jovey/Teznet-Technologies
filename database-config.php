<?php
declare(strict_types=1);

$localDatabaseConfigPath = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'private' .
    DIRECTORY_SEPARATOR . 'teznet-db.php';

if (is_file($localDatabaseConfigPath)) {
    $localDatabaseConfig = require $localDatabaseConfigPath;
    if (!is_array($localDatabaseConfig)) {
        throw new RuntimeException('The local database configuration must return an array.');
    }

    foreach ($localDatabaseConfig as $name => $value) {
        if (!in_array($name, ['DB_HOST', 'DB_PORT', 'DB_NAME', 'DB_USER', 'DB_PASS', 'TEZNET_APPLICATION_UPLOAD_DIR'], true) || !is_string($value)) {
            throw new RuntimeException('The local database configuration contains an invalid setting.');
        }

        if (getenv($name) === false) {
            putenv($name . '=' . $value);
        }
    }
}
