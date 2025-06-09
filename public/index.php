<?php
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../app/bootstrap.php';

// Start session
session_start();

// Get the current route
$route = $_SERVER['REQUEST_URI'];
$route = parse_url($route, PHP_URL_PATH);

// Handle API routes FIRST (before web routes)
if (strpos($route, '/api') === 0) {
    require_once __DIR__ . '/api.php';
    exit;
}

// Define routes (your existing routes)
$routes = [
    // Auth routes
    '/login' => ['controller' => 'AuthController', 'action' => 'login'],
    '/logout' => ['controller' => 'AuthController', 'action' => 'logout'],

    // Home routes
    '/' => ['controller' => 'HomeController', 'action' => 'index'],
    '/dashboard' => ['controller' => 'HomeController', 'action' => 'dashboard'],

    // Client routes
    '/clients' => ['controller' => 'ClientController', 'action' => 'index'],
    '/clients/create' => ['controller' => 'ClientController', 'action' => 'create'],
    '/clients/{id}' => ['controller' => 'ClientController', 'action' => 'show'],
    '/clients/{id}/edit' => ['controller' => 'ClientController', 'action' => 'edit'],
    '/clients/{id}/delete' => ['controller' => 'ClientController', 'action' => 'delete'],

    // Movement routes
    '/movements' => ['controller' => 'MovementController', 'action' => 'index'],
    '/movements/create' => ['controller' => 'MovementController', 'action' => 'create'],
    '/movements/store' => ['controller' => 'MovementController', 'action' => 'store'],
    '/movements/{id}' => ['controller' => 'MovementController', 'action' => 'show'],
    '/movements/{id}/edit' => ['controller' => 'MovementController', 'action' => 'edit'],
    '/movements/{id}/update' => ['controller' => 'MovementController', 'action' => 'update'],
    '/movements/{id}/delete' => ['controller' => 'MovementController', 'action' => 'delete']
];

error_log("Current route: " . $route);
error_log("Request method: " . $_SERVER['REQUEST_METHOD']);
error_log("Available routes: " . print_r($routes, true));

// Check if route exists
if (isset($routes[$route])) {
    error_log("Route matched: " . $route);
    $controller = $routes[$route]['controller'];
    $action = $routes[$route]['action'];

    error_log("Controller: " . $controller);
    error_log("Action: " . $action);

    // Create controller instance
    $controllerClass = "App\\Controllers\\{$controller}";
    error_log("Creating controller instance: " . $controllerClass);
    $controllerInstance = new $controllerClass();

    // Call the action
    error_log("Calling action: " . $action);
    $controllerInstance->$action();
    error_log("Action completed");
} else {
    error_log("No direct route match found for: " . $route);

    // Check for dynamic routes
    foreach ($routes as $pattern => $routeInfo) {
        $pattern = str_replace('{id}', '(\d+)', $pattern);
        if (preg_match("#^{$pattern}$#", $route, $matches)) {
            $controller = $routeInfo['controller'];
            $action = $routeInfo['action'];

            error_log("Matched dynamic route: " . $pattern);
            error_log("Controller: " . $controller);
            error_log("Action: " . $action);

            // Create controller instance
            $controllerClass = "App\\Controllers\\{$controller}";
            error_log("Creating controller instance: " . $controllerClass);
            $controllerInstance = new $controllerClass();

            // Call the action with the ID parameter
            error_log("Calling action: " . $action . " with ID: " . $matches[1]);
            $controllerInstance->$action($matches[1]);
            error_log("Action completed");
            exit;
        }
    }

    error_log("No route match found at all for: " . $route);
    // Route not found
    header("HTTP/1.0 404 Not Found");
    echo "404 Not Found";
}
