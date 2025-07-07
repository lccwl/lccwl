<?php
/**
 * Settings class
 */

if (!defined('ABSPATH')) {
    exit;
}

class AI_Optimizer_Settings {
    
    /**
     * Render settings page
     */
    public static function render() {
        // Handle form submission
        if (isset($_POST['submit']) && wp_verify_nonce($_POST['_wpnonce'], 'ai_optimizer_settings_nonce')) {
            self::save_settings();
            
            $redirect_url = add_query_arg(array(
                'ai_optimizer_message' => 'settings_saved',
                'ai_optimizer_type' => 'success',
            ), wp_get_referer());
            
            wp_redirect($redirect_url);
            exit;
        }
        
        include AI_OPTIMIZER_PLUGIN_PATH . 'admin/views/settings.php';
    }
    
    /**
     * Save settings
     */
    private static function save_settings() {
        // API Settings
        $api_key = sanitize_text_field($_POST['ai_optimizer_api_key'] ?? '');
        if ($api_key) {
            update_option('ai_optimizer_api_key', AI_Optimizer_Security::encrypt($api_key));
        }
        
        // Monitoring Settings
        update_option('ai_optimizer_monitoring_enabled', isset($_POST['ai_optimizer_monitoring_enabled']));
        update_option('ai_optimizer_monitoring_interval', sanitize_text_field($_POST['ai_optimizer_monitoring_interval'] ?? 'hourly'));
        update_option('ai_optimizer_frontend_monitoring', isset($_POST['ai_optimizer_frontend_monitoring']));
        
        // SEO Settings
        update_option('ai_optimizer_seo_auto_optimize', isset($_POST['ai_optimizer_seo_auto_optimize']));
        update_option('ai_optimizer_seo_backup_before_changes', isset($_POST['ai_optimizer_seo_backup_before_changes']));
        update_option('ai_optimizer_seo_target_keywords', sanitize_textarea_field($_POST['ai_optimizer_seo_target_keywords'] ?? ''));
        
        // Code Analysis Settings
        update_option('ai_optimizer_code_auto_fix', isset($_POST['ai_optimizer_code_auto_fix']));
        update_option('ai_optimizer_code_scan_plugins', isset($_POST['ai_optimizer_code_scan_plugins']));
        update_option('ai_optimizer_code_scan_themes', isset($_POST['ai_optimizer_code_scan_themes']));
        
        // Content Settings
        update_option('ai_optimizer_content_auto_publish', isset($_POST['ai_optimizer_content_auto_publish']));
        update_option('ai_optimizer_content_categories', array_map('intval', $_POST['ai_optimizer_content_categories'] ?? array()));
        update_option('ai_optimizer_content_sources', sanitize_textarea_field($_POST['ai_optimizer_content_sources'] ?? ''));
        
        // Multimedia Settings
        update_option('ai_optimizer_video_quality', sanitize_text_field($_POST['ai_optimizer_video_quality'] ?? 'standard'));
        update_option('ai_optimizer_image_size', sanitize_text_field($_POST['ai_optimizer_image_size'] ?? '1024x1024'));
        update_option('ai_optimizer_audio_voice', sanitize_text_field($_POST['ai_optimizer_audio_voice'] ?? 'default'));
        
        // Security Settings
        update_option('ai_optimizer_require_approval', isset($_POST['ai_optimizer_require_approval']));
        update_option('ai_optimizer_backup_retention', intval($_POST['ai_optimizer_backup_retention'] ?? 30));
        update_option('ai_optimizer_log_level', sanitize_text_field($_POST['ai_optimizer_log_level'] ?? 'info'));
        
        // Performance Settings
        update_option('ai_optimizer_cache_duration', intval($_POST['ai_optimizer_cache_duration'] ?? 3600));
        update_option('ai_optimizer_rate_limit', intval($_POST['ai_optimizer_rate_limit'] ?? 60));
        update_option('ai_optimizer_batch_size', intval($_POST['ai_optimizer_batch_size'] ?? 10));
    }
    
    /**
     * Get setting value
     */
    public static function get($option_name, $default = '') {
        return get_option('ai_optimizer_' . $option_name, $default);
    }
    
    /**
     * Get API key (decrypted)
     */
    public static function get_api_key() {
        $encrypted_key = get_option('ai_optimizer_api_key', '');
        
        if ($encrypted_key) {
            return AI_Optimizer_Security::decrypt($encrypted_key);
        }
        
        return '';
    }
    
