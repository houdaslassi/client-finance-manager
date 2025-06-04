<?php
// config/database.php

return [
    'host' => 'localhost',
    'dbname' => 'client_finance_manager',
    'user' => 'root',
    'pass' => '',
    'charset' => 'utf8mb4',
];

function db_connect() {
    $config = require __DIR__ . '/database.php';
    $dsn = "mysql:host={$config['host']};dbname={$config['dbname']};charset={$config['charset']}";
    try {
        $pdo = new PDO($dsn, $config['user'], $config['pass'], [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
        return $pdo;
    } catch (PDOException $e) {
        die('Database connection failed: ' . $e->getMessage());
    }
} 