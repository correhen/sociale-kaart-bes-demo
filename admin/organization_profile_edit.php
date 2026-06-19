<?php

declare(strict_types=1);

require __DIR__ . '/layout.php';
require_admin_login();

const PROFILE_LANGUAGES = ['nl', 'pap', 'en', 'es'];
const PROFILE_TRANSLATION_STATUSES = ['missing', 'draft', 'reviewed', 'published'];
const YOUTH_PROFILE_FIELDS = [
    'who_we_are' => 'Wie zijn wij?',
    'who_for' => 'Voor wie zijn wij?',
    'what_help' => 'Waarmee kunnen wij helpen?',
    'how_to_access' => 'Hoe kom je terecht?',
    'how_we_help' => 'Hoe helpen wij?',
    'duration' => 'Hoelang duurt de hulp?',
    'partners' => 'Met wie werken wij samen?',
    'contact' => 'Contact',
];
const PROFESSIONAL_PROFILE_GROUPS = [
    'general' => [
        'label' => 'Algemene informatie',
        'fields' => [
            'organisation_name' => 'Naam van de organisatie/instantie',
            'short_description' => 'Korte omschrijving van de organisatie',
            'target_group' => 'Doelgroep',
            'support_offer' => 'Hulpaanbod/dienstverlening',
            'services' => 'Welke diensten biedt u aan?',
            'methods' => 'Werkwijzen, methodieken of trainingen',
            'execution' => 'Uitvoering in de praktijk',
            'problems' => 'Problematiek of hulpvragen',
            'trajectory' => 'Verloop van een traject',
            'average_duration' => 'Gemiddelde duur van een traject',
        ],
    ],
    'referral' => [
        'label' => 'Verwijzing en toegankelijkheid',
        'fields' => [
            'when_appropriate' => 'Wanneer is doorverwijzing passend?',
            'criteria' => 'Verwijscriteria en toegankelijkheid',
            'indications_required' => 'Zijn indicaties nodig?',
        ],
    ],
    'practical' => [
        'label' => 'Praktische informatie',
        'fields' => [
            'contact_details' => 'Contactgegevens',
            'opening_hours' => 'Openingstijden',
            'waiting_times' => 'Eventuele wachttijden',
        ],
    ],
    'additional' => [
        'label' => 'Aanvullende informatie',
        'fields' => [
            'partners' => 'Samenwerkingspartners',
            'other_information' => 'Overige relevante informatie voor professionals',
        ],
    ],
];

$id = (int)($_GET['id'] ?? $_POST['id'] ?? 0);
$audience = trim((string)($_GET['audience'] ?? $_POST['audience'] ?? ''));
$organization = null;
$values = [];
$errors = [];
$error = '';
$saved = (string)($_GET['saved'] ?? '') === '1';

function profile_definition(string $audience): array
{
    if ($audience === 'youth') {
        return [
            '' => [
                'label' => 'Jongerenprofiel',
                'fields' => YOUTH_PROFILE_FIELDS,
            ],
        ];
    }

    return PROFESSIONAL_PROFILE_GROUPS;
}

function profile_empty_values(array $definition): array
{
    $values = [];
    foreach ($definition as $groupKey => $group) {
        foreach ($group['fields'] as $fieldKey => $label) {
            foreach (PROFILE_LANGUAGES as $language) {
                $values[$groupKey][$fieldKey][$language] = [
                    'answer_text' => '',
                    'translation_status' => 'missing',
                ];
            }
        }
    }

    return $values;
}

