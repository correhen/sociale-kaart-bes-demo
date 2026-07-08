<?php

declare(strict_types=1);

require __DIR__ . '/layout.php';
require_admin_login();

$error = '';
$entries = [];

function audit_values(?string $json): array
{
    if ($json === null || trim($json) === '') {
        return [];
    }

    $decoded = json_decode($json, true);

    return is_array($decoded) ? $decoded : [];
}

function audit_action_label(string $action): string
{
    $labels = [
        'organization.create' => 'Organisatie aangemaakt',
        'organization.update_basic' => 'Basisgegevens gewijzigd',
        'organization.update_visibility' => 'Zichtbaarheid gewijzigd',
        'organization.update_audiences' => 'Doelgroepen gewijzigd',
        'organization.update_themes' => 'Thema\'s gewijzigd',
        'organization.update_profile' => 'Profiel gewijzigd',
        'organization.update_translation_intro' => 'Korte introtekst gewijzigd',
        'user.create' => 'Gebruiker aangemaakt',
        'user.update' => 'Gebruiker gewijzigd',
        'user.change_own_password' => 'Eigen wachtwoord gewijzigd',
        'user.admin_reset_password' => 'Wachtwoord door admin gereset',
    ];

    return $labels[$action] ?? $action;
}

function audit_profile_field_label(string $field): string
{
    $labels = [
        'who_we_are' => 'Wie zijn wij?',
        'who_for' => 'Voor wie zijn wij?',
        'what_help' => 'Waarmee kunnen wij helpen?',
        'how_to_access' => 'Hoe kom je bij deze organisatie terecht?',
        'how_we_help' => 'Hoe helpen wij?',
        'duration' => 'Hoelang duurt de hulp?',
        'partners' => 'Samenwerkingspartners',
        'contact' => 'Contact',
        'organisation_name' => 'Organisatienaam',
        'short_description' => 'Korte omschrijving',
        'target_group' => 'Doelgroep',
        'support_offer' => 'Hulpaanbod',
        'services' => 'Diensten',
        'methods' => 'Werkwijzen en methodieken',
        'execution' => 'Uitvoering',
        'problems' => 'Problematiek en hulpvragen',
        'trajectory' => 'Trajectverloop',
        'average_duration' => 'Gemiddelde duur',
        'when_appropriate' => 'Passende doorverwijzing',
        'criteria' => 'Verwijscriteria',
        'indications_required' => 'Indicaties vereist',
        'contact_details' => 'Contactgegevens',
        'opening_hours' => 'Openingstijden',
        'waiting_times' => 'Wachttijden',
        'other_information' => 'Overige informatie',
    ];

    return $labels[$field] ?? str_replace('_', ' ', $field);
}

function audit_field_label(string $field, string $action = ''): string
{
    $basicLabels = [
        'name.nl' => 'Organisatienaam (NL)',
        'professional_summary.nl' => 'Korte omschrijving (NL)',
        'type_label.nl' => 'Type-label (NL)',
        'age_range.nl' => 'Leeftijd (NL)',
        'professional_referral_or_access.nl' => 'Toegang/verwijzing (NL)',
        'contact.phone' => 'Telefoon',
        'contact.whatsapp' => 'WhatsApp',
        'contact.email' => 'E-mail',
        'contact.website' => 'Website',
        'contact.address_nl' => 'Adres (NL)',
        'status' => 'Publicatiestatus',
        'visibility_public' => 'Publieke zichtbaarheid',
        'source_status' => 'Bronstatus',
        'last_checked_at' => 'Laatst gecontroleerd',
        'audiences' => 'Doelgroepen',
        'themes' => 'Thema\'s',
        'name' => 'Organisatienaam',
        'professional_summary' => 'Korte omschrijving',
        'type_label' => 'Type-label',
        'age_range' => 'Leeftijd',
        'professional_referral_or_access' => 'Toegang/verwijzing',
        'phone' => 'Telefoon',
        'whatsapp' => 'WhatsApp',
        'email' => 'E-mail',
        'website' => 'Website',
        'address_nl' => 'Adres (NL)',
        'role' => 'Rol',
        'user_status' => 'Gebruikersstatus',
        'password' => 'Wachtwoord',
    ];
    if (isset($basicLabels[$field])) {
        return $basicLabels[$field];
    }

    $parts = explode('.', $field);
    $language = '';
    $lastPart = end($parts);
    if (in_array($lastPart, ['nl', 'pap', 'en', 'es'], true)) {
        $language = strtoupper((string)array_pop($parts));
    }

    if ($action === 'organization.update_profile' && $parts) {
        $fieldKey = (string)array_pop($parts);
        $prefix = $parts ? 'Professionalprofiel' : 'Jongerenprofiel';

        return $prefix
            . " \u{00B7} "
            . audit_profile_field_label($fieldKey)
            . ($language !== '' ? ' (' . $language . ')' : '');
    }

    if ($action === 'organization.update_translation_intro' && $parts) {
        $fieldKey = implode('.', $parts);
        $labels = [
            'youth_short' => 'Korte tekst jongerenpagina',
            'professional_summary' => 'Korte tekst professionalpagina',
        ];

        return ($labels[$fieldKey] ?? ucfirst(str_replace('_', ' ', $fieldKey)))
            . ($language !== '' ? ' (' . $language . ')' : '');
    }

    return ucfirst(str_replace('_', ' ', $field))
        . ($language !== '' ? ' (' . $language . ')' : '');
}

