<?php
// Prevent multiple inclusions
if (defined('APP_CONFIG_LOADED')) {
    // If already loaded, just return the database config
    $isProduction = getenv('JAWSDB_URL') || getenv('APP_ENV') === 'production';

    if ($isProduction) {
        return ['use_jawsdb' => true];
    } else {
        return [
            'host' => getenv('DB_HOST') ?: 'localhost',
            'dbname' => getenv('DB_NAME') ?: 'client_finance_manager',
            'user' => getenv('DB_USER') ?: 'root',
            'pass' => getenv('DB_PASS') ?: '',
            'port' => (int) (getenv('DB_PORT') ?: 3306),
            'charset' => 'utf8mb4',
        ];
    }
}
define('APP_CONFIG_LOADED', true);

// Load .env file for local development
if (file_exists(__DIR__ . '/../.env')) {
    $lines = file(__DIR__ . '/../.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        if (strpos($line, '=') === false) continue;

        list($name, $value) = explode('=', $line, 2);
        $name = trim($name);
        $value = trim($value, '"');

        if (!getenv($name)) {
            putenv("{$name}={$value}");
        }
    }
}

// Detect if we're in production
$isProduction = getenv('JAWSDB_URL') || getenv('APP_ENV') === 'production';

// Application configuration
define('APP_NAME', getenv('APP_NAME') ?: 'Client Finance Manager');
define('APP_URL', getenv('APP_URL') ?: ($isProduction ? 'https://client-manager-4a181d29d6c8.herokuapp.com' : 'http://localhost:8000'));
define('APP_DEBUG', getenv('APP_DEBUG') ?: !$isProduction);
define('APP_ENV', getenv('APP_ENV') ?: ($isProduction ? 'production' : 'development'));

// Session configuration
define('SESSION_LIFETIME', (int) (getenv('SESSION_LIFETIME') ?: 3600));
ini_set('session.gc_maxlifetime', SESSION_LIFETIME);
session_set_cookie_params(SESSION_LIFETIME);

// Database configuration for your existing Database class
if ($isProduction) {
    // Production uses JAWSDB_URL (your Database class handles this)
    $databaseConfig = ['use_jawsdb' => true];
} else {
    // Local development
    $databaseConfig = [
        'host' => getenv('DB_HOST') ?: 'localhost',
        'dbname' => getenv('DB_NAME') ?: 'client_finance_manager',
        'user' => getenv('DB_USER') ?: 'root',
        'pass' => getenv('DB_PASS') ?: '',
        'port' => (int) (getenv('DB_PORT') ?: 3306),
        'charset' => 'utf8mb4',
    ];
}

// Return database config
return $databaseConfig;
