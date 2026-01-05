<?php
// Simple autoloader
spl_autoload_register(function ($class_name) {
    $paths = [
        DIR . '/models/' . $class_name . '.php',
        DIR . '/config/' . $class_name . '.php',
        DIR . '/controllers/' . $class_name . '.php'
    ];
    
    foreach ($paths as $path) {
        if (file_exists($path)) {
            require_once $path;
            return;
        }
    }
    
    // If not found
    throw new Exception("Class '$class_name' not found");
});
?>