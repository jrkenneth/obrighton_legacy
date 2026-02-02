<?php
/**
 * Input Validation & Sanitization Library
 * 
 * Prevents XSS, SQL injection, and data type violations
 * All user input should be validated using these functions before use
 * 
 * Usage:
 *   $email = InputValidator::validateEmail($_POST['email']);
 *   $phone = InputValidator::validatePhone($_POST['phone']);
 *   $name = InputValidator::sanitizeText($_POST['name'], 100);
 */

class InputValidator {
    
    /**
     * Sanitize text input (removes HTML tags, trims whitespace)
     * 
     * @param string $input User input
     * @param int $max_length Maximum length allowed
     * @return string Sanitized text
     */
    public static function sanitizeText($input, $max_length = 255) {
        if (!is_string($input)) {
            return '';
        }
        
        // Remove HTML tags
        $clean = strip_tags($input);
        
        // Trim whitespace
        $clean = trim($clean);
        
        // Limit length
        $clean = substr($clean, 0, $max_length);
        
        return $clean;
    }
    
    /**
     * Validate and sanitize email
     * 
     * @param string $email User email
     * @return string|false Valid email or false
     */
    public static function validateEmail($email) {
        $email = trim($email);
        $email = strtolower($email);
        
        // Check length
        if (strlen($email) > 255 || strlen($email) < 5) {
            return false;
        }
        
        // Validate email format
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return false;
        }
        
