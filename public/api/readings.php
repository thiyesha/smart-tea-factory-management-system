<?php

declare(strict_types=1);

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../src/permissions.php';
require_once __DIR__ . '/../../src/sensor_service.php';

header('Content-Type: application/json; charset=utf-8');
requirePermission('manage_machines');

try {
    $pdo = getDatabaseConnection();
    $rows = $pdo->query(
        "SELECT
            s.sensor_id,
            s.sensor_code,
            s.sensor_name,
            s.sensor_type,
            s.unit,
            s.sensor_status,
            m.machine_name,
            d.reading_value,
            d.reading_time
         FROM sensor s
         JOIN machine m ON m.machine_id = s.machine_id
         LEFT JOIN sensor_data d
           ON d.reading_id = (
               SELECT sd.reading_id
               FROM sensor_data sd
               WHERE sd.sensor_id = s.sensor_id
               ORDER BY sd.reading_time DESC, sd.reading_id DESC
               LIMIT 1
           )
         ORDER BY m.machine_name, s.sensor_name"
    )->fetchAll();

    foreach ($rows as &$row) {
        $limits = sensorLimits($row['sensor_type']);
        $value = $row['reading_value'] !== null
            ? (float) $row['reading_value']
            : null;
        $row['abnormal'] = $value !== null && (
            $value < $limits['minimum']
            || $value > $limits['maximum']
        );
        $row['minimum'] = $limits['minimum'];
        $row['maximum'] = $limits['maximum'];
    }
    unset($row);

    echo json_encode([
        'success' => true,
        'sensors' => $rows,
        'server_time' => date('Y-m-d H:i:s'),
    ]);
} catch (Throwable $exception) {
    error_log('Reading feed failed: ' . $exception->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Unable to load readings.']);
}
