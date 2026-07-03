<?php

declare(strict_types=1);

require __DIR__ . '/layout.php';
require_admin_login();

$error = '';
$organizations = [];
$byIsland = [];
$islandCards = [
    'bonaire' => [
        'name' => 'Bonaire',
        'description' => 'Bekijk en beheer organisaties op Bonaire.',
        'asset' => 'flags/svg/flag-bonaire.svg',
        'asset_alt' => 'Vlag van Bonaire',
        'count' => null,
    ],
    'statia' => [
        'name' => 'Sint Eustatius',
        'description' => 'Bekijk en beheer organisaties op Sint Eustatius.',
        'asset' => 'icons/people/address-location.svg',
        'asset_alt' => '',
        'count' => null,
    ],
    'saba' => [
        'name' => 'Saba',
        'description' => 'Bekijk en beheer organisaties op Saba.',
        'asset' => 'icons/people/address-location.svg',
        'asset_alt' => '',
        'count' => null,
    ],
];

function dashboard_asset(string $path, string $class, string $alt = ''): string
{
    $ariaHidden = $alt === '' ? ' aria-hidden="true"' : '';

    return '<img class="' . h($class) . '" src="../assets/admin-icons/admin_assetpack_sociale_kaart_bes_v1/' . h($path) . '" alt="' . h($alt) . '"' . $ariaHidden . '>';
}

function dashboard_status_label(string $status): string
{
    return [
        'published' => 'Gepubliceerd',
        'draft' => 'Concept',
        'needs_review' => 'Review nodig',
        'archived' => 'Archief',
    ][$status] ?? ucfirst(str_replace('_', ' ', $status));
}

try {
    $organizations = fetch_all(
        "SELECT
            o.id,
            o.slug,
            o.external_key,
            o.status,
            o.visibility_public,
            COALESCE(NULLIF(ot_nl.name, ''), NULLIF(ot_en.name, ''), o.slug) AS name,
            COALESCE(NULLIF(ot_nl.type_label, ''), NULLIF(ot_en.type_label, '')) AS type_label,
            COALESCE(NULLIF(ot_nl.age_range, ''), NULLIF(ot_en.age_range, '')) AS age_range,
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
                SELECT GROUP_CONCAT(a.label_nl ORDER BY a.sort_order ASC SEPARATOR ', ')
                FROM organization_audience oa
                INNER JOIN audiences a ON a.id = oa.audience_id
                WHERE oa.organization_id = o.id
            ) AS audiences,
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
            AND ot_en.language_code = 'en'
        ORDER BY name ASC
        LIMIT 300"
    );

    $byIsland = fetch_all(
        "SELECT i.code, COUNT(oi.organization_id) AS total
        FROM islands i
        LEFT JOIN organization_islands oi ON oi.island_id = i.id
        WHERE i.code IN ('bonaire', 'statia', 'saba')
        GROUP BY i.id, i.name, i.code, i.sort_order
        ORDER BY i.sort_order ASC, i.name ASC"
    );
    foreach ($byIsland as $row) {
        $code = (string)($row['code'] ?? '');
        if (isset($islandCards[$code])) {
            $islandCards[$code]['count'] = (int)($row['total'] ?? 0);
        }
    }
} catch (Throwable) {
    $error = 'Dashboardgegevens konden niet worden geladen.';
}

$searchData = array_map(
    static fn(array $organization): array => [
        'id' => (int)$organization['id'],
        'name' => (string)$organization['name'],
        'slug' => (string)$organization['slug'],
        'externalKey' => (string)$organization['external_key'],
        'status' => (string)$organization['status'],
        'statusLabel' => dashboard_status_label((string)$organization['status']),
        'visibility' => (int)$organization['visibility_public'] === 1 ? 'publiek zichtbaar' : 'niet publiek',
        'islands' => (string)($organization['islands'] ?? ''),
        'islandCodes' => (string)($organization['island_codes'] ?? ''),
        'typeLabel' => (string)($organization['type_label'] ?? ''),
        'ageRange' => (string)($organization['age_range'] ?? ''),
        'audiences' => (string)($organization['audiences'] ?? ''),
        'themes' => (string)($organization['themes'] ?? ''),
    ],
    $organizations
);

admin_header('Dashboard', 'dashboard');
?>
<?php if ($error !== ''): ?>
  <p class="error"><?= h($error) ?></p>
<?php endif; ?>

