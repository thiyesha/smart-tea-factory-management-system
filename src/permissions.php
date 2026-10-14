<?php

declare(strict_types=1);

require_once __DIR__ . '/auth.php';


/*==============================================================
  ROLE PERMISSIONS
==============================================================*/

const PERMISSION_ROLES = [
    'manage_users' => [
        'Administrator'
    ],

    'manage_production' => [
        'Administrator',
        'Production Manager'
    ],

    'manage_quality' => [
        'Administrator',
        'Quality Officer'
    ],

    'manage_inventory' => [
        'Administrator',
        'Store Keeper'
    ],

    'manage_machines' => [
        'Administrator',
        'Production Manager',
        'Machine Operator'
    ],

    'manage_alerts' => [
        'Administrator',
        'Production Manager',
        'Machine Operator'
    ],

    'manage_maintenance' => [
        'Administrator',
        'Production Manager',
        'Machine Operator'
    ],

    'view_reports' => [
        'Administrator',
        'Production Manager',
        'Quality Officer',
        'Store Keeper'
    ]
];


/*==============================================================
  CHECK A PERMISSION
==============================================================*/

function can(string $permission): bool
{
    $user = currentUser();

    if ($user === null) {
        return false;
    }

    $allowedRoles = PERMISSION_ROLES[$permission] ?? [];

    return in_array(
        $user['role'],
        $allowedRoles,
        true
    );
}


/*==============================================================
  REQUIRE A PERMISSION
==============================================================*/

function requirePermission(string $permission): void
{
    requireLogin();

    if (!can($permission)) {
        header(
            'Location: ' .
            BASE_URL .
            '/unauthorized.php'
        );

        exit;
    }
}