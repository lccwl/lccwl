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
        $this->init();
    }
    
    /**
     * Initialize plugin
     */
    private function init() {
        // Load text domain
        add_action('plugins_loaded', array($this, 'load_textdomain'));
        
        // Activation and deactivation hooks
        register_activation_hook(__FILE__, array($this, 'activate'));
        register_deactivation_hook(__FILE__, array($this, 'deactivate'));
        
        // Include required files
        $this->includes();
        
        // Initialize components
        add_action('init', array($this, 'init_components'));
        
        // Admin hooks
        if (is_admin()) {
            add_action('admin_init', array($this, 'admin_init'));
            add_action('admin_menu', array($this, 'admin_menu'));
            add_action('admin_enqueue_scripts', array($this, 'admin_enqueue_scripts'));
        }
        
        // Frontend hooks
        add_action('wp_enqueue_scripts', array($this, 'frontend_enqueue_scripts'));
        
        // AJAX hooks
        add_action('wp_ajax_ai_optimizer_action', array($this, 'ajax_handler'));
        add_action('wp_ajax_nopriv_ai_optimizer_action', array($this, 'ajax_handler'));
        
        // REST API
        add_action('rest_api_init', array($this, 'register_rest_routes'));
        
        // Cron jobs
        add_action('ai_optimizer_monitor_cron', array($this, 'run_monitoring'));
        
        // Schedule monitoring if not already scheduled
        if (!wp_next_scheduled('ai_optimizer_monitor_cron')) {
            wp_schedule_event(time(), 'hourly', 'ai_optimizer_monitor_cron');
        }
    }
    
    /**
     * Load text domain
     */
    public function load_textdomain() {
        load_plugin_textdomain('ai-website-optimizer', false, dirname(plugin_basename(__FILE__)) . '/languages');
    }
    
    /**
     * Include required files
     */
    private function includes() {
        require_once AI_OPTIMIZER_PLUGIN_PATH . 'includes/class-database.php';
        require_once AI_OPTIMIZER_PLUGIN_PATH . 'includes/class-api-handler.php';
        require_once AI_OPTIMIZER_PLUGIN_PATH . 'includes/class-code-analyzer.php';
        require_once AI_OPTIMIZER_PLUGIN_PATH . 'includes/class-video-generator.php';
        require_once AI_OPTIMIZER_PLUGIN_PATH . 'includes/class-content-collector.php';
        require_once AI_OPTIMIZER_PLUGIN_PATH . 'includes/class-utils.php';
        require_once AI_OPTIMIZER_PLUGIN_PATH . 'includes/class-security.php';
        require_once AI_OPTIMIZER_PLUGIN_PATH . 'config/api-endpoints.php';
        
        if (is_admin()) {
            require_once AI_OPTIMIZER_PLUGIN_PATH . 'admin/class-admin.php';
            require_once AI_OPTIMIZER_PLUGIN_PATH . 'admin/class-dashboard.php';
            require_once AI_OPTIMIZER_PLUGIN_PATH . 'admin/class-monitor.php';
            require_once AI_OPTIMIZER_PLUGIN_PATH . 'admin/class-seo-optimizer.php';
            require_once AI_OPTIMIZER_PLUGIN_PATH . 'admin/class-ai-tools.php';
            require_once AI_OPTIMIZER_PLUGIN_PATH . 'admin/class-settings.php';
        } else {
            require_once AI_OPTIMIZER_PLUGIN_PATH . 'public/class-public.php';
        }
    }
    
    /**
     * Initialize components
     */
    public function init_components() {
        // Initialize database
        AI_Optimizer_Database::get_instance();
        
        // Initialize security
        AI_Optimizer_Security::get_instance();
        
        // Initialize public components
        if (!is_admin()) {
            AI_Optimizer_Public::get_instance();
        }
    }
    
    /**
     * Admin initialization
     */
    public function admin_init() {
        // Initialize admin components
        AI_Optimizer_Admin::get_instance();
    }
    
    /**
     * Add admin menu
     */
    public function admin_menu() {
        // Main menu
        add_menu_page(
            __('AI智能优化器', 'ai-website-optimizer'),
            __('AI智能优化器', 'ai-website-optimizer'),
            'manage_options',
            'ai-optimizer',
            array('AI_Optimizer_Dashboard', 'render'),
            'dashicons-chart-area',
            30
        );
        
        // Submenu pages
        add_submenu_page(
            'ai-optimizer',
            __('仪表盘', 'ai-website-optimizer'),
            __('仪表盘', 'ai-website-optimizer'),
            'manage_options',
            'ai-optimizer',
            array('AI_Optimizer_Dashboard', 'render')
        );
        
        add_submenu_page(
            'ai-optimizer',
            __('性能监控', 'ai-website-optimizer'),
            __('性能监控', 'ai-website-optimizer'),
            'manage_options',
            'ai-optimizer-monitor',
            array('AI_Optimizer_Monitor', 'render')
        );
        
        add_submenu_page(
            'ai-optimizer',
            __('SEO优化', 'ai-website-optimizer'),
            __('SEO优化', 'ai-website-optimizer'),
            'manage_options',
            'ai-optimizer-seo',
            array('AI_Optimizer_SEO', 'render')
        );
        
        add_submenu_page(
            'ai-optimizer',
            __('AI工具', 'ai-website-optimizer'),
            __('AI工具', 'ai-website-optimizer'),
            'manage_options',
            'ai-optimizer-ai-tools',
            array('AI_Optimizer_AI_Tools', 'render')
        );
        
        add_submenu_page(
            'ai-optimizer',
            __('插件设置', 'ai-website-optimizer'),
            __('插件设置', 'ai-website-optimizer'),
            'manage_options',
            'ai-optimizer-settings',
            array('AI_Optimizer_Settings', 'render')
        );
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
        $monitor = new AI_Optimizer_Monitor();
        $monitor->collect_data();
    }
    
    /**
     * Plugin activation
     */
    public function activate() {
        // Create database tables
        AI_Optimizer_Database::create_tables();
        
        // Set default options
        $this->set_default_options();
        
        // Schedule cron
        if (!wp_next_scheduled('ai_optimizer_monitor_cron')) {
            wp_schedule_event(time(), 'hourly', 'ai_optimizer_monitor_cron');
        }
        
        // Flush rewrite rules
        flush_rewrite_rules();
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
     * Set default options
     */
    private function set_default_options() {
        $defaults = array(
            'ai_optimizer_api_key' => '',
            'ai_optimizer_monitoring_enabled' => true,
            'ai_optimizer_seo_auto_optimize' => false,
            'ai_optimizer_code_auto_fix' => false,
            'ai_optimizer_content_auto_publish' => false,
            'ai_optimizer_frontend_monitoring' => false,
        );
        
        foreach ($defaults as $option => $value) {
            if (get_option($option) === false) {
                add_option($option, $value);
            }
        }
    }
    
    /**
     * AJAX methods
     */
    private function ajax_run_analysis() {
        $analyzer = new AI_Optimizer_Code_Analyzer();
        $result = $analyzer->run_full_analysis();
        wp_send_json_success($result);
    }
    
    private function ajax_get_monitoring_data() {
        $monitor = new AI_Optimizer_Monitor();
        $data = $monitor->get_recent_data();
        wp_send_json_success($data);
    }
    
    private function ajax_apply_seo_suggestion() {
        $suggestion_id = intval($_POST['suggestion_id'] ?? 0);
        $seo = new AI_Optimizer_SEO();
        $result = $seo->apply_suggestion($suggestion_id);
        wp_send_json_success($result);
    }
    
    private function ajax_generate_content() {
        $type = sanitize_text_field($_POST['content_type'] ?? '');
        $prompt = sanitize_textarea_field($_POST['prompt'] ?? '');
        
        $generator = new AI_Optimizer_Video_Generator();
        $result = $generator->generate_content($type, $prompt);
        wp_send_json_success($result);
    }
    
    /**
     * REST API methods
     */
    public function rest_get_monitor_data() {
        $monitor = new AI_Optimizer_Monitor();
        return rest_ensure_response($monitor->get_recent_data());
    }
    
    public function rest_run_analysis() {
        $analyzer = new AI_Optimizer_Code_Analyzer();
        $result = $analyzer->run_full_analysis();
        return rest_ensure_response($result);
    }
}

// Initialize plugin
AI_Website_Optimizer::get_instance();
