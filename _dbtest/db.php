<?php

declare(strict_types=1);

function db_config(): array
{
    $path = __DIR__ . '/../config/database.php';
    if (!is_file($path)) {
        throw new RuntimeException('Databaseconfig ontbreekt. Kopieer config/database.example.php naar config/database.php en vul lokale gegevens in.');
    }

    $config = require $path;
    if (!is_array($config)) {
        throw new RuntimeException('Databaseconfig moet een array teruggeven.');
    }

    return $config;
}

function db(): PDO
{
    static $pdo = null;
    if ($pdo instanceof PDO) {
        return $pdo;
    }

    $config = db_config();
    $host = $config['host'] ?? 'localhost';
    $port = (int)($config['port'] ?? 3306);
    $database = $config['database'] ?? '';
    $charset = $config['charset'] ?? 'utf8mb4';

    $dsn = sprintf('mysql:host=%s;port=%d;dbname=%s;charset=%s', $host, $port, $database, $charset);
    $pdo = new PDO($dsn, $config['username'] ?? '', $config['password'] ?? '', [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);

    $pdo->exec("SET NAMES utf8mb4");

    return $pdo;
}

function h(?string $value): string
{
    return htmlspecialchars((string)$value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function allowed_lang(string $lang): string
{
    return in_array($lang, ['nl', 'pap', 'en', 'es'], true) ? $lang : 'nl';
}

function allowed_audience(string $audience): string
{
    return in_array($audience, ['youth', 'professional'], true) ? $audience : 'youth';
}

function first_text(array $row, array $keys): string
{
    foreach ($keys as $key) {
        $value = trim((string)($row[$key] ?? ''));
        if ($value !== '') {
            return $value;
        }
    }

    return '';
}
