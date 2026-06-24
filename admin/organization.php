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

function render_profile_table(array $answers, array $languages): void
{
    ?>
  <div class="table-wrap">
    <table>
      <thead>
        <tr>
          <th>Groep</th>
          <th>Veld</th>
          <?php foreach ($languages as $language): ?>
            <th><?= h($language) ?></th>
          <?php endforeach; ?>
        </tr>
      </thead>
      <tbody>
        <?php if (!$answers): ?>
          <tr><td colspan="<?= h((string)(count($languages) + 2)) ?>" class="muted">Geen profielvelden gevonden.</td></tr>
        <?php endif; ?>
        <?php foreach ($answers as $answer): ?>
          <tr>
            <td><?= empty_label($answer['group_key']) ?></td>
            <td><code><?= h($answer['field_key']) ?></code></td>
            <?php foreach ($languages as $language): ?>
              <?php $cell = $answer['languages'][$language] ?? null; ?>
              <td class="profile-answer">
                <?php if ($cell): ?>
                  <?= empty_label($cell['answer_text']) ?>
                  <br><small class="muted">status: <?= h($cell['translation_status']) ?></small>
                <?php else: ?>
                  <span class="muted">ontbreekt</span>
                <?php endif; ?>
              </td>
            <?php endforeach; ?>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
<?php
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
?>
<?php if ($error !== ''): ?>
  <p class="error"><?= h($error) ?></p>
  <p><a class="button" href="organizations.php">Terug naar organisaties</a></p>
  <?php admin_footer(); exit; ?>
<?php endif; ?>

<section class="detail-hero">
  <div>
    <a class="back-link" href="organizations.php">← Terug naar overzicht</a>
    <p class="eyebrow">Organisatie</p>
    <h2><?= h((string)$organization['name']) ?></h2>
    <div class="status-row">
      <?= status_badge($organization['status']) ?>
      <?= status_badge($organization['source_status']) ?>
      <span class="badge"><?= ((int)$organization['visibility_public'] === 1) ? 'publiek zichtbaar' : 'niet publiek' ?></span>
    </div>
  </div>
  <div class="detail-actions">
    <?php if (admin_can_edit_organizations()): ?>
      <a class="button primary" href="organization_edit.php?id=<?= h((string)$organization['id']) ?>">Basisgegevens bewerken</a>
    <?php endif; ?>
    <a class="button" href="organization_profile_edit.php?id=<?= h((string)$organization['id']) ?>&amp;audience=youth">
      <?= admin_can_edit_profiles() ? 'Jongerenprofiel bewerken' : 'Jongerenprofiel bekijken' ?>
    </a>
    <a class="button" href="organization_profile_edit.php?id=<?= h((string)$organization['id']) ?>&amp;audience=professional">
      <?= admin_can_edit_profiles() ? 'Professionalprofiel bewerken' : 'Professionalprofiel bekijken' ?>
    </a>
    <?php if ($publicYouthUrl): ?>
      <a class="button" href="<?= h($publicYouthUrl) ?>">Bekijk jongerenpagina</a>
    <?php endif; ?>
    <?php if ($publicProfessionalUrl): ?>
      <a class="button" href="<?= h($publicProfessionalUrl) ?>">Bekijk professionalpagina</a>
    <?php endif; ?>
  </div>
</section>

<?php if ($saved): ?>
  <p class="notice">Wijzigingen zijn opgeslagen.</p>
<?php endif; ?>

<div class="section-nav">
  <a class="button" href="#basis">Basis</a>
  <a class="button" href="#contact">Contact</a>
  <a class="button" href="#youth">Jongerenprofiel</a>
  <a class="button" href="#professional">Professionalprofiel</a>
  <a class="button" href="#translations">Vertalingen/status</a>
</div>

<section class="panel detail-section" id="basis">
  <div class="panel-heading"><div><p class="eyebrow">Overzicht</p><h2>Basisgegevens</h2></div></div>
  <dl class="detail-list">
    <dt>ID</dt><dd><?= h((string)$organization['id']) ?></dd>
    <dt>Naam</dt><dd><?= h($organization['name']) ?></dd>
    <dt>Slug</dt><dd><code><?= h($organization['slug']) ?></code></dd>
    <dt>Status</dt><dd><?= status_badge($organization['status']) ?></dd>
    <dt>Bronstatus</dt><dd><?= status_badge($organization['source_status']) ?></dd>
    <dt>Zichtbaar publiek</dt><dd><?= ((int)$organization['visibility_public'] === 1) ? 'ja' : 'nee' ?></dd>
    <dt>Source locked</dt><dd><?= ((int)$organization['source_locked'] === 1) ? 'ja' : 'nee' ?></dd>
    <dt>Bijgewerkt</dt><dd><?= h((string)$organization['updated_at']) ?></dd>
    <dt>Laatst gecontroleerd</dt><dd><?= readable_date($organization['last_checked_at']) ?></dd>
  </dl>

  <div class="relation-grid">
  <div><h3>Eilanden</h3>
  <ul class="compact-list">
    <?php foreach ($islands as $island): ?>
      <li><?= h($island['name']) ?> <small class="muted"><?= h($island['code']) ?><?= (int)$island['is_primary'] === 1 ? ', primair' : '' ?></small></li>
    <?php endforeach; ?>
  </ul></div>

  <div><h3>Thema's</h3>
  <ul class="compact-list">
    <?php foreach ($themes as $theme): ?>
      <li><?= h($theme['name']) ?> <small class="muted"><?= h($theme['slug']) ?><?= (int)$theme['is_primary'] === 1 ? ', primair' : '' ?></small></li>
    <?php endforeach; ?>
  </ul></div>

  <div><h3>Doelgroepen</h3>
  <ul class="compact-list">
    <?php foreach ($audiences as $audience): ?>
      <li><?= h($audience['label_nl']) ?> <small class="muted"><?= h($audience['code']) ?></small></li>
    <?php endforeach; ?>
  </ul></div>
  </div>
</section>

<section class="panel detail-section" id="contact">
  <h2>Contact</h2>
  <dl class="detail-list">
    <dt>Telefoon</dt><dd><?= empty_label($contact['phone'] ?? '') ?></dd>
    <dt>WhatsApp</dt><dd><?= empty_label($contact['whatsapp'] ?? '') ?></dd>
    <dt>E-mail</dt><dd><?= empty_label($contact['email'] ?? '') ?></dd>
    <dt>Website</dt><dd><?= empty_label($contact['website'] ?? '') ?></dd>
    <dt>Adres NL</dt><dd><?= empty_label($contact['address_nl'] ?? '') ?></dd>
  </dl>
</section>

<section class="panel detail-section" id="youth">
  <h2>Jongerenprofiel</h2>
  <p class="muted">Lege velden blijven zichtbaar voor beheercontrole.</p>
  <?php render_profile_table($youthAnswers, $languages); ?>
</section>

<section class="panel detail-section" id="professional">
  <h2>Professionalprofiel</h2>
  <p class="muted">Lege velden blijven zichtbaar voor beheercontrole.</p>
  <?php render_profile_table($professionalAnswers, $languages); ?>
</section>

<section class="panel detail-section" id="translations">
  <h2>Vertalingen/status</h2>
  <div class="table-wrap">
    <table>
      <thead>
        <tr>
          <th>Taal</th>
          <th>Status</th>
          <th>Naam</th>
          <th>Jongerentitel</th>
          <th>Korte jongerentekst</th>
          <th>Professionele samenvatting</th>
          <th>Type</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($languages as $language): ?>
          <?php $row = $translations[$language] ?? null; ?>
          <tr>
            <td><code><?= h($language) ?></code></td>
            <td><?= $row ? status_badge($row['translation_status']) : status_badge('missing') ?></td>
            <td><?= $row ? empty_label($row['name']) : '<span class="muted">ontbreekt</span>' ?></td>
            <td><?= $row ? empty_label($row['youth_title']) : '<span class="muted">ontbreekt</span>' ?></td>
            <td><?= $row ? empty_label($row['youth_short']) : '<span class="muted">ontbreekt</span>' ?></td>
            <td><?= $row ? empty_label($row['professional_summary']) : '<span class="muted">ontbreekt</span>' ?></td>
            <td><?= $row ? empty_label($row['type_label']) : '<span class="muted">ontbreekt</span>' ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</section>

<?php admin_footer(); ?>
