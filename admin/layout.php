<?php

declare(strict_types=1);

require_once __DIR__ . '/auth.php';

function admin_header(string $title, string $active = ''): void
{
    $user = current_admin_user();
    ?>
<!doctype html>
<html lang="nl">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= h($title) ?> - Kadena Admin</title>
  <link rel="stylesheet" href="admin.css">
</head>
<body>
  <header class="admin-header">
    <div class="admin-header-main">
      <a class="admin-brand" href="dashboard.php">
        <span>Kadena Hubenil</span>
        <small>Sociale Kaart BES · Admin</small>
      </a>
      <nav class="admin-nav" aria-label="Admin navigatie">
        <a class="<?= $active === 'dashboard' ? 'is-active' : '' ?>" href="dashboard.php">Dashboard</a>
        <a class="<?= $active === 'organizations' ? 'is-active' : '' ?>" href="organizations.php">Organisaties</a>
        <a class="<?= $active === 'audit_log' ? 'is-active' : '' ?>" href="audit_log.php">Auditlog</a>
        <span>Thema's</span>
        <span>Feedback</span>
        <?php if (admin_can_manage_users()): ?>
          <a class="<?= $active === 'users' ? 'is-active' : '' ?>" href="users.php">Gebruikers</a>
        <?php else: ?>
          <span>Gebruikers</span>
        <?php endif; ?>
      </nav>
    </div>
    <?php if ($user): ?>
      <div class="admin-user">
        <div>
          <span><?= h($user['name'] ?? '') ?></span>
          <small><?= h(admin_role_label($user)) ?></small>
        </div>
        <a class="button button-small" href="logout.php">Uitloggen</a>
      </div>
    <?php endif; ?>
  </header>
  <main class="admin-main">
    <div class="page-title page-heading">
      <h1><?= h($title) ?></h1>
    </div>
<?php
}

function admin_footer(): void
{
    ?>
  </main>
  <script src="../assets/richtext-editor.js"></script>
</body>
</html>
<?php
}

function status_badge(string $status): string
{
    $class = preg_replace('/[^a-z0-9_-]+/i', '-', $status);

    return '<span class="badge badge-' . h($class) . '">' . h($status) . '</span>';
}

function empty_label(?string $value = null): string
{
    $text = trim((string)$value);
    if ($text === '') {
        return '<span class="muted">ontbreekt</span>';
    }

    return nl2br(h($text));
}

function readable_date(?string $value): string
{
    $date = trim((string)$value);
    if ($date === '') {
        return '<span class="muted">ontbreekt</span>';
    }

    $parsed = DateTimeImmutable::createFromFormat('!Y-m-d', $date);
    if (!$parsed || $parsed->format('Y-m-d') !== $date) {
        return h($date);
    }

    return h($parsed->format('d-m-Y'));
}

function readable_datetime(?string $value): string
{
    $dateTime = trim((string)$value);
    if ($dateTime === '') {
        return '<span class="muted">ontbreekt</span>';
    }

    $parsed = DateTimeImmutable::createFromFormat('!Y-m-d H:i:s', $dateTime);
    if (!$parsed || $parsed->format('Y-m-d H:i:s') !== $dateTime) {
        return h($dateTime);
    }

    return h($parsed->format('d-m-Y H:i'));
}
