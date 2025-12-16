<?php
/**
 * Autoloader for TCPDF library
 * Allows: require_once('../vendor/autoload.php');
 */

// Register TCPDF
if (file_exists(__DIR__ . '/tecnickcom/tcpdf/tcpdf.php')) {
    require_once __DIR__ . '/tecnickcom/tcpdf/tcpdf.php';
} else {
    trigger_error('TCPDF library not found', E_USER_WARNING);
}

// Define TCPDF constants if not already defined
if (!defined('K_PATH_URL_CACHE')) {
    define('K_PATH_URL_CACHE', K_PATH_CACHE);
}

// Auto-loader for TCPDF and related classes
spl_autoload_register(function ($class) {
    // Check if it's a TCPDF class
    if (strpos($class, 'TCPDF') === 0 || strpos($class, 'tcpdf') === 0) {
        $file = __DIR__ . '/tecnickcom/tcpdf/tcpdf.php';
        if (file_exists($file)) {
            require_once $file;
            return true;
        }
    }
    return false;
});
