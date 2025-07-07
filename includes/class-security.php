<?php
/**
 * 安全管理类
 */

if (!defined('ABSPATH')) {
    exit;
}

class AI_Optimizer_Security {
    
    private static $instance = null;
    
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
        // 安全检查钩子
        add_action('init', array($this, 'security_init'));
        add_action('wp_login', array($this, 'track_login'));
        add_action('wp_login_failed', array($this, 'track_failed_login'));
        
        // 前端安全
        add_action('template_redirect', array($this, 'check_rate_limits'));
        
        // AJAX安全检查
        add_action('wp_ajax_nopriv_ai_optimizer_track', array($this, 'track_frontend_data'));
        add_action('wp_ajax_ai_optimizer_track', array($this, 'track_frontend_data'));
    }
    
    /**
     * 安全初始化
     */
    public function security_init() {
        // 移除不必要的头信息
        remove_action('wp_head', 'wp_generator');
        remove_action('wp_head', 'wlwmanifest_link');
        remove_action('wp_head', 'rsd_link');
        
        // 限制登录尝试次数
        $this->limit_login_attempts();
    }
    
    /**
     * 验证nonce
     */
    public static function verify_nonce($nonce, $action = 'ai_optimizer_nonce') {
        return wp_verify_nonce($nonce, $action);
    }
    
    /**
     * 检查用户权限
     */
    public static function check_permission($capability = 'manage_options') {
        return current_user_can($capability);
    }
    
    /**
     * 清理用户输入
     */
    public static function sanitize_input($input, $type = 'text') {
        switch ($type) {
            case 'email':
                return sanitize_email($input);
            case 'url':
                return esc_url_raw($input);
            case 'textarea':
                return sanitize_textarea_field($input);
            case 'html':
                return wp_kses_post($input);
            case 'int':
                return intval($input);
            case 'float':
                return floatval($input);
            case 'array':
                return array_map('sanitize_text_field', (array)$input);
            default:
                return sanitize_text_field($input);
        }
    }
    
    /**
     * 验证API密钥
     */
    public static function validate_api_key($api_key) {
        if (empty($api_key)) {
            return false;
        }
        
        // Siliconflow API密钥格式验证
        if (strpos($api_key, 'sk-') === 0 && strlen($api_key) >= 20) {
            return true;
        }
        
        return false;
    }
    
    /**
     * 加密敏感数据
     */
    public static function encrypt_data($data) {
        if (!function_exists('openssl_encrypt')) {
            return base64_encode($data);
        }
        
        $key = self::get_encryption_key();
        $iv = openssl_random_pseudo_bytes(16);
        $encrypted = openssl_encrypt($data, 'AES-256-CBC', $key, 0, $iv);
        
        return base64_encode($iv . $encrypted);
    }
    
    /**
     * 解密敏感数据
     */
    public static function decrypt_data($encrypted_data) {
        if (!function_exists('openssl_decrypt')) {
            return base64_decode($encrypted_data);
        }
        
        $data = base64_decode($encrypted_data);
        $key = self::get_encryption_key();
        $iv = substr($data, 0, 16);
        $encrypted = substr($data, 16);
        
        return openssl_decrypt($encrypted, 'AES-256-CBC', $key, 0, $iv);
    }
    
    /**
     * 获取加密密钥
     */
    private static function get_encryption_key() {
        $key = get_option('ai_optimizer_encryption_key');
        
        if (!$key) {
            $key = wp_generate_password(64, true, true);
            update_option('ai_optimizer_encryption_key', $key);
        }
        
        return hash('sha256', $key . AUTH_KEY);
    }
    
    /**
     * 限制登录尝试
     */
    private function limit_login_attempts() {
        $max_attempts = 5;
        $lockout_duration = 30 * 60; // 30分钟
        
        $ip = AI_Optimizer_Utils::get_client_ip();
        $attempts_key = 'ai_optimizer_login_attempts_' . md5($ip);
        $lockout_key = 'ai_optimizer_lockout_' . md5($ip);
        
        // 检查是否被锁定
        if (get_transient($lockout_key)) {
            wp_die(__('由于多次登录失败，您的IP已被临时锁定。', 'ai-website-optimizer'));
        }
    }
    
    /**
     * 跟踪登录成功
     */
    public function track_login($user_login) {
        $ip = AI_Optimizer_Utils::get_client_ip();
        
        // 清除失败记录
        delete_transient('ai_optimizer_login_attempts_' . md5($ip));
        
        AI_Optimizer_Utils::log('用户登录成功', 'info', array(
            'user_login' => $user_login,
            'ip_address' => $ip
        ));
    }
    
    /**
     * 跟踪登录失败
     */
    public function track_failed_login($username) {
        $ip = AI_Optimizer_Utils::get_client_ip();
        $attempts_key = 'ai_optimizer_login_attempts_' . md5($ip);
        $lockout_key = 'ai_optimizer_lockout_' . md5($ip);
        
        $attempts = get_transient($attempts_key) ?: 0;
        $attempts++;
        
        set_transient($attempts_key, $attempts, 3600); // 1小时
        
        if ($attempts >= 5) {
            set_transient($lockout_key, true, 1800); // 30分钟锁定
            
            AI_Optimizer_Utils::log('IP被锁定', 'warning', array(
                'ip_address' => $ip,
                'attempts' => $attempts,
                'username' => $username
            ));
        } else {
            AI_Optimizer_Utils::log('登录失败', 'warning', array(
                'ip_address' => $ip,
                'attempts' => $attempts,
                'username' => $username
            ));
        }
    }
    
    /**
     * 检查访问频率限制
     */
    public function check_rate_limits() {
        if (!get_option('ai_optimizer_enable_rate_limiting', false)) {
            return;
        }
        
        $ip = AI_Optimizer_Utils::get_client_ip();
        $rate_key = 'ai_optimizer_rate_' . md5($ip);
        
        $requests = get_transient($rate_key) ?: 0;
        $max_requests = get_option('ai_optimizer_max_requests_per_minute', 60);
        
        if ($requests >= $max_requests) {
            wp_die(__('请求过于频繁，请稍后再试。', 'ai-website-optimizer'), 429);
        }
        
        set_transient($rate_key, $requests + 1, 60);
    }
    
    /**
     * 跟踪前端数据
     */
    public function track_frontend_data() {
        // 验证nonce
        if (!wp_verify_nonce($_POST['nonce'] ?? '', 'ai_optimizer_frontend_nonce')) {
            wp_die(__('安全检查失败', 'ai-website-optimizer'));
        }
        
        $data_type = sanitize_text_field($_POST['type'] ?? '');
        $data = $_POST['data'] ?? array();
        
        switch ($data_type) {
            case 'performance':
                $this->track_performance_data($data);
                break;
            case 'error':
                $this->track_error_data($data);
                break;
            default:
                wp_send_json_error(__('无效的数据类型', 'ai-website-optimizer'));
        }
        
        wp_send_json_success();
    }
    
    /**
     * 跟踪性能数据
     */
    private function track_performance_data($data) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'ai_optimizer_frontend_performance';
        
        $insert_data = array(
            'url' => esc_url_raw($data['url'] ?? ''),
            'page_load_time' => floatval($data['pageLoadTime'] ?? 0),
            'dom_content_loaded' => floatval($data['domContentLoaded'] ?? 0),
            'first_contentful_paint' => floatval($data['firstContentfulPaint'] ?? 0),
            'largest_contentful_paint' => floatval($data['largestContentfulPaint'] ?? 0),
            'cumulative_layout_shift' => floatval($data['cumulativeLayoutShift'] ?? 0),
            'first_input_delay' => floatval($data['firstInputDelay'] ?? 0),
            'user_agent' => sanitize_text_field($_SERVER['HTTP_USER_AGENT'] ?? ''),
            'viewport_width' => intval($data['viewportWidth'] ?? 0),
            'viewport_height' => intval($data['viewportHeight'] ?? 0),
            'connection_type' => sanitize_text_field($data['connectionType'] ?? ''),
            'created_at' => current_time('mysql')
        );
        
        $wpdb->insert($table_name, $insert_data);
    }
    
    /**
     * 跟踪错误数据
     */
    private function track_error_data($data) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'ai_optimizer_frontend_errors';
        
        $insert_data = array(
            'url' => esc_url_raw($data['url'] ?? ''),
            'error_message' => sanitize_text_field($data['message'] ?? ''),
            'error_stack' => sanitize_textarea_field($data['stack'] ?? ''),
            'line_number' => intval($data['lineno'] ?? 0),
            'column_number' => intval($data['colno'] ?? 0),
            'filename' => sanitize_text_field($data['filename'] ?? ''),
            'user_agent' => sanitize_text_field($_SERVER['HTTP_USER_AGENT'] ?? ''),
            'browser_info' => wp_json_encode($data['browserInfo'] ?? array()),
            'created_at' => current_time('mysql')
        );
        
        $wpdb->insert($table_name, $insert_data);
    }
    
    /**
     * 验证文件上传
     */
    public static function validate_file_upload($file) {
        $allowed_types = array('jpg', 'jpeg', 'png', 'gif', 'pdf', 'doc', 'docx');
        $max_size = 10 * 1024 * 1024; // 10MB
        
        if ($file['error'] !== UPLOAD_ERR_OK) {
            return array('success' => false, 'message' => '文件上传失败');
        }
        
        if ($file['size'] > $max_size) {
            return array('success' => false, 'message' => '文件大小超过限制');
        }
        
        $file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($file_extension, $allowed_types)) {
            return array('success' => false, 'message' => '不支持的文件类型');
        }
        
        return array('success' => true);
    }
    
    /**
     * 生成安全令牌
     */
    public static function generate_token($length = 32) {
        return bin2hex(random_bytes($length / 2));
    }
    
    /**
     * 验证令牌
     */
    public static function verify_token($token, $stored_token) {
        return hash_equals($stored_token, $token);
    }
    
    /**
     * 获取安全报告
     */
    public function get_security_report() {
        global $wpdb;
        
        $report = array();
        
        // 登录失败统计
        $failed_logins = $wpdb->get_var(
            "SELECT COUNT(*) FROM {$wpdb->prefix}ai_optimizer_logs 
             WHERE level = 'warning' AND message LIKE '%登录失败%' 
             AND created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)"
        );
        
        // 被锁定的IP数量
        $locked_ips = $wpdb->get_var(
            "SELECT COUNT(*) FROM {$wpdb->prefix}ai_optimizer_logs 
             WHERE level = 'warning' AND message LIKE '%IP被锁定%' 
             AND created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)"
        );
        
        // 异常访问统计
        $suspicious_activity = $wpdb->get_var(
            "SELECT COUNT(*) FROM {$wpdb->prefix}ai_optimizer_frontend_errors 
             WHERE created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)"
        );
        
        $report = array(
            'failed_logins_24h' => intval($failed_logins),
            'locked_ips_24h' => intval($locked_ips),
            'frontend_errors_24h' => intval($suspicious_activity),
            'security_level' => $this->calculate_security_level($failed_logins, $locked_ips, $suspicious_activity),
            'recommendations' => $this->get_security_recommendations()
        );
        
        return $report;
    }
    
    /**
     * 计算安全等级
     */
    private function calculate_security_level($failed_logins, $locked_ips, $errors) {
        $score = 100;
        
        // 根据各种安全指标降低分数
        $score -= min($failed_logins * 2, 30);
        $score -= min($locked_ips * 5, 25);
        $score -= min($errors * 0.5, 20);
        
        if ($score >= 90) return 'excellent';
        if ($score >= 75) return 'good';
        if ($score >= 60) return 'fair';
        return 'poor';
    }
    
    /**
     * 获取安全建议
     */
    private function get_security_recommendations() {
        $recommendations = array();
        
        // 检查SSL
        if (!is_ssl()) {
            $recommendations[] = '建议启用SSL证书以加密数据传输';
        }
        
        // 检查WordPress版本
        if (version_compare(get_bloginfo('version'), '6.0', '<')) {
            $recommendations[] = '建议更新WordPress到最新版本';
        }
        
        // 检查插件更新
        $outdated_plugins = get_site_transient('update_plugins');
        if (!empty($outdated_plugins->plugins)) {
            $recommendations[] = '有插件需要更新，建议及时更新';
        }
        
        // 检查强密码策略
        if (!get_option('ai_optimizer_enforce_strong_passwords', false)) {
            $recommendations[] = '建议启用强密码策略';
        }
        
        return $recommendations;
    }
    
    /**
     * 清理安全日志
     */
    public function cleanup_security_logs($days = 30) {
        global $wpdb;
        
        $cutoff_date = date('Y-m-d H:i:s', strtotime("-$days days"));
        
        $cleaned = $wpdb->query($wpdb->prepare(
            "DELETE FROM {$wpdb->prefix}ai_optimizer_logs 
             WHERE level IN ('warning', 'error') AND created_at < %s",
            $cutoff_date
        ));
        
        return $cleaned;
    }
    
    /**
     * 检查文件完整性
     */
    public function check_file_integrity() {
        $critical_files = array(
            ABSPATH . 'wp-config.php',
            ABSPATH . 'wp-settings.php',
            ABSPATH . 'wp-load.php',
            AI_OPTIMIZER_PLUGIN_PATH . 'ai-website-optimizer.php'
        );
        
        $integrity_report = array();
        
        foreach ($critical_files as $file) {
            if (file_exists($file)) {
                $integrity_report[basename($file)] = array(
                    'exists' => true,
                    'size' => filesize($file),
                    'modified' => filemtime($file),
                    'hash' => md5_file($file)
                );
            } else {
                $integrity_report[basename($file)] = array(
                    'exists' => false
                );
            }
        }
        
        return $integrity_report;
    }
}