<?php

declare(strict_types=1);

require_once __DIR__ . '/../src/permissions.php';
require_once __DIR__ . '/../src/modules.php';

$moduleKey = (string) ($_GET['name'] ?? '');

if (!isset(SYSTEM_MODULES[$moduleKey])) {
    http_response_code(404);
    exit('Module not found.');
}

$module = SYSTEM_MODULES[$moduleKey];

requirePermission($module['permission']);

$path = $module['path'] ?? '/dashboard.php';
header('Location: ' . BASE_URL . $path);
exit;