function audit_profile_cell($value): array
{
    if (!is_array($value)) {
        return [];
    }

    $cell = [];
    if (array_key_exists('answer_text', $value)) {
        $cell['answer_text'] = (string)$value['answer_text'];
    }
    if (array_key_exists('translation_status', $value)) {
        $cell['translation_status'] = (string)$value['translation_status'];
    }

    return $cell;
}

function audit_changes(array $before, array $after, string $action): array
{
    $changes = [];
    if ($action === 'organization.update_translation_intro' || isset($before['translations']) || isset($after['translations'])) {
        $beforeTranslations = is_array($before['translations'] ?? null) ? $before['translations'] : [];
        $afterTranslations = is_array($after['translations'] ?? null) ? $after['translations'] : [];
        $keys = array_values(array_unique(array_merge(array_keys($beforeTranslations), array_keys($afterTranslations))));
        foreach ($keys as $key) {
            $old = $beforeTranslations[$key] ?? null;
            $new = $afterTranslations[$key] ?? null;
            if (!audit_values_differ($old, $new)) {
                continue;
            }
            $changes[$key] = ['before' => $old, 'after' => $new];
        }

        return $changes;
    }

    if ($action === 'organization.update_profile' || isset($before['answers']) || isset($after['answers'])) {
        $beforeAnswers = is_array($before['answers'] ?? null) ? $before['answers'] : [];
        $afterAnswers = is_array($after['answers'] ?? null) ? $after['answers'] : [];
        $keys = array_values(array_unique(array_merge(array_keys($beforeAnswers), array_keys($afterAnswers))));
        foreach ($keys as $key) {
            $old = audit_profile_cell($beforeAnswers[$key] ?? null);
            $new = audit_profile_cell($afterAnswers[$key] ?? null);
            $components = array_values(array_unique(array_merge(array_keys($old), array_keys($new))));
            $differs = false;
            foreach ($components as $component) {
                if (audit_values_differ($old[$component] ?? null, $new[$component] ?? null)) {
                    $differs = true;
                    break;
                }
            }
            if (!$differs) {
                continue;
            }
            $changes[$key] = ['before' => $old, 'after' => $new];
        }

        return $changes;
    }

    $keys = array_values(array_unique(array_merge(array_keys($before), array_keys($after))));
    foreach ($keys as $key) {
        $old = $before[$key] ?? null;
        $new = $after[$key] ?? null;
        if (!audit_values_differ($old, $new)) {
            continue;
        }
        $changes[$key] = ['before' => $old, 'after' => $new];
    }

    return $changes;
}

function audit_display_value($value): string
{
    if (is_array($value) && array_key_exists('answer_text', $value)) {
        $text = trim((string)$value['answer_text']);
        $display = $text !== '' ? $text : 'Leeg';
        if (array_key_exists('translation_status', $value)) {
            $display .= "\nStatus: " . (string)$value['translation_status'];
        }

        return $display;
    }
    if (is_array($value) && array_key_exists('translation_status', $value)) {
        return 'Status: ' . (string)$value['translation_status'];
    }
    if (is_array($value) && array_key_exists('label', $value)) {
        $label = (string)$value['label'];
        if ((int)($value['is_primary'] ?? 0) === 1) {
            $label .= ' (primair)';
        }

        return $label !== '' ? $label : 'ID ' . (string)($value['id'] ?? '');
    }
    if (is_array($value)) {
        $items = [];
        foreach ($value as $item) {
            $items[] = audit_display_value($item);
        }

        $items = array_values(array_filter($items, static fn(string $item): bool => $item !== 'Leeg'));

        return $items ? implode("\n", $items) : 'Leeg';
    }

    $text = trim((string)($value ?? ''));

    return $text !== '' ? $text : 'Leeg';
}

function audit_status_transition(array $change): string
{
    $before = is_array($change['before'] ?? null) ? $change['before'] : [];
    $after = is_array($change['after'] ?? null) ? $change['after'] : [];
    if (
        count($before) !== 1
        || count($after) !== 1
        || !array_key_exists('translation_status', $before)
        || !array_key_exists('translation_status', $after)
    ) {
        return '';
    }

    return 'Vertaalstatus: '
        . (string)$before['translation_status']
        . ' -> '
        . (string)$after['translation_status'];
}

