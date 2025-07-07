<?php
/**
 * 数据库管理类
 */

if (!defined('ABSPATH')) {
    exit;
}

class AI_Optimizer_Database {
    
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
        // 数据库初始化钩子
        add_action('init', array($this, 'check_database_version'));
    }
    
    /**
     * 创建数据库表
     */
    public static function create_tables() {
        global $wpdb;
        
        $charset_collate = $wpdb->get_charset_collate();
        
        // 监控数据表
        $monitoring_table = $wpdb->prefix . 'ai_optimizer_monitoring';
        $sql_monitoring = "CREATE TABLE IF NOT EXISTS $monitoring_table (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            url varchar(500) NOT NULL,
            load_time float NOT NULL,
            memory_usage bigint(20) NOT NULL,
            queries_count int(11) NOT NULL DEFAULT 0,
            response_time float NOT NULL DEFAULT 0,
            status_code int(11) NOT NULL DEFAULT 200,
            created_at datetime NOT NULL,
            PRIMARY KEY (id),
            KEY url (url),
            KEY created_at (created_at)
        ) $charset_collate;";
        
        // SEO分析表
        $seo_table = $wpdb->prefix . 'ai_optimizer_seo_analysis';
        $sql_seo = "CREATE TABLE IF NOT EXISTS $seo_table (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            url varchar(500) NOT NULL,
            seo_score int(11) NOT NULL,
            title_score int(11) NOT NULL DEFAULT 0,
            meta_score int(11) NOT NULL DEFAULT 0,
            content_score int(11) NOT NULL DEFAULT 0,
            suggestions longtext,
            issues longtext,
            created_at datetime NOT NULL,
            PRIMARY KEY (id),
            KEY url (url),
            KEY created_at (created_at)
        ) $charset_collate;";
        
        // SEO建议表
        $suggestions_table = $wpdb->prefix . 'ai_optimizer_seo_suggestions';
        $sql_suggestions = "CREATE TABLE IF NOT EXISTS $suggestions_table (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            analysis_id bigint(20) unsigned NOT NULL,
            type varchar(50) NOT NULL,
            title varchar(255) NOT NULL,
            description text NOT NULL,
            priority enum('low', 'medium', 'high', 'critical') NOT NULL DEFAULT 'medium',
            status enum('pending', 'applied', 'dismissed') NOT NULL DEFAULT 'pending',
            auto_fix_code longtext,
            created_at datetime NOT NULL,
            updated_at datetime NOT NULL,
            PRIMARY KEY (id),
            KEY analysis_id (analysis_id),
            KEY status (status),
            KEY priority (priority),
            FOREIGN KEY (analysis_id) REFERENCES $seo_table(id) ON DELETE CASCADE
        ) $charset_collate;";
        
        // 代码问题表
        $code_issues_table = $wpdb->prefix . 'ai_optimizer_code_issues';
        $sql_code_issues = "CREATE TABLE IF NOT EXISTS $code_issues_table (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            file_path varchar(500) NOT NULL,
            line_number int(11) NOT NULL DEFAULT 0,
            issue_type varchar(100) NOT NULL,
            severity enum('info', 'warning', 'error', 'critical') NOT NULL DEFAULT 'warning',
            message text NOT NULL,
            suggestion text,
            auto_fix_code longtext,
            status enum('open', 'fixed', 'dismissed') NOT NULL DEFAULT 'open',
            created_at datetime NOT NULL,
            PRIMARY KEY (id),
            KEY file_path (file_path),
            KEY issue_type (issue_type),
            KEY severity (severity),
            KEY status (status)
        ) $charset_collate;";
        
        // AI生成内容表
        $generations_table = $wpdb->prefix . 'ai_optimizer_generations';
        $sql_generations = "CREATE TABLE IF NOT EXISTS $generations_table (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            type enum('text', 'image', 'video', 'audio', 'code') NOT NULL,
            prompt text NOT NULL,
            result longtext,
            model varchar(100),
            parameters longtext,
            status enum('pending', 'processing', 'completed', 'failed') NOT NULL DEFAULT 'pending',
            error_message text,
            created_at datetime NOT NULL,
            completed_at datetime,
            PRIMARY KEY (id),
            KEY type (type),
            KEY status (status),
            KEY created_at (created_at)
        ) $charset_collate;";
        
        // 视频生成请求表
        $video_requests_table = $wpdb->prefix . 'ai_optimizer_video_requests';
        $sql_video_requests = "CREATE TABLE IF NOT EXISTS $video_requests_table (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            request_id varchar(100) NOT NULL UNIQUE,
            prompt text NOT NULL,
            model varchar(100) NOT NULL DEFAULT 'ltx-video',
            status enum('submitted', 'processing', 'completed', 'failed') NOT NULL DEFAULT 'submitted',
            video_url text,
            error_message text,
            created_at datetime NOT NULL,
            updated_at datetime NOT NULL,
            PRIMARY KEY (id),
            KEY request_id (request_id),
            KEY status (status)
        ) $charset_collate;";
        
        // API使用统计表
        $api_usage_table = $wpdb->prefix . 'ai_optimizer_api_usage';
        $sql_api_usage = "CREATE TABLE IF NOT EXISTS $api_usage_table (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            endpoint varchar(100) NOT NULL,
            model varchar(100),
            tokens_used int(11) NOT NULL DEFAULT 0,
            cost decimal(10,6) NOT NULL DEFAULT 0,
            response_time float NOT NULL DEFAULT 0,
            status enum('success', 'error') NOT NULL DEFAULT 'success',
            error_message text,
            created_at datetime NOT NULL,
            PRIMARY KEY (id),
            KEY endpoint (endpoint),
            KEY model (model),
            KEY created_at (created_at)
        ) $charset_collate;";
        
        // 前端性能监控表
        $frontend_perf_table = $wpdb->prefix . 'ai_optimizer_frontend_performance';
        $sql_frontend_perf = "CREATE TABLE IF NOT EXISTS $frontend_perf_table (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            url varchar(500) NOT NULL,
            page_load_time float NOT NULL,
            dom_content_loaded float NOT NULL,
            first_contentful_paint float NOT NULL,
            largest_contentful_paint float NOT NULL,
            cumulative_layout_shift float NOT NULL,
            first_input_delay float NOT NULL,
            user_agent text,
            viewport_width int(11),
            viewport_height int(11),
            connection_type varchar(50),
            created_at datetime NOT NULL,
            PRIMARY KEY (id),
            KEY url (url),
            KEY created_at (created_at)
        ) $charset_collate;";
        
        // 前端错误监控表
        $frontend_errors_table = $wpdb->prefix . 'ai_optimizer_frontend_errors';
        $sql_frontend_errors = "CREATE TABLE IF NOT EXISTS $frontend_errors_table (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            url varchar(500) NOT NULL,
            error_message text NOT NULL,
            error_stack longtext,
            line_number int(11),
            column_number int(11),
            filename varchar(500),
            user_agent text,
            browser_info longtext,
            created_at datetime NOT NULL,
            PRIMARY KEY (id),
            KEY url (url),
            KEY created_at (created_at)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        
        dbDelta($sql_monitoring);
        dbDelta($sql_seo);
        dbDelta($sql_suggestions);
        dbDelta($sql_code_issues);
        dbDelta($sql_generations);
        dbDelta($sql_video_requests);
        dbDelta($sql_api_usage);
        dbDelta($sql_frontend_perf);
        dbDelta($sql_frontend_errors);
        
        // 设置数据库版本
        update_option('ai_optimizer_db_version', AI_OPTIMIZER_VERSION);
    }
    
    /**
     * 检查数据库版本
     */
    public function check_database_version() {
        $current_version = get_option('ai_optimizer_db_version', '0.0.0');
        
        if (version_compare($current_version, AI_OPTIMIZER_VERSION, '<')) {
            self::create_tables();
        }
    }
    
    /**
     * 删除所有数据表
     */
    public static function drop_tables() {
        global $wpdb;
        
        $tables = array(
            $wpdb->prefix . 'ai_optimizer_monitoring',
            $wpdb->prefix . 'ai_optimizer_seo_analysis',
            $wpdb->prefix . 'ai_optimizer_seo_suggestions',
            $wpdb->prefix . 'ai_optimizer_code_issues',
            $wpdb->prefix . 'ai_optimizer_generations',
            $wpdb->prefix . 'ai_optimizer_video_requests',
            $wpdb->prefix . 'ai_optimizer_api_usage',
            $wpdb->prefix . 'ai_optimizer_frontend_performance',
            $wpdb->prefix . 'ai_optimizer_frontend_errors',
            $wpdb->prefix . 'ai_optimizer_logs',
            $wpdb->prefix . 'ai_optimizer_content_sources',
            $wpdb->prefix . 'ai_optimizer_collected_content'
        );
        
        foreach ($tables as $table) {
            $wpdb->query("DROP TABLE IF EXISTS $table");
        }
        
        delete_option('ai_optimizer_db_version');
    }
    
    /**
     * 获取数据库状态
     */
    public function get_database_status() {
        global $wpdb;
        
        $tables = array(
            'monitoring' => $wpdb->prefix . 'ai_optimizer_monitoring',
            'seo_analysis' => $wpdb->prefix . 'ai_optimizer_seo_analysis',
            'seo_suggestions' => $wpdb->prefix . 'ai_optimizer_seo_suggestions',
            'code_issues' => $wpdb->prefix . 'ai_optimizer_code_issues',
            'generations' => $wpdb->prefix . 'ai_optimizer_generations',
            'video_requests' => $wpdb->prefix . 'ai_optimizer_video_requests',
            'api_usage' => $wpdb->prefix . 'ai_optimizer_api_usage',
            'frontend_performance' => $wpdb->prefix . 'ai_optimizer_frontend_performance',
            'frontend_errors' => $wpdb->prefix . 'ai_optimizer_frontend_errors'
        );
        
        $status = array();
        
        foreach ($tables as $key => $table) {
            $exists = $wpdb->get_var("SHOW TABLES LIKE '$table'") === $table;
            $count = $exists ? $wpdb->get_var("SELECT COUNT(*) FROM $table") : 0;
            
            $status[$key] = array(
                'exists' => $exists,
                'count' => intval($count),
                'table_name' => $table
            );
        }
        
        return $status;
    }
    
    /**
     * 清理过期数据
     */
    public function cleanup_expired_data($days = 30) {
        global $wpdb;
        
        $cutoff_date = date('Y-m-d H:i:s', strtotime("-$days days"));
        
        $tables = array(
            $wpdb->prefix . 'ai_optimizer_monitoring',
            $wpdb->prefix . 'ai_optimizer_frontend_performance',
            $wpdb->prefix . 'ai_optimizer_frontend_errors',
            $wpdb->prefix . 'ai_optimizer_api_usage'
        );
        
        $cleaned_records = 0;
        
        foreach ($tables as $table) {
            if ($wpdb->get_var("SHOW TABLES LIKE '$table'") === $table) {
                $result = $wpdb->query($wpdb->prepare(
                    "DELETE FROM $table WHERE created_at < %s",
                    $cutoff_date
                ));
                
                if ($result !== false) {
                    $cleaned_records += $result;
                }
            }
        }
        
        return $cleaned_records;
    }
    
    /**
     * 优化数据库表
     */
    public function optimize_tables() {
        global $wpdb;
        
        $tables = array(
            $wpdb->prefix . 'ai_optimizer_monitoring',
            $wpdb->prefix . 'ai_optimizer_seo_analysis',
            $wpdb->prefix . 'ai_optimizer_seo_suggestions',
            $wpdb->prefix . 'ai_optimizer_code_issues',
            $wpdb->prefix . 'ai_optimizer_generations',
            $wpdb->prefix . 'ai_optimizer_video_requests',
            $wpdb->prefix . 'ai_optimizer_api_usage',
            $wpdb->prefix . 'ai_optimizer_frontend_performance',
            $wpdb->prefix . 'ai_optimizer_frontend_errors'
        );
        
        $optimized_count = 0;
        
        foreach ($tables as $table) {
            if ($wpdb->get_var("SHOW TABLES LIKE '$table'") === $table) {
                $result = $wpdb->query("OPTIMIZE TABLE $table");
                if ($result !== false) {
                    $optimized_count++;
                }
            }
        }
        
        return $optimized_count;
    }
    
    /**
     * 获取数据库大小
     */
    public function get_database_size() {
        global $wpdb;
        
        $total_size = 0;
        $tables_info = array();
        
        $tables = array(
            $wpdb->prefix . 'ai_optimizer_monitoring',
            $wpdb->prefix . 'ai_optimizer_seo_analysis',
            $wpdb->prefix . 'ai_optimizer_seo_suggestions',
            $wpdb->prefix . 'ai_optimizer_code_issues',
            $wpdb->prefix . 'ai_optimizer_generations',
            $wpdb->prefix . 'ai_optimizer_video_requests',
            $wpdb->prefix . 'ai_optimizer_api_usage',
            $wpdb->prefix . 'ai_optimizer_frontend_performance',
            $wpdb->prefix . 'ai_optimizer_frontend_errors'
        );
        
        foreach ($tables as $table) {
            $size_query = $wpdb->prepare(
                "SELECT 
                    table_name AS 'table_name',
                    ROUND(((data_length + index_length) / 1024 / 1024), 2) AS 'size_mb',
                    table_rows AS 'rows'
                FROM information_schema.TABLES 
                WHERE table_schema = %s AND table_name = %s",
                DB_NAME,
                $table
            );
            
            $result = $wpdb->get_row($size_query);
            
            if ($result) {
                $tables_info[] = $result;
                $total_size += floatval($result->size_mb);
            }
        }
        
        return array(
            'total_size_mb' => round($total_size, 2),
            'tables' => $tables_info
        );
    }
}