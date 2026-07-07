<?php

declare(strict_types=1);

require __DIR__ . '/layout.php';
require_admin_login();

$error = '';
$errors = [];
$islands = [];
$audiences = [];
$values = [
    'island_id' => '',
    'name' => '',
    'professional_summary' => '',
    'audience_codes' => ['youth', 'professional'],
    'phone' => '',
    'whatsapp' => '',
    'email' => '',
    'website' => '',
    'address_nl' => '',
];

function organization_create_slugify(string $value): string
{
    $value = trim($value);
    if (function_exists('iconv')) {
        $converted = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $value);
        if ($converted !== false) {
            $value = $converted;
        }
    }
    $value = strtolower($value);
    $value = preg_replace('/[^a-z0-9]+/', '-', $value) ?? '';
    $value = trim($value, '-');

    return $value !== '' ? $value : 'organisatie';
}

function organization_create_unique_slug(PDO $pdo, string $base): string
{
    $slug = $base;
    $counter = 2;
    $stmt = $pdo->prepare('SELECT id FROM organizations WHERE slug = :slug LIMIT 1');
    while (true) {
        $stmt->execute(['slug' => $slug]);
        if (!$stmt->fetch()) {
            return $slug;
        }
        $slug = $base . '-' . $counter;
        $counter++;
    }
}

function organization_create_unique_external_key(PDO $pdo, string $slug): string
{
    $base = 'org_' . str_replace('-', '_', $slug);
    $key = $base;
    $counter = 2;
    $stmt = $pdo->prepare('SELECT id FROM organizations WHERE external_key = :external_key LIMIT 1');
    while (true) {
        $stmt->execute(['external_key' => $key]);
        if (!$stmt->fetch()) {
            return $key;
        }
        $key = $base . '_' . $counter;
        $counter++;
    }
}

function organization_create_island_code(array $islands, string $islandId): string
{
    foreach ($islands as $island) {
        if ((string)$island['id'] === $islandId) {
            return (string)$island['code'];
        }
    }

    return '';
}

function organization_create_posted_values(): array
{
    $audienceCodes = $_POST['audience_codes'] ?? [];
    if (!is_array($audienceCodes)) {
        $audienceCodes = [];
    }

    return [
        'island_id' => trim((string)($_POST['island_id'] ?? '')),
        'name' => (string)($_POST['name'] ?? ''),
        'professional_summary' => (string)($_POST['professional_summary'] ?? ''),
        'audience_codes' => array_values(array_unique(array_map('strval', $audienceCodes))),
        'phone' => (string)($_POST['phone'] ?? ''),
        'whatsapp' => (string)($_POST['whatsapp'] ?? ''),
        'email' => (string)($_POST['email'] ?? ''),
        'website' => (string)($_POST['website'] ?? ''),
        'address_nl' => (string)($_POST['address_nl'] ?? ''),
    ];
}

