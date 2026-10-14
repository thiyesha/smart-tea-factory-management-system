<?php

declare(strict_types=1);

require_once __DIR__ . '/../src/permissions.php';
require_once __DIR__ . '/../src/modules.php';
require_once __DIR__ . '/../src/csrf.php';
require_once __DIR__ . '/../src/flash.php';

requireLogin();

$pageTitle = $pageTitle ?? 'Dashboard';
$currentModule = $currentModule ?? 'dashboard';

$user = currentUser();
$flashMessage = getFlashMessage();

$allowedFlashTypes = [
    'success',
    'error',
    'warning'
];

$flashType = $flashMessage['type'] ?? '';

if (!in_array($flashType, $allowedFlashTypes, true)) {
    $flashType = 'warning';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>
        <?= htmlspecialchars($pageTitle) ?> |
        <?= APP_NAME ?>
    </title>

    <link
        rel="stylesheet"
        href="<?= BASE_URL ?>/assets/css/app.css"
    >
</head>

<body>
<div class="app-shell">

    <aside class="sidebar">
        <div class="brand">
            <div class="brand-icon">🍃</div>

            <div>
                <h1>Smart Tea Factory</h1>
                <p>Management System</p>
            </div>
        </div>

        <nav class="sidebar-nav">
            <a
                class="nav-link
                    <?= $currentModule === 'dashboard'
                        ? 'active'
                        : '' ?>"
                href="<?= BASE_URL ?>/dashboard.php"
            >
                <span class="nav-icon">🏠</span>
                Dashboard
            </a>

            <?php foreach (SYSTEM_MODULES as $key => $module): ?>

                <?php if (can($module['permission'])): ?>

                    <a
                        class="nav-link
                            <?= $currentModule === $key
                                ? 'active'
                                : '' ?>"
                        href="<?= BASE_URL . ($module['path'] ?? '/module.php?name=' . urlencode($key)) ?>"
                    >
                        <span class="nav-icon">
                            <?= $module['icon'] ?>
                        </span>

                        <?= htmlspecialchars($module['title']) ?>
                    </a>

                <?php endif; ?>

            <?php endforeach; ?>
        </nav>
    </aside>

    <div class="main-area">

        <header class="topbar">
            <div class="topbar-title">
                <h2><?= htmlspecialchars($pageTitle) ?></h2>
                <p><?= APP_NAME ?></p>
            </div>

            <div class="user-area">
                <div class="user-information">
                    <strong>
                        <?= htmlspecialchars($user['full_name']) ?>
                    </strong>

                    <span>
                        <?= htmlspecialchars($user['role']) ?>
                    </span>
                </div>

                <form
                    action="<?= BASE_URL ?>/logout.php"
                    method="POST"
                >
                    <input
                        type="hidden"
                        name="csrf_token"
                        value="<?= htmlspecialchars(csrfToken()) ?>"
                    >

                    <button
                        type="submit"
                        class="logout-button"
                    >
                        Logout
                    </button>
                </form>
            </div>
        </header>

        <main class="page-content">

            <?php if ($flashMessage !== null): ?>

                <div
                    class="alert-message
                        alert-<?= htmlspecialchars($flashType) ?>"
                >
                    <?= htmlspecialchars($flashMessage['message']) ?>
                </div>

            <?php endif; ?>
