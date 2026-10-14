<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../src/permissions.php';
require_once __DIR__ . '/../src/csrf.php';
require_once __DIR__ . '/../src/flash.php';
require_once __DIR__ . '/../src/activity.php';

requirePermission('manage_production');
$pdo = getDatabaseConnection();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? null)) {
        http_response_code(403);
        exit('Invalid form request.');
    }
    $kind = (string) ($_POST['kind'] ?? '');
    $batchId = filter_input(INPUT_POST, 'batch_id', FILTER_VALIDATE_INT) ?: 0;
    $itemId = filter_input(INPUT_POST, 'item_id', FILTER_VALIDATE_INT) ?: 0;
    $quantity = filter_input(INPUT_POST, 'quantity_used', FILTER_VALIDATE_FLOAT);

    try {
        if ($batchId < 1 || $itemId < 1 || $quantity === false || $quantity <= 0) {
            throw new RuntimeException('Enter a valid batch, item and quantity.');
        }
        $pdo->beginTransaction();
        if ($kind === 'leaf') {
            $stockStatement = $pdo->prepare('CALL sp_adjust_tea_leaf_stock(:item, :change)');
            $stockStatement->execute(['item' => $itemId, 'change' => -$quantity]);
            $stockStatement->closeCursor();
            $pdo->prepare(
                'INSERT INTO batch_tea_leaf (batch_id, tea_leaf_id, quantity_used)
                 VALUES (:batch, :item, :quantity)
                 ON DUPLICATE KEY UPDATE quantity_used=quantity_used+VALUES(quantity_used)'
            )->execute(['batch' => $batchId, 'item' => $itemId, 'quantity' => $quantity]);
        } elseif ($kind === 'material') {
            $stockStatement = $pdo->prepare('CALL sp_adjust_material_stock(:item, :change)');
            $stockStatement->execute(['item' => $itemId, 'change' => -$quantity]);
            $stockStatement->closeCursor();
            $pdo->prepare(
                'INSERT INTO batch_other_material (batch_id, material_id, quantity_used)
                 VALUES (:batch, :item, :quantity)
                 ON DUPLICATE KEY UPDATE quantity_used=quantity_used+VALUES(quantity_used)'
            )->execute(['batch' => $batchId, 'item' => $itemId, 'quantity' => $quantity]);
        } else {
            throw new RuntimeException('Invalid inventory type.');
        }
        logActivity($pdo, 'ALLOCATE_STOCK', "Allocated {$quantity} {$kind} stock to batch #{$batchId}");
        $pdo->commit();
        setFlashMessage('success', 'Inventory was allocated to the batch.');
    } catch (Throwable $exception) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        setFlashMessage('error', $exception instanceof RuntimeException ? $exception->getMessage() : 'Stock could not be allocated. Check available stock.');
    }
    header('Location: ' . BASE_URL . '/batch_usage.php');
    exit;
}

$batches = $pdo->query("SELECT batch_id, batch_code FROM production_batch WHERE batch_status <> 'Cancelled' ORDER BY production_date DESC")->fetchAll();
$leaves = $pdo->query("SELECT tea_leaf_id id, CONCAT(tea_leaf_code,' — ',leaf_type,' (',current_stock,' ',unit,')') label FROM tea_leaf_inventory WHERE current_stock>0 ORDER BY tea_leaf_code")->fetchAll();
$materials = $pdo->query("SELECT material_id id, CONCAT(material_code,' — ',material_name,' (',current_stock,' ',unit,')') label FROM other_material WHERE current_stock>0 ORDER BY material_code")->fetchAll();
$usage = $pdo->query(
    "SELECT pb.batch_code, 'Tea leaf' item_type, t.tea_leaf_code item_code, t.leaf_type item_name, b.quantity_used, t.unit
     FROM batch_tea_leaf b JOIN production_batch pb ON pb.batch_id=b.batch_id JOIN tea_leaf_inventory t ON t.tea_leaf_id=b.tea_leaf_id
     UNION ALL
     SELECT pb.batch_code, 'Material', m.material_code, m.material_name, b.quantity_used, m.unit
     FROM batch_other_material b JOIN production_batch pb ON pb.batch_id=b.batch_id JOIN other_material m ON m.material_id=b.material_id
     ORDER BY batch_code, item_type"
)->fetchAll();

$pageTitle = 'Batch Material Usage';
$currentModule = 'production';
require_once __DIR__ . '/../templates/header.php';
?>
<div class="subnav">
    <a href="<?= BASE_URL ?>/records.php?entity=batches">Batches</a>
    <a href="<?= BASE_URL ?>/records.php?entity=stages">Stages</a>
    <a href="<?= BASE_URL ?>/records.php?entity=processes">Processes</a>
    <a class="active" href="<?= BASE_URL ?>/batch_usage.php">Material Usage</a>
</div>
<section class="card">
    <h2>Allocate Inventory to a Batch</h2>
    <p>The operation records usage and deducts the quantity from available stock in one transaction.</p>
    <form class="entity-form" method="post">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrfToken()) ?>">
        <div class="form-group"><label>Batch *</label><select name="batch_id" required><option value="">Select…</option><?php foreach ($batches as $batch): ?><option value="<?= (int) $batch['batch_id'] ?>"><?= htmlspecialchars($batch['batch_code']) ?></option><?php endforeach; ?></select></div>
        <div class="form-group"><label>Inventory Type *</label><select name="kind" id="usage-kind" required><option value="leaf">Tea Leaf</option><option value="material">Other Material</option></select></div>
        <div class="form-group"><label>Tea Leaf / Material *</label><select name="item_id" id="usage-item" required>
            <?php foreach ($leaves as $item): ?><option data-kind="leaf" value="<?= (int) $item['id'] ?>"><?= htmlspecialchars($item['label']) ?></option><?php endforeach; ?>
            <?php foreach ($materials as $item): ?><option data-kind="material" value="<?= (int) $item['id'] ?>"><?= htmlspecialchars($item['label']) ?></option><?php endforeach; ?>
        </select></div>
        <div class="form-group"><label>Quantity Used *</label><input type="number" min="0.01" step="0.01" name="quantity_used" required></div>
        <div class="form-actions"><button class="button">Allocate Stock</button></div>
    </form>
</section>
<section class="card top-gap"><h2>Recorded Usage</h2><div class="table-container"><table class="data-table">
    <thead><tr><th>Batch</th><th>Type</th><th>Code</th><th>Item</th><th>Quantity Used</th></tr></thead>
    <tbody><?php foreach ($usage as $row): ?><tr><td><?= htmlspecialchars($row['batch_code']) ?></td><td><?= htmlspecialchars($row['item_type']) ?></td><td><?= htmlspecialchars($row['item_code']) ?></td><td><?= htmlspecialchars($row['item_name']) ?></td><td><?= htmlspecialchars($row['quantity_used'] . ' ' . $row['unit']) ?></td></tr><?php endforeach; ?></tbody>
</table></div></section>
<script>
const kind = document.getElementById('usage-kind');
const item = document.getElementById('usage-item');
function filterUsageItems() {
    [...item.options].forEach(option => option.hidden = option.dataset.kind !== kind.value);
    const first = [...item.options].find(option => !option.hidden);
    if (first) item.value = first.value;
}
kind.addEventListener('change', filterUsageItems);
filterUsageItems();
</script>
<?php require_once __DIR__ . '/../templates/footer.php'; ?>
