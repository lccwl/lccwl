<?php
/**
 * Plugin Name: AI智能网站优化器 (测试版)
 * Plugin URI: https://example.com/ai-website-optimizer
 * Description: 基于Siliconflow API的WordPress智能SEO优化插件，提供AI驱动的网站分析、性能监控和自动优化功能。
 * Version: 2.1.0-test
 * Author: AI开发团队
 * License: GPL v2 or later
 * Requires at least: 5.0
 * Tested up to: 6.4
 * Requires PHP: 7.4
 * Text Domain: ai-website-optimizer
 */

// 防止直接访问
if (!defined('ABSPATH')) {
    exit;
}

// 定义插件常量
define('AI_OPT_VERSION', '2.1.0-test');
define('AI_OPT_PLUGIN_URL', plugin_dir_url(__FILE__));
define('AI_OPT_PLUGIN_PATH', plugin_dir_path(__FILE__));

/**
 * 简化的AI网站优化器主类 - 用于测试激活
 */
class AI_Website_Optimizer_Test {
    
    private static $instance = null;
    
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        add_action('init', array($this, 'init'));
        add_action('admin_menu', array($this, 'add_admin_menu'));
        
        // 注册激活和停用钩子
        register_activation_hook(__FILE__, array($this, 'activate'));
        register_deactivation_hook(__FILE__, array($this, 'deactivate'));
    }
    
    public function init() {
        // 检查WordPress版本
        if (version_compare(get_bloginfo('version'), '5.0', '<')) {
            add_action('admin_notices', array($this, 'version_notice'));
            return;
        }
        
        // 加载文本域
        load_plugin_textdomain('ai-website-optimizer', false, dirname(plugin_basename(__FILE__)) . '/languages');
    }
    
    public function version_notice() {
        echo '<div class="notice notice-error"><p><strong>AI智能网站优化器：</strong>此插件需要WordPress 5.0或更高版本。</p></div>';
    }
    
    public function activate() {
        // 创建默认选项
        add_option('ai_opt_api_key', '');
        add_option('ai_seo_competitor_urls', array());
        
        // 记录激活时间
        update_option('ai_opt_activated_at', current_time('mysql'));
    }
    
    public function deactivate() {
        // 清理定时任务
        wp_clear_scheduled_hook('ai_optimizer_daily_patrol');
    }
    
    public function add_admin_menu() {
        add_menu_page(
            'AI智能优化器',
            'AI优化器',
            'manage_options',
            'ai-optimizer',
            array($this, 'admin_page'),
            'dashicons-chart-line',
            30
        );
        
        add_submenu_page(
            'ai-optimizer',
            'SEO分析',
            'SEO分析',
            'manage_options',
            'ai-optimizer-seo',
            array($this, 'seo_page')
        );
        
        add_submenu_page(
            'ai-optimizer',
            '设置',
            '设置',
            'manage_options',
            'ai-optimizer-settings',
            array($this, 'settings_page')
        );
    }
    
    public function admin_page() {
        ?>
        <div class="wrap">
            <h1>🚀 AI智能网站优化器</h1>
            <div class="notice notice-success">
                <p><strong>插件已成功激活！</strong>这是简化的测试版本。</p>
            </div>
            
            <div class="card">
                <h2>功能状态</h2>
                <p>✅ 插件核心已加载</p>
                <p>✅ 管理菜单已注册</p>
                <p>✅ WordPress兼容性检查通过</p>
                
                <h3>下一步操作</h3>
                <ol>
                    <li>前往设置页面配置API密钥</li>
                    <li>使用SEO分析功能测试插件</li>
                    <li>如测试正常，可激活完整版本</li>
                </ol>
            </div>
        </div>
        <?php
    }
    
    public function seo_page() {
        $api_key = get_option('ai_opt_api_key', '');
        ?>
        <div class="wrap">
            <h1>SEO分析</h1>
            
            <?php if (empty($api_key)): ?>
                <div class="notice notice-warning">
                    <p><strong>注意：</strong>请先在设置页面配置Siliconflow API密钥。</p>
                </div>
            <?php else: ?>
                <div class="notice notice-info">
                    <p>API密钥已配置，SEO分析功能可用。</p>
                </div>
            <?php endif; ?>
            
            <div class="card">
                <h2>SEO分析测试</h2>
                <p>当前网站URL: <?php echo home_url(); ?></p>
                <p>WordPress版本: <?php echo get_bloginfo('version'); ?></p>
                <p>PHP版本: <?php echo PHP_VERSION; ?></p>
                <p>活跃主题: <?php echo wp_get_theme()->get('Name'); ?></p>
                <p>活跃插件数量: <?php echo count(get_option('active_plugins', array())); ?></p>
            </div>
        </div>
        <?php
    }
    
    public function settings_page() {
        if (isset($_POST['submit'])) {
            check_admin_referer('ai_opt_settings');
            
            $api_key = sanitize_text_field($_POST['api_key']);
            update_option('ai_opt_api_key', $api_key);
            
            echo '<div class="notice notice-success"><p>设置已保存！</p></div>';
        }
        
        $api_key = get_option('ai_opt_api_key', '');
        ?>
        <div class="wrap">
            <h1>AI优化器设置</h1>
            
            <form method="post" action="">
                <?php wp_nonce_field('ai_opt_settings'); ?>
                
                <table class="form-table">
                    <tr>
                        <th scope="row">
                            <label for="api_key">Siliconflow API密钥</label>
                        </th>
                        <td>
                            <input type="password" id="api_key" name="api_key" value="<?php echo esc_attr($api_key); ?>" class="regular-text" />
                            <p class="description">
                                请输入您的Siliconflow API密钥。
                                <a href="https://siliconflow.cn" target="_blank">获取API密钥</a>
                            </p>
                        </td>
                    </tr>
                </table>
                
                <?php submit_button(); ?>
            </form>
            
            <?php if (!empty($api_key)): ?>
                <div class="card">
                    <h2>API测试</h2>
                    <p>✅ API密钥已配置</p>
                    <p>密钥前缀: <?php echo substr($api_key, 0, 8) . '...'; ?></p>
                </div>
            <?php endif; ?>
        </div>
        <?php
    }
}

// 插件激活钩子
register_activation_hook(__FILE__, 'ai_optimizer_test_activate');

function ai_optimizer_test_activate() {
    // 检查WordPress版本
    if (version_compare(get_bloginfo('version'), '5.0', '<')) {
        deactivate_plugins(basename(__FILE__));
        wp_die('此插件需要WordPress 5.0或更高版本。');
    }
    
    // 检查PHP版本
    if (version_compare(PHP_VERSION, '7.4.0', '<')) {
        deactivate_plugins(basename(__FILE__));
        wp_die('此插件需要PHP 7.4.0或更高版本。');
    }
}

// 启动插件
add_action('plugins_loaded', function() {
    AI_Website_Optimizer_Test::get_instance();
});