        return $email;
    }
    
    /**
     * Validate and sanitize phone number
     * 
     * @param string $phone Phone number
     * @return string|false Valid phone or false
     */
    public static function validatePhone($phone) {
        $phone = preg_replace('/[^0-9+\-\s().]/', '', $phone);
        $phone = trim($phone);
        
        // Check length (phone numbers typically 7-15 digits)
        if (strlen(preg_replace('/[^0-9]/', '', $phone)) < 7) {
            return false;
        }
        
        if (strlen($phone) > 20) {
            return false;
        }
        
        return $phone;
    }
    
    /**
     * Validate integer
     * 
     * @param mixed $value Value to validate
     * @param int $min Minimum value
     * @param int $max Maximum value
     * @return int|false Valid integer or false
     */
    public static function validateInteger($value, $min = 0, $max = PHP_INT_MAX) {
        if (!is_numeric($value)) {
            return false;
        }
        
        $int_value = intval($value);
        
        if ($int_value < $min || $int_value > $max) {
            return false;
        }
        
        return $int_value;
    }
    
    /**
     * Validate float/decimal
     * 
     * @param mixed $value Value to validate
     * @param int $decimal_places Number of decimal places
     * @return float|false Valid float or false
     */
    public static function validateFloat($value, $decimal_places = 2) {
        if (!is_numeric($value)) {
            return false;
        }
        
        $float_value = floatval($value);
        
        // Check decimal places
        $parts = explode('.', strval($value));
        if (isset($parts[1]) && strlen($parts[1]) > $decimal_places) {
            return false;
        }
        
        return round($float_value, $decimal_places);
    }
    
    /**
     * Validate URL
     * 
     * @param string $url URL to validate
     * @return string|false Valid URL or false
     */
    public static function validateUrl($url) {
        $url = trim($url);
        
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            return false;
        }
        
        // Only allow http and https
        if (!preg_match('/^https?:\/\//', $url)) {
            return false;
        }
        
        return $url;
    }
    
    /**
     * Validate UUID format
     * 
     * @param string $uuid UUID to validate
     * @return string|false Valid UUID or false
     */
    public static function validateUUID($uuid) {
        $uuid = trim($uuid);
        
        // UUID v4 format: 8-4-4-4-12
        if (!preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i', $uuid)) {
            return false;
        }
        
        return $uuid;
    }
    
    /**
     * Validate date format (YYYY-MM-DD)
     * 
     * @param string $date Date to validate
     * @return string|false Valid date or false
     */
    public static function validateDate($date) {
        $date = trim($date);
        
        // Check format
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            return false;
        }
        
        // Validate as actual date
        $parsed = date_parse_from_format('Y-m-d', $date);
        if ($parsed['error_count'] > 0 || $parsed['warning_count'] > 0) {
            return false;
        }
        
        return $date;
    }
    
    /**
     * Validate datetime format (YYYY-MM-DD HH:MM:SS)
     * 
     * @param string $datetime Datetime to validate
     * @return string|false Valid datetime or false
     */
    public static function validateDateTime($datetime) {
        $datetime = trim($datetime);
        
        // Check format
        if (!preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/', $datetime)) {
            return false;
        }
        
        // Validate as actual datetime
        $parsed = date_parse_from_format('Y-m-d H:i:s', $datetime);
        if ($parsed['error_count'] > 0) {
            return false;
        }
        
        return $datetime;
    }
    
    /**
     * Validate choice against allowed values
     * 
     * @param mixed $value Value to validate
     * @param array $allowed Allowed values
     * @return mixed|false Valid value or false
     */
    public static function validateChoice($value, $allowed = []) {
        if (!in_array($value, $allowed, true)) {
            return false;
        }
        
        return $value;
    }
    
    /**
     * Sanitize HTML content (allows safe HTML tags)
     * 
     * @param string $html HTML content to sanitize
     * @return string Sanitized HTML
     */
    public static function sanitizeHtml($html) {
        // Define allowed tags
        $allowed_tags = '<p><br><strong><em><u><h1><h2><h3><h4><h5><h6><ul><ol><li><a><blockquote>';
        
        // Remove disallowed tags
        $clean = strip_tags($html, $allowed_tags);
        
        // Remove javascript: and data: protocols
        $clean = preg_replace('/on\w+\s*=\s*["\']?[^"\']*["\']?/i', '', $clean);
        $clean = preg_replace('/(javascript|data):/i', '', $clean);
        
        return $clean;
    }
    
    /**
     * Escape for output (prevents XSS when displaying user data)
     * 
     * @param string $output Data to display
     * @return string Escaped output safe for HTML
     */
    public static function escapeOutput($output) {
        return htmlspecialchars($output, ENT_QUOTES, 'UTF-8');
    }
    
    /**
     * Validate array of fields
     * 
     * @param array $data Data to validate
     * @param array $rules Validation rules
     * @return array Validated data or errors
     * 
     * Example:
     * $rules = [
     *   'email' => ['type' => 'email', 'required' => true],
     *   'age' => ['type' => 'integer', 'min' => 18, 'max' => 120],
     *   'phone' => ['type' => 'phone', 'required' => false]
     * ];
     * $result = InputValidator::validateArray($data, $rules);
     */
    public static function validateArray($data, $rules) {
        $validated = [];
        $errors = [];
        
        foreach ($rules as $field => $rule) {
            $value = $data[$field] ?? null;
            $required = $rule['required'] ?? true;
            $type = $rule['type'] ?? 'text';
            
            // Check required
            if ($required && (empty($value) || (is_string($value) && trim($value) === ''))) {
                $errors[$field] = "Field '{$field}' is required";
                continue;
            }
            
            // Skip validation if not required and empty
            if (!$required && empty($value)) {
                $validated[$field] = null;
                continue;
            }
            
            // Validate based on type
            switch ($type) {
                case 'email':
                    $validated[$field] = self::validateEmail($value);
                    if ($validated[$field] === false) {
                        $errors[$field] = "Invalid email format";
                    }
                    break;
                    
                case 'phone':
                    $validated[$field] = self::validatePhone($value);
                    if ($validated[$field] === false) {
                        $errors[$field] = "Invalid phone number";
                    }
                    break;
                    
                case 'integer':
                    $min = $rule['min'] ?? 0;
                    $max = $rule['max'] ?? PHP_INT_MAX;
                    $validated[$field] = self::validateInteger($value, $min, $max);
                    if ($validated[$field] === false) {
                        $errors[$field] = "Invalid integer (min: {$min}, max: {$max})";
                    }
                    break;
                    
                case 'float':
                    $decimals = $rule['decimals'] ?? 2;
                    $validated[$field] = self::validateFloat($value, $decimals);
                    if ($validated[$field] === false) {
                        $errors[$field] = "Invalid decimal number";
                    }
                    break;
                    
                case 'url':
                    $validated[$field] = self::validateUrl($value);
                    if ($validated[$field] === false) {
                        $errors[$field] = "Invalid URL";
                    }
                    break;
                    
                case 'date':
                    $validated[$field] = self::validateDate($value);
                    if ($validated[$field] === false) {
                        $errors[$field] = "Invalid date format (YYYY-MM-DD)";
                    }
                    break;
                    
                case 'datetime':
                    $validated[$field] = self::validateDateTime($value);
                    if ($validated[$field] === false) {
                        $errors[$field] = "Invalid datetime format (YYYY-MM-DD HH:MM:SS)";
                    }
                    break;
                    
                case 'choice':
                    $allowed = $rule['allowed'] ?? [];
                    $validated[$field] = self::validateChoice($value, $allowed);
                    if ($validated[$field] === false) {
                        $errors[$field] = "Invalid choice";
                    }
                    break;
                    
                case 'text':
                default:
                    $max_length = $rule['max_length'] ?? 255;
                    $validated[$field] = self::sanitizeText($value, $max_length);
                    break;
            }
        }
        
        return [
            'validated' => $validated,
            'errors' => $errors,
            'success' => empty($errors)
        ];
    }
}

?>
