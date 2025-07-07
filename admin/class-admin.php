<?php
/**
 * Admin class
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
        add_action('admin_notices', array($this, 'admin_notices'));
        add_action('admin_init', array($this, 'handle_bulk_actions'));
    }
    
    /**
     * Display admin notices
     */
    public function admin_notices() {
        // Check if API key is configured
        if (!get_option('ai_optimizer_api_key')) {
            echo '<div class="notice notice-warning is-dismissible">';
            echo '<p>' . sprintf(
                __('AI Website Optimizer requires an API key to function. Please <a href="%s">configure it in settings</a>.', 'ai-website-optimizer'),
                admin_url('admin.php?page=ai-optimizer-settings')
            ) . '</p>';
            echo '</div>';
        }
        
        // Show success/error messages
        if (isset($_GET['ai_optimizer_message'])) {
            $message = sanitize_text_field($_GET['ai_optimizer_message']);
            $type = sanitize_text_field($_GET['ai_optimizer_type'] ?? 'success');
            
            echo '<div class="notice notice-' . esc_attr($type) . ' is-dismissible">';
            echo '<p>' . esc_html($this->get_message($message)) . '</p>';
            echo '</div>';
        }
    }
    
    /**
     * Handle bulk actions
     */
    public function handle_bulk_actions() {
        if (!isset($_POST['ai_optimizer_bulk_action']) || !wp_verify_nonce($_POST['_wpnonce'], 'ai_optimizer_bulk_nonce')) {
            return;
        }
        
        $action = sanitize_text_field($_POST['ai_optimizer_bulk_action']);
        $items = array_map('intval', $_POST['bulk_items'] ?? array());
        
        if (empty($items)) {
            return;
        }
        
        switch ($action) {
            case 'apply_seo_suggestions':
                $this->bulk_apply_seo_suggestions($items);
                break;
            case 'fix_code_issues':
                $this->bulk_fix_code_issues($items);
                break;
            case 'delete_logs':
                $this->bulk_delete_logs($items);
                break;
        }
    }
    
    /**
     * Get admin message
     */
    private function get_message($key) {
        $messages = array(
            'settings_saved' => __('Settings saved successfully.', 'ai-website-optimizer'),
            'analysis_completed' => __('Analysis completed successfully.', 'ai-website-optimizer'),
            'seo_applied' => __('SEO optimization applied successfully.', 'ai-website-optimizer'),
            'code_fixed' => __('Code issue fixed successfully.', 'ai-website-optimizer'),
            'content_generated' => __('Content generated successfully.', 'ai-website-optimizer'),
            'api_error' => __('API request failed. Please check your settings.', 'ai-website-optimizer'),
            'permission_denied' => __('You do not have permission to perform this action.', 'ai-website-optimizer'),
        );
        
        return $messages[$key] ?? __('Unknown message.', 'ai-website-optimizer');
    }
    
    /**
     * Bulk actions
     */
    private function bulk_apply_seo_suggestions($items) {
        $seo = new AI_Optimizer_SEO();
        $applied = 0;
        
        foreach ($items as $item_id) {
            if ($seo->apply_suggestion($item_id)) {
                $applied++;
            }
        }
        
        $redirect_url = add_query_arg(array(
            'ai_optimizer_message' => 'seo_applied',
            'ai_optimizer_type' => 'success',
            'applied_count' => $applied,
        ), wp_get_referer());
        
        wp_redirect($redirect_url);
        exit;
    }
    
    private function bulk_fix_code_issues($items) {
        $analyzer = new AI_Optimizer_Code_Analyzer();
        $fixed = 0;
        
        foreach ($items as $item_id) {
            if ($analyzer->fix_issue($item_id)) {
                $fixed++;
            }
        }
        
        $redirect_url = add_query_arg(array(
            'ai_optimizer_message' => 'code_fixed',
            'ai_optimizer_type' => 'success',
            'fixed_count' => $fixed,
        ), wp_get_referer());
        
        wp_redirect($redirect_url);
        exit;
    }
    
    private function bulk_delete_logs($items) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'ai_optimizer_logs';
        $placeholders = implode(',', array_fill(0, count($items), '%d'));
        
        $deleted = $wpdb->query(
            $wpdb->prepare(
                "DELETE FROM {$table_name} WHERE id IN ({$placeholders})",
                ...$items
            )
        );
        
        $redirect_url = add_query_arg(array(
            'ai_optimizer_message' => 'logs_deleted',
            'ai_optimizer_type' => 'success',
            'deleted_count' => $deleted,
        ), wp_get_referer());
        
        wp_redirect($redirect_url);
        exit;
    }
    
    /**
     * Render admin header
     */
    public static function render_header($title, $description = '') {
        ?>
        <div class="ai-optimizer-header">
            <div class="ai-optimizer-header-content">
                <h1 class="ai-optimizer-title">
                    <span class="ai-optimizer-icon"></span>
                    <?php echo esc_html($title); ?>
                </h1>
                <?php if ($description): ?>
                    <p class="ai-optimizer-description"><?php echo esc_html($description); ?></p>
                <?php endif; ?>
            </div>
        </div>
        <?php
    }
    
    /**
     * Render admin footer
     */
    public static function render_footer() {
        ?>
        <div class="ai-optimizer-footer">
            <p>&copy; <?php echo date('Y'); ?> AI Website Optimizer. <?php _e('Powered by advanced AI technology.', 'ai-website-optimizer'); ?></p>
        </div>
        <?php
    }
}
