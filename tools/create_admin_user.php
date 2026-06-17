<?php

declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    fwrite(STDERR, "Dit script mag alleen via de command line worden gebruikt.\n");
    exit(1);
}

function usage(): void
{
    echo "Maak of update een eerste admin-gebruiker.\n";
    echo "Gebruik alleen lokaal of server-side, nooit publiek via de browser.\n\n";
    echo "Voorbeeld:\n";
    echo "  php tools/create_admin_user.php --name=\"Admin\" --email=\"admin@example.org\"\n\n";
    echo "Optioneel kan --password worden meegegeven, maar interactief invoeren is veiliger.\n";
}

function option_value(string $name): ?string
{
    global $argv;
    $prefix = '--' . $name . '=';
    foreach ($argv as $argument) {
        if (str_starts_with($argument, $prefix)) {
            return substr($argument, strlen($prefix));
        }
    }

    return null;
}

function prompt(string $label, bool $required = true): string
{
    do {
        fwrite(STDOUT, $label . ': ');
        $value = trim((string)fgets(STDIN));
    } while ($required && $value === '');

    return $value;
}

function db_config(): array
{
    $path = __DIR__ . '/../config/database.php';
    if (!is_file($path)) {
        throw new RuntimeException('config/database.php ontbreekt. Kopieer config/database.example.php en vul databasegegevens in.');
    }

    $config = require $path;
    if (!is_array($config)) {
        throw new RuntimeException('config/database.php moet een array teruggeven.');
    }

    return $config;
}

function db(): PDO
{
    $config = db_config();
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

function seed_roles(PDO $pdo): void
{
    $roles = [
        ['admin', 'Admin', 'Kan gebruikers beheren, content wijzigen en publiceren.'],
        ['editor', 'Redacteur', 'Kan content wijzigen en publiceren.'],
        ['translator', 'Vertaler', 'Kan vertalingen bewerken en status voorstellen.'],
        ['viewer', 'Lezer', 'Kan admincontent bekijken zonder te wijzigen.'],
    ];

    $stmt = $pdo->prepare(
        "INSERT INTO roles (code, name, description)
        VALUES (:code, :name, :description)
        ON DUPLICATE KEY UPDATE
            name = VALUES(name),
            description = VALUES(description)"
    );

    foreach ($roles as [$code, $name, $description]) {
        $stmt->execute([
            'code' => $code,
            'name' => $name,
            'description' => $description,
        ]);
    }
}

function create_or_update_admin(PDO $pdo, string $name, string $email, string $password): int
{
    $passwordHash = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $pdo->prepare(
        "INSERT INTO users (name, email, password_hash, status)
        VALUES (:name, :email, :password_hash, 'active')
        ON DUPLICATE KEY UPDATE
            name = VALUES(name),
            password_hash = VALUES(password_hash),
            status = 'active'"
    );
    $stmt->execute([
        'name' => $name,
        'email' => $email,
        'password_hash' => $passwordHash,
    ]);

    $userIdStmt = $pdo->prepare('SELECT id FROM users WHERE email = :email LIMIT 1');
    $userIdStmt->execute(['email' => $email]);
    $userId = (int)$userIdStmt->fetchColumn();

    $roleIdStmt = $pdo->prepare("SELECT id FROM roles WHERE code = 'admin' LIMIT 1");
    $roleIdStmt->execute();
    $roleId = (int)$roleIdStmt->fetchColumn();

    $linkStmt = $pdo->prepare(
        "INSERT IGNORE INTO user_roles (user_id, role_id)
        VALUES (:user_id, :role_id)"
    );
    $linkStmt->execute([
        'user_id' => $userId,
        'role_id' => $roleId,
    ]);

    return $userId;
}

try {
    if (in_array('--help', $argv, true) || in_array('-h', $argv, true)) {
        usage();
        exit(0);
    }

    echo "Kadena Hubenil / Sociale Kaart BES admin-user seed\n";
    echo "Gebruik dit script alleen lokaal of server-side. Er wordt geen plaintext wachtwoord opgeslagen.\n\n";

    $name = option_value('name') ?: prompt('Naam');
    $email = option_value('email') ?: prompt('E-mail');
    $password = option_value('password') ?: prompt('Wachtwoord');

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new RuntimeException('Ongeldig e-mailadres.');
    }
    if (strlen($password) < 12) {
        throw new RuntimeException('Gebruik een wachtwoord van minimaal 12 tekens.');
    }

    $pdo = db();
    $pdo->beginTransaction();
    seed_roles($pdo);
    $userId = create_or_update_admin($pdo, $name, $email, $password);
    $pdo->commit();

    echo "\nAdmin-gebruiker is aangemaakt/bijgewerkt.\n";
    echo "User ID: " . $userId . "\n";
    echo "Rol: admin\n";
} catch (Throwable $exception) {
    if (isset($pdo) && $pdo instanceof PDO && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    fwrite(STDERR, "Fout: " . $exception->getMessage() . "\n");
    exit(1);
}
