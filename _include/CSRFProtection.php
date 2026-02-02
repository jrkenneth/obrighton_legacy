<?php
/**
 * CSRF (Cross-Site Request Forgery) Protection
 * 
 * Generates and validates CSRF tokens to prevent unauthorized form submissions
 * 
 * Usage in forms:
 *   <?php $token = CSRFProtection::generateToken(); ?>
 *   <form method="POST">
 *       <?php CSRFProtection::tokenField(); ?>
 *       <input type="text" name="username">
 *       <button type="submit">Submit</button>
 *   </form>
 * 
 * Usage in route handlers:
 *   if ($_POST) {
 *       if (!CSRFProtection::validateToken($_POST['csrf_token'] ?? '')) {
 *           die('CSRF token validation failed');
 *       }
 *       // Process form
 *   }
 */

class CSRFProtection {
    
    // Token configuration
    private static $token_name = 'csrf_token';
    private static $session_key = '_csrf_token';
    private static $token_length = 32;
    private static $token_lifetime = 3600; // 1 hour
    
    /**
     * Initialize CSRF protection (call this in session_mgr.php or early in header.php)
     */
    public static function initialize() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        // Generate token if not exists or expired
        if (!self::tokenExists() || self::isTokenExpired()) {
            self::generateToken();
        }
    }
    
    /**
     * Generate a new CSRF token
     * 
     * @return string Generated token
     */
    public static function generateToken() {
        $token = bin2hex(random_bytes(self::$token_length / 2));
        
        $_SESSION[self::$session_key] = [
            'token' => $token,
            'created' => time(),
            'lifetime' => self::$token_lifetime
        ];
        
        return $token;
    }
    
    /**
     * Get current CSRF token
     * 
     * @return string Current token or new token if expired
     */
    public static function getToken() {
        if (!self::tokenExists() || self::isTokenExpired()) {
            return self::generateToken();
        }
        
        return $_SESSION[self::$session_key]['token'];
    }
    
    /**
     * Check if token exists in session
     * 
     * @return bool
     */
    private static function tokenExists() {
        return isset($_SESSION[self::$session_key]) && isset($_SESSION[self::$session_key]['token']);
    }
    
    /**
     * Check if current token is expired
     * 
     * @return bool
     */
    private static function isTokenExpired() {
        if (!self::tokenExists()) {
            return true;
        }
        
        $data = $_SESSION[self::$session_key];
        $lifetime = $data['lifetime'] ?? self::$token_lifetime;
        $elapsed = time() - $data['created'];
        
        return $elapsed > $lifetime;
    }
    
    /**
     * Validate CSRF token from user input
     * 
     * IMPORTANT: Call this at the start of any POST/PUT/DELETE handler
     * 
     * @param string $token Token to validate (usually from $_POST or $_REQUEST)
     * @return bool True if valid, false otherwise
     */
    public static function validateToken($token) {
        // Ensure token exists in POST/REQUEST
        if (empty($token)) {
            error_log("CSRF: Token missing from request");
            return false;
        }
        
        // Ensure token exists in session
        if (!self::tokenExists()) {
            error_log("CSRF: No token in session");
            return false;
        }
        
        // Check token expiration
        if (self::isTokenExpired()) {
            error_log("CSRF: Token expired");
            return false;
        }
        
        // Compare tokens using hash_equals to prevent timing attacks
        $session_token = $_SESSION[self::$session_key]['token'];
        
        if (!hash_equals($session_token, $token)) {
            error_log("CSRF: Token mismatch");
            return false;
        }
        
        // Token is valid, regenerate for next request
        self::generateToken();
        
        return true;
    }
    
    /**
     * Output hidden CSRF token field for forms
     * 
     * Usage in forms:
     *   <?php CSRFProtection::tokenField(); ?>
     */
    public static function tokenField() {
        $token = self::getToken();
        $field_name = self::$token_name;
        echo "<input type=\"hidden\" name=\"{$field_name}\" value=\"{$token}\">";
    }
    
    /**
     * Get CSRF token as hidden field HTML
     * 
     * @return string HTML hidden input field
     */
    public static function getTokenField() {
        $token = self::getToken();
        $field_name = self::$token_name;
        return "<input type=\"hidden\" name=\"{$field_name}\" value=\"{$token}\">";
    }
    
    /**
     * Get CSRF token for AJAX requests (in data attribute or response header)
     * 
     * @return string Raw token
     */
    public static function getTokenForAjax() {
        return self::getToken();
    }
    
    /**
     * Validate token and return JSON response (for AJAX)
     * 
     * Usage in AJAX handler:
     *   header('Content-Type: application/json');
     *   echo CSRFProtection::validateTokenJson($_POST['csrf_token'] ?? '');
     * 
     * @param string $token Token to validate
     * @return string JSON response
     */
    public static function validateTokenJson($token) {
        if (self::validateToken($token)) {
            return json_encode(['success' => true, 'message' => 'CSRF validation passed']);
        } else {
            return json_encode(['success' => false, 'message' => 'CSRF validation failed']);
        }
    }
    
    /**
     * Check CSRF token and handle validation failure
     * 
     * Call this at the start of POST handlers:
     *   CSRFProtection::checkToken($_POST['csrf_token'] ?? '');
     * 
     * @param string $token Token to validate
     * @param string $error_message Custom error message
     */
    public static function checkToken($token, $error_message = 'Invalid form submission. Please try again.') {
        if (!self::validateToken($token)) {
            // Log potential attack
            $remote_ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
            error_log("CSRF attack attempt from IP: {$remote_ip}");
            
            // Return error response
            $_SESSION['response'] = 'error';
            $_SESSION['message'] = $error_message;
            
            // Redirect or die depending on request type
            if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') {
                header('Content-Type: application/json');
                die(json_encode(['success' => false, 'message' => $error_message]));
            } else {
                header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? 'index.php'));
                die();
            }
        }
    }
    
    /**
     * Regenerate token after successful operation
     * (Automatically done by validateToken, but can be called manually)
     */
    public static function regenerateToken() {
        self::generateToken();
    }
}

// Initialize CSRF protection when this file is included
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
CSRFProtection::initialize();

?>
