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
        email,
        phone,
        account_status
     FROM users
     WHERE user_id = :user_id"
);

$statement->execute([
    'user_id' => $userId
]);

$editingUser = $statement->fetch();

if ($editingUser === false) {
    http_response_code(404);
    exit('User not found.');
}

$errors = $_SESSION['user_edit_errors'] ?? [];
$oldInput = $_SESSION['user_edit_input'] ?? [];

unset(
    $_SESSION['user_edit_errors'],
    $_SESSION['user_edit_input']
);

$formData = array_merge(
    $editingUser,
    $oldInput
);

$roles = [
    'Administrator',
    'Production Manager',
    'Quality Officer',
    'Machine Operator',
    'Store Keeper'
];

$currentLoggedUser = currentUser();

$isEditingOwnAccount =
    (int) $currentLoggedUser['user_id']
    === (int) $editingUser['user_id'];

$pageTitle = 'Edit User';
$currentModule = 'users';

require_once __DIR__ . '/../../templates/header.php';
?>

<section class="card">

    <div class="page-actions">
        <div>
            <h2>Edit User</h2>

            <p>
                Update the selected user’s profile and access role.
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

    <?php if ($isEditingOwnAccount): ?>

        <div class="alert-message alert-warning">
            You are editing your own account. Your role and active
            status cannot be changed here.
        </div>

    <?php endif; ?>

    <form
        action="<?= BASE_URL ?>/users/update.php"
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
            value="<?= (int) $editingUser['user_id'] ?>"
        >

        <div class="form-group">
            <label for="user_code">User Code</label>

            <input
                type="text"
                id="user_code"
                value="<?= htmlspecialchars(
                    $editingUser['user_code']
                ) ?>"
                readonly
            >

            <span class="form-help">
                User codes cannot be changed.
            </span>
        </div>

        <div class="form-group">
            <label for="username">Username</label>

            <input
                type="text"
                id="username"
                name="username"
                value="<?= htmlspecialchars(
                    $formData['username']
                ) ?>"
                maxlength="50"
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
                    $formData['full_name']
                ) ?>"
                maxlength="100"
                required
            >
        </div>

        <div class="form-group">
            <label for="role">Role</label>

            <select
                id="role"
                name="role"
                <?= $isEditingOwnAccount ? 'disabled' : '' ?>
                required
            >
                <?php foreach ($roles as $role): ?>

                    <option
                        value="<?= htmlspecialchars($role) ?>"
                        <?= $formData['role'] === $role
                            ? 'selected'
                            : '' ?>
                    >
                        <?= htmlspecialchars($role) ?>
                    </option>

                <?php endforeach; ?>
            </select>

            <?php if ($isEditingOwnAccount): ?>

                <input
                    type="hidden"
                    name="role"
                    value="<?= htmlspecialchars(
                        $editingUser['role']
                    ) ?>"
                >

            <?php endif; ?>
        </div>

        <div class="form-group">
            <label for="email">Email</label>

            <input
                type="email"
                id="email"
                name="email"
                value="<?= htmlspecialchars(
                    $formData['email'] ?? ''
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
                    $formData['phone'] ?? ''
                ) ?>"
                maxlength="20"
            >
        </div>

        <div class="form-group">
            <label for="account_status">Account Status</label>

            <select
                id="account_status"
                name="account_status"
                <?= $isEditingOwnAccount ? 'disabled' : '' ?>
                required
            >
                <option
                    value="Active"
                    <?= $formData['account_status'] === 'Active'
                        ? 'selected'
                        : '' ?>
                >
                    Active
                </option>

                <option
                    value="Inactive"
                    <?= $formData['account_status'] === 'Inactive'
                        ? 'selected'
                        : '' ?>
                >
                    Inactive
                </option>
            </select>

            <?php if ($isEditingOwnAccount): ?>

                <input
                    type="hidden"
                    name="account_status"
                    value="Active"
                >

            <?php endif; ?>
        </div>

        <div class="form-actions">
            <button type="submit" class="button">
                Save Changes
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