<?php
/**
 * 监控页面类
 */

if (!defined('ABSPATH')) {
    exit;
}

class AI_Optimizer_Monitor {
    
    /**
     * 渲染监控页面
     */
    public static function render() {
        AI_Optimizer_Admin::verify_admin_access();
        ?>
        <div class="wrap ai-optimizer-monitor">
            <h1>性能监控</h1>
            <p>实时监控网站性能，包括页面加载时间、内存使用和前端错误。</p>
            
            <div class="ai-optimizer-card">
                <h3>监控数据</h3>
                <div id="monitoringChart">
                    <canvas id="performanceChart" width="400" height="200"></canvas>
                </div>
            </div>
        </div>
        <?php
    }
    
    /**
     * 收集监控数据
     */
    public function collect_data() {
        global $wpdb;
        
        $urls_to_monitor = array(
            home_url(),
            home_url('/about/'),
            home_url('/contact/')
        );
        
        foreach ($urls_to_monitor as $url) {
            $this->monitor_url($url);
        }
        
        return array('success' => true, 'monitored_urls' => count($urls_to_monitor));
    }
    
    /**
     * 监控单个URL
     */
    private function monitor_url($url) {
        $start_time = microtime(true);
        $memory_start = memory_get_usage();
        
        $response = wp_remote_get($url, array(
            'timeout' => 10,
            'user-agent' => 'AI-Website-Optimizer/' . AI_OPTIMIZER_VERSION
        ));
        
        $end_time = microtime(true);
        $memory_end = memory_get_usage();
        
        $load_time = $end_time - $start_time;
        $memory_usage = $memory_end - $memory_start;
        $status_code = wp_remote_retrieve_response_code($response);
        
        global $wpdb;
        $table_name = $wpdb->prefix . 'ai_optimizer_monitoring';
        
        $wpdb->insert($table_name, array(
            'url' => $url,
            'load_time' => $load_time,
            'memory_usage' => $memory_usage,
            'status_code' => $status_code,
            'created_at' => current_time('mysql')
        ));
    }
    
    /**
     * 获取最近监控数据
     */
    public function get_recent_data($limit = 50) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'ai_optimizer_monitoring';
        
        return $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM $table_name ORDER BY created_at DESC LIMIT %d",
            $limit
        ));
    }
}