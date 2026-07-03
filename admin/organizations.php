<?php

declare(strict_types=1);

require __DIR__ . '/layout.php';
require_admin_login();

$filters = [
    'status' => trim((string)($_GET['status'] ?? '')),
    'island' => trim((string)($_GET['island'] ?? '')),
    'theme' => trim((string)($_GET['theme'] ?? '')),
    'show_archived' => (string)($_GET['show_archived'] ?? '') === '1',
];

$organizations = [];
$islands = [];
$themes = [];
$error = '';

function admin_icon(string $path, string $class = 'admin-icon'): string
{
    return '<img class="' . h($class) . '" src="../assets/admin-icons/admin_assetpack_sociale_kaart_bes_v1/' . h($path) . '" alt="" aria-hidden="true">';
}

function organization_status_label(string $status): string
{
    return [
        'published' => 'Gepubliceerd',
        'draft' => 'Concept',
        'needs_review' => 'Review nodig',
        'archived' => 'Archief',
    ][$status] ?? ucfirst(str_replace('_', ' ', $status));
}

function organization_status_badge(string $status): string
{
    $class = preg_replace('/[^a-z0-9_-]+/i', '-', $status);

    return '<span class="badge badge-' . h($class) . '">' . h(organization_status_label($status)) . '</span>';
}

function source_status_label(string $status): string
{
    return [
        'demo' => 'Demo/oud',
        'submitted' => 'Aangeleverd',
        'verified' => 'Gecontroleerd',
        'needs_check' => 'Controleren',
        'expired' => 'Verouderd',
    ][$status] ?? ucfirst(str_replace('_', ' ', $status));
}

function visible_date(?string $value): string
{
    $dateTime = trim((string)$value);
    if ($dateTime === '') {
        return 'Onbekend';
    }

    $parsed = DateTimeImmutable::createFromFormat('!Y-m-d H:i:s', $dateTime);
    if (!$parsed || $parsed->format('Y-m-d H:i:s') !== $dateTime) {
        return h($dateTime);
    }

    return h($parsed->format('d-m-Y'));
}

function split_labels(?string $value): array
{
    $parts = array_filter(array_map('trim', explode(',', (string)$value)), static fn(string $part): bool => $part !== '');

    return array_values($parts);
}

function render_compact_labels(?string $value, string $emptyText = 'Geen'): string
{
    $labels = split_labels($value);
    if (!$labels) {
        return '<span class="muted">' . h($emptyText) . '</span>';
    }

    $visible = array_slice($labels, 0, 2);
    $html = '<span class="compact-badges">';
    foreach ($visible as $label) {
        $html .= '<span class="badge">' . h($label) . '</span>';
    }
    $remaining = count($labels) - count($visible);
    if ($remaining > 0) {
        $html .= '<span class="badge badge-count">+' . h((string)$remaining) . ' meer</span>';
    }
    $html .= '</span>';

    return $html;
}

