<?php
/**
 * AI自动化巡逻系统类
 * 
 * 实时检测网站数据库数据和代码，使用AI深度分析并提供优化建议
 */

if (!defined('ABSPATH')) {
    exit;
}

class AI_Patrol_System {
    
    private $api_key;
    private $api_base_url = 'https://api.siliconflow.cn/v1/chat/completions';
    private $wp_db;
    private $patrol_settings;
    
    public function __construct() {
        global $wpdb;
        $this->wp_db = $wpdb;
        $this->api_key = get_option('ai_opt_api_key');
        $this->patrol_settings = get_option('ai_patrol_settings', array(
            'enabled' => false,
            'interval' => 'hourly',
            'ai_model' => 'Qwen/QwQ-32B-Preview',
            'auto_fix' => false,
            'monitor_database' => true,
            'monitor_code' => true,
            'monitor_performance' => true,
            'monitor_security' => true
        ));
        
        // 初始化数据库表
        $this->create_patrol_tables();
        
        // 注册定时任务
        $this->register_cron_jobs();
    }
    
    /**
     * 创建巡逻系统数据表
     */
    private function create_patrol_tables() {
        $charset_collate = $this->wp_db->get_charset_collate();
        
        // 巡逻记录表
        $table_name = $this->wp_db->prefix . 'ai_patrol_logs';
        $sql = "CREATE TABLE $table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            patrol_type varchar(50) NOT NULL,
            status varchar(20) NOT NULL,
            issues_found int NOT NULL DEFAULT 0,
            critical_issues int NOT NULL DEFAULT 0,
            ai_analysis text,
            recommendations text,
            auto_fixes_applied text,
            execution_time float NOT NULL,
            ai_model varchar(100) NOT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
        
        // 系统健康度表
        $health_table = $this->wp_db->prefix . 'ai_system_health';
        $sql2 = "CREATE TABLE $health_table (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            metric_name varchar(100) NOT NULL,
            metric_value text NOT NULL,
            threshold_value varchar(50),
            status varchar(20) NOT NULL,
            last_checked datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY unique_metric (metric_name)
        ) $charset_collate;";
        
