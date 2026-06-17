<?php

declare(strict_types=1);

require __DIR__ . '/db.php';

$lang = allowed_lang((string)($_GET['lang'] ?? 'nl'));
$audience = allowed_audience((string)($_GET['audience'] ?? 'youth'));

try {
    $stmt = db()->prepare(
        "SELECT
            o.slug,
            COALESCE(NULLIF(req.name, ''), nl.name, o.slug) AS name
        FROM organizations o
        LEFT JOIN organization_translations nl
            ON nl.organization_id = o.id
            AND nl.language_code = 'nl'
            AND nl.translation_status = 'published'
        LEFT JOIN organization_translations req
            ON req.organization_id = o.id
            AND req.language_code = :lang
            AND req.translation_status = 'published'
        WHERE o.status = 'published'
            AND o.visibility_public = 1
        ORDER BY name ASC
        LIMIT 50"
    );
    $stmt->execute(['lang' => $lang]);
    $organizations = $stmt->fetchAll();
    $error = '';
} catch (Throwable $exception) {
    $organizations = [];
    $error = $exception->getMessage();
}
?>
<!doctype html>
<html lang="<?= h($lang) ?>">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>DB test - Kadena Hubenil</title>
</head>
<body>
  <main>
    <h1>Database test</h1>
    <p>Read-only prototype. Deze pagina is niet gekoppeld aan de publieke navigatie.</p>

    <?php if ($error !== ''): ?>
      <p><strong>Databasefout:</strong> <?= h($error) ?></p>
    <?php elseif (!$organizations): ?>
      <p>Geen gepubliceerde organisaties gevonden.</p>
    <?php else: ?>
      <ul>
        <?php foreach ($organizations as $organization): ?>
          <li>
            <a href="organization.php?slug=<?= rawurlencode($organization['slug']) ?>&amp;audience=<?= h($audience) ?>&amp;lang=<?= h($lang) ?>">
              <?= h($organization['name']) ?>
            </a>
            <small><?= h($organization['slug']) ?></small>
          </li>
        <?php endforeach; ?>
      </ul>
    <?php endif; ?>
  </main>
</body>
</html>
