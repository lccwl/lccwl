<?php
/**
 * Monitor class
 */

if (!defined('ABSPATH')) {
    exit;
}

class AI_Optimizer_Monitor {
    
    private $api_handler;
    
    public function __construct() {
        $this->api_handler = new AI_Optimizer_API_Handler();
    }
    
    /**
     * Render monitor page
     */
    public static function render() {
        $monitor = new self();
        $data = $monitor->get_monitoring_data();
        
        include AI_OPTIMIZER_PLUGIN_PATH . 'admin/views/monitor.php';
    }
    
    /**
     * Collect monitoring data
     */
    public function collect_data() {
        $data = array(
            'timestamp' => current_time('mysql'),
            'performance' => $this->collect_performance_data(),
            'seo' => $this->collect_seo_data(),
            'security' => $this->collect_security_data(),
            'errors' => $this->collect_error_data(),
        );
        
        $this->store_monitoring_data($data);
        $this->analyze_with_ai($data);
        
        return $data;
    }
    
    /**
     * Collect performance data
     */
    private function collect_performance_data() {
        $start_time = microtime(true);
        
        // Test homepage load time
        $response = wp_remote_get(home_url());
        $load_time = microtime(true) - $start_time;
        
        // Memory usage
        $memory_usage = memory_get_usage(true);
        $memory_peak = memory_get_peak_usage(true);
        
        // Database queries (if Query Monitor is available)
        $db_queries = 0;
        if (defined('SAVEQUERIES') && SAVEQUERIES) {
            global $wpdb;
            $db_queries = count($wpdb->queries);
        }
        
        return array(
            'load_time' => $load_time,
            'memory_usage' => $memory_usage,
            'memory_peak' => $memory_peak,
            'db_queries' => $db_queries,
            'response_code' => wp_remote_retrieve_response_code($response),
        );
    }
    
    /**
     * Collect SEO data
     */
    private function collect_seo_data() {
        $homepage = wp_remote_get(home_url());
        $content = wp_remote_retrieve_body($homepage);
        
        // Parse HTML
        $dom = new DOMDocument();
        @$dom->loadHTML($content);
        
        $title = '';
        $meta_description = '';
        $h1_count = 0;
        $images_without_alt = 0;
        
        // Get title
        $title_tags = $dom->getElementsByTagName('title');
        if ($title_tags->length > 0) {
            $title = $title_tags->item(0)->textContent;
        }
        
        // Get meta description
        $meta_tags = $dom->getElementsByTagName('meta');
        foreach ($meta_tags as $meta) {
            if ($meta->getAttribute('name') === 'description') {
                $meta_description = $meta->getAttribute('content');
                break;
            }
        }
        
        // Count H1 tags
        $h1_count = $dom->getElementsByTagName('h1')->length;
        
        // Check images without alt text
        $images = $dom->getElementsByTagName('img');
        foreach ($images as $img) {
            if (!$img->hasAttribute('alt') || empty($img->getAttribute('alt'))) {
                $images_without_alt++;
            }
        }
        
        return array(
            'title_length' => strlen($title),
            'meta_description_length' => strlen($meta_description),
            'h1_count' => $h1_count,
            'images_without_alt' => $images_without_alt,
            'has_sitemap' => $this->check_sitemap(),
            'robots_txt_exists' => $this->check_robots_txt(),
        );
    }
    
    /**
     * Collect security data
     */
    private function collect_security_data() {
        return array(
            'wp_version' => get_bloginfo('version'),
            'php_version' => PHP_VERSION,
            'ssl_enabled' => is_ssl(),
            'admin_user_exists' => $this->check_admin_user(),
            'file_permissions' => $this->check_file_permissions(),
        );
    }
    
