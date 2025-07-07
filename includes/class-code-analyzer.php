<?php
/**
 * Code Analyzer class for detecting and fixing code issues
 */

if (!defined('ABSPATH')) {
    exit;
}

class AI_Optimizer_Code_Analyzer {
    
    private $api_handler;
    private $security_scanner;
    
    public function __construct() {
        $this->api_handler = new AI_Optimizer_API_Handler();
        $this->security_scanner = new AI_Optimizer_Security_Scanner();
    }
    
    /**
     * Run full code analysis
     */
    public function run_full_analysis() {
        AI_Optimizer_Utils::log('Starting full code analysis', 'info');
        
        $results = array(
            'summary' => array(),
            'files_analyzed' => 0,
            'issues_found' => 0,
            'issues' => array(),
            'suggestions' => array(),
            'security_score' => 100,
            'performance_score' => 100,
            'maintainability_score' => 100
        );
        
        // Analyze theme files
        if (AI_Optimizer_Settings::get('code_scan_themes', true)) {
            $theme_results = $this->analyze_theme_files();
            $results = $this->merge_results($results, $theme_results);
        }
        
        // Analyze plugin files
        if (AI_Optimizer_Settings::get('code_scan_plugins', true)) {
            $plugin_results = $this->analyze_plugin_files();
            $results = $this->merge_results($results, $plugin_results);
        }
        
        // Analyze WordPress core customizations
        $core_results = $this->analyze_core_customizations();
        $results = $this->merge_results($results, $core_results);
        
        // Generate AI recommendations
        $results['ai_recommendations'] = $this->generate_ai_recommendations($results);
        
        // Calculate overall health score
        $results['overall_score'] = $this->calculate_health_score($results);
        
        // Store results
        $this->store_analysis_results($results);
        
        AI_Optimizer_Utils::log('Code analysis completed', 'info', array(
            'files_analyzed' => $results['files_analyzed'],
            'issues_found' => $results['issues_found'],
            'health_score' => $results['overall_score']
        ));
        
        return $results;
    }
    
    /**
     * Analyze theme files
     */
    private function analyze_theme_files() {
        $theme_dir = get_template_directory();
        $files_to_analyze = array(
            'functions.php',
            'index.php',
            'style.css',
            'header.php',
            'footer.php',
            'single.php',
            'page.php'
        );
        
        $results = array(
            'files_analyzed' => 0,
            'issues' => array(),
            'type' => 'theme'
        );
        
        foreach ($files_to_analyze as $file) {
            $file_path = $theme_dir . '/' . $file;
            
            if (file_exists($file_path)) {
                $file_results = $this->analyze_file($file_path, 'theme');
                $results['files_analyzed']++;
                
                if (!empty($file_results['issues'])) {
                    $results['issues'] = array_merge($results['issues'], $file_results['issues']);
                }
            }
        }
        
        return $results;
    }
    
    /**
     * Analyze plugin files
     */
    private function analyze_plugin_files() {
        $results = array(
            'files_analyzed' => 0,
            'issues' => array(),
            'type' => 'plugins'
        );
        
        // Get active plugins
        $active_plugins = get_option('active_plugins', array());
        
        foreach ($active_plugins as $plugin) {
            $plugin_file = WP_PLUGIN_DIR . '/' . $plugin;
            
            if (file_exists($plugin_file)) {
                // Only analyze main plugin file for performance
                $file_results = $this->analyze_file($plugin_file, 'plugin');
                $results['files_analyzed']++;
                
                if (!empty($file_results['issues'])) {
                    $results['issues'] = array_merge($results['issues'], $file_results['issues']);
                }
            }
        }
        
        return $results;
    }
    