function profile_load_values(int $organizationId, string $audience, array $definition): array
{
    $values = profile_empty_values($definition);
    $rows = fetch_all(
        "SELECT group_key, field_key, language_code, answer_text, translation_status
        FROM organization_profile_answers
        WHERE organization_id = :organization_id
          AND audience_code = :audience
        ORDER BY sort_order ASC, group_key ASC, field_key ASC, language_code ASC",
        [
            'organization_id' => $organizationId,
            'audience' => $audience,
        ]
    );

    foreach ($rows as $row) {
        $group = (string)$row['group_key'];
        $field = (string)$row['field_key'];
        $language = (string)$row['language_code'];
        if (
            !isset($values[$group][$field][$language])
            || !in_array($language, PROFILE_LANGUAGES, true)
        ) {
            continue;
        }
        $values[$group][$field][$language] = [
            'answer_text' => (string)($row['answer_text'] ?? ''),
            'translation_status' => in_array((string)$row['translation_status'], PROFILE_TRANSLATION_STATUSES, true)
                ? (string)$row['translation_status']
                : 'missing',
        ];
    }

    return $values;
}

function profile_posted_values(array $current, array $definition): array
{
    $posted = is_array($_POST['answers'] ?? null) ? $_POST['answers'] : [];
    $values = $current;

    foreach ($definition as $groupKey => $group) {
        $postedGroupKey = $groupKey === '' ? '_root' : $groupKey;
        foreach ($group['fields'] as $fieldKey => $label) {
            foreach (PROFILE_LANGUAGES as $language) {
                if (!admin_can_edit_profile_language($language)) {
                    continue;
                }
                $cell = $posted[$postedGroupKey][$fieldKey][$language] ?? [];
                $postedText = is_array($cell) && is_scalar($cell['answer_text'] ?? null)
                    ? (string)$cell['answer_text']
                    : '';
                $postedStatus = is_array($cell) && is_scalar($cell['translation_status'] ?? null)
                    ? trim((string)$cell['translation_status'])
                    : '';
                $values[$groupKey][$fieldKey][$language] = [
                    'answer_text' => $postedText,
                    'translation_status' => $postedStatus,
                ];
            }
        }
    }

    return $values;
}

function profile_validate(array $values, array $definition): array
{
    $errors = [];
    foreach ($definition as $groupKey => $group) {
        foreach ($group['fields'] as $fieldKey => $label) {
            foreach (PROFILE_LANGUAGES as $language) {
                $status = (string)($values[$groupKey][$fieldKey][$language]['translation_status'] ?? '');
                if (!in_array($status, PROFILE_TRANSLATION_STATUSES, true)) {
                    $errors[] = $label . ' (' . strtoupper($language) . '): ongeldige vertaalstatus.';
                }
            }
        }
    }

    return $errors;
}

function profile_changes(array $before, array $after, array $definition): array
{
    $changes = [];
    $sortOrder = 0;
    foreach ($definition as $groupKey => $group) {
        foreach ($group['fields'] as $fieldKey => $label) {
            $sortOrder++;
            foreach (PROFILE_LANGUAGES as $language) {
                $old = $before[$groupKey][$fieldKey][$language];
                $new = $after[$groupKey][$fieldKey][$language];
                $textChanged = audit_values_differ($old['answer_text'], $new['answer_text']);
                $statusChanged = audit_values_differ($old['translation_status'], $new['translation_status']);
                if (!$textChanged && !$statusChanged) {
                    continue;
                }
                $auditBefore = [];
                $auditAfter = [];
                if ($textChanged) {
                    $auditBefore['answer_text'] = $old['answer_text'];
                    $auditAfter['answer_text'] = $new['answer_text'];
                }
                if ($statusChanged) {
                    $auditBefore['translation_status'] = $old['translation_status'];
                    $auditAfter['translation_status'] = $new['translation_status'];
                }
                $changes[] = [
                    'group_key' => $groupKey,
                    'field_key' => $fieldKey,
                    'language_code' => $language,
                    'sort_order' => $sortOrder,
                    'before' => $old,
                    'after' => $new,
                    'audit_before' => $auditBefore,
                    'audit_after' => $auditAfter,
                ];
            }
        }
    }

    return $changes;
}

function profile_audit_values(array $changes, string $side): array
{
    $answers = [];
    foreach ($changes as $change) {
        $key = ($change['group_key'] !== '' ? $change['group_key'] . '.' : '')
            . $change['field_key']
            . '.'
            . $change['language_code'];
        $answers[$key] = $change['audit_' . $side];
    }

    return $answers;
}

