<?php

declare(strict_types=1);

const SYSTEM_MODULES = [
    'users' => [
        'title'       => 'User Management',
        'description' => 'Manage system users, roles and account status.',
        'icon'        => '👥',
        'permission'  => 'manage_users',
        'path'        => '/users/index.php'
    ],

    'production' => [
        'title'       => 'Production Management',
        'description' => 'Manage production batches, stages and processes.',
        'icon'        => '🏭',
        'permission'  => 'manage_production',
        'path'        => '/records.php?entity=batches'
    ],

    'quality' => [
        'title'       => 'Quality Control',
        'description' => 'Record and review tea-quality inspections.',
        'icon'        => '✅',
        'permission'  => 'manage_quality',
        'path'        => '/records.php?entity=quality'
    ],

    'inventory' => [
        'title'       => 'Inventory Management',
        'description' => 'Manage tea leaves, materials and finished products.',
        'icon'        => '📦',
        'permission'  => 'manage_inventory',
        'path'        => '/records.php?entity=suppliers'
    ],

    'machines' => [
        'title'       => 'Machines and Sensors',
        'description' => 'View machines, sensors and operating conditions.',
        'icon'        => '⚙️',
        'permission'  => 'manage_machines',
        'path'        => '/monitoring.php'
    ],

    'alerts' => [
        'title'       => 'System Alerts',
        'description' => 'View and acknowledge abnormal sensor alerts.',
        'icon'        => '⚠️',
        'permission'  => 'manage_alerts',
        'path'        => '/alerts.php'
    ],

    'maintenance' => [
        'title'       => 'Maintenance',
        'description' => 'Schedule and record machine maintenance.',
        'icon'        => '🛠️',
        'permission'  => 'manage_maintenance',
        'path'        => '/records.php?entity=maintenance'
    ],

    'reports' => [
        'title'       => 'Reports',
        'description' => 'View production, quality and inventory reports.',
        'icon'        => '📊',
        'permission'  => 'view_reports',
        'path'        => '/reports.php'
    ]
];
