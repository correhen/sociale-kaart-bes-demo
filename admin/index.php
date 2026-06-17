<?php

declare(strict_types=1);

require __DIR__ . '/auth.php';

header('Location: ' . (admin_is_logged_in() ? 'dashboard.php' : 'login.php'));
exit;
