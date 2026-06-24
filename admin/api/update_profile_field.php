<?php

declare(strict_types=1);

ini_set('display_errors', '0');

require __DIR__ . '/../auth.php';

const INLINE_PROFILE_LANGUAGES = ['nl', 'pap', 'en', 'es'];
const INLINE_TRANSLATION_FIELDS = [
    'youth' => ['youth_short'],
    'professional' => ['professional_summary'],
];
const INLINE_YOUTH_FIELDS = [
    'who_we_are',
    'who_for',
    'what_help',
    'how_to_access',
    'how_we_help',
    'duration',
    'partners',
    'contact',
];
const INLINE_PROFESSIONAL_FIELDS = [
    'general' => [
        'short_description',
        'target_group',
        'support_offer',
        'services',
        'methods',
        'execution',
        'problems',
        'trajectory',
        'average_duration',
    ],
    'referral' => [
        'when_appropriate',
        'criteria',
        'indications_required',
    ],
    'practical' => [
        'contact_details',
        'opening_hours',
        'waiting_times',
    ],
    'additional' => [
        'partners',
        'other_information',
    ],
];
const INLINE_PROFILE_MAX_LENGTH = 100000;

function inline_json(array $payload, int $status = 200): void
{
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
    header('Pragma: no-cache');
    header('X-Content-Type-Options: nosniff');

    $json = json_encode(
        $payload,
        JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_SUBSTITUTE
    );
    echo $json !== false ? $json : '{"ok":false,"error":"server_error"}';
    exit;
}

function inline_profile_definition(string $audience, string $group, string $field): ?array
{
    if (
        $group === '__translation'
        && isset(INLINE_TRANSLATION_FIELDS[$audience])
        && in_array($field, INLINE_TRANSLATION_FIELDS[$audience], true)
    ) {
        return [
            'kind' => 'translation',
            'sort_order' => 0,
        ];
    }

    if ($audience === 'youth') {
        if ($group !== '' || !in_array($field, INLINE_YOUTH_FIELDS, true)) {
            return null;
        }

        return [
            'kind' => 'profile',
            'sort_order' => array_search($field, INLINE_YOUTH_FIELDS, true) + 1,
        ];
    }

    if (
        $audience !== 'professional'
        || !isset(INLINE_PROFESSIONAL_FIELDS[$group])
        || !in_array($field, INLINE_PROFESSIONAL_FIELDS[$group], true)
    ) {
        return null;
    }

    // organisation_name is position 1 in the full admin definition, but is not inline editable.
    $sortOrder = 1;
    foreach (INLINE_PROFESSIONAL_FIELDS as $definitionGroup => $groupFields) {
        foreach ($groupFields as $groupField) {
            $sortOrder++;
            if ($group === $definitionGroup && $field === $groupField) {
                return ['kind' => 'profile', 'sort_order' => $sortOrder];
            }
        }
    }

    return null;
}

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
    header('Allow: POST');
    inline_json(['ok' => false, 'error' => 'method_not_allowed'], 405);
}

if (!admin_is_logged_in()) {
    inline_json(['ok' => false, 'error' => 'session_expired'], 401);
}

