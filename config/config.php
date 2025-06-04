<?php
// Database configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'client_finance_manager');
define('DB_USER', 'root');
define('DB_PASS', '');

// Application configuration
define('APP_NAME', 'Client Finance Manager');
define('APP_URL', 'http://localhost:8000');

// Session configuration
define('SESSION_LIFETIME', 3600); // 1 hour
ini_set('session.gc_maxlifetime', SESSION_LIFETIME);
session_set_cookie_params(SESSION_LIFETIME); 