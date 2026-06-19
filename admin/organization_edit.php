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
$values = [
    'name' => '',
    'professional_summary' => '',
    'type_label' => '',
    'age_range' => '',
    'professional_referral_or_access' => '',
    'status' => '',
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

function posted_values(): array
{
    return [
        'name' => (string)($_POST['name'] ?? ''),
        'professional_summary' => (string)($_POST['professional_summary'] ?? ''),
        'type_label' => (string)($_POST['type_label'] ?? ''),
        'age_range' => (string)($_POST['age_range'] ?? ''),
        'professional_referral_or_access' => (string)($_POST['professional_referral_or_access'] ?? ''),
        'status' => trim((string)($_POST['status'] ?? '')),
        'source_status' => trim((string)($_POST['source_status'] ?? '')),
        'last_checked_at' => trim((string)($_POST['last_checked_at'] ?? '')),
        'phone' => (string)($_POST['phone'] ?? ''),
        'whatsapp' => (string)($_POST['whatsapp'] ?? ''),
        'email' => (string)($_POST['email'] ?? ''),
        'website' => (string)($_POST['website'] ?? ''),
        'address_nl' => (string)($_POST['address_nl'] ?? ''),
    ];
}

function validate_values(array $values): array
{
    $errors = [];
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

function basic_audit_key(string $field): string
{
    $keys = [
        'name' => 'name.nl',
        'professional_summary' => 'professional_summary.nl',
        'type_label' => 'type_label.nl',
        'age_range' => 'age_range.nl',
        'professional_referral_or_access' => 'professional_referral_or_access.nl',
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
        "SELECT o.*, COALESCE(NULLIF(t.name, ''), o.slug) AS name
        FROM organizations o
        LEFT JOIN organization_translations t
            ON t.organization_id = o.id
            AND t.language_code = 'nl'
        WHERE o.id = :id
        LIMIT 1",
        ['id' => $id]
    );

    if (!$organization) {
        throw new RuntimeException('Organisatie niet gevonden.');
    }

    $contact = fetch_one('SELECT * FROM organization_contacts WHERE organization_id = :id', ['id' => $id]);
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
        $errors = array_merge($errors, validate_values($values));

        if (!$errors) {
            $before = editable_snapshot($organization, $translation, $contact);
            $after = $values;
            $changed = changed_values($before, $after);

            if ($changed) {
                $pdo = admin_db();
                $pdo->beginTransaction();

                $organizationFields = array_intersect_key($changed, array_flip([
                    'status',
                    'source_status',
                    'last_checked_at',
                ]));
                if ($organizationFields) {
                    $updateOrg = $pdo->prepare(
                        "UPDATE organizations
                        SET status = :status,
                            source_status = :source_status,
                            last_checked_at = :last_checked_at
                        WHERE id = :id"
                    );
                    $updateOrg->execute([
                        'status' => $values['status'],
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

                write_audit_log(
                    'organization.update_basic',
                    'organization',
                    $id,
                    basic_audit_values($before, $changed),
                    basic_audit_values($after, $changed)
                );

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
  <p class="notice">In deze fase kun je Nederlandse basisgegevens, status en contactgegevens wijzigen.</p>
  <dl class="detail-list">
    <dt>Organisatie</dt>
    <dd><?= h($organization['name']) ?></dd>
    <dt>Slug</dt>
    <dd><code><?= h($organization['slug']) ?></code></dd>
  </dl>
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

  <section class="form-section">
  <h2>Basisgegevens</h2>
  <p class="muted">Alleen Nederlandse basisvelden. De slug en profielantwoorden blijven ongewijzigd.</p>
  <label>
    Organisatienaam (NL)
    <input name="name" value="<?= h($values['name']) ?>" required>
  </label>
  <label>
    Korte omschrijving / summary (NL)
    <textarea name="professional_summary" rows="5"><?= h($values['professional_summary']) ?></textarea>
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
    <textarea name="professional_referral_or_access" rows="5"><?= h($values['professional_referral_or_access']) ?></textarea>
  </label>
  <label>
    Slug (alleen-lezen)
    <input value="<?= h($organization['slug']) ?>" readonly>
  </label>
  </section>

  <section class="form-section">
  <h2>Status</h2>
  <div class="form-grid">
    <label>
      Publicatiestatus
      <select name="status" required>
        <?php foreach (ORGANIZATION_STATUSES as $status): ?>
          <option value="<?= h($status) ?>" <?= $values['status'] === $status ? 'selected' : '' ?>><?= h($status) ?></option>
        <?php endforeach; ?>
      </select>
    </label>
    <label>
      Bronstatus
      <select name="source_status" required>
        <?php foreach (ORGANIZATION_SOURCE_STATUSES as $sourceStatus): ?>
          <option value="<?= h($sourceStatus) ?>" <?= $values['source_status'] === $sourceStatus ? 'selected' : '' ?>><?= h($sourceStatus) ?></option>
        <?php endforeach; ?>
      </select>
    </label>
    <label>
      Laatst gecontroleerd
      <input name="last_checked_at" type="date" value="<?= h($values['last_checked_at']) ?>">
    </label>
  </div>
  </section>

  <section class="form-section">
  <h2>Contactgegevens</h2>
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

  <div class="form-actions">
    <button type="submit">Opslaan</button>
    <a class="button" href="organization.php?id=<?= h((string)$id) ?>">Annuleren</a>
  </div>
</form>
<?php admin_footer(); ?>