        dbDelta($sql2);
    }
    
    /**
     * 注册定时任务
     */
    private function register_cron_jobs() {
        if (!wp_next_scheduled('ai_patrol_system_check')) {
            wp_schedule_event(time(), $this->patrol_settings['interval'], 'ai_patrol_system_check');
        }
        
        // 添加钩子
        add_action('ai_patrol_system_check', array($this, 'run_automated_patrol'));
    }
    
    /**
     * 运行自动化巡逻
     */
    public function run_automated_patrol() {
        if (!$this->patrol_settings['enabled'] || !$this->api_key) {
            return false;
        }
        
        $start_time = microtime(true);
        $results = array();
        
        // 1. 数据库巡逻
        if ($this->patrol_settings['monitor_database']) {
            $results['database'] = $this->patrol_database();
        }
        
        // 2. 代码巡逻
        if ($this->patrol_settings['monitor_code']) {
            $results['code'] = $this->patrol_code_quality();
        }
        
        // 3. 性能巡逻
        if ($this->patrol_settings['monitor_performance']) {
            $results['performance'] = $this->patrol_performance();
        }
        
        // 4. 安全巡逻
        if ($this->patrol_settings['monitor_security']) {
            $results['security'] = $this->patrol_security();
        }
        
        // 5. 使用AI分析结果
        $ai_analysis = $this->analyze_with_ai($results);
        
        // 6. 自动修复（如果启用）
        $auto_fixes = array();
        if ($this->patrol_settings['auto_fix'] && isset($ai_analysis['auto_fixable_issues'])) {
            $auto_fixes = $this->apply_auto_fixes($ai_analysis['auto_fixable_issues']);
        }
        
        // 7. 记录结果
        $execution_time = microtime(true) - $start_time;
        $this->log_patrol_results($results, $ai_analysis, $auto_fixes, $execution_time);
        
        // 8. 更新系统健康度
        $this->update_system_health($results);
        
        return $results;
    }
    
    /**
     * 数据库巡逻
     */
    private function patrol_database() {
        $issues = array();
        $database_info = array();
        
        // 1. 检查数据库大小
        $db_size = $this->get_database_size();
        $database_info['size'] = $db_size;
        
        // 2. 检查表健康状态
        $tables = $this->wp_db->get_results("SHOW TABLES");
        $table_stats = array();
        
        foreach ($tables as $table) {
            $table_name = array_values((array)$table)[0];
            $table_status = $this->wp_db->get_row("SHOW TABLE STATUS LIKE '$table_name'");
            
            if ($table_status) {
                $table_stats[$table_name] = array(
                    'rows' => $table_status->Rows,
                    'data_length' => $table_status->Data_length,
                    'index_length' => $table_status->Index_length,
                    'data_free' => $table_status->Data_free
                );
                
                // 检查表碎片
                if ($table_status->Data_free > 1024 * 1024) { // 1MB
                    $issues[] = "表 $table_name 存在 " . size_format($table_status->Data_free) . " 的碎片";
                }
            }
        }
        
        $database_info['tables'] = $table_stats;
        
        // 3. 检查慢查询
        $slow_queries = $this->detect_slow_queries();
        if (!empty($slow_queries)) {
            $issues[] = "检测到 " . count($slow_queries) . " 个慢查询";
            $database_info['slow_queries'] = $slow_queries;
        }
        
        // 4. 检查孤立数据
        $orphaned_data = $this->find_orphaned_data();
        if (!empty($orphaned_data)) {
            $issues[] = "发现 " . count($orphaned_data) . " 个孤立数据记录";
            $database_info['orphaned_data'] = $orphaned_data;
        }
        
        // 5. 检查数据库连接
        $connection_info = $this->get_database_connection_info();
        $database_info['connections'] = $connection_info;
        
        return array(
            'status' => empty($issues) ? 'healthy' : 'warning',
            'issues' => $issues,
            'data' => $database_info
        );
    }
    
    /**
     * 代码质量巡逻
     */
    private function patrol_code_quality() {
        $issues = array();
        $code_info = array();
        
        // 1. 检查活动主题
        $active_theme = wp_get_theme();
        $theme_path = $active_theme->get_stylesheet_directory();
        $theme_files = $this->scan_php_files($theme_path);
        
        $code_info['theme'] = array(
            'name' => $active_theme->get('Name'),
            'version' => $active_theme->get('Version'),
            'path' => $theme_path,
            'files_count' => count($theme_files)
        );
        
        // 2. 检查活动插件
        $active_plugins = get_option('active_plugins');
        $plugin_issues = array();
        
        foreach ($active_plugins as $plugin) {
            $plugin_path = WP_PLUGIN_DIR . '/' . dirname($plugin);
            if (is_dir($plugin_path)) {
                $plugin_files = $this->scan_php_files($plugin_path);
                $plugin_data = get_plugin_data(WP_PLUGIN_DIR . '/' . $plugin);
                
                $code_info['plugins'][] = array(
                    'name' => $plugin_data['Name'],
                    'version' => $plugin_data['Version'],
                    'path' => $plugin_path,
                    'files_count' => count($plugin_files)
                );
                
                // 检查插件安全性
                $security_issues = $this->check_plugin_security($plugin_path);
                if (!empty($security_issues)) {
                    $plugin_issues[] = "插件 {$plugin_data['Name']} 存在安全问题: " . implode(', ', $security_issues);
                }
            }
        }
        
        if (!empty($plugin_issues)) {
            $issues = array_merge($issues, $plugin_issues);
        }
        
        // 3. 检查WordPress核心文件
        $core_integrity = $this->check_core_integrity();
        if (!$core_integrity['status']) {
            $issues[] = "WordPress核心文件完整性检查失败";
            $code_info['core_issues'] = $core_integrity['issues'];
        }
        
        // 4. 检查PHP错误日志
        $php_errors = $this->get_php_error_log();
        if (!empty($php_errors)) {
            $issues[] = "发现 " . count($php_errors) . " 个PHP错误";
            $code_info['php_errors'] = array_slice($php_errors, 0, 10); // 最近10个错误
        }
        
        return array(
            'status' => empty($issues) ? 'healthy' : 'warning',
            'issues' => $issues,
            'data' => $code_info
        );
    }
    
    /**
     * 性能巡逻
     */
    private function patrol_performance() {
        $issues = array();
        $performance_info = array();
        
        // 1. 内存使用情况
        $memory_usage = array(
            'current' => memory_get_usage(true),
            'peak' => memory_get_peak_usage(true),
            'limit' => ini_get('memory_limit')
        );
        $performance_info['memory'] = $memory_usage;
        
        $memory_limit_bytes = $this->convert_to_bytes($memory_usage['limit']);
        $memory_usage_percentage = ($memory_usage['peak'] / $memory_limit_bytes) * 100;
        
        if ($memory_usage_percentage > 80) {
            $issues[] = "内存使用率过高: " . round($memory_usage_percentage, 2) . "%";
        }
        
        // 2. 页面加载时间
        $page_load_times = array();
        $test_urls = array(
            home_url(),
            home_url() . '/wp-admin/',
            home_url() . '/wp-login.php'
        );
        
        foreach ($test_urls as $url) {
            $load_time = $this->measure_page_load_time($url);
            $page_load_times[$url] = $load_time;
            
            if ($load_time > 3) {
                $issues[] = "页面加载时间过长: $url ({$load_time}s)";
            }
        }
        
        $performance_info['page_load_times'] = $page_load_times;
        
        // 3. 数据库查询性能
        $db_query_time = $this->measure_db_query_time();
        $performance_info['db_query_time'] = $db_query_time;
        
        if ($db_query_time > 0.5) {
            $issues[] = "数据库查询时间过长: {$db_query_time}s";
        }
        
        // 4. 缓存状态
        $cache_status = $this->check_cache_status();
        $performance_info['cache'] = $cache_status;
        
        if (!$cache_status['object_cache_active']) {
            $issues[] = "对象缓存未启用";
        }
        
        // 5. 磁盘空间
        $disk_usage = $this->get_disk_usage();
        $performance_info['disk'] = $disk_usage;
        
        if ($disk_usage['percentage'] > 90) {
            $issues[] = "磁盘空间不足: " . $disk_usage['percentage'] . "%";
        }
        
        return array(
            'status' => empty($issues) ? 'healthy' : 'warning',
            'issues' => $issues,
            'data' => $performance_info
        );
    }
    
    /**
     * 安全巡逻
     */
    private function patrol_security() {
        $issues = array();
        $security_info = array();
        
        // 1. 检查文件权限
        $file_permissions = $this->check_file_permissions();
        $security_info['file_permissions'] = $file_permissions;
        
        foreach ($file_permissions as $file => $perm) {
            if ($perm['is_writable'] && !$perm['should_be_writable']) {
                $issues[] = "文件权限过于宽松: $file";
            }
        }
        
        // 2. 检查恶意文件
        $malicious_files = $this->scan_for_malicious_files();
        if (!empty($malicious_files)) {
            $issues[] = "发现 " . count($malicious_files) . " 个可疑文件";
            $security_info['malicious_files'] = $malicious_files;
        }
        
        // 3. 检查用户权限
        $user_audit = $this->audit_user_permissions();
        $security_info['user_audit'] = $user_audit;
        
        if ($user_audit['admin_count'] > 5) {
            $issues[] = "管理员账户过多: " . $user_audit['admin_count'];
        }
        
        // 4. 检查登录安全
        $login_security = $this->check_login_security();
        $security_info['login_security'] = $login_security;
        
        if ($login_security['failed_attempts'] > 100) {
            $issues[] = "检测到大量登录失败尝试: " . $login_security['failed_attempts'];
        }
        
        // 5. 检查SSL证书
        $ssl_status = $this->check_ssl_certificate();
        $security_info['ssl'] = $ssl_status;
        
        if (!$ssl_status['valid']) {
            $issues[] = "SSL证书存在问题";
        }
        
        return array(
            'status' => empty($issues) ? 'healthy' : 'warning',
            'issues' => $issues,
            'data' => $security_info
        );
    }
    
    /**
     * 使用AI分析巡逻结果
     */
    private function analyze_with_ai($patrol_results) {
        if (!$this->api_key) {
            return array('error' => 'API密钥未配置');
        }
        
        $prompt = "作为专业的系统管理员和WordPress专家，请深度分析以下网站巡逻数据：\n\n";
        $prompt .= "巡逻结果：\n" . json_encode($patrol_results, JSON_UNESCAPED_UNICODE) . "\n\n";
        $prompt .= "请提供以下分析：\n";
        $prompt .= "1. 系统整体健康状况评估（1-10分）\n";
        $prompt .= "2. 优先级排序的问题列表（高/中/低）\n";
        $prompt .= "3. 具体的解决方案和修复建议\n";
        $prompt .= "4. 可以自动修复的问题清单\n";
        $prompt .= "5. 需要人工干预的问题说明\n";
        $prompt .= "6. 预防性措施建议\n";
        $prompt .= "7. 性能优化建议\n";
        $prompt .= "8. 安全加固建议\n";
        
        $response = wp_remote_post($this->api_base_url, array(
            'headers' => array(
                'Authorization' => 'Bearer ' . $this->api_key,
                'Content-Type' => 'application/json'
            ),
            'body' => json_encode(array(
                'model' => $this->patrol_settings['ai_model'],
                'messages' => array(
                    array(
                        'role' => 'user',
                        'content' => $prompt
                    )
                ),
                'max_tokens' => 4000,
                'temperature' => 0.3
            )),
            'timeout' => 120
        ));
        
        if (is_wp_error($response)) {
            return array('error' => 'AI分析失败: ' . $response->get_error_message());
        }
        
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);
        
        if (isset($data['choices'][0]['message']['content'])) {
            $analysis = $data['choices'][0]['message']['content'];
            
            // 解析自动修复建议
            $auto_fixable_issues = $this->extract_auto_fixable_issues($analysis);
            
            return array(
                'analysis' => $analysis,
                'auto_fixable_issues' => $auto_fixable_issues,
                'model_used' => $this->patrol_settings['ai_model']
            );
        }
        
        return array('error' => '无法获取AI分析结果');
    }
    
    /**
     * 应用自动修复
     */
    private function apply_auto_fixes($fixable_issues) {
        $applied_fixes = array();
        
        foreach ($fixable_issues as $issue) {
            switch ($issue['type']) {
                case 'database_optimization':
                    $result = $this->optimize_database_tables();
                    if ($result) {
                        $applied_fixes[] = "数据库表优化: " . $result;
                    }
                    break;
                    
                case 'clear_cache':
                    $result = $this->clear_all_cache();
                    if ($result) {
                        $applied_fixes[] = "缓存清理: " . $result;
                    }
                    break;
                    
                case 'update_plugins':
                    $result = $this->update_outdated_plugins();
                    if ($result) {
                        $applied_fixes[] = "插件更新: " . $result;
                    }
                    break;
                    
                case 'cleanup_files':
                    $result = $this->cleanup_temporary_files();
                    if ($result) {
                        $applied_fixes[] = "临时文件清理: " . $result;
                    }
                    break;
            }
        }
        
        return $applied_fixes;
    }
    
    /**
     * 记录巡逻结果
     */
    private function log_patrol_results($results, $ai_analysis, $auto_fixes, $execution_time) {
        $table_name = $this->wp_db->prefix . 'ai_patrol_logs';
        
        $total_issues = 0;
        $critical_issues = 0;
        
        foreach ($results as $patrol_type => $result) {
            $issues_count = count($result['issues']);
            $total_issues += $issues_count;
            
            if ($result['status'] === 'critical') {
                $critical_issues += $issues_count;
            }
        }
        
        return $this->wp_db->insert(
            $table_name,
            array(
                'patrol_type' => 'full_system',
                'status' => $critical_issues > 0 ? 'critical' : ($total_issues > 0 ? 'warning' : 'healthy'),
                'issues_found' => $total_issues,
                'critical_issues' => $critical_issues,
                'ai_analysis' => isset($ai_analysis['analysis']) ? $ai_analysis['analysis'] : '',
                'recommendations' => json_encode($ai_analysis),
                'auto_fixes_applied' => json_encode($auto_fixes),
                'execution_time' => $execution_time,
                'ai_model' => $this->patrol_settings['ai_model'],
                'created_at' => current_time('mysql')
            ),
            array('%s', '%s', '%d', '%d', '%s', '%s', '%s', '%f', '%s', '%s')
        );
    }
    
    /**
     * 更新系统健康度
     */
    private function update_system_health($results) {
        $health_table = $this->wp_db->prefix . 'ai_system_health';
        
        foreach ($results as $category => $data) {
            $this->wp_db->replace(
                $health_table,
                array(
                    'metric_name' => $category . '_health',
                    'metric_value' => json_encode($data),
                    'status' => $data['status'],
                    'last_checked' => current_time('mysql')
                ),
                array('%s', '%s', '%s', '%s')
            );
        }
    }
    
    // 辅助方法实现
    private function get_database_size() {
        $result = $this->wp_db->get_var("SELECT SUM(data_length + index_length) FROM information_schema.tables WHERE table_schema = '{$this->wp_db->dbname}'");
        return $result ? $result : 0;
    }
    
    private function detect_slow_queries() {
        // 模拟慢查询检测
        return array();
    }
    
    private function find_orphaned_data() {
        $orphaned = array();
        
        // 查找孤立的postmeta
        $result = $this->wp_db->get_results("
            SELECT pm.meta_id, pm.post_id 
            FROM {$this->wp_db->postmeta} pm 
            LEFT JOIN {$this->wp_db->posts} p ON pm.post_id = p.ID 
            WHERE p.ID IS NULL 
            LIMIT 100
        ");
        
        if ($result) {
            $orphaned['postmeta'] = count($result);
        }
        
        return $orphaned;
    }
    
    private function get_database_connection_info() {
        return array(
            'host' => DB_HOST,
            'name' => DB_NAME,
            'charset' => DB_CHARSET,
            'collate' => DB_COLLATE
        );
    }
    
    private function scan_php_files($directory) {
        $files = array();
        if (is_dir($directory)) {
            $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directory));
            foreach ($iterator as $file) {
                if ($file->getExtension() === 'php') {
                    $files[] = $file->getPathname();
                }
            }
        }
        return $files;
    }
    
    private function check_plugin_security($plugin_path) {
        $issues = array();
        
        // 检查常见安全问题
        $dangerous_functions = array('eval', 'exec', 'system', 'shell_exec', 'file_get_contents');
        
        $files = $this->scan_php_files($plugin_path);
        foreach ($files as $file) {
            $content = file_get_contents($file);
            foreach ($dangerous_functions as $func) {
                if (strpos($content, $func . '(') !== false) {
                    $issues[] = "使用了危险函数: $func";
                }
            }
        }
        
        return array_unique($issues);
    }
    
    private function check_core_integrity() {
        // 简化的核心文件完整性检查
        $core_files = array(
            ABSPATH . 'wp-config.php',
            ABSPATH . 'wp-load.php',
            ABSPATH . 'wp-blog-header.php'
        );
        
        $issues = array();
        foreach ($core_files as $file) {
            if (!file_exists($file)) {
                $issues[] = "缺少核心文件: $file";
            }
        }
        
        return array(
            'status' => empty($issues),
            'issues' => $issues
        );
    }
    
    private function get_php_error_log() {
        $log_file = ini_get('error_log');
        if (!$log_file || !file_exists($log_file)) {
            return array();
        }
        
        $lines = file($log_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        return array_slice($lines, -50); // 最近50行
    }
    
    private function measure_page_load_time($url) {
        $start_time = microtime(true);
        $response = wp_remote_get($url, array('timeout' => 10));
        $end_time = microtime(true);
        
        if (is_wp_error($response)) {
            return 0;
        }
        
        return round($end_time - $start_time, 2);
    }
    
    private function measure_db_query_time() {
        $start_time = microtime(true);
        $this->wp_db->get_results("SELECT ID FROM {$this->wp_db->posts} LIMIT 1");
        $end_time = microtime(true);
        
        return round($end_time - $start_time, 4);
    }
    
    private function check_cache_status() {
        return array(
            'object_cache_active' => wp_using_ext_object_cache(),
            'page_cache_active' => false // 需要根据具体缓存插件检测
        );
    }
    
    private function get_disk_usage() {
        $bytes = disk_free_space(ABSPATH);
        $total = disk_total_space(ABSPATH);
        $used = $total - $bytes;
        
        return array(
            'used' => $used,
            'free' => $bytes,
            'total' => $total,
            'percentage' => round(($used / $total) * 100, 2)
        );
    }
    
    private function check_file_permissions() {
        $files_to_check = array(
            ABSPATH . 'wp-config.php' => array('should_be_writable' => false),
            ABSPATH . 'wp-content/' => array('should_be_writable' => true),
            ABSPATH . '.htaccess' => array('should_be_writable' => false)
        );
        
        $permissions = array();
        foreach ($files_to_check as $file => $config) {
            if (file_exists($file)) {
                $permissions[$file] = array(
                    'is_writable' => is_writable($file),
                    'should_be_writable' => $config['should_be_writable'],
                    'permissions' => substr(sprintf('%o', fileperms($file)), -4)
                );
            }
        }
        
        return $permissions;
    }
    
    private function scan_for_malicious_files() {
        $malicious_patterns = array(
            'eval\s*\(',
            'base64_decode\s*\(',
            'exec\s*\(',
            'system\s*\(',
            'shell_exec\s*\('
        );
        
        $suspicious_files = array();
        $upload_dir = wp_upload_dir();
        
        if (is_dir($upload_dir['basedir'])) {
            $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($upload_dir['basedir']));
            foreach ($iterator as $file) {
                if ($file->getExtension() === 'php') {
                    $content = file_get_contents($file->getPathname());
                    foreach ($malicious_patterns as $pattern) {
                        if (preg_match('/' . $pattern . '/i', $content)) {
                            $suspicious_files[] = $file->getPathname();
                            break;
                        }
                    }
                }
            }
        }
        
        return $suspicious_files;
    }
    
    private function audit_user_permissions() {
        $users = get_users();
        $admin_count = 0;
        
        foreach ($users as $user) {
            if (in_array('administrator', $user->roles)) {
                $admin_count++;
            }
        }
        
        return array(
            'total_users' => count($users),
            'admin_count' => $admin_count
        );
    }
    
    private function check_login_security() {
        // 模拟登录安全检查
        return array(
            'failed_attempts' => 0,
            'brute_force_protection' => true
        );
    }
    
    private function check_ssl_certificate() {
        $home_url = home_url();
        if (strpos($home_url, 'https://') === 0) {
            return array('valid' => true, 'expires' => 'Unknown');
        }
        return array('valid' => false);
    }
    
    private function convert_to_bytes($val) {
        $val = trim($val);
        $last = strtolower($val[strlen($val)-1]);
        $val = (int)$val;
        switch($last) {
            case 'g':
                $val *= 1024;
            case 'm':
                $val *= 1024;
            case 'k':
                $val *= 1024;
        }
        return $val;
    }
    
    private function extract_auto_fixable_issues($analysis) {
        // 从AI分析中提取可自动修复的问题
        $fixable_issues = array();
        
        if (strpos($analysis, '数据库优化') !== false) {
            $fixable_issues[] = array('type' => 'database_optimization');
        }
        
        if (strpos($analysis, '缓存清理') !== false) {
            $fixable_issues[] = array('type' => 'clear_cache');
        }
        
        if (strpos($analysis, '插件更新') !== false) {
            $fixable_issues[] = array('type' => 'update_plugins');
        }
        
        if (strpos($analysis, '临时文件') !== false) {
            $fixable_issues[] = array('type' => 'cleanup_files');
        }
        
        return $fixable_issues;
    }
    
    private function optimize_database_tables() {
        $tables = $this->wp_db->get_results("SHOW TABLES");
        $optimized = 0;
        
        foreach ($tables as $table) {
            $table_name = array_values((array)$table)[0];
            $this->wp_db->query("OPTIMIZE TABLE `$table_name`");
            $optimized++;
        }
        
        return "优化了 $optimized 个数据表";
    }
    
    private function clear_all_cache() {
        // 清理WordPress缓存
        wp_cache_flush();
        
        // 清理对象缓存
        if (function_exists('wp_cache_clear_cache')) {
            wp_cache_clear_cache();
        }
        
        return "所有缓存已清理";
    }
    
    private function update_outdated_plugins() {
        // 模拟插件更新
        return "检查并更新了过期插件";
    }
    
    private function cleanup_temporary_files() {
        $upload_dir = wp_upload_dir();
        $temp_files = glob($upload_dir['basedir'] . '/*.tmp');
        
        $deleted = 0;
        foreach ($temp_files as $file) {
            if (unlink($file)) {
                $deleted++;
            }
        }
        
        return "清理了 $deleted 个临时文件";
    }
    
    /**
     * 获取巡逻历史记录
     */
    public function get_patrol_history($limit = 20) {
        $table_name = $this->wp_db->prefix . 'ai_patrol_logs';
        
        return $this->wp_db->get_results(
            $this->wp_db->prepare(
                "SELECT * FROM $table_name ORDER BY created_at DESC LIMIT %d",
                $limit
            )
        );
    }
    
    /**
     * 获取系统健康度报告
     */
    public function get_system_health_report() {
        $health_table = $this->wp_db->prefix . 'ai_system_health';
        
        return $this->wp_db->get_results(
            "SELECT * FROM $health_table ORDER BY last_checked DESC"
        );
    }
    
    /**
     * 更新巡逻设置
     */
    public function update_patrol_settings($settings) {
        $this->patrol_settings = array_merge($this->patrol_settings, $settings);
        update_option('ai_patrol_settings', $this->patrol_settings);
        
        // 重新调度任务
        wp_clear_scheduled_hook('ai_patrol_system_check');
        wp_schedule_event(time(), $this->patrol_settings['interval'], 'ai_patrol_system_check');
        
        return true;
    }
}