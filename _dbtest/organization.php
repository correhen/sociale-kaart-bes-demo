<?php

declare(strict_types=1);

require __DIR__ . '/db.php';

$slug = trim((string)($_GET['slug'] ?? ''));
$lang = allowed_lang((string)($_GET['lang'] ?? 'nl'));
$audience = allowed_audience((string)($_GET['audience'] ?? 'youth'));
$error = '';
$organization = null;
$islands = [];
$themes = [];
$answers = [];
$displayName = '';

function fetch_organization(string $slug, string $lang): ?array
{
    $stmt = db()->prepare(
        "SELECT
            o.id,
            o.slug,
            o.status,
            o.source_status,
            COALESCE(NULLIF(req.name, ''), nl.name, o.slug) AS name,
            COALESCE(NULLIF(req.youth_title, ''), nl.youth_title, '') AS youth_title,
            COALESCE(NULLIF(req.type_label, ''), nl.type_label, '') AS type_label,
            COALESCE(NULLIF(req.age_range, ''), nl.age_range, '') AS age_range,
            c.phone,
            c.whatsapp,
            c.email,
            c.website,
            c.address_nl
        FROM organizations o
        LEFT JOIN organization_translations nl
            ON nl.organization_id = o.id
            AND nl.language_code = 'nl'
            AND nl.translation_status = 'published'
        LEFT JOIN organization_translations req
            ON req.organization_id = o.id
            AND req.language_code = :lang
            AND req.translation_status = 'published'
        LEFT JOIN organization_contacts c
            ON c.organization_id = o.id
        WHERE o.slug = :slug
            AND o.status = 'published'
            AND o.visibility_public = 1
        LIMIT 1"
    );
    $stmt->execute([
        'slug' => $slug,
        'lang' => $lang,
    ]);
    $row = $stmt->fetch();

    return $row ?: null;
}

function fetch_islands(int $organizationId): array
{
    $stmt = db()->prepare(
        "SELECT i.code, i.name
        FROM organization_islands oi
        INNER JOIN islands i ON i.id = oi.island_id
        WHERE oi.organization_id = :organization_id
        ORDER BY oi.is_primary DESC, i.sort_order ASC, i.name ASC"
    );
    $stmt->execute(['organization_id' => $organizationId]);

    return $stmt->fetchAll();
}

function fetch_themes(int $organizationId, string $lang): array
{
    $stmt = db()->prepare(
        "SELECT
            t.slug,
            t.color,
            COALESCE(NULLIF(req.name, ''), nl.name, t.slug) AS name
        FROM organization_theme ot
        INNER JOIN themes t ON t.id = ot.theme_id
        LEFT JOIN theme_translations nl
            ON nl.theme_id = t.id
            AND nl.language_code = 'nl'
            AND nl.translation_status = 'published'
        LEFT JOIN theme_translations req
            ON req.theme_id = t.id
            AND req.language_code = :lang
            AND req.translation_status = 'published'
        WHERE ot.organization_id = :organization_id
        ORDER BY ot.is_primary DESC, ot.sort_order ASC, t.sort_order ASC"
    );
    $stmt->execute([
        'organization_id' => $organizationId,
        'lang' => $lang,
    ]);

    return $stmt->fetchAll();
}

function fetch_profile_answers(int $organizationId, string $audience, string $lang): array
{
    $languages = $lang === 'nl' ? ['nl'] : ['nl', $lang];
    $placeholders = implode(',', array_fill(0, count($languages), '?'));
    $params = [$organizationId, $audience, ...$languages];

    $stmt = db()->prepare(
        "SELECT group_key, field_key, language_code, answer_text, sort_order
        FROM organization_profile_answers
        WHERE organization_id = ?
            AND audience_code = ?
            AND language_code IN ($placeholders)
            AND translation_status = 'published'
            AND answer_text IS NOT NULL
            AND TRIM(answer_text) <> ''
        ORDER BY sort_order ASC, group_key ASC, field_key ASC"
    );
    $stmt->execute($params);

    $byField = [];
    foreach ($stmt->fetchAll() as $row) {
        $key = $row['group_key'] . '|' . $row['field_key'];
        if (!isset($byField[$key])) {
            $byField[$key] = $row;
            continue;
        }
        if ($row['language_code'] === $lang) {
            $byField[$key] = $row;
        }
    }

    uasort($byField, static fn(array $a, array $b): int => (int)$a['sort_order'] <=> (int)$b['sort_order']);

    return array_values($byField);
}

