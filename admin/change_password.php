<?php

declare(strict_types=1);

require __DIR__ . '/layout.php';
require_admin_login();

$errors = [];
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $currentPassword = (string)($_POST['current_password'] ?? '');
    $newPassword = (string)($_POST['new_password'] ?? '');
    $newPasswordConfirmation = (string)($_POST['new_password_confirmation'] ?? '');

    if (!verify_csrf((string)($_POST['csrf_token'] ?? ''))) {
        $errors[] = 'Het formulier is verlopen. Probeer opnieuw.';
    }
    if ($currentPassword === '') {
        $errors[] = 'Vul je huidige wachtwoord in.';
    }
    if ($newPassword === '') {
        $errors[] = 'Vul een nieuw wachtwoord in.';
    } elseif (strlen($newPassword) < 12) {
        $errors[] = 'Het nieuwe wachtwoord moet minimaal 12 tekens bevatten.';
    }
    if ($newPassword !== $newPasswordConfirmation) {
        $errors[] = 'Het nieuwe wachtwoord en de herhaling komen niet overeen.';
    }

    if (!$errors) {
        try {
            $currentUser = current_admin_user();
            $userId = (int)($currentUser['id'] ?? 0);
            $user = fetch_one(
                "SELECT id, password_hash
                FROM users
                WHERE id = :id
                  AND status = 'active'
                LIMIT 1",
                ['id' => $userId]
            );

            if (!$user || !password_verify($currentPassword, (string)$user['password_hash'])) {
                $errors[] = 'Het huidige wachtwoord is niet juist.';
            } else {
                $pdo = admin_db();
                $pdo->beginTransaction();

                $update = $pdo->prepare(
                    "UPDATE users
                    SET password_hash = :password_hash
                    WHERE id = :id"
                );
                $update->execute([
                    'password_hash' => password_hash($newPassword, PASSWORD_DEFAULT),
                    'id' => $userId,
                ]);

                write_audit_log(
                    'user.change_own_password',
                    'user',
                    $userId,
                    [],
                    ['password' => 'gewijzigd']
                );

                $pdo->commit();
                session_regenerate_id(true);

                header('Location: change_password.php?changed=1');
                exit;
            }
        } catch (Throwable) {
            if (isset($pdo) && $pdo instanceof PDO && $pdo->inTransaction()) {
                $pdo->rollBack();
            }
            $error = 'Je wachtwoord kon niet worden gewijzigd. Probeer het later opnieuw.';
        }
    }
}

admin_header('Wachtwoord wijzigen', 'change_password');
?>
<?php if ($error !== ''): ?>
  <p class="error"><?= h($error) ?></p>
<?php endif; ?>

<?php if ((string)($_GET['changed'] ?? '') === '1'): ?>
  <p class="notice">Je wachtwoord is gewijzigd.</p>
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
  <div>
    <p class="eyebrow">Accountbeveiliging</p>
    <h2>Kies een nieuw wachtwoord</h2>
    <p>Gebruik minimaal 12 tekens. Je huidige wachtwoord wordt gecontroleerd voordat de wijziging wordt opgeslagen.</p>
  </div>
</section>

<form method="post" action="change_password.php" class="panel edit-form">
  <input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>">

  <div class="form-grid">
    <label>
      Huidig wachtwoord
      <input name="current_password" type="password" autocomplete="current-password" required>
    </label>
    <label>
      Nieuw wachtwoord
      <input name="new_password" type="password" minlength="12" autocomplete="new-password" required>
    </label>
    <label>
      Nieuw wachtwoord herhalen
      <input name="new_password_confirmation" type="password" minlength="12" autocomplete="new-password" required>
    </label>
  </div>

  <div class="form-actions sticky-actions">
    <button type="submit">Wachtwoord wijzigen</button>
    <a class="button" href="dashboard.php">Annuleren</a>
  </div>
</form>

<?php admin_footer(); ?>
