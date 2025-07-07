<?php
/**
 * 工具类 - 提供各种实用功能
 */

if (!defined('ABSPATH')) {
    exit;
}

class AI_Optimizer_Utils {
    
    /**
     * 获取客户端IP地址
     */
    public static function get_client_ip() {
        $ip_headers = array(
            'HTTP_CF_CONNECTING_IP',     // Cloudflare
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
                
                // 处理多个IP的情况（以逗号分隔）
                if (strpos($ip, ',') !== false) {
                    $ip = trim(explode(',', $ip)[0]);
                }
                
                // 验证IP地址格式
                if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                    return $ip;
                }
            }
        }
        
        return $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
    }
    
    /**
     * 记录日志
     */
    public static function log($message, $level = 'info', $context = array()) {
        if (!AI_Optimizer_Settings::get('enable_logging', true)) {
            return;
        }
        
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'ai_optimizer_logs';
        
        // 创建日志表（如果不存在）
        self::create_logs_table();
        
        $log_data = array(
            'level' => sanitize_text_field($level),
            'message' => sanitize_text_field($message),
            'context' => wp_json_encode($context),
            'user_id' => get_current_user_id(),
            'ip_address' => self::get_client_ip(),
            'user_agent' => sanitize_text_field($_SERVER['HTTP_USER_AGENT'] ?? ''),
            'url' => sanitize_text_field($_SERVER['REQUEST_URI'] ?? ''),
            'created_at' => current_time('mysql')
        );
        
        $wpdb->insert($table_name, $log_data);
        
        // 如果启用了调试模式，也写入WordPress日志
        if (defined('WP_DEBUG') && WP_DEBUG && defined('WP_DEBUG_LOG') && WP_DEBUG_LOG) {
            error_log(sprintf(
                '[AI Optimizer] [%s] %s %s',
                strtoupper($level),
                $message,
                !empty($context) ? '- Context: ' . wp_json_encode($context) : ''
            ));
        }
    }
    
    /**
     * 创建日志表
     */
    private static function create_logs_table() {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'ai_optimizer_logs';
        
        $charset_collate = $wpdb->get_charset_collate();
        
        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            level varchar(20) NOT NULL DEFAULT 'info',
            message text NOT NULL,
            context longtext,
            user_id bigint(20) unsigned DEFAULT NULL,
            ip_address varchar(45),
            user_agent text,
            url text,
            created_at datetime NOT NULL,
            PRIMARY KEY (id),
            KEY level (level),
            KEY user_id (user_id),
            KEY created_at (created_at)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }
    
    /**
     * 格式化文件大小
     */
    public static function format_bytes($size, $precision = 2) {
        if ($size == 0) {
            return '0 B';
        }
        
        $base = log($size, 1024);
        $suffixes = array('B', 'KB', 'MB', 'GB', 'TB');
        
        return round(pow(1024, $base - floor($base)), $precision) . ' ' . $suffixes[floor($base)];
    }
    
    /**
     * 生成随机字符串
     */
    public static function generate_random_string($length = 32, $special_chars = false) {
        $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        
        if ($special_chars) {
            $chars .= '!@#$%^&*()_+-=[]{}|;:,.<>?';
        }
        
        $chars_length = strlen($chars);
        $random_string = '';
        
        for ($i = 0; $i < $length; $i++) {
            $random_string .= $chars[random_int(0, $chars_length - 1)];
        }
        
        return $random_string;
    }
    
    /**
     * 验证API密钥格式
     */
    public static function validate_api_key($api_key) {
        if (empty($api_key)) {
            return false;
        }
        
        // Siliconflow API密钥格式验证
        if (strpos($api_key, 'sk-') === 0 && strlen($api_key) >= 32) {
            return true;
        }
        
        return false;
    }
    
    /**
     * 格式化时间差
     */
    public static function time_elapsed($datetime, $full = false) {
        $now = new DateTime;
        $ago = new DateTime($datetime);
        $diff = $now->diff($ago);
        
        $diff->w = floor($diff->d / 7);
        $diff->d -= $diff->w * 7;
        
        $string = array(
            'y' => '年',
            'm' => '月', 
            'w' => '周',
            'd' => '天',
            'h' => '小时',
            'i' => '分钟',
            's' => '秒',
        );
        
        foreach ($string as $k => &$v) {
            if ($diff->$k) {
                $v = $diff->$k . $v;
            } else {
                unset($string[$k]);
            }
        }
        
        if (!$full) {
            $string = array_slice($string, 0, 1);
        }
        
        return $string ? implode(', ', $string) . '前' : '刚才';
    }
    
    /**
     * 清理HTML标签
     */
    public static function clean_html($content, $allowed_tags = '') {
        return wp_strip_all_tags($content, $allowed_tags);
    }
    
    /**
     * 截取文本
     */
    public static function truncate_text($text, $length = 100, $suffix = '...') {
        if (mb_strlen($text) <= $length) {
            return $text;
        }
        
        return mb_substr($text, 0, $length) . $suffix;
    }
    
    /**
     * 验证URL格式
     */
    public static function validate_url($url) {
        return filter_var($url, FILTER_VALIDATE_URL) !== false;
    }
    
    /**
     * 获取网页标题
     */
    public static function get_page_title($url) {
        if (!self::validate_url($url)) {
            return false;
        }
        
        $response = wp_remote_get($url, array(
            'timeout' => 10,
            'user-agent' => 'AI-Website-Optimizer/' . AI_OPTIMIZER_VERSION
        ));
        
        if (is_wp_error($response)) {
            return false;
        }
        
        $body = wp_remote_retrieve_body($response);
        
        if (preg_match('/<title[^>]*>(.*?)<\/title>/is', $body, $matches)) {
            return html_entity_decode(trim($matches[1]), ENT_QUOTES, 'UTF-8');
        }
        
        return false;
    }
    
    /**
     * 压缩JSON数据
     */
    public static function compress_json($data) {
        $json = wp_json_encode($data);
        
        if (function_exists('gzencode')) {
            return base64_encode(gzencode($json));
        }
        
        return base64_encode($json);
    }
    
    /**
     * 解压JSON数据
     */
    public static function decompress_json($compressed_data) {
        $decoded = base64_decode($compressed_data);
        
        if (function_exists('gzdecode')) {
            $decompressed = gzdecode($decoded);
            if ($decompressed !== false) {
                return json_decode($decompressed, true);
            }
        }
        
        return json_decode($decoded, true);
    }
    
    /**
     * 获取内存使用情况
     */
    public static function get_memory_usage() {
        return array(
            'current' => memory_get_usage(true),
            'peak' => memory_get_peak_usage(true),
            'limit' => ini_get('memory_limit'),
            'usage_percentage' => round((memory_get_usage(true) / self::convert_to_bytes(ini_get('memory_limit'))) * 100, 2)
        );
    }
    
    /**
     * 转换内存大小为字节
     */
    private static function convert_to_bytes($value) {
        $value = trim($value);
        $last = strtolower($value[strlen($value) - 1]);
        $value = (int) $value;
        
        switch ($last) {
            case 'g':
                $value *= 1024;
            case 'm':
                $value *= 1024;
            case 'k':
                $value *= 1024;
        }
        
        return $value;
    }
    
    /**
     * 检查插件依赖
     */
    public static function check_dependencies() {
        $dependencies = array(
            'php_version' => array(
                'required' => '7.4',
                'current' => PHP_VERSION,
                'status' => version_compare(PHP_VERSION, '7.4', '>=')
            ),
            'wordpress_version' => array(
                'required' => '5.0',
                'current' => get_bloginfo('version'),
                'status' => version_compare(get_bloginfo('version'), '5.0', '>=')
            ),
            'curl_extension' => array(
                'required' => true,
                'current' => extension_loaded('curl'),
                'status' => extension_loaded('curl')
            ),
            'json_extension' => array(
                'required' => true,
                'current' => extension_loaded('json'),
                'status' => extension_loaded('json')
            ),
            'openssl_extension' => array(
                'required' => true,
                'current' => extension_loaded('openssl'),
                'status' => extension_loaded('openssl')
            ),
            'gd_extension' => array(
                'required' => false,
                'current' => extension_loaded('gd'),
                'status' => extension_loaded('gd')
            )
        );
        
        return $dependencies;
    }
    
    /**
     * 获取系统信息
     */
    public static function get_system_info() {
        global $wpdb;
        
        return array(
            'plugin_version' => AI_OPTIMIZER_VERSION,
            'wordpress_version' => get_bloginfo('version'),
            'php_version' => PHP_VERSION,
            'mysql_version' => $wpdb->db_version(),
            'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown',
            'max_execution_time' => ini_get('max_execution_time'),
            'memory_limit' => ini_get('memory_limit'),
            'upload_max_filesize' => ini_get('upload_max_filesize'),
            'post_max_size' => ini_get('post_max_size'),
            'timezone' => wp_timezone_string(),
            'site_url' => get_site_url(),
            'admin_email' => get_option('admin_email'),
            'debug_mode' => defined('WP_DEBUG') && WP_DEBUG,
            'multisite' => is_multisite(),
            'ssl_enabled' => is_ssl()
        );
    }
    
    /**
     * 清理过期数据
     */
    public static function cleanup_expired_data() {
        global $wpdb;
        
        $retention_days = AI_Optimizer_Settings::get('data_retention_days', 30);
        $cutoff_date = date('Y-m-d H:i:s', strtotime("-$retention_days days"));
        
        // 清理监控数据
        $tables = array(
            $wpdb->prefix . 'ai_optimizer_monitoring',
            $wpdb->prefix . 'ai_optimizer_frontend_performance',
            $wpdb->prefix . 'ai_optimizer_frontend_errors',
            $wpdb->prefix . 'ai_optimizer_logs'
        );
        
        $cleaned_records = 0;
        
        foreach ($tables as $table) {
            if ($wpdb->get_var("SHOW TABLES LIKE '$table'")) {
                $result = $wpdb->query($wpdb->prepare(
                    "DELETE FROM $table WHERE created_at < %s",
                    $cutoff_date
                ));
                
                if ($result !== false) {
                    $cleaned_records += $result;
                }
            }
        }
        
        self::log("清理了 $cleaned_records 条过期数据记录", 'info', array(
            'retention_days' => $retention_days,
            'cutoff_date' => $cutoff_date
        ));
        
        return $cleaned_records;
    }
    
    /**
     * 优化数据库表
     */
    public static function optimize_database_tables() {
        global $wpdb;
        
        $tables = array(
            $wpdb->prefix . 'ai_optimizer_monitoring',
            $wpdb->prefix . 'ai_optimizer_analysis',
            $wpdb->prefix . 'ai_optimizer_seo_analysis',
            $wpdb->prefix . 'ai_optimizer_seo_suggestions',
            $wpdb->prefix . 'ai_optimizer_code_issues',
            $wpdb->prefix . 'ai_optimizer_generations',
            $wpdb->prefix . 'ai_optimizer_video_requests',
            $wpdb->prefix . 'ai_optimizer_api_usage',
            $wpdb->prefix . 'ai_optimizer_logs',
            $wpdb->prefix . 'ai_optimizer_collected_content',
            $wpdb->prefix . 'ai_optimizer_frontend_performance',
            $wpdb->prefix . 'ai_optimizer_frontend_errors'
        );
        
        $optimized_tables = 0;
        
        foreach ($tables as $table) {
            if ($wpdb->get_var("SHOW TABLES LIKE '$table'")) {
                $result = $wpdb->query("OPTIMIZE TABLE $table");
                if ($result !== false) {
                    $optimized_tables++;
                }
            }
        }
        
        self::log("优化了 $optimized_tables 个数据库表", 'info');
        
        return $optimized_tables;
    }
    
    /**
     * 生成唯一ID
     */
    public static function generate_unique_id($prefix = '') {
        return $prefix . uniqid() . '_' . random_int(1000, 9999);
    }
    
    /**
     * 验证nonce安全
     */
    public static function verify_nonce($nonce, $action = 'ai_optimizer_nonce') {
        return wp_verify_nonce($nonce, $action);
    }
    
    /**
     * 检查用户权限
     */
    public static function check_user_capability($capability = 'manage_options') {
        return current_user_can($capability);
    }
    
    /**
     * 安全重定向
     */
    public static function safe_redirect($location, $status = 302) {
        wp_safe_redirect($location, $status);
        exit;
    }
    
    /**
     * 获取插件数据目录
     */
    public static function get_data_directory() {
        $upload_dir = wp_upload_dir();
        $data_dir = $upload_dir['basedir'] . '/ai-optimizer-data/';
        
        if (!file_exists($data_dir)) {
            wp_mkdir_p($data_dir);
            
            // 创建.htaccess文件保护目录
            $htaccess_content = "Order deny,allow\nDeny from all\n";
            file_put_contents($data_dir . '.htaccess', $htaccess_content);
            
            // 创建index.php文件
            file_put_contents($data_dir . 'index.php', '<?php // Silence is golden.');
        }
        
        return $data_dir;
    }
    
    /**
     * 保存文件到数据目录
     */
    public static function save_file_to_data_dir($filename, $content) {
        $data_dir = self::get_data_directory();
        $file_path = $data_dir . sanitize_file_name($filename);
        
        $result = file_put_contents($file_path, $content);
        
        if ($result !== false) {
            self::log("文件保存成功", 'info', array(
                'filename' => $filename,
                'size' => $result
            ));
        } else {
            self::log("文件保存失败", 'error', array(
                'filename' => $filename
            ));
        }
        
        return $result !== false ? $file_path : false;
    }
    
    /**
     * 从数据目录读取文件
     */
    public static function read_file_from_data_dir($filename) {
        $data_dir = self::get_data_directory();
        $file_path = $data_dir . sanitize_file_name($filename);
        
        if (file_exists($file_path)) {
            return file_get_contents($file_path);
        }
        
        return false;
    }
    
    /**
     * 删除数据目录中的文件
     */
    public static function delete_file_from_data_dir($filename) {
        $data_dir = self::get_data_directory();
        $file_path = $data_dir . sanitize_file_name($filename);
        
        if (file_exists($file_path)) {
            return unlink($file_path);
        }
        
        return false;
    }
}