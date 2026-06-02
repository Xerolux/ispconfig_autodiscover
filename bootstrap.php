<?php
/**
 * Bootstrap file for Autodiscover Service
 */

// Simple PSR-4 Autoloader
spl_autoload_register(function ($class) {
    if (strpos($class, 'Autodiscover\\') === 0) {
        $file = __DIR__ . '/src/' . str_replace('\\', '/', substr($class, 13)) . '.php';
        if (file_exists($file)) {
            require_once $file;
        }
    }
});

// Check config file exists
if (!file_exists(__DIR__ . '/config/config.php')) {
    die('Configuration file not found. Copy config/config.example.php to config/config.php');
}

// Error handling
set_error_handler(function ($errno, $errstr, $errfile, $errline) {
    error_log("[$errno] $errstr in $errfile:$errline");
    return true;
});

set_exception_handler(function ($e) {
    error_log("Exception: " . $e->getMessage() . " in " . $e->getFile() . ":" . $e->getLine());
    http_response_code(500);
    echo 'Internal Server Error';
});