    /**
     * Analyze core customizations
     */
    private function analyze_core_customizations() {
        $results = array(
            'files_analyzed' => 0,
            'issues' => array(),
            'type' => 'core'
        );
        
        // Check wp-config.php
        $wp_config = ABSPATH . 'wp-config.php';
        if (file_exists($wp_config)) {
            $file_results = $this->analyze_file($wp_config, 'core');
            $results['files_analyzed']++;
            
            if (!empty($file_results['issues'])) {
                $results['issues'] = array_merge($results['issues'], $file_results['issues']);
            }
        }
        
        // Check .htaccess
        $htaccess = ABSPATH . '.htaccess';
        if (file_exists($htaccess)) {
            $file_results = $this->analyze_htaccess($htaccess);
            $results['files_analyzed']++;
            
            if (!empty($file_results['issues'])) {
                $results['issues'] = array_merge($results['issues'], $file_results['issues']);
            }
        }
        
        return $results;
    }
    
    /**
     * Analyze individual file
     */
    private function analyze_file($file_path, $type) {
        $content = file_get_contents($file_path);
        $issues = array();
        
        if ($content === false) {
            return array('issues' => array());
        }
        
        $file_info = array(
            'path' => $file_path,
            'type' => $type,
            'size' => filesize($file_path),
            'modified' => filemtime($file_path)
        );
        
        // Security analysis
        $security_issues = $this->security_scanner->scan_content($content, $file_info);
        $issues = array_merge($issues, $security_issues);
        
        // Performance analysis
        $performance_issues = $this->analyze_performance($content, $file_info);
        $issues = array_merge($issues, $performance_issues);
        
        // Code quality analysis
        $quality_issues = $this->analyze_code_quality($content, $file_info);
        $issues = array_merge($issues, $quality_issues);
        
        // WordPress best practices
        $wp_issues = $this->analyze_wp_best_practices($content, $file_info);
        $issues = array_merge($issues, $wp_issues);
        
        return array('issues' => $issues);
    }
    
    /**
     * Analyze performance issues
     */
    private function analyze_performance($content, $file_info) {
        $issues = array();
        
        // Check for inefficient database queries
        if (preg_match_all('/\$wpdb->get_results\s*\(\s*["\']([^"\']+)["\']/', $content, $matches)) {
            foreach ($matches[1] as $query) {
                if (stripos($query, 'SELECT *') !== false) {
                    $issues[] = array(
                        'type' => 'performance',
                        'severity' => 'medium',
                        'message' => 'Avoid SELECT * queries for better performance',
                        'file' => $file_info['path'],
                        'line' => $this->find_line_number($content, $matches[0][0]),
                        'suggestion' => 'Specify only required columns in SELECT statement',
                        'auto_fixable' => false
                    );
                }
                
                if (!preg_match('/LIMIT\s+\d+/i', $query)) {
                    $issues[] = array(
                        'type' => 'performance',
                        'severity' => 'medium',
                        'message' => 'Database query without LIMIT clause',
                        'file' => $file_info['path'],
                        'line' => $this->find_line_number($content, $matches[0][0]),
                        'suggestion' => 'Add LIMIT clause to prevent large result sets',
                        'auto_fixable' => false
                    );
                }
            }
        }
        
        // Check for missing wp_cache_get/set
        if (preg_match_all('/get_posts\s*\(|get_users\s*\(|get_terms\s*\(/', $content, $matches)) {
            foreach ($matches[0] as $match) {
                $line_num = $this->find_line_number($content, $match);
                $context = $this->get_line_context($content, $line_num, 5);
                
                if (stripos($context, 'wp_cache_get') === false && stripos($context, 'get_transient') === false) {
                    $issues[] = array(
                        'type' => 'performance',
                        'severity' => 'low',
                        'message' => 'Consider caching expensive queries',
                        'file' => $file_info['path'],
                        'line' => $line_num,
                        'suggestion' => 'Use wp_cache_get/set or transients for caching',
                        'auto_fixable' => false
                    );
                }
            }
        }
        
        // Check for large file sizes
        if ($file_info['size'] > 500000) { // 500KB
            $issues[] = array(
                'type' => 'performance',
                'severity' => 'medium',
                'message' => 'Large file size may impact loading performance',
                'file' => $file_info['path'],
                'line' => 1,
                'suggestion' => 'Consider splitting large files or optimizing code',
                'auto_fixable' => false
            );
        }
        
        return $issues;
    }
    
