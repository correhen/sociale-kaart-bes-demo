<?php

declare(strict_types=1);

require __DIR__ . '/layout.php';
require_admin_login();

const ORGANIZATION_STATUSES = ['draft', 'published', 'needs_review', 'archived'];
const ORGANIZATION_SOURCE_STATUSES = ['demo', 'submitted', 'verified', 'needs_check', 'expired'];

$id = (int)($_GET['id'] ?? $_POST['id'] ?? 0);
$error = '';
$errors = [];
$organization = null;
$contact = null;
$islands = [];
$availableAudiences = [];
$availableThemes = [];
$selectedAudienceIds = [];
$selectedThemeIds = [];
$primaryThemeId = 0;
$values = [
    'name' => '',
    'professional_summary' => '',
    'type_label' => '',
    'age_range' => '',
    'professional_referral_or_access' => '',
    'status' => '',
    'visibility_public' => 0,
    'source_status' => '',
    'last_checked_at' => '',
    'phone' => '',
    'whatsapp' => '',
    'email' => '',
    'website' => '',
    'address_nl' => '',
];

function editable_snapshot(array $organization, ?array $translation, ?array $contact): array
{
    return [
        'name' => (string)($translation['name'] ?? ''),
        'professional_summary' => (string)($translation['professional_summary'] ?? ''),
        'type_label' => (string)($translation['type_label'] ?? ''),
        'age_range' => (string)($translation['age_range'] ?? ''),
        'professional_referral_or_access' => (string)($translation['professional_referral_or_access'] ?? ''),
        'status' => (string)($organization['status'] ?? ''),
        'visibility_public' => (int)($organization['visibility_public'] ?? 0),
        'source_status' => (string)($organization['source_status'] ?? ''),
        'last_checked_at' => (string)($organization['last_checked_at'] ?? ''),
        'phone' => (string)($contact['phone'] ?? ''),
        'whatsapp' => (string)($contact['whatsapp'] ?? ''),
        'email' => (string)($contact['email'] ?? ''),
        'website' => (string)($contact['website'] ?? ''),
        'address_nl' => (string)($contact['address_nl'] ?? ''),
    ];
}

function valid_date_or_empty(string $value): bool
{
    if ($value === '') {
        return true;
    }
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
        return false;
    }
    [$year, $month, $day] = array_map('intval', explode('-', $value));

    return checkdate($month, $day, $year);
}

function organization_edit_status_label(string $status): string
{
    return [
        'published' => 'Gepubliceerd',
        'draft' => 'Concept',
        'needs_review' => 'Review nodig',
        'archived' => 'Archief',
        'submitted' => 'Aangeleverd',
    ][$status] ?? $status;
}

function organization_edit_source_status_label(string $status): string
{
    return [
        'demo' => 'Demo/oude data',
        'submitted' => 'Aangeleverd',
        'verified' => 'Gecontroleerd',
        'needs_check' => 'Controle nodig',
        'expired' => 'Verlopen',
    ][$status] ?? $status;
}

function posted_values(): array
{
    return [
        'name' => (string)($_POST['name'] ?? ''),
        'professional_summary' => (string)($_POST['professional_summary'] ?? ''),
        'type_label' => (string)($_POST['type_label'] ?? ''),
        'age_range' => (string)($_POST['age_range'] ?? ''),
        'professional_referral_or_access' => (string)($_POST['professional_referral_or_access'] ?? ''),
        'status' => trim((string)($_POST['status'] ?? '')),
        'visibility_public' => isset($_POST['visibility_public']) ? 1 : 0,
        'source_status' => trim((string)($_POST['source_status'] ?? '')),
        'last_checked_at' => trim((string)($_POST['last_checked_at'] ?? '')),
        'phone' => (string)($_POST['phone'] ?? ''),
        'whatsapp' => (string)($_POST['whatsapp'] ?? ''),
        'email' => (string)($_POST['email'] ?? ''),
        'website' => (string)($_POST['website'] ?? ''),
        'address_nl' => (string)($_POST['address_nl'] ?? ''),
    ];
}

