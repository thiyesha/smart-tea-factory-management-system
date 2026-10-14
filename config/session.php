<?php

declare(strict_types=1);

require_once __DIR__ . '/app.php';

if (session_status() === PHP_SESSION_NONE) {
    $usingHttps =
        !empty($_SERVER['HTTPS'])
        && $_SERVER['HTTPS'] !== 'off';

    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/smart_tea_factory/',
        'domain'   => '',
        'secure'   => $usingHttps,
        'httponly' => true,
        'samesite' => 'Lax'
    ]);

    session_start();
}