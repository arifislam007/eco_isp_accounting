<?php
declare(strict_types=1);

function db(): PDO
{
    static $pdo = null;

    if ($pdo instanceof PDO) {
        return $pdo;
    }

    $host = (string) env_value('DB_HOST', 'mysql');
    $port = (string) env_value('DB_PORT', '3306');
    $name = (string) env_value('DB_NAME', 'isp_billing');
    $charset = (string) env_value('DB_CHARSET', 'utf8mb4');
    $user = (string) env_value('DB_USER', 'isp_user');
    $pass = (string) env_value('DB_PASS', 'isp_secret');

    $dsn = sprintf('mysql:host=%s;port=%s;dbname=%s;charset=%s', $host, $port, $name, $charset);
    $pdo = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);

    return $pdo;
}
