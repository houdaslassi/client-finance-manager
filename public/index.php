<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../app/bootstrap.php';

// Start session
session_start();

// Define routes
$routes = [
    // Auth routes
    '/login' => ['controller' => 'AuthController', 'action' => 'login'],
    '/logout' => ['controller' => 'AuthController', 'action' => 'logout'],

    // Home routes
    '/' => ['controller' => 'HomeController', 'action' => 'index'],
    '/dashboard' => ['controller' => 'HomeController', 'action' => 'dashboard'],

    // Test routes
    '/test' => ['controller' => 'TestController', 'action' => 'index'],
    '/test/json' => ['controller' => 'TestController', 'action' => 'json'],
    '/test/redirect' => ['controller' => 'TestController', 'action' => 'redirect'],
    '/test/not-found' => ['controller' => 'TestController', 'action' => 'notFound'],
    '/test/unauthorized' => ['controller' => 'TestController', 'action' => 'unauthorized'],
    '/test/admin' => ['controller' => 'TestController', 'action' => 'admin']
];

// Get the current route
$route = $_SERVER['REQUEST_URI'];
$route = parse_url($route, PHP_URL_PATH);

// Check if route exists
if (isset($routes[$route])) {
    $controller = $routes[$route]['controller'];
    $action = $routes[$route]['action'];
    
    // Create controller instance
    $controllerClass = "App\\Controllers\\{$controller}";
    $controllerInstance = new $controllerClass();
    
    // Call the action
    $controllerInstance->$action();
} else {
    // Route not found
    header("HTTP/1.0 404 Not Found");
    echo "404 Not Found";
} 