<?php

declare(strict_types=1);

require __DIR__ . '/bootstrap.php';

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'GET') {
    header('Allow: GET');
    api_json_response(['error' => 'Alleen GET is toegestaan.'], 405);
}

function public_themes(): array
{
    $themes = [];
    $rows = api_fetch_all(
        "SELECT
            t.id AS database_id,
            t.external_key,
            t.slug,
            t.color,
            t.sort_order,
            tt.language_code,
            tt.name,
            tt.short,
            tt.translation_status
        FROM themes t
        LEFT JOIN theme_translations tt ON tt.theme_id = t.id
        WHERE t.status = 'published'
        ORDER BY t.sort_order ASC, t.id ASC, tt.language_code ASC"
    );

    foreach ($rows as $row) {
        $databaseId = (int)$row['database_id'];
        if (!isset($themes[$databaseId])) {
            $themes[$databaseId] = [
                'id' => (string)$row['external_key'],
                'slug' => (string)$row['slug'],
                'order' => (int)$row['sort_order'],
                'color' => (string)($row['color'] ?? ''),
                'labels' => [
                    'youth' => [],
                    'professional' => [],
                ],
                'translations' => [],
            ];
        }

        $language = (string)($row['language_code'] ?? '');
        if (!in_array($language, API_LANGUAGES, true)) {
            continue;
        }

        $name = (string)($row['name'] ?? '');
        $short = (string)($row['short'] ?? '');
        $themes[$databaseId]['translations'][$language] = [
            'name' => $name,
            'short' => $short,
            'translation_status' => (string)($row['translation_status'] ?? 'missing'),
        ];
        if ($name !== '') {
            $themes[$databaseId]['labels']['youth'][$language] = $name;
        }
        if ($short !== '') {
            $themes[$databaseId]['labels']['professional'][$language] = $short;
        }
    }

    return array_values($themes);
}

