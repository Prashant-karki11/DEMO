<?php
/**
 * TCPDF Loader Wrapper
 * Handles all TCPDF initialization and constant definitions
 */

class TCPDFWrapper {
    
    public static function initialize() {
        // Set up all TCPDF constants
        self::defineConstants();
        
        // Create core font definition files if missing
        self::ensureCoreFonts();
        
        // Load TCPDF
        $tcpdf_file = __DIR__ . '/../vendor/tecnickcom/tcpdf/tcpdf.php';
        if (!file_exists($tcpdf_file)) {
            throw new Exception("TCPDF library not found at: " . $tcpdf_file);
        }
        
        require_once($tcpdf_file);
    }
    
    protected static function ensureCoreFonts() {
        $fonts_dir = K_PATH_FONTS;
        if (!is_dir($fonts_dir)) {
            @mkdir($fonts_dir, 0777, true);
        }
        
        // Create core font definition files if missing
        $core_fonts = ['courier', 'courierB', 'courierI', 'courierBI'];
        
        foreach ($core_fonts as $font) {
            $font_file = $fonts_dir . $font . '.php';
            if (!file_exists($font_file)) {
                self::createCoreFontFile($font_file, $font);
            }
        }
    }
    
    protected static function createCoreFontFile($file_path, $font_name) {
        // Core font metrics for Courier (monospace)
        $font_data = self::getCoreFontData($font_name);
        
        // Create the font definition file
        $code = '<?php' . "\n";
        $code .= '$type = ' . var_export($font_data['type'], true) . ';' . "\n";
        $code .= '$name = ' . var_export($font_data['name'], true) . ';' . "\n";
        $code .= '$desc = ' . var_export($font_data['desc'], true) . ';' . "\n";
        $code .= '$up = ' . var_export($font_data['up'], true) . ';' . "\n";
        $code .= '$ut = ' . var_export($font_data['ut'], true) . ';' . "\n";
        $code .= '$cw = ' . var_export($font_data['cw'], true) . ';' . "\n";
        
        @file_put_contents($file_path, $code);
    }
    
    protected static function getCoreFontData($font_name) {
        // Character widths for core fonts (Courier is monospace - all chars are 600 units wide)
        $cw = [];
        for ($i = 32; $i <= 255; $i++) {
            $cw[$i] = 600;
        }
        
        $font_names = [
            'courier' => 'Courier',
            'courierB' => 'Courier-Bold',
            'courierI' => 'Courier-Oblique',
            'courierBI' => 'Courier-BoldOblique',
        ];
        
        return [
            'type' => 'core',
            'name' => $font_names[$font_name] ?? $font_name,
            'desc' => [
                'Type' => 'FontDescriptor',
                'Ascent' => 750,
                'Descent' => -250,
                'CapHeight' => 700,
                'Flags' => 32,
                'FontBBox' => '[-27 -250 1122 750]',
                'ItalicAngle' => 0,
                'StemV' => 70,
                'MissingWidth' => 600,
            ],
            'up' => -100,
            'ut' => 50,
            'cw' => $cw,
        ];
    }
    
