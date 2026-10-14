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

$pdo = getDatabaseConnection();

$existingStatement = $pdo->prepare(
    "SELECT
        user_id,
        user_code,
        username,
        full_name,
        role,
        email,
        phone,
        account_status
     FROM users
     WHERE user_id = :user_id"
);

$existingStatement->execute([
    'user_id' => $userId
]);

$existingUser = $existingStatement->fetch();

if ($existingUser === false) {
    http_response_code(404);
    exit('User not found.');
}

$username = trim(
    (string) ($_POST['username'] ?? '')
);

$fullName = trim(
    (string) ($_POST['full_name'] ?? '')
);

$role = (string) ($_POST['role'] ?? '');

$email = strtolower(
    trim((string) ($_POST['email'] ?? ''))
);

$phone = trim(
    (string) ($_POST['phone'] ?? '')
);

$accountStatus =
    (string) ($_POST['account_status'] ?? '');

$currentLoggedUser = currentUser();

$isEditingOwnAccount =
    (int) $currentLoggedUser['user_id']
    === (int) $userId;

if ($isEditingOwnAccount) {
    $role = $existingUser['role'];
    $accountStatus = 'Active';
}

$allowedRoles = [
    'Administrator',
    'Production Manager',
    'Quality Officer',
    'Machine Operator',
    'Store Keeper'
];

$allowedStatuses = [
    'Active',
    'Inactive'
];

$errors = [];

if (
    !preg_match(
        '/^[A-Za-z0-9._-]{3,50}$/',
        $username
    )
) {
    $errors[] =
        'Username must contain 3–50 valid characters.';
}

if ($fullName === '' || strlen($fullName) > 100) {
    $errors[] = 'A valid full name is required.';
}

if (!in_array($role, $allowedRoles, true)) {
    $errors[] = 'Please select a valid role.';
}

if (
    $email !== ''
    && !filter_var($email, FILTER_VALIDATE_EMAIL)
) {
    $errors[] = 'Please enter a valid email address.';
}

if (strlen($phone) > 20) {
    $errors[] =
        'Phone number cannot exceed 20 characters.';
}

if (
    !in_array(
        $accountStatus,
        $allowedStatuses,
        true
    )
) {
    $errors[] = 'Please select a valid account status.';
}

if ($errors === []) {
    $duplicateStatement = $pdo->prepare(
        "SELECT
            username,
            email
         FROM users
         WHERE user_id <> :user_id
           AND (
               username = :username
               OR email = NULLIF(:email, '')
           )"
    );

    $duplicateStatement->execute([
        'user_id'  => $userId,
        'username' => $username,
        'email'    => $email
    ]);

    $duplicateRecords = $duplicateStatement->fetchAll();

    foreach ($duplicateRecords as $record) {
        if ($record['username'] === $username) {
            $errors[] = 'That username already exists.';
        }

        if (
            $email !== ''
            && $record['email'] === $email
        ) {
            $errors[] =
                'That email address already exists.';
        }
    }
}

if ($errors !== []) {
    $_SESSION['user_edit_errors'] = $errors;

    $_SESSION['user_edit_input'] = [
        'username'       => $username,
        'full_name'      => $fullName,
        'role'           => $role,
        'email'          => $email,
        'phone'          => $phone,
        'account_status' => $accountStatus
    ];

    header(
        'Location: ' .
        BASE_URL .
        '/users/edit.php?id=' .
        $userId
    );

    exit;
}

try {
    $pdo->beginTransaction();

    $updateStatement = $pdo->prepare(
        "UPDATE users
         SET
            username = :username,
            full_name = :full_name,
            role = :role,
            email = :email,
            phone = :phone,
            account_status = :account_status
         WHERE user_id = :user_id"
    );

    $updateStatement->execute([
        'username'       => $username,
        'full_name'      => $fullName,
        'role'           => $role,
        'email'          => $email !== '' ? $email : null,
        'phone'          => $phone !== '' ? $phone : null,
        'account_status' => $accountStatus,
        'user_id'        => $userId
    ]);

    $activityStatement = $pdo->prepare(
        "INSERT INTO user_activity_log (
            user_id,
            activity_type,
            activity_description,
            ip_address
         )
         VALUES (
            :administrator_id,
            'UPDATE_USER',
            :description,
            :ip_address
         )"
    );

    $activityStatement->execute([
        'administrator_id' =>
            $currentLoggedUser['user_id'],

        'description' =>
            'Updated user account ' .
            $existingUser['user_code'] .
            ' (' .
            $username .
            ')',

        'ip_address' =>
            $_SERVER['REMOTE_ADDR'] ?? null
    ]);

    $pdo->commit();

    if ($isEditingOwnAccount) {
        $_SESSION['user']['username'] = $username;
        $_SESSION['user']['full_name'] = $fullName;
    }

    setFlashMessage(
        'success',
        'User ' . $fullName . ' was updated successfully.'
    );

    header('Location: ' . BASE_URL . '/users/index.php');
    exit;

} catch (Throwable $exception) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    error_log(
        'Update user error: ' .
        $exception->getMessage()
    );

    $_SESSION['user_edit_errors'] = [
        'The user could not be updated. Please try again.'
    ];

    $_SESSION['user_edit_input'] = [
        'username'       => $username,
        'full_name'      => $fullName,
        'role'           => $role,
        'email'          => $email,
        'phone'          => $phone,
        'account_status' => $accountStatus
    ];

    header(
        'Location: ' .
        BASE_URL .
        '/users/edit.php?id=' .
        $userId
    );

    exit;
}