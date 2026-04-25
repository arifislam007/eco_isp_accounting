<?php
declare(strict_types=1);

define('APP_ROOT', dirname(__DIR__));

require_once APP_ROOT . '/config/helpers.php';
load_env_file(APP_ROOT . '/.env');

$timezone = (string) env_value('APP_TIMEZONE', 'UTC');
date_default_timezone_set($timezone);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once APP_ROOT . '/config/database.php';
require_once APP_ROOT . '/config/auth.php';

spl_autoload_register(function (string $class): void {
    $paths = [
        APP_ROOT . '/controllers/' . $class . '.php',
        APP_ROOT . '/models/' . $class . '.php',
    ];

    foreach ($paths as $path) {
        if (is_file($path)) {
            require_once $path;
            return;
        }
    }
});
