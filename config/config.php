<?php
// Database configuration
if (getenv('JAWSDB_URL')) {
    // Production - Use Heroku JawsDB
    $jawsdb_url = parse_url(getenv('JAWSDB_URL'));

    define('DB_HOST', $jawsdb_url['host']);
    define('DB_NAME', substr($jawsdb_url['path'], 1)); // Remove leading slash
    define('DB_USER', $jawsdb_url['user']);
    define('DB_PASS', $jawsdb_url['pass']);
} else {
    // Local development
    define('DB_HOST', 'localhost');
    define('DB_NAME', 'client_finance_manager');
    define('DB_USER', 'root');
    define('DB_PASS', '');
}

// Application configuration
if (getenv('JAWSDB_URL')) {
    define('APP_NAME', 'Client Finance Manager');
    define('APP_URL', 'https://client-manager-4a181d29d6c8.herokuapp.com');
} else {
    define('APP_NAME', 'Client Finance Manager');
    define('APP_URL', 'http://localhost:8000');
}

// Session configuration
define('SESSION_LIFETIME', 3600); // 1 hour
ini_set('session.gc_maxlifetime', SESSION_LIFETIME);
session_set_cookie_params(SESSION_LIFETIME);
