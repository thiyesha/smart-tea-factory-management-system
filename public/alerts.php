<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../src/permissions.php';
require_once __DIR__ . '/../src/csrf.php';
require_once __DIR__ . '/../src/flash.php';
require_once __DIR__ . '/../src/activity.php';

requirePermission('manage_alerts');
$pdo = getDatabaseConnection();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? null)) {
        http_response_code(403);
        exit('Invalid form request.');
    }
    $id = filter_input(INPUT_POST, 'alert_id', FILTER_VALIDATE_INT) ?: 0;
    $action = (string) ($_POST['action'] ?? '');
    if ($action === 'acknowledge') {
        $pdo->prepare("UPDATE alert SET alert_status='Acknowledged', acknowledged_by=:user, acknowledged_at=NOW() WHERE alert_id=:id AND alert_status='Active'")
            ->execute(['user' => currentUser()['user_id'], 'id' => $id]);
    } elseif ($action === 'resolve') {
        $pdo->prepare("UPDATE alert SET alert_status='Resolved', acknowledged_by=COALESCE(acknowledged_by,:user), acknowledged_at=COALESCE(acknowledged_at,NOW()), resolved_at=NOW() WHERE alert_id=:id")
            ->execute(['user' => currentUser()['user_id'], 'id' => $id]);
    }
    logActivity($pdo, 'UPDATE_ALERT', ucfirst($action) . " alert #{$id}");
    setFlashMessage('success', 'Alert updated.');
    header('Location: ' . BASE_URL . '/alerts.php');
    exit;
}

$status = (string) ($_GET['status'] ?? '');
$parameters = [];
$where = '';
if (in_array($status, ['Active', 'Acknowledged', 'Resolved'], true)) {
    $where = ' WHERE a.alert_status = :status';
    $parameters['status'] = $status;
}
$statement = $pdo->prepare(
    "SELECT a.*, s.sensor_name, m.machine_name, u.full_name acknowledged_name
     FROM alert a JOIN sensor s ON s.sensor_id=a.sensor_id
     JOIN machine m ON m.machine_id=s.machine_id
     LEFT JOIN users u ON u.user_id=a.acknowledged_by
     {$where} ORDER BY FIELD(a.alert_status,'Active','Acknowledged','Resolved'), a.generated_at DESC"
);
$statement->execute($parameters);
$alerts = $statement->fetchAll();
$pageTitle = 'System Alerts';
$currentModule = 'alerts';
require_once __DIR__ . '/../templates/header.php';
?>
<section class="card">
    <div class="page-actions"><div><h2>Alert Centre</h2><p>Acknowledge and resolve abnormal machine readings.</p></div></div>
    <div class="filter-links">
        <?php foreach (['' => 'All', 'Active' => 'Active', 'Acknowledged' => 'Acknowledged', 'Resolved' => 'Resolved'] as $key => $label): ?>
            <a class="<?= $status === $key ? 'active' : '' ?>" href="<?= BASE_URL ?>/alerts.php?status=<?= urlencode($key) ?>"><?= $label ?></a>
        <?php endforeach; ?>
    </div>
    <div class="table-container"><table class="data-table">
        <thead><tr><th>Severity</th><th>Machine / Sensor</th><th>Message</th><th>Generated</th><th>Status</th><th>Handled By</th><th>Action</th></tr></thead>
        <tbody>
        <?php if ($alerts === []): ?><tr><td colspan="7" class="empty-state">No alerts found.</td></tr><?php endif; ?>
        <?php foreach ($alerts as $alert): ?><tr>
            <td><span class="severity severity-<?= strtolower($alert['severity']) ?>"><?= htmlspecialchars($alert['severity']) ?></span></td>
            <td><?= htmlspecialchars($alert['machine_name']) ?><small class="block"><?= htmlspecialchars($alert['sensor_name']) ?></small></td>
            <td><?= htmlspecialchars($alert['alert_message']) ?></td>
            <td><?= htmlspecialchars($alert['generated_at']) ?></td>
            <td><?= htmlspecialchars($alert['alert_status']) ?></td>
            <td><?= htmlspecialchars($alert['acknowledged_name'] ?? '—') ?></td>
            <td><?php if ($alert['alert_status'] !== 'Resolved'): ?>
                <form method="post" class="action-group">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrfToken()) ?>">
                    <input type="hidden" name="alert_id" value="<?= (int) $alert['alert_id'] ?>">
                    <?php if ($alert['alert_status'] === 'Active'): ?><button class="button button-small" name="action" value="acknowledge">Acknowledge</button><?php endif; ?>
                    <button class="button button-small button-secondary" name="action" value="resolve">Resolve</button>
                </form>
            <?php endif; ?></td>
        </tr><?php endforeach; ?>
        </tbody>
    </table></div>
</section>
<?php require_once __DIR__ . '/../templates/footer.php'; ?>
