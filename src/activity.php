<?php

declare(strict_types=1);

require_once __DIR__ . '/auth.php';

function logActivity(PDO $pdo, string $type, string $description): void
{
    $user = currentUser();
    if ($user === null) {
        return;
    }

    $statement = $pdo->prepare(
        'INSERT INTO user_activity_log
         (user_id, activity_type, activity_description, ip_address)
         VALUES (:user_id, :type, :description, :ip)'
    );
    $statement->execute([
        'user_id' => $user['user_id'],
        'type' => substr($type, 0, 50),
        'description' => substr($description, 0, 255),
        'ip' => $_SERVER['REMOTE_ADDR'] ?? null,
    ]);
}
