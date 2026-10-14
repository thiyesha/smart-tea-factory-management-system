<?php

declare(strict_types=1);

/*==============================================================
  DATABASE CONFIGURATION
==============================================================*/

const DB_HOST = '127.0.0.1';
const DB_PORT = '3306';
const DB_NAME = 'tea_factory_db';
const DB_USER = 'root';

/*
 * If your MySQL root account has no password, leave this blank.
 * Otherwise, enter the same password used by MySQL Workbench.
 */
const DB_PASS = '';


/*==============================================================
  CREATE PDO DATABASE CONNECTION
==============================================================*/

function getDatabaseConnection(): PDO
{
    static $pdo = null;

    if ($pdo instanceof PDO) {
        return $pdo;
    }

    $dsn = sprintf(
        'mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4',
        DB_HOST,
        DB_PORT,
        DB_NAME
    );

    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false
    ];

    try {
        $pdo = new PDO(
            $dsn,
            DB_USER,
            DB_PASS,
            $options
        );

        return $pdo;

    } catch (PDOException $exception) {
        error_log(
            'Database connection failed: ' .
            $exception->getMessage()
        );

        throw new RuntimeException(
            'Unable to connect to the database.'
        );
    }
}