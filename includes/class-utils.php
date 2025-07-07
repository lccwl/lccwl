<?php
/**
 * Utility functions and helper methods
 */

if (!defined('ABSPATH')) {
    exit;
}

class AI_Optimizer_Utils {
    
    /**
     * Log messages with different levels
     */
    public static function log($message, $level = 'info', $context = array()) {
        $log_level = AI_Optimizer_Settings::get('log_level', 'info');
        
        // Check if we should log this level
        if (!self::should_log($level, $log_level)) {
            return;
        }
        
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'ai_optimizer_logs';
        
        $context_json = !empty($context) ? json_encode($context) : null;
        
        $wpdb->insert(
            $table_name,
            array(
                'level' => $level,
                'message' => $message,
                'context' => $context_json,
                'user_id' => get_current_user_id(),
                'ip_address' => self::get_client_ip(),
                'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
                'created_at' => current_time('mysql')
            ),
            array('%s', '%s', '%s', '%d', '%s', '%s', '%s')
        );
        
        // Also log to WordPress debug log if enabled
        if (defined('WP_DEBUG') && WP_DEBUG) {
            $log_entry = sprintf(
                '[%s] [%s] %s %s',
                date('Y-m-d H:i:s'),
                strtoupper($level),
                $message,
                !empty($context) ? json_encode($context) : ''
            );
            error_log($log_entry);
        }
    }
    
    /**
     * Check if we should log this level
     */
    private static function should_log($message_level, $configured_level) {
        $levels = array(
            'debug' => 0,
            'info' => 1,
            'warning' => 2,
            'error' => 3,
            'critical' => 4
        );
        
        $message_priority = $levels[$message_level] ?? 1;
        $configured_priority = $levels[$configured_level] ?? 1;
        
        return $message_priority >= $configured_priority;
    }
    
    /**
     * Get client IP address
     */
    public static function get_client_ip() {
        $ip_headers = array(
            'HTTP_CF_CONNECTING_IP',
            'HTTP_CLIENT_IP',
            'HTTP_X_FORWARDED_FOR',
            'HTTP_X_FORWARDED',
            'HTTP_X_CLUSTER_CLIENT_IP',
            'HTTP_FORWARDED_FOR',
            'HTTP_FORWARDED',
            'REMOTE_ADDR'
        );
        
        foreach ($ip_headers as $header) {
            if (!empty($_SERVER[$header])) {
                $ip = $_SERVER[$header];
                
                // Handle comma-separated IPs
                if (strpos($ip, ',') !== false) {
                    $ip = trim(explode(',', $ip)[0]);
                }
                
                // Validate IP
                if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                    return $ip;
                }
            }
        }
        
