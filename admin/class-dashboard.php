<?php
/**
 * Dashboard class
 */

if (!defined('ABSPATH')) {
    exit;
}

class AI_Optimizer_Dashboard {
    
    /**
     * Render dashboard page
     */
    public static function render() {
        $monitor = new AI_Optimizer_Monitor();
        $stats = $monitor->get_dashboard_stats();
        
        include AI_OPTIMIZER_PLUGIN_PATH . 'admin/views/dashboard.php';
    }
    
    /**
     * Get dashboard data for AJAX
     */
    public static function get_dashboard_data() {
        $monitor = new AI_Optimizer_Monitor();
        $seo = new AI_Optimizer_SEO();
        $analyzer = new AI_Optimizer_Code_Analyzer();
        
        return array(
            'performance' => $monitor->get_performance_data(),
            'seo_score' => $seo->get_current_score(),
            'code_health' => $analyzer->get_health_score(),
            'recent_activities' => $monitor->get_recent_activities(),
            'alerts' => $monitor->get_active_alerts(),
        );
    }
}
