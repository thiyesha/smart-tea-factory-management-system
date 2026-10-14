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

if (!$userId) {
    http_response_code(404);
    exit('User not found.');
}

$newPassword =
    (string) ($_POST['new_password'] ?? '');

$confirmPassword =
    (string) ($_POST['confirm_password'] ?? '');

$errors = [];

if (strlen($newPassword) < 8) {
    $errors[] =
        'Password must contain at least eight characters.';
}

if ($newPassword !== $confirmPassword) {
    $errors[] = 'The passwords do not match.';
}

if ($errors !== []) {
    $_SESSION['password_reset_errors'] = $errors;

    header(
        'Location: ' .
        BASE_URL .
        '/users/reset_password.php?id=' .
        $userId
    );

    exit;
}

$pdo = getDatabaseConnection();

$userStatement = $pdo->prepare(
    "SELECT
        user_id,
        user_code,
        username,
        full_name
     FROM users
     WHERE user_id = :user_id"
);

$userStatement->execute([
    'user_id' => $userId
]);

$selectedUser = $userStatement->fetch();

if ($selectedUser === false) {
    http_response_code(404);
    exit('User not found.');
}

try {
    $pdo->beginTransaction();

    $passwordStatement = $pdo->prepare(
        "UPDATE users
         SET password_hash = :password_hash
         WHERE user_id = :user_id"
    );

    $passwordStatement->execute([
        'password_hash' => password_hash(
            $newPassword,
            PASSWORD_DEFAULT
        ),
        'user_id' => $userId
    ]);

    $administrator = currentUser();

    $activityStatement = $pdo->prepare(
        "INSERT INTO user_activity_log (
            user_id,
            activity_type,
            activity_description,
            ip_address
         )
         VALUES (
            :administrator_id,
            'RESET_PASSWORD',
            :description,
            :ip_address
         )"
    );

    $activityStatement->execute([
        'administrator_id' =>
            $administrator['user_id'],

        'description' =>
            'Reset password for user ' .
            $selectedUser['user_code'] .
            ' (' .
            $selectedUser['username'] .
            ')',

        'ip_address' =>
            $_SERVER['REMOTE_ADDR'] ?? null
    ]);

    $pdo->commit();

    setFlashMessage(
        'success',
        'Password for ' .
        $selectedUser['full_name'] .
        ' was reset successfully.'
    );

    header('Location: ' . BASE_URL . '/users/index.php');
    exit;

} catch (Throwable $exception) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    error_log(
        'Reset user password error: ' .
        $exception->getMessage()
    );

    $_SESSION['password_reset_errors'] = [
        'The password could not be reset. Please try again.'
    ];

    header(
        'Location: ' .
        BASE_URL .
        '/users/reset_password.php?id=' .
        $userId
    );

    exit;
}