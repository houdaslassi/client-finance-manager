<?php
// Autoload classes
spl_autoload_register(function ($class) {
    // Convert namespace to full file path
    $file = __DIR__ . '/../' . str_replace('\\', '/', $class) . '.php';
    if (file_exists($file)) {
        require $file;
    }
}); 