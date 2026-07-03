<?php

declare(strict_types=1);

require __DIR__ . '/layout.php';
require_admin_login();

$id = (int)($_GET['id'] ?? 0);
$error = '';
$organization = null;
$contact = null;
$islands = [];
$themes = [];
$audiences = [];
$translations = [];
$youthAnswers = [];
$professionalAnswers = [];
$auditEntries = [];
$languages = ['nl', 'pap', 'en', 'es'];
$saved = (string)($_GET['saved'] ?? '') === '1';

function translation_by_language(array $rows): array
{
    $mapped = [];
    foreach ($rows as $row) {
        $mapped[$row['language_code']] = $row;
    }

    return $mapped;
}

function profile_matrix(array $rows): array
{
    $matrix = [];
    foreach ($rows as $row) {
        $key = $row['group_key'] . '|' . $row['field_key'];
        if (!isset($matrix[$key])) {
            $matrix[$key] = [
                'group_key' => $row['group_key'],
                'field_key' => $row['field_key'],
                'sort_order' => (int)$row['sort_order'],
                'languages' => [],
            ];
        }
        $matrix[$key]['languages'][$row['language_code']] = $row;
    }

    uasort($matrix, static fn(array $a, array $b): int => $a['sort_order'] <=> $b['sort_order']);

    return array_values($matrix);
}

function organization_source_language(array $islands): string
{
    foreach ($islands as $island) {
        $code = (string)($island['code'] ?? '');
        if ((int)($island['is_primary'] ?? 0) === 1) {
            return $code === 'bonaire' ? 'nl' : 'en';
        }
    }

    return 'nl';
}

function organization_island_label(array $islands): string
{
    if (!$islands) {
        return '';
    }

    return implode(', ', array_map(
        static fn(array $island): string => (string)$island['name'] . ((int)$island['is_primary'] === 1 ? ' (primair)' : ''),
        $islands
    ));
}

function profile_language_state(array $answers, string $language, string $sourceLanguage): array
{
    $total = count($answers);
    $filled = 0;
    $published = 0;
    $reviewed = 0;
    $draft = 0;

    foreach ($answers as $answer) {
        $cell = $answer['languages'][$language] ?? null;
        $text = trim((string)($cell['answer_text'] ?? ''));
        if ($text === '') {
            continue;
        }
        $filled++;
        $status = (string)($cell['translation_status'] ?? 'missing');
        if ($status === 'published') {
            $published++;
        } elseif ($status === 'reviewed') {
            $reviewed++;
        } elseif ($status === 'draft') {
            $draft++;
        }
    }

    if ($filled === 0) {
        return ['label' => 'Leeg', 'class' => 'status-empty', 'meta' => '0/' . $total . ' velden'];
    }
    if ($language === 'pap') {
        return ['label' => 'Concept review', 'class' => 'status-review', 'meta' => $filled . '/' . $total . ' gevuld'];
    }
    if ($language === $sourceLanguage) {
        return ['label' => 'Gevuld', 'class' => 'status-filled', 'meta' => $filled . '/' . $total . ' gevuld'];
    }
    if (($published + $reviewed) >= $filled && $draft === 0) {
        return ['label' => 'Gereviewd', 'class' => 'status-filled', 'meta' => $filled . '/' . $total . ' gevuld'];
    }

    return ['label' => 'Concept', 'class' => 'status-draft', 'meta' => $filled . '/' . $total . ' gevuld'];
}

