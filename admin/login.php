<?php

declare(strict_types=1);

require __DIR__ . '/auth.php';

if (admin_is_logged_in()) {
    header('Location: dashboard.php');
    exit;
}

$error = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim((string)($_POST['email'] ?? ''));
    $password = (string)($_POST['password'] ?? '');
    $token = (string)($_POST['csrf_token'] ?? '');

    if (!verify_csrf($token)) {
        $error = 'Het formulier is verlopen. Probeer opnieuw.';
    } elseif ($email === '' || $password === '') {
        $error = 'Vul e-mail en wachtwoord in.';
    } else {
        try {
            if (admin_login($email, $password)) {
                header('Location: dashboard.php');
                exit;
            }
            $error = 'Inloggen is niet gelukt.';
        } catch (Throwable) {
            $error = 'Inloggen is tijdelijk niet mogelijk.';
        }
    }
}
?>
<!doctype html>
<html lang="nl">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Inloggen - Sociale Kaart BES Admin</title>
  <link rel="stylesheet" href="admin.css">
</head>
<body class="login-page">
  <main class="login-panel">
    <p class="eyebrow">Beheeromgeving</p>
    <h1>Admin login</h1>
    <p class="muted">Sociale Kaart BES</p>

    <?php if ($error !== ''): ?>
      <p class="error"><?= h($error) ?></p>
    <?php endif; ?>

    <form method="post" action="login.php">
      <input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>">
      <label>
        E-mail
        <input name="email" type="email" value="<?= h($email) ?>" autocomplete="username" required>
      </label>
      <label>
        Wachtwoord
        <input name="password" type="password" autocomplete="current-password" required>
      </label>
      <button type="submit">Inloggen</button>
    </form>
  </main>
</body>
</html>
