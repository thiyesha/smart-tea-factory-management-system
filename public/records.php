<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../src/permissions.php';
require_once __DIR__ . '/../src/entities.php';
require_once __DIR__ . '/../src/csrf.php';

$entityKey = (string) ($_GET['entity'] ?? '');
$config = entityConfig($entityKey);
requirePermission($config['permission']);

$pdo = getDatabaseConnection();
$search = trim((string) ($_GET['search'] ?? ''));
$editId = filter_input(INPUT_GET, 'edit', FILTER_VALIDATE_INT) ?: 0;
$record = [];

if ($editId > 0) {
    $statement = $pdo->prepare(
        "SELECT * FROM `{$config['table']}` WHERE `{$config['pk']}` = :id"
    );
    $statement->execute(['id' => $editId]);
    $record = $statement->fetch() ?: [];
}

$where = '';
$parameters = [];
if ($search !== '' && $config['search'] !== []) {
    $parts = [];
    foreach ($config['search'] as $index => $column) {
        $parts[] = "`{$column}` LIKE :search{$index}";
        $parameters["search{$index}"] = '%' . $search . '%';
    }
    $where = ' WHERE ' . implode(' OR ', $parts);
}

$rowsStatement = $pdo->prepare(
    "SELECT * FROM `{$config['table']}`{$where}
     ORDER BY `{$config['pk']}` DESC LIMIT 250"
);
$rowsStatement->execute($parameters);
$rows = $rowsStatement->fetchAll();

$foreignOptions = [];
foreach ($config['fields'] as $column => $field) {
    if (($field['type'] ?? '') === 'foreign') {
        $foreignOptions[$column] = $pdo->query(
            "SELECT `{$field['pk']}` AS option_id, `{$field['display']}` AS option_label
             FROM `{$field['table']}` ORDER BY `{$field['display']}`"
        )->fetchAll();
    }
}

$pageTitle = $config['title'];
$currentModule = $config['module'];
require_once __DIR__ . '/../templates/header.php';
?>

<div class="subnav">
    <?php foreach (ENTITY_CONFIG as $key => $item): ?>
        <?php if ($item['module'] === $config['module'] && can($item['permission'])): ?>
            <a class="<?= $key === $entityKey ? 'active' : '' ?>"
               href="<?= BASE_URL ?>/records.php?entity=<?= urlencode($key) ?>">
                <?= htmlspecialchars($item['title']) ?>
            </a>
        <?php endif; ?>
    <?php endforeach; ?>
</div>
<?php if ($config['module'] === 'production'): ?>
    <div class="subnav"><a href="<?= BASE_URL ?>/batch_usage.php">Batch Material Usage</a></div>
<?php elseif ($config['module'] === 'machines'): ?>
    <div class="subnav"><a href="<?= BASE_URL ?>/monitoring.php">Live Sensor Readings</a></div>
<?php endif; ?>