    /**
     * Collect error data
     */
    private function collect_error_data() {
        $errors = array();
        
        // Check for PHP errors in log
        $error_log = ini_get('error_log');
        if ($error_log && file_exists($error_log)) {
            $recent_errors = $this->parse_error_log($error_log);
            $errors['php_errors'] = count($recent_errors);
        }
        
        // Check for WordPress debug log
        if (defined('WP_DEBUG_LOG') && WP_DEBUG_LOG) {
            $debug_log = WP_CONTENT_DIR . '/debug.log';
            if (file_exists($debug_log)) {
                $recent_errors = $this->parse_error_log($debug_log);
                $errors['wp_errors'] = count($recent_errors);
            }
        }
        
        return $errors;
    }
    
    /**
     * Store monitoring data
     */
    private function store_monitoring_data($data) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'ai_optimizer_monitoring';
        
        $wpdb->insert(
            $table_name,
            array(
                'timestamp' => $data['timestamp'],
                'data' => json_encode($data),
                'created_at' => current_time('mysql'),
            ),
            array('%s', '%s', '%s')
        );
    }
    
    /**
     * Analyze data with AI
     */
    private function analyze_with_ai($data) {
        $prompt = "Analyze this website monitoring data and provide optimization suggestions:\n\n" . json_encode($data, JSON_PRETTY_PRINT);
        
        $analysis = $this->api_handler->chat_completion($prompt, 'Qwen/QwQ-32B');
        
        if ($analysis) {
            $this->store_ai_analysis($analysis, $data);
        }
    }
    
    /**
     * Store AI analysis
     */
    private function store_ai_analysis($analysis, $original_data) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'ai_optimizer_analysis';
        
        $wpdb->insert(
            $table_name,
            array(
                'type' => 'monitoring',
                'analysis' => $analysis,
                'original_data' => json_encode($original_data),
                'created_at' => current_time('mysql'),
            ),
            array('%s', '%s', '%s', '%s')
        );
    }
    
    /**
     * Get monitoring data for display
     */
    public function get_monitoring_data() {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'ai_optimizer_monitoring';
        
        return $wpdb->get_results(
            "SELECT * FROM {$table_name} ORDER BY created_at DESC LIMIT 100",
            ARRAY_A
        );
    }
    
    /**
     * Get recent data
     */
    public function get_recent_data() {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'ai_optimizer_monitoring';
        
        $recent = $wpdb->get_row(
            "SELECT * FROM {$table_name} ORDER BY created_at DESC LIMIT 1",
            ARRAY_A
        );
        
        if ($recent) {
            $recent['data'] = json_decode($recent['data'], true);
        }
        
        return $recent;
    }
    
    /**
     * Get dashboard stats
     */
    public function get_dashboard_stats() {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'ai_optimizer_monitoring';
        
        // Get stats from last 24 hours
        $stats = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT data FROM {$table_name} WHERE created_at >= %s ORDER BY created_at ASC",
                date('Y-m-d H:i:s', strtotime('-24 hours'))
            ),
            ARRAY_A
        );
        
        $processed_stats = array(
            'avg_load_time' => 0,
            'avg_memory_usage' => 0,
            'error_count' => 0,
            'uptime_percentage' => 100,
        );
        
        if (!empty($stats)) {
            $total_load_time = 0;
            $total_memory = 0;
            $error_count = 0;
            $down_count = 0;
            
            foreach ($stats as $stat) {
                $data = json_decode($stat['data'], true);
                
                if (isset($data['performance'])) {
                    $total_load_time += $data['performance']['load_time'];
                    $total_memory += $data['performance']['memory_usage'];
                    
                    if ($data['performance']['response_code'] !== 200) {
                        $down_count++;
                    }
                }
                
                if (isset($data['errors'])) {
                    $error_count += array_sum($data['errors']);
                }
            }
            
            $count = count($stats);
            $processed_stats['avg_load_time'] = $total_load_time / $count;
            $processed_stats['avg_memory_usage'] = $total_memory / $count;
            $processed_stats['error_count'] = $error_count;
            $processed_stats['uptime_percentage'] = (($count - $down_count) / $count) * 100;
        }
        
        return $processed_stats;
    }
    
    /**
     * Helper methods
     */
    private function check_sitemap() {
        $sitemap_url = home_url('/sitemap.xml');
        $response = wp_remote_head($sitemap_url);
        return wp_remote_retrieve_response_code($response) === 200;
    }
    
    private function check_robots_txt() {
        $robots_url = home_url('/robots.txt');
        $response = wp_remote_head($robots_url);
        return wp_remote_retrieve_response_code($response) === 200;
    }
    
    private function check_admin_user() {
        $admin_user = get_user_by('login', 'admin');
        return $admin_user !== false;
    }
    
    private function check_file_permissions() {
        $wp_config_perms = substr(sprintf('%o', fileperms(ABSPATH . 'wp-config.php')), -4);
        return $wp_config_perms === '0644' || $wp_config_perms === '0600';
    }
    
    private function parse_error_log($log_file) {
        if (!file_exists($log_file)) {
            return array();
        }
        
        $lines = file($log_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $recent_errors = array();
        $cutoff_time = strtotime('-24 hours');
        
        foreach (array_reverse($lines) as $line) {
            if (preg_match('/^\[(\d{2}-\w{3}-\d{4} \d{2}:\d{2}:\d{2})/', $line, $matches)) {
                $error_time = strtotime($matches[1]);
                if ($error_time >= $cutoff_time) {
                    $recent_errors[] = $line;
                } else {
                    break;
                }
            }
        }
        
        return $recent_errors;
    }
    
    /**
     * Get performance data for charts
     */
    public function get_performance_data() {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'ai_optimizer_monitoring';
        
        $data = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT data, created_at FROM {$table_name} WHERE created_at >= %s ORDER BY created_at ASC",
                date('Y-m-d H:i:s', strtotime('-7 days'))
            ),
            ARRAY_A
        );
        
        $chart_data = array(
            'labels' => array(),
            'load_times' => array(),
            'memory_usage' => array(),
        );
        
        foreach ($data as $row) {
            $monitoring_data = json_decode($row['data'], true);
            
            if (isset($monitoring_data['performance'])) {
                $chart_data['labels'][] = date('M j H:i', strtotime($row['created_at']));
                $chart_data['load_times'][] = round($monitoring_data['performance']['load_time'] * 1000, 2);
                $chart_data['memory_usage'][] = round($monitoring_data['performance']['memory_usage'] / 1024 / 1024, 2);
            }
        }
        
        return $chart_data;
    }
    
    /**
     * Get recent activities
     */
    public function get_recent_activities() {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'ai_optimizer_logs';
        
        return $wpdb->get_results(
            "SELECT * FROM {$table_name} ORDER BY created_at DESC LIMIT 10",
            ARRAY_A
        );
    }
    
    /**
     * Get active alerts
     */
    public function get_active_alerts() {
        $alerts = array();
        
        // Check recent performance
        $recent_data = $this->get_recent_data();
        if ($recent_data && isset($recent_data['data']['performance'])) {
            $performance = $recent_data['data']['performance'];
            
            if ($performance['load_time'] > 3) {
                $alerts[] = array(
                    'type' => 'warning',
                    'message' => 'Website load time is slower than recommended (>3 seconds)',
                    'action' => 'optimize_performance',
                );
            }
            
            if ($performance['memory_usage'] > 128 * 1024 * 1024) {
                $alerts[] = array(
                    'type' => 'warning',
                    'message' => 'High memory usage detected',
                    'action' => 'optimize_memory',
                );
            }
            
            if ($performance['response_code'] !== 200) {
                $alerts[] = array(
                    'type' => 'error',
                    'message' => 'Website returning error response code: ' . $performance['response_code'],
                    'action' => 'check_errors',
                );
            }
        }
        
        return $alerts;
    }
}
