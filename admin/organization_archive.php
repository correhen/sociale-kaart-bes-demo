<?php

declare(strict_types=1);

require __DIR__ . '/layout.php';
require_admin_login();

$error = '';
$status = 400;
$organizationId = (int)($_POST['id'] ?? 0);

try {
    if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
        header('Allow: POST');
        $status = 405;
        throw new RuntimeException('Alleen POST is toegestaan.');
    }
    if (!admin_can_edit_organizations()) {
        $status = 403;
        throw new RuntimeException('Je hebt geen rechten om organisaties te archiveren of te herstellen.');
    }
    if (!verify_csrf((string)($_POST['csrf_token'] ?? ''))) {
        throw new RuntimeException('Het formulier is verlopen. Probeer opnieuw.');
    }
    if ($organizationId <= 0) {
        throw new RuntimeException('Geen geldige organisatie-id opgegeven.');
    }

    $action = trim((string)($_POST['action'] ?? ''));
    if (!in_array($action, ['archive', 'restore'], true)) {
        throw new RuntimeException('Ongeldige actie.');
    }

    $pdo = admin_db();
    $pdo->beginTransaction();

    $organization = fetch_one(
        'SELECT id, slug, status, visibility_public FROM organizations WHERE id = :id LIMIT 1',
        ['id' => $organizationId]
    );
    if (!$organization) {
        $status = 404;
        throw new RuntimeException('Organisatie niet gevonden.');
    }

    $before = [
        'status' => (string)$organization['status'],
        'visibility_public' => (int)$organization['visibility_public'],
    ];
    $after = $action === 'archive'
        ? ['status' => 'archived', 'visibility_public' => 0]
        : ['status' => 'draft', 'visibility_public' => 0];

    $update = $pdo->prepare(
        "UPDATE organizations
        SET status = :status,
            visibility_public = 0
        WHERE id = :id"
    );
    $update->execute([
        'status' => $after['status'],
        'id' => $organizationId,
    ]);

    write_audit_log(
        $action === 'archive' ? 'organization.archive' : 'organization.restore',
        'organization',
        $organizationId,
        $before,
        $after
    );

    $pdo->commit();

    $message = $action === 'archive' ? 'archived=1' : 'restored=1';
    header('Location: organization.php?id=' . rawurlencode((string)$organizationId) . '&' . $message);
    exit;
} catch (Throwable $exception) {
    if (isset($pdo) && $pdo instanceof PDO && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    $knownMessages = [
        'Alleen POST is toegestaan.',
        'Je hebt geen rechten om organisaties te archiveren of te herstellen.',
        'Het formulier is verlopen. Probeer opnieuw.',
        'Geen geldige organisatie-id opgegeven.',
        'Ongeldige actie.',
        'Organisatie niet gevonden.',
    ];
    $error = in_array($exception->getMessage(), $knownMessages, true)
        ? $exception->getMessage()
        : 'De organisatie kon niet worden bijgewerkt. Probeer het later opnieuw.';
}

http_response_code($status);
admin_header('Organisatie archiveren of herstellen', 'organizations');
?>
<p class="error"><?= h($error) ?></p>
<?php if ($organizationId > 0): ?>
  <p><a class="button" href="organization.php?id=<?= h((string)$organizationId) ?>">Terug naar organisatie</a></p>
<?php else: ?>
  <p><a class="button" href="organizations.php">Terug naar organisaties</a></p>
<?php endif; ?>
<?php admin_footer(); ?>