        return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    }
    
    /**
     * Sanitize and validate input data
     */
    public static function sanitize_input($data, $type = 'text') {
        switch ($type) {
            case 'email':
                return sanitize_email($data);
            case 'url':
                return esc_url_raw($data);
            case 'int':
                return intval($data);
            case 'float':
                return floatval($data);
            case 'textarea':
                return sanitize_textarea_field($data);
            case 'html':
                return wp_kses_post($data);
            case 'filename':
                return sanitize_file_name($data);
            case 'key':
                return sanitize_key($data);
            case 'text':
            default:
                return sanitize_text_field($data);
        }
    }
    
    /**
     * Generate secure random string
     */
    public static function generate_random_string($length = 32) {
        if (function_exists('random_bytes')) {
            try {
                return bin2hex(random_bytes($length / 2));
            } catch (Exception $e) {
                // Fall back to wp_generate_password
            }
        }
        
        return wp_generate_password($length, false);
    }
    
    /**
     * Format file size
     */
    public static function format_file_size($bytes) {
        $units = array('B', 'KB', 'MB', 'GB', 'TB');
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, 2) . ' ' . $units[$i];
    }
    
    /**
     * Format time duration
     */
    public static function format_duration($seconds) {
        if ($seconds < 60) {
            return round($seconds, 1) . 's';
        } elseif ($seconds < 3600) {
            return round($seconds / 60, 1) . 'm';
        } else {
            return round($seconds / 3600, 1) . 'h';
        }
    }
    
    /**
     * Check if string is JSON
     */
    public static function is_json($string) {
        json_decode($string);
        return json_last_error() === JSON_ERROR_NONE;
    }
    
    /**
     * Validate URL
     */
    public static function is_valid_url($url) {
        return filter_var($url, FILTER_VALIDATE_URL) !== false;
    }
    
    /**
     * Calculate text similarity
     */
    public static function calculate_similarity($text1, $text2) {
        $text1 = strtolower(trim($text1));
        $text2 = strtolower(trim($text2));
        
        if (empty($text1) || empty($text2)) {
            return 0;
        }
        
        // Simple Levenshtein distance based similarity
        $max_length = max(strlen($text1), strlen($text2));
        
        if ($max_length === 0) {
            return 100;
        }
        
        $distance = levenshtein($text1, $text2);
        $similarity = (1 - ($distance / $max_length)) * 100;
        
        return max(0, $similarity);
    }
    
    /**
     * Extract keywords from text
     */
    public static function extract_keywords($text, $limit = 10) {
        $text = strtolower(strip_tags($text));
        
        // Remove common stop words
        $stop_words = array(
            'the', 'a', 'an', 'and', 'or', 'but', 'in', 'on', 'at', 'to', 'for', 'of', 'with', 'by',
            'is', 'are', 'was', 'were', 'be', 'been', 'being', 'have', 'has', 'had', 'do', 'does',
            'did', 'will', 'would', 'could', 'should', 'may', 'might', 'must', 'can', 'this', 'that',
            'these', 'those', 'i', 'me', 'my', 'myself', 'we', 'our', 'ours', 'ourselves', 'you',
            'your', 'yours', 'yourself', 'yourselves', 'he', 'him', 'his', 'himself', 'she', 'her',
            'hers', 'herself', 'it', 'its', 'itself', 'they', 'them', 'their', 'theirs', 'themselves'
        );
        
        // Extract words
        $words = str_word_count($text, 1);
        
        // Filter out stop words and short words
        $filtered_words = array_filter($words, function($word) use ($stop_words) {
            return strlen($word) > 3 && !in_array($word, $stop_words);
        });
        
        // Count word frequency
        $word_count = array_count_values($filtered_words);
        arsort($word_count);
        
        return array_slice(array_keys($word_count), 0, $limit);
    }
    
    /**
     * Generate SEO-friendly slug
     */
    public static function generate_slug($text) {
        return sanitize_title($text);
    }
    
    /**
     * Validate API key format
     */
    public static function validate_api_key($api_key) {
        // Basic validation for Siliconflow API key format
        if (empty($api_key)) {
            return false;
        }
        
        // Should be a string of reasonable length
        if (strlen($api_key) < 20 || strlen($api_key) > 200) {
            return false;
        }
        
        // Should contain alphanumeric characters and possibly hyphens/underscores
        if (!preg_match('/^[a-zA-Z0-9_-]+$/', $api_key)) {
            return false;
        }
        
        return true;
    }
    
    /**
     * Check if current user can perform action
     */
    public static function current_user_can_optimize() {
        return current_user_can('manage_options') || current_user_can('edit_posts');
    }
    
    /**
     * Get WordPress memory limit in bytes
     */
    public static function get_memory_limit() {
        $memory_limit = ini_get('memory_limit');
        
        if (empty($memory_limit) || $memory_limit == -1) {
            return false;
        }
        
        $unit = strtolower(substr($memory_limit, -1));
        $value = intval($memory_limit);
        
        switch ($unit) {
            case 'g':
                return $value * 1024 * 1024 * 1024;
            case 'm':
                return $value * 1024 * 1024;
            case 'k':
                return $value * 1024;
            default:
                return $value;
        }
    }
    
    /**
     * Check if WordPress is in debug mode
     */
    public static function is_debug_mode() {
        return defined('WP_DEBUG') && WP_DEBUG;
    }
    
    /**
     * Get WordPress timezone
     */
    public static function get_timezone() {
        $timezone_string = get_option('timezone_string');
        
        if (!empty($timezone_string)) {
            return $timezone_string;
        }
        
        $offset = get_option('gmt_offset');
        $sign = ($offset < 0) ? '-' : '+';
        $hour = intval(abs($offset));
        $minute = (abs($offset) - $hour) * 60;
        
        return sprintf('%s%02d:%02d', $sign, $hour, $minute);
    }
    
    /**
     * Convert timezone
     */
    public static function convert_timezone($datetime, $from_timezone = 'UTC', $to_timezone = null) {
        if ($to_timezone === null) {
            $to_timezone = self::get_timezone();
        }
        
        try {
            $date = new DateTime($datetime, new DateTimeZone($from_timezone));
            $date->setTimezone(new DateTimeZone($to_timezone));
            return $date->format('Y-m-d H:i:s');
        } catch (Exception $e) {
            self::log('Timezone conversion error', 'error', array(
                'datetime' => $datetime,
                'from' => $from_timezone,
                'to' => $to_timezone,
                'error' => $e->getMessage()
            ));
            return $datetime;
        }
    }
    
    /**
     * Create nonce for AJAX requests
     */
    public static function create_ajax_nonce($action = 'ai_optimizer_ajax') {
        return wp_create_nonce($action);
    }
    
    /**
     * Verify nonce for AJAX requests
     */
    public static function verify_ajax_nonce($nonce, $action = 'ai_optimizer_ajax') {
        return wp_verify_nonce($nonce, $action);
    }
    
    /**
     * Get plugin version
     */
    public static function get_plugin_version() {
        return AI_OPTIMIZER_VERSION;
    }
    
    /**
     * Check if plugin is network activated
     */
    public static function is_network_activated() {
        if (!function_exists('is_plugin_active_for_network')) {
            require_once(ABSPATH . 'wp-admin/includes/plugin.php');
        }
        
        return is_plugin_active_for_network(AI_OPTIMIZER_PLUGIN_BASENAME);
    }
    
    /**
     * Get all logs with pagination
     */
    public static function get_logs($page = 1, $per_page = 50, $level = null) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'ai_optimizer_logs';
        $offset = ($page - 1) * $per_page;
        
        $where_clause = '';
        $params = array();
        
        if ($level && $level !== 'all') {
            $where_clause = 'WHERE level = %s';
            $params[] = $level;
        }
        
        $query = "SELECT * FROM {$table_name} {$where_clause} ORDER BY created_at DESC LIMIT %d OFFSET %d";
        $params[] = $per_page;
        $params[] = $offset;
        
        return $wpdb->get_results($wpdb->prepare($query, ...$params), ARRAY_A);
    }
    
    /**
     * Clear old logs
     */
    public static function clear_old_logs($days = 30) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'ai_optimizer_logs';
        
        $deleted = $wpdb->query(
            $wpdb->prepare(
                "DELETE FROM {$table_name} WHERE created_at < DATE_SUB(NOW(), INTERVAL %d DAY)",
                $days
            )
        );
        
        self::log('Old logs cleared', 'info', array('deleted_count' => $deleted, 'days' => $days));
        
        return $deleted;
    }
    
    /**
     * Format log level for display
     */
    public static function format_log_level($level) {
        $levels = array(
            'debug' => array('color' => '#6c757d', 'icon' => 'fas fa-bug'),
            'info' => array('color' => '#17a2b8', 'icon' => 'fas fa-info-circle'),
            'warning' => array('color' => '#ffc107', 'icon' => 'fas fa-exclamation-triangle'),
            'error' => array('color' => '#dc3545', 'icon' => 'fas fa-times-circle'),
            'critical' => array('color' => '#721c24', 'icon' => 'fas fa-skull')
        );
        
        return $levels[$level] ?? $levels['info'];
    }
    
    /**
     * Check system requirements
     */
    public static function check_system_requirements() {
        $requirements = array(
            'php_version' => array(
                'required' => '7.4',
                'current' => PHP_VERSION,
                'status' => version_compare(PHP_VERSION, '7.4', '>=')
            ),
            'wp_version' => array(
                'required' => '5.0',
                'current' => get_bloginfo('version'),
                'status' => version_compare(get_bloginfo('version'), '5.0', '>=')
            ),
            'memory_limit' => array(
                'required' => '128M',
                'current' => ini_get('memory_limit'),
                'status' => self::get_memory_limit() >= (128 * 1024 * 1024)
            ),
            'curl' => array(
                'required' => true,
                'current' => extension_loaded('curl'),
                'status' => extension_loaded('curl')
            ),
            'json' => array(
                'required' => true,
                'current' => extension_loaded('json'),
                'status' => extension_loaded('json')
            ),
            'openssl' => array(
                'required' => true,
                'current' => extension_loaded('openssl'),
                'status' => extension_loaded('openssl')
            )
        );
        
        return $requirements;
    }
    
    /**
     * Create backup before critical operations
     */
    public static function create_backup($type, $data) {
        $backup_dir = wp_upload_dir()['basedir'] . '/ai-optimizer-backups';
        
        if (!wp_mkdir_p($backup_dir)) {
            self::log('Failed to create backup directory', 'error');
            return false;
        }
        
        $filename = sprintf(
            '%s_%s_%s.json',
            $type,
            date('Y-m-d_H-i-s'),
            wp_generate_password(8, false)
        );
        
        $backup_file = $backup_dir . '/' . $filename;
        
        $backup_data = array(
            'timestamp' => current_time('mysql'),
            'type' => $type,
            'version' => AI_OPTIMIZER_VERSION,
            'data' => $data
        );
        
        $result = file_put_contents($backup_file, json_encode($backup_data, JSON_PRETTY_PRINT));
        
        if ($result === false) {
            self::log('Failed to create backup file', 'error', array('file' => $backup_file));
            return false;
        }
        
        self::log('Backup created', 'info', array('file' => $filename, 'type' => $type));
        
        return $filename;
    }
    
    /**
     * Send notification email
     */
    public static function send_notification($to, $subject, $message, $type = 'info') {
        $admin_email = get_option('admin_email');
        
        if (empty($to)) {
            $to = $admin_email;
        }
        
        $headers = array(
            'Content-Type: text/html; charset=UTF-8',
            'From: AI Website Optimizer <' . $admin_email . '>'
        );
        
        $template = self::get_email_template($type);
        $formatted_message = sprintf($template, $subject, $message);
        
        $result = wp_mail($to, $subject, $formatted_message, $headers);
        
        if (!$result) {
            self::log('Failed to send notification email', 'warning', array(
                'to' => $to,
                'subject' => $subject
            ));
        }
        
        return $result;
    }
    
    /**
     * Get email template
     */
    private static function get_email_template($type) {
        $color = array(
            'info' => '#17a2b8',
            'success' => '#28a745',
            'warning' => '#ffc107',
            'error' => '#dc3545'
        )[$type] ?? '#17a2b8';
        
        return '
        <div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;">
            <div style="background-color: ' . $color . '; color: white; padding: 20px; text-align: center;">
                <h1 style="margin: 0;">%s</h1>
            </div>
            <div style="padding: 20px; background-color: #f8f9fa;">
                %s
            </div>
            <div style="padding: 10px; text-align: center; font-size: 12px; color: #6c757d;">
                AI Website Optimizer Plugin - ' . site_url() . '
            </div>
        </div>';
    }
}