function public_profile_url(array $organization, string $audience): ?string
{
    $code = (string)($organization['primary_island_code'] ?: $organization['first_island_code'] ?: 'bonaire');

    return admin_public_organization_url(
        $organization,
        $audience,
        [['code' => $code, 'is_primary' => 1]]
    );
}

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

    if ($filters['status'] !== '') {
        $where[] = 'o.status = :status';
        $params['status'] = $filters['status'];
    } elseif (!$filters['show_archived']) {
        $where[] = "o.status <> 'archived'";
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
            o.external_key,
            o.status,
            o.source_status,
            o.visibility_public,
            o.updated_at,
            COALESCE(NULLIF(ot_nl.name, ''), NULLIF(ot_en.name, ''), o.slug) AS name,
            COALESCE(NULLIF(ot_nl.type_label, ''), NULLIF(ot_en.type_label, '')) AS type_label,
            (
                SELECT GROUP_CONCAT(i.name ORDER BY oi.is_primary DESC, i.sort_order ASC SEPARATOR ', ')
                FROM organization_islands oi
                INNER JOIN islands i ON i.id = oi.island_id
                WHERE oi.organization_id = o.id
            ) AS islands,
            (
                SELECT GROUP_CONCAT(i.code ORDER BY oi.is_primary DESC, i.sort_order ASC SEPARATOR ' ')
                FROM organization_islands oi
                INNER JOIN islands i ON i.id = oi.island_id
                WHERE oi.organization_id = o.id
            ) AS island_codes,
            (
                SELECT i.code
                FROM organization_islands oi
                INNER JOIN islands i ON i.id = oi.island_id
                WHERE oi.organization_id = o.id
                    AND oi.is_primary = 1
                ORDER BY i.sort_order ASC
                LIMIT 1
            ) AS primary_island_code,
            (
                SELECT i.code
                FROM organization_islands oi
                INNER JOIN islands i ON i.id = oi.island_id
                WHERE oi.organization_id = o.id
                ORDER BY oi.is_primary DESC, i.sort_order ASC
                LIMIT 1
            ) AS first_island_code,
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
            AND ot_nl.language_code = 'nl'
        LEFT JOIN organization_translations ot_en
            ON ot_en.organization_id = o.id
            AND ot_en.language_code = 'en'";

    if ($where) {
        $sql .= ' WHERE ' . implode(' AND ', $where);
    }

    $sql .= ' ORDER BY name ASC LIMIT 300';
    $organizations = fetch_all($sql, $params);
} catch (Throwable) {
    $error = 'Organisaties konden niet worden geladen.';
}

$titleAction = '<span class="button button-disabled" title="Nieuwe organisatie toevoegen wordt nog ingericht" aria-disabled="true">+ Nieuwe organisatie toevoegen</span>';

admin_header('Organisaties', 'organizations', $titleAction);
?>
<?php if ($error !== ''): ?>
  <p class="error"><?= h($error) ?></p>
<?php endif; ?>

<section class="panel filter-panel organizations-filter-panel">
  <div class="panel-heading">
    <div>
      <h2>Zoeken en filteren</h2>
      <p class="muted"><span data-organization-visible-count><?= h((string)count($organizations)) ?></span> organisaties in dit overzicht.</p>
    </div>
    <a class="button button-small" href="organizations.php">Wis filters</a>
  </div>
  <form class="filters organizations-filters" method="get" action="organizations.php">
    <label class="organizations-search-field">
      Zoekterm
      <input name="q" value="" placeholder="Zoek op naam, afkorting of eiland..." autocomplete="off" data-organizations-search>
    </label>
    <label>
      Eiland
      <select name="island">
        <option value="">Alle eilanden</option>
        <?php foreach ($islands as $island): ?>
          <option value="<?= h($island['code']) ?>" <?= $filters['island'] === $island['code'] ? 'selected' : '' ?>><?= h($island['name']) ?></option>
        <?php endforeach; ?>
      </select>
    </label>
    <label>
      Publicatiestatus
      <select name="status">
        <option value="">Alle actieve statussen</option>
        <?php foreach (['published', 'draft', 'needs_review', 'archived'] as $status): ?>
          <option value="<?= h($status) ?>" <?= $filters['status'] === $status ? 'selected' : '' ?>><?= h(organization_status_label($status)) ?></option>
        <?php endforeach; ?>
      </select>
    </label>
    <label>
      Thema
      <select name="theme">
        <option value="">Alle thema's</option>
        <?php foreach ($themes as $theme): ?>
          <option value="<?= h($theme['external_key']) ?>" <?= $filters['theme'] === $theme['external_key'] ? 'selected' : '' ?>><?= h($theme['name']) ?></option>
        <?php endforeach; ?>
      </select>
    </label>
    <label class="checkbox-label">
      <input type="checkbox" name="show_archived" value="1" <?= $filters['show_archived'] ? 'checked' : '' ?>>
      Archief tonen
    </label>
    <button type="submit">Filters toepassen</button>
  </form>
</section>

