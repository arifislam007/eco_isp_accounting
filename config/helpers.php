<?php
declare(strict_types=1);

function load_env_file(string $path): void
{
    if (!is_file($path)) {
        return;
    }

    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    if ($lines === false) {
        return;
    }

    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#') || !str_contains($line, '=')) {
            continue;
        }

        [$key, $value] = explode('=', $line, 2);
        $key = trim($key);
        $value = trim($value);

        if ($value !== '' && (($value[0] === '"' && str_ends_with($value, '"')) || ($value[0] === "'" && str_ends_with($value, "'")))) {
            $value = substr($value, 1, -1);
        }

        putenv($key . '=' . $value);
        $_ENV[$key] = $value;
        $_SERVER[$key] = $value;
    }
}

function env_value(string $key, mixed $default = null): mixed
{
    $value = $_ENV[$key] ?? $_SERVER[$key] ?? getenv($key);
    if ($value === false || $value === null || $value === '') {
        return $default;
    }

    return $value;
}

function app_name(): string
{
    return (string) env_value('APP_NAME', 'ISP Billing Dashboard');
}

function app_url(string $path = ''): string
{
    $base = rtrim((string) env_value('APP_URL', ''), '/');
    if ($base === '') {
        $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost:8080';
        $base = $scheme . '://' . $host;
    }

    return $base . '/' . ltrim($path, '/');
}

function redirect_to(string $path): never
{
    header('Location: ' . $path);
    exit;
}

function h(mixed $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function money(mixed $value): string
{
    return number_format((float) $value, 2);
}

function percent(mixed $value): string
{
    return number_format((float) $value, 2);
}

function month_value(?string $month = null): string
{
    return $month ?: date('Y-m');
}

function month_label(string $month): string
{
    $date = DateTime::createFromFormat('Y-m', $month);
    return $date ? $date->format('F Y') : $month;
}

function month_bounds(string $month): array
{
    $start = DateTime::createFromFormat('Y-m-d', $month . '-01');
    if (!$start) {
        $start = new DateTime(date('Y-m-01'));
    }

    $fifteenth = DateTime::createFromFormat('Y-m-d', $month . '-15');
    if (!$fifteenth) {
        $fifteenth = (clone $start)->setDate((int) $start->format('Y'), (int) $start->format('m'), 15);
    }

    $nextMonth = (clone $start)->modify('first day of next month');

    return [
        $start->format('Y-m-d'),
        $fifteenth->format('Y-m-d'),
        $nextMonth->format('Y-m-d'),
    ];
}

function flash(string $key, ?string $value = null): ?string
{
    if ($value !== null) {
        $_SESSION['flash'][$key] = $value;
        return null;
    }

    $message = $_SESSION['flash'][$key] ?? null;
    unset($_SESSION['flash'][$key]);
    return $message;
}

function is_post(): bool
{
    return ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST';
}