function render_profile_status_card(string $title, array $answers, array $languages, string $sourceLanguage, string $href): void
{
    ?>
  <section class="admin-dashboard-card profile-status-card">
    <div class="admin-card-heading">
      <div>
        <p class="eyebrow">Profielstatus</p>
        <h2><?= h($title) ?></h2>
      </div>
      <a class="button" href="<?= h($href) ?>"><?= h($title) ?> bewerken</a>
    </div>
    <div class="language-status-grid">
      <?php foreach ($languages as $language): ?>
        <?php $state = profile_language_state($answers, $language, $sourceLanguage); ?>
        <div class="language-status-tile <?= h($state['class']) ?>">
          <strong><?= h(strtoupper($language)) ?></strong>
          <span><?= h($state['label']) ?></span>
          <small><?= h($state['meta']) ?></small>
        </div>
      <?php endforeach; ?>
    </div>
  </section>
<?php
}

function audit_action_label_short(string $action): string
{
    $labels = [
        'organization.update_basic' => 'Basisgegevens gewijzigd',
        'organization.update_profile' => 'Profiel gewijzigd',
        'organization.update_translation_intro' => 'Korte introtekst gewijzigd',
    ];

    return $labels[$action] ?? $action;
}

try {
    if ($id <= 0) {
        throw new RuntimeException('Geen geldige organisatie-id opgegeven.');
    }

    $organization = fetch_one(
        "SELECT o.*, COALESCE(NULLIF(t.name, ''), NULLIF(t_en.name, ''), o.slug) AS name
        FROM organizations o
        LEFT JOIN organization_translations t
            ON t.organization_id = o.id
            AND t.language_code = 'nl'
        LEFT JOIN organization_translations t_en
            ON t_en.organization_id = o.id
            AND t_en.language_code = 'en'
        WHERE o.id = :id
        LIMIT 1",
        ['id' => $id]
    );

    if (!$organization) {
        throw new RuntimeException('Organisatie niet gevonden.');
    }

    $contact = fetch_one('SELECT * FROM organization_contacts WHERE organization_id = :id', ['id' => $id]);
    $islands = fetch_all(
        "SELECT i.name, i.code, oi.is_primary
        FROM organization_islands oi
        INNER JOIN islands i ON i.id = oi.island_id
        WHERE oi.organization_id = :id
        ORDER BY oi.is_primary DESC, i.sort_order ASC",
        ['id' => $id]
    );
    $themes = fetch_all(
        "SELECT t.slug, t.external_key, t.color, ot.is_primary, COALESCE(NULLIF(tt.name, ''), t.slug) AS name
        FROM organization_theme ot
        INNER JOIN themes t ON t.id = ot.theme_id
        LEFT JOIN theme_translations tt
            ON tt.theme_id = t.id
            AND tt.language_code = 'nl'
        WHERE ot.organization_id = :id
        ORDER BY ot.is_primary DESC, ot.sort_order ASC",
        ['id' => $id]
    );
    $audiences = fetch_all(
        "SELECT a.code, a.label_nl
        FROM organization_audience oa
        INNER JOIN audiences a ON a.id = oa.audience_id
        WHERE oa.organization_id = :id
        ORDER BY a.sort_order ASC",
        ['id' => $id]
    );
    $translations = translation_by_language(fetch_all(
        'SELECT * FROM organization_translations WHERE organization_id = :id ORDER BY language_code ASC',
        ['id' => $id]
    ));
    $profileRows = fetch_all(
        "SELECT *
        FROM organization_profile_answers
        WHERE organization_id = :id
        ORDER BY audience_code ASC, sort_order ASC, group_key ASC, field_key ASC, language_code ASC",
        ['id' => $id]
    );
    $youthAnswers = profile_matrix(array_values(array_filter($profileRows, static fn(array $row): bool => $row['audience_code'] === 'youth')));
    $professionalAnswers = profile_matrix(array_values(array_filter($profileRows, static fn(array $row): bool => $row['audience_code'] === 'professional')));
    $auditEntries = fetch_all(
        "SELECT a.action, a.created_at, u.name AS user_name, u.email AS user_email
        FROM audit_log a
        LEFT JOIN users u ON u.id = a.user_id
        WHERE a.entity_type = 'organization'
          AND a.entity_id = :id
        ORDER BY a.created_at DESC, a.id DESC
        LIMIT 10",
        ['id' => $id]
    );
} catch (Throwable $exception) {
    $knownMessages = [
        'Geen geldige organisatie-id opgegeven.',
        'Organisatie niet gevonden.',
    ];
    $error = in_array($exception->getMessage(), $knownMessages, true)
        ? $exception->getMessage()
        : 'De organisatie kon niet worden geladen. Probeer het later opnieuw.';
}

