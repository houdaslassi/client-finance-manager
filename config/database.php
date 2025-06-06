<?php
// config/database.php

if (getenv('JAWSDB_URL')) {
    // Production - Use Heroku JawsDB
    $jawsdb_url = parse_url(getenv('JAWSDB_URL'));

    return [
        'host' => $jawsdb_url['host'],
        'dbname' => substr($jawsdb_url['path'], 1), // Remove leading slash
        'user' => $jawsdb_url['user'],
        'pass' => $jawsdb_url['pass'],
        'port' => $jawsdb_url['port'],
        'charset' => 'utf8mb4',
    ];
} else {
    // Local development
    return [
        'host' => 'localhost',
        'dbname' => 'client_finance_manager',
        'user' => 'root',
        'pass' => '',
        'charset' => 'utf8mb4',
    ];
}
