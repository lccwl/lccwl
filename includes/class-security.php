<?php
/**
 * Security class for encryption, authentication, and access control
 */

if (!defined('ABSPATH')) {
    exit;
}

class AI_Optimizer_Security {
    
    private static $instance = null;
    private $encryption_key;
    
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        $this->init();
    }
    
    private function init() {
        $this->encryption_key = $this->get_encryption_key();
        
        // Security hooks
        add_action('init', array($this, 'security_headers'));
        add_action('wp_login_failed', array($this, 'log_failed_login'));
        add_action('wp_login', array($this, 'log_successful_login'), 10, 2);
        
        // Rate limiting
        add_action('wp_ajax_ai_optimizer_action', array($this, 'check_rate_limit'), 1);
        add_action('wp_ajax_nopriv_ai_optimizer_action', array($this, 'check_rate_limit'), 1);
    }
    
    /**
     * Add security headers
     */
    public function security_headers() {
        if (!headers_sent()) {
            header('X-Content-Type-Options: nosniff');
            header('X-Frame-Options: SAMEORIGIN');
            header('X-XSS-Protection: 1; mode=block');
            header('Referrer-Policy: strict-origin-when-cross-origin');
        }
    }
    
    /**
     * Encrypt sensitive data
     */
    public static function encrypt($data) {
        if (empty($data)) {
            return '';
        }
        
        $instance = self::get_instance();
        
        if (!extension_loaded('openssl')) {
            AI_Optimizer_Utils::log('OpenSSL not available for encryption', 'warning');
            return base64_encode($data); // Fallback to base64 encoding
        }
        
        $cipher = 'AES-256-CBC';
        $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length($cipher));
        
        $encrypted = openssl_encrypt($data, $cipher, $instance->encryption_key, 0, $iv);
        
        if ($encrypted === false) {
            AI_Optimizer_Utils::log('Encryption failed', 'error');
            return '';
        }
        
        return base64_encode($iv . $encrypted);
    }
    
    /**
     * Decrypt sensitive data
     */
    public static function decrypt($encrypted_data) {
        if (empty($encrypted_data)) {
            return '';
        }
        
        $instance = self::get_instance();
        
        if (!extension_loaded('openssl')) {
            AI_Optimizer_Utils::log('OpenSSL not available for decryption', 'warning');
            return base64_decode($encrypted_data); // Fallback from base64 encoding
        }
        
        $data = base64_decode($encrypted_data);
        
        if ($data === false) {
            return '';
        }
        
        $cipher = 'AES-256-CBC';
        $iv_length = openssl_cipher_iv_length($cipher);
        
        if (strlen($data) < $iv_length) {
            return '';
        }
        
        $iv = substr($data, 0, $iv_length);
        $encrypted = substr($data, $iv_length);
        
        $decrypted = openssl_decrypt($encrypted, $cipher, $instance->encryption_key, 0, $iv);
        
        if ($decrypted === false) {
            AI_Optimizer_Utils::log('Decryption failed', 'error');
            return '';
        }
        
        return $decrypted;
    }
    
    /**
     * Get or generate encryption key
     */
    private function get_encryption_key() {
        $key = get_option('ai_optimizer_encryption_key');
        
        if (empty($key)) {
            $key = $this->generate_encryption_key();
            update_option('ai_optimizer_encryption_key', $key);
        }
        
        return $key;
    }
    
    /**
     * Generate encryption key
     */
    private function generate_encryption_key() {
        if (function_exists('random_bytes')) {
            try {
                return base64_encode(random_bytes(32));
            } catch (Exception $e) {
                AI_Optimizer_Utils::log('Failed to generate random bytes', 'warning');
            }
        }
        
        // Fallback method
        return base64_encode(wp_generate_password(32, true, true));
    }
    
    /**
     * Hash password with salt
     */
    public static function hash_password($password, $salt = null) {
        if ($salt === null) {
            $salt = wp_generate_password(16, true, true);
        }
        
        return hash('sha256', $password . $salt) . ':' . $salt;
    }
    
    /**
     * Verify password hash
     */
    public static function verify_password($password, $hash) {
        $parts = explode(':', $hash);
        
        if (count($parts) !== 2) {
            return false;
        }
        
        $stored_hash = $parts[0];
        $salt = $parts[1];
        
        $test_hash = hash('sha256', $password . $salt);
        
        return hash_equals($stored_hash, $test_hash);
    }
    
    /**
     * Generate secure token
     */
    public static function generate_token($length = 32) {
        return AI_Optimizer_Utils::generate_random_string($length);
    }
    
    /**
     * Validate nonce with custom action
     */
    public static function verify_nonce($nonce, $action = 'ai_optimizer_nonce') {
        return wp_verify_nonce($nonce, $action);
    }
    
    /**
     * Check user permissions for specific action
     */
    public static function check_permission($action, $user_id = null) {
        if ($user_id === null) {
            $user_id = get_current_user_id();
        }
        
        if (!$user_id) {
            return false;
        }
        
        $permissions = array(
            'view_dashboard' => 'read',
            'run_analysis' => 'edit_posts',
            'apply_optimizations' => 'manage_options',
            'manage_settings' => 'manage_options',
            'generate_content' => 'edit_posts',
            'access_logs' => 'manage_options'
        );
        
        $required_capability = $permissions[$action] ?? 'manage_options';
        
        return user_can($user_id, $required_capability);
    }
    
    /**
     * Rate limiting check
     */
    public function check_rate_limit() {
        $user_ip = AI_Optimizer_Utils::get_client_ip();
        $user_id = get_current_user_id();
        
        $rate_limit = AI_Optimizer_Settings::get('rate_limit', 60);
        $window = 60; // 1 minute window
        
        $cache_key = 'ai_optimizer_rate_limit_' . ($user_id ? $user_id : $user_ip);
        $requests = get_transient($cache_key) ?: array();
        
        $current_time = time();
        $window_start = $current_time - $window;
        
        // Clean old requests
        $requests = array_filter($requests, function($timestamp) use ($window_start) {
            return $timestamp > $window_start;
        });
        
        if (count($requests) >= $rate_limit) {
            AI_Optimizer_Utils::log('Rate limit exceeded', 'warning', array(
                'user_ip' => $user_ip,
                'user_id' => $user_id,
                'requests_count' => count($requests)
            ));
            
            wp_die(__('Rate limit exceeded. Please try again later.', 'ai-website-optimizer'), 429);
        }
        
        // Add current request
        $requests[] = $current_time;
        set_transient($cache_key, $requests, $window);
    }
    
    /**
     * Log failed login attempts
     */
    public function log_failed_login($username) {
        AI_Optimizer_Utils::log('Failed login attempt', 'warning', array(
            'username' => $username,
            'ip_address' => AI_Optimizer_Utils::get_client_ip(),
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? ''
        ));
    }
    
    /**
     * Log successful login
     */
    public function log_successful_login($user_login, $user) {
        AI_Optimizer_Utils::log('Successful login', 'info', array(
            'user_id' => $user->ID,
            'username' => $user_login,
            'ip_address' => AI_Optimizer_Utils::get_client_ip()
        ));
    }
    
    /**
     * Sanitize file upload
     */
    public static function sanitize_upload($file) {
        // Check file type
        $allowed_types = array('json', 'txt', 'csv');
        $file_extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        
        if (!in_array(strtolower($file_extension), $allowed_types)) {
            return new WP_Error('invalid_file_type', __('Invalid file type.', 'ai-website-optimizer'));
        }
        
        // Check file size (max 10MB)
        $max_size = 10 * 1024 * 1024;
        if ($file['size'] > $max_size) {
            return new WP_Error('file_too_large', __('File too large.', 'ai-website-optimizer'));
        }
        
        // Sanitize filename
        $file['name'] = sanitize_file_name($file['name']);
        
        return $file;
    }
    
    /**
     * Scan uploaded file for malicious content
     */
    public static function scan_file_content($file_path) {
        if (!file_exists($file_path)) {
            return false;
        }
        
        $content = file_get_contents($file_path);
        
        // Check for suspicious patterns
        $suspicious_patterns = array(
            '/<\?php/',
            '/<script/',
            '/eval\s*\(/',
            '/exec\s*\(/',
            '/system\s*\(/',
            '/shell_exec\s*\(/',
            '/base64_decode\s*\(/',
        );
        
        foreach ($suspicious_patterns as $pattern) {
            if (preg_match($pattern, $content)) {
                AI_Optimizer_Utils::log('Suspicious content detected in uploaded file', 'error', array(
                    'file' => $file_path,
                    'pattern' => $pattern
                ));
                return false;
            }
        }
        
        return true;
    }
    
    /**
     * Validate API request signature (if implemented)
     */
    public static function validate_api_signature($payload, $signature, $secret) {
        $expected_signature = hash_hmac('sha256', $payload, $secret);
        return hash_equals($expected_signature, $signature);
    }
    
    /**
     * Generate CSRF token
     */
    public static function generate_csrf_token() {
        $token = self::generate_token();
        set_transient('ai_optimizer_csrf_' . $token, true, 3600); // 1 hour expiry
        return $token;
    }
    
    /**
     * Verify CSRF token
     */
    public static function verify_csrf_token($token) {
        $key = 'ai_optimizer_csrf_' . $token;
        $valid = get_transient($key);
        
        if ($valid) {
            delete_transient($key); // Single use token
            return true;
        }
        
        return false;
    }
    
    /**
     * Secure session management
     */
    public static function start_secure_session() {
        if (session_status() === PHP_SESSION_NONE) {
            // Configure secure session parameters
            ini_set('session.cookie_httponly', 1);
            ini_set('session.use_only_cookies', 1);
            ini_set('session.cookie_secure', is_ssl() ? 1 : 0);
            
            session_start();
        }
    }
    
    /**
     * Input validation and sanitization
     */
    public static function validate_input($input, $rules) {
        $errors = array();
        $sanitized = array();
        
        foreach ($rules as $field => $rule) {
            $value = $input[$field] ?? null;
            
            // Required field check
            if (isset($rule['required']) && $rule['required'] && empty($value)) {
                $errors[$field] = sprintf(__('%s is required.', 'ai-website-optimizer'), $rule['label'] ?? $field);
                continue;
            }
            
            // Skip validation if field is empty and not required
            if (empty($value)) {
                $sanitized[$field] = '';
                continue;
            }
            
            // Type validation and sanitization
            switch ($rule['type']) {
                case 'email':
                    if (!is_email($value)) {
                        $errors[$field] = sprintf(__('%s must be a valid email address.', 'ai-website-optimizer'), $rule['label'] ?? $field);
                    } else {
                        $sanitized[$field] = sanitize_email($value);
                    }
                    break;
                    
                case 'url':
                    if (!filter_var($value, FILTER_VALIDATE_URL)) {
                        $errors[$field] = sprintf(__('%s must be a valid URL.', 'ai-website-optimizer'), $rule['label'] ?? $field);
                    } else {
                        $sanitized[$field] = esc_url_raw($value);
                    }
                    break;
                    
                case 'int':
                    if (!is_numeric($value)) {
                        $errors[$field] = sprintf(__('%s must be a number.', 'ai-website-optimizer'), $rule['label'] ?? $field);
                    } else {
                        $int_value = intval($value);
                        
                        if (isset($rule['min']) && $int_value < $rule['min']) {
                            $errors[$field] = sprintf(__('%s must be at least %d.', 'ai-website-optimizer'), $rule['label'] ?? $field, $rule['min']);
                        } elseif (isset($rule['max']) && $int_value > $rule['max']) {
                            $errors[$field] = sprintf(__('%s must be no more than %d.', 'ai-website-optimizer'), $rule['label'] ?? $field, $rule['max']);
                        } else {
                            $sanitized[$field] = $int_value;
                        }
                    }
                    break;
                    
                case 'text':
                    $sanitized_value = sanitize_text_field($value);
                    
                    if (isset($rule['min_length']) && strlen($sanitized_value) < $rule['min_length']) {
                        $errors[$field] = sprintf(__('%s must be at least %d characters long.', 'ai-website-optimizer'), $rule['label'] ?? $field, $rule['min_length']);
                    } elseif (isset($rule['max_length']) && strlen($sanitized_value) > $rule['max_length']) {
                        $errors[$field] = sprintf(__('%s must be no more than %d characters long.', 'ai-website-optimizer'), $rule['label'] ?? $field, $rule['max_length']);
                    } else {
                        $sanitized[$field] = $sanitized_value;
                    }
                    break;
                    
                case 'textarea':
                    $sanitized[$field] = sanitize_textarea_field($value);
                    break;
                    
                case 'api_key':
                    if (!AI_Optimizer_Utils::validate_api_key($value)) {
                        $errors[$field] = sprintf(__('%s is not a valid API key format.', 'ai-website-optimizer'), $rule['label'] ?? $field);
                    } else {
                        $sanitized[$field] = sanitize_text_field($value);
                    }
                    break;
                    
                default:
                    $sanitized[$field] = sanitize_text_field($value);
            }
        }
        
        return array(
            'errors' => $errors,
            'data' => $sanitized,
            'valid' => empty($errors)
        );
    }
    
    /**
     * Log security events
     */
    public static function log_security_event($event, $level = 'warning', $context = array()) {
        $context['security_event'] = true;
        $context['ip_address'] = AI_Optimizer_Utils::get_client_ip();
        $context['user_agent'] = $_SERVER['HTTP_USER_AGENT'] ?? '';
        
        AI_Optimizer_Utils::log($event, $level, $context);
    }
    
    /**
     * Check for suspicious activity
     */
    public function check_suspicious_activity() {
        $ip = AI_Optimizer_Utils::get_client_ip();
        $cache_key = 'ai_optimizer_suspicious_' . $ip;
        
        $activity = get_transient($cache_key) ?: array(
            'failed_logins' => 0,
            'failed_requests' => 0,
            'last_activity' => time()
        );
        
        // Check if IP should be blocked
        if ($activity['failed_logins'] > 5 || $activity['failed_requests'] > 20) {
            self::log_security_event('Suspicious activity detected', 'error', array(
                'ip' => $ip,
                'failed_logins' => $activity['failed_logins'],
                'failed_requests' => $activity['failed_requests']
            ));
            
            // Block for 1 hour
            set_transient('ai_optimizer_blocked_' . $ip, true, 3600);
            
            wp_die(__('Access blocked due to suspicious activity.', 'ai-website-optimizer'), 403);
        }
    }
    
    /**
     * Check if IP is blocked
     */
    public function is_ip_blocked($ip = null) {
        if ($ip === null) {
            $ip = AI_Optimizer_Utils::get_client_ip();
        }
        
        return get_transient('ai_optimizer_blocked_' . $ip) === true;
    }
    
    /**
     * Clean up security data
     */
    public static function cleanup_security_data() {
        global $wpdb;
        
        // Clean old rate limit data
        $wpdb->query(
            "DELETE FROM {$wpdb->options} 
            WHERE option_name LIKE '_transient_ai_optimizer_rate_limit_%' 
            OR option_name LIKE '_transient_timeout_ai_optimizer_rate_limit_%'"
        );
        
        // Clean old CSRF tokens
        $wpdb->query(
            "DELETE FROM {$wpdb->options} 
            WHERE option_name LIKE '_transient_ai_optimizer_csrf_%' 
            OR option_name LIKE '_transient_timeout_ai_optimizer_csrf_%'"
        );
        
        // Clean old blocked IPs
        $wpdb->query(
            "DELETE FROM {$wpdb->options} 
            WHERE option_name LIKE '_transient_ai_optimizer_blocked_%' 
            OR option_name LIKE '_transient_timeout_ai_optimizer_blocked_%'"
        );
        
        AI_Optimizer_Utils::log('Security data cleanup completed', 'info');
    }
}
