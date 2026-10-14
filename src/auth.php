<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/database.php';


/*==============================================================
  CHECK LOGIN STATE
==============================================================*/

function isLoggedIn(): bool
{
    return isset($_SESSION['user']);
}


/*==============================================================
  GET CURRENT USER
==============================================================*/

function currentUser(): ?array
{
    return $_SESSION['user'] ?? null;
}


/*==============================================================
  STORE USER IN SESSION
==============================================================*/

function loginUser(array $user): void
{
    session_regenerate_id(true);

    $_SESSION['user'] = [
        'user_id'   => (int) $user['user_id'],
        'user_code' => $user['user_code'],
        'username'  => $user['username'],
        'full_name' => $user['full_name'],
        'role'      => $user['role']
    ];
}


/*==============================================================
  LOGOUT USER
==============================================================*/

function logoutUser(): void
{
    $_SESSION = [];

    if (ini_get('session.use_cookies')) {
        $cookieParameters =
            session_get_cookie_params();

        setcookie(
            session_name(),
            '',
            time() - 42000,
            $cookieParameters['path'],
            $cookieParameters['domain'],
            $cookieParameters['secure'],
            $cookieParameters['httponly']
        );
    }

    session_destroy();
}


/*==============================================================
  REQUIRE ACTIVE LOGIN
==============================================================*/

function requireLogin(): void
{
    if (!isLoggedIn()) {
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
                full_name,
                role,
                account_status
             FROM users
             WHERE user_id = :user_id"
        );

        $statement->execute([
            'user_id' => $_SESSION['user']['user_id']
        ]);

        $databaseUser = $statement->fetch();

    } catch (Throwable $exception) {
        error_log(
            'Session user verification error: ' .
            $exception->getMessage()
        );

        http_response_code(500);

        exit(
            'The system could not verify your account. ' .
            'Please try again later.'
        );
    }

    if (
        $databaseUser === false
        || $databaseUser['account_status'] !== 'Active'
    ) {
        logoutUser();

        header(
            'Location: ' .
            BASE_URL .
            '/login.php?account=inactive'
        );

        exit;
    }

    /*
     * Refresh session details so role/name changes apply
     * immediately.
     */
    $_SESSION['user'] = [
        'user_id'   => (int) $databaseUser['user_id'],
        'user_code' => $databaseUser['user_code'],
        'username'  => $databaseUser['username'],
        'full_name' => $databaseUser['full_name'],
        'role'      => $databaseUser['role']
    ];
}


/*==============================================================
  CHECK ROLE
==============================================================*/

function hasRole(array $allowedRoles): bool
{
    $user = currentUser();

    if ($user === null) {
        return false;
    }

    return in_array(
        $user['role'],
        $allowedRoles,
        true
    );
}


/*==============================================================
  REQUIRE ROLE
==============================================================*/

function requireRole(array $allowedRoles): void
{
    requireLogin();

    if (!hasRole($allowedRoles)) {
        http_response_code(403);

        exit(
            'Access denied. You do not have permission ' .
            'to access this page.'
        );
    }
}