<section class="card">
    <div class="page-actions">
        <div>
            <h2><?= htmlspecialchars($editId ? 'Edit ' . $config['title'] : 'Add ' . $config['title']) ?></h2>
            <p>Fields marked with * are required.</p>
        </div>
        <span class="role-badge"><?= count($rows) ?> records</span>
    </div>

    <form class="entity-form" method="post" action="<?= BASE_URL ?>/save_record.php">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrfToken()) ?>">
        <input type="hidden" name="entity" value="<?= htmlspecialchars($entityKey) ?>">
        <input type="hidden" name="id" value="<?= $editId ?>">

        <?php foreach ($config['fields'] as $column => $field): ?>
            <?php if (($field['type'] ?? '') === 'current_user'): ?>
                <input type="hidden" name="<?= $column ?>" value="<?= (int) ($record[$column] ?? currentUser()['user_id']) ?>">
                <?php continue; ?>
            <?php endif; ?>
            <?php $value = (string) ($record[$column] ?? ''); ?>
            <div class="form-group">
                <label for="<?= $column ?>">
                    <?= htmlspecialchars($field['label']) ?><?= !empty($field['required']) ? ' *' : '' ?>
                </label>
                <?php if (($field['type'] ?? '') === 'textarea'): ?>
                    <textarea id="<?= $column ?>" name="<?= $column ?>" <?= !empty($field['required']) ? 'required' : '' ?>><?= htmlspecialchars($value) ?></textarea>
                <?php elseif (($field['type'] ?? '') === 'select'): ?>
                    <select id="<?= $column ?>" name="<?= $column ?>" <?= !empty($field['required']) ? 'required' : '' ?>>
                        <?php foreach ($field['options'] as $option): ?>
                            <option value="<?= htmlspecialchars($option) ?>" <?= $value === $option ? 'selected' : '' ?>><?= htmlspecialchars($option) ?></option>
                        <?php endforeach; ?>
                    </select>
                <?php elseif (($field['type'] ?? '') === 'foreign'): ?>
                    <select id="<?= $column ?>" name="<?= $column ?>" required>
                        <option value="">Select…</option>
                        <?php foreach ($foreignOptions[$column] as $option): ?>
                            <option value="<?= (int) $option['option_id'] ?>" <?= $value === (string) $option['option_id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($option['option_label']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                <?php else: ?>
                    <?php
                    $type = $field['type'] ?? 'text';
                    if ($type === 'datetime-local' && $value !== '') {
                        $value = str_replace(' ', 'T', substr($value, 0, 16));
                    }
                    ?>
                    <input id="<?= $column ?>" name="<?= $column ?>" type="<?= htmlspecialchars($type) ?>"
                           value="<?= htmlspecialchars($value) ?>"
                           <?= isset($field['step']) ? 'step="' . htmlspecialchars($field['step']) . '"' : '' ?>
                           <?= !empty($field['required']) ? 'required' : '' ?>>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
        <div class="form-actions">
            <button class="button" type="submit"><?= $editId ? 'Save Changes' : 'Create Record' ?></button>
            <?php if ($editId): ?>
                <a class="button button-secondary" href="<?= BASE_URL ?>/records.php?entity=<?= urlencode($entityKey) ?>">Cancel</a>
            <?php endif; ?>
        </div>
    </form>
</section>

<section class="card top-gap">
    <form class="search-form" method="get">
        <input type="hidden" name="entity" value="<?= htmlspecialchars($entityKey) ?>">
        <input type="search" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Search records">
        <button class="button">Search</button>
    </form>
    <div class="table-container">
        <table class="data-table">
            <thead><tr>
                <?php foreach ($config['fields'] as $field): ?><th><?= htmlspecialchars($field['label']) ?></th><?php endforeach; ?>
                <th>Actions</th>
            </tr></thead>
            <tbody>
            <?php if ($rows === []): ?>
                <tr><td class="empty-state" colspan="<?= count($config['fields']) + 1 ?>">No records found.</td></tr>
            <?php endif; ?>
            <?php foreach ($rows as $row): ?>
                <tr>
                    <?php foreach ($config['fields'] as $column => $field): ?>
                        <td><?= htmlspecialchars((string) ($row[$column] ?? '—')) ?></td>
                    <?php endforeach; ?>
                    <td class="actions-cell">
                        <a class="button button-small" href="<?= BASE_URL ?>/records.php?entity=<?= urlencode($entityKey) ?>&edit=<?= (int) $row[$config['pk']] ?>">Edit</a>
                        <form method="post" action="<?= BASE_URL ?>/delete_record.php" onsubmit="return confirm('Delete this record?');">
                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrfToken()) ?>">
                            <input type="hidden" name="entity" value="<?= htmlspecialchars($entityKey) ?>">
                            <input type="hidden" name="id" value="<?= (int) $row[$config['pk']] ?>">
                            <button class="button button-small button-danger">Delete</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>

<?php require_once __DIR__ . '/../templates/footer.php'; ?>
