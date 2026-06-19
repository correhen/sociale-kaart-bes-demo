<?php

declare(strict_types=1);

session_name('kadena_admin');
session_start();

const ADMIN_ALLOWED_ROLES = ['admin', 'editor', 'translator', 'viewer'];

function admin_db_config(): array
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

function admin_db(): PDO
{
    static $pdo = null;
    if ($pdo instanceof PDO) {
        return $pdo;
    }

    $config = admin_db_config();
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

function h(?string $value): string
{
    return htmlspecialchars((string)$value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}

function verify_csrf(?string $token): bool
{
    return is_string($token)
        && isset($_SESSION['csrf_token'])
        && hash_equals((string)$_SESSION['csrf_token'], $token);
}

function current_admin_user(): ?array
{
    return isset($_SESSION['admin_user']) && is_array($_SESSION['admin_user'])
        ? $_SESSION['admin_user']
        : null;
}

function admin_is_logged_in(): bool
{
    return current_admin_user() !== null;
}

function require_admin_login(): void
{
    if (admin_is_logged_in()) {
        return;
    }

    header('Location: login.php');
    exit;
}

function admin_login(string $email, string $password): bool
{
    $stmt = admin_db()->prepare(
        "SELECT id, name, email, password_hash, status
        FROM users
        WHERE email = :email
        LIMIT 1"
    );
    $stmt->execute(['email' => $email]);
    $user = $stmt->fetch();

    if (!$user || $user['status'] !== 'active' || !password_verify($password, $user['password_hash'])) {
        return false;
    }

    $rolesStmt = admin_db()->prepare(
        "SELECT r.code
        FROM user_roles ur
        INNER JOIN roles r ON r.id = ur.role_id
        WHERE ur.user_id = :user_id
        ORDER BY r.code"
    );
    $rolesStmt->execute(['user_id' => (int)$user['id']]);
    $roles = array_values(array_map(static fn(array $row): string => $row['code'], $rolesStmt->fetchAll()));
    $canLogin = count(array_intersect($roles, ADMIN_ALLOWED_ROLES)) > 0;

    if (!$canLogin) {
        return false;
    }

    session_regenerate_id(true);
    $_SESSION['admin_user'] = [
        'id' => (int)$user['id'],
        'name' => $user['name'],
        'email' => $user['email'],
        'roles' => $roles,
    ];

    $update = admin_db()->prepare('UPDATE users SET last_login_at = NOW() WHERE id = :id');
    $update->execute(['id' => (int)$user['id']]);

    return true;
}

function admin_logout(): void
{
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], (bool)$params['secure'], (bool)$params['httponly']);
    }
    session_destroy();
}

function admin_role_label(array $user): string
{
    return implode(', ', $user['roles'] ?? []);
}

function admin_has_role(string $role): bool
{
    $user = current_admin_user();
    if (!$user) {
        return false;
    }

    return in_array($role, $user['roles'] ?? [], true);
}

function admin_can_edit_organizations(): bool
{
    return admin_has_role('admin') || admin_has_role('editor');
}

function admin_can_edit_profiles(): bool
{
    return admin_can_edit_organizations() || admin_has_role('translator');
}

function admin_can_edit_profile_language(string $language): bool
{
    if (admin_can_edit_organizations()) {
        return true;
    }

    return admin_has_role('translator') && $language !== 'nl';
}

function fetch_one(string $sql, array $params = []): ?array
{
    $stmt = admin_db()->prepare($sql);
    $stmt->execute($params);
    $row = $stmt->fetch();

    return $row ?: null;
}

function fetch_all(string $sql, array $params = []): array
{
    $stmt = admin_db()->prepare($sql);
    $stmt->execute($params);

    return $stmt->fetchAll();
}

function write_audit_log(string $action, string $entityType, int $entityId, array $before, array $after): void
{
    $user = current_admin_user();
    $stmt = admin_db()->prepare(
        "INSERT INTO audit_log (user_id, action, entity_type, entity_id, before_json, after_json, ip_address, user_agent)
        VALUES (:user_id, :action, :entity_type, :entity_id, :before_json, :after_json, :ip_address, :user_agent)"
    );
    $stmt->execute([
        'user_id' => $user['id'] ?? null,
        'action' => $action,
        'entity_type' => $entityType,
        'entity_id' => $entityId,
        'before_json' => json_encode($before, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
        'after_json' => json_encode($after, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
        'ip_address' => substr((string)($_SERVER['REMOTE_ADDR'] ?? ''), 0, 45),
        'user_agent' => substr((string)($_SERVER['HTTP_USER_AGENT'] ?? ''), 0, 255),
    ]);
}