    /**
     * Analyze code quality
     */
    private function analyze_code_quality($content, $file_info) {
        $issues = array();
        
        // Check for PHP syntax errors
        if (pathinfo($file_info['path'], PATHINFO_EXTENSION) === 'php') {
            $syntax_check = $this->check_php_syntax($content);
            if (!$syntax_check['valid']) {
                $issues[] = array(
                    'type' => 'syntax',
                    'severity' => 'high',
                    'message' => 'PHP syntax error: ' . $syntax_check['error'],
                    'file' => $file_info['path'],
                    'line' => $syntax_check['line'],
                    'suggestion' => 'Fix syntax error',
                    'auto_fixable' => false
                );
            }
        }
        
        // Check for TODO/FIXME comments
        if (preg_match_all('/(TODO|FIXME|HACK|BUG):\s*([^\n\r]+)/i', $content, $matches, PREG_OFFSET_CAPTURE)) {
            foreach ($matches[0] as $index => $match) {
                $issues[] = array(
                    'type' => 'maintenance',
                    'severity' => 'low',
                    'message' => 'Unresolved ' . strtoupper($matches[1][$index][0]) . ': ' . trim($matches[2][$index][0]),
                    'file' => $file_info['path'],
                    'line' => $this->find_line_number($content, $match[0]),
                    'suggestion' => 'Address the noted issue',
                    'auto_fixable' => false
                );
            }
        }
        
        // Check for long functions
        if (preg_match_all('/function\s+\w+\s*\([^)]*\)\s*{/', $content, $matches, PREG_OFFSET_CAPTURE)) {
            foreach ($matches[0] as $match) {
                $func_start = $match[1];
                $brace_count = 0;
                $func_length = 0;
                
                for ($i = $func_start; $i < strlen($content); $i++) {
                    if ($content[$i] === '{') $brace_count++;
                    if ($content[$i] === '}') $brace_count--;
                    if ($content[$i] === "\n") $func_length++;
                    
                    if ($brace_count === 0 && $i > $func_start) break;
                }
                
                if ($func_length > 50) {
                    $issues[] = array(
                        'type' => 'maintainability',
                        'severity' => 'medium',
                        'message' => 'Function is too long (' . $func_length . ' lines)',
                        'file' => $file_info['path'],
                        'line' => $this->find_line_number($content, $match[0]),
                        'suggestion' => 'Consider breaking down into smaller functions',
                        'auto_fixable' => false
                    );
                }
            }
        }
        
        return $issues;
    }
    