    protected static function defineConstants() {
        // Path constants
        $tcpdf_dir = __DIR__ . '/../vendor/tecnickcom/tcpdf/';
        if (!defined('K_PATH_MAIN')) {
            define('K_PATH_MAIN', $tcpdf_dir);
        }
        if (!defined('K_PATH_URL')) {
            define('K_PATH_URL', '../../../');
        }
        if (!defined('K_PATH_FONTS')) {
            // Use a writable temp directory for fonts
            $font_path = sys_get_temp_dir() . '/tcpdf_fonts/';
            if (!is_dir($font_path)) {
                @mkdir($font_path, 0777, true);
            }
            define('K_PATH_FONTS', $font_path);
        }
        if (!defined('K_PATH_CACHE')) {
            define('K_PATH_CACHE', sys_get_temp_dir() . '/');
        }
        if (!defined('K_PATH_URL_CACHE')) {
            define('K_PATH_URL_CACHE', K_PATH_CACHE);
        }
        
        // Page format
        if (!defined('PDF_PAGE_FORMAT')) {
            define('PDF_PAGE_FORMAT', 'A4');
        }
        if (!defined('PDF_PAGE_ORIENTATION')) {
            define('PDF_PAGE_ORIENTATION', 'P');
        }
        if (!defined('PDF_UNIT')) {
            define('PDF_UNIT', 'mm');
        }
        
        // Fonts
        if (!defined('PDF_FONT_NAME_MAIN')) {
            define('PDF_FONT_NAME_MAIN', 'courier');
        }
        if (!defined('PDF_FONT_SIZE_MAIN')) {
            define('PDF_FONT_SIZE_MAIN', 10);
        }
        if (!defined('PDF_FONT_NAME_DATA')) {
            define('PDF_FONT_NAME_DATA', 'courier');
        }
        if (!defined('PDF_FONT_SIZE_DATA')) {
            define('PDF_FONT_SIZE_DATA', 8);
        }
        if (!defined('PDF_FONT_MONOSPACED')) {
            define('PDF_FONT_MONOSPACED', 'courier');
        }
        
        // Margins
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
        if (!defined('PDF_MARGIN_HEADER')) {
            define('PDF_MARGIN_HEADER', 5);
        }
        if (!defined('PDF_MARGIN_FOOTER')) {
            define('PDF_MARGIN_FOOTER', 10);
        }
        
        // Other constants
        if (!defined('PDF_IMAGE_SCALE_RATIO')) {
            define('PDF_IMAGE_SCALE_RATIO', 1.25);
        }
        if (!defined('K_CELL_HEIGHT_RATIO')) {
            define('K_CELL_HEIGHT_RATIO', 1.25);
        }
        if (!defined('K_TCPDF_CALLS_IN_HTML')) {
            define('K_TCPDF_CALLS_IN_HTML', true);
        }
        if (!defined('K_TCPDF_THROW_EXCEPTION_ERROR')) {
            define('K_TCPDF_THROW_EXCEPTION_ERROR', false);
        }
        
        // cURL constants  - only define if they don't exist
        if (extension_loaded('curl')) {
            // PHP has curl extension, constants will be defined
            return;
        }
        
        // Only define curl constants if extension is not loaded
        $curl_constants = [
            'CURLOPT_RETURNTRANSFER' => 19913,
            'CURLOPT_BINARYTRANSFER' => 19914,
            'CURLOPT_SSL_VERIFYPEER' => 64,
            'CURLOPT_SSL_VERIFYHOST' => 81,
            'CURLOPT_FOLLOWLOCATION' => 52,
            'CURLOPT_MAXREDIRS' => 68,
            'CURLOPT_TIMEOUT' => 13,
            'CURLOPT_CONNECTTIMEOUT' => 78,
            'CURLOPT_USERAGENT' => 10018,
            'CURLOPT_REFERER' => 10016,
            'CURLOPT_HTTPAUTH' => 107,
            'CURLOPT_USERPWD' => 10005,
            'CURLOPT_PROTOCOLS' => 181,
            'CURLOPT_REDIR_PROTOCOLS' => 182,
            'CURLOPT_FAILONERROR' => 45,
            'CURLOPT_HEADER' => 42,
            'CURLOPT_NOBODY' => 44,
            'CURLOPT_VERBOSE' => 41,
            'CURLOPT_POST' => 47,
            'CURLOPT_POSTFIELDS' => 10015,
            'CURLOPT_HTTPHEADER' => 10023,
            'CURLOPT_ENCODING' => 10102,
            'CURLOPT_COOKIE' => 10031,
            'CURLOPT_COOKIEFILE' => 10031,
            'CURLOPT_COOKIEJAR' => 10082,
            'CURLOPT_SSL_CIPHER_LIST' => 10083,
            'CURLOPT_CERTINFO' => 172,
            'CURLOPT_CERTTYPE' => 10086,
            'CURLOPT_SSLKEY' => 10087,
            'CURLOPT_SSLKEYTYPE' => 10088,
            'CURLOPT_KEYPASSWD' => 10026,
            'CURLOPT_CAINFO' => 10065,
            'CURLOPT_CAPATH' => 10068,
            'CURLOPT_RANDOM_FILE' => 10076,
            'CURLOPT_EGDSOCKET' => 10077,
            'CURLOPT_CUSTOMREQUEST' => 10036,
            'CURLOPT_FTPPORT' => 10017,
            'CURLOPT_USERPWD_COOKIE' => 10100,
            'CURLOPT_NETRC' => 51,
            'CURLOPT_NETRC_FILE' => 10118,
            'CURLOPT_HTTPGET' => 80,
            'CURLOPT_FRESH_CONNECT' => 37,
            'CURLOPT_FORBID_REUSE' => 75,
            'CURLPROTO_HTTP' => 1,
            'CURLPROTO_HTTPS' => 2,
            'CURLPROTO_FTP' => 4,
            'CURLPROTO_FTPS' => 8,
            'CURL_HTTP_VERSION_1_1' => 2,
        ];
        
        foreach ($curl_constants as $const => $value) {
            if (!defined($const)) {
                define($const, $value);
            }
        }
    }
}

// Initialize when this file is loaded
TCPDFWrapper::initialize();
