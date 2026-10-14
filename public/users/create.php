<?php

declare(strict_types=1);

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../src/permissions.php';
require_once __DIR__ . '/../../src/csrf.php';

requirePermission('manage_users');

$pdo = getDatabaseConnection();

$nextNumber = (int) $pdo
    ->query(
        "SELECT COALESCE(MAX(user_id), 0) + 1
         FROM users"
    )
    ->fetchColumn();

$suggestedUserCode = sprintf(
    'USR%03d',
    $nextNumber
);

$errors = $_SESSION['user_form_errors'] ?? [];
$oldInput = $_SESSION['user_form_input'] ?? [];

unset(
    $_SESSION['user_form_errors'],
    $_SESSION['user_form_input']
);

$roles = [
    'Administrator',
    'Production Manager',
    'Quality Officer',
    'Machine Operator',
    'Store Keeper'
];

$pageTitle = 'Create User';
$currentModule = 'users';

require_once __DIR__ . '/../../templates/header.php';
?>

<section class="card">

    <div class="page-actions">
        <div>
            <h2>Add New User</h2>

            <p>
                Create a login account and assign its system role.
            </p>
        </div>

        <a
            href="<?= BASE_URL ?>/users/index.php"
            class="button button-secondary"
        >
            Back to Users
        </a>
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
        action="<?= BASE_URL ?>/users/store.php"
        method="POST"
        class="form-grid"
    >
        <input
            type="hidden"
            name="csrf_token"
            value="<?= htmlspecialchars(csrfToken()) ?>"
        >

        <div class="form-group">
            <label for="user_code">User Code</label>

            <input
                type="text"
                id="user_code"
                name="user_code"
                value="<?= htmlspecialchars(
                    $oldInput['user_code']
                    ?? $suggestedUserCode
                ) ?>"
                maxlength="10"
                required
            >

            <span class="form-help">
                Format example: USR006
            </span>
        </div>

        <div class="form-group">
            <label for="username">Username</label>

            <input
                type="text"
                id="username"
                name="username"
                value="<?= htmlspecialchars(
                    $oldInput['username'] ?? ''
                ) ?>"
                maxlength="50"
                autocomplete="off"
                required
            >
        </div>

        <div class="form-group">
            <label for="full_name">Full Name</label>

            <input
                type="text"
                id="full_name"
                name="full_name"
                value="<?= htmlspecialchars(
                    $oldInput['full_name'] ?? ''
                ) ?>"
                maxlength="100"
                required
            >
        </div>

        <div class="form-group">
            <label for="role">Role</label>

            <select id="role" name="role" required>
                <option value="">Select a role</option>

                <?php foreach ($roles as $role): ?>

                    <option
                        value="<?= htmlspecialchars($role) ?>"
                        <?= ($oldInput['role'] ?? '') === $role
                            ? 'selected'
                            : '' ?>
                    >
                        <?= htmlspecialchars($role) ?>
                    </option>

                <?php endforeach; ?>

            </select>
        </div>

        <div class="form-group">
            <label for="email">Email</label>

            <input
                type="email"
                id="email"
                name="email"
                value="<?= htmlspecialchars(
                    $oldInput['email'] ?? ''
                ) ?>"
                maxlength="100"
            >
        </div>

        <div class="form-group">
            <label for="phone">Phone Number</label>

            <input
                type="text"
                id="phone"
                name="phone"
                value="<?= htmlspecialchars(
                    $oldInput['phone'] ?? ''
                ) ?>"
                maxlength="20"
            >
        </div>

        <div class="form-group">
            <label for="account_status">Account Status</label>

            <select
                id="account_status"
                name="account_status"
                required
            >
                <option
                    value="Active"
                    <?= ($oldInput['account_status'] ?? 'Active')
                        === 'Active'
                        ? 'selected'
                        : '' ?>
                >
                    Active
                </option>

                <option
                    value="Inactive"
                    <?= ($oldInput['account_status'] ?? '')
                        === 'Inactive'
                        ? 'selected'
                        : '' ?>
                >
                    Inactive
                </option>
            </select>
        </div>

        <div></div>

        <div class="form-group">
            <label for="password">Password</label>

            <input
                type="password"
                id="password"
                name="password"
                minlength="8"
                autocomplete="new-password"
                required
            >

            <span class="form-help">
                Use at least eight characters.
            </span>
        </div>

        <div class="form-group">
            <label for="confirm_password">
                Confirm Password
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
            <button type="submit" class="button">
                Create User
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