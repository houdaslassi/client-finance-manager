<?php
// API Entry Point - Clean and Simple
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../app/bootstrap.php';

// Import API controllers
use App\Controllers\API\MovementAPIController;
use App\Controllers\API\AuthAPIController;

// Simple API router
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$method = $_SERVER['REQUEST_METHOD'];

try {
    switch ($path) {
        case '/api':
            // API Documentation
            header('Content-Type: application/json');
            echo json_encode([
                'success' => true,
                'data' => [
                    'name' => 'Client Finance Manager API',
                    'description' => 'Professional API for client movement management',
                    'version' => '1.0',
                    'authentication' => [
                        'login' => 'POST /api/auth/login',
                        'logout' => 'POST /api/auth/logout',
                        'token_usage' => 'Authorization: Bearer <token>'
                    ],
                    'endpoints' => [
                        'POST /api/auth/login' => 'Login and get access token',
                        'POST /api/auth/logout' => 'Logout and revoke token',
                        'GET /api/auth/me' => 'Get current user info',
                        'GET /api/movements?client_id=X' => 'Get user movements (requires auth)'
                    ],
                    'example_usage' => [
                        'login' => 'curl -X POST /api/auth/login -d {"username":"admin","password":"password"}',
                        'api_call' => 'curl -H "Authorization: Bearer <token>" /api/movements?client_id=1'
                    ]
                ]
            ], JSON_PRETTY_PRINT);
            break;

        // Authentication endpoints
        case '/api/auth/login':
            $controller = new AuthAPIController();
            $controller->login();
            break;

        case '/api/auth/logout':
            $controller = new AuthAPIController();
            $controller->logout();
            break;

        case '/api/auth/logout-all':
            $controller = new AuthAPIController();
            $controller->logoutAll();
            break;

        case '/api/auth/me':
            $controller = new AuthAPIController();
            $controller->me();
            break;

        case '/api/movements':
            $controller = new MovementAPIController();

            if ($method === 'GET') {
                $controller->getUserMovements();
            } elseif ($method === 'POST') {
                $controller->createMovement();
            } else {
                http_response_code(405);
                echo json_encode(['success' => false, 'error' => 'Method not allowed']);
            }
            break;

        default:
            http_response_code(404);
            echo json_encode([
                'success' => false,
                'error' => 'Endpoint not found',
                'available_endpoints' => ['/api', '/api/movements']
            ]);
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Server error occurred',
        'message' => defined('APP_DEBUG') && APP_DEBUG ? $e->getMessage() : 'Please try again later'
    ]);
}