<div class="dashboard-home">
  <section class="dashboard-search-card" aria-labelledby="dashboard-search-title">
    <div class="dashboard-search-heading">
      <div>
        <p class="eyebrow">Snel starten</p>
        <h2 id="dashboard-search-title">Organisatie zoeken</h2>
        <p>Zoek op organisatie, eiland of thema en open direct het juiste beheerprofiel.</p>
      </div>
      <div class="dashboard-create-action">
        <span class="dashboard-placeholder-action" aria-disabled="true"><?= dashboard_asset('icons/actions/add.svg', 'admin-icon') ?>Nieuwe organisatie toevoegen</span>
        <small>Binnenkort beschikbaar: eerst veilig aanmaken als concept.</small>
      </div>
    </div>

    <label class="dashboard-search-label" for="organization-search">
      <span class="sr-only">Organisatie zoeken</span>
      <input
        id="organization-search"
        class="dashboard-search-input"
        type="search"
        autocomplete="off"
        placeholder="Zoek op organisatienaam, afkorting of eiland..."
        data-organization-search
      >
    </label>

    <p class="dashboard-search-count muted" data-organization-count aria-live="polite">Begin met typen om een organisatie te zoeken.</p>
    <div class="dashboard-search-results" data-organization-results></div>
  </section>

  <section class="island-card-grid" aria-label="Organisaties per eiland">
    <?php foreach ($islandCards as $code => $island): ?>
      <a class="island-card" href="organizations.php?island=<?= h($code) ?>">
        <span class="island-card-visual"><?= dashboard_asset($island['asset'], 'island-card-asset', $island['asset_alt']) ?></span>
        <span class="island-card-name"><?= h($island['name']) ?></span>
        <span class="island-card-description"><?= h($island['description']) ?></span>
        <?php if ($island['count'] !== null): ?>
          <span class="island-card-count"><?= h((string)$island['count']) ?> organisaties</span>
        <?php endif; ?>
        <span class="button button-small">Organisaties bekijken</span>
      </a>
    <?php endforeach; ?>
  </section>
</div>

<script>
(() => {
  const organizations = <?= json_encode($searchData, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
  const input = document.querySelector('[data-organization-search]');
  const results = document.querySelector('[data-organization-results]');
  const count = document.querySelector('[data-organization-count]');

  if (!input || !results || !count) {
    return;
  }

  const escapeHtml = (value) => String(value || '').replace(/[&<>"']/g, (character) => ({
    '&': '&amp;',
    '<': '&lt;',
    '>': '&gt;',
    '"': '&quot;',
    "'": '&#039;'
  })[character]);

  const renderEmpty = (message) => {
    count.textContent = message;
    results.innerHTML = '';
  };

  const renderResults = () => {
    const query = input.value.trim().toLowerCase();
    if (query.length === 0) {
      renderEmpty('Begin met typen om een organisatie te zoeken.');
      return;
    }

    const matches = organizations.filter((organization) => [
      organization.name,
      organization.slug,
      organization.externalKey,
      organization.islands,
      organization.islandCodes,
      organization.typeLabel,
      organization.ageRange,
      organization.audiences,
      organization.themes
    ].join(' ').toLowerCase().includes(query)).slice(0, 12);

    if (matches.length === 0) {
      renderEmpty('Geen organisatie gevonden. Controleer de spelling of zoek op eiland of thema.');
      return;
    }

    count.textContent = matches.length === 1 ? '1 organisatie gevonden.' : `${matches.length} organisaties gevonden.`;
    results.innerHTML = matches.map((organization) => {
      const meta = [organization.typeLabel, organization.ageRange, organization.audiences, organization.themes].filter(Boolean).join(' - ');
      return `
        <article class="dashboard-result-card">
          <div>
            <h3>${escapeHtml(organization.name)}</h3>
            <div class="dashboard-result-badges">
              ${organization.islands ? `<span class="badge">${escapeHtml(organization.islands)}</span>` : ''}
              <span class="badge badge-${escapeHtml(organization.status)}">${escapeHtml(organization.statusLabel)}</span>
              <span class="badge">${escapeHtml(organization.visibility)}</span>
            </div>
            ${meta ? `<p class="muted">${escapeHtml(meta)}</p>` : ''}
            <small><code>${escapeHtml(organization.slug || organization.externalKey)}</code></small>
          </div>
          <div class="dashboard-result-actions">
            <a class="button button-small primary" href="organization.php?id=${encodeURIComponent(organization.id)}">Open organisatie</a>
            <a class="button button-small" href="organization_profile_edit.php?id=${encodeURIComponent(organization.id)}&amp;audience=youth">Jongerenprofiel</a>
            <a class="button button-small" href="organization_profile_edit.php?id=${encodeURIComponent(organization.id)}&amp;audience=professional">Professionalsprofiel</a>
          </div>
        </article>
      `;
    }).join('');
  };

  input.addEventListener('input', renderResults);
})();
</script>
<?php admin_footer(); ?>
