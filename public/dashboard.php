<?php

declare(strict_types=1);

require_once __DIR__ . '/../src/permissions.php';
require_once __DIR__ . '/../src/modules.php';

requireLogin();

$pdo = getDatabaseConnection();
$statistics = [
    ['label' => 'Active Batches', 'value' => (int) $pdo->query("SELECT COUNT(*) FROM production_batch WHERE batch_status IN ('Planned','In Progress')")->fetchColumn(), 'icon' => '🏭'],
    ['label' => 'Low-stock Items', 'value' => (int) $pdo->query("SELECT (SELECT COUNT(*) FROM tea_leaf_inventory WHERE stock_status <> 'Available') + (SELECT COUNT(*) FROM other_material WHERE material_status IN ('Low Stock','Out of Stock'))")->fetchColumn(), 'icon' => '📦'],
    ['label' => 'Open Alerts', 'value' => (int) $pdo->query("SELECT COUNT(*) FROM alert WHERE alert_status <> 'Resolved'")->fetchColumn(), 'icon' => '⚠️'],
    ['label' => 'Scheduled Maintenance', 'value' => (int) $pdo->query("SELECT COUNT(*) FROM maintenance WHERE maintenance_status = 'Scheduled'")->fetchColumn(), 'icon' => '🛠️'],
];
$recentAlerts = $pdo->query(
    "SELECT a.severity, a.alert_message, a.generated_at, m.machine_name
     FROM alert a JOIN sensor s ON s.sensor_id = a.sensor_id
     JOIN machine m ON m.machine_id = s.machine_id
     ORDER BY a.generated_at DESC LIMIT 5"
)->fetchAll();

$pageTitle = 'Dashboard';
$currentModule = 'dashboard';

require_once __DIR__ . '/../templates/header.php';

$user = currentUser();
?>

<section class="card welcome-card">
    <h2>
        Welcome,
        <?= htmlspecialchars($user['full_name']) ?>
    </h2>

    <p>
        Select an authorized module to begin managing the
        Smart Tea Factory system.
    </p>

    <span class="role-badge">
        <?= htmlspecialchars($user['role']) ?>
    </span>
</section>

<section class="stats-grid">
    <?php foreach ($statistics as $statistic): ?>
        <article class="stat-card">
            <span><?= $statistic['icon'] ?></span>
            <div><strong><?= $statistic['value'] ?></strong><small><?= htmlspecialchars($statistic['label']) ?></small></div>
        </article>
    <?php endforeach; ?>
</section>

<section class="module-grid">

    <?php foreach (SYSTEM_MODULES as $key => $module): ?>

        <?php if (can($module['permission'])): ?>

            <a
                class="module-card"
                href="<?= BASE_URL . ($module['path'] ?? '/module.php?name=' . urlencode($key)) ?>"
            >
                <div class="module-card-icon">
                    <?= $module['icon'] ?>
                </div>

                <h3>
                    <?= htmlspecialchars($module['title']) ?>
                </h3>

                <p>
                    <?= htmlspecialchars($module['description']) ?>
                </p>
            </a>

        <?php endif; ?>

    <?php endforeach; ?>

</section>

<?php if ($recentAlerts !== [] && can('manage_alerts')): ?>
<section class="card top-gap">
    <div class="page-actions"><h2>Recent Alerts</h2><a class="button button-secondary" href="<?= BASE_URL ?>/alerts.php">View all</a></div>
    <?php foreach ($recentAlerts as $alert): ?>
        <div class="feed-row">
            <strong><?= htmlspecialchars($alert['severity']) ?></strong>
            <span><?= htmlspecialchars($alert['machine_name'] . ': ' . $alert['alert_message']) ?></span>
            <small><?= htmlspecialchars($alert['generated_at']) ?></small>
        </div>
    <?php endforeach; ?>
</section>
<?php endif; ?>

<?php require_once __DIR__ . '/../templates/footer.php'; ?>
