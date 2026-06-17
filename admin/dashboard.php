<?php

declare(strict_types=1);

require __DIR__ . '/layout.php';
require_admin_login();

$error = '';
$stats = [
    'total' => 0,
    'published' => 0,
    'draft' => 0,
    'archived' => 0,
    'recent' => 0,
];
$byIsland = [];
$bySource = [];
$missingTranslations = [];

try {
    $stats = fetch_one(
        "SELECT
            COUNT(*) AS total,
            SUM(status = 'published') AS published,
            SUM(status = 'draft') AS draft,
            SUM(status = 'archived') AS archived,
            SUM(updated_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)) AS recent
        FROM organizations"
    ) ?: $stats;

    $byIsland = fetch_all(
        "SELECT i.name, i.code, COUNT(oi.organization_id) AS total
        FROM islands i
        LEFT JOIN organization_islands oi ON oi.island_id = i.id
        GROUP BY i.id, i.name, i.code, i.sort_order
        ORDER BY i.sort_order ASC, i.name ASC"
    );

    $bySource = fetch_all(
        "SELECT source_status, COUNT(*) AS total
        FROM organizations
        GROUP BY source_status
        ORDER BY source_status ASC"
    );

    $missingTranslations = fetch_all(
        "SELECT language_code, SUM(total) AS total
        FROM (
            SELECT language_code, COUNT(*) AS total
            FROM organization_translations
            WHERE language_code <> 'nl'
                AND translation_status = 'missing'
            GROUP BY language_code
            UNION ALL
            SELECT language_code, COUNT(*) AS total
            FROM organization_profile_answers
            WHERE language_code <> 'nl'
                AND translation_status = 'missing'
            GROUP BY language_code
        ) missing
        GROUP BY language_code
        ORDER BY language_code ASC"
    );
} catch (Throwable) {
    $error = 'Dashboardstatistieken konden niet worden geladen.';
}

admin_header('Dashboard', 'dashboard');
?>
<?php if ($error !== ''): ?>
  <p class="error"><?= h($error) ?></p>
<?php endif; ?>

<section class="grid" aria-label="Organisatiestatistieken">
  <div class="card"><span>Totaal organisaties</span><strong><?= h((string)($stats['total'] ?? 0)) ?></strong></div>
  <div class="card"><span>Gepubliceerd</span><strong><?= h((string)($stats['published'] ?? 0)) ?></strong></div>
  <div class="card"><span>Concepten</span><strong><?= h((string)($stats['draft'] ?? 0)) ?></strong></div>
  <div class="card"><span>Gearchiveerd</span><strong><?= h((string)($stats['archived'] ?? 0)) ?></strong></div>
  <div class="card"><span>Gewijzigd laatste 30 dagen</span><strong><?= h((string)($stats['recent'] ?? 0)) ?></strong></div>
</section>

<section class="panel">
  <h2>Organisaties per eiland</h2>
  <div class="table-wrap">
    <table>
      <thead><tr><th>Eiland</th><th>Code</th><th>Aantal</th></tr></thead>
      <tbody>
        <?php foreach ($byIsland as $row): ?>
          <tr>
            <td><?= h($row['name']) ?></td>
            <td><code><?= h($row['code']) ?></code></td>
            <td><?= h((string)$row['total']) ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</section>

<section class="panel">
  <h2>Organisaties per bronstatus</h2>
  <div class="table-wrap">
    <table>
      <thead><tr><th>Bronstatus</th><th>Aantal</th></tr></thead>
      <tbody>
        <?php foreach ($bySource as $row): ?>
          <tr>
            <td><?= status_badge($row['source_status']) ?></td>
            <td><?= h((string)$row['total']) ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</section>

<section class="panel">
  <h2>Ontbrekende organisatievertalingen</h2>
  <p class="muted">Gebaseerd op ontbrekende organisatievertalingen en profielantwoordvertalingen.</p>
  <div class="table-wrap">
    <table>
      <thead><tr><th>Taal</th><th>Aantal</th></tr></thead>
      <tbody>
        <?php foreach ($missingTranslations as $row): ?>
          <tr>
            <td><code><?= h($row['language_code']) ?></code></td>
            <td><?= h((string)$row['total']) ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</section>
<?php admin_footer(); ?>
