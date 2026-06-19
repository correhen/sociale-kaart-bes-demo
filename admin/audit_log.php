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

function audit_field_label(string $field): string
{
    $labels = [
        'name' => 'naam',
        'professional_summary' => 'korte omschrijving',
        'type_label' => 'type-label',
        'age_range' => 'leeftijd',
        'professional_referral_or_access' => 'toegang/verwijzing',
        'status' => 'status',
        'source_status' => 'bronstatus',
        'last_checked_at' => 'laatst gecontroleerd',
        'phone' => 'telefoon',
        'whatsapp' => 'WhatsApp',
        'email' => 'e-mail',
        'website' => 'website',
        'address_nl' => 'adres',
    ];

    return $labels[$field] ?? str_replace('_', ' ', $field);
}

function audit_summary(?string $beforeJson, ?string $afterJson): string
{
    $before = audit_values($beforeJson);
    $after = audit_values($afterJson);
    $fields = array_values(array_unique(array_merge(array_keys($before), array_keys($after))));

    if (!$fields) {
        return 'Geen veldwijzigingen vastgelegd';
    }

    return implode(', ', array_map('audit_field_label', $fields));
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
        ORDER BY a.created_at DESC, a.id DESC
        LIMIT 250"
    );
} catch (Throwable) {
    $error = 'Het auditlog kon niet worden geladen. Probeer het later opnieuw.';
}

admin_header('Auditlog', 'audit_log');
?>
<section class="panel">
  <p class="muted">Read-only overzicht voor admin, editor en viewer. De nieuwste 250 registraties worden getoond.</p>
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
            <th>Entiteit</th>
            <th>Gewijzigde velden</th>
            <th>IP-adres</th>
          </tr>
        </thead>
        <tbody>
          <?php if (!$entries): ?>
            <tr><td colspan="6" class="muted">Er zijn nog geen auditregistraties.</td></tr>
          <?php endif; ?>
          <?php foreach ($entries as $entry): ?>
            <tr>
              <td><?= readable_datetime($entry['created_at']) ?></td>
              <td>
                <?= h((string)($entry['user_name'] ?? 'Onbekende gebruiker')) ?>
                <?php if (!empty($entry['user_email'])): ?>
                  <br><small class="muted"><?= h((string)$entry['user_email']) ?></small>
                <?php endif; ?>
              </td>
              <td><code><?= h((string)$entry['action']) ?></code></td>
              <td>
                <?= h((string)$entry['entity_type']) ?>
                <?php if ($entry['entity_id'] !== null): ?>
                  #<?= h((string)$entry['entity_id']) ?>
                <?php endif; ?>
                <?php if ($entry['entity_type'] === 'organization' && $entry['entity_id'] !== null): ?>
                  <br><a href="organization.php?id=<?= h((string)$entry['entity_id']) ?>"><?= h((string)($entry['entity_name'] ?? 'Organisatie openen')) ?></a>
                <?php elseif (!empty($entry['entity_name'])): ?>
                  <br><?= h((string)$entry['entity_name']) ?>
                <?php endif; ?>
              </td>
              <td><?= h(audit_summary($entry['before_json'], $entry['after_json'])) ?></td>
              <td><code><?= h((string)$entry['ip_address']) ?></code></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </section>
<?php endif; ?>

<?php admin_footer(); ?>