    /**
     * Test API connection
     */
    public static function test_api_connection() {
        $api_key = self::get_api_key();
        
        if (empty($api_key)) {
            return array(
                'success' => false,
                'message' => 'API key not configured'
            );
        }
        
        $api_handler = new AI_Optimizer_API_Handler();
        $user_info = $api_handler->get_user_info();
        
        if ($user_info) {
            return array(
                'success' => true,
                'message' => 'API connection successful',
                'user_info' => $user_info
            );
        }
        
        return array(
            'success' => false,
            'message' => 'Failed to connect to API'
        );
    }
    
    /**
     * Get available models
     */
    public static function get_available_models() {
        $api_handler = new AI_Optimizer_API_Handler();
        return $api_handler->get_models();
    }
    
    /**
     * Reset settings to defaults
     */
    public static function reset_to_defaults() {
        $options = array(
            'ai_optimizer_monitoring_enabled' => true,
            'ai_optimizer_monitoring_interval' => 'hourly',
            'ai_optimizer_frontend_monitoring' => false,
            'ai_optimizer_seo_auto_optimize' => false,
            'ai_optimizer_seo_backup_before_changes' => true,
            'ai_optimizer_seo_target_keywords' => '',
            'ai_optimizer_code_auto_fix' => false,
            'ai_optimizer_code_scan_plugins' => true,
            'ai_optimizer_code_scan_themes' => true,
            'ai_optimizer_content_auto_publish' => false,
            'ai_optimizer_content_categories' => array(),
            'ai_optimizer_content_sources' => '',
            'ai_optimizer_video_quality' => 'standard',
            'ai_optimizer_image_size' => '1024x1024',
            'ai_optimizer_audio_voice' => 'default',
            'ai_optimizer_require_approval' => true,
            'ai_optimizer_backup_retention' => 30,
            'ai_optimizer_log_level' => 'info',
            'ai_optimizer_cache_duration' => 3600,
            'ai_optimizer_rate_limit' => 60,
            'ai_optimizer_batch_size' => 10,
        );
        
        foreach ($options as $option => $value) {
            update_option($option, $value);
        }
        
        return true;
    }
    
    /**
     * Export settings
     */
    public static function export_settings() {
        $settings = array();
        
        $option_names = array(
            'ai_optimizer_monitoring_enabled',
            'ai_optimizer_monitoring_interval',
            'ai_optimizer_frontend_monitoring',
            'ai_optimizer_seo_auto_optimize',
            'ai_optimizer_seo_backup_before_changes',
            'ai_optimizer_seo_target_keywords',
            'ai_optimizer_code_auto_fix',
            'ai_optimizer_code_scan_plugins',
            'ai_optimizer_code_scan_themes',
            'ai_optimizer_content_auto_publish',
            'ai_optimizer_content_categories',
            'ai_optimizer_content_sources',
            'ai_optimizer_video_quality',
            'ai_optimizer_image_size',
            'ai_optimizer_audio_voice',
            'ai_optimizer_require_approval',
            'ai_optimizer_backup_retention',
            'ai_optimizer_log_level',
            'ai_optimizer_cache_duration',
            'ai_optimizer_rate_limit',
            'ai_optimizer_batch_size',
        );
        
        foreach ($option_names as $option) {
            $settings[$option] = get_option($option);
        }
        
        return $settings;
    }
    
    /**
     * Import settings
     */
    public static function import_settings($settings) {
        if (!is_array($settings)) {
            return false;
        }
        
        foreach ($settings as $option => $value) {
            if (strpos($option, 'ai_optimizer_') === 0) {
                update_option($option, $value);
            }
        }
        
        return true;
    }
    
    /**
     * Get system status
     */
    public static function get_system_status() {
        return array(
            'php_version' => PHP_VERSION,
            'wp_version' => get_bloginfo('version'),
            'plugin_version' => AI_OPTIMIZER_VERSION,
            'memory_limit' => ini_get('memory_limit'),
            'max_execution_time' => ini_get('max_execution_time'),
            'upload_max_filesize' => ini_get('upload_max_filesize'),
            'api_connection' => self::test_api_connection(),
            'database_tables' => AI_Optimizer_Database::check_tables(),
            'cron_status' => wp_next_scheduled('ai_optimizer_monitor_cron') ? 'active' : 'inactive',
        );
    }
}
