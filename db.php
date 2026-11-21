<?php
declare(strict_types=1);

function db(): PDO
{
    static $pdo = null;
    if ($pdo instanceof PDO) {
        return $pdo;
    }

    $configPath = __DIR__ . '/../config.php';
    if (!is_file($configPath)) {
        $configPath = __DIR__ . '/../config.example.php';
    }
    $config = require $configPath;

    $host = (string) ($config['db_host'] ?? '127.0.0.1');
    $port = (int) ($config['db_port'] ?? 3306);
    $name = (string) ($config['db_name'] ?? 'clubhub');
    $charset = (string) ($config['db_charset'] ?? 'utf8mb4');
    $user = (string) ($config['db_user'] ?? 'clubhub');
    $pass = (string) ($config['db_pass'] ?? '');

    $dsn = sprintf('mysql:host=%s;port=%d;dbname=%s;charset=%s', $host, $port, $name, $charset);
    $pdo = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);

    return $pdo;
}