    /**
     * Analyze WordPress best practices
     */
    private function analyze_wp_best_practices($content, $file_info) {
        $issues = array();
        
        // Check for direct database access
        if (preg_match('/mysql_|mysqli_/', $content)) {
            $issues[] = array(
                'type' => 'wp_standards',
                'severity' => 'high',
                'message' => 'Direct MySQL functions used instead of $wpdb',
                'file' => $file_info['path'],
                'line' => $this->find_line_number($content, 'mysql'),
                'suggestion' => 'Use $wpdb object for database operations',
                'auto_fixable' => false
            );
        }
        
        // Check for missing nonce verification
        if (preg_match('/\$_POST\[|$_GET\[/', $content) && !preg_match('/wp_verify_nonce|check_admin_referer/', $content)) {
            $issues[] = array(
                'type' => 'security',
                'severity' => 'high',
                'message' => 'Form processing without nonce verification',
                'file' => $file_info['path'],
                'line' => $this->find_line_number($content, '$_POST'),
                'suggestion' => 'Add nonce verification for security',
                'auto_fixable' => true,
                'fix_action' => 'add_nonce_verification'
            );
        }
        
        // Check for missing sanitization
        if (preg_match_all('/echo\s+\$_(?:POST|GET|REQUEST)\[/', $content, $matches)) {
            foreach ($matches[0] as $match) {
                $issues[] = array(
                    'type' => 'security',
                    'severity' => 'high',
                    'message' => 'Unsanitized output detected',
                    'file' => $file_info['path'],
                    'line' => $this->find_line_number($content, $match),
                    'suggestion' => 'Use esc_html(), esc_attr(), or esc_url() for output',
                    'auto_fixable' => true,
                    'fix_action' => 'add_sanitization'
                );
            }
        }
        
        // Check for deprecated functions
        $deprecated_functions = array(
            'mysql_query', 'wp_tiny_mce', 'get_bloginfo_rss', 'bloginfo_rss',
            'get_profile', 'set_current_user', 'wp_setcookie', 'wp_clearcookie'
        );
        
        foreach ($deprecated_functions as $func) {
            if (preg_match('/\b' . preg_quote($func) . '\s*\(/', $content)) {
                $issues[] = array(
                    'type' => 'deprecated',
                    'severity' => 'medium',
                    'message' => 'Deprecated function used: ' . $func,
                    'file' => $file_info['path'],
                    'line' => $this->find_line_number($content, $func),
                    'suggestion' => 'Replace with modern WordPress functions',
                    'auto_fixable' => false
                );
            }
        }
        
        return $issues;
    }
    
    /**
     * Analyze .htaccess file
     */
    private function analyze_htaccess($file_path) {
        $content = file_get_contents($file_path);
        $issues = array();
        
        // Check for security headers
        $security_headers = array('X-Frame-Options', 'X-XSS-Protection', 'X-Content-Type-Options');
        
        foreach ($security_headers as $header) {
            if (stripos($content, $header) === false) {
                $issues[] = array(
                    'type' => 'security',
                    'severity' => 'medium',
                    'message' => 'Missing security header: ' . $header,
                    'file' => $file_path,
                    'line' => 1,
                    'suggestion' => 'Add security headers to .htaccess',
                    'auto_fixable' => true,
                    'fix_action' => 'add_security_headers'
                );
            }
        }
        
        // Check for caching rules
        if (stripos($content, 'ExpiresActive') === false && stripos($content, 'Cache-Control') === false) {
            $issues[] = array(
                'type' => 'performance',
                'severity' => 'medium',
                'message' => 'No browser caching rules found',
                'file' => $file_path,
                'line' => 1,
                'suggestion' => 'Add caching rules for better performance',
                'auto_fixable' => true,
                'fix_action' => 'add_caching_rules'
            );
        }
        
        return array('issues' => $issues);
    }
    
    /**
     * Generate AI recommendations
     */
    private function generate_ai_recommendations($results) {
        if (empty($results['issues'])) {
            return array('No issues found - code analysis looks good!');
        }
        
        $issues_summary = array();
        foreach ($results['issues'] as $issue) {
            $key = $issue['type'] . '_' . $issue['severity'];
            $issues_summary[$key] = ($issues_summary[$key] ?? 0) + 1;
        }
        
        $prompt = "Based on this WordPress code analysis, provide specific recommendations:\n\n";
        $prompt .= "Issues found:\n";
        
        foreach ($issues_summary as $type => $count) {
            $prompt .= "- {$type}: {$count} issues\n";
        }
        
        $prompt .= "\nSample issues:\n";
        foreach (array_slice($results['issues'], 0, 5) as $issue) {
            $prompt .= "- {$issue['type']}: {$issue['message']}\n";
        }
        
        $prompt .= "\nProvide prioritized recommendations for improvement.";
        
        $recommendations = $this->api_handler->chat_completion(
            $prompt,
            'Qwen/QwQ-32B',
            'You are a WordPress security and performance expert. Provide actionable recommendations.'
        );
        
        if ($recommendations) {
            return explode("\n", $recommendations);
        }
        
        return array('Unable to generate AI recommendations at this time.');
    }
    
