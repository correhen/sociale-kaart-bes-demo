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
    'status' => '',
    'source_status' => '',
    'last_checked_at' => '',
    'phone' => '',
    'whatsapp' => '',
    'email' => '',
    'website' => '',
    'address_nl' => '',
];

function editable_snapshot(array $organization, ?array $contact): array
{
    return [
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
        'status' => trim((string)($_POST['status'] ?? '')),
        'source_status' => trim((string)($_POST['source_status'] ?? '')),
        'last_checked_at' => trim((string)($_POST['last_checked_at'] ?? '')),
        'phone' => trim((string)($_POST['phone'] ?? '')),
        'whatsapp' => trim((string)($_POST['whatsapp'] ?? '')),
        'email' => trim((string)($_POST['email'] ?? '')),
        'website' => trim((string)($_POST['website'] ?? '')),
        'address_nl' => trim((string)($_POST['address_nl'] ?? '')),
    ];
}

function validate_values(array $values): array
{
    $errors = [];
    if (!in_array($values['status'], ORGANIZATION_STATUSES, true)) {
        $errors[] = 'Ongeldige status.';
    }
    if (!in_array($values['source_status'], ORGANIZATION_SOURCE_STATUSES, true)) {
        $errors[] = 'Ongeldige bronstatus.';
    }
    if ($values['email'] !== '' && !filter_var($values['email'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'E-mail is ongeldig.';
    }
    if ($values['website'] !== '' && !preg_match('/^https?:\/\//i', $values['website'])) {
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
        if ((string)($before[$key] ?? '') !== (string)$value) {
            $changed[$key] = $value;
        }
    }

    return $changed;
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
    $values = editable_snapshot($organization, $contact);

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
            $before = editable_snapshot($organization, $contact);
            $after = $values;
            $changed = changed_values($before, $after);

            if ($changed) {
                $pdo = admin_db();
                $pdo->beginTransaction();

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

                write_audit_log(
                    'organization.update_basic',
                    'organization',
                    $id,
                    array_intersect_key($before, $changed),
                    array_intersect_key($after, $changed)
                );

                $pdo->commit();
            }

            header('Location: organization.php?id=' . rawurlencode((string)$id) . '&saved=1');
            exit;
        }
    }
} catch (Throwable $exception) {
    if (isset($pdo) && $pdo instanceof PDO && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    $error = $exception->getMessage();
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

<section class="panel">
  <p class="notice">In deze fase kun je alleen status en contactgegevens wijzigen.</p>
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

  <div class="form-actions">
    <button type="submit">Opslaan</button>
    <a class="button" href="organization.php?id=<?= h((string)$id) ?>">Annuleren</a>
  </div>
</form>
<?php admin_footer(); ?>
