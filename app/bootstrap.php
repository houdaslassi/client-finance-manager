<?php
// Autoload classes
spl_autoload_register(function ($class) {
    $prefixes = [
        'App\\' => 'app/',
        'Core\\' => 'app/Core/',
    ];
    foreach ($prefixes as $prefix => $dir) {
        if (strpos($class, $prefix) === 0) {
            $relative = str_replace('\\', '/', substr($class, strlen($prefix)));
            $file = __DIR__ . '/../' . $dir . $relative . '.php';
            if (file_exists($file)) {
                require $file;
                return;
            }
        }
    }
}); 