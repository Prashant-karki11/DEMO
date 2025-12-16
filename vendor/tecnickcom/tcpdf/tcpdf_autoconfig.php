<?php
/**
 * TCPDF Configuration File
 * Auto-configuration for TCPDF library
 */

// Define the path for TCPDF
$tcpdf_path = __DIR__ . '/';

// Detect the path for TCPDF resources
if (!defined('K_PATH_MAIN')) {
    define('K_PATH_MAIN', $tcpdf_path);
}

if (!defined('K_PATH_URL')) {
    define('K_PATH_URL', '../../../');
}

if (!defined('K_PATH_FONTS')) {
    define('K_PATH_FONTS', K_PATH_MAIN . 'include/barcodes/fonts/');
}

if (!defined('K_PATH_CACHE')) {
    define('K_PATH_CACHE', sys_get_temp_dir() . '/');
}

if (!defined('K_PATH_URL_CACHE')) {
    define('K_PATH_URL_CACHE', K_PATH_CACHE);
}

if (!defined('K_PATH_IMAGES')) {
    define('K_PATH_IMAGES', '');
}

// Document encryption
if (!defined('K_BLANK_IMAGE')) {
    define('K_BLANK_IMAGE', K_PATH_IMAGES . '_blank.png');
}

// Page format
if (!defined('PDF_PAGE_FORMAT')) {
    define('PDF_PAGE_FORMAT', 'A4');
}

// Page orientation (P=portrait, L=landscape)
if (!defined('PDF_PAGE_ORIENTATION')) {
    define('PDF_PAGE_ORIENTATION', 'P');
}

// Page unit (pt=point, mm=millimeter, cm=centimeter, in=inch)
if (!defined('PDF_UNIT')) {
    define('PDF_UNIT', 'mm');
}

// Default font
if (!defined('PDF_FONT_NAME_MAIN')) {
    define('PDF_FONT_NAME_MAIN', 'helvetica');
}

if (!defined('PDF_FONT_SIZE_MAIN')) {
    define('PDF_FONT_SIZE_MAIN', 10);
}

// Font for data
if (!defined('PDF_FONT_NAME_DATA')) {
    define('PDF_FONT_NAME_DATA', 'helvetica');
}

if (!defined('PDF_FONT_SIZE_DATA')) {
    define('PDF_FONT_SIZE_DATA', 8);
}

// Default monospaced font
if (!defined('PDF_FONT_MONOSPACED')) {
    define('PDF_FONT_MONOSPACED', 'courier');
}

// Margin sizes in user units
if (!defined('PDF_MARGIN_LEFT')) {
    define('PDF_MARGIN_LEFT', 15);
}

if (!defined('PDF_MARGIN_TOP')) {
    define('PDF_MARGIN_TOP', 27);
}

if (!defined('PDF_MARGIN_RIGHT')) {
    define('PDF_MARGIN_RIGHT', 15);
}

if (!defined('PDF_MARGIN_BOTTOM')) {
    define('PDF_MARGIN_BOTTOM', 25);
}

// Margin for header
if (!defined('PDF_MARGIN_HEADER')) {
    define('PDF_MARGIN_HEADER', 5);
}

// Margin for footer
if (!defined('PDF_MARGIN_FOOTER')) {
    define('PDF_MARGIN_FOOTER', 10);
}

// Image scale factor
if (!defined('PDF_IMAGE_SCALE_RATIO')) {
    define('PDF_IMAGE_SCALE_RATIO', 1.25);
}

// Cell height ratio
if (!defined('K_CELL_HEIGHT_RATIO')) {
    define('K_CELL_HEIGHT_RATIO', 1.25);
}

// cURL constants (if curl is not available)
if (!defined('CURLOPT_CONNECTTIMEOUT')) {
    define('CURLOPT_CONNECTTIMEOUT', 78);
}
if (!defined('CURLOPT_TIMEOUT')) {
    define('CURLOPT_TIMEOUT', 13);
}
if (!defined('CURLOPT_MAXREDIRS')) {
    define('CURLOPT_MAXREDIRS', 68);
}
if (!defined('CURLOPT_FOLLOWLOCATION')) {
    define('CURLOPT_FOLLOWLOCATION', 52);
}
if (!defined('CURLOPT_SSL_VERIFYPEER')) {
    define('CURLOPT_SSL_VERIFYPEER', 64);
}
if (!defined('CURLOPT_USERAGENT')) {
    define('CURLOPT_USERAGENT', 10018);
}

// Enable disk caching of HTML pages
if (!defined('K_TCPDF_CALLS_IN_HTML')) {
    define('K_TCPDF_CALLS_IN_HTML', true);
}

// Throw exception instead of die
if (!defined('K_TCPDF_THROW_EXCEPTION_ERROR')) {
    define('K_TCPDF_THROW_EXCEPTION_ERROR', false);
}

// Timezone for TCPDF
if (!defined('date_default_timezone_set')) {
    if (function_exists('date_default_timezone_set') && function_exists('date_default_timezone_get')) {
        @date_default_timezone_set(date_default_timezone_get());
    }
}

?>
