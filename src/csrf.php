<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/session.php';

function csrfToken(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}

function verifyCsrfToken(?string $submittedToken): bool
{
    if (
        $submittedToken === null
        || empty($_SESSION['csrf_token'])
    ) {
        return false;
    }

    return hash_equals(
        $_SESSION['csrf_token'],
        $submittedToken
    );
}