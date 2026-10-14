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

$userCode = strtoupper(
    trim((string) ($_POST['user_code'] ?? ''))
);

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

$password = (string) ($_POST['password'] ?? '');

$confirmPassword =
    (string) ($_POST['confirm_password'] ?? '');

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

if (!preg_match('/^USR[0-9]{3,7}$/', $userCode)) {
    $errors[] =
        'User code must use a format such as USR006.';
}

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

if (strlen($password) < 8) {
    $errors[] =
        'Password must contain at least eight characters.';
}

if ($password !== $confirmPassword) {
    $errors[] = 'The passwords do not match.';
}

$pdo = getDatabaseConnection();

if ($errors === []) {
    $duplicateStatement = $pdo->prepare(
        "SELECT
            user_code,
            username,
            email
         FROM users
         WHERE
            user_code = :user_code
            OR username = :username
            OR email = NULLIF(:email, '')"
    );

    $duplicateStatement->execute([
        'user_code' => $userCode,
        'username'  => $username,
        'email'     => $email
    ]);

    $duplicateRecords = $duplicateStatement->fetchAll();

    foreach ($duplicateRecords as $record) {
        if ($record['user_code'] === $userCode) {
            $errors[] = 'That user code already exists.';
        }

        if ($record['username'] === $username) {
            $errors[] = 'That username already exists.';
        }

        if (
            $email !== ''
            && $record['email'] === $email
        ) {
            $errors[] = 'That email address already exists.';
        }
    }
}

if ($errors !== []) {
    $_SESSION['user_form_errors'] = $errors;

    $_SESSION['user_form_input'] = [
        'user_code'      => $userCode,
        'username'       => $username,
        'full_name'      => $fullName,
        'role'           => $role,
        'email'          => $email,
        'phone'          => $phone,
        'account_status' => $accountStatus
    ];

    header('Location: ' . BASE_URL . '/users/create.php');
    exit;
}

try {
    $pdo->beginTransaction();

    $insertStatement = $pdo->prepare(
        "INSERT INTO users (
            user_code,
            username,
            password_hash,
            full_name,
            role,
            email,
            phone,
            account_status
         )
         VALUES (
            :user_code,
            :username,
            :password_hash,
            :full_name,
            :role,
            :email,
            :phone,
            :account_status
         )"
    );

    $insertStatement->execute([
        'user_code'      => $userCode,
        'username'       => $username,
        'password_hash'  => password_hash(
            $password,
            PASSWORD_DEFAULT
        ),
        'full_name'      => $fullName,
        'role'           => $role,
        'email'          => $email !== '' ? $email : null,
        'phone'          => $phone !== '' ? $phone : null,
        'account_status' => $accountStatus
    ]);

    $newUserId = (int) $pdo->lastInsertId();

    $administrator = currentUser();

    $activityStatement = $pdo->prepare(
        "INSERT INTO user_activity_log (
            user_id,
            activity_type,
            activity_description,
            ip_address
         )
         VALUES (
            :user_id,
            'CREATE_USER',
            :description,
            :ip_address
         )"
    );

    $activityStatement->execute([
        'user_id' => $administrator['user_id'],

        'description' =>
            'Created user account ' .
            $userCode .
            ' (' .
            $username .
            ')',

        'ip_address' =>
            $_SERVER['REMOTE_ADDR'] ?? null
    ]);

    $pdo->commit();

    setFlashMessage(
        'success',
        'User ' . $fullName . ' was created successfully.'
    );

    header('Location: ' . BASE_URL . '/users/index.php');
    exit;

} catch (Throwable $exception) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    error_log(
        'Create user error: ' .
        $exception->getMessage()
    );

    $_SESSION['user_form_errors'] = [
        'The user could not be created. Please try again.'
    ];

    $_SESSION['user_form_input'] = [
        'user_code'      => $userCode,
        'username'       => $username,
        'full_name'      => $fullName,
        'role'           => $role,
        'email'          => $email,
        'phone'          => $phone,
        'account_status' => $accountStatus
    ];

    header('Location: ' . BASE_URL . '/users/create.php');
    exit;
}