function public_organizations(string $island): array
{
    $organizations = [];
    $rows = api_fetch_all(
        "SELECT
            o.id AS database_id,
            o.external_key,
            o.slug,
            o.source_status,
            o.entity_type,
            o.last_checked_at,
            o.review_flags_json,
            o.source_format_json,
            o.legacy_sections_json,
            i.code AS island_code
        FROM organizations o
        INNER JOIN organization_islands oi ON oi.organization_id = o.id
        INNER JOIN islands i ON i.id = oi.island_id
        WHERE o.status = 'published'
          AND o.visibility_public = 1
          AND i.code = :island
        ORDER BY o.id ASC",
        ['island' => $island]
    );

    foreach ($rows as $row) {
        $legacySections = api_json_value($row['legacy_sections_json'], []);
        $organizations[(int)$row['database_id']] = [
            'id' => (string)$row['external_key'],
            'name' => (string)$row['slug'],
            'slug' => (string)$row['slug'],
            'island' => (string)$row['island_code'],
            'status' => 'active',
            'visibility' => ['default' => true],
            'target_audiences' => [],
            'primary_theme_ids' => [],
            'secondary_theme_ids' => [],
            'search_keywords_nl' => [],
            'contact' => [],
            'translations' => [],
            'review_flags' => api_json_value($row['review_flags_json'], []),
            'source_format' => api_json_value($row['source_format_json'], []),
            'entity_type' => (string)$row['entity_type'],
            'type' => (string)$row['entity_type'],
            'source_status' => (string)$row['source_status'],
            'last_checked_at' => $row['last_checked_at'] === null ? '' : (string)$row['last_checked_at'],
            'service_labels' => [],
            'youth_sections' => is_array($legacySections['youth_sections'] ?? null)
                ? $legacySections['youth_sections']
                : [],
            'professional_sections' => is_array($legacySections['professional_sections'] ?? null)
                ? $legacySections['professional_sections']
                : [],
            'youth_profile' => [],
            'youth_profile_status' => [],
            'professional_profile' => [],
            'professional_profile_status' => [],
            'translation_status' => [],
        ];
    }

    if (!$organizations) {
        return [];
    }

    $organizationIds = array_keys($organizations);
    $placeholders = implode(',', array_fill(0, count($organizationIds), '?'));

    $translationRows = api_fetch_all(
        "SELECT
            organization_id,
            language_code,
            name,
            youth_title,
            youth_short,
            youth_where_can_you_go,
            youth_how_it_works,
            professional_summary,
            professional_referral_or_access,
            professional_notes,
            type_label,
            age_range,
            translation_status
        FROM organization_translations
        WHERE organization_id IN ($placeholders)
        ORDER BY organization_id ASC, language_code ASC",
        $organizationIds
    );
    foreach ($translationRows as $row) {
        $organizationId = (int)$row['organization_id'];
        $language = (string)$row['language_code'];
        if (!isset($organizations[$organizationId]) || !in_array($language, API_LANGUAGES, true)) {
            continue;
        }

        $organizations[$organizationId]['translations'][$language] = [
            'name' => (string)$row['name'],
            'youth_title' => (string)$row['youth_title'],
            'youth_short' => (string)($row['youth_short'] ?? ''),
            'youth_where_can_you_go' => (string)($row['youth_where_can_you_go'] ?? ''),
            'youth_how_it_works' => (string)($row['youth_how_it_works'] ?? ''),
            'professional_summary' => (string)($row['professional_summary'] ?? ''),
            'professional_referral_or_access' => (string)($row['professional_referral_or_access'] ?? ''),
            'professional_notes' => (string)($row['professional_notes'] ?? ''),
            'type' => (string)$row['type_label'],
            'age_range' => (string)$row['age_range'],
            'translation_status' => (string)$row['translation_status'],
        ];
        $organizations[$organizationId]['translation_status'][$language] = (string)$row['translation_status'];
        if ($language === 'nl' && trim((string)$row['name']) !== '') {
            $organizations[$organizationId]['name'] = (string)$row['name'];
        }
    }

    $contactRows = api_fetch_all(
        "SELECT organization_id, phone, whatsapp, email, website, address_nl, contact_json
        FROM organization_contacts
        WHERE organization_id IN ($placeholders)",
        $organizationIds
    );
    foreach ($contactRows as $row) {
        $organizationId = (int)$row['organization_id'];
        if (!isset($organizations[$organizationId])) {
            continue;
        }

        $contact = api_json_value($row['contact_json'], []);
        $contact['phone'] = (string)$row['phone'];
        $contact['phone_whatsapp'] = (string)$row['whatsapp'];
        $contact['whatsapp'] = (string)$row['whatsapp'];
        $contact['email'] = (string)$row['email'];
        $contact['website'] = (string)$row['website'];
        $address = is_array($contact['address'] ?? null) ? $contact['address'] : [];
        $address['nl'] = (string)($row['address_nl'] ?? '');
        $contact['address'] = $address;
        $organizations[$organizationId]['contact'] = $contact;
    }

    $themeRows = api_fetch_all(
        "SELECT
            ot.organization_id,
            t.external_key,
            ot.is_primary
        FROM organization_theme ot
        INNER JOIN themes t ON t.id = ot.theme_id
        WHERE ot.organization_id IN ($placeholders)
          AND t.status = 'published'
        ORDER BY ot.organization_id ASC, ot.is_primary DESC, ot.sort_order ASC, t.id ASC",
        $organizationIds
    );
    foreach ($themeRows as $row) {
        $organizationId = (int)$row['organization_id'];
        if (!isset($organizations[$organizationId])) {
            continue;
        }
        $key = (int)$row['is_primary'] === 1 ? 'primary_theme_ids' : 'secondary_theme_ids';
        $organizations[$organizationId][$key][] = (string)$row['external_key'];
    }

    $audienceRows = api_fetch_all(
        "SELECT oa.organization_id, a.code
        FROM organization_audience oa
        INNER JOIN audiences a ON a.id = oa.audience_id
        WHERE oa.organization_id IN ($placeholders)
        ORDER BY oa.organization_id ASC, a.sort_order ASC, a.id ASC",
        $organizationIds
    );
    foreach ($audienceRows as $row) {
        $organizationId = (int)$row['organization_id'];
        if (isset($organizations[$organizationId])) {
            $organizations[$organizationId]['target_audiences'][] = (string)$row['code'];
        }
    }

    $keywordRows = api_fetch_all(
        "SELECT organization_id, keyword
        FROM organization_keywords
        WHERE organization_id IN ($placeholders)
          AND language_code = 'nl'
        ORDER BY organization_id ASC, keyword ASC",
        $organizationIds
    );
    foreach ($keywordRows as $row) {
        $organizationId = (int)$row['organization_id'];
        if (isset($organizations[$organizationId])) {
            $organizations[$organizationId]['search_keywords_nl'][] = (string)$row['keyword'];
        }
    }

    $profileRows = api_fetch_all(
        "SELECT
            organization_id,
            audience_code,
            group_key,
            field_key,
            language_code,
            answer_text,
            translation_status
        FROM organization_profile_answers
        WHERE organization_id IN ($placeholders)
        ORDER BY organization_id ASC, audience_code ASC, sort_order ASC, group_key ASC, field_key ASC, language_code ASC",
        $organizationIds
    );
    foreach ($profileRows as $row) {
        $organizationId = (int)$row['organization_id'];
        $language = (string)$row['language_code'];
        if (
            !isset($organizations[$organizationId])
            || !in_array($language, API_LANGUAGES, true)
        ) {
            continue;
        }

        $answer = (string)($row['answer_text'] ?? '');
        $status = (string)($row['translation_status'] ?? 'missing');
        $field = (string)$row['field_key'];
        if ((string)$row['audience_code'] === 'youth') {
            $organizations[$organizationId]['youth_profile'][$field][$language] = $answer;
            $organizations[$organizationId]['youth_profile_status'][$field][$language] = $status;
            continue;
        }
        if ((string)$row['audience_code'] === 'professional') {
            $group = (string)$row['group_key'];
            $organizations[$organizationId]['professional_profile'][$group][$field][$language] = $answer;
            $organizations[$organizationId]['professional_profile_status'][$group][$field][$language] = $status;
        }
    }

    return array_values($organizations);
}

try {
    $island = api_island(trim((string)($_GET['island'] ?? 'bonaire')));
    api_json_response([
        'metadata' => [
            'project' => 'Kadena Hubenil / Sociale Kaart BES',
            'version' => 'database-api-v1',
            'generated_at' => gmdate('c'),
            'default_island' => 'bonaire',
            'requested_island' => $island,
            'future_island_ready' => ['statia', 'saba'],
            'languages' => API_LANGUAGES,
            'primary_language' => 'nl',
            'source' => 'mysql',
        ],
        'audiences' => [
            ['id' => 'youth', 'label_nl' => 'Jongere'],
            ['id' => 'professional', 'label_nl' => 'Professional'],
            ['id' => 'parents', 'label_nl' => 'Ouder/verzorger'],
        ],
        'themes' => public_themes(),
        'organizations' => public_organizations($island),
    ]);
} catch (Throwable) {
    api_json_response([
        'error' => 'De publieke gegevens konden niet worden geladen.',
    ], 500);
}