try {
    $entries = fetch_all(
        "SELECT
            a.id,
            a.action,
            a.entity_type,
            a.entity_id,
            a.before_json,
            a.after_json,
            a.ip_address,
            a.created_at,
            u.name AS user_name,
            u.email AS user_email,
            CASE
                WHEN a.entity_type = 'organization' THEN COALESCE(NULLIF(ot.name, ''), o.slug)
                WHEN a.entity_type = 'user' THEN target_user.name
                ELSE NULL
            END AS entity_name
        FROM audit_log a
        LEFT JOIN users u ON u.id = a.user_id
        LEFT JOIN organizations o
            ON a.entity_type = 'organization'
            AND o.id = a.entity_id
        LEFT JOIN organization_translations ot
            ON ot.organization_id = o.id
            AND ot.language_code = 'nl'
        LEFT JOIN users target_user
            ON a.entity_type = 'user'
            AND target_user.id = a.entity_id
        ORDER BY a.created_at DESC, a.id DESC
        LIMIT 250"
    );
} catch (Throwable) {
    $error = 'Het auditlog kon niet worden geladen. Probeer het later opnieuw.';
}

admin_header('Auditlog', 'audit_log');
?>
<section class="panel">
  <p class="muted">Read-only overzicht voor admin, editor, translator en viewer. Alleen inhoudelijk gewijzigde waarden worden getoond.</p>
</section>

<?php if ($error !== ''): ?>
  <p class="error"><?= h($error) ?></p>
<?php else: ?>
  <section class="panel">
    <div class="table-wrap">
      <table>
        <thead>
          <tr>
            <th>Datum/tijd</th>
            <th>Gebruiker</th>
            <th>Actie</th>
            <th>Organisatie/entiteit</th>
            <th>Wijzigingen</th>
            <th>IP-adres</th>
          </tr>
        </thead>
        <tbody>
          <?php if (!$entries): ?>
            <tr><td colspan="6" class="muted">Er zijn nog geen auditregistraties.</td></tr>
          <?php endif; ?>
          <?php foreach ($entries as $entry): ?>
            <?php
            $before = audit_values($entry['before_json']);
            $after = audit_values($entry['after_json']);
            $changes = audit_changes($before, $after, (string)$entry['action']);
            ?>
            <tr>
              <td><?= readable_datetime($entry['created_at']) ?></td>
              <td>
                <?= h((string)($entry['user_name'] ?? 'Onbekende gebruiker')) ?>
                <?php if (!empty($entry['user_email'])): ?>
                  <br><small class="muted"><?= h((string)$entry['user_email']) ?></small>
                <?php endif; ?>
              </td>
              <td><span class="badge badge-action"><?= h(audit_action_label((string)$entry['action'])) ?></span></td>
              <td>
                <?php if ($entry['entity_type'] === 'organization' && $entry['entity_id'] !== null): ?>
                  <a href="organization.php?id=<?= h((string)$entry['entity_id']) ?>"><?= h((string)($entry['entity_name'] ?? 'Organisatie')) ?></a>
                <?php elseif ($entry['entity_type'] === 'user' && $entry['entity_id'] !== null && admin_can_manage_users()): ?>
                  <a href="user_edit.php?id=<?= h((string)$entry['entity_id']) ?>"><?= h((string)($entry['entity_name'] ?? 'Gebruiker')) ?></a>
                <?php else: ?>
                  <?= h((string)($entry['entity_name'] ?? $entry['entity_type'])) ?>
                <?php endif; ?>
              </td>
              <td>
                <span class="badge badge-count"><?= h((string)count($changes)) ?> gewijzigd</span>
                <?php if ($changes): ?>
                  <ul class="audit-field-list">
                    <?php foreach (array_keys($changes) as $field): ?>
                      <li><?= h(audit_field_label((string)$field, (string)$entry['action'])) ?></li>
                    <?php endforeach; ?>
                  </ul>
                  <details class="audit-details">
                    <summary>Bekijk wijziging</summary>
                    <?php foreach ($changes as $field => $change): ?>
                      <section class="audit-change">
                        <h4><?= h(audit_field_label((string)$field, (string)$entry['action'])) ?></h4>
                        <?php $statusTransition = audit_status_transition($change); ?>
                        <?php if ($statusTransition !== ''): ?>
                          <p><?= h($statusTransition) ?></p>
                        <?php else: ?>
                          <div class="audit-diff">
                            <div><strong>Oud</strong><pre><?= h(audit_display_value($change['before'])) ?></pre></div>
                            <div><strong>Nieuw</strong><pre><?= h(audit_display_value($change['after'])) ?></pre></div>
                          </div>
                        <?php endif; ?>
                      </section>
                    <?php endforeach; ?>
                  </details>
                <?php endif; ?>
              </td>
              <td><code><?= h((string)$entry['ip_address']) ?></code></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </section>
<?php endif; ?>

<?php admin_footer(); ?>