admin_header($organization ? (string)$organization['name'] : 'Organisatie', 'organizations');
$publicYouthUrl = $organization ? admin_public_organization_url($organization, 'youth', $islands) : null;
$publicProfessionalUrl = $organization ? admin_public_organization_url($organization, 'professional', $islands) : null;
$sourceLanguage = organization_source_language($islands);
$islandLabel = organization_island_label($islands);
?>
<?php if ($error !== ''): ?>
  <p class="error"><?= h($error) ?></p>
  <p><a class="button" href="organizations.php">Terug naar organisaties</a></p>
  <?php admin_footer(); exit; ?>
<?php endif; ?>

<section class="detail-hero organization-dashboard-hero">
  <div>
    <a class="back-link" href="organizations.php">Terug naar organisaties</a>
    <p class="eyebrow">Organisatie</p>
    <h2><?= h((string)$organization['name']) ?></h2>
    <p><?= $islandLabel !== '' ? h($islandLabel) : '<span class="muted">Geen eiland gekoppeld</span>' ?></p>
    <div class="status-row">
      <?= status_badge($organization['status']) ?>
      <?= status_badge($organization['source_status']) ?>
      <span class="badge"><?= ((int)$organization['visibility_public'] === 1) ? 'publiek zichtbaar' : 'niet publiek' ?></span>
    </div>
  </div>
  <div class="detail-actions">
    <a class="button" href="organizations.php">Terug naar organisaties</a>
    <?php if (admin_can_edit_organizations()): ?>
      <a class="button primary" href="organization_edit.php?id=<?= h((string)$organization['id']) ?>">Basisgegevens bewerken</a>
    <?php endif; ?>
    <a class="button" href="organization_profile_edit.php?id=<?= h((string)$organization['id']) ?>&amp;audience=youth">Jongerenprofiel bewerken</a>
    <a class="button" href="organization_profile_edit.php?id=<?= h((string)$organization['id']) ?>&amp;audience=professional">Professionalsprofiel bewerken</a>
    <?php if ($publicYouthUrl): ?>
      <a class="button" href="<?= h($publicYouthUrl) ?>">Open publieke pagina</a>
    <?php elseif ($publicProfessionalUrl): ?>
      <a class="button" href="<?= h($publicProfessionalUrl) ?>">Open publieke pagina</a>
    <?php endif; ?>
  </div>
</section>

<?php if ($saved): ?>
  <p class="notice">Wijzigingen zijn opgeslagen.</p>
<?php endif; ?>

