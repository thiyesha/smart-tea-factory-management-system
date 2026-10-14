<?php

declare(strict_types=1);

require_once __DIR__ . '/../src/auth.php';
require_once __DIR__ . '/../src/csrf.php';

if (isLoggedIn()) {
    header('Location: ' . BASE_URL . '/dashboard.php');
    exit;
}

$errorMessage =
    $_SESSION['login_error'] ?? '';

$oldUsername =
    $_SESSION['old_username'] ?? '';

if (
    ($_GET['account'] ?? '') === 'inactive'
    && $errorMessage === ''
) {
    $errorMessage =
        'Your account is inactive. Please contact an administrator.';
}

unset(
    $_SESSION['login_error'],
    $_SESSION['old_username']
);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Login | <?= APP_NAME ?></title>

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            min-height: 100vh;
            display: grid;
            place-items: center;
            padding: 20px;
            font-family: Arial, sans-serif;
            background:
                linear-gradient(
                    135deg,
                    #eaf5ed,
                    #f8faf8
                );
            color: #1f3328;
        }

        .login-card {
            width: 100%;
            max-width: 420px;
            padding: 36px;
            background: white;
            border-radius: 16px;
            box-shadow:
                0 15px 40px rgba(0, 60, 30, 0.13);
            border-top: 6px solid #16834b;
        }

        .logo {
            width: 65px;
            height: 65px;
            margin: 0 auto 18px;
            display: grid;
            place-items: center;
            border-radius: 50%;
            background: #16834b;
            color: white;
            font-size: 30px;
        }

        h1 {
            margin: 0;
            text-align: center;
            color: #137744;
            font-size: 28px;
        }

        .subtitle {
            margin: 10px 0 28px;
            text-align: center;
            color: #66756c;
        }

        .form-group {
            margin-bottom: 18px;
        }

        label {
            display: block;
            margin-bottom: 7px;
            font-weight: bold;
        }

        input {
            width: 100%;
            padding: 12px 14px;
            border: 1px solid #cdd8d1;
            border-radius: 8px;
            font-size: 15px;
        }

        input:focus {
            outline: none;
            border-color: #16834b;
            box-shadow:
                0 0 0 3px rgba(22, 131, 75, 0.12);
        }

        button {
            width: 100%;
            padding: 13px;
            border: none;
            border-radius: 8px;
            background: #16834b;
            color: white;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
        }

        button:hover {
            background: #106b3b;
        }

        .error-message {
            margin-bottom: 20px;
            padding: 12px;
            border-radius: 8px;
            background: #fde8e8;
            color: #a61b1b;
            border-left: 4px solid #dc3545;
        }

        .development-note {
            margin-top: 22px;
            padding: 12px;
            border-radius: 8px;
            background: #f1f6f3;
            color: #53645a;
            font-size: 13px;
            line-height: 1.5;
        }
    </style>
</head>

<body>
<div class="login-card">

    <div class="logo">🍃</div>

    <h1>Smart Tea Factory</h1>

    <p class="subtitle">
        Sign in to access the management system
    </p>

    <?php if ($errorMessage !== ''): ?>

        <div class="error-message">
            <?= htmlspecialchars($errorMessage) ?>
        </div>

    <?php endif; ?>

    <form
        action="<?= BASE_URL ?>/process_login.php"
        method="POST"
    >
        <input
            type="hidden"
            name="csrf_token"
            value="<?= htmlspecialchars(csrfToken()) ?>"
        >

        <div class="form-group">
            <label for="username">Username</label>

            <input
                type="text"
                id="username"
                name="username"
                value="<?= htmlspecialchars($oldUsername) ?>"
                autocomplete="username"
                required
                autofocus
            >
        </div>

        <div class="form-group">
            <label for="password">Password</label>

            <input
                type="password"
                id="password"
                name="password"
                autocomplete="current-password"
                required
            >
        </div>

        <button type="submit">
            Sign In
        </button>
    </form>

    <div class="development-note">
        Development account:<br>
        Username: <strong>admin</strong><br>
        Password: <strong>password</strong>
    </div>

</div>
</body>
</html>