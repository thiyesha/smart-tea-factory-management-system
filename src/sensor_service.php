<?php

declare(strict_types=1);

/**
 * Safe operating ranges used by both the simulator and physical-device API.
 * Replace these values with factory-approved limits before real deployment.
 */
const SENSOR_LIMITS = [
    'Temperature' => ['minimum' => 15.0, 'maximum' => 95.0],
    'Humidity' => ['minimum' => 20.0, 'maximum' => 90.0],
    'Speed' => ['minimum' => 0.0, 'maximum' => 1500.0],
    'Vibration' => ['minimum' => 0.0, 'maximum' => 12.0],
];

function sensorLimits(string $type): array
{
    return SENSOR_LIMITS[$type] ?? [
        'minimum' => -PHP_FLOAT_MAX,
        'maximum' => PHP_FLOAT_MAX,
    ];
}

/**
 * Store one reading and create an alert when it is outside the safe range.
 */
function recordSensorReading(
    PDO $pdo,
    int $sensorId,
    string $sensorName,
    string $sensorType,
    string $unit,
    float $value,
    ?string $readingTime = null
): array {
    $limits = sensorLimits($sensorType);
    $isAbnormal =
        $value < $limits['minimum']
        || $value > $limits['maximum'];

    $readingStatement = $pdo->prepare(
        'INSERT INTO sensor_data
            (sensor_id, reading_value, reading_time)
         VALUES
            (:sensor_id, :reading_value, COALESCE(:reading_time, NOW()))'
    );
    $readingStatement->execute([
        'sensor_id' => $sensorId,
        'reading_value' => $value,
        'reading_time' => $readingTime,
    ]);
    $readingId = (int) $pdo->lastInsertId();

    $alertCreated = false;
    if ($isAbnormal) {
        /*
         * Avoid producing a duplicate alert on every simulation cycle.
         * A new alert is allowed after the previous one is resolved.
         */
        $duplicateStatement = $pdo->prepare(
            "SELECT COUNT(*)
             FROM alert
             WHERE sensor_id = :sensor_id
               AND alert_status IN ('Active', 'Acknowledged')
               AND alert_type = :alert_type"
        );
        $alertType = $sensorType . ' threshold';
        $duplicateStatement->execute([
            'sensor_id' => $sensorId,
            'alert_type' => $alertType,
        ]);

        if ((int) $duplicateStatement->fetchColumn() === 0) {
            $distance = $value > $limits['maximum']
                ? $value - $limits['maximum']
                : $limits['minimum'] - $value;
            $range = max(
                1.0,
                $limits['maximum'] - $limits['minimum']
            );
            $severity = ($distance / $range) >= 0.20
                ? 'Critical'
                : 'High';

            $alertStatement = $pdo->prepare(
                'INSERT INTO alert
                    (sensor_id, alert_type, severity, alert_message)
                 VALUES
                    (:sensor_id, :alert_type, :severity, :message)'
            );
            $alertStatement->execute([
                'sensor_id' => $sensorId,
                'alert_type' => $alertType,
                'severity' => $severity,
                'message' => sprintf(
                    '%s recorded %.3f %s; safe range is %.3f–%.3f %s.',
                    $sensorName,
                    $value,
                    $unit,
                    $limits['minimum'],
                    $limits['maximum'],
                    $unit
                ),
            ]);
            $alertCreated = true;
        }
    }

    return [
        'reading_id' => $readingId,
        'value' => $value,
        'abnormal' => $isAbnormal,
        'alert_created' => $alertCreated,
    ];
}

/**
 * Generate a plausible value. About 8% of readings intentionally exceed the
 * safe range so the alert workflow can be demonstrated.
 */
function simulatedSensorValue(string $type): float
{
    $limits = sensorLimits($type);
    $minimum = $limits['minimum'];
    $maximum = $limits['maximum'];
    $abnormal = random_int(1, 100) <= 8;

    if ($abnormal) {
        $aboveMaximum = $minimum <= 0 || random_int(0, 1) === 1;
        if ($aboveMaximum) {
            return round(
                $maximum + max(1.0, $maximum * random_int(6, 25) / 100),
                3
            );
        }

        return round(
            max(0.0, $minimum - max(1.0, $minimum * random_int(6, 25) / 100)),
            3
        );
    }

    /*
     * Use the middle 70% of the valid range for normal values.
     */
    $span = $maximum - $minimum;
    $low = $minimum + ($span * 0.15);
    $high = $maximum - ($span * 0.15);

    return round(
        random_int((int) round($low * 1000), (int) round($high * 1000))
        / 1000,
        3
    );
}