try {
    $rawBody = file_get_contents('php://input');
    $payload = json_decode(is_string($rawBody) ? $rawBody : '', true);
    if (!is_array($payload)) {
        inline_json(['ok' => false, 'error' => 'invalid_request'], 400);
    }

    $csrfCandidate = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? ($payload['csrf_token'] ?? '');
    $csrfToken = is_scalar($csrfCandidate) ? (string)$csrfCandidate : '';
    if (!verify_csrf($csrfToken)) {
        inline_json(['ok' => false, 'error' => 'csrf_failed'], 403);
    }
    if (!admin_can_edit_profiles()) {
        inline_json(['ok' => false, 'error' => 'forbidden'], 403);
    }

    $slug = trim((string)($payload['organization_slug'] ?? ''));
    $audience = trim((string)($payload['audience'] ?? ''));
    $group = trim((string)($payload['group'] ?? ''));
    $field = trim((string)($payload['field'] ?? ''));
    $language = trim((string)($payload['language'] ?? ''));
    if (!array_key_exists('answer_text', $payload) || !is_scalar($payload['answer_text'])) {
        inline_json(['ok' => false, 'error' => 'invalid_answer_text'], 400);
    }
    $answerText = str_replace(["\r\n", "\r"], "\n", (string)$payload['answer_text']);

    if (
        $slug === ''
        || strlen($slug) > 180
        || !preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/', $slug)
    ) {
        inline_json(['ok' => false, 'error' => 'invalid_organization'], 400);
    }
    $definition = inline_profile_definition($audience, $group, $field);
    if ($definition === null) {
        inline_json(['ok' => false, 'error' => 'invalid_profile_field'], 400);
    }
    if (
        !in_array($language, INLINE_PROFILE_LANGUAGES, true)
        || !admin_can_edit_profile_language($language)
    ) {
        inline_json(['ok' => false, 'error' => 'language_forbidden'], 403);
    }
    $textLength = function_exists('mb_strlen') ? mb_strlen($answerText, 'UTF-8') : strlen($answerText);
    if ($textLength > INLINE_PROFILE_MAX_LENGTH) {
        inline_json(['ok' => false, 'error' => 'text_too_long'], 422);
    }

    $organization = fetch_one(
        'SELECT id FROM organizations WHERE slug = :slug LIMIT 1',
        ['slug' => $slug]
    );
    if (!$organization) {
        inline_json(['ok' => false, 'error' => 'organization_not_found'], 404);
    }
    $organizationId = (int)$organization['id'];
    if (($definition['kind'] ?? 'profile') === 'translation') {
        $fieldSql = $field;
        $existing = fetch_one(
            "SELECT {$fieldSql} AS answer_text
            FROM organization_translations
            WHERE organization_id = :organization_id
              AND language_code = :language_code
            LIMIT 1",
            [
                'organization_id' => $organizationId,
                'language_code' => $language,
            ]
        );
        $beforeText = (string)($existing['answer_text'] ?? '');
        if (!audit_values_differ($beforeText, $answerText)) {
            inline_json([
                'ok' => true,
                'changed' => false,
                'answer_text' => $beforeText,
                'updated_at' => gmdate('c'),
            ]);
        }

        $pdo = admin_db();
        $pdo->beginTransaction();
        $upsert = $pdo->prepare(
            "INSERT INTO organization_translations (
                organization_id,
                language_code,
                {$fieldSql},
                translation_status
            )
            VALUES (
                :organization_id,
                :language_code,
                :answer_text,
                'draft'
            )
            ON DUPLICATE KEY UPDATE
                {$fieldSql} = VALUES({$fieldSql})"
        );
        $upsert->execute([
            'organization_id' => $organizationId,
            'language_code' => $language,
            'answer_text' => $answerText,
        ]);

        write_audit_log(
            'organization.update_translation_intro',
            'organization',
            $organizationId,
            [
                'audience' => $audience,
                'translations' => [
                    $field . '.' . $language => $beforeText,
                ],
            ],
            [
                'audience' => $audience,
                'translations' => [
                    $field . '.' . $language => $answerText,
                ],
            ]
        );
        $pdo->commit();

        inline_json([
            'ok' => true,
            'changed' => true,
            'answer_text' => $answerText,
            'updated_at' => gmdate('c'),
        ]);
    }

    $existing = fetch_one(
        "SELECT answer_text, translation_status
        FROM organization_profile_answers
        WHERE organization_id = :organization_id
          AND audience_code = :audience
          AND group_key = :group_key
          AND field_key = :field_key
          AND language_code = :language_code
        LIMIT 1",
        [
            'organization_id' => $organizationId,
            'audience' => $audience,
            'group_key' => $group,
            'field_key' => $field,
            'language_code' => $language,
        ]
    );
    $beforeText = (string)($existing['answer_text'] ?? '');
    if (!audit_values_differ($beforeText, $answerText)) {
        inline_json([
            'ok' => true,
            'changed' => false,
            'answer_text' => $beforeText,
            'updated_at' => gmdate('c'),
        ]);
    }

    $translationStatus = (string)($existing['translation_status'] ?? 'draft');
    if (!in_array($translationStatus, ['missing', 'draft', 'reviewed', 'published'], true)) {
        $translationStatus = 'draft';
    }

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
    $upsert->execute([
        'organization_id' => $organizationId,
        'audience_code' => $audience,
        'group_key' => $group,
        'field_key' => $field,
        'language_code' => $language,
        'answer_text' => $answerText,
        'translation_status' => $translationStatus,
        'sort_order' => (int)$definition['sort_order'],
    ]);

    $auditKey = ($group !== '' ? $group . '.' : '') . $field . '.' . $language;
    write_audit_log(
        'organization.update_profile',
        'organization',
        $organizationId,
        [
            'audience' => $audience,
            'answers' => [
                $auditKey => ['answer_text' => $beforeText],
            ],
        ],
        [
            'audience' => $audience,
            'answers' => [
                $auditKey => ['answer_text' => $answerText],
            ],
        ]
    );
    $pdo->commit();

    inline_json([
        'ok' => true,
        'changed' => true,
        'answer_text' => $answerText,
        'updated_at' => gmdate('c'),
    ]);
} catch (Throwable) {
    if (isset($pdo) && $pdo instanceof PDO && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    inline_json(['ok' => false, 'error' => 'server_error'], 500);
}