<section class="panel organizations-list-panel">
  <div class="table-wrap organizations-table-wrap">
    <table class="organizations-table">
      <thead>
        <tr>
          <th>Organisatie</th>
          <th>Eiland</th>
          <th>Publicatie</th>
          <th>Thema's</th>
          <th>Laatst bijgewerkt</th>
          <th>Publiek</th>
        </tr>
      </thead>
      <tbody data-organizations-body>
        <?php foreach ($organizations as $organization): ?>
          <?php
            $publicYouthUrl = public_profile_url($organization, 'youth');
            $publicProfessionalUrl = public_profile_url($organization, 'professional');
            $searchText = implode(' ', [
                $organization['name'],
                $organization['slug'],
                $organization['external_key'],
                $organization['islands'],
                $organization['island_codes'],
                $organization['type_label'],
                $organization['themes'],
                organization_status_label((string)$organization['status']),
                source_status_label((string)$organization['source_status']),
            ]);
            $rowClasses = ['organization-row'];
            if ((string)$organization['status'] === 'archived' || (string)$organization['source_status'] === 'demo') {
                $rowClasses[] = 'is-subtle';
            }
          ?>
          <tr class="<?= h(implode(' ', $rowClasses)) ?>" data-search="<?= h(strtolower($searchText)) ?>">
            <td class="organization-name-cell">
              <a class="organization-name-link" href="organization.php?id=<?= h((string)$organization['id']) ?>"><?= h($organization['name']) ?></a>
              <small>
                <?= h($organization['slug']) ?>
                <?php if ((string)$organization['source_status'] === 'demo'): ?>
                  <span class="source-note"><?= h(source_status_label((string)$organization['source_status'])) ?></span>
                <?php endif; ?>
              </small>
            </td>
            <td><?= render_compact_labels($organization['islands'], 'Geen eiland') ?></td>
            <td><?= organization_status_badge((string)$organization['status']) ?></td>
            <td><?= render_compact_labels($organization['themes'], 'Geen thema') ?></td>
            <td><?= visible_date($organization['updated_at']) ?></td>
            <td>
              <div class="public-link-actions">
                <?php if ($publicYouthUrl): ?>
                  <a class="icon-button" href="<?= h($publicYouthUrl) ?>" title="Jongerenpagina bekijken" aria-label="Jongerenpagina bekijken"><?= admin_icon('icons/content/youth-profile.svg') ?></a>
                <?php else: ?>
                  <span class="icon-button is-disabled" title="Jongerenpagina niet publiek beschikbaar" aria-label="Jongerenpagina niet publiek beschikbaar"><?= admin_icon('icons/content/youth-profile.svg') ?></span>
                <?php endif; ?>
                <?php if ($publicProfessionalUrl): ?>
                  <a class="icon-button" href="<?= h($publicProfessionalUrl) ?>" title="Professionalspagina bekijken" aria-label="Professionalspagina bekijken"><?= admin_icon('icons/content/professional-profile.svg') ?></a>
                <?php else: ?>
                  <span class="icon-button is-disabled" title="Professionalspagina niet publiek beschikbaar" aria-label="Professionalspagina niet publiek beschikbaar"><?= admin_icon('icons/content/professional-profile.svg') ?></span>
                <?php endif; ?>
              </div>
            </td>
          </tr>
        <?php endforeach; ?>
        <tr data-organizations-empty <?= $organizations ? 'hidden' : '' ?>><td colspan="6" class="empty-state">Geen organisaties gevonden.</td></tr>
      </tbody>
    </table>
  </div>
</section>

<script>
(() => {
  const input = document.querySelector('[data-organizations-search]');
  const rows = Array.from(document.querySelectorAll('[data-search]'));
  const emptyRow = document.querySelector('[data-organizations-empty]');
  const count = document.querySelector('[data-organization-visible-count]');

  if (!input || !emptyRow || !count) {
    return;
  }

  const updateRows = () => {
    const query = input.value.trim().toLowerCase();
    let visible = 0;

    rows.forEach((row) => {
      const matches = query === '' || row.dataset.search.includes(query);
      row.hidden = !matches;
      if (matches) {
        visible += 1;
      }
    });

    emptyRow.hidden = visible !== 0;
    count.textContent = String(visible);
  };

  input.addEventListener('input', updateRows);
  updateRows();
})();
</script>
<?php admin_footer(); ?>
