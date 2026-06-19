<?php

declare(strict_types=1);

ini_set('display_errors', '0');

function admin_context_response(array $payload, int $status = 200): void
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
    echo $json !== false ? $json : '{"authenticated":false}';
    exit;
}

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'GET') {
    header('Allow: GET');
    admin_context_response(['authenticated' => false], 405);
}

if (!isset($_COOKIE['kadena_admin']) || (string)$_COOKIE['kadena_admin'] === '') {
    admin_context_response(['authenticated' => false]);
}

require __DIR__ . '/../admin/auth.php';

try {
    $user = current_admin_user();
    if (!$user) {
        admin_context_response(['authenticated' => false]);
    }

    $roles = array_values(array_intersect(
        is_array($user['roles'] ?? null) ? $user['roles'] : [],
        ADMIN_ALLOWED_ROLES
    ));
    $primaryRole = $roles[0] ?? 'viewer';

    admin_context_response([
        'authenticated' => true,
        'user' => [
            'name' => (string)($user['name'] ?? ''),
            'role' => $primaryRole,
        ],
        'permissions' => [
            'can_view_admin' => true,
            'can_edit_basic' => admin_can_edit_organizations(),
            'can_edit_youth_profile' => admin_can_edit_profiles(),
            'can_edit_professional_profile' => admin_can_edit_profiles(),
            'can_manage_users' => admin_can_manage_users(),
        ],
    ]);
} catch (Throwable) {
    admin_context_response(['authenticated' => false]);
}
