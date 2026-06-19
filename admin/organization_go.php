<?php

declare(strict_types=1);

require __DIR__ . '/layout.php';
require_admin_login();

$slug = trim((string)($_GET['slug'] ?? ''));
$target = trim((string)($_GET['target'] ?? 'view'));
$audience = trim((string)($_GET['audience'] ?? ''));
$error = '';
$status = 400;

try {
    if (
        $slug === ''
        || strlen($slug) > 180
        || !preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/', $slug)
    ) {
        throw new RuntimeException('Ongeldige organisatieslug.');
    }
    if (!in_array($target, ['view', 'basic', 'profile'], true)) {
        throw new RuntimeException('Ongeldig beheerdoel.');
    }
    if ($target === 'profile' && !in_array($audience, ['youth', 'professional'], true)) {
        throw new RuntimeException('Ongeldig profieltype.');
    }

    $organization = fetch_one(
        'SELECT id FROM organizations WHERE slug = :slug LIMIT 1',
        ['slug' => $slug]
    );
    if (!$organization) {
        $status = 404;
        throw new RuntimeException('Organisatie niet gevonden.');
    }

    $id = (int)$organization['id'];
    if ($target === 'view') {
        header('Location: organization.php?id=' . rawurlencode((string)$id));
        exit;
    }
    if ($target === 'basic') {
        if (!admin_can_edit_organizations()) {
            $status = 403;
            throw new RuntimeException('Je hebt geen rechten om basisgegevens te bewerken.');
        }
        header('Location: organization_edit.php?id=' . rawurlencode((string)$id));
        exit;
    }
    if (!admin_can_edit_profiles()) {
        $status = 403;
        throw new RuntimeException('Je hebt geen rechten om profielen te bewerken.');
    }

    header(
        'Location: organization_profile_edit.php?id='
        . rawurlencode((string)$id)
        . '&audience='
        . rawurlencode($audience)
    );
    exit;
} catch (Throwable $exception) {
    $knownMessages = [
        'Ongeldige organisatieslug.',
        'Ongeldig beheerdoel.',
        'Ongeldig profieltype.',
        'Organisatie niet gevonden.',
        'Je hebt geen rechten om basisgegevens te bewerken.',
        'Je hebt geen rechten om profielen te bewerken.',
    ];
    $error = in_array($exception->getMessage(), $knownMessages, true)
        ? $exception->getMessage()
        : 'De beheerpagina kon niet worden geopend. Probeer het later opnieuw.';
}

http_response_code($status);
admin_header('Beheerpagina openen', 'organizations');
?>
<p class="error"><?= h($error) ?></p>
<p><a class="button" href="organizations.php">Terug naar organisaties</a></p>
<?php admin_footer(); ?>
