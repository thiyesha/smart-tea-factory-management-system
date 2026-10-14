<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../src/permissions.php';

requirePermission('manage_users');
$pdo = getDatabaseConnection();
$rows = $pdo->query(
    "SELECT l.activity_id, u.user_code, u.full_name, l.activity_type,
            l.activity_description, l.ip_address, l.activity_time
     FROM user_activity_log l JOIN users u ON u.user_id=l.user_id
     ORDER BY l.activity_time DESC LIMIT 500"
)->fetchAll();
$pageTitle = 'Activity Log';
$currentModule = 'reports';
require_once __DIR__ . '/../templates/header.php';
?>
<section class="card">
    <div class="page-actions"><div><h2>System Activity</h2><p>Latest 500 auditable user actions.</p></div><a class="button button-secondary" href="<?= BASE_URL ?>/reports.php">Back to Reports</a></div>
    <div class="table-container"><table class="data-table">
        <thead><tr><th>Time</th><th>User</th><th>Type</th><th>Description</th><th>IP Address</th></tr></thead>
        <tbody><?php foreach ($rows as $row): ?><tr>
            <td><?= htmlspecialchars($row['activity_time']) ?></td>
            <td><?= htmlspecialchars($row['user_code'] . ' — ' . $row['full_name']) ?></td>
            <td><?= htmlspecialchars($row['activity_type']) ?></td>
            <td><?= htmlspecialchars($row['activity_description']) ?></td>
            <td><?= htmlspecialchars($row['ip_address'] ?? '—') ?></td>
        </tr><?php endforeach; ?></tbody>
    </table></div>
</section>
<?php require_once __DIR__ . '/../templates/footer.php'; ?>
