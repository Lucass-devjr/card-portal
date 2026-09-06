<?php

function getDbConnection(): PDO
{
    static $pdo = null;
    if ($pdo !== null) {
        return $pdo;
    }

    $envPath = __DIR__ . '/../../.env';
    if (file_exists($envPath)) {
        $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            if (strpos(trim($line), '#') === 0) continue;
            list($name, $value) = explode('=', $line, 2);
            putenv(trim($name) . '=' . trim($value));
        }
    }

    $host     = getenv('DB_HOST');
    $port     = getenv('DB_PORT');
    $dbname   = getenv('DB_NAME');
    $user     = getenv('DB_USER');
    $password = getenv('DB_PASSWORD');

    $dsn = "mysql:host={$host};port={$port};dbname={$dbname};charset=utf8mb4";

    $pdo = new PDO($dsn, $user, $password, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ]);

    return $pdo;
}
