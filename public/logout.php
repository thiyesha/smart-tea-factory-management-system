<?php

declare(strict_types=1);

require_once __DIR__ . '/../src/auth.php';
require_once __DIR__ . '/../src/csrf.php';

if (
    $_SERVER['REQUEST_METHOD'] !== 'POST'
    || !verifyCsrfToken($_POST['csrf_token'] ?? null)
) {
    http_response_code(403);
    exit('Invalid logout request.');
}

logoutUser();

header('Location: ' . BASE_URL . '/login.php');
exit;