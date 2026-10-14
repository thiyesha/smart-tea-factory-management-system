<?php

declare(strict_types=1);

require_once __DIR__ . '/../src/auth.php';

requireLogin();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Access Denied | <?= APP_NAME ?></title>

    <style>
        body {
            margin: 0;
            min-height: 100vh;
            display: grid;
            place-items: center;
            padding: 20px;
            font-family: Arial, sans-serif;
            background: #f5f7f5;
            color: #26382e;
        }

        .card {
            width: min(500px, 90%);
            padding: 35px;
            text-align: center;
            background: white;
            border-radius: 14px;
            border-top: 6px solid #dc3545;
            box-shadow: 0 12px 35px rgba(0, 0, 0, 0.10);
        }

        .icon {
            font-size: 55px;
        }

        h1 {
            color: #b4232c;
        }

        a {
            display: inline-block;
            margin-top: 15px;
            padding: 11px 18px;
            border-radius: 8px;
            background: #16834b;
            color: white;
            text-decoration: none;
        }
    </style>
</head>

<body>
<div class="card">
    <div class="icon">⛔</div>

    <h1>Access Denied</h1>

    <p>
        Your role does not have permission to access this module.
    </p>

    <a href="<?= BASE_URL ?>/dashboard.php">
        Return to Dashboard
    </a>
</div>
</body>
</html>