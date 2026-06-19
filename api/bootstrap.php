<?php

declare(strict_types=1);

ini_set('display_errors', '0');

const API_LANGUAGES = ['nl', 'pap', 'en', 'es'];
const API_ISLANDS = ['bonaire', 'statia', 'saba'];

function api_db_config(): array
{
    $path = __DIR__ . '/../config/database.php';
    if (!is_file($path)) {
        throw new RuntimeException('Databaseconfig ontbreekt.');
    }

    $config = require $path;
    if (!is_array($config)) {
        throw new RuntimeException('Databaseconfig is ongeldig.');
    }

    return $config;
}

function api_db(): PDO
{
    static $pdo = null;
    if ($pdo instanceof PDO) {
        return $pdo;
    }

    $config = api_db_config();
    $dsn = sprintf(
        'mysql:host=%s;port=%d;dbname=%s;charset=%s',
        $config['host'] ?? 'localhost',
        (int)($config['port'] ?? 3306),
        $config['database'] ?? '',
        $config['charset'] ?? 'utf8mb4'
    );

    $pdo = new PDO($dsn, $config['username'] ?? '', $config['password'] ?? '', [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
    $pdo->exec('SET NAMES utf8mb4');

    return $pdo;
}

function api_fetch_all(string $sql, array $params = []): array
{
    $stmt = api_db()->prepare($sql);
    $stmt->execute($params);

    return $stmt->fetchAll();
}

function api_json_value(?string $json, $fallback)
{
    if ($json === null || trim($json) === '') {
        return $fallback;
    }

    $decoded = json_decode($json, true);

    return is_array($decoded) ? $decoded : $fallback;
}

function api_island(string $value): string
{
    return in_array($value, API_ISLANDS, true) ? $value : 'bonaire';
}

function api_json_response(array $payload, int $status = 200): void
{
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
    header('Pragma: no-cache');
    header('X-Content-Type-Options: nosniff');

    $json = json_encode(
        $payload,
        JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_SUBSTITUTE
    );

    if ($json === false) {
        http_response_code(500);
        echo '{"error":"JSON-output kon niet worden opgebouwd."}';
        exit;
    }

    echo $json;
    exit;
}
