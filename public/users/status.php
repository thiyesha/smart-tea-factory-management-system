<?php

declare(strict_types=1);

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../src/permissions.php';
require_once __DIR__ . '/../../src/csrf.php';
require_once __DIR__ . '/../../src/flash.php';

requirePermission('manage_users');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . BASE_URL . '/users/index.php');
    exit;
}

if (!verifyCsrfToken($_POST['csrf_token'] ?? null)) {
    http_response_code(403);
    exit('Invalid form request.');
}

$userId = filter_var(
    $_POST['user_id'] ?? null,
    FILTER_VALIDATE_INT
);

$newStatus = (string) ($_POST['new_status'] ?? '');

if (
    !$userId
    || !in_array(
        $newStatus,
        ['Active', 'Inactive'],
        true
    )
) {
    setFlashMessage(
        'error',
        'Invalid account-status request.'
    );

    header('Location: ' . BASE_URL . '/users/index.php');
    exit;
}

$administrator = currentUser();

if ((int) $administrator['user_id'] === (int) $userId) {
    setFlashMessage(
        'error',
        'You cannot deactivate or change the status of your own account.'
    );

    header('Location: ' . BASE_URL . '/users/index.php');
    exit;
}

$pdo = getDatabaseConnection();

$userStatement = $pdo->prepare(
    "SELECT
        user_id,
        user_code,
        username,
        full_name,
        account_status
     FROM users
     WHERE user_id = :user_id"
);

$userStatement->execute([
    'user_id' => $userId
]);

$selectedUser = $userStatement->fetch();

if ($selectedUser === false) {
    setFlashMessage(
        'error',
        'The selected user was not found.'
    );

    header('Location: ' . BASE_URL . '/users/index.php');
    exit;
}

if ($selectedUser['account_status'] === $newStatus) {
    setFlashMessage(
        'warning',
        'The account already has that status.'
    );

    header('Location: ' . BASE_URL . '/users/index.php');
    exit;
}

try {
    $pdo->beginTransaction();

    $statusStatement = $pdo->prepare(
        "UPDATE users
         SET account_status = :account_status
         WHERE user_id = :user_id"
    );

    $statusStatement->execute([
        'account_status' => $newStatus,
        'user_id'        => $userId
    ]);

    $activityType =
        $newStatus === 'Active'
            ? 'ACTIVATE_USER'
            : 'DEACTIVATE_USER';

    $description =
        ($newStatus === 'Active'
            ? 'Activated'
            : 'Deactivated') .
        ' user account ' .
        $selectedUser['user_code'] .
        ' (' .
        $selectedUser['username'] .
        ')';

    $activityStatement = $pdo->prepare(
        "INSERT INTO user_activity_log (
            user_id,
            activity_type,
            activity_description,
            ip_address
         )
         VALUES (
            :administrator_id,
            :activity_type,
            :description,
            :ip_address
         )"
    );

    $activityStatement->execute([
        'administrator_id' =>
            $administrator['user_id'],

        'activity_type' => $activityType,

        'description' => $description,

        'ip_address' =>
            $_SERVER['REMOTE_ADDR'] ?? null
    ]);

    $pdo->commit();

    setFlashMessage(
        'success',
        $selectedUser['full_name'] .
        ' was ' .
        strtolower($newStatus) .
        ' successfully.'
    );

} catch (Throwable $exception) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    error_log(
        'Change user status error: ' .
        $exception->getMessage()
    );

    setFlashMessage(
        'error',
        'The account status could not be changed.'
    );
}

header('Location: ' . BASE_URL . '/users/index.php');
exit;