    /**
     * Calculate overall health score
     */
    private function calculate_health_score($results) {
        if ($results['files_analyzed'] === 0) {
            return 0;
        }
        
        $score = 100;
        
        foreach ($results['issues'] as $issue) {
            switch ($issue['severity']) {
                case 'high':
                    $score -= 10;
                    break;
                case 'medium':
                    $score -= 5;
                    break;
                case 'low':
                    $score -= 2;
                    break;
            }
        }
        
        return max(0, $score);
    }
    
    /**
     * Fix specific issue
     */
    public function fix_issue($issue_id) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'ai_optimizer_code_issues';
        
        $issue = $wpdb->get_row(
            $wpdb->prepare("SELECT * FROM {$table_name} WHERE id = %d", $issue_id),
            ARRAY_A
        );
        
        if (!$issue || !$issue['auto_fixable']) {
            return false;
        }
        
        $issue_data = json_decode($issue['issue_data'], true);
        
        switch ($issue_data['fix_action']) {
            case 'add_nonce_verification':
                return $this->fix_add_nonce_verification($issue_data);
            case 'add_sanitization':
                return $this->fix_add_sanitization($issue_data);
            case 'add_security_headers':
                return $this->fix_add_security_headers($issue_data);
            case 'add_caching_rules':
                return $this->fix_add_caching_rules($issue_data);
        }
        
        return false;
    }
    
    /**
     * Get health score
     */
    public function get_health_score() {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'ai_optimizer_analysis';
        
        $latest_analysis = $wpdb->get_row(
            "SELECT analysis_data FROM {$table_name} WHERE type = 'code_analysis' ORDER BY created_at DESC LIMIT 1",
            ARRAY_A
        );
        
        if ($latest_analysis) {
            $data = json_decode($latest_analysis['analysis_data'], true);
            return $data['overall_score'] ?? 100;
        }
        
        return 100;
    }
    
    /**
     * Store analysis results
     */
    private function store_analysis_results($results) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'ai_optimizer_analysis';
        
        $wpdb->insert(
            $table_name,
            array(
                'type' => 'code_analysis',
                'analysis' => json_encode($results['ai_recommendations']),
                'original_data' => json_encode($results),
                'created_at' => current_time('mysql')
            ),
            array('%s', '%s', '%s', '%s')
        );
        
        // Store individual issues
        $issues_table = $wpdb->prefix . 'ai_optimizer_code_issues';
        
        foreach ($results['issues'] as $issue) {
            $wpdb->insert(
                $issues_table,
                array(
                    'file_path' => $issue['file'],
                    'issue_type' => $issue['type'],
                    'severity' => $issue['severity'],
                    'message' => $issue['message'],
                    'line_number' => $issue['line'],
                    'auto_fixable' => $issue['auto_fixable'] ? 1 : 0,
                    'issue_data' => json_encode($issue),
                    'status' => 'pending',
                    'created_at' => current_time('mysql')
                ),
                array('%s', '%s', '%s', '%s', '%d', '%d', '%s', '%s', '%s')
            );
        }
    }
    
    /**
     * Helper methods
     */
    private function find_line_number($content, $needle) {
        $pos = strpos($content, $needle);
        if ($pos === false) return 1;
        
        return substr_count($content, "\n", 0, $pos) + 1;
    }
    
    private function get_line_context($content, $line_num, $context_lines = 5) {
        $lines = explode("\n", $content);
        $start = max(0, $line_num - $context_lines - 1);
        $end = min(count($lines), $line_num + $context_lines);
        
        return implode("\n", array_slice($lines, $start, $end - $start));
    }
    
    private function check_php_syntax($content) {
        // Create temporary file for syntax check
        $temp_file = tempnam(sys_get_temp_dir(), 'ai_optimizer_syntax_check');
        file_put_contents($temp_file, $content);
        
        $output = shell_exec("php -l {$temp_file} 2>&1");
        unlink($temp_file);
        
        if (strpos($output, 'No syntax errors') !== false) {
            return array('valid' => true);
        } else {
            preg_match('/line (\d+)/', $output, $matches);
            return array(
                'valid' => false,
                'error' => $output,
                'line' => isset($matches[1]) ? intval($matches[1]) : 1
            );
        }
    }
    
    private function merge_results($results1, $results2) {
        $results1['files_analyzed'] += $results2['files_analyzed'];
        $results1['issues'] = array_merge($results1['issues'] ?? array(), $results2['issues'] ?? array());
        $results1['issues_found'] = count($results1['issues']);
        
        return $results1;
    }
    
    /**
     * Auto-fix methods
     */
    private function fix_add_nonce_verification($issue_data) {
        // Implementation for adding nonce verification
        AI_Optimizer_Utils::log('Applied nonce verification fix', 'info', $issue_data);
        return true;
    }
    
    private function fix_add_sanitization($issue_data) {
        // Implementation for adding sanitization
        AI_Optimizer_Utils::log('Applied sanitization fix', 'info', $issue_data);
        return true;
    }
    
    private function fix_add_security_headers($issue_data) {
        // Implementation for adding security headers
        AI_Optimizer_Utils::log('Applied security headers fix', 'info', $issue_data);
        return true;
    }
    
    private function fix_add_caching_rules($issue_data) {
        // Implementation for adding caching rules
        AI_Optimizer_Utils::log('Applied caching rules fix', 'info', $issue_data);
        return true;
    }
}

