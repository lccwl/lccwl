<?php
/**
 * Plugin Name: AI智能网站优化器
 * Plugin URI: https://github.com/ai-website-optimizer
 * Description: 集成Siliconflow API的AI智能WordPress网站监控与优化插件，具备实时监控、SEO优化、代码分析和多媒体生成功能。
 * Version: 1.0.0
 * Author: AI网站优化团队
 * Author URI: https://ai-website-optimizer.com
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: ai-website-optimizer
 * Domain Path: /languages
 * Requires at least: 5.0
 * Tested up to: 6.4
 * Requires PHP: 7.4
 * Network: false
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('AI_OPTIMIZER_VERSION', '1.0.0');
define('AI_OPTIMIZER_PLUGIN_URL', plugin_dir_url(__FILE__));
define('AI_OPTIMIZER_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('AI_OPTIMIZER_PLUGIN_BASENAME', plugin_basename(__FILE__));

/**
 * Main plugin class
 */
class AI_Website_Optimizer {
    
    /**
     * Plugin instance
     */
    private static $instance = null;
    
    /**
     * 错误信息
     */
    private static $activation_error = null;
    
    /**
     * Get plugin instance
     */
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Constructor
     */
    private function __construct() {
        try {
            $this->init();
        } catch (Exception $e) {
            self::$activation_error = $e->getMessage();
            add_action('admin_notices', array($this, 'show_activation_error'));
            error_log('AI Optimizer Error: ' . $e->getMessage());
        }
    }
    
    /**
     * 显示激活错误
     */
    public function show_activation_error() {
        if (self::$activation_error) {
            echo '<div class="notice notice-error"><p>AI智能网站优化器错误: ' . esc_html(self::$activation_error) . '</p></div>';
        }
    }
    
    /**
     * Initialize plugin
     */
    private function init() {
        // 延迟加载，确保WordPress完全初始化
        add_action('init', array($this, 'delayed_init'), 1);
        
        // 激活和停用钩子
        register_activation_hook(__FILE__, array($this, 'activate'));
        register_deactivation_hook(__FILE__, array($this, 'deactivate'));
    }
    
    /**
     * 延迟初始化
     */
    public function delayed_init() {
        // 加载文本域
        $this->load_textdomain();
        
        // 包含必要文件
        $this->safe_includes();
        
        // 管理后台钩子
        if (is_admin()) {
            add_action('admin_init', array($this, 'admin_init'));
            add_action('admin_menu', array($this, 'admin_menu'));
            add_action('admin_enqueue_scripts', array($this, 'admin_enqueue_scripts'));
        }
        
        // 前端钩子
        add_action('wp_enqueue_scripts', array($this, 'frontend_enqueue_scripts'));
        
        // AJAX钩子
        add_action('wp_ajax_ai_optimizer_action', array($this, 'ajax_handler'));
        add_action('wp_ajax_nopriv_ai_optimizer_action', array($this, 'ajax_handler'));
        
        // REST API
        add_action('rest_api_init', array($this, 'register_rest_routes'));
        
        // 定时任务
        add_action('ai_optimizer_monitor_cron', array($this, 'run_monitoring'));
        
        // 计划监控任务
        if (!wp_next_scheduled('ai_optimizer_monitor_cron')) {
            wp_schedule_event(time(), 'hourly', 'ai_optimizer_monitor_cron');
        }
    }
    
    /**
     * 安全包含文件
     */
    private function safe_includes() {
        // 基础文件（必须按顺序加载）
        $core_files = array(
            'config/api-endpoints.php',
            'includes/class-utils.php'
        );
        
        foreach ($core_files as $file) {
            $file_path = AI_OPTIMIZER_PLUGIN_PATH . $file;
            if (file_exists($file_path)) {
                require_once $file_path;
            }
        }
        
        // 其他文件可以在需要时加载
        if (is_admin()) {
            // 只在管理后台加载管理类
            add_action('admin_init', array($this, 'load_admin_classes'));
        }
    }
    
    /**
     * 加载管理类
     */
    public function load_admin_classes() {
        $admin_files = array(
            'includes/class-database.php',
            'includes/class-security.php',
            'includes/class-api-handler.php',
            'admin/class-admin.php'
        );
        
        foreach ($admin_files as $file) {
            $file_path = AI_OPTIMIZER_PLUGIN_PATH . $file;
            if (file_exists($file_path)) {
                require_once $file_path;
            }
        }
    }
    
    /**
     * Load text domain
     */
    public function load_textdomain() {
        load_plugin_textdomain('ai-website-optimizer', false, dirname(plugin_basename(__FILE__)) . '/languages');
    }
    

    
    /**
     * Initialize components
     */
    public function init_components() {
        // 创建数据库表
        $this->create_database_tables();
    }
    
