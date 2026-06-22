<?php

declare(strict_types=1);

require __DIR__ . '/layout.php';
require_admin_login();

const USER_ROLES = ['admin', 'editor', 'translator', 'viewer'];
const USER_STATUSES = ['active', 'disabled'];

if (!admin_can_manage_users()) {
    http_response_code(403);
    admin_header('Geen toegang', '');
    ?>
    <p class="error">Alleen een admin mag gebruikers beheren.</p>
    <?php
    admin_footer();
    exit;
}

$id = (int)($_GET['id'] ?? $_POST['id'] ?? 0);
$isCreate = $id <= 0;
$sessionUser = current_admin_user();
$isCurrentUser = !$isCreate && (int)($sessionUser['id'] ?? 0) === $id;
$user = null;
$error = '';
$errors = [];
$values = [
    'name' => '',
    'email' => '',
    'role' => 'viewer',
    'user_status' => 'active',
    'password' => '',
];

function user_snapshot(array $user, string $role): array
{
    return [
        'name' => (string)$user['name'],
        'email' => (string)$user['email'],
        'role' => $role,
        'user_status' => (string)$user['status'],
    ];
}

function user_posted_values(): array
{
    return [
        'name' => (string)($_POST['name'] ?? ''),
        'email' => trim((string)($_POST['email'] ?? '')),
        'role' => trim((string)($_POST['role'] ?? '')),
        'user_status' => trim((string)($_POST['user_status'] ?? '')),
        'password' => (string)($_POST['password'] ?? ''),
    ];
}