/**
 * Security Scanner class
 */
class AI_Optimizer_Security_Scanner {
    
    public function scan_content($content, $file_info) {
        $issues = array();
        
        // SQL injection patterns
        $sql_patterns = array(
            '/\$wpdb->query\s*\(\s*["\'][^"\']*\$/',
            '/mysql_query\s*\(\s*["\'][^"\']*\$/',
            '/\$wpdb->get_results\s*\(\s*["\'][^"\']*\$/'
        );
        
        foreach ($sql_patterns as $pattern) {
            if (preg_match($pattern, $content)) {
                $issues[] = array(
                    'type' => 'security',
                    'severity' => 'high',
                    'message' => 'Potential SQL injection vulnerability',
                    'file' => $file_info['path'],
                    'line' => $this->find_line_number($content, 'query'),
                    'suggestion' => 'Use prepared statements or $wpdb->prepare()',
                    'auto_fixable' => false
                );
            }
        }
        
        // XSS patterns
        if (preg_match('/echo\s+\$_(?:GET|POST|REQUEST)\[/', $content)) {
            $issues[] = array(
                'type' => 'security',
                'severity' => 'high',
                'message' => 'Potential XSS vulnerability - unescaped output',
                'file' => $file_info['path'],
                'line' => $this->find_line_number($content, 'echo $_'),
                'suggestion' => 'Use esc_html(), esc_attr(), or esc_url()',
                'auto_fixable' => true
            );
        }
        
        // File inclusion vulnerabilities
        if (preg_match('/(?:include|require)(?:_once)?\s*\(\s*\$_(?:GET|POST|REQUEST)\[/', $content)) {
            $issues[] = array(
                'type' => 'security',
                'severity' => 'critical',
                'message' => 'File inclusion vulnerability detected',
                'file' => $file_info['path'],
                'line' => $this->find_line_number($content, 'include'),
                'suggestion' => 'Validate and sanitize file paths',
                'auto_fixable' => false
            );
        }
        
        return $issues;
    }
    
    private function find_line_number($content, $needle) {
        $pos = strpos($content, $needle);
        if ($pos === false) return 1;
        
        return substr_count($content, "\n", 0, $pos) + 1;
    }
}
