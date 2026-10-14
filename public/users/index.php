<?php

declare(strict_types=1);

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../src/permissions.php';

requirePermission('manage_users');

$pdo = getDatabaseConnection();

$search = trim(
    (string) ($_GET['search'] ?? '')
);

$sql = "
    SELECT
        user_id,
        user_code,
        username,
        full_name,
        role,
        email,
        phone,
        account_status,
        last_login_at,
        created_at
    FROM users
";

$parameters = [];

if ($search !== '') {
    $sql .= "
        WHERE
            user_code LIKE :search_code
            OR username LIKE :search_username
            OR full_name LIKE :search_name
            OR email LIKE :search_email
    ";

    $searchValue = '%' . $search . '%';
    $parameters = [
        'search_code' => $searchValue,
        'search_username' => $searchValue,
        'search_name' => $searchValue,
        'search_email' => $searchValue
    ];
}

$sql .= " ORDER BY user_id ASC";

$statement = $pdo->prepare($sql);
$statement->execute($parameters);

$users = $statement->fetchAll();

$pageTitle = 'User Management';
$currentModule = 'users';

require_once __DIR__ . '/../../templates/header.php';

$loggedInUser = currentUser();
?>

<section class="card">

    <div class="page-actions">

        <div>
            <h2>System Users</h2>

            <p>
                View, search and manage users who can access
                the system.
            </p>
        </div>

        <div>
            <span class="role-badge">
                <?= count($users) ?>
                User<?= count($users) === 1 ? '' : 's' ?>
            </span>

            <a
                href="<?= BASE_URL ?>/users/create.php"
                class="button"
            >
                + Add User
            </a>
        </div>

    </div>

    <form
        action="<?= BASE_URL ?>/users/index.php"
        method="GET"
        class="search-form"
    >
        <input
            type="search"
            name="search"
            value="<?= htmlspecialchars($search) ?>"
            placeholder="Search code, username, name or email"
        >

        <button
            type="submit"
            class="button"
        >
            Search
        </button>

        <?php if ($search !== ''): ?>

            <a
                href="<?= BASE_URL ?>/users/index.php"
                class="button button-secondary"
            >
                Clear
            </a>

        <?php endif; ?>
    </form>

    <div class="table-container">

        <table class="data-table">

            <thead>
            <tr>
                <th>User Code</th>
                <th>Username</th>
                <th>Full Name</th>
                <th>Role</th>
                <th>Contact</th>
                <th>Status</th>
                <th>Last Login</th>
                <th>Actions</th>
            </tr>
            </thead>

            <tbody>

            <?php if ($users === []): ?>

                <tr>
                    <td
                        colspan="8"
                        class="empty-state"
                    >
                        No users were found.
                    </td>
                </tr>

            <?php else: ?>

                <?php foreach ($users as $listedUser): ?>

                    <?php
                    $statusClass =
                        $listedUser['account_status']
                        === 'Active'
                            ? 'status-active'
                            : 'status-inactive';

                    $isCurrentAccount =
                        (int) $loggedInUser['user_id']
                        === (int) $listedUser['user_id'];

                    $newStatus =
                        $listedUser['account_status']
                        === 'Active'
                            ? 'Inactive'
                            : 'Active';

                    $statusButtonText =
                        $newStatus === 'Active'
                            ? 'Activate'
                            : 'Deactivate';

                    $statusButtonClass =
                        $newStatus === 'Active'
                            ? 'button'
                            : 'button-danger';
                    ?>

                    <tr>

                        <td>
                            <strong>
                                <?= htmlspecialchars(
                                    $listedUser['user_code']
                                ) ?>
                            </strong>
                        </td>

                        <td>
                            <?= htmlspecialchars(
                                $listedUser['username']
                            ) ?>
                        </td>

                        <td>
                            <?= htmlspecialchars(
                                $listedUser['full_name']
                            ) ?>
                        </td>

                        <td>
                            <?= htmlspecialchars(
                                $listedUser['role']
                            ) ?>
                        </td>

                        <td>
                            <div>
                                <?= htmlspecialchars(
                                    $listedUser['email'] ?? '—'
                                ) ?>
                            </div>

                            <small>
                                <?= htmlspecialchars(
                                    $listedUser['phone'] ?? '—'
                                ) ?>
                            </small>
                        </td>

                        <td>
                            <span
                                class="status-badge
                                    <?= $statusClass ?>"
                            >
                                <?= htmlspecialchars(
                                    $listedUser['account_status']
                                ) ?>
                            </span>
                        </td>

                        <td>
                            <?=
                            $listedUser['last_login_at'] !== null
                                ? htmlspecialchars(
                                    date(
                                        'Y-m-d H:i',
                                        strtotime(
                                            $listedUser[
                                                'last_login_at'
                                            ]
                                        )
                                    )
                                )
                                : 'Never'
                            ?>
                        </td>

                        <td>
                            <div class="action-group">

                                <a
                                    href="<?= BASE_URL ?>/users/edit.php?id=<?= (int) $listedUser['user_id'] ?>"
                                    class="button button-small"
                                >
                                    Edit
                                </a>

                                <a
                                    href="<?= BASE_URL ?>/users/reset_password.php?id=<?= (int) $listedUser['user_id'] ?>"
                                    class="button button-small button-warning"
                                >
                                    Reset Password
                                </a>

                                <?php if (!$isCurrentAccount): ?>

                                    <form
                                        action="<?= BASE_URL ?>/users/status.php"
                                        method="POST"
                                        class="inline-form"
                                        onsubmit="return confirm(
                                            'Are you sure you want to <?= strtolower($statusButtonText) ?> this account?'
                                        );"
                                    >
                                        <input
                                            type="hidden"
                                            name="csrf_token"
                                            value="<?= htmlspecialchars(csrfToken()) ?>"
                                        >

                                        <input
                                            type="hidden"
                                            name="user_id"
                                            value="<?= (int) $listedUser['user_id'] ?>"
                                        >

                                        <input
                                            type="hidden"
                                            name="new_status"
                                            value="<?= $newStatus ?>"
                                        >

                                        <button
                                            type="submit"
                                            class="button button-small <?= $statusButtonClass ?>"
                                        >
                                            <?= $statusButtonText ?>
                                        </button>
                                    </form>

                                <?php else: ?>

                                    <span class="role-badge">
                                        Current Account
                                    </span>

                                <?php endif; ?>

                            </div>
                        </td>

                    </tr>

                <?php endforeach; ?>

            <?php endif; ?>

            </tbody>

        </table>

    </div>

</section>

<?php

require_once __DIR__ . '/../../templates/footer.php';

?>
