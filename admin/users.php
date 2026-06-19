<?php

declare(strict_types=1);

require __DIR__ . '/layout.php';
require_admin_login();

if (!admin_can_manage_users()) {
    http_response_code(403);
    admin_header('Geen toegang', '');
    ?>
    <p class="error">Alleen een admin mag gebruikers beheren.</p>
    <?php
    admin_footer();
    exit;
}

$error = '';
$users = [];

try {
    $users = fetch_all(
        "SELECT
            u.id,
            u.name,
            u.email,
            u.status,
            u.last_login_at,
            u.created_at,
            GROUP_CONCAT(r.code ORDER BY r.code SEPARATOR ', ') AS roles
        FROM users u
        LEFT JOIN user_roles ur ON ur.user_id = u.id
        LEFT JOIN roles r ON r.id = ur.role_id
        GROUP BY u.id, u.name, u.email, u.status, u.last_login_at, u.created_at
        ORDER BY u.name ASC, u.email ASC"
    );
} catch (Throwable) {
    $error = 'De gebruikerslijst kon niet worden geladen. Probeer het later opnieuw.';
}

admin_header('Gebruikers', 'users');
?>
<section class="page-intro compact">
  <div><p class="eyebrow">Toegangsbeheer</p><h2>Adminaccounts en rollen</h2><p>Beheer toegang voor admins, editors, vertalers en lezers.</p></div>
  <a class="button primary" href="user_edit.php">Gebruiker aanmaken</a>
</section>

<?php if ($error !== ''): ?>
  <p class="error"><?= h($error) ?></p>
<?php else: ?>
  <section class="panel">
    <div class="table-wrap">
      <table>
        <thead>
          <tr>
            <th>Naam</th>
            <th>E-mail</th>
            <th>Rol</th>
            <th>Status</th>
            <th>Laatste login</th>
            <th>Aangemaakt</th>
          </tr>
        </thead>
        <tbody>
          <?php if (!$users): ?>
            <tr><td colspan="6" class="muted">Geen gebruikers gevonden.</td></tr>
          <?php endif; ?>
          <?php foreach ($users as $user): ?>
            <tr>
              <td><a href="user_edit.php?id=<?= h((string)$user['id']) ?>"><?= h((string)$user['name']) ?></a></td>
              <td><?= h((string)$user['email']) ?></td>
              <td><span class="badge badge-role"><?= h((string)($user['roles'] ?: 'geen rol')) ?></span></td>
              <td><?= status_badge((string)$user['status']) ?></td>
              <td><?= readable_datetime($user['last_login_at']) ?></td>
              <td><?= readable_datetime($user['created_at']) ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </section>
<?php endif; ?>

<?php admin_footer(); ?>
