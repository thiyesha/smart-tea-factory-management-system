<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../src/permissions.php';
require_once __DIR__ . '/../src/entities.php';
require_once __DIR__ . '/../src/csrf.php';
require_once __DIR__ . '/../src/flash.php';
require_once __DIR__ . '/../src/activity.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !verifyCsrfToken($_POST['csrf_token'] ?? null)) {
    http_response_code(403);
    exit('Invalid form request.');
}

$entityKey = (string) ($_POST['entity'] ?? '');
$config = entityConfig($entityKey);
requirePermission($config['permission']);
$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT) ?: 0;

try {
    $pdo = getDatabaseConnection();
    $pdo->beginTransaction();
    $statement = $pdo->prepare(
        "DELETE FROM `{$config['table']}` WHERE `{$config['pk']}` = :id"
    );
    $statement->execute(['id' => $id]);
    logActivity($pdo, 'DELETE_RECORD', "{$config['title']} record #{$id}");
    $pdo->commit();
    setFlashMessage('success', 'Record deleted.');
} catch (Throwable $exception) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    setFlashMessage('error', 'This record cannot be deleted because another record depends on it.');
}

header('Location: ' . BASE_URL . '/records.php?entity=' . urlencode($entityKey));
exit;
