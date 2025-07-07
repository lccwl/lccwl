<?php
/**
 * Database management class
 */

if (!defined('ABSPATH')) {
    exit;
}

class AI_Optimizer_Database {
    
    private static $instance = null;
    private $db_version = '1.0.0';
    
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
        add_action('plugins_loaded', array($this, 'check_database_version'));
    }
    
    /**
     * Check database version and update if needed
     */
    public function check_database_version() {
        $installed_version = get_option('ai_optimizer_db_version', '0.0.0');
        
        if (version_compare($installed_version, $this->db_version, '<')) {
            $this->create_tables();
            update_option('ai_optimizer_db_version', $this->db_version);
        }
    }
    
    /**
     * Create all required database tables
     */
    public static function create_tables() {
        global $wpdb;
        
        $charset_collate = $wpdb->get_charset_collate();
        
        // Monitoring data table
        $monitoring_table = $wpdb->prefix . 'ai_optimizer_monitoring';
        $monitoring_sql = "CREATE TABLE $monitoring_table (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            timestamp datetime NOT NULL,
            data longtext NOT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY timestamp (timestamp),
            KEY created_at (created_at)
        ) $charset_collate;";
        
        // Analysis results table
        $analysis_table = $wpdb->prefix . 'ai_optimizer_analysis';
        $analysis_sql = "CREATE TABLE $analysis_table (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            type varchar(50) NOT NULL,
            analysis longtext NOT NULL,
            original_data longtext,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY type (type),
            KEY created_at (created_at)
        ) $charset_collate;";
        
        // SEO analysis table
        $seo_analysis_table = $wpdb->prefix . 'ai_optimizer_seo_analysis';
        $seo_analysis_sql = "CREATE TABLE $seo_analysis_table (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            page_id bigint(20) NOT NULL,
            analysis_data longtext NOT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY page_id (page_id),
            KEY created_at (created_at)
        ) $charset_collate;";
        
        // SEO suggestions table
        $seo_suggestions_table = $wpdb->prefix . 'ai_optimizer_seo_suggestions';
        $seo_suggestions_sql = "CREATE TABLE $seo_suggestions_table (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            page_id bigint(20) DEFAULT NULL,
            type varchar(50) NOT NULL,
            priority varchar(20) NOT NULL DEFAULT 'medium',
            title varchar(255) NOT NULL,
            description text NOT NULL,
            action varchar(100) NOT NULL,
            status varchar(20) NOT NULL DEFAULT 'pending',
            applied_at datetime DEFAULT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY page_id (page_id),
            KEY status (status),
            KEY priority (priority),
            KEY created_at (created_at)
        ) $charset_collate;";
        
        // Code issues table
        $code_issues_table = $wpdb->prefix . 'ai_optimizer_code_issues';
        $code_issues_sql = "CREATE TABLE $code_issues_table (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            file_path varchar(500) NOT NULL,
            issue_type varchar(50) NOT NULL,
            severity varchar(20) NOT NULL,
            message text NOT NULL,
            line_number int(11) DEFAULT NULL,
            auto_fixable tinyint(1) DEFAULT 0,
            issue_data longtext,
            status varchar(20) NOT NULL DEFAULT 'pending',
            fixed_at datetime DEFAULT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY file_path (file_path(255)),
            KEY issue_type (issue_type),
            KEY severity (severity),
            KEY status (status),
            KEY created_at (created_at)
        ) $charset_collate;";
        
        // Content generations table
        $generations_table = $wpdb->prefix . 'ai_optimizer_generations';
        $generations_sql = "CREATE TABLE $generations_table (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            type varchar(50) NOT NULL,
            prompt text NOT NULL,
            result longtext,
            options longtext,
            user_id bigint(20) NOT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY type (type),
            KEY user_id (user_id),
            KEY created_at (created_at)
        ) $charset_collate;";
        
        // Video requests table
        $video_requests_table = $wpdb->prefix . 'ai_optimizer_video_requests';
        $video_requests_sql = "CREATE TABLE $video_requests_table (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            request_id varchar(100) NOT NULL UNIQUE,
            prompt text NOT NULL,
            options longtext,
            status varchar(20) NOT NULL DEFAULT 'processing',
            result_url text,
            user_id bigint(20) NOT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            completed_at datetime DEFAULT NULL,
            PRIMARY KEY (id),
            UNIQUE KEY request_id (request_id),
            KEY status (status),
            KEY user_id (user_id),
            KEY created_at (created_at)
        ) $charset_collate;";
        
        // Video projects table (for long videos)
        $video_projects_table = $wpdb->prefix . 'ai_optimizer_video_projects';
        $video_projects_sql = "CREATE TABLE $video_projects_table (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            segments_data longtext NOT NULL,
            total_duration int(11) NOT NULL,
            status varchar(20) NOT NULL DEFAULT 'processing',
            final_video_url text,
            user_id bigint(20) NOT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            completed_at datetime DEFAULT NULL,
            PRIMARY KEY (id),
            KEY status (status),
            KEY user_id (user_id),
            KEY created_at (created_at)
        ) $charset_collate;";
        
        // API usage tracking table
        $api_usage_table = $wpdb->prefix . 'ai_optimizer_api_usage';
        $api_usage_sql = "CREATE TABLE $api_usage_table (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            endpoint varchar(255) NOT NULL,
            status varchar(20) NOT NULL,
            response_time float DEFAULT NULL,
            tokens_used int(11) DEFAULT NULL,
            cost_estimate decimal(10,6) DEFAULT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY endpoint (endpoint),
            KEY status (status),
            KEY created_at (created_at)
        ) $charset_collate;";
        
        // Logs table
        $logs_table = $wpdb->prefix . 'ai_optimizer_logs';
        $logs_sql = "CREATE TABLE $logs_table (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            level varchar(20) NOT NULL,
            message text NOT NULL,
            context longtext,
            user_id bigint(20) DEFAULT NULL,
            ip_address varchar(45) DEFAULT NULL,
            user_agent text,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY level (level),
            KEY user_id (user_id),
            KEY created_at (created_at)
        ) $charset_collate;";
        
        // Settings backup table
        $settings_backup_table = $wpdb->prefix . 'ai_optimizer_settings_backup';
        $settings_backup_sql = "CREATE TABLE $settings_backup_table (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            settings_data longtext NOT NULL,
            backup_reason varchar(255),
            user_id bigint(20) NOT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY user_id (user_id),
            KEY created_at (created_at)
        ) $charset_collate;";
        
        // Content collection table
        $content_collection_table = $wpdb->prefix . 'ai_optimizer_collected_content';
        $content_collection_sql = "CREATE TABLE $content_collection_table (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            source_url varchar(500) NOT NULL,
            source_type varchar(50) NOT NULL,
            original_title varchar(500),
            original_content longtext,
            processed_title varchar(500),
            processed_content longtext,
            post_id bigint(20) DEFAULT NULL,
            status varchar(20) NOT NULL DEFAULT 'pending',
            quality_score float DEFAULT NULL,
            similarity_score float DEFAULT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            processed_at datetime DEFAULT NULL,
            published_at datetime DEFAULT NULL,
            PRIMARY KEY (id),
            KEY source_url (source_url(255)),
            KEY source_type (source_type),
            KEY post_id (post_id),
            KEY status (status),
            KEY created_at (created_at)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        
        // Execute table creation
        $tables = array(
            $monitoring_sql,
            $analysis_sql,
            $seo_analysis_sql,
            $seo_suggestions_sql,
            $code_issues_sql,
            $generations_sql,
            $video_requests_sql,
            $video_projects_sql,
            $api_usage_sql,
            $logs_sql,
            $settings_backup_sql,
            $content_collection_sql
        );
        
        foreach ($tables as $sql) {
            dbDelta($sql);
        }
        
        // Create indexes for better performance
        self::create_indexes();
        
        AI_Optimizer_Utils::log('Database tables created/updated', 'info', array(
            'db_version' => self::get_instance()->db_version,
            'tables_count' => count($tables)
        ));
    }
    
    /**
     * Create additional indexes for performance
     */
    private static function create_indexes() {
        global $wpdb;
        
        $indexes = array(
            // Monitoring table indexes
            "CREATE INDEX idx_monitoring_timestamp ON {$wpdb->prefix}ai_optimizer_monitoring (timestamp)",
            
            // Analysis table indexes
            "CREATE INDEX idx_analysis_type_date ON {$wpdb->prefix}ai_optimizer_analysis (type, created_at)",
            
            // SEO suggestions compound indexes
            "CREATE INDEX idx_seo_suggestions_status_priority ON {$wpdb->prefix}ai_optimizer_seo_suggestions (status, priority)",
            
            // Code issues compound indexes
            "CREATE INDEX idx_code_issues_severity_status ON {$wpdb->prefix}ai_optimizer_code_issues (severity, status)",
            
            // API usage indexes
            "CREATE INDEX idx_api_usage_endpoint_date ON {$wpdb->prefix}ai_optimizer_api_usage (endpoint, created_at)",
            
            // Logs cleanup index
            "CREATE INDEX idx_logs_cleanup ON {$wpdb->prefix}ai_optimizer_logs (level, created_at)",
        );
        
        foreach ($indexes as $index_sql) {
            $wpdb->query($index_sql);
        }
    }
    
    /**
     * Check if all tables exist
     */
    public static function check_tables() {
        global $wpdb;
        
        $required_tables = array(
            $wpdb->prefix . 'ai_optimizer_monitoring',
            $wpdb->prefix . 'ai_optimizer_analysis',
            $wpdb->prefix . 'ai_optimizer_seo_analysis',
            $wpdb->prefix . 'ai_optimizer_seo_suggestions',
            $wpdb->prefix . 'ai_optimizer_code_issues',
            $wpdb->prefix . 'ai_optimizer_generations',
            $wpdb->prefix . 'ai_optimizer_video_requests',
            $wpdb->prefix . 'ai_optimizer_video_projects',
            $wpdb->prefix . 'ai_optimizer_api_usage',
            $wpdb->prefix . 'ai_optimizer_logs',
            $wpdb->prefix . 'ai_optimizer_settings_backup',
            $wpdb->prefix . 'ai_optimizer_collected_content'
        );
        
        foreach ($required_tables as $table) {
            if ($wpdb->get_var("SHOW TABLES LIKE '$table'") !== $table) {
                return false;
            }
        }
        
        return true;
    }
    
    /**
     * Drop all plugin tables
     */
    public static function drop_tables() {
        global $wpdb;
        
        $tables = array(
            $wpdb->prefix . 'ai_optimizer_monitoring',
            $wpdb->prefix . 'ai_optimizer_analysis',
            $wpdb->prefix . 'ai_optimizer_seo_analysis',
            $wpdb->prefix . 'ai_optimizer_seo_suggestions',
            $wpdb->prefix . 'ai_optimizer_code_issues',
            $wpdb->prefix . 'ai_optimizer_generations',
            $wpdb->prefix . 'ai_optimizer_video_requests',
            $wpdb->prefix . 'ai_optimizer_video_projects',
            $wpdb->prefix . 'ai_optimizer_api_usage',
            $wpdb->prefix . 'ai_optimizer_logs',
            $wpdb->prefix . 'ai_optimizer_settings_backup',
            $wpdb->prefix . 'ai_optimizer_collected_content'
        );
        
        foreach ($tables as $table) {
            $wpdb->query("DROP TABLE IF EXISTS $table");
        }
        
        delete_option('ai_optimizer_db_version');
    }
    
    /**
     * Clean up old data
     */
    public static function cleanup_old_data($days = 30) {
        global $wpdb;
        
        $date_threshold = date('Y-m-d H:i:s', strtotime("-{$days} days"));
        
        // Clean monitoring data older than specified days
        $deleted_monitoring = $wpdb->query(
            $wpdb->prepare(
                "DELETE FROM {$wpdb->prefix}ai_optimizer_monitoring WHERE created_at < %s",
                $date_threshold
            )
        );
        
        // Clean old logs
        $deleted_logs = $wpdb->query(
            $wpdb->prepare(
                "DELETE FROM {$wpdb->prefix}ai_optimizer_logs WHERE created_at < %s AND level NOT IN ('error', 'critical')",
                $date_threshold
            )
        );
        
        // Clean old API usage data
        $deleted_api_usage = $wpdb->query(
            $wpdb->prepare(
                "DELETE FROM {$wpdb->prefix}ai_optimizer_api_usage WHERE created_at < %s",
                $date_threshold
            )
        );
        
        // Clean completed video requests older than 7 days
        $video_threshold = date('Y-m-d H:i:s', strtotime('-7 days'));
        $deleted_videos = $wpdb->query(
            $wpdb->prepare(
                "DELETE FROM {$wpdb->prefix}ai_optimizer_video_requests WHERE status = 'completed' AND completed_at < %s",
                $video_threshold
            )
        );
        
        AI_Optimizer_Utils::log('Database cleanup completed', 'info', array(
            'days_threshold' => $days,
            'deleted_monitoring' => $deleted_monitoring,
            'deleted_logs' => $deleted_logs,
            'deleted_api_usage' => $deleted_api_usage,
            'deleted_videos' => $deleted_videos
        ));
        
        return array(
            'monitoring' => $deleted_monitoring,
            'logs' => $deleted_logs,
            'api_usage' => $deleted_api_usage,
            'videos' => $deleted_videos
        );
    }
    
    /**
     * Optimize database tables
     */
    public static function optimize_tables() {
        global $wpdb;
        
        $tables = array(
            $wpdb->prefix . 'ai_optimizer_monitoring',
            $wpdb->prefix . 'ai_optimizer_analysis',
            $wpdb->prefix . 'ai_optimizer_seo_analysis',
            $wpdb->prefix . 'ai_optimizer_seo_suggestions',
            $wpdb->prefix . 'ai_optimizer_code_issues',
            $wpdb->prefix . 'ai_optimizer_generations',
            $wpdb->prefix . 'ai_optimizer_video_requests',
            $wpdb->prefix . 'ai_optimizer_video_projects',
            $wpdb->prefix . 'ai_optimizer_api_usage',
            $wpdb->prefix . 'ai_optimizer_logs',
            $wpdb->prefix . 'ai_optimizer_settings_backup',
            $wpdb->prefix . 'ai_optimizer_collected_content'
        );
        
        foreach ($tables as $table) {
            $wpdb->query("OPTIMIZE TABLE $table");
        }
        
        AI_Optimizer_Utils::log('Database tables optimized', 'info', array(
            'tables_count' => count($tables)
        ));
    }
    
    /**
     * Get database statistics
     */
    public static function get_database_stats() {
        global $wpdb;
        
        $stats = array();
        
        $tables = array(
            'monitoring' => $wpdb->prefix . 'ai_optimizer_monitoring',
            'analysis' => $wpdb->prefix . 'ai_optimizer_analysis',
            'seo_analysis' => $wpdb->prefix . 'ai_optimizer_seo_analysis',
            'seo_suggestions' => $wpdb->prefix . 'ai_optimizer_seo_suggestions',
            'code_issues' => $wpdb->prefix . 'ai_optimizer_code_issues',
            'generations' => $wpdb->prefix . 'ai_optimizer_generations',
            'video_requests' => $wpdb->prefix . 'ai_optimizer_video_requests',
            'video_projects' => $wpdb->prefix . 'ai_optimizer_video_projects',
            'api_usage' => $wpdb->prefix . 'ai_optimizer_api_usage',
            'logs' => $wpdb->prefix . 'ai_optimizer_logs',
            'collected_content' => $wpdb->prefix . 'ai_optimizer_collected_content'
        );
        
        foreach ($tables as $key => $table) {
            $count = $wpdb->get_var("SELECT COUNT(*) FROM $table");
            $stats[$key] = intval($count);
        }
        
        // Get total database size
        $size_query = $wpdb->prepare(
            "SELECT SUM(data_length + index_length) as size 
            FROM information_schema.TABLES 
            WHERE table_schema = %s 
            AND table_name LIKE %s",
            DB_NAME,
            $wpdb->prefix . 'ai_optimizer_%'
        );
        
        $total_size = $wpdb->get_var($size_query);
        $stats['total_size_bytes'] = intval($total_size);
        $stats['total_size_mb'] = round($total_size / 1024 / 1024, 2);
        
        return $stats;
    }
    
    /**
     * Export data for backup
     */
    public static function export_data($table_name) {
        global $wpdb;
        
        $full_table_name = $wpdb->prefix . 'ai_optimizer_' . $table_name;
        
        $results = $wpdb->get_results("SELECT * FROM $full_table_name", ARRAY_A);
        
        if ($results) {
            return json_encode($results, JSON_PRETTY_PRINT);
        }
        
        return false;
    }
    
    /**
     * Import data from backup
     */
    public static function import_data($table_name, $data) {
        global $wpdb;
        
        $full_table_name = $wpdb->prefix . 'ai_optimizer_' . $table_name;
        $imported_data = json_decode($data, true);
        
        if (!$imported_data || !is_array($imported_data)) {
            return false;
        }
        
        $imported_count = 0;
        
        foreach ($imported_data as $row) {
            $result = $wpdb->insert($full_table_name, $row);
            if ($result) {
                $imported_count++;
            }
        }
        
        return $imported_count;
    }
    
    /**
     * Backup settings before major changes
     */
    public static function backup_settings($reason = 'Manual backup') {
        global $wpdb;
        
        // Get all plugin options
        $options = $wpdb->get_results(
            "SELECT option_name, option_value 
            FROM {$wpdb->options} 
            WHERE option_name LIKE 'ai_optimizer_%'",
            ARRAY_A
        );
        
        $settings_data = array();
        foreach ($options as $option) {
            $settings_data[$option['option_name']] = $option['option_value'];
        }
        
        $table_name = $wpdb->prefix . 'ai_optimizer_settings_backup';
        
        $result = $wpdb->insert(
            $table_name,
            array(
                'settings_data' => json_encode($settings_data),
                'backup_reason' => $reason,
                'user_id' => get_current_user_id(),
                'created_at' => current_time('mysql')
            ),
            array('%s', '%s', '%d', '%s')
        );
        
        if ($result) {
            AI_Optimizer_Utils::log('Settings backed up', 'info', array(
                'reason' => $reason,
                'options_count' => count($settings_data)
            ));
        }
        
        return $result ? $wpdb->insert_id : false;
    }
    
    /**
     * Restore settings from backup
     */
    public static function restore_settings($backup_id) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'ai_optimizer_settings_backup';
        
        $backup = $wpdb->get_row(
            $wpdb->prepare("SELECT * FROM $table_name WHERE id = %d", $backup_id),
            ARRAY_A
        );
        
        if (!$backup) {
            return false;
        }
        
        $settings_data = json_decode($backup['settings_data'], true);
        
        if (!$settings_data) {
            return false;
        }
        
        $restored_count = 0;
        
        foreach ($settings_data as $option_name => $option_value) {
            update_option($option_name, $option_value);
            $restored_count++;
        }
        
        AI_Optimizer_Utils::log('Settings restored from backup', 'info', array(
            'backup_id' => $backup_id,
            'restored_options' => $restored_count
        ));
        
        return $restored_count;
    }
}
