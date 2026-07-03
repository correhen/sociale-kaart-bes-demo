<?php

declare(strict_types=1);

require_once __DIR__ . '/auth.php';

function admin_header(string $title, string $active = '', string $actionsHtml = ''): void
{
    $user = current_admin_user();
    ?>
<!doctype html>
<html lang="nl">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= h($title) ?> - Sociale Kaart BES Admin</title>
  <link rel="stylesheet" href="admin.css">
</head>
<body>
  <header class="admin-header">
    <div class="admin-header-main">
      <a class="admin-brand" href="dashboard.php">
        <span>Sociale Kaart BES</span>
        <small>Beheeromgeving</small>
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
        <a class="button button-small" href="change_password.php">Wachtwoord wijzigen</a>
        <a class="button button-small" href="logout.php">Uitloggen</a>
      </div>
    <?php endif; ?>
  </header>
  <main class="admin-main">
    <div class="page-title page-heading">
      <h1><?= h($title) ?></h1>
      <?php if ($actionsHtml !== ''): ?>
        <div class="page-title-actions"><?= $actionsHtml ?></div>
      <?php endif; ?>
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
    $labels = [
        'published' => 'Gepubliceerd',
        'draft' => 'Concept',
        'needs_review' => 'Review nodig',
        'review_needed' => 'Review nodig',
        'archived' => 'Archief',
        'submitted' => 'Aangeleverd',
        'verified' => 'Gecontroleerd',
        'needs_check' => 'Controle nodig',
        'expired' => 'Verlopen',
        'demo' => 'Demo/oude data',
        'public' => 'Publiek zichtbaar',
        'publiek' => 'Publiek zichtbaar',
        'publiek zichtbaar' => 'Publiek zichtbaar',
        'not public' => 'Niet publiek zichtbaar',
        'niet publiek' => 'Niet publiek zichtbaar',
        'niet publiek zichtbaar' => 'Niet publiek zichtbaar',
    ];

    return '<span class="badge badge-' . h($class) . '">' . h($labels[$status] ?? $status) . '</span>';
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

function admin_public_organization_url(array $organization, string $audience, array $islands): ?string
{
    if (
        !in_array($audience, ['youth', 'professional'], true)
        || (string)($organization['status'] ?? '') !== 'published'
        || (int)($organization['visibility_public'] ?? 0) !== 1
    ) {
        return null;
    }

    $slug = trim((string)($organization['slug'] ?? ''));
    if ($slug === '' || !preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/', $slug)) {
        return null;
    }

    $islandCode = null;
    foreach ($islands as $island) {
        $code = (string)($island['code'] ?? '');
        if ((int)($island['is_primary'] ?? 0) === 1 && in_array($code, ['bonaire', 'saba', 'statia'], true)) {
            $islandCode = $code;
            break;
        }
        if ($islandCode === null && in_array($code, ['bonaire', 'saba', 'statia'], true)) {
            $islandCode = $code;
        }
    }
    $islandCode ??= 'bonaire';

    $audiencePath = $audience === 'professional' ? 'professionals' : 'jongeren';
    $prefix = $islandCode === 'bonaire' ? '' : $islandCode . '/';

    return '../' . $prefix . $audiencePath . '/organisaties/' . rawurlencode($slug) . '/';
}
