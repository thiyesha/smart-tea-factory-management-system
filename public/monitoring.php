<?php

declare(strict_types=1);

require_once __DIR__ . '/../src/permissions.php';
require_once __DIR__ . '/../src/csrf.php';

requirePermission('manage_machines');

$pageTitle = 'Machines and Sensors';
$currentModule = 'machines';
require_once __DIR__ . '/../templates/header.php';
?>

<div class="subnav">
    <a href="<?= BASE_URL ?>/records.php?entity=machines">Manage Machines</a>
    <a href="<?= BASE_URL ?>/records.php?entity=sensors">Manage Sensors</a>
    <a class="active" href="<?= BASE_URL ?>/monitoring.php">Live Readings</a>
</div>

<section class="card simulation-panel">
    <div class="page-actions">
        <div>
            <h2>Automatic Sensor Simulation</h2>
            <p>
                The simulator creates a reading for every active sensor every
                five seconds. Abnormal readings automatically generate alerts.
            </p>
        </div>

        <div class="simulation-actions">
            <button id="start-simulation" class="button" type="button">
                Start Simulation
            </button>

            <button
                id="stop-simulation"
                class="button button-danger"
                type="button"
            >
                Stop Simulation
            </button>
        </div>
    </div>

    <div class="simulation-status">
        <span id="simulation-indicator" class="status-dot"></span>

        <strong id="simulation-status-text">Starting…</strong>

        <span id="simulation-message">
            Preparing the automatic sensor feed.
        </span>
    </div>

    <p class="form-help">
        The browser page acts as the development IoT simulator. Keep this page
        open while demonstrating automatic readings. A real ESP32 can use
        <code><?= htmlspecialchars(BASE_URL) ?>/api/ingest.php</code>.
    </p>
</section>

<section class="stats-grid top-gap">
    <article class="stat-card">
        <span>📡</span>
        <div>
            <strong id="active-sensor-count">0</strong>
            <small>Active sensors</small>
        </div>
    </article>

    <article class="stat-card">
        <span>✅</span>
        <div>
            <strong id="normal-reading-count">0</strong>
            <small>Normal readings</small>
        </div>
    </article>

    <article class="stat-card">
        <span>⚠️</span>
        <div>
            <strong id="abnormal-reading-count">0</strong>
            <small>Abnormal readings</small>
        </div>
    </article>

    <article class="stat-card">
        <span>🕒</span>
        <div>
            <strong id="last-cycle-time">—</strong>
            <small>Last simulation cycle</small>
        </div>
    </article>
</section>

<section class="card">
    <div class="page-actions">
        <div>
            <h2>Live Machine Conditions</h2>
            <p>The table refreshes automatically without reloading the page.</p>
        </div>

        <a class="button button-secondary" href="<?= BASE_URL ?>/alerts.php">
            Open Alert Centre
        </a>
    </div>

    <div class="table-container">
        <table class="data-table">
            <thead>
            <tr>
                <th>Machine</th>
                <th>Sensor</th>
                <th>Type</th>
                <th>Latest Reading</th>
                <th>Safe Range</th>
                <th>Recorded</th>
                <th>Condition</th>
            </tr>
            </thead>

            <tbody id="sensor-reading-body">
            <tr>
                <td colspan="7" class="empty-state">
                    Loading sensor readings…
                </td>
            </tr>
            </tbody>
        </table>
    </div>
</section>

