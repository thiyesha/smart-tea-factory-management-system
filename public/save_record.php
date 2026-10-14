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
$values = [];
$errors = [];

foreach ($config['fields'] as $column => $field) {
    $value = trim((string) ($_POST[$column] ?? ''));
    if (($field['type'] ?? '') === 'datetime-local' && $value !== '') {
        $value = str_replace('T', ' ', $value) . ':00';
    }
    if (!empty($field['required']) && $value === '') {
        $errors[] = $field['label'] . ' is required.';
    }
    if (($field['type'] ?? '') === 'select' && !in_array($value, $field['options'], true)) {
        $errors[] = $field['label'] . ' is invalid.';
    }
    if (($field['type'] ?? '') === 'email' && $value !== '' && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Enter a valid email address.';
    }
    $values[$column] = $value === '' ? null : $value;
}

if ($errors !== []) {
    setFlashMessage('error', implode(' ', $errors));
    header('Location: ' . BASE_URL . '/records.php?entity=' . urlencode($entityKey) . ($id ? '&edit=' . $id : ''));
    exit;
}

$pdo = getDatabaseConnection();
try {
    $pdo->beginTransaction();
    if ($id > 0) {
        $sets = array_map(static fn(string $column): string => "`{$column}` = :{$column}", array_keys($values));
        $values['record_id'] = $id;
        $statement = $pdo->prepare(
            "UPDATE `{$config['table']}` SET " . implode(', ', $sets) .
            " WHERE `{$config['pk']}` = :record_id"
        );
        $statement->execute($values);
        $action = 'UPDATE_RECORD';
    } else {
        $columns = array_keys($values);
        $statement = $pdo->prepare(
            "INSERT INTO `{$config['table']}` (`" . implode('`, `', $columns) . '`) VALUES (:' .
            implode(', :', $columns) . ')'
        );
        $statement->execute($values);
        $id = (int) $pdo->lastInsertId();
        $action = 'CREATE_RECORD';
    }
    logActivity($pdo, $action, "{$config['title']} record #{$id}");
    $pdo->commit();
    setFlashMessage('success', $config['title'] . ' saved successfully.');
} catch (Throwable $exception) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log($exception->getMessage());
    setFlashMessage('error', 'The record could not be saved. Check for duplicate values or invalid relationships.');
}

header('Location: ' . BASE_URL . '/records.php?entity=' . urlencode($entityKey));
exit;
