<?php

declare(strict_types=1);

require_once __DIR__ . '/../../config/app.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../src/sensor_service.php';

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'POST is required.']);
    exit;
}

$providedKey = (string) ($_SERVER['HTTP_X_API_KEY'] ?? '');
if (
    SENSOR_API_KEY === 'change-this-sensor-api-key'
    || !hash_equals(SENSOR_API_KEY, $providedKey)
) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Invalid API key.']);
    exit;
}

$payload = json_decode((string) file_get_contents('php://input'), true);
if (!is_array($payload)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'A JSON body is required.']);
    exit;
}

$sensorCode = strtoupper(trim((string) ($payload['sensor_code'] ?? '')));
$value = filter_var($payload['value'] ?? null, FILTER_VALIDATE_FLOAT);
$readingTime = isset($payload['reading_time'])
    ? trim((string) $payload['reading_time'])
    : null;

if ($sensorCode === '' || $value === false) {
    http_response_code(422);
    echo json_encode(['success' => false, 'message' => 'sensor_code and numeric value are required.']);
    exit;
}

try {
    $pdo = getDatabaseConnection();
    $statement = $pdo->prepare(
        "SELECT sensor_id, sensor_name, sensor_type, unit
         FROM sensor
         WHERE sensor_code = :sensor_code
           AND sensor_status = 'Active'"
    );
    $statement->execute(['sensor_code' => $sensorCode]);
    $sensor = $statement->fetch();
    if (!$sensor) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Active sensor not found.']);
        exit;
    }

    $pdo->beginTransaction();
    $result = recordSensorReading(
        $pdo,
        (int) $sensor['sensor_id'],
        $sensor['sensor_name'],
        $sensor['sensor_type'],
        $sensor['unit'],
        (float) $value,
        $readingTime !== '' ? $readingTime : null
    );
    $pdo->commit();

    echo json_encode(['success' => true] + $result);
} catch (Throwable $exception) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log('Sensor ingestion failed: ' . $exception->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Reading could not be stored.']);
}
