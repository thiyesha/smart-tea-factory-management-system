<?php

declare(strict_types=1);

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../src/permissions.php';
require_once __DIR__ . '/../../src/csrf.php';

requirePermission('manage_users');

$userId = filter_input(
    INPUT_GET,
    'id',
    FILTER_VALIDATE_INT
);

if (!$userId) {
    http_response_code(404);
    exit('User not found.');
}

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
    'user_id' => $userId
]);

$selectedUser = $statement->fetch();

if ($selectedUser === false) {
    http_response_code(404);
    exit('User not found.');
}

$errors = $_SESSION['password_reset_errors'] ?? [];

unset($_SESSION['password_reset_errors']);

$pageTitle = 'Reset User Password';
$currentModule = 'users';

require_once __DIR__ . '/../../templates/header.php';
?>

<section class="card">

    <div class="page-actions">
        <div>
            <h2>Reset Password</h2>

            <p>
                Create a new password for the selected user.
            </p>
        </div>

        <a
            href="<?= BASE_URL ?>/users/index.php"
            class="button button-secondary"
        >
            Back to Users
        </a>
    </div>

    <div class="alert-message alert-warning">
        You are resetting the password for
        <strong>
            <?= htmlspecialchars($selectedUser['full_name']) ?>
        </strong>
        (<?= htmlspecialchars($selectedUser['username']) ?>).
    </div>

    <?php if ($errors !== []): ?>

        <div class="alert-message alert-error">
            <strong>Please correct these errors:</strong>

            <ul class="validation-list">

                <?php foreach ($errors as $error): ?>

                    <li><?= htmlspecialchars($error) ?></li>

                <?php endforeach; ?>

            </ul>
        </div>

    <?php endif; ?>

    <form
        action="<?= BASE_URL ?>/users/update_password.php"
        method="POST"
        class="form-grid"
    >
        <input
            type="hidden"
            name="csrf_token"
            value="<?= htmlspecialchars(csrfToken()) ?>"
        >

        <input
            type="hidden"
            name="user_id"
            value="<?= (int) $selectedUser['user_id'] ?>"
        >

        <div class="form-group">
            <label>User Code</label>

            <input
                type="text"
                value="<?= htmlspecialchars(
                    $selectedUser['user_code']
                ) ?>"
                readonly
            >
        </div>

        <div class="form-group">
            <label>Role</label>

            <input
                type="text"
                value="<?= htmlspecialchars(
                    $selectedUser['role']
                ) ?>"
                readonly
            >
        </div>

        <div class="form-group">
            <label for="new_password">
                New Password
            </label>

            <input
                type="password"
                id="new_password"
                name="new_password"
                minlength="8"
                autocomplete="new-password"
                required
            >

            <span class="form-help">
                Password must contain at least eight characters.
            </span>
        </div>

        <div class="form-group">
            <label for="confirm_password">
                Confirm New Password
            </label>

            <input
                type="password"
                id="confirm_password"
                name="confirm_password"
                minlength="8"
                autocomplete="new-password"
                required
            >
        </div>

        <div class="form-actions">
            <button
                type="submit"
                class="button button-warning"
            >
                Reset Password
            </button>

            <a
                href="<?= BASE_URL ?>/users/index.php"
                class="button button-secondary"
            >
                Cancel
            </a>
        </div>
    </form>
</section>

<?php require_once __DIR__ . '/../../templates/footer.php'; ?>