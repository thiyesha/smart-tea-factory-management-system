<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../src/permissions.php';

requirePermission('view_reports');
$pdo = getDatabaseConnection();

$reportDefinitions = [
    'production' => [
        'title' => 'Production Report',
        'sql' => "SELECT batch_code, production_date, total_leaf_weight, expected_output, batch_status, created_by_name
                  FROM vw_production_batch_details ORDER BY production_date DESC",
    ],
    'inventory' => [
        'title' => 'Tea Leaf Inventory Report',
        'sql' => "SELECT tea_leaf_code, leaf_type, quality_grade, current_stock, unit, stock_status, supplier_name
                  FROM vw_tea_leaf_inventory_details ORDER BY stock_status, tea_leaf_code",
    ],
    'quality' => [
        'title' => 'Quality Report',
        'sql' => "SELECT pb.batch_code, qc.inspection_date, qc.moisture_content, qc.colour, qc.aroma,
                         qc.appearance, qc.quality_status, u.full_name AS inspected_by
                  FROM quality_control qc JOIN production_batch pb ON pb.batch_id=qc.batch_id
                  JOIN users u ON u.user_id=qc.inspected_by ORDER BY qc.inspection_date DESC",
    ],
    'finished' => [
        'title' => 'Finished Products Report',
        'sql' => "SELECT product_code, product_name, tea_grade, batch_code, quantity, manufacture_date,
                         expiry_date, warehouse_location, stock_status
                  FROM vw_finished_product_inventory ORDER BY manufacture_date DESC",
    ],
];

$reportKey = (string) ($_GET['report'] ?? 'production');
if (!isset($reportDefinitions[$reportKey])) {
    $reportKey = 'production';
}
$definition = $reportDefinitions[$reportKey];
$rows = $pdo->query($definition['sql'])->fetchAll();

if (($_GET['export'] ?? '') === 'csv') {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $reportKey . '-report-' . date('Y-m-d') . '.csv"');
    $output = fopen('php://output', 'wb');
    if ($rows !== []) {
        fputcsv($output, array_keys($rows[0]));
        foreach ($rows as $row) {
            fputcsv($output, $row);
        }
    }
    fclose($output);
    exit;
}

$pageTitle = 'Reports';
$currentModule = 'reports';
require_once __DIR__ . '/../templates/header.php';
?>
<section class="card">
    <div class="page-actions">
        <div><h2><?= htmlspecialchars($definition['title']) ?></h2><p>Operational information generated from the live database.</p></div>
        <a class="button" href="<?= BASE_URL ?>/reports.php?report=<?= urlencode($reportKey) ?>&export=csv">Export CSV</a>
    </div>
    <div class="filter-links">
        <?php foreach ($reportDefinitions as $key => $report): ?>
            <a class="<?= $key === $reportKey ? 'active' : '' ?>" href="<?= BASE_URL ?>/reports.php?report=<?= urlencode($key) ?>"><?= htmlspecialchars($report['title']) ?></a>
        <?php endforeach; ?>
        <?php if (can('manage_users')): ?><a href="<?= BASE_URL ?>/activity.php">Activity Log</a><?php endif; ?>
    </div>
    <div class="table-container"><table class="data-table">
        <?php if ($rows !== []): ?>
            <thead><tr><?php foreach (array_keys($rows[0]) as $column): ?><th><?= htmlspecialchars(ucwords(str_replace('_', ' ', $column))) ?></th><?php endforeach; ?></tr></thead>
        <?php endif; ?>
        <tbody>
        <?php if ($rows === []): ?><tr><td class="empty-state">No report data is available.</td></tr><?php endif; ?>
        <?php foreach ($rows as $row): ?><tr><?php foreach ($row as $value): ?><td><?= htmlspecialchars((string) ($value ?? '—')) ?></td><?php endforeach; ?></tr><?php endforeach; ?>
        </tbody>
    </table></div>
</section>
<?php require_once __DIR__ . '/../templates/footer.php'; ?>
