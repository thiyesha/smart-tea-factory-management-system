<?php

declare(strict_types=1);

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../src/permissions.php';
require_once __DIR__ . '/../../src/csrf.php';
require_once __DIR__ . '/../../src/sensor_service.php';

header('Content-Type: application/json; charset=utf-8');
requirePermission('manage_machines');

if (
    $_SERVER['REQUEST_METHOD'] !== 'POST'
    || !verifyCsrfToken($_POST['csrf_token'] ?? null)
) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Invalid request.']);
    exit;
}

try {
    $pdo = getDatabaseConnection();
    $sensors = $pdo->query(
        "SELECT sensor_id, sensor_name, sensor_type, unit
         FROM sensor
         WHERE sensor_status = 'Active'
         ORDER BY sensor_id"
    )->fetchAll();

    $pdo->beginTransaction();
    $alertsCreated = 0;
    foreach ($sensors as $sensor) {
        $result = recordSensorReading(
            $pdo,
            (int) $sensor['sensor_id'],
            $sensor['sensor_name'],
            $sensor['sensor_type'],
            $sensor['unit'],
            simulatedSensorValue($sensor['sensor_type'])
        );
        $alertsCreated += $result['alert_created'] ? 1 : 0;
    }
    $pdo->commit();

    echo json_encode([
        'success' => true,
        'readings_created' => count($sensors),
        'alerts_created' => $alertsCreated,
        'generated_at' => date('Y-m-d H:i:s'),
    ]);
} catch (Throwable $exception) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log('Sensor simulation failed: ' . $exception->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'The simulation cycle failed.',
    ]);
}
