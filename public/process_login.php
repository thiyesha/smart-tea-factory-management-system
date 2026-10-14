<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../src/auth.php';
require_once __DIR__ . '/../src/csrf.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . BASE_URL . '/login.php');
    exit;
}

$submittedToken = $_POST['csrf_token'] ?? null;

if (!verifyCsrfToken($submittedToken)) {
    $_SESSION['login_error'] =
        'Your session expired. Please try again.';

    header('Location: ' . BASE_URL . '/login.php');
    exit;
}

$username = trim((string) ($_POST['username'] ?? ''));
$password = (string) ($_POST['password'] ?? '');

$_SESSION['old_username'] = $username;

if ($username === '' || $password === '') {
    $_SESSION['login_error'] =
        'Please enter both username and password.';

    header('Location: ' . BASE_URL . '/login.php');
    exit;
}

try {
    $pdo = getDatabaseConnection();

    $statement = $pdo->prepare(
        "SELECT
            user_id,
            user_code,
            username,
            password_hash,
            full_name,
            role,
            account_status
         FROM users
         WHERE username = :username
         LIMIT 1"
    );

    $statement->execute([
        'username' => $username
    ]);

    $user = $statement->fetch();

    $validLogin =
        $user !== false
        && $user['account_status'] === 'Active'
        && password_verify(
            $password,
            $user['password_hash']
        );

    if (!$validLogin) {
        $_SESSION['login_error'] =
            'Invalid username or password.';

        header('Location: ' . BASE_URL . '/login.php');
        exit;
    }

    $pdo->beginTransaction();

    $updateLogin = $pdo->prepare(
        "UPDATE users
         SET last_login_at = CURRENT_TIMESTAMP
         WHERE user_id = :user_id"
    );

    $updateLogin->execute([
        'user_id' => $user['user_id']
    ]);

    $activityLog = $pdo->prepare(
        "INSERT INTO user_activity_log (
            user_id,
            activity_type,
            activity_description,
            ip_address
         )
         VALUES (
            :user_id,
            'LOGIN',
            'User logged into the web application',
            :ip_address
         )"
    );

    $activityLog->execute([
        'user_id'    => $user['user_id'],
        'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null
    ]);

    $pdo->commit();

    loginUser($user);

    unset($_SESSION['old_username']);

    header('Location: ' . BASE_URL . '/dashboard.php');
    exit;

} catch (Throwable $exception) {
    if (
        isset($pdo)
        && $pdo instanceof PDO
        && $pdo->inTransaction()
    ) {
        $pdo->rollBack();
    }

    error_log(
        'Login processing error: ' .
        $exception->getMessage()
    );

    $_SESSION['login_error'] =
        'Unable to complete the login. Please try again.';

    header('Location: ' . BASE_URL . '/login.php');
    exit;
}