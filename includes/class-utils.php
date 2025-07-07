<?php
/**
 * AI Website Optimizer - 工具类
 * 
 * @package AI_Website_Optimizer
 * @subpackage Utils
 */

// 防止直接访问
if (!defined('ABSPATH')) {
    exit;
}

/**
 * 工具类
 */
class AI_Optimizer_Utils {
    
    /**
     * 记录日志
     */
    public static function log($message, $level = 'info', $context = array()) {
        if (!WP_DEBUG) {
            return;
        }
        
        $log_message = sprintf(
            '[%s] [%s] %s %s',
            date('Y-m-d H:i:s'),
            strtoupper($level),
            $message,
            !empty($context) ? json_encode($context) : ''
        );
        
        error_log($log_message);
        
        // 同时保存到数据库
        global $wpdb;
        $table_name = $wpdb->prefix . 'ai_optimizer_logs';
        
        if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") == $table_name) {
            $wpdb->insert(
                $table_name,
                array(
                    'level' => $level,
                    'message' => $message,
                    'context' => json_encode($context),
                    'created_at' => current_time('mysql')
                )
            );
        }
    }
    
    /**
     * 清理HTML输入
     */
    public static function sanitize_html($input) {
        return wp_kses_post($input);
    }
    
    /**
     * 获取客户端IP
     */
    public static function get_client_ip() {
        $ip_keys = array('HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR');
        
        foreach ($ip_keys as $key) {
            if (array_key_exists($key, $_SERVER) === true) {
                foreach (explode(',', $_SERVER[$key]) as $ip) {
                    $ip = trim($ip);
                    
                    if (filter_var($ip, FILTER_VALIDATE_IP, 
                        FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false) {
                        return $ip;
                    }
                }
            }
        }
        
        return '0.0.0.0';
    }
    
    /**
     * 格式化字节大小
     */
    public static function format_bytes($bytes, $precision = 2) {
        $units = array('B', 'KB', 'MB', 'GB', 'TB');
        
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        
        $bytes /= pow(1024, $pow);
        
        return round($bytes, $precision) . ' ' . $units[$pow];
    }
    
    /**
     * 生成随机字符串
     */
    public static function generate_random_string($length = 16) {
        return wp_generate_password($length, false, false);
    }
    
    /**
     * 验证URL
     */
    public static function is_valid_url($url) {
        return filter_var($url, FILTER_VALIDATE_URL) !== false;
    }
    
    /**
     * 获取时间差描述
     */
    public static function time_ago($timestamp) {
        $time_ago = strtotime($timestamp);
        $current_time = time();
        $time_difference = $current_time - $time_ago;
        $seconds = $time_difference;
        
        $minutes = round($seconds / 60);
        $hours = round($seconds / 3600);
        $days = round($seconds / 86400);
        $weeks = round($seconds / 604800);
        $months = round($seconds / 2629440);
        $years = round($seconds / 31553280);
        
        if ($seconds <= 60) {
            return "刚刚";
        } else if ($minutes <= 60) {
            return $minutes == 1 ? "1分钟前" : "$minutes 分钟前";
        } else if ($hours <= 24) {
            return $hours == 1 ? "1小时前" : "$hours 小时前";
        } else if ($days <= 7) {
            return $days == 1 ? "1天前" : "$days 天前";
        } else if ($weeks <= 4.3) {
            return $weeks == 1 ? "1周前" : "$weeks 周前";
        } else if ($months <= 12) {
            return $months == 1 ? "1个月前" : "$months 个月前";
        } else {
            return $years == 1 ? "1年前" : "$years 年前";
        }
    }
    
    /**
     * 清理过期数据
     */
    public static function cleanup_expired_data($table, $date_column, $days = 30) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . $table;
        $cutoff_date = date('Y-m-d H:i:s', strtotime("-$days days"));
        
        return $wpdb->query($wpdb->prepare(
            "DELETE FROM $table_name WHERE $date_column < %s",
            $cutoff_date
        ));
    }
    
    /**
     * 验证nonce
     */
    public static function verify_nonce($nonce, $action) {
        if (!wp_verify_nonce($nonce, $action)) {
            wp_die('安全验证失败');
        }
    }
    
    /**
     * 获取当前用户IP地址的地理位置
     */
    public static function get_ip_location($ip = null) {
        if (!$ip) {
            $ip = self::get_client_ip();
        }
        
        // 这里可以集成IP地理位置API
        // 暂时返回默认值
        return array(
            'country' => 'CN',
            'city' => 'Unknown'
        );
    }
    
    /**
     * 加密数据
     */
    public static function encrypt($data, $key = null) {
        if (!$key) {
            $key = wp_salt('auth');
        }
        
        $method = 'AES-256-CBC';
        $key = substr(hash('sha256', $key, true), 0, 32);
        $iv = openssl_random_pseudo_bytes(16);
        
        $encrypted = openssl_encrypt($data, $method, $key, 0, $iv);
        
        return base64_encode($encrypted . '::' . $iv);
    }
    
    /**
     * 解密数据
     */
    public static function decrypt($data, $key = null) {
        if (!$key) {
            $key = wp_salt('auth');
        }
        
        $method = 'AES-256-CBC';
        $key = substr(hash('sha256', $key, true), 0, 32);
        
        list($encrypted_data, $iv) = explode('::', base64_decode($data), 2);
        
        return openssl_decrypt($encrypted_data, $method, $key, 0, $iv);
    }
}