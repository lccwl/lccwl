<?php
/**
 * 设置页面类
 */

if (!defined('ABSPATH')) {
    exit;
}

class AI_Optimizer_Settings {
    
    private static $instance = null;
    
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * 渲染设置页面
     */
    public static function render() {
        AI_Optimizer_Admin::verify_admin_access();
        
        // 处理表单提交
        if (isset($_POST['submit']) && wp_verify_nonce($_POST['ai_optimizer_nonce'], 'ai_optimizer_nonce')) {
            self::save_settings();
            echo '<div class="notice notice-success"><p>设置已保存。</p></div>';
        }
        ?>
        <div class="wrap ai-optimizer-settings">
            <h1>插件设置</h1>
            
            <form method="post" action="">
                <?php AI_Optimizer_Admin::nonce_field(); ?>
                
                <table class="form-table">
                    <tr>
                        <th scope="row">Siliconflow API密钥</th>
                        <td>
                            <input type="password" name="ai_optimizer_api_key" value="<?php echo esc_attr(get_option('ai_optimizer_api_key')); ?>" class="regular-text" />
                            <p class="description">请输入您的Siliconflow API密钥</p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">启用性能监控</th>
                        <td>
                            <input type="checkbox" name="ai_optimizer_monitoring_enabled" value="1" <?php checked(get_option('ai_optimizer_monitoring_enabled'), 1); ?> />
                            <p class="description">启用后将自动监控网站性能</p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">自动SEO优化</th>
                        <td>
                            <input type="checkbox" name="ai_optimizer_seo_auto_optimize" value="1" <?php checked(get_option('ai_optimizer_seo_auto_optimize'), 1); ?> />
                            <p class="description">启用后将自动应用SEO优化建议</p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">前端监控</th>
                        <td>
                            <input type="checkbox" name="ai_optimizer_frontend_monitoring" value="1" <?php checked(get_option('ai_optimizer_frontend_monitoring'), 1); ?> />
                            <p class="description">启用前端性能和错误监控</p>
                        </td>
                    </tr>
                </table>
                
                <?php submit_button(); ?>
            </form>
        </div>
        <?php
    }
    
    /**
     * 保存设置
     */
    private static function save_settings() {
        $settings = array(
            'ai_optimizer_api_key',
            'ai_optimizer_monitoring_enabled',
            'ai_optimizer_seo_auto_optimize',
            'ai_optimizer_frontend_monitoring'
        );
        
        foreach ($settings as $setting) {
            $value = isset($_POST[$setting]) ? sanitize_text_field($_POST[$setting]) : '';
            update_option($setting, $value);
        }
    }
    
    /**
     * 获取设置值
     */
    public static function get($option, $default = false) {
        return get_option('ai_optimizer_' . $option, $default);
    }
}