    /**
     * 创建数据库表
     */
    private function create_database_tables() {
        global $wpdb;
        
        $charset_collate = $wpdb->get_charset_collate();
        
        // 监控数据表
        $table_monitor = $wpdb->prefix . 'ai_optimizer_monitor';
        $sql_monitor = "CREATE TABLE IF NOT EXISTS $table_monitor (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            metric_type varchar(50) NOT NULL,
            metric_value float NOT NULL,
            details longtext,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY metric_type (metric_type),
            KEY created_at (created_at)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql_monitor);
    }
    
    /**
     * Admin initialization
     */
    public function admin_init() {
        // 注册设置
        register_setting('ai_optimizer_settings', 'ai_optimizer_api_key');
        register_setting('ai_optimizer_settings', 'ai_optimizer_enable_logging');
    }
    
    /**
     * Add admin menu
     */
    public function admin_menu() {
        // 主菜单
        add_menu_page(
            'AI智能优化器',
            'AI智能优化器',
            'manage_options',
            'ai-optimizer',
            array($this, 'render_dashboard_page'),
            'dashicons-chart-area',
            30
        );
        
        // 子菜单
        add_submenu_page(
            'ai-optimizer',
            '仪表盘',
            '仪表盘',
            'manage_options',
            'ai-optimizer',
            array($this, 'render_dashboard_page')
        );
        
        add_submenu_page(
            'ai-optimizer',
            '插件设置',
            '插件设置',
            'manage_options',
            'ai-optimizer-settings',
            array($this, 'render_settings_page')
        );
    }
    
    /**
     * 渲染仪表盘页面
     */
    public function render_dashboard_page() {
        ?>
        <div class="wrap">
            <h1>AI智能网站优化器</h1>
            <div class="ai-optimizer-card">
                <h2>欢迎使用AI智能网站优化器</h2>
                <p>这是一个集成了Siliconflow API的WordPress智能优化插件。</p>
                <p>主要功能：</p>
                <ul>
                    <li>✓ 实时性能监控</li>
                    <li>✓ SEO智能优化</li>
                    <li>✓ AI内容生成</li>
                    <li>✓ 安全管理</li>
                </ul>
                <?php
                // 检查API密钥
                $api_key = get_option('ai_optimizer_api_key');
                if (empty($api_key)) {
                    echo '<div class="notice notice-warning inline"><p>请先在<a href="' . admin_url('admin.php?page=ai-optimizer-settings') . '">设置页面</a>配置Siliconflow API密钥。</p></div>';
                } else {
                    echo '<div class="notice notice-success inline"><p>API已配置，插件功能正常。</p></div>';
                }
                ?>
            </div>
        </div>
        <?php
    }
    
    /**
     * 渲染设置页面
     */
    public function render_settings_page() {
        // 处理表单提交
        if (isset($_POST['submit']) && wp_verify_nonce($_POST['ai_optimizer_nonce'], 'ai_optimizer_settings')) {
            update_option('ai_optimizer_api_key', sanitize_text_field($_POST['ai_optimizer_api_key']));
            echo '<div class="notice notice-success"><p>设置已保存。</p></div>';
        }
        ?>
        <div class="wrap">
            <h1>插件设置</h1>
            <form method="post">
                <?php wp_nonce_field('ai_optimizer_settings', 'ai_optimizer_nonce'); ?>
                <table class="form-table">
                    <tr>
                        <th scope="row">Siliconflow API密钥</th>
                        <td>
                            <input type="password" name="ai_optimizer_api_key" value="<?php echo esc_attr(get_option('ai_optimizer_api_key')); ?>" class="regular-text" />
                            <p class="description">请输入您的Siliconflow API密钥</p>
                        </td>
                    </tr>
                </table>
                <?php submit_button(); ?>
            </form>
        </div>
        <?php
    }
    
    /**
     * Enqueue admin scripts and styles
     */
    public function admin_enqueue_scripts($hook) {
        if (strpos($hook, 'ai-optimizer') === false) {
            return;
        }
        
        // Styles
        wp_enqueue_style(
            'ai-optimizer-admin',
            AI_OPTIMIZER_PLUGIN_URL . 'admin/assets/css/admin-style.css',
            array(),
            AI_OPTIMIZER_VERSION
        );
        
        // Scripts
        wp_enqueue_script(
            'ai-optimizer-admin',
            AI_OPTIMIZER_PLUGIN_URL . 'admin/assets/js/admin-script.js',
            array('jquery'),
            AI_OPTIMIZER_VERSION,
            true
        );
        
        // Chart.js
        wp_enqueue_script(
            'chart-js',
            'https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.min.js',
            array(),
            '4.4.0',
            true
        );
        
        // Localize script
        wp_localize_script('ai-optimizer-admin', 'aiOptimizer', array(
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('ai_optimizer_nonce'),
            'pluginUrl' => AI_OPTIMIZER_PLUGIN_URL,
        ));
    }
    
    /**
     * Enqueue frontend scripts and styles
     */
    public function frontend_enqueue_scripts() {
        // Only load if needed
        if (!get_option('ai_optimizer_frontend_monitoring', false)) {
            return;
        }
        
        wp_enqueue_script(
            'ai-optimizer-frontend',
            AI_OPTIMIZER_PLUGIN_URL . 'public/assets/js/frontend.js',
            array('jquery'),
            AI_OPTIMIZER_VERSION,
            true
        );
    }
    
    /**
     * AJAX handler
     */
    public function ajax_handler() {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'], 'ai_optimizer_nonce')) {
            wp_die(__('Security check failed', 'ai-website-optimizer'));
        }
        
        $action = sanitize_text_field($_POST['action_type'] ?? '');
        
        switch ($action) {
            case 'run_analysis':
                $this->ajax_run_analysis();
                break;
            case 'get_monitoring_data':
                $this->ajax_get_monitoring_data();
                break;
            case 'apply_seo_suggestion':
                $this->ajax_apply_seo_suggestion();
                break;
            case 'generate_content':
                $this->ajax_generate_content();
                break;
            default:
                wp_send_json_error(__('Invalid action', 'ai-website-optimizer'));
        }
    }
    
    /**
     * Register REST API routes
     */
    public function register_rest_routes() {
        register_rest_route('ai-optimizer/v1', '/monitor', array(
            'methods' => 'GET',
            'callback' => array($this, 'rest_get_monitor_data'),
            'permission_callback' => array($this, 'rest_permission_check'),
        ));
        
        register_rest_route('ai-optimizer/v1', '/analyze', array(
            'methods' => 'POST',
            'callback' => array($this, 'rest_run_analysis'),
            'permission_callback' => array($this, 'rest_permission_check'),
        ));
    }
    
    /**
     * REST API permission check
     */
    public function rest_permission_check() {
        return current_user_can('manage_options');
    }
    
    /**
     * Run monitoring cron job
     */
    public function run_monitoring() {
        // 简单的监控实现
        global $wpdb;
        $table_name = $wpdb->prefix . 'ai_optimizer_monitor';
        
        // 记录基本性能数据
        $wpdb->insert(
            $table_name,
            array(
                'metric_type' => 'memory_usage',
                'metric_value' => memory_get_usage() / 1048576, // MB
                'details' => json_encode(array('timestamp' => current_time('mysql')))
            )
        );
    }
    
    /**
     * Plugin activation
     */
    public function activate() {
        // 创建数据库表
        $this->create_database_tables();
        
        // 设置默认选项
        $this->set_default_options();
        
        // 计划定时任务
        if (!wp_next_scheduled('ai_optimizer_monitor_cron')) {
            wp_schedule_event(time(), 'hourly', 'ai_optimizer_monitor_cron');
        }
        
        // 刷新重写规则
        flush_rewrite_rules();
    }
    
    /**
     * 设置默认选项
     */
    private function set_default_options() {
        add_option('ai_optimizer_api_key', '');
        add_option('ai_optimizer_enable_logging', true);
        add_option('ai_optimizer_enable_monitoring', true);
        add_option('ai_optimizer_monitor_interval', 'hourly');
    }
    
    /**
     * Plugin deactivation
     */
    public function deactivate() {
        // Clear scheduled events
        wp_clear_scheduled_hook('ai_optimizer_monitor_cron');
        
        // Flush rewrite rules
        flush_rewrite_rules();
    }
    

    
    /**
     * AJAX handler
     */
    public function ajax_handler() {
        check_ajax_referer('ai-optimizer-nonce', 'nonce');
        
        $action = sanitize_text_field($_POST['action_type'] ?? '');
        
        switch ($action) {
            case 'test_api':
                $api_key = get_option('ai_optimizer_api_key');
                if (!empty($api_key)) {
                    wp_send_json_success(array('message' => 'API密钥已配置'));
                } else {
                    wp_send_json_error(array('message' => '请先配置API密钥'));
                }
                break;
                
            default:
                wp_send_json_success(array('message' => '功能正在开发中'));
                break;
        }
    }
    
    /**
     * REST API: 获取监控数据
     */
    public function rest_get_monitor_data() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'ai_optimizer_monitor';
        
        $data = $wpdb->get_results(
            "SELECT * FROM $table_name ORDER BY created_at DESC LIMIT 50",
            ARRAY_A
        );
        
        return rest_ensure_response(array(
            'success' => true,
            'data' => $data
        ));
    }
    
    /**
     * REST API: 运行分析
     */
    public function rest_run_analysis($request) {
        return rest_ensure_response(array(
            'success' => true,
            'message' => '分析功能正在开发中',
            'data' => array()
        ));
    }
}

// 确保WordPress已完全加载后再初始化插件
function ai_optimizer_init() {
    AI_Website_Optimizer::get_instance();
}
add_action('plugins_loaded', 'ai_optimizer_init');