<script>
(() => {
    const csrfToken = <?= json_encode(csrfToken()) ?>;
    const simulateUrl = <?= json_encode(BASE_URL . '/api/simulate.php') ?>;
    const readingsUrl = <?= json_encode(BASE_URL . '/api/readings.php') ?>;
    const intervalMilliseconds = 5000;

    const startButton = document.getElementById('start-simulation');
    const stopButton = document.getElementById('stop-simulation');
    const indicator = document.getElementById('simulation-indicator');
    const statusText = document.getElementById('simulation-status-text');
    const message = document.getElementById('simulation-message');
    const tableBody = document.getElementById('sensor-reading-body');

    let simulationTimer = null;
    let cycleRunning = false;

    function escapeHtml(value) {
        const element = document.createElement('div');
        element.textContent = String(value ?? '');
        return element.innerHTML;
    }

    function updateSimulatorState(running, statusMessage = '') {
        indicator.classList.toggle('running', running);
        statusText.textContent = running
            ? 'Simulation running'
            : 'Simulation stopped';
        message.textContent = statusMessage || (
            running
                ? 'Generating readings every five seconds.'
                : 'No automatic readings are currently being generated.'
        );
        startButton.disabled = running;
        stopButton.disabled = !running;
    }

    async function loadReadings() {
        try {
            const response = await fetch(readingsUrl, {
                headers: {'Accept': 'application/json'},
                cache: 'no-store'
            });
            const data = await response.json();
            if (!response.ok || !data.success) {
                throw new Error(data.message || 'Unable to load readings.');
            }

            let activeCount = 0;
            let normalCount = 0;
            let abnormalCount = 0;

            if (data.sensors.length === 0) {
                tableBody.innerHTML =
                    '<tr><td colspan="7" class="empty-state">' +
                    'No sensors have been configured.</td></tr>';
            } else {
                tableBody.innerHTML = data.sensors.map(sensor => {
                    if (sensor.sensor_status === 'Active') {
                        activeCount++;
                    }
                    if (sensor.reading_value !== null) {
                        sensor.abnormal ? abnormalCount++ : normalCount++;
                    }

                    const reading = sensor.reading_value === null
                        ? 'No data'
                        : `${Number(sensor.reading_value).toFixed(3)} ${escapeHtml(sensor.unit)}`;
                    const condition = sensor.reading_value === null
                        ? '<span class="status-badge status-inactive">Waiting</span>'
                        : sensor.abnormal
                            ? '<span class="status-badge reading-abnormal">Abnormal</span>'
                            : '<span class="status-badge status-active">Normal</span>';
                    const rowClass = sensor.abnormal ? 'abnormal-row' : '';

                    return `
                        <tr class="${rowClass}">
                            <td>${escapeHtml(sensor.machine_name)}</td>
                            <td>
                                <strong>${escapeHtml(sensor.sensor_code)}</strong>
                                <small class="block">${escapeHtml(sensor.sensor_name)}</small>
                            </td>
                            <td>${escapeHtml(sensor.sensor_type)}</td>
                            <td><strong>${reading}</strong></td>
                            <td>
                                ${Number(sensor.minimum).toFixed(3)}
                                –
                                ${Number(sensor.maximum).toFixed(3)}
                                ${escapeHtml(sensor.unit)}
                            </td>
                            <td>${escapeHtml(sensor.reading_time || '—')}</td>
                            <td>${condition}</td>
                        </tr>
                    `;
                }).join('');
            }

            document.getElementById('active-sensor-count').textContent =
                activeCount;
            document.getElementById('normal-reading-count').textContent =
                normalCount;
            document.getElementById('abnormal-reading-count').textContent =
                abnormalCount;
        } catch (error) {
            tableBody.innerHTML =
                `<tr><td colspan="7" class="empty-state">` +
                `${escapeHtml(error.message)}</td></tr>`;
        }
    }

    async function runSimulationCycle() {
        if (cycleRunning) {
            return;
        }

        cycleRunning = true;
        try {
            const body = new URLSearchParams({csrf_token: csrfToken});
            const response = await fetch(simulateUrl, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body
            });
            const data = await response.json();
            if (!response.ok || !data.success) {
                throw new Error(data.message || 'Simulation cycle failed.');
            }

            document.getElementById('last-cycle-time').textContent =
                data.generated_at.substring(11);
            message.textContent =
                `${data.readings_created} readings generated; ` +
                `${data.alerts_created} new alerts created.`;
            await loadReadings();
        } catch (error) {
            updateSimulatorState(false, error.message);
            stopSimulation();
        } finally {
            cycleRunning = false;
        }
    }

    function startSimulation() {
        if (simulationTimer !== null) {
            return;
        }

        updateSimulatorState(true);
        runSimulationCycle();
        simulationTimer = window.setInterval(
            runSimulationCycle,
            intervalMilliseconds
        );
    }

    function stopSimulation() {
        if (simulationTimer !== null) {
            window.clearInterval(simulationTimer);
            simulationTimer = null;
        }
        updateSimulatorState(false);
    }

    startButton.addEventListener('click', startSimulation);
    stopButton.addEventListener('click', stopSimulation);

    loadReadings();
    startSimulation();
})();
</script>

<?php require_once __DIR__ . '/../templates/footer.php'; ?>
