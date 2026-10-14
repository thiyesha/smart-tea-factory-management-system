<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/session.php';

function setFlashMessage(
    string $type,
    string $message
): void {
    $_SESSION['flash_message'] = [
        'type'    => $type,
        'message' => $message
    ];
}

function getFlashMessage(): ?array
{
    $flashMessage = $_SESSION['flash_message'] ?? null;

    unset($_SESSION['flash_message']);

    return $flashMessage;
}