try {
    if ($id <= 0) {
        throw new RuntimeException('Geen geldige organisatie-id opgegeven.');
    }
    if (!in_array($audience, ['youth', 'professional'], true)) {
        throw new RuntimeException('Ongeldig profieltype.');
    }

    $organization = fetch_one(
        "SELECT o.id, o.slug, COALESCE(NULLIF(t.name, ''), o.slug) AS name
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

    $definition = profile_definition($audience);
    $values = profile_load_values($id, $audience, $definition);

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (!admin_can_edit_profiles()) {
            throw new RuntimeException('Je hebt geen rechten om profielantwoorden op te slaan.');
        }
        if (!verify_csrf((string)($_POST['csrf_token'] ?? ''))) {
            $errors[] = 'Het formulier is verlopen. Probeer opnieuw.';
        }

        $before = $values;
        $values = profile_posted_values($before, $definition);
        $errors = array_merge($errors, profile_validate($values, $definition));

        if (!$errors) {
            $changes = profile_changes($before, $values, $definition);
            if ($changes) {
                $pdo = admin_db();
                $pdo->beginTransaction();
                $upsert = $pdo->prepare(
                    "INSERT INTO organization_profile_answers (
                        organization_id,
                        audience_code,
                        group_key,
                        field_key,
                        language_code,
                        answer_text,
                        answer_format,
                        translation_status,
                        source_locked,
                        sort_order
                    )
                    VALUES (
                        :organization_id,
                        :audience_code,
                        :group_key,
                        :field_key,
                        :language_code,
                        :answer_text,
                        'plain_text',
                        :translation_status,
                        1,
                        :sort_order
                    )
                    ON DUPLICATE KEY UPDATE
                        answer_text = VALUES(answer_text),
                        answer_format = 'plain_text',
                        translation_status = VALUES(translation_status),
                        sort_order = VALUES(sort_order)"
                );

                foreach ($changes as $change) {
                    $upsert->execute([
                        'organization_id' => $id,
                        'audience_code' => $audience,
                        'group_key' => $change['group_key'],
                        'field_key' => $change['field_key'],
                        'language_code' => $change['language_code'],
                        'answer_text' => $change['after']['answer_text'],
                        'translation_status' => $change['after']['translation_status'],
                        'sort_order' => $change['sort_order'],
                    ]);
                }

                write_audit_log(
                    'organization.update_profile',
                    'organization',
                    $id,
                    [
                        'audience' => $audience,
                        'answers' => profile_audit_values($changes, 'before'),
                    ],
                    [
                        'audience' => $audience,
                        'answers' => profile_audit_values($changes, 'after'),
                    ]
                );
                $pdo->commit();
            }

            header(
                'Location: organization_profile_edit.php?id='
                . rawurlencode((string)$id)
                . '&audience='
                . rawurlencode($audience)
                . '&saved=1'
            );
            exit;
        }
    }
} catch (Throwable $exception) {
    if (isset($pdo) && $pdo instanceof PDO && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    $knownMessages = [
        'Geen geldige organisatie-id opgegeven.',
        'Ongeldig profieltype.',
        'Organisatie niet gevonden.',
        'Je hebt geen rechten om profielantwoorden op te slaan.',
    ];
    $error = in_array($exception->getMessage(), $knownMessages, true)
        ? $exception->getMessage()
        : 'Het profiel kon niet worden geladen of opgeslagen. Probeer het later opnieuw.';
}

$profileLabel = $audience === 'professional' ? 'Professionalprofiel' : 'Jongerenprofiel';
admin_header(
    $organization ? $profileLabel . ': ' . (string)$organization['name'] : $profileLabel,
    'organizations'
);
?>
<?php if ($error !== ''): ?>
  <p class="error"><?= h($error) ?></p>
  <p><a class="button" href="organizations.php">Terug naar organisaties</a></p>
  <?php admin_footer(); exit; ?>
<?php endif; ?>

<?php if ($saved): ?>
  <p class="notice">De profielwijzigingen zijn succesvol opgeslagen.</p>
<?php endif; ?>

<section class="panel">
  <p class="notice">Bewerk hier de vaste profielvelden. Nederlands is de brontekst.</p>
  <dl class="detail-list">
    <dt>Organisatie</dt><dd><?= h((string)$organization['name']) ?></dd>
    <dt>Slug</dt><dd><code><?= h((string)$organization['slug']) ?></code></dd>
    <dt>Profiel</dt><dd><?= h($profileLabel) ?></dd>
    <dt>Rechten</dt>
    <dd>
      <?php if (admin_can_edit_organizations()): ?>
        Alle talen bewerkbaar.
      <?php elseif (admin_has_role('translator')): ?>
        PAP, EN en ES bewerkbaar; NL is alleen-lezen.
      <?php else: ?>
        Alleen-lezen.
      <?php endif; ?>
    </dd>
  </dl>
  <div class="form-actions">
    <a class="button" href="organization.php?id=<?= h((string)$id) ?>">Terug naar organisatie</a>
    <a class="button" href="organization_profile_edit.php?id=<?= h((string)$id) ?>&amp;audience=<?= $audience === 'youth' ? 'professional' : 'youth' ?>">
      Naar <?= $audience === 'youth' ? 'professionalprofiel' : 'jongerenprofiel' ?>
    </a>
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

<form method="post" action="organization_profile_edit.php?id=<?= h((string)$id) ?>&amp;audience=<?= h($audience) ?>" class="panel profile-editor">
  <input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>">
  <input type="hidden" name="id" value="<?= h((string)$id) ?>">
  <input type="hidden" name="audience" value="<?= h($audience) ?>">

  <?php foreach ($definition as $groupKey => $group): ?>
    <section class="profile-group">
      <h2><?= h((string)$group['label']) ?></h2>

      <?php foreach ($group['fields'] as $fieldKey => $fieldLabel): ?>
        <article class="profile-field">
          <div class="profile-field-header">
            <h3><?= h((string)$fieldLabel) ?></h3>
            <code><?= h((string)$fieldKey) ?></code>
          </div>

          <div class="profile-language-grid">
            <?php foreach (PROFILE_LANGUAGES as $language): ?>
              <?php
              $cell = $values[$groupKey][$fieldKey][$language];
              $canEditLanguage = admin_can_edit_profile_language($language);
              $formGroupKey = $groupKey === '' ? '_root' : $groupKey;
              $inputBase = 'answers[' . $formGroupKey . '][' . $fieldKey . '][' . $language . ']';
              ?>
              <section class="profile-language<?= $language === 'nl' ? ' is-source' : '' ?>">
                <h4><?= h(strtoupper($language)) ?><?= $language === 'nl' ? ' - bron' : '' ?></h4>
                <label>
                  Antwoord
                  <textarea
                    name="<?= h($inputBase) ?>[answer_text]"
                    rows="7"
                    <?= $canEditLanguage ? '' : 'disabled' ?>
                  ><?= h((string)$cell['answer_text']) ?></textarea>
                </label>
                <label>
                  Vertaalstatus
                  <select name="<?= h($inputBase) ?>[translation_status]" <?= $canEditLanguage ? '' : 'disabled' ?>>
                    <?php foreach (PROFILE_TRANSLATION_STATUSES as $status): ?>
                      <option value="<?= h($status) ?>" <?= $cell['translation_status'] === $status ? 'selected' : '' ?>><?= h($status) ?></option>
                    <?php endforeach; ?>
                  </select>
                </label>
              </section>
            <?php endforeach; ?>
          </div>
        </article>
      <?php endforeach; ?>
    </section>
  <?php endforeach; ?>

  <div class="form-actions">
    <?php if (admin_can_edit_profiles()): ?>
      <button type="submit">Profiel opslaan</button>
    <?php endif; ?>
    <a class="button" href="organization.php?id=<?= h((string)$id) ?>">Annuleren</a>
  </div>
</form>

<?php admin_footer(); ?>