<div class="organization-dashboard">
  <section class="admin-dashboard-card">
    <div class="admin-card-heading"><div><p class="eyebrow">Overzicht</p><h2>Algemene gegevens</h2></div></div>
    <dl class="compact-detail-list">
      <dt>Naam</dt><dd><?= h($organization['name']) ?></dd>
      <dt>Slug</dt><dd><code><?= h($organization['slug']) ?></code></dd>
      <dt>External key</dt><dd><code><?= h((string)$organization['external_key']) ?></code></dd>
      <dt>Eiland</dt><dd><?= $islandLabel !== '' ? h($islandLabel) : '<span class="muted">ontbreekt</span>' ?></dd>
      <dt>Status</dt><dd><?= status_badge($organization['status']) ?></dd>
      <dt>Zichtbaarheid</dt><dd><?= ((int)$organization['visibility_public'] === 1) ? status_badge('publiek') : status_badge('niet publiek') ?></dd>
    </dl>
  </section>

  <section class="admin-dashboard-card">
    <div class="admin-card-heading"><div><p class="eyebrow">Relaties</p><h2>Doelgroepen en thema's</h2></div></div>
    <h3>Doelgroepen</h3>
    <div class="badge-list">
      <?php foreach ($audiences as $audience): ?>
        <span class="badge"><?= h($audience['label_nl']) ?></span>
      <?php endforeach; ?>
      <?php if (!$audiences): ?><span class="muted">Geen doelgroepen gekoppeld.</span><?php endif; ?>
    </div>
    <h3>Thema's</h3>
    <div class="badge-list">
      <?php foreach ($themes as $theme): ?>
        <span class="badge"><?= h($theme['name']) ?><?= (int)$theme['is_primary'] === 1 ? ' - primair' : '' ?></span>
      <?php endforeach; ?>
      <?php if (!$themes): ?><span class="muted">Geen thema's gekoppeld.</span><?php endif; ?>
    </div>
  </section>

  <section class="admin-dashboard-card">
    <div class="admin-card-heading"><div><p class="eyebrow">Contact</p><h2>Contactgegevens</h2></div></div>
    <dl class="compact-detail-list">
      <dt>Telefoon</dt><dd><?= empty_label($contact['phone'] ?? '') ?></dd>
      <dt>WhatsApp</dt><dd><?= empty_label($contact['whatsapp'] ?? '') ?></dd>
      <dt>E-mail</dt><dd><?= empty_label($contact['email'] ?? '') ?></dd>
      <dt>Website</dt><dd><?= empty_label($contact['website'] ?? '') ?></dd>
      <dt>Adres NL</dt><dd><?= empty_label($contact['address_nl'] ?? '') ?></dd>
    </dl>
  </section>

  <section class="admin-dashboard-card">
    <div class="admin-card-heading"><div><p class="eyebrow">Controle</p><h2>Bron en controle</h2></div></div>
    <dl class="compact-detail-list">
      <dt>Bronstatus</dt><dd><?= status_badge($organization['source_status']) ?></dd>
      <dt>Laatst gecontroleerd</dt><dd><?= readable_date($organization['last_checked_at']) ?></dd>
      <dt>Bijgewerkt</dt><dd><?= readable_datetime($organization['updated_at']) ?></dd>
      <dt>Source locked</dt><dd><?= ((int)$organization['source_locked'] === 1) ? 'ja' : 'nee' ?></dd>
    </dl>
  </section>

  <?php render_profile_status_card('Jongerenprofiel', $youthAnswers, $languages, $sourceLanguage, 'organization_profile_edit.php?id=' . rawurlencode((string)$organization['id']) . '&audience=youth'); ?>
  <?php render_profile_status_card('Professionalsprofiel', $professionalAnswers, $languages, $sourceLanguage, 'organization_profile_edit.php?id=' . rawurlencode((string)$organization['id']) . '&audience=professional'); ?>

  <section class="admin-dashboard-card audit-summary-card">
    <div class="admin-card-heading">
      <div><p class="eyebrow">Audit</p><h2>Laatste wijzigingen</h2></div>
      <a class="button" href="audit_log.php">Volledige auditlog</a>
    </div>
    <?php if (!$auditEntries): ?>
      <p class="muted">Nog geen wijzigingen gevonden.</p>
    <?php else: ?>
      <ol class="audit-summary-list">
        <?php foreach ($auditEntries as $entry): ?>
          <li>
            <time><?= readable_datetime($entry['created_at']) ?></time>
            <strong><?= h(audit_action_label_short((string)$entry['action'])) ?></strong>
            <span><?= h((string)($entry['user_name'] ?: $entry['user_email'] ?: 'Onbekende gebruiker')) ?></span>
          </li>
        <?php endforeach; ?>
      </ol>
    <?php endif; ?>
  </section>
</div>

<?php admin_footer(); ?>
