<?php
/**
 * 管理后台主类
 */

if (!defined('ABSPATH')) {
    exit;
}

class AI_Optimizer_Admin {
    
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
        // 管理后台钩子
        add_action('admin_init', array($this, 'admin_init'));
        add_action('admin_notices', array($this, 'admin_notices'));
        
        // AJAX处理
        add_action('wp_ajax_ai_optimizer_dashboard_stats', array($this, 'get_dashboard_stats'));
        add_action('wp_ajax_ai_optimizer_run_monitoring', array($this, 'run_monitoring'));
        add_action('wp_ajax_ai_optimizer_save_settings', array($this, 'save_settings'));
    }
    
    /**
     * 管理后台初始化
     */
    public function admin_init() {
        // 检查必要条件
        $this->check_requirements();
        
        // 初始化设置
        $this->init_settings();
    }
    
    /**
     * 检查插件要求
     */
    private function check_requirements() {
        // 检查PHP版本
        if (version_compare(PHP_VERSION, '7.4', '<')) {
            add_action('admin_notices', function() {
                echo '<div class="notice notice-error"><p>AI智能网站优化器需要PHP 7.4或更高版本。</p></div>';
            });
        }
        
        // 检查WordPress版本
        if (version_compare(get_bloginfo('version'), '5.0', '<')) {
            add_action('admin_notices', function() {
                echo '<div class="notice notice-error"><p>AI智能网站优化器需要WordPress 5.0或更高版本。</p></div>';
            });
        }
        
        // 检查必要扩展
        if (!extension_loaded('curl')) {
            add_action('admin_notices', function() {
                echo '<div class="notice notice-error"><p>AI智能网站优化器需要启用cURL扩展。</p></div>';
            });
        }
    }
    
    /**
     * 初始化设置
     */
    private function init_settings() {
        // 注册设置
        register_setting('ai_optimizer_settings', 'ai_optimizer_api_key');
        register_setting('ai_optimizer_settings', 'ai_optimizer_monitoring_enabled');
        register_setting('ai_optimizer_settings', 'ai_optimizer_seo_auto_optimize');
        register_setting('ai_optimizer_settings', 'ai_optimizer_frontend_monitoring');
    }
    
    /**
     * 显示管理通知
     */
    public function admin_notices() {
        // API密钥未设置提醒
        if (empty(get_option('ai_optimizer_api_key'))) {
            $settings_url = admin_url('admin.php?page=ai-optimizer-settings');
            echo '<div class="notice notice-warning is-dismissible">';
            echo '<p>请先<a href="' . esc_url($settings_url) . '">配置Siliconflow API密钥</a>以启用AI功能。</p>';
            echo '</div>';
        }
        
        // 显示成功消息
        if (isset($_GET['settings-updated']) && $_GET['settings-updated']) {
            echo '<div class="notice notice-success is-dismissible">';
            echo '<p>设置已保存。</p>';
            echo '</div>';
        }
    }
    
    /**
     * 获取仪表盘统计数据
     */
    public function get_dashboard_stats() {
        // 验证nonce
        if (!wp_verify_nonce($_POST['nonce'], 'ai_optimizer_nonce')) {
            wp_die(__('安全检查失败', 'ai-website-optimizer'));
        }
        
        global $wpdb;
        
        $stats = array();
        
        // 监控数据统计
        $monitoring_table = $wpdb->prefix . 'ai_optimizer_monitoring';
        $stats['total_monitored_pages'] = $wpdb->get_var(
            "SELECT COUNT(DISTINCT url) FROM $monitoring_table"
        );
        
        $stats['avg_load_time'] = $wpdb->get_var(
            "SELECT AVG(load_time) FROM $monitoring_table 
             WHERE created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)"
        );
        
        // SEO数据统计
        $seo_table = $wpdb->prefix . 'ai_optimizer_seo_analysis';
        $stats['avg_seo_score'] = $wpdb->get_var(
            "SELECT AVG(seo_score) FROM $seo_table"
        );
        
        $stats['total_seo_issues'] = $wpdb->get_var(
            "SELECT COUNT(*) FROM {$wpdb->prefix}ai_optimizer_seo_suggestions 
             WHERE status = 'pending'"
        );
        
        // AI生成统计
        $generations_table = $wpdb->prefix . 'ai_optimizer_generations';
        $stats['total_generations'] = $wpdb->get_var(
            "SELECT COUNT(*) FROM $generations_table"
        );
        
        $stats['completed_generations'] = $wpdb->get_var(
            "SELECT COUNT(*) FROM $generations_table 
             WHERE status = 'completed'"
        );
        
        // 错误统计
        $stats['frontend_errors'] = $wpdb->get_var(
            "SELECT COUNT(*) FROM {$wpdb->prefix}ai_optimizer_frontend_errors 
             WHERE created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)"
        );
        
        // API使用统计
        $stats['api_calls_today'] = $wpdb->get_var(
            "SELECT COUNT(*) FROM {$wpdb->prefix}ai_optimizer_api_usage 
             WHERE DATE(created_at) = CURDATE()"
        );
        
        wp_send_json_success($stats);
    }
    
    /**
     * 运行监控
     */
    public function run_monitoring() {
        // 验证nonce和权限
        if (!wp_verify_nonce($_POST['nonce'], 'ai_optimizer_nonce')) {
            wp_die(__('安全检查失败', 'ai-website-optimizer'));
        }
        
        if (!current_user_can('manage_options')) {
            wp_die(__('权限不足', 'ai-website-optimizer'));
        }
        
        // 运行监控
        $monitor = new AI_Optimizer_Monitor();
        $result = $monitor->collect_data();
        
        wp_send_json_success($result);
    }
    
    /**
     * 保存设置
     */
    public function save_settings() {
        // 验证nonce和权限
        if (!wp_verify_nonce($_POST['nonce'], 'ai_optimizer_nonce')) {
            wp_die(__('安全检查失败', 'ai-website-optimizer'));
        }
        
        if (!current_user_can('manage_options')) {
            wp_die(__('权限不足', 'ai-website-optimizer'));
        }
        
        $settings = $_POST['settings'] ?? array();
        
        foreach ($settings as $key => $value) {
            $option_name = 'ai_optimizer_' . sanitize_key($key);
            update_option($option_name, sanitize_text_field($value));
        }
        
        wp_send_json_success(__('设置已保存', 'ai-website-optimizer'));
    }
    
    /**
     * 获取系统状态
     */
    public function get_system_status() {
        $status = array(
            'php_version' => PHP_VERSION,
            'wp_version' => get_bloginfo('version'),
            'plugin_version' => AI_OPTIMIZER_VERSION,
            'memory_limit' => ini_get('memory_limit'),
            'max_execution_time' => ini_get('max_execution_time'),
            'curl_enabled' => extension_loaded('curl'),
            'openssl_enabled' => extension_loaded('openssl'),
            'api_key_configured' => !empty(get_option('ai_optimizer_api_key')),
            'monitoring_enabled' => get_option('ai_optimizer_monitoring_enabled', false),
            'seo_enabled' => get_option('ai_optimizer_seo_auto_optimize', false)
        );
        
        return $status;
    }
    
    /**
     * 添加管理后台样式
     */
    public function add_admin_styles() {
        $custom_css = "
        .ai-optimizer-dashboard {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        }
        
        .ai-optimizer-card {
            background: #fff;
            border: 1px solid #e1e5e9;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .ai-optimizer-stat {
            text-align: center;
            padding: 15px;
        }
        
        .ai-optimizer-stat .number {
            font-size: 2em;
            font-weight: bold;
            color: #165DFF;
        }
        
        .ai-optimizer-stat .label {
            color: #666;
            font-size: 0.9em;
        }
        
        .ai-optimizer-progress {
            height: 6px;
            background: #f0f0f0;
            border-radius: 3px;
            overflow: hidden;
        }
        
        .ai-optimizer-progress-bar {
            height: 100%;
            background: linear-gradient(90deg, #165DFF, #00F5D4);
            transition: width 0.3s ease;
        }
        
        .ai-optimizer-btn {
            background: #165DFF;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            transition: background 0.3s;
        }
        
        .ai-optimizer-btn:hover {
            background: #0d47cc;
        }
        
        .ai-optimizer-alert {
            padding: 12px;
            border-radius: 5px;
            margin: 10px 0;
        }
        
        .ai-optimizer-alert.success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .ai-optimizer-alert.warning {
            background: #fff3cd;
            color: #856404;
            border: 1px solid #ffeaa7;
        }
        
        .ai-optimizer-alert.error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        ";
        
        wp_add_inline_style('ai-optimizer-admin', $custom_css);
    }
    
    /**
     * 生成nonce字段
     */
    public static function nonce_field() {
        wp_nonce_field('ai_optimizer_nonce', 'ai_optimizer_nonce');
    }
    
    /**
     * 验证管理后台权限
     */
    public static function verify_admin_access() {
        if (!current_user_can('manage_options')) {
            wp_die(__('您没有权限访问此页面。', 'ai-website-optimizer'));
        }
    }
    
    /**
     * 格式化文件大小
     */
    public static function format_bytes($bytes, $precision = 2) {
        $units = array('B', 'KB', 'MB', 'GB', 'TB');
        
        for ($i = 0; $bytes > 1024; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, $precision) . ' ' . $units[$i];
    }
    
    /**
     * 生成状态指示器
     */
    public static function status_indicator($status, $label = '') {
        $class = $status ? 'success' : 'error';
        $icon = $status ? '✓' : '✗';
        $text = $status ? '正常' : '异常';
        
        if (!empty($label)) {
            $text = $label;
        }
        
        return sprintf(
            '<span class="ai-optimizer-status %s">%s %s</span>',
            esc_attr($class),
            $icon,
            esc_html($text)
        );
    }
    
    /**
     * 生成进度条
     */
    public static function progress_bar($percentage, $label = '') {
        $percentage = max(0, min(100, $percentage));
        
        $html = '<div class="ai-optimizer-progress">';
        $html .= '<div class="ai-optimizer-progress-bar" style="width: ' . $percentage . '%"></div>';
        $html .= '</div>';
        
        if (!empty($label)) {
            $html .= '<div class="ai-optimizer-progress-label">' . esc_html($label) . '</div>';
        }
        
        return $html;
    }
}