function user_validate(array $values, bool $isCreate): array
{
    $errors = [];
    if (trim($values['name']) === '') {
        $errors[] = 'Naam mag niet leeg zijn.';
    }
    if (!filter_var($values['email'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'E-mail is ongeldig.';
    }
    if (!in_array($values['role'], USER_ROLES, true)) {
        $errors[] = 'Ongeldige rol.';
    }
    if (!in_array($values['user_status'], USER_STATUSES, true)) {
        $errors[] = 'Ongeldige gebruikersstatus.';
    }
    if ($isCreate && $values['password'] === '') {
        $errors[] = 'Wachtwoord is verplicht bij een nieuwe gebruiker.';
    }
    if ($values['password'] !== '' && strlen($values['password']) < 12) {
        $errors[] = 'Wachtwoord moet minimaal 12 tekens bevatten.';
    }

    return $errors;
}

function user_audit_changes(array $before, array $after): array
{
    $changedBefore = [];
    $changedAfter = [];
    foreach ($after as $key => $value) {
        if (!audit_values_differ($before[$key] ?? null, $value)) {
            continue;
        }
        $changedBefore[$key] = $before[$key] ?? null;
        $changedAfter[$key] = $value;
    }

    return [$changedBefore, $changedAfter];
}

try {
    if (!$isCreate) {
        $user = fetch_one(
            "SELECT u.id, u.name, u.email, u.status, u.last_login_at, u.created_at
            FROM users u
            WHERE u.id = :id
            LIMIT 1",
            ['id' => $id]
        );
        if (!$user) {
            throw new RuntimeException('Gebruiker niet gevonden.');
        }
        $roleRow = fetch_one(
            "SELECT r.code
            FROM user_roles ur
            INNER JOIN roles r ON r.id = ur.role_id
            WHERE ur.user_id = :user_id
              AND r.code IN ('admin', 'editor', 'translator', 'viewer')
            ORDER BY r.code ASC
            LIMIT 1",
            ['user_id' => $id]
        );
        $values = user_snapshot($user, (string)($roleRow['code'] ?? 'viewer')) + ['password' => ''];
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (!verify_csrf((string)($_POST['csrf_token'] ?? ''))) {
            $errors[] = 'Het formulier is verlopen. Probeer opnieuw.';
        }

        $values = user_posted_values();
        $errors = array_merge($errors, user_validate($values, $isCreate));
        if ($isCurrentUser && ($values['role'] !== 'admin' || $values['user_status'] !== 'active')) {
            $errors[] = 'Je kunt je eigen adminrol of actieve toegang niet uitschakelen.';
        }
        if ($isCurrentUser && $values['password'] !== '') {
            $errors[] = 'Wijzig je eigen wachtwoord via de pagina Wachtwoord wijzigen.';
        }

        if (!$errors) {
            $pdo = admin_db();
            $pdo->beginTransaction();

            if ($isCreate) {
                $insert = $pdo->prepare(
                    "INSERT INTO users (name, email, password_hash, status)
                    VALUES (:name, :email, :password_hash, :status)"
                );
                $insert->execute([
                    'name' => $values['name'],
                    'email' => $values['email'],
                    'password_hash' => password_hash($values['password'], PASSWORD_DEFAULT),
                    'status' => $values['user_status'],
                ]);
                $id = (int)$pdo->lastInsertId();
                $beforeAudit = [];
            } else {
                $beforeAudit = user_snapshot($user, (string)($roleRow['code'] ?? 'viewer'));
                if ($values['password'] !== '') {
                    $update = $pdo->prepare(
                        "UPDATE users
                        SET name = :name,
                            email = :email,
                            status = :status,
                            password_hash = :password_hash
                        WHERE id = :id"
                    );
                    $update->execute([
                        'name' => $values['name'],
                        'email' => $values['email'],
                        'status' => $values['user_status'],
                        'password_hash' => password_hash($values['password'], PASSWORD_DEFAULT),
                        'id' => $id,
                    ]);
                } else {
                    $update = $pdo->prepare(
                        "UPDATE users
                        SET name = :name,
                            email = :email,
                            status = :status
                        WHERE id = :id"
                    );
                    $update->execute([
                        'name' => $values['name'],
                        'email' => $values['email'],
                        'status' => $values['user_status'],
                        'id' => $id,
                    ]);
                }
            }

            $roleStmt = $pdo->prepare(
                "SELECT id
                FROM roles
                WHERE code = :code
                LIMIT 1"
            );
            $roleStmt->execute(['code' => $values['role']]);
            $roleId = (int)$roleStmt->fetchColumn();
            if ($roleId <= 0) {
                throw new RuntimeException('De gekozen rol bestaat niet in de database.');
            }

            $deleteRoles = $pdo->prepare('DELETE FROM user_roles WHERE user_id = :user_id');
            $deleteRoles->execute(['user_id' => $id]);
            $insertRole = $pdo->prepare(
                "INSERT INTO user_roles (user_id, role_id)
                VALUES (:user_id, :role_id)"
            );
            $insertRole->execute([
                'user_id' => $id,
                'role_id' => $roleId,
            ]);

            $afterAudit = [
                'name' => $values['name'],
                'email' => $values['email'],
                'role' => $values['role'],
                'user_status' => $values['user_status'],
            ];
            [$changedBefore, $changedAfter] = user_audit_changes($beforeAudit, $afterAudit);
            if ($isCreate || $changedAfter) {
                write_audit_log(
                    $isCreate ? 'user.create' : 'user.update',
                    'user',
                    $id,
                    $changedBefore,
                    $changedAfter
                );
            }
            if (!$isCreate && $values['password'] !== '') {
                write_audit_log(
                    'user.admin_reset_password',
                    'user',
                    $id,
                    [],
                    ['password' => 'gewijzigd']
                );
            }

            $pdo->commit();

            if ($isCurrentUser) {
                $_SESSION['admin_user']['name'] = $values['name'];
                $_SESSION['admin_user']['email'] = $values['email'];
                $_SESSION['admin_user']['roles'] = [$values['role']];
            }

            header('Location: user_edit.php?id=' . rawurlencode((string)$id) . '&saved=1');
            exit;
        }
    }
} catch (PDOException $exception) {
    if (isset($pdo) && $pdo instanceof PDO && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    $error = (string)$exception->getCode() === '23000'
        ? 'Dit e-mailadres is al in gebruik.'
        : 'De gebruiker kon niet worden opgeslagen. Probeer het later opnieuw.';
} catch (Throwable $exception) {
    if (isset($pdo) && $pdo instanceof PDO && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    $knownMessages = [
        'Gebruiker niet gevonden.',
        'De gekozen rol bestaat niet in de database.',
    ];
    $error = in_array($exception->getMessage(), $knownMessages, true)
        ? $exception->getMessage()
        : 'De gebruiker kon niet worden geladen of opgeslagen. Probeer het later opnieuw.';
}

admin_header($isCreate ? 'Gebruiker aanmaken' : 'Gebruiker bewerken', 'users');
?>
<?php if ($error !== ''): ?>
  <p class="error"><?= h($error) ?></p>
<?php endif; ?>

<?php if ((string)($_GET['saved'] ?? '') === '1'): ?>
  <p class="notice">De gebruiker is succesvol opgeslagen.</p>
<?php endif; ?>

<?php if ($errors): ?>
  <section class="panel">
    <h2>Controleer de invoer</h2>
    <ul class="error-list">
      <?php foreach ($errors as $message): ?>
        <li><?= h($message) ?></li>
      <?php endforeach; ?>
    </ul>
  </section>
<?php endif; ?>

<section class="page-intro compact">
  <div><p class="eyebrow">Toegangsbeheer</p><h2><?= $isCreate ? 'Nieuw account' : 'Accountgegevens' ?></h2><p>Rollen bepalen welke onderdelen van de beheeromgeving toegankelijk zijn.</p></div>
</section>

<form method="post" action="user_edit.php<?= $isCreate ? '' : '?id=' . h((string)$id) ?>" class="panel edit-form">
  <input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>">
  <?php if (!$isCreate): ?>
    <input type="hidden" name="id" value="<?= h((string)$id) ?>">
  <?php endif; ?>

  <div class="form-grid">
    <label>
      Naam
      <input name="name" value="<?= h($values['name']) ?>" required>
    </label>
    <label>
      E-mail
      <input name="email" type="email" value="<?= h($values['email']) ?>" required>
    </label>
    <label>
      Rol
      <select name="role" required>
        <?php foreach (USER_ROLES as $role): ?>
          <option value="<?= h($role) ?>" <?= $values['role'] === $role ? 'selected' : '' ?>><?= h($role) ?></option>
        <?php endforeach; ?>
      </select>
    </label>
    <label>
      Status
      <select name="user_status" required>
        <?php foreach (USER_STATUSES as $status): ?>
          <option value="<?= h($status) ?>" <?= $values['user_status'] === $status ? 'selected' : '' ?>><?= h($status) ?></option>
        <?php endforeach; ?>
      </select>
    </label>
  </div>

  <?php if ($isCreate || !$isCurrentUser): ?>
    <section class="form-section password-section">
      <div class="section-heading"><div><p class="eyebrow">Beveiliging</p><h2><?= $isCreate ? 'Wachtwoord instellen' : 'Wachtwoord resetten' ?></h2></div></div>
      <label>
        <?= $isCreate ? 'Nieuw wachtwoord' : 'Nieuw wachtwoord instellen' ?>
        <input name="password" type="password" minlength="12" autocomplete="new-password" <?= $isCreate ? 'required' : '' ?>>
        <small><?= $isCreate ? 'Minimaal 12 tekens.' : 'Laat leeg om het bestaande wachtwoord te behouden. Minimaal 12 tekens bij een reset.' ?> Het wachtwoord wordt nooit zichtbaar opgeslagen.</small>
      </label>
    </section>
  <?php else: ?>
    <section class="form-section password-section">
      <div class="section-heading"><div><p class="eyebrow">Beveiliging</p><h2>Eigen wachtwoord</h2></div></div>
      <p>Wijzig je eigen wachtwoord via <a href="change_password.php">Wachtwoord wijzigen</a>. Daar wordt eerst je huidige wachtwoord gecontroleerd.</p>
    </section>
  <?php endif; ?>

  <?php if (!$isCreate && $user): ?>
    <dl class="detail-list">
      <dt>Laatste login</dt><dd><?= readable_datetime($user['last_login_at']) ?></dd>
      <dt>Aangemaakt</dt><dd><?= readable_datetime($user['created_at']) ?></dd>
    </dl>
  <?php endif; ?>

  <div class="form-actions sticky-actions">
    <button type="submit">Opslaan</button>
    <a class="button" href="users.php">Terug naar gebruikers</a>
  </div>
</form>

<?php admin_footer(); ?>
