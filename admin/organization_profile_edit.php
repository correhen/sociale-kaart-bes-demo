<?php

declare(strict_types=1);

require __DIR__ . '/layout.php';
require_admin_login();

const PROFILE_LANGUAGES = ['nl', 'pap', 'en', 'es'];
const PROFILE_TRANSLATION_STATUSES = ['missing', 'draft', 'reviewed', 'published'];
const PROFILE_INTRO_FIELDS = [
    'youth' => [
        'field' => 'youth_short',
        'label' => 'Korte tekst jongerenpagina',
        'help' => 'Deze tekst verschijnt kort onder de organisatietitel op de publieke pagina en op organisatiekaarten.',
    ],
    'professional' => [
        'field' => 'professional_summary',
        'label' => 'Korte tekst professionalpagina',
        'help' => 'Deze tekst verschijnt kort onder de organisatietitel op de publieke pagina en op organisatiekaarten.',
    ],
];
const YOUTH_PROFILE_FIELDS = [
    'who_we_are' => 'Wie zijn wij?',
    'who_for' => 'Voor wie zijn wij?',
    'what_help' => 'Waarmee kunnen wij helpen?',
    'how_to_access' => 'Hoe kom je bij deze organisatie terecht?',
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
$islands = [];
$values = [];
$introValues = [];
$errors = [];
$error = '';
$saved = (string)($_GET['saved'] ?? '') === '1';
$selectedLanguage = trim((string)($_GET['language'] ?? $_POST['language'] ?? 'nl'));
if (!in_array($selectedLanguage, PROFILE_LANGUAGES, true)) {
    $selectedLanguage = 'nl';
}

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
                if (!isset($posted[$postedGroupKey][$fieldKey][$language])) {
                    continue;
                }
                $cell = $posted[$postedGroupKey][$fieldKey][$language];
                $postedText = is_array($cell) && is_scalar($cell['answer_text'] ?? null)
                    ? (string)$cell['answer_text']
                    : $current[$groupKey][$fieldKey][$language]['answer_text'];
                $postedStatus = is_array($cell) && is_scalar($cell['translation_status'] ?? null)
                    ? trim((string)$cell['translation_status'])
                    : $current[$groupKey][$fieldKey][$language]['translation_status'];
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
                if (!admin_can_edit_profile_language($language)) {
                    continue;
                }
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

function profile_intro_empty_values(): array
{
    return array_fill_keys(PROFILE_LANGUAGES, '');
}

function profile_intro_load_values(int $organizationId, string $audience): array
{
    $field = PROFILE_INTRO_FIELDS[$audience]['field'];
    $values = profile_intro_empty_values();
    $rows = fetch_all(
        "SELECT language_code, {$field} AS intro_text
        FROM organization_translations
        WHERE organization_id = :organization_id
        ORDER BY language_code ASC",
        ['organization_id' => $organizationId]
    );

    foreach ($rows as $row) {
        $language = (string)$row['language_code'];
        if (in_array($language, PROFILE_LANGUAGES, true)) {
            $values[$language] = (string)($row['intro_text'] ?? '');
        }
    }

    return $values;
}

function profile_intro_posted_values(array $current): array
{
    $posted = is_array($_POST['intro'] ?? null) ? $_POST['intro'] : [];
    $values = $current;

    foreach (PROFILE_LANGUAGES as $language) {
        if (!admin_can_edit_profile_language($language) || !array_key_exists($language, $posted)) {
            continue;
        }
        $values[$language] = is_scalar($posted[$language]) ? (string)$posted[$language] : $current[$language];
    }

    return $values;
}

function profile_intro_changes(array $before, array $after): array
{
    $changes = [];
    foreach (PROFILE_LANGUAGES as $language) {
        if (!admin_can_edit_profile_language($language)) {
            continue;
        }
        if (!audit_values_differ($before[$language] ?? '', $after[$language] ?? '')) {
            continue;
        }
        $changes[] = [
            'language_code' => $language,
            'before' => (string)($before[$language] ?? ''),
            'after' => (string)($after[$language] ?? ''),
        ];
    }

    return $changes;
}

function profile_intro_audit_values(array $changes, string $field, string $side): array
{
    $values = [];
    foreach ($changes as $change) {
        $values[$field . '.' . $change['language_code']] = $change[$side];
    }

    return $values;
}

function profile_source_language(array $islands): string
{
    foreach ($islands as $island) {
        $code = (string)($island['code'] ?? '');
        if ((int)($island['is_primary'] ?? 0) === 1) {
            return $code === 'bonaire' ? 'nl' : 'en';
        }
    }

    return 'nl';
}

function admin_asset_icon(string $path, string $class = 'admin-icon admin-icon--sm'): string
{
    return '<img class="' . h($class) . '" src="../assets/admin-icons/admin_assetpack_sociale_kaart_bes_v1/' . h($path) . '" alt="" aria-hidden="true">';
}

function admin_language_flag(string $language): string
{
    return admin_asset_icon('flags/svg/flag-' . $language . '.svg', 'admin-flag');
}

function admin_status_icon(string $statusClass): string
{
    $icons = [
        'status-filled' => 'icons/status/ready.svg',
        'status-ready' => 'icons/status/ready.svg',
        'status-review' => 'icons/status/review-needed.svg',
        'status-draft' => 'icons/status/draft.svg',
        'status-empty' => 'icons/status/empty.svg',
        'status-fallback' => 'icons/status/fallback.svg',
    ];

    return admin_asset_icon($icons[$statusClass] ?? 'icons/status/info.svg');
}

function profile_editor_state(array $cell, string $language, string $sourceLanguage, string $sourceText = ''): array
{
    $text = trim((string)($cell['answer_text'] ?? ''));
    $status = (string)($cell['translation_status'] ?? 'missing');
    if ($text === '' && $language !== $sourceLanguage && trim($sourceText) !== '') {
        return ['label' => 'Fallback', 'class' => 'status-fallback', 'filter' => 'fallback'];
    }
    if ($text === '') {
        return ['label' => 'Leeg', 'class' => 'status-empty', 'filter' => 'empty'];
    }
    if ($language === 'pap') {
        return ['label' => 'Review nodig', 'class' => 'status-review', 'filter' => 'review'];
    }
    if ($language === $sourceLanguage || in_array($status, ['published', 'reviewed'], true)) {
        return ['label' => 'Gevuld', 'class' => 'status-filled', 'filter' => 'filled'];
    }

    return ['label' => 'Concept', 'class' => 'status-draft', 'filter' => 'review'];
}

function profile_editor_badge(array $state): string
{
    return '<span class="admin-status-pill ' . h($state['class']) . '">' . admin_status_icon((string)$state['class']) . h($state['label']) . '</span>';
}

function profile_editor_summary(array $definition, array $values, array $introValues, string $language, string $sourceLanguage): array
{
    $summary = ['total' => 1, 'filled' => 0, 'review' => 0, 'empty' => 0, 'fallback' => 0];
    $introCell = [
        'answer_text' => (string)($introValues[$language] ?? ''),
        'translation_status' => trim((string)($introValues[$language] ?? '')) === '' ? 'missing' : 'draft',
    ];
    $introState = profile_editor_state($introCell, $language, $sourceLanguage, (string)($introValues[$sourceLanguage] ?? ''));
    $summary[$introState['filter']]++;
    if (trim((string)($introCell['answer_text'] ?? '')) !== '') {
        $summary['filled']++;
    }

    foreach ($definition as $groupKey => $group) {
        foreach ($group['fields'] as $fieldKey => $label) {
            $summary['total']++;
            $cell = $values[$groupKey][$fieldKey][$language];
            $sourceText = (string)($values[$groupKey][$fieldKey][$sourceLanguage]['answer_text'] ?? '');
            $state = profile_editor_state($cell, $language, $sourceLanguage, $sourceText);
            $summary[$state['filter']]++;
            if (trim((string)($cell['answer_text'] ?? '')) !== '') {
                $summary['filled']++;
            }
        }
    }

    return $summary;
}

try {
    if ($id <= 0) {
        throw new RuntimeException('Geen geldige organisatie-id opgegeven.');
    }
    if (!in_array($audience, ['youth', 'professional'], true)) {
        throw new RuntimeException('Ongeldig profieltype.');
    }

    $organization = fetch_one(
        "SELECT o.id, o.slug, o.status, o.visibility_public, COALESCE(NULLIF(t.name, ''), NULLIF(t_en.name, ''), o.slug) AS name
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
    $islands = fetch_all(
        "SELECT i.code, i.name, oi.is_primary
        FROM organization_islands oi
        INNER JOIN islands i ON i.id = oi.island_id
        WHERE oi.organization_id = :id
        ORDER BY oi.is_primary DESC, i.sort_order ASC",
        ['id' => $id]
    );

    $definition = profile_definition($audience);
    $values = profile_load_values($id, $audience, $definition);
    $introValues = profile_intro_load_values($id, $audience);

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (!admin_can_edit_profiles()) {
            throw new RuntimeException('Je hebt geen rechten om profielantwoorden op te slaan.');
        }
        if (!verify_csrf((string)($_POST['csrf_token'] ?? ''))) {
            $errors[] = 'Het formulier is verlopen. Probeer opnieuw.';
        }

        $before = $values;
        $introBefore = $introValues;
        $values = profile_posted_values($before, $definition);
        $introValues = profile_intro_posted_values($introBefore);
        $errors = array_merge($errors, profile_validate($values, $definition));

        if (!$errors) {
            $changes = profile_changes($before, $values, $definition);
            $introChanges = profile_intro_changes($introBefore, $introValues);
            if ($changes || $introChanges) {
                $pdo = admin_db();
                $pdo->beginTransaction();
                if ($changes) {
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
                            'markdown',
                            :translation_status,
                            1,
                            :sort_order
                        )
                        ON DUPLICATE KEY UPDATE
                            answer_text = VALUES(answer_text),
                            answer_format = 'markdown',
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
                }

                if ($introChanges) {
                    $introField = PROFILE_INTRO_FIELDS[$audience]['field'];
                    $introSql = "INSERT INTO organization_translations (
                            organization_id,
                            language_code,
                            {$introField},
                            translation_status
                        )
                        VALUES (
                            :organization_id,
                            :language_code,
                            :intro_text,
                            'draft'
                        )
                        ON DUPLICATE KEY UPDATE
                            {$introField} = VALUES({$introField})";
                    $upsertIntro = $pdo->prepare($introSql);
                    foreach ($introChanges as $change) {
                        $upsertIntro->execute([
                            'organization_id' => $id,
                            'language_code' => $change['language_code'],
                            'intro_text' => $change['after'],
                        ]);
                    }

                    write_audit_log(
                        'organization.update_translation_intro',
                        'organization',
                        $id,
                        [
                            'audience' => $audience,
                            'translations' => profile_intro_audit_values($introChanges, $introField, 'before'),
                        ],
                        [
                            'audience' => $audience,
                            'translations' => profile_intro_audit_values($introChanges, $introField, 'after'),
                        ]
                    );
                }
                $pdo->commit();
            }

            header(
                'Location: organization_profile_edit.php?id='
                . rawurlencode((string)$id)
                . '&audience='
                . rawurlencode($audience)
                . '&language='
                . rawurlencode($selectedLanguage)
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
$introDefinition = PROFILE_INTRO_FIELDS[$audience] ?? PROFILE_INTRO_FIELDS['youth'];
$sourceLanguage = profile_source_language($islands);
$selectedSummary = profile_editor_summary($definition, $values, $introValues, $selectedLanguage, $sourceLanguage);
$languageSummaries = [];
foreach (PROFILE_LANGUAGES as $language) {
    $languageSummaries[$language] = profile_editor_summary($definition, $values, $introValues, $language, $sourceLanguage);
}
$baseProfileUrl = 'organization_profile_edit.php?id=' . rawurlencode((string)$id) . '&audience=';
admin_header(
    $organization ? $profileLabel . ': ' . (string)$organization['name'] : $profileLabel,
    'organizations'
);
$publicUrl = $organization ? admin_public_organization_url($organization, $audience, $islands) : null;
?>
<?php if ($error !== ''): ?>
  <p class="error"><?= h($error) ?></p>
  <p><a class="button" href="organizations.php">Terug naar organisaties</a></p>
  <?php admin_footer(); exit; ?>
<?php endif; ?>

<?php if ($saved): ?>
  <p class="notice">Opgeslagen.</p>
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

<form method="post" action="organization_profile_edit.php?id=<?= h((string)$id) ?>&amp;audience=<?= h($audience) ?>&amp;language=<?= h($selectedLanguage) ?>" class="profile-editor profile-editor-single-language" data-profile-editor-form>
  <input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>">
  <input type="hidden" name="id" value="<?= h((string)$id) ?>">
  <input type="hidden" name="audience" value="<?= h($audience) ?>">
  <input type="hidden" name="language" value="<?= h($selectedLanguage) ?>">

  <section class="profile-editor-hero">
    <div>
      <a class="back-link" href="organization.php?id=<?= h((string)$id) ?>"><?= admin_asset_icon('icons/navigation/back.svg') ?>Terug naar organisatie</a>
      <p class="eyebrow">Redactie</p>
      <h2><?= h($profileLabel) ?> - <?= h((string)$organization['name']) ?></h2>
      <p><?= h($profileLabel) ?> / gekozen taal: <strong><?= h(strtoupper($selectedLanguage)) ?></strong> / brontaal: <strong><?= h(strtoupper($sourceLanguage)) ?></strong></p>
      <p class="save-state is-saved" data-save-state>Opgeslagen</p>
      <p class="unsaved-notice" data-unsaved-notice hidden><?= admin_asset_icon('icons/status/warning.svg') ?>Je hebt onopgeslagen wijzigingen</p>
    </div>
    <div class="profile-editor-actions">
      <button type="button" class="button is-secondary" data-expand-all><?= admin_asset_icon('icons/navigation/all-open.svg') ?>Alles uitklappen</button>
      <button type="button" class="button is-secondary" data-collapse-all><?= admin_asset_icon('icons/navigation/all-closed.svg') ?>Alles inklappen</button>
      <?php if (admin_can_edit_profiles()): ?>
        <button type="submit" class="admin-primary-action"><?= admin_asset_icon('icons/actions/save.svg') ?>Opslaan</button>
      <?php endif; ?>
    </div>
  </section>

  <section class="panel profile-editor-toolbar">
    <div class="profile-switcher" aria-label="Profieltype">
      <a class="button<?= $audience === 'youth' ? ' is-active' : '' ?>" href="<?= h($baseProfileUrl . 'youth&language=' . rawurlencode($selectedLanguage)) ?>" data-language-link><?= admin_asset_icon('icons/content/youth-profile.svg') ?>Jongerenprofiel</a>
      <a class="button<?= $audience === 'professional' ? ' is-active' : '' ?>" href="<?= h($baseProfileUrl . 'professional&language=' . rawurlencode($selectedLanguage)) ?>" data-language-link><?= admin_asset_icon('icons/content/professional-profile.svg') ?>Professionalsprofiel</a>
    </div>
    <div class="admin-language-switch" aria-label="Taal kiezen">
      <?php foreach (PROFILE_LANGUAGES as $language): ?>
        <?php
        $languageSummary = $languageSummaries[$language];
        $languageStateClass = $languageSummary['empty'] === $languageSummary['total']
            ? 'status-empty'
            : ($languageSummary['review'] > 0 ? 'status-review' : 'status-filled');
        ?>
        <a
          class="language-button <?= h($languageStateClass) ?><?= $language === $selectedLanguage ? ' is-active' : '' ?>"
          href="organization_profile_edit.php?id=<?= h((string)$id) ?>&amp;audience=<?= h($audience) ?>&amp;language=<?= h($language) ?>"
          data-language-link
        >
          <span class="language-button-top">
            <?= admin_language_flag($language) ?>
            <strong><?= h(strtoupper($language)) ?></strong>
          </span>
          <span class="language-button-count"><?= h((string)$languageSummary['filled']) ?>/<?= h((string)$languageSummary['total']) ?> gevuld</span>
          <small><?= h((string)$languageSummary['review']) ?> review / <?= h((string)$languageSummary['fallback']) ?> fallback / <?= h((string)$languageSummary['empty']) ?> leeg</small>
        </a>
      <?php endforeach; ?>
    </div>
    <p class="form-help">Er wordt maar een taal tegelijk getoond. Wisselen met onopgeslagen wijzigingen vraagt eerst bevestiging.</p>
    <?php if ($publicUrl): ?>
      <a class="button" href="<?= h($publicUrl) ?>"><?= admin_asset_icon('icons/navigation/open-public-page.svg') ?>Bekijk publieke pagina</a>
    <?php endif; ?>
  </section>

  <section class="admin-progress-card">
    <div>
      <p class="eyebrow">Voortgang <?= h(strtoupper($selectedLanguage)) ?></p>
      <h2><?= h((string)$selectedSummary['total']) ?> vragen totaal</h2>
      <div class="progress-metrics" aria-label="Voortgang per status">
        <span class="admin-status-pill status-filled"><?= admin_status_icon('status-filled') ?><?= h((string)$selectedSummary['filled']) ?> gevuld</span>
        <span class="admin-status-pill status-review"><?= admin_status_icon('status-review') ?><?= h((string)$selectedSummary['review']) ?> review nodig</span>
        <span class="admin-status-pill status-fallback"><?= admin_status_icon('status-fallback') ?><?= h((string)$selectedSummary['fallback']) ?> fallback</span>
        <span class="admin-status-pill status-empty"><?= admin_status_icon('status-empty') ?><?= h((string)$selectedSummary['empty']) ?> leeg</span>
      </div>
    </div>
    <div class="profile-filter-bar" aria-label="Velden filteren">
      <button type="button" class="button is-active" data-profile-filter="all">Alles</button>
      <button type="button" class="button" data-profile-filter="empty">Alleen leeg</button>
      <button type="button" class="button" data-profile-filter="review">Alleen review</button>
      <button type="button" class="button" data-profile-filter="fallback">Alleen fallback</button>
      <button type="button" class="button" data-profile-filter="filled">Alleen ingevuld</button>
    </div>
  </section>

  <section class="profile-group profile-intro-group">
    <?php
    $introSourceText = (string)($introValues[$sourceLanguage] ?? '');
    $introCell = [
        'answer_text' => (string)($introValues[$selectedLanguage] ?? ''),
        'translation_status' => trim((string)($introValues[$selectedLanguage] ?? '')) === '' ? 'missing' : 'draft',
    ];
    $introState = profile_editor_state($introCell, $selectedLanguage, $sourceLanguage, $introSourceText);
    $canEditSelectedLanguage = admin_can_edit_profile_language($selectedLanguage);
    ?>
    <details class="profile-editor-panel <?= h($introState['class']) ?>" data-profile-status="<?= h($introState['filter']) ?>">
      <summary>
        <span class="admin-section-marker" aria-hidden="true">i</span>
        <span>
          <strong><?= h($introDefinition['label']) ?></strong>
          <small><?= admin_asset_icon('icons/content/source-text.svg') ?><?= h($introDefinition['help']) ?></small>
        </span>
        <?= profile_editor_badge($introState) ?>
        <span class="admin-chevron" aria-hidden="true"></span>
      </summary>
      <div class="profile-editor-panel-body">
        <?php if (!$canEditSelectedLanguage): ?><p class="readonly-note">Alleen-lezen voor jouw rol.</p><?php endif; ?>
        <?php if ($selectedLanguage !== $sourceLanguage && trim($introSourceText) !== ''): ?>
          <details class="source-preview">
            <summary>Brontekst <?= h(strtoupper($sourceLanguage)) ?></summary>
            <div><?= nl2br(h($introSourceText)) ?></div>
          </details>
        <?php endif; ?>
        <label>
          Tekst <?= h(strtoupper($selectedLanguage)) ?>
          <textarea
            name="intro[<?= h($selectedLanguage) ?>]"
            rows="5"
            <?= $canEditSelectedLanguage ? 'data-richtext-editor' : '' ?>
            <?= $canEditSelectedLanguage ? '' : 'disabled' ?>
          ><?= h((string)($introValues[$selectedLanguage] ?? '')) ?></textarea>
        </label>
        <div class="field-meta">
          <span><?= admin_asset_icon('icons/content/translation.svg') ?>Brontaal: <?= h(strtoupper($sourceLanguage)) ?></span>
          <span><?= admin_status_icon((string)$introState['class']) ?>Reviewstatus: <?= h($introState['label']) ?></span>
          <span data-field-save-state>Opgeslagen</span>
        </div>
      </div>
    </details>
  </section>

  <?php $fieldIndex = 0; ?>
  <?php foreach ($definition as $groupKey => $group): ?>
    <section class="profile-group">
      <div class="section-heading"><div><p class="eyebrow"><?= h($profileLabel) ?></p><h2><?= h((string)$group['label']) ?></h2></div></div>

      <?php foreach ($group['fields'] as $fieldKey => $fieldLabel): ?>
        <?php
        $fieldIndex++;
        $cell = $values[$groupKey][$fieldKey][$selectedLanguage];
        $sourceCell = $values[$groupKey][$fieldKey][$sourceLanguage] ?? ['answer_text' => '', 'translation_status' => 'missing'];
        $sourceText = (string)($sourceCell['answer_text'] ?? '');
        $state = profile_editor_state($cell, $selectedLanguage, $sourceLanguage, $sourceText);
        $formGroupKey = $groupKey === '' ? '_root' : $groupKey;
        $inputBase = 'answers[' . $formGroupKey . '][' . $fieldKey . '][' . $selectedLanguage . ']';
        ?>
        <details class="profile-editor-panel <?= h($state['class']) ?>" data-profile-status="<?= h($state['filter']) ?>">
          <summary>
            <span class="admin-section-marker" aria-hidden="true"><?= h((string)$fieldIndex) ?></span>
            <span>
              <strong><?= h((string)$fieldLabel) ?></strong>
              <small><?= admin_asset_icon('icons/content/question.svg') ?><code><?= h((string)$fieldKey) ?></code></small>
            </span>
            <?= profile_editor_badge($state) ?>
            <span class="admin-chevron" aria-hidden="true"></span>
          </summary>
          <div class="profile-editor-panel-body">
            <?php if (!$canEditSelectedLanguage): ?><p class="readonly-note">Alleen-lezen voor jouw rol.</p><?php endif; ?>
            <?php if ($selectedLanguage === 'pap'): ?>
              <p class="notice notice-review"><?= admin_asset_icon('icons/status/review-needed.svg') ?>Papiamentu concepttekst - bedoeld voor review. Lange detailteksten worden pas publiek getoond na redactionele goedkeuring.</p>
            <?php endif; ?>
            <?php if ($selectedLanguage !== $sourceLanguage && trim($sourceText) !== ''): ?>
              <details class="source-preview">
                <summary>Brontekst <?= h(strtoupper($sourceLanguage)) ?></summary>
                <div><?= nl2br(h($sourceText)) ?></div>
              </details>
            <?php endif; ?>
            <label>
              Antwoord <?= h(strtoupper($selectedLanguage)) ?>
              <textarea
                name="<?= h($inputBase) ?>[answer_text]"
                rows="9"
                <?= $canEditSelectedLanguage ? 'data-richtext-editor' : '' ?>
                <?= $canEditSelectedLanguage ? '' : 'disabled' ?>
              ><?= h((string)$cell['answer_text']) ?></textarea>
            </label>
            <label>
              Vertaalstatus
              <select name="<?= h($inputBase) ?>[translation_status]" <?= $canEditSelectedLanguage ? '' : 'disabled' ?>>
                <?php foreach (PROFILE_TRANSLATION_STATUSES as $status): ?>
                  <option value="<?= h($status) ?>" <?= $cell['translation_status'] === $status ? 'selected' : '' ?>><?= h($status) ?></option>
                <?php endforeach; ?>
              </select>
            </label>
            <div class="field-meta">
              <span><?= admin_asset_icon('icons/content/translation.svg') ?>Brontaal: <?= h(strtoupper($sourceLanguage)) ?></span>
              <span><?= admin_asset_icon('icons/content/review.svg') ?>Vertaalstatus: <?= h((string)$cell['translation_status']) ?></span>
              <span><?= admin_status_icon((string)$state['class']) ?>Reviewstatus: <?= h($state['label']) ?></span>
              <span data-field-save-state>Opgeslagen</span>
            </div>
          </div>
        </details>
      <?php endforeach; ?>
    </section>
  <?php endforeach; ?>

  <div class="form-actions sticky-actions">
    <?php if (admin_can_edit_profiles()): ?>
      <button type="submit">Opslaan</button>
    <?php endif; ?>
    <a class="button" href="organization.php?id=<?= h((string)$id) ?>">Annuleren</a>
  </div>
</form>

<script>
(() => {
  const form = document.querySelector('[data-profile-editor-form]');
  if (!form) return;
  let dirty = false;
  const saveState = document.querySelector('[data-save-state]');
  const unsavedNotice = document.querySelector('[data-unsaved-notice]');
  const markDirty = () => {
    dirty = true;
    if (unsavedNotice) unsavedNotice.hidden = false;
    if (saveState) {
      saveState.textContent = 'Wijzigingen nog niet opgeslagen';
      saveState.classList.remove('is-saved');
      saveState.classList.add('is-dirty');
    }
    form.querySelectorAll('[data-field-save-state]').forEach(item => {
      item.textContent = 'Niet opgeslagen';
      item.classList.add('is-dirty');
    });
  };
  form.querySelectorAll('textarea, select').forEach(input => {
    input.addEventListener('input', markDirty);
    input.addEventListener('change', markDirty);
  });
  form.addEventListener('submit', () => { dirty = false; });
  document.querySelectorAll('[data-language-link]').forEach(link => {
    link.addEventListener('click', event => {
      if (!dirty) return;
      if (!window.confirm('Je hebt onopgeslagen wijzigingen. Weet je zeker dat je wilt wisselen zonder eerst op te slaan?')) {
        event.preventDefault();
      }
    });
  });
  document.querySelector('[data-expand-all]')?.addEventListener('click', () => {
    form.querySelectorAll('details.profile-editor-panel').forEach(panel => { panel.open = true; });
  });
  document.querySelector('[data-collapse-all]')?.addEventListener('click', () => {
    form.querySelectorAll('details.profile-editor-panel').forEach(panel => { panel.open = false; });
  });
  document.querySelectorAll('[data-profile-filter]').forEach(button => {
    button.addEventListener('click', () => {
      const filter = button.dataset.profileFilter || 'all';
      document.querySelectorAll('[data-profile-filter]').forEach(item => item.classList.toggle('is-active', item === button));
      form.querySelectorAll('[data-profile-status]').forEach(panel => {
        panel.hidden = filter !== 'all' && panel.dataset.profileStatus !== filter;
      });
    });
  });
})();
</script>

<?php admin_footer(); ?>