if ($slug === '') {
    $error = 'Geen slug opgegeven.';
} else {
    try {
        $organization = fetch_organization($slug, $lang);
        if ($organization) {
            $organizationId = (int)$organization['id'];
            $islands = fetch_islands($organizationId);
            $themes = fetch_themes($organizationId, $lang);
            $answers = fetch_profile_answers($organizationId, $audience, $lang);
            $displayName = $audience === 'youth'
                ? first_text($organization, ['youth_title', 'name'])
                : first_text($organization, ['name']);
        }
    } catch (Throwable $exception) {
        $error = $exception->getMessage();
    }
}
?>
<!doctype html>
<html lang="<?= h($lang) ?>">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= h($displayName !== '' ? $displayName : 'Organisatie') ?> - DB test</title>
</head>
<body>
  <main>
    <p><a href="./?audience=<?= h($audience) ?>&amp;lang=<?= h($lang) ?>">Terug naar DB test</a></p>

    <?php if ($error !== ''): ?>
      <h1>Database test</h1>
      <p><strong>Fout:</strong> <?= h($error) ?></p>
    <?php elseif (!$organization): ?>
      <h1>Niet gevonden</h1>
      <p>Geen gepubliceerde organisatie gevonden voor slug <code><?= h($slug) ?></code>.</p>
    <?php else: ?>
      <article>
        <h1><?= h($displayName) ?></h1>
        <dl>
          <dt>Slug</dt>
          <dd><code><?= h($organization['slug']) ?></code></dd>
          <dt>Status</dt>
          <dd><?= h($organization['status']) ?></dd>
          <dt>Bronstatus</dt>
          <dd><?= h($organization['source_status']) ?></dd>
          <?php if (trim((string)$organization['type_label']) !== ''): ?>
            <dt>Type</dt>
            <dd><?= h($organization['type_label']) ?></dd>
          <?php endif; ?>
          <?php if (trim((string)$organization['age_range']) !== ''): ?>
            <dt>Leeftijd</dt>
            <dd><?= h($organization['age_range']) ?></dd>
          <?php endif; ?>
        </dl>

        <h2>Eilanden</h2>
        <ul>
          <?php foreach ($islands as $island): ?>
            <li><?= h($island['name']) ?> <small><?= h($island['code']) ?></small></li>
          <?php endforeach; ?>
        </ul>

        <h2>Thema's</h2>
        <ul>
          <?php foreach ($themes as $theme): ?>
            <li><?= h($theme['name']) ?> <small><?= h($theme['slug']) ?></small></li>
          <?php endforeach; ?>
        </ul>

        <h2>Contact</h2>
        <dl>
          <?php foreach (['phone' => 'Telefoon', 'whatsapp' => 'WhatsApp', 'email' => 'E-mail', 'website' => 'Website', 'address_nl' => 'Adres'] as $key => $label): ?>
            <?php if (trim((string)($organization[$key] ?? '')) !== ''): ?>
              <dt><?= h($label) ?></dt>
              <dd><?= h($organization[$key]) ?></dd>
            <?php endif; ?>
          <?php endforeach; ?>
        </dl>

        <h2>Profielantwoorden</h2>
        <?php if (!$answers): ?>
          <p>Geen gepubliceerde profielantwoorden voor deze combinatie.</p>
        <?php else: ?>
          <?php foreach ($answers as $answer): ?>
            <section>
              <h3>
                <?php if ($answer['group_key'] !== ''): ?>
                  <?= h($answer['group_key']) ?> /
                <?php endif; ?>
                <?= h($answer['field_key']) ?>
              </h3>
              <p><?= nl2br(h($answer['answer_text'])) ?></p>
            </section>
          <?php endforeach; ?>
        <?php endif; ?>
      </article>
    <?php endif; ?>
  </main>
</body>
</html>
