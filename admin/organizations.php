<?php

declare(strict_types=1);

require __DIR__ . '/layout.php';
require_admin_login();

$filters = [
    'q' => trim((string)($_GET['q'] ?? '')),
    'status' => trim((string)($_GET['status'] ?? '')),
    'island' => trim((string)($_GET['island'] ?? '')),
    'source_status' => trim((string)($_GET['source_status'] ?? '')),
    'theme' => trim((string)($_GET['theme'] ?? '')),
];

$organizations = [];
$islands = [];
$themes = [];
$error = '';

try {
    $islands = fetch_all('SELECT code, name FROM islands ORDER BY sort_order ASC, name ASC');
    $themes = fetch_all(
        "SELECT t.external_key, t.slug, COALESCE(NULLIF(tt.name, ''), t.slug) AS name
        FROM themes t
        LEFT JOIN theme_translations tt
            ON tt.theme_id = t.id
            AND tt.language_code = 'nl'
        ORDER BY t.sort_order ASC, name ASC"
    );

    $where = [];
    $params = [];

    if ($filters['q'] !== '') {
        $where[] = "(o.slug LIKE :q OR ot_nl.name LIKE :q OR ot_nl.type_label LIKE :q)";
        $params['q'] = '%' . $filters['q'] . '%';
    }
    if ($filters['status'] !== '') {
        $where[] = 'o.status = :status';
        $params['status'] = $filters['status'];
    }
    if ($filters['source_status'] !== '') {
        $where[] = 'o.source_status = :source_status';
        $params['source_status'] = $filters['source_status'];
    }
    if ($filters['island'] !== '') {
        $where[] = "EXISTS (
            SELECT 1
            FROM organization_islands oi_filter
            INNER JOIN islands i_filter ON i_filter.id = oi_filter.island_id
            WHERE oi_filter.organization_id = o.id
                AND i_filter.code = :island
        )";
        $params['island'] = $filters['island'];
    }
    if ($filters['theme'] !== '') {
        $where[] = "EXISTS (
            SELECT 1
            FROM organization_theme oth_filter
            INNER JOIN themes t_filter ON t_filter.id = oth_filter.theme_id
            WHERE oth_filter.organization_id = o.id
                AND t_filter.external_key = :theme
        )";
        $params['theme'] = $filters['theme'];
    }

    $sql = "SELECT
            o.id,
            o.slug,
            o.status,
            o.source_status,
            o.updated_at,
            o.last_checked_at,
            COALESCE(NULLIF(ot_nl.name, ''), o.slug) AS name,
            ot_nl.type_label,
            (
                SELECT GROUP_CONCAT(i.name ORDER BY oi.is_primary DESC, i.sort_order ASC SEPARATOR ', ')
                FROM organization_islands oi
                INNER JOIN islands i ON i.id = oi.island_id
                WHERE oi.organization_id = o.id
            ) AS islands,
            (
                SELECT GROUP_CONCAT(COALESCE(NULLIF(tt.name, ''), t.slug) ORDER BY oth.is_primary DESC, oth.sort_order ASC SEPARATOR ', ')
                FROM organization_theme oth
                INNER JOIN themes t ON t.id = oth.theme_id
                LEFT JOIN theme_translations tt
                    ON tt.theme_id = t.id
                    AND tt.language_code = 'nl'
                WHERE oth.organization_id = o.id
            ) AS themes
        FROM organizations o
        LEFT JOIN organization_translations ot_nl
            ON ot_nl.organization_id = o.id
            AND ot_nl.language_code = 'nl'";

    if ($where) {
        $sql .= ' WHERE ' . implode(' AND ', $where);
    }

    $sql .= ' ORDER BY name ASC LIMIT 200';
    $organizations = fetch_all($sql, $params);
} catch (Throwable) {
    $error = 'Organisaties konden niet worden geladen.';
}

admin_header('Organisaties', 'organizations');
?>
<?php if ($error !== ''): ?>
  <p class="error"><?= h($error) ?></p>
<?php endif; ?>

<section class="panel">
  <form class="filters" method="get" action="organizations.php">
    <label>
      Zoekterm
      <input name="q" value="<?= h($filters['q']) ?>" placeholder="Naam, slug of type">
    </label>
    <label>
      Status
      <select name="status">
        <option value="">Alle</option>
        <?php foreach (['draft', 'published', 'needs_review', 'archived'] as $status): ?>
          <option value="<?= h($status) ?>" <?= $filters['status'] === $status ? 'selected' : '' ?>><?= h($status) ?></option>
        <?php endforeach; ?>
      </select>
    </label>
    <label>
      Eiland
      <select name="island">
        <option value="">Alle</option>
        <?php foreach ($islands as $island): ?>
          <option value="<?= h($island['code']) ?>" <?= $filters['island'] === $island['code'] ? 'selected' : '' ?>><?= h($island['name']) ?></option>
        <?php endforeach; ?>
      </select>
    </label>
    <label>
      Bronstatus
      <select name="source_status">
        <option value="">Alle</option>
        <?php foreach (['demo', 'submitted', 'verified', 'needs_check', 'expired'] as $sourceStatus): ?>
          <option value="<?= h($sourceStatus) ?>" <?= $filters['source_status'] === $sourceStatus ? 'selected' : '' ?>><?= h($sourceStatus) ?></option>
        <?php endforeach; ?>
      </select>
    </label>
    <label>
      Thema
      <select name="theme">
        <option value="">Alle</option>
        <?php foreach ($themes as $theme): ?>
          <option value="<?= h($theme['external_key']) ?>" <?= $filters['theme'] === $theme['external_key'] ? 'selected' : '' ?>><?= h($theme['name']) ?></option>
        <?php endforeach; ?>
      </select>
    </label>
    <button type="submit">Filter</button>
  </form>
</section>

<section class="panel">
  <div class="table-wrap">
    <table>
      <thead>
        <tr>
          <th>Naam</th>
          <th>Slug</th>
          <th>Status</th>
          <th>Bronstatus</th>
          <th>Eiland(en)</th>
          <th>Thema's</th>
          <th>Type</th>
          <th>Bijgewerkt</th>
          <th>Laatst gecontroleerd</th>
        </tr>
      </thead>
      <tbody>
        <?php if (!$organizations): ?>
          <tr><td colspan="9" class="muted">Geen organisaties gevonden.</td></tr>
        <?php endif; ?>
        <?php foreach ($organizations as $organization): ?>
          <tr>
            <td><a href="organization.php?id=<?= h((string)$organization['id']) ?>"><?= h($organization['name']) ?></a></td>
            <td><code><?= h($organization['slug']) ?></code></td>
            <td><?= status_badge($organization['status']) ?></td>
            <td><?= status_badge($organization['source_status']) ?></td>
            <td><?= empty_label($organization['islands']) ?></td>
            <td><?= empty_label($organization['themes']) ?></td>
            <td><?= empty_label($organization['type_label']) ?></td>
            <td><?= h((string)$organization['updated_at']) ?></td>
            <td><?= readable_date($organization['last_checked_at']) ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</section>
<?php admin_footer(); ?>