function organization_create_validate(array $values, array $islands, array $audiences): array
{
    $errors = [];
    $islandIds = array_map(static fn(array $island): string => (string)$island['id'], $islands);
    $audienceCodes = array_map(static fn(array $audience): string => (string)$audience['code'], $audiences);

    if (trim($values['name']) === '') {
        $errors[] = 'Organisatienaam is verplicht.';
    }
    if ($values['island_id'] === '' || !in_array($values['island_id'], $islandIds, true)) {
        $errors[] = 'Kies een eiland.';
    }
    if (trim($values['email']) !== '' && !filter_var(trim($values['email']), FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'E-mail is ongeldig.';
    }
    foreach ($values['audience_codes'] as $code) {
        if (!in_array($code, $audienceCodes, true)) {
            $errors[] = 'Een gekozen doelgroep is ongeldig.';
            break;
        }
    }

    return $errors;
}

try {
    if (!admin_can_edit_organizations()) {
        throw new RuntimeException('Je hebt geen rechten om organisaties aan te maken.');
    }

    $islands = fetch_all('SELECT id, code, name FROM islands ORDER BY sort_order ASC, name ASC');
    $audiences = fetch_all(
        "SELECT id, code, label_nl
        FROM audiences
        WHERE code IN ('youth', 'professional')
        ORDER BY sort_order ASC"
    );

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (!verify_csrf((string)($_POST['csrf_token'] ?? ''))) {
            $errors[] = 'Het formulier is verlopen. Probeer opnieuw.';
        }

        $values = organization_create_posted_values();
        $errors = array_merge($errors, organization_create_validate($values, $islands, $audiences));

        if (!$errors) {
            $pdo = admin_db();
            $pdo->beginTransaction();

            $islandCode = organization_create_island_code($islands, $values['island_id']);
            $slugBase = organization_create_slugify($values['name']);
            if ($islandCode !== '' && !preg_match('/(^|-)' . preg_quote($islandCode, '/') . '($|-)/', $slugBase)) {
                $slugBase = organization_create_slugify($slugBase . ' ' . $islandCode);
            }
            $slug = organization_create_unique_slug($pdo, $slugBase);
            $externalKey = organization_create_unique_external_key($pdo, $slug);

            $insertOrganization = $pdo->prepare(
                "INSERT INTO organizations (
                    external_key,
                    slug,
                    status,
                    source_status,
                    entity_type,
                    visibility_public,
                    source_locked
                )
                VALUES (
                    :external_key,
                    :slug,
                    'draft',
                    'submitted',
                    'organisation',
                    0,
                    1
                )"
            );
            $insertOrganization->execute([
                'external_key' => $externalKey,
                'slug' => $slug,
            ]);
            $organizationId = (int)$pdo->lastInsertId();

            $insertIsland = $pdo->prepare(
                'INSERT INTO organization_islands (organization_id, island_id, is_primary)
                VALUES (:organization_id, :island_id, 1)'
            );
            $insertIsland->execute([
                'organization_id' => $organizationId,
                'island_id' => (int)$values['island_id'],
            ]);

            $insertTranslation = $pdo->prepare(
                "INSERT INTO organization_translations (
                    organization_id,
                    language_code,
                    name,
                    professional_summary,
                    translation_status
                )
                VALUES (
                    :organization_id,
                    'nl',
                    :name,
                    :professional_summary,
                    'draft'
                )"
            );
            $insertTranslation->execute([
                'organization_id' => $organizationId,
                'name' => trim($values['name']),
                'professional_summary' => $values['professional_summary'],
            ]);

            $insertContact = $pdo->prepare(
                'INSERT INTO organization_contacts (organization_id, phone, whatsapp, email, website, address_nl)
                VALUES (:organization_id, :phone, :whatsapp, :email, :website, :address_nl)'
            );
            $insertContact->execute([
                'organization_id' => $organizationId,
                'phone' => $values['phone'],
                'whatsapp' => $values['whatsapp'],
                'email' => trim($values['email']),
                'website' => trim($values['website']),
                'address_nl' => $values['address_nl'],
            ]);

            if ($values['audience_codes']) {
                $audienceByCode = [];
                foreach ($audiences as $audience) {
                    $audienceByCode[(string)$audience['code']] = (int)$audience['id'];
                }
                $insertAudience = $pdo->prepare(
                    'INSERT INTO organization_audience (organization_id, audience_id)
                    VALUES (:organization_id, :audience_id)'
                );
                foreach ($values['audience_codes'] as $code) {
                    if (!isset($audienceByCode[$code])) {
                        continue;
                    }
                    $insertAudience->execute([
                        'organization_id' => $organizationId,
                        'audience_id' => $audienceByCode[$code],
                    ]);
                }
            }

            write_audit_log(
                'organization.create',
                'organization',
                $organizationId,
                [],
                [
                    'name.nl' => trim($values['name']),
                    'slug' => $slug,
                    'external_key' => $externalKey,
                    'status' => 'draft',
                    'source_status' => 'submitted',
                    'visibility_public' => 0,
                    'source_locked' => 1,
                ]
            );

            $pdo->commit();

            header('Location: organization.php?id=' . rawurlencode((string)$organizationId) . '&created=1');
            exit;
        }
    }
} catch (Throwable $exception) {
    if (isset($pdo) && $pdo instanceof PDO && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    $knownMessages = [
        'Je hebt geen rechten om organisaties aan te maken.',
    ];
    $error = in_array($exception->getMessage(), $knownMessages, true)
        ? $exception->getMessage()
        : 'De organisatie kon niet worden aangemaakt. Probeer het later opnieuw.';
}

admin_header('Nieuwe organisatie toevoegen', 'dashboard');
?>
<?php if ($error !== ''): ?>
  <p class="error"><?= h($error) ?></p>
  <p><a class="button" href="dashboard.php">Terug naar dashboard</a></p>
  <?php admin_footer(); exit; ?>
<?php endif; ?>

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

<section class="panel">
  <p class="notice">Nieuwe organisaties worden aangemaakt als concept en zijn niet publiek zichtbaar. Na aanmaken kun je basisgegevens en profielen verder aanvullen.</p>
</section>

<form method="post" action="organization_create.php" class="panel edit-form">
  <input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>">

  <section class="form-section">
    <div class="section-heading"><div><p class="eyebrow">Basis</p><h2>Nieuwe organisatie</h2></div></div>
    <div class="form-grid">
      <label>
        Eiland
        <select name="island_id" required>
          <option value="">Kies een eiland</option>
          <?php foreach ($islands as $island): ?>
            <option value="<?= h((string)$island['id']) ?>" <?= $values['island_id'] === (string)$island['id'] ? 'selected' : '' ?>><?= h((string)$island['name']) ?></option>
          <?php endforeach; ?>
        </select>
      </label>
      <label>
        Organisatienaam (NL)
        <input name="name" value="<?= h($values['name']) ?>" required>
      </label>
    </div>
    <label>
      Korte omschrijving NL
      <textarea name="professional_summary" rows="5" data-richtext-editor><?= h($values['professional_summary']) ?></textarea>
    </label>
  </section>

  <?php if ($audiences): ?>
    <section class="form-section">
      <div class="section-heading"><div><p class="eyebrow">Doelgroep</p><h2>Beschikbaar voor</h2></div></div>
      <div class="checkbox-list">
        <?php foreach ($audiences as $audience): ?>
          <label>
            <input
              type="checkbox"
              name="audience_codes[]"
              value="<?= h((string)$audience['code']) ?>"
              <?= in_array((string)$audience['code'], $values['audience_codes'], true) ? 'checked' : '' ?>
            >
            <?= h((string)$audience['label_nl']) ?>
          </label>
        <?php endforeach; ?>
      </div>
    </section>
  <?php endif; ?>

  <section class="form-section">
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
    <button type="submit">Organisatie aanmaken</button>
    <a class="button" href="dashboard.php">Annuleren</a>
  </div>
</form>

<?php admin_footer(); ?>
