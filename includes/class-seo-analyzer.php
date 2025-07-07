<?php
/**
 * SEO智能分析器类
 * 
 * 深度结合AI分析网站整体SEO，实时搜索SEO知识并提供自动化优化建议
 */

if (!defined('ABSPATH')) {
    exit;
}

class AI_SEO_Analyzer {
    
    private $api_key;
    private $api_base_url = 'https://api.siliconflow.cn/v1/chat/completions';
    private $search_api_url = 'https://api.siliconflow.cn/v1/web/search';
    private $wp_db;
    
    public function __construct() {
        global $wpdb;
        $this->wp_db = $wpdb;
        $this->api_key = get_option('ai_opt_api_key');
        
        // 初始化数据库表
        $this->create_seo_tables();
    }
    
    /**
     * 创建SEO分析数据表
     */
    private function create_seo_tables() {
        $charset_collate = $this->wp_db->get_charset_collate();
        
        // SEO分析结果表
        $table_name = $this->wp_db->prefix . 'ai_seo_analysis';
        $sql = "CREATE TABLE $table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            url varchar(255) NOT NULL,
            analysis_type varchar(50) NOT NULL,
            score int(3) NOT NULL,
            issues text,
            suggestions text,
            ai_model varchar(100) NOT NULL,
            analyzed_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
        
        // SEO关键词表
        $keywords_table = $this->wp_db->prefix . 'ai_seo_keywords';
        $sql2 = "CREATE TABLE $keywords_table (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            keyword varchar(255) NOT NULL,
            density float NOT NULL,
            position int NOT NULL,
            competition_score int NOT NULL,
            search_volume int NOT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id)
        ) $charset_collate;";
        
        dbDelta($sql2);
    }
    
    /**
     * 执行完整的SEO分析
     */
    public function analyze_website_seo($selected_ai_model = 'Qwen/QwQ-32B-Preview') {
        $results = array();
        
        // 1. 获取网站基本信息
        $site_info = $this->get_site_info();
        
        // 2. 分析页面结构
        $structure_analysis = $this->analyze_page_structure();
        
        // 3. 检查技术SEO
        $technical_seo = $this->analyze_technical_seo();
        
        // 4. 内容分析
        $content_analysis = $this->analyze_content_quality();
        
        // 5. 竞争对手分析
        $competitor_analysis = $this->analyze_competitors();
        
        // 6. 实时获取SEO知识
        $seo_knowledge = $this->fetch_latest_seo_knowledge();
        
        // 7. 使用AI分析并生成建议
        $ai_suggestions = $this->generate_ai_suggestions($site_info, $structure_analysis, $technical_seo, $content_analysis, $competitor_analysis, $seo_knowledge, $selected_ai_model);
        
        // 8. 保存分析结果
        $this->save_analysis_results($ai_suggestions, $selected_ai_model);
        
        return $ai_suggestions;
    }
    
    /**
     * 获取网站基本信息
     */
    private function get_site_info() {
        $home_url = home_url();
        $site_title = get_bloginfo('name');
        $site_description = get_bloginfo('description');
        
        // 获取页面加载时间
        $load_time = $this->measure_page_load_time($home_url);
        
        // 获取页面HTML
        $page_html = $this->fetch_page_html($home_url);
        
        return array(
            'url' => $home_url,
            'title' => $site_title,
            'description' => $site_description,
            'load_time' => $load_time,
            'html_content' => $page_html
        );
    }
    
    /**
     * 分析页面结构
     */
    private function analyze_page_structure() {
        $home_url = home_url();
        $html = $this->fetch_page_html($home_url);
        
        $analysis = array();
        
        // 检查HTML结构
        if (preg_match('/<title>(.*?)<\/title>/i', $html, $matches)) {
            $analysis['title'] = trim($matches[1]);
            $analysis['title_length'] = strlen($analysis['title']);
        }
        
        // 检查Meta描述
        if (preg_match('/<meta\s+name=["\']description["\']\s+content=["\']([^"\']*)["\'][^>]*>/i', $html, $matches)) {
            $analysis['meta_description'] = trim($matches[1]);
            $analysis['meta_description_length'] = strlen($analysis['meta_description']);
        }
        
        // 检查H1标签
        preg_match_all('/<h1[^>]*>(.*?)<\/h1>/i', $html, $h1_matches);
        $analysis['h1_count'] = count($h1_matches[1]);
        $analysis['h1_tags'] = $h1_matches[1];
        
        // 检查H2-H6标签
        for ($i = 2; $i <= 6; $i++) {
            preg_match_all("/<h{$i}[^>]*>(.*?)<\/h{$i}>/i", $html, $h_matches);
            $analysis["h{$i}_count"] = count($h_matches[1]);
        }
        
        // 检查图片alt属性
        preg_match_all('/<img[^>]*>/i', $html, $img_matches);
        $images_without_alt = 0;
        foreach ($img_matches[0] as $img) {
            if (!preg_match('/alt\s*=\s*["\'][^"\']*["\']/', $img)) {
                $images_without_alt++;
            }
        }
        $analysis['images_without_alt'] = $images_without_alt;
        $analysis['total_images'] = count($img_matches[0]);
        
        return $analysis;
    }
    
    /**
     * 分析技术SEO
     */
    private function analyze_technical_seo() {
        $home_url = home_url();
        $analysis = array();
        
        // 检查robots.txt
        $robots_url = $home_url . '/robots.txt';
        $robots_response = wp_remote_get($robots_url);
        $analysis['robots_txt_exists'] = !is_wp_error($robots_response) && wp_remote_retrieve_response_code($robots_response) === 200;
        
        // 检查sitemap.xml
        $sitemap_url = $home_url . '/sitemap.xml';
        $sitemap_response = wp_remote_get($sitemap_url);
        $analysis['sitemap_exists'] = !is_wp_error($sitemap_response) && wp_remote_retrieve_response_code($sitemap_response) === 200;
        
        // 检查SSL
        $analysis['has_ssl'] = strpos($home_url, 'https://') === 0;
        
        // 检查移动友好性
        $analysis['mobile_friendly'] = $this->check_mobile_friendly();
        
        // 检查页面速度
        $analysis['page_speed'] = $this->measure_page_load_time($home_url);
        
        // 检查Schema标记
        $html = $this->fetch_page_html($home_url);
        $analysis['has_schema'] = strpos($html, 'application/ld+json') !== false || strpos($html, 'schema.org') !== false;
        
        return $analysis;
    }
    
    /**
     * 分析内容质量
     */
    private function analyze_content_quality() {
        $analysis = array();
        
        // 获取最新文章
        $recent_posts = get_posts(array(
            'numberposts' => 10,
            'post_status' => 'publish'
        ));
        
        $total_word_count = 0;
        $keyword_density = array();
        
        foreach ($recent_posts as $post) {
            $content = strip_tags($post->post_content);
            $word_count = str_word_count($content);
            $total_word_count += $word_count;
            
            // 简单的关键词密度分析
            $words = str_word_count($content, 1);
            foreach ($words as $word) {
                $word = strtolower($word);
                if (strlen($word) > 3) {
                    $keyword_density[$word] = ($keyword_density[$word] ?? 0) + 1;
                }
            }
        }
        
        arsort($keyword_density);
        $analysis['average_word_count'] = count($recent_posts) > 0 ? $total_word_count / count($recent_posts) : 0;
        $analysis['top_keywords'] = array_slice($keyword_density, 0, 10, true);
        $analysis['total_posts'] = count($recent_posts);
        
        return $analysis;
    }
    
    /**
     * 分析竞争对手
     */
    private function analyze_competitors() {
        // 这里可以集成第三方API来获取竞争对手数据
        // 目前返回基本结构
        return array(
            'competitors_found' => 0,
            'analysis' => '竞争对手分析功能需要配置外部API'
        );
    }
    
    /**
     * 实时获取SEO知识
     */
    private function fetch_latest_seo_knowledge() {
        if (!$this->api_key) {
            return array('error' => 'API密钥未配置');
        }
        
        $search_queries = array(
            '2024年最新SEO优化技巧',
            '百度搜索引擎优化指南',
            '网站收录提升方法',
            '页面排名优化策略'
        );
        
        $knowledge = array();
        
        foreach ($search_queries as $query) {
            $response = wp_remote_post($this->search_api_url, array(
                'headers' => array(
                    'Authorization' => 'Bearer ' . $this->api_key,
                    'Content-Type' => 'application/json'
                ),
                'body' => json_encode(array(
                    'query' => $query,
                    'max_results' => 5
                )),
                'timeout' => 30
            ));
            
            if (!is_wp_error($response)) {
                $body = wp_remote_retrieve_body($response);
                $data = json_decode($body, true);
                if (isset($data['results'])) {
                    $knowledge[$query] = $data['results'];
                }
            }
        }
        
        return $knowledge;
    }
    
    /**
     * 使用AI生成优化建议
     */
    private function generate_ai_suggestions($site_info, $structure_analysis, $technical_seo, $content_analysis, $competitor_analysis, $seo_knowledge, $ai_model) {
        if (!$this->api_key) {
            return array('error' => 'API密钥未配置');
        }
        
        $prompt = "作为SEO专家，请深度分析以下网站数据并提供详细的优化建议：\n\n";
        $prompt .= "网站信息：\n" . json_encode($site_info, JSON_UNESCAPED_UNICODE) . "\n\n";
        $prompt .= "页面结构分析：\n" . json_encode($structure_analysis, JSON_UNESCAPED_UNICODE) . "\n\n";
        $prompt .= "技术SEO分析：\n" . json_encode($technical_seo, JSON_UNESCAPED_UNICODE) . "\n\n";
        $prompt .= "内容分析：\n" . json_encode($content_analysis, JSON_UNESCAPED_UNICODE) . "\n\n";
        $prompt .= "最新SEO知识：\n" . json_encode($seo_knowledge, JSON_UNESCAPED_UNICODE) . "\n\n";
        $prompt .= "请提供以下方面的详细建议：\n";
        $prompt .= "1. 技术SEO优化建议\n";
        $prompt .= "2. 内容优化建议\n";
        $prompt .= "3. 页面结构优化建议\n";
        $prompt .= "4. 关键词优化建议\n";
        $prompt .= "5. 具体的实施步骤\n";
        $prompt .= "6. 预期效果评估\n";
        
        $response = wp_remote_post($this->api_base_url, array(
            'headers' => array(
                'Authorization' => 'Bearer ' . $this->api_key,
                'Content-Type' => 'application/json'
            ),
            'body' => json_encode(array(
                'model' => $ai_model,
                'messages' => array(
                    array(
                        'role' => 'user',
                        'content' => $prompt
                    )
                ),
                'max_tokens' => 4000,
                'temperature' => 0.7
            )),
            'timeout' => 120
        ));
        
        if (is_wp_error($response)) {
            return array('error' => 'AI请求失败: ' . $response->get_error_message());
        }
        
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);
        
        if (isset($data['choices'][0]['message']['content'])) {
            return array(
                'suggestions' => $data['choices'][0]['message']['content'],
                'model_used' => $ai_model,
                'analysis_data' => array(
                    'site_info' => $site_info,
                    'structure' => $structure_analysis,
                    'technical' => $technical_seo,
                    'content' => $content_analysis
                )
            );
        }
        
        return array('error' => 'AI分析失败');
    }
    
    /**
     * 保存分析结果
     */
    private function save_analysis_results($results, $ai_model) {
        if (isset($results['error'])) {
            return false;
        }
        
        $table_name = $this->wp_db->prefix . 'ai_seo_analysis';
        
        return $this->wp_db->insert(
            $table_name,
            array(
                'url' => home_url(),
                'analysis_type' => 'full_analysis',
                'score' => $this->calculate_seo_score($results['analysis_data']),
                'issues' => json_encode($this->extract_issues($results['analysis_data'])),
                'suggestions' => $results['suggestions'],
                'ai_model' => $ai_model,
                'analyzed_at' => current_time('mysql')
            ),
            array('%s', '%s', '%d', '%s', '%s', '%s', '%s')
        );
    }
    
    /**
     * 计算SEO分数
     */
    private function calculate_seo_score($analysis_data) {
        $score = 100;
        
        // 技术SEO检查
        if (!$analysis_data['technical']['has_ssl']) $score -= 10;
        if (!$analysis_data['technical']['sitemap_exists']) $score -= 10;
        if (!$analysis_data['technical']['robots_txt_exists']) $score -= 5;
        if (!$analysis_data['technical']['mobile_friendly']) $score -= 15;
        if ($analysis_data['technical']['page_speed'] > 3) $score -= 10;
        
        // 页面结构检查
        if ($analysis_data['structure']['h1_count'] != 1) $score -= 5;
        if ($analysis_data['structure']['title_length'] < 30 || $analysis_data['structure']['title_length'] > 60) $score -= 5;
        if (!isset($analysis_data['structure']['meta_description']) || $analysis_data['structure']['meta_description_length'] < 120) $score -= 5;
        if ($analysis_data['structure']['images_without_alt'] > 0) $score -= 5;
        
        // 内容质量检查
        if ($analysis_data['content']['average_word_count'] < 300) $score -= 10;
        
        return max(0, min(100, $score));
    }
    
    /**
     * 提取问题
     */
    private function extract_issues($analysis_data) {
        $issues = array();
        
        if (!$analysis_data['technical']['has_ssl']) $issues[] = 'SSL证书未配置';
        if (!$analysis_data['technical']['sitemap_exists']) $issues[] = 'sitemap.xml不存在';
        if (!$analysis_data['technical']['robots_txt_exists']) $issues[] = 'robots.txt不存在';
        if (!$analysis_data['technical']['mobile_friendly']) $issues[] = '移动端不友好';
        if ($analysis_data['technical']['page_speed'] > 3) $issues[] = '页面加载速度过慢';
        
        if ($analysis_data['structure']['h1_count'] != 1) $issues[] = 'H1标签数量不正确';
        if ($analysis_data['structure']['title_length'] < 30 || $analysis_data['structure']['title_length'] > 60) $issues[] = '标题长度不合适';
        if (!isset($analysis_data['structure']['meta_description']) || $analysis_data['structure']['meta_description_length'] < 120) $issues[] = 'Meta描述缺失或过短';
        if ($analysis_data['structure']['images_without_alt'] > 0) $issues[] = '图片缺少alt属性';
        
        if ($analysis_data['content']['average_word_count'] < 300) $issues[] = '文章平均字数过少';
        
        return $issues;
    }
    
    /**
     * 辅助函数：测量页面加载时间
     */
    private function measure_page_load_time($url) {
        $start_time = microtime(true);
        $response = wp_remote_get($url, array('timeout' => 10));
        $end_time = microtime(true);
        
        if (is_wp_error($response)) {
            return 0;
        }
        
        return round($end_time - $start_time, 2);
    }
    
    /**
     * 辅助函数：获取页面HTML
     */
    private function fetch_page_html($url) {
        $response = wp_remote_get($url, array('timeout' => 15));
        
        if (is_wp_error($response)) {
            return '';
        }
        
        return wp_remote_retrieve_body($response);
    }
    
    /**
     * 辅助函数：检查移动友好性
     */
    private function check_mobile_friendly() {
        $home_url = home_url();
        $html = $this->fetch_page_html($home_url);
        
        // 检查viewport meta标签
        return preg_match('/<meta[^>]*name=["\']viewport["\'][^>]*>/i', $html);
    }
    
    /**
     * 执行自动优化
     */
    public function execute_auto_optimization($optimization_settings) {
        $results = array();
        
        if ($optimization_settings['auto_optimize_images']) {
            $results['images'] = $this->optimize_images();
        }
        
        if ($optimization_settings['auto_generate_sitemap']) {
            $results['sitemap'] = $this->generate_sitemap();
        }
        
        if ($optimization_settings['auto_optimize_database']) {
            $results['database'] = $this->optimize_database();
        }
        
        return $results;
    }
    
    /**
     * 优化图片
     */
    private function optimize_images() {
        // 查找没有alt属性的图片并添加
        $posts = get_posts(array(
            'numberposts' => -1,
            'post_type' => 'any',
            'post_status' => 'publish'
        ));
        
        $updated_count = 0;
        
        foreach ($posts as $post) {
            $content = $post->post_content;
            $updated_content = preg_replace_callback(
                '/<img([^>]*?)src=["\']([^"\']*)["\']([^>]*?)>/i',
                function($matches) {
                    if (strpos($matches[0], 'alt=') === false) {
                        $filename = basename($matches[2]);
                        $alt_text = pathinfo($filename, PATHINFO_FILENAME);
                        $alt_text = str_replace(array('-', '_'), ' ', $alt_text);
                        return '<img' . $matches[1] . 'src="' . $matches[2] . '"' . $matches[3] . ' alt="' . $alt_text . '">';
                    }
                    return $matches[0];
                },
                $content
            );
            
            if ($updated_content !== $content) {
                wp_update_post(array(
                    'ID' => $post->ID,
                    'post_content' => $updated_content
                ));
                $updated_count++;
            }
        }
        
        return "优化了 {$updated_count} 篇文章的图片";
    }
    
    /**
     * 生成sitemap
     */
    private function generate_sitemap() {
        // 基本的sitemap生成
        $sitemap_content = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $sitemap_content .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
        
        // 添加首页
        $sitemap_content .= '<url>' . "\n";
        $sitemap_content .= '<loc>' . home_url() . '</loc>' . "\n";
        $sitemap_content .= '<lastmod>' . date('Y-m-d') . '</lastmod>' . "\n";
        $sitemap_content .= '<changefreq>daily</changefreq>' . "\n";
        $sitemap_content .= '<priority>1.0</priority>' . "\n";
        $sitemap_content .= '</url>' . "\n";
        
        // 添加文章
        $posts = get_posts(array(
            'numberposts' => -1,
            'post_status' => 'publish'
        ));
        
        foreach ($posts as $post) {
            $sitemap_content .= '<url>' . "\n";
            $sitemap_content .= '<loc>' . get_permalink($post->ID) . '</loc>' . "\n";
            $sitemap_content .= '<lastmod>' . date('Y-m-d', strtotime($post->post_modified)) . '</lastmod>' . "\n";
            $sitemap_content .= '<changefreq>weekly</changefreq>' . "\n";
            $sitemap_content .= '<priority>0.8</priority>' . "\n";
            $sitemap_content .= '</url>' . "\n";
        }
        
        $sitemap_content .= '</urlset>';
        
        // 保存sitemap文件
        $sitemap_file = ABSPATH . 'sitemap.xml';
        if (file_put_contents($sitemap_file, $sitemap_content)) {
            return 'Sitemap生成成功';
        } else {
            return 'Sitemap生成失败';
        }
    }
    
    /**
     * 优化数据库
     */
    private function optimize_database() {
        global $wpdb;
        
        // 清理垃圾数据
        $wpdb->query("DELETE FROM {$wpdb->comments} WHERE comment_approved = 'spam'");
        $wpdb->query("DELETE FROM {$wpdb->postmeta} WHERE meta_key = '_edit_lock'");
        $wpdb->query("DELETE FROM {$wpdb->postmeta} WHERE meta_key = '_edit_last'");
        $wpdb->query("OPTIMIZE TABLE {$wpdb->posts}");
        $wpdb->query("OPTIMIZE TABLE {$wpdb->postmeta}");
        $wpdb->query("OPTIMIZE TABLE {$wpdb->comments}");
        
        return '数据库优化完成';
    }
    
    /**
     * 获取历史分析结果
     */
    public function get_analysis_history($limit = 10) {
        $table_name = $this->wp_db->prefix . 'ai_seo_analysis';
        
        return $this->wp_db->get_results(
            $this->wp_db->prepare(
                "SELECT * FROM $table_name ORDER BY analyzed_at DESC LIMIT %d",
                $limit
            )
        );
    }
}