function posted_int_list(string $field): array
{
    $posted = $_POST[$field] ?? [];
    if (!is_array($posted)) {
        return [];
    }

    $ids = [];
    foreach ($posted as $value) {
        $id = (int)$value;
        if ($id > 0) {
            $ids[] = $id;
        }
    }

    return array_values(array_unique($ids));
}

function valid_relation_ids(array $availableRows): array
{
    return array_map(static fn(array $row): int => (int)$row['id'], $availableRows);
}

function validate_values(array $values, array $audienceIds, array $themeIds, int $primaryThemeId, array $availableAudiences, array $availableThemes): array
{
    $errors = [];
    $validAudienceIds = valid_relation_ids($availableAudiences);
    $validThemeIds = valid_relation_ids($availableThemes);

    if (trim($values['name']) === '') {
        $errors[] = 'Organisatienaam mag niet leeg zijn.';
    }
    if (!in_array($values['status'], ORGANIZATION_STATUSES, true)) {
        $errors[] = 'Ongeldige status.';
    }
    if (!in_array($values['source_status'], ORGANIZATION_SOURCE_STATUSES, true)) {
        $errors[] = 'Ongeldige bronstatus.';
    }
    if (trim($values['email']) !== '' && !filter_var(trim($values['email']), FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'E-mail is ongeldig.';
    }
    if (trim($values['website']) !== '' && !preg_match('/^https?:\/\//i', trim($values['website']))) {
        $errors[] = 'Website moet beginnen met http:// of https://.';
    }
    if (!valid_date_or_empty($values['last_checked_at'])) {
        $errors[] = 'Laatst gecontroleerd moet leeg zijn of het formaat YYYY-MM-DD hebben.';
    }
    if (!$audienceIds) {
        $errors[] = 'Kies minimaal een doelgroep.';
    }
    foreach ($audienceIds as $audienceId) {
        if (!in_array($audienceId, $validAudienceIds, true)) {
            $errors[] = 'Een gekozen doelgroep is ongeldig.';
            break;
        }
    }
    foreach ($themeIds as $themeId) {
        if (!in_array($themeId, $validThemeIds, true)) {
            $errors[] = 'Een gekozen thema is ongeldig.';
            break;
        }
    }
    if ($primaryThemeId > 0 && !in_array($primaryThemeId, $themeIds, true)) {
        $errors[] = 'Het primaire thema moet ook als thema geselecteerd zijn.';
    }

    return $errors;
}

function changed_values(array $before, array $after): array
{
    $changed = [];
    foreach ($after as $key => $value) {
        if (audit_values_differ($before[$key] ?? null, $value)) {
            $changed[$key] = $value;
        }
    }

    return $changed;
}

function relation_snapshot(array $rows, string $labelField = 'label'): array
{
    $snapshot = [];
    foreach ($rows as $row) {
        $snapshot[] = [
            'id' => (int)$row['id'],
            'label' => (string)($row[$labelField] ?? ''),
            'is_primary' => (int)($row['is_primary'] ?? 0),
        ];
    }

    return $snapshot;
}

function relation_values_differ(array $before, array $after): bool
{
    return audit_values_differ($before, $after);
}

function basic_audit_key(string $field): string
{
    $keys = [
        'name' => 'name.nl',
        'professional_summary' => 'professional_summary.nl',
        'type_label' => 'type_label.nl',
        'age_range' => 'age_range.nl',
        'professional_referral_or_access' => 'professional_referral_or_access.nl',
        'visibility_public' => 'visibility_public',
        'phone' => 'contact.phone',
        'whatsapp' => 'contact.whatsapp',
        'email' => 'contact.email',
        'website' => 'contact.website',
        'address_nl' => 'contact.address_nl',
    ];

    return $keys[$field] ?? $field;
}

function basic_audit_values(array $values, array $changed): array
{
    $audit = [];
    foreach (array_keys($changed) as $field) {
        $audit[basic_audit_key($field)] = $values[$field] ?? null;
    }

    return $audit;
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
        "SELECT i.code, i.name, oi.is_primary
        FROM organization_islands oi
        INNER JOIN islands i ON i.id = oi.island_id
        WHERE oi.organization_id = :id
        ORDER BY oi.is_primary DESC, i.sort_order ASC",
        ['id' => $id]
    );
    $availableAudiences = fetch_all(
        "SELECT id, code, label_nl
        FROM audiences
        ORDER BY sort_order ASC, label_nl ASC"
    );
    $selectedAudiences = fetch_all(
        "SELECT a.id, a.code, a.label_nl AS label
        FROM organization_audience oa
        INNER JOIN audiences a ON a.id = oa.audience_id
        WHERE oa.organization_id = :id
        ORDER BY a.sort_order ASC, a.label_nl ASC",
        ['id' => $id]
    );
    $selectedAudienceIds = array_map(static fn(array $row): int => (int)$row['id'], $selectedAudiences);
    $availableThemes = fetch_all(
        "SELECT t.id, t.slug, t.external_key, COALESCE(NULLIF(tt.name, ''), t.slug) AS name
        FROM themes t
        LEFT JOIN theme_translations tt
            ON tt.theme_id = t.id
            AND tt.language_code = 'nl'
        WHERE t.status = 'published'
        ORDER BY t.sort_order ASC, name ASC"
    );
    $selectedThemes = fetch_all(
        "SELECT t.id, COALESCE(NULLIF(tt.name, ''), t.slug) AS label, ot.is_primary
        FROM organization_theme ot
        INNER JOIN themes t ON t.id = ot.theme_id
        LEFT JOIN theme_translations tt
            ON tt.theme_id = t.id
            AND tt.language_code = 'nl'
        WHERE ot.organization_id = :id
        ORDER BY ot.is_primary DESC, ot.sort_order ASC, t.sort_order ASC",
        ['id' => $id]
    );
    $selectedThemeIds = array_map(static fn(array $row): int => (int)$row['id'], $selectedThemes);
    foreach ($selectedThemes as $theme) {
        if ((int)$theme['is_primary'] === 1) {
            $primaryThemeId = (int)$theme['id'];
            break;
        }
    }
    $translation = fetch_one(
        "SELECT *
        FROM organization_translations
        WHERE organization_id = :id
          AND language_code = 'nl'
        LIMIT 1",
        ['id' => $id]
    );
    $values = editable_snapshot($organization, $translation, $contact);

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (!admin_can_edit_organizations()) {
            throw new RuntimeException('Je hebt geen rechten om organisaties op te slaan.');
        }
        if (!verify_csrf((string)($_POST['csrf_token'] ?? ''))) {
            $errors[] = 'Het formulier is verlopen. Probeer opnieuw.';
        }

        $values = posted_values();
        $selectedAudienceIds = posted_int_list('audience_ids');
        $selectedThemeIds = posted_int_list('theme_ids');
        $primaryThemeId = (int)($_POST['primary_theme_id'] ?? 0);
        if ($selectedThemeIds && !in_array($primaryThemeId, $selectedThemeIds, true)) {
            $primaryThemeId = $selectedThemeIds[0];
        } elseif (!$selectedThemeIds) {
            $primaryThemeId = 0;
        }
        $errors = array_merge($errors, validate_values($values, $selectedAudienceIds, $selectedThemeIds, $primaryThemeId, $availableAudiences, $availableThemes));

        if (!$errors) {
            $before = editable_snapshot($organization, $translation, $contact);
            $after = $values;
            $changed = changed_values($before, $after);
            $audiencesBefore = relation_snapshot($selectedAudiences);
            $audienceLabelById = [];
            foreach ($availableAudiences as $audience) {
                $audienceLabelById[(int)$audience['id']] = (string)$audience['label_nl'];
            }
            $audiencesAfter = [];
            foreach ($selectedAudienceIds as $audienceId) {
                $audiencesAfter[] = [
                    'id' => $audienceId,
                    'label' => $audienceLabelById[$audienceId] ?? '',
                    'is_primary' => 0,
                ];
            }
            $themesBefore = relation_snapshot($selectedThemes);
            $themeLabelById = [];
            foreach ($availableThemes as $theme) {
                $themeLabelById[(int)$theme['id']] = (string)$theme['name'];
            }
            $themesAfter = [];
            foreach ($selectedThemeIds as $themeId) {
                $themesAfter[] = [
                    'id' => $themeId,
                    'label' => $themeLabelById[$themeId] ?? '',
                    'is_primary' => $themeId === $primaryThemeId ? 1 : 0,
                ];
            }
            $audiencesChanged = relation_values_differ($audiencesBefore, $audiencesAfter);
            $themesChanged = relation_values_differ($themesBefore, $themesAfter);

            if ($changed || $audiencesChanged || $themesChanged) {
                $pdo = admin_db();
                $pdo->beginTransaction();

                $organizationFields = array_intersect_key($changed, array_flip([
                    'status',
                    'visibility_public',
                    'source_status',
                    'last_checked_at',
                ]));
                if ($organizationFields) {
                    $updateOrg = $pdo->prepare(
                        "UPDATE organizations
                        SET status = :status,
                            visibility_public = :visibility_public,
                            source_status = :source_status,
                            last_checked_at = :last_checked_at
                        WHERE id = :id"
                    );
                    $updateOrg->execute([
                        'status' => $values['status'],
                        'visibility_public' => (int)$values['visibility_public'],
                        'source_status' => $values['source_status'],
                        'last_checked_at' => $values['last_checked_at'] === '' ? null : $values['last_checked_at'],
                        'id' => $id,
                    ]);
                }

                $translationFields = array_intersect_key($changed, array_flip([
                    'name',
                    'professional_summary',
                    'type_label',
                    'age_range',
                    'professional_referral_or_access',
                ]));
                if ($translationFields) {
                    $upsertTranslation = $pdo->prepare(
                        "INSERT INTO organization_translations (
                            organization_id,
                            language_code,
                            name,
                            professional_summary,
                            type_label,
                            age_range,
                            professional_referral_or_access
                        )
                        VALUES (
                            :organization_id,
                            'nl',
                            :name,
                            :professional_summary,
                            :type_label,
                            :age_range,
                            :professional_referral_or_access
                        )
                        ON DUPLICATE KEY UPDATE
                            name = VALUES(name),
                            professional_summary = VALUES(professional_summary),
                            type_label = VALUES(type_label),
                            age_range = VALUES(age_range),
                            professional_referral_or_access = VALUES(professional_referral_or_access)"
                    );
                    $upsertTranslation->execute([
                        'organization_id' => $id,
                        'name' => $values['name'],
                        'professional_summary' => $values['professional_summary'],
                        'type_label' => $values['type_label'],
                        'age_range' => $values['age_range'],
                        'professional_referral_or_access' => $values['professional_referral_or_access'],
                    ]);
                }

                $contactFields = array_intersect_key($changed, array_flip([
                    'phone',
                    'whatsapp',
                    'email',
                    'website',
                    'address_nl',
                ]));
                if ($contactFields) {
                    $upsertContact = $pdo->prepare(
                        "INSERT INTO organization_contacts (organization_id, phone, whatsapp, email, website, address_nl)
                        VALUES (:organization_id, :phone, :whatsapp, :email, :website, :address_nl)
                        ON DUPLICATE KEY UPDATE
                            phone = VALUES(phone),
                            whatsapp = VALUES(whatsapp),
                            email = VALUES(email),
                            website = VALUES(website),
                            address_nl = VALUES(address_nl)"
                    );
                    $upsertContact->execute([
                        'organization_id' => $id,
                        'phone' => $values['phone'],
                        'whatsapp' => $values['whatsapp'],
                        'email' => $values['email'],
                        'website' => $values['website'],
                        'address_nl' => $values['address_nl'],
                    ]);
                }

                if ($audiencesChanged) {
                    $pdo->prepare('DELETE FROM organization_audience WHERE organization_id = :id')->execute(['id' => $id]);
                    $insertAudience = $pdo->prepare(
                        'INSERT INTO organization_audience (organization_id, audience_id)
                        VALUES (:organization_id, :audience_id)'
                    );
                    foreach ($selectedAudienceIds as $audienceId) {
                        $insertAudience->execute([
                            'organization_id' => $id,
                            'audience_id' => $audienceId,
                        ]);
                    }
                }

                if ($themesChanged) {
                    $pdo->prepare('DELETE FROM organization_theme WHERE organization_id = :id')->execute(['id' => $id]);
                    if ($selectedThemeIds) {
                        $insertTheme = $pdo->prepare(
                            'INSERT INTO organization_theme (organization_id, theme_id, is_primary, sort_order)
                            VALUES (:organization_id, :theme_id, :is_primary, :sort_order)'
                        );
                        $sortOrder = 1;
                        foreach ($selectedThemeIds as $themeId) {
                            $insertTheme->execute([
                                'organization_id' => $id,
                                'theme_id' => $themeId,
                                'is_primary' => $themeId === $primaryThemeId ? 1 : 0,
                                'sort_order' => $sortOrder,
                            ]);
                            $sortOrder++;
                        }
                    }
                }

                $basicAuditChanged = array_diff_key($changed, array_flip(['visibility_public']));
                if ($basicAuditChanged) {
                    write_audit_log(
                        'organization.update_basic',
                        'organization',
                        $id,
                        basic_audit_values($before, $basicAuditChanged),
                        basic_audit_values($after, $basicAuditChanged)
                    );
                }
                if (array_key_exists('visibility_public', $changed)) {
                    write_audit_log(
                        'organization.update_visibility',
                        'organization',
                        $id,
                        ['visibility_public' => $before['visibility_public']],
                        ['visibility_public' => $after['visibility_public']]
                    );
                }
                if ($audiencesChanged) {
                    write_audit_log(
                        'organization.update_audiences',
                        'organization',
                        $id,
                        ['audiences' => $audiencesBefore],
                        ['audiences' => $audiencesAfter]
                    );
                }
                if ($themesChanged) {
                    write_audit_log(
                        'organization.update_themes',
                        'organization',
                        $id,
                        ['themes' => $themesBefore],
                        ['themes' => $themesAfter]
                    );
                }

                $pdo->commit();
            }

            header('Location: organization_edit.php?id=' . rawurlencode((string)$id) . '&saved=1');
            exit;
        }
    }
} catch (Throwable $exception) {
    if (isset($pdo) && $pdo instanceof PDO && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    $knownMessages = [
        'Geen geldige organisatie-id opgegeven.',
        'Organisatie niet gevonden.',
        'Je hebt geen rechten om organisaties op te slaan.',
    ];
    $error = in_array($exception->getMessage(), $knownMessages, true)
        ? $exception->getMessage()
        : 'De organisatie kon niet worden geladen of opgeslagen. Probeer het later opnieuw.';
}

admin_header($organization ? 'Bewerken: ' . (string)$organization['name'] : 'Organisatie bewerken', 'organizations');
$publicYouthUrl = $organization ? admin_public_organization_url($organization, 'youth', $islands) : null;
$publicProfessionalUrl = $organization ? admin_public_organization_url($organization, 'professional', $islands) : null;
?>
<?php if ($error !== ''): ?>
  <p class="error"><?= h($error) ?></p>
  <p><a class="button" href="organizations.php">Terug naar organisaties</a></p>
  <?php admin_footer(); exit; ?>
<?php endif; ?>

<?php if (!admin_can_edit_organizations()): ?>
  <p class="error">Je kunt deze organisatie bekijken, maar je hebt geen rechten om wijzigingen op te slaan.</p>
  <p><a class="button" href="organization.php?id=<?= h((string)$id) ?>">Terug naar organisatie</a></p>
  <?php admin_footer(); exit; ?>
<?php endif; ?>

<?php if ((string)($_GET['saved'] ?? '') === '1'): ?>
  <p class="notice">De wijzigingen zijn succesvol opgeslagen.</p>
<?php endif; ?>

<section class="panel">
  <p class="notice">Wijzigingen worden na opslaan via de publieke data-API zichtbaar op de website.</p>
  <dl class="detail-list">
    <dt>Organisatie</dt>
    <dd><?= h($organization['name']) ?></dd>
    <dt>Slug</dt>
    <dd><code><?= h($organization['slug']) ?></code></dd>
  </dl>
  <div class="form-actions">
    <a class="button" href="organization.php?id=<?= h((string)$id) ?>">Terug naar organisatie</a>
    <?php if ($publicYouthUrl): ?>
      <a class="button" href="<?= h($publicYouthUrl) ?>">Bekijk jongerenpagina</a>
    <?php endif; ?>
    <?php if ($publicProfessionalUrl): ?>
      <a class="button" href="<?= h($publicProfessionalUrl) ?>">Bekijk professionalpagina</a>
    <?php endif; ?>
  </div>
</section>

<?php if ($errors): ?>
  <section class="panel">
    <h2>Controleer de invoer</h2>
    <ul class="error-list">
      <?php foreach ($errors as $message): ?>
        <li><?= h($message) ?></li>
      <?php endforeach; ?>
    </ul>
  </section>
<?php endif; ?>

<form method="post" action="organization_edit.php?id=<?= h((string)$id) ?>" class="panel edit-form">
  <input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>">
  <input type="hidden" name="id" value="<?= h((string)$id) ?>">

  <section class="form-section" id="basisgegevens">
  <div class="section-heading"><div><p class="eyebrow">Inhoud</p><h2>Basisgegevens</h2></div></div>
  <p class="form-help">Alleen Nederlandse basisvelden. De slug en profielantwoorden blijven ongewijzigd.</p>
  <label>
    Organisatienaam (NL)
    <input name="name" value="<?= h($values['name']) ?>" required>
  </label>
  <label>
    Korte omschrijving / summary (NL)
    <textarea name="professional_summary" rows="5" data-richtext-editor><?= h($values['professional_summary']) ?></textarea>
  </label>
  <div class="form-grid">
    <label>
      Type-label (NL)
      <input name="type_label" value="<?= h($values['type_label']) ?>">
    </label>
    <label>
      Leeftijd / age range (NL)
      <input name="age_range" value="<?= h($values['age_range']) ?>">
    </label>
  </div>
  <label>
    Toegang / verwijzing (NL)
    <textarea name="professional_referral_or_access" rows="5" data-richtext-editor><?= h($values['professional_referral_or_access']) ?></textarea>
  </label>
  <label>
    Slug (alleen-lezen)
    <input value="<?= h($organization['slug']) ?>" readonly>
  </label>
  </section>

  <section class="form-section" id="publicatie">
  <div class="section-heading"><div><p class="eyebrow">Publicatie</p><h2>Status</h2></div></div>
  <p class="form-help">Publicatiestatus is inhoudelijk gepubliceerd of concept. Publiek zichtbaar bepaalt apart of de organisatie op de website mag verschijnen.</p>
  <div class="form-grid">
    <label>
      Publicatiestatus
      <select name="status" required>
        <?php foreach (ORGANIZATION_STATUSES as $status): ?>
          <option value="<?= h($status) ?>" <?= $values['status'] === $status ? 'selected' : '' ?>><?= h(organization_edit_status_label($status)) ?></option>
        <?php endforeach; ?>
      </select>
      <small>Gebruik gepubliceerd wanneer de inhoud gereed is. Dit zet de organisatie niet automatisch publiek.</small>
    </label>
    <label>
      Publieke zichtbaarheid
      <span class="checkbox-inline">
        <input
          type="checkbox"
          name="visibility_public"
          value="1"
          <?= (int)$values['visibility_public'] === 1 ? 'checked' : '' ?>
        >
        Publiek zichtbaar
      </span>
      <small>Alleen zichtbaar op de publieke website als de publicatiestatus ook 'Gepubliceerd' is.</small>
    </label>
    <label>
      Bronstatus
      <select name="source_status" required>
        <?php foreach (ORGANIZATION_SOURCE_STATUSES as $sourceStatus): ?>
          <option value="<?= h($sourceStatus) ?>" <?= $values['source_status'] === $sourceStatus ? 'selected' : '' ?>><?= h(organization_edit_source_status_label($sourceStatus)) ?></option>
        <?php endforeach; ?>
      </select>
      <small>Geeft aan hoe actueel en betrouwbaar de broninformatie is.</small>
    </label>
    <label>
      Laatst gecontroleerd
      <input name="last_checked_at" type="date" value="<?= h($values['last_checked_at']) ?>">
      <small>Datum waarop de organisatiegegevens voor het laatst inhoudelijk zijn gecontroleerd.</small>
    </label>
  </div>
  </section>

  <section class="form-section" id="doelgroepen">
  <div class="section-heading"><div><p class="eyebrow">Doelgroepen</p><h2>Beschikbaar voor</h2></div></div>
  <p class="form-help">Koppel alleen doelgroepen waarvoor het profiel klaar genoeg is. Een organisatie met alleen 'Professional' verschijnt niet op de jongerenkant.</p>
  <div class="checkbox-list">
    <?php foreach ($availableAudiences as $audience): ?>
      <?php $audienceId = (int)$audience['id']; ?>
      <label>
        <input
          type="checkbox"
          name="audience_ids[]"
          value="<?= h((string)$audienceId) ?>"
          <?= in_array($audienceId, $selectedAudienceIds, true) ? 'checked' : '' ?>
        >
        <?= h((string)$audience['label_nl']) ?>
      </label>
    <?php endforeach; ?>
  </div>
  </section>

  <section class="form-section" id="themas">
  <div class="section-heading"><div><p class="eyebrow">Thema's</p><h2>Thema's koppelen</h2></div></div>
  <p class="form-help">Thema's mogen leeg blijven. Kies bij gekoppelde thema's een primair thema; zonder keuze wordt het eerste geselecteerde thema primair.</p>
  <?php if (!$selectedThemeIds): ?>
    <p class="notice notice-review">Er zijn nog geen thema's gekoppeld aan deze organisatie.</p>
  <?php endif; ?>
  <div class="relation-choice-list">
    <?php foreach ($availableThemes as $theme): ?>
      <?php $themeId = (int)$theme['id']; ?>
      <div class="relation-choice">
        <label>
          <input
            type="checkbox"
            name="theme_ids[]"
            value="<?= h((string)$themeId) ?>"
            <?= in_array($themeId, $selectedThemeIds, true) ? 'checked' : '' ?>
          >
          <?= h((string)$theme['name']) ?>
        </label>
        <label class="primary-choice">
          <input
            type="radio"
            name="primary_theme_id"
            value="<?= h((string)$themeId) ?>"
            <?= $primaryThemeId === $themeId ? 'checked' : '' ?>
          >
          primair
        </label>
      </div>
    <?php endforeach; ?>
  </div>
  </section>

  <section class="form-section" id="contactgegevens">
  <div class="section-heading"><div><p class="eyebrow">Bereikbaarheid</p><h2>Contactgegevens</h2></div></div>
  <div class="form-grid">
    <label>
      Telefoon
      <input name="phone" value="<?= h($values['phone']) ?>">
    </label>
    <label>
      WhatsApp
      <input name="whatsapp" value="<?= h($values['whatsapp']) ?>">
    </label>
    <label>
      E-mail
      <input name="email" type="email" value="<?= h($values['email']) ?>">
    </label>
    <label>
      Website
      <input name="website" value="<?= h($values['website']) ?>" placeholder="https://...">
    </label>
  </div>
  <label>
    Adres
    <textarea name="address_nl" rows="4"><?= h($values['address_nl']) ?></textarea>
  </label>
  </section>

  <div class="form-actions sticky-actions">
    <button type="submit">Opslaan</button>
    <a class="button" href="organization.php?id=<?= h((string)$id) ?>">Annuleren / terug</a>
  </div>
</form>
<?php admin_footer(); ?>
