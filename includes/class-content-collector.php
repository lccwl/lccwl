<?php
/**
 * 内容收集器 - 从RSS源和网站收集内容并使用AI重写
 */

if (!defined('ABSPATH')) {
    exit;
}

class AI_Optimizer_Content_Collector {
    
    private static $instance = null;
    private $api_handler;
    
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        $this->api_handler = AI_Optimizer_API_Handler::get_instance();
        $this->init();
    }
    
    private function init() {
        // 注册定时任务
        add_action('wp', array($this, 'setup_cron_jobs'));
        add_action('ai_optimizer_collect_content', array($this, 'run_content_collection'));
        
        // AJAX处理
        add_action('wp_ajax_ai_optimizer_add_source', array($this, 'handle_add_source'));
        add_action('wp_ajax_ai_optimizer_remove_source', array($this, 'handle_remove_source'));
        add_action('wp_ajax_ai_optimizer_test_source', array($this, 'handle_test_source'));
        add_action('wp_ajax_ai_optimizer_manual_collection', array($this, 'handle_manual_collection'));
        add_action('wp_ajax_ai_optimizer_publish_content', array($this, 'handle_publish_content'));
        
        // 数据库表初始化
        $this->create_tables();
    }
    
    /**
     * 创建数据库表
     */
    private function create_tables() {
        global $wpdb;
        
        $charset_collate = $wpdb->get_charset_collate();
        
        // 内容源表
        $sources_table = $wpdb->prefix . 'ai_optimizer_content_sources';
        $sql_sources = "CREATE TABLE IF NOT EXISTS $sources_table (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            name varchar(255) NOT NULL,
            type enum('rss', 'website', 'api') NOT NULL DEFAULT 'rss',
            url text NOT NULL,
            status enum('active', 'inactive', 'error') NOT NULL DEFAULT 'active',
            settings longtext,
            last_collected datetime,
            total_collected int(11) NOT NULL DEFAULT 0,
            success_rate float NOT NULL DEFAULT 100,
            created_at datetime NOT NULL,
            updated_at datetime NOT NULL,
            PRIMARY KEY (id),
            KEY type (type),
            KEY status (status)
        ) $charset_collate;";
        
        // 收集到的内容表
        $content_table = $wpdb->prefix . 'ai_optimizer_collected_content';
        $sql_content = "CREATE TABLE IF NOT EXISTS $content_table (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            source_id bigint(20) unsigned NOT NULL,
            original_title text NOT NULL,
            original_content longtext NOT NULL,
            original_url text,
            rewritten_title text,
            rewritten_content longtext,
            rewrite_status enum('pending', 'processing', 'completed', 'failed') NOT NULL DEFAULT 'pending',
            publish_status enum('draft', 'pending', 'published', 'rejected') NOT NULL DEFAULT 'draft',
            post_id bigint(20) unsigned,
            keywords text,
            category varchar(255),
            tags text,
            seo_score int(11),
            quality_score int(11),
            language varchar(10) NOT NULL DEFAULT 'zh',
            collected_at datetime NOT NULL,
            rewritten_at datetime,
            published_at datetime,
            PRIMARY KEY (id),
            KEY source_id (source_id),
            KEY rewrite_status (rewrite_status),
            KEY publish_status (publish_status),
            KEY collected_at (collected_at),
            FOREIGN KEY (source_id) REFERENCES {$wpdb->prefix}ai_optimizer_content_sources(id) ON DELETE CASCADE
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql_sources);
        dbDelta($sql_content);
    }
    
    /**
     * 设置定时任务
     */
    public function setup_cron_jobs() {
        if (!wp_next_scheduled('ai_optimizer_collect_content')) {
            $frequency = AI_Optimizer_Settings::get('content_collection_frequency', 'hourly');
            wp_schedule_event(time(), $frequency, 'ai_optimizer_collect_content');
        }
    }
    
    /**
     * 执行内容收集
     */
    public function run_content_collection() {
        if (!AI_Optimizer_Settings::get('enable_content_collection', false)) {
            return;
        }
        
        $sources = $this->get_active_sources();
        
        foreach ($sources as $source) {
            $this->collect_from_source($source);
        }
        
        // 处理待重写的内容
        $this->process_pending_rewrites();
        
        // 自动发布（如果启用）
        if (AI_Optimizer_Settings::get('auto_publish_content', false)) {
            $this->auto_publish_content();
        }
    }
    
    /**
     * 获取活跃内容源
     */
    private function get_active_sources() {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'ai_optimizer_content_sources';
        
        return $wpdb->get_results("
            SELECT * FROM $table_name 
            WHERE status = 'active' 
            ORDER BY last_collected ASC
        ");
    }
    
    /**
     * 从指定源收集内容
     */
    private function collect_from_source($source) {
        AI_Optimizer_Utils::log("开始从源收集内容", 'info', array(
            'source_id' => $source->id,
            'source_name' => $source->name,
            'source_type' => $source->type
        ));
        
        try {
            $collected_items = array();
            
            switch ($source->type) {
                case 'rss':
                    $collected_items = $this->collect_from_rss($source);
                    break;
                case 'website':
                    $collected_items = $this->collect_from_website($source);
                    break;
                case 'api':
                    $collected_items = $this->collect_from_api($source);
                    break;
            }
            
            // 保存收集到的内容
            foreach ($collected_items as $item) {
                $this->save_collected_content($source->id, $item);
            }
            
            // 更新源状态
            $this->update_source_status($source->id, 'active', count($collected_items));
            
            AI_Optimizer_Utils::log("内容收集完成", 'info', array(
                'source_id' => $source->id,
                'items_collected' => count($collected_items)
            ));
            
        } catch (Exception $e) {
            $this->update_source_status($source->id, 'error');
            
            AI_Optimizer_Utils::log("内容收集失败", 'error', array(
                'source_id' => $source->id,
                'error' => $e->getMessage()
            ));
        }
    }
    
    /**
     * 从RSS源收集内容
     */
    private function collect_from_rss($source) {
        $settings = json_decode($source->settings, true) ?: array();
        $max_items = $settings['max_items'] ?? 10;
        
        $rss = fetch_feed($source->url);
        
        if (is_wp_error($rss)) {
            throw new Exception('无法获取RSS源: ' . $rss->get_error_message());
        }
        
        $items = array();
        $rss_items = $rss->get_items(0, $max_items);
        
        foreach ($rss_items as $item) {
            // 检查是否已经收集过这个内容
            if ($this->is_content_already_collected($item->get_permalink())) {
                continue;
            }
            
            $content = array(
                'title' => $item->get_title(),
                'content' => $item->get_content(),
                'url' => $item->get_permalink(),
                'published_date' => $item->get_date('Y-m-d H:i:s'),
                'author' => $item->get_author() ? $item->get_author()->get_name() : '',
                'categories' => $this->extract_categories($item)
            );
            
            // 内容质量检查
            if ($this->check_content_quality($content)) {
                $items[] = $content;
            }
        }
        
        return $items;
    }
    
    /**
     * 从网站收集内容
     */
    private function collect_from_website($source) {
        $settings = json_decode($source->settings, true) ?: array();
        $selectors = $settings['selectors'] ?? array();
        
        $response = wp_remote_get($source->url, array(
            'timeout' => 30,
            'user-agent' => 'AI-Website-Optimizer/' . AI_OPTIMIZER_VERSION
        ));
        
        if (is_wp_error($response)) {
            throw new Exception('无法访问网站: ' . $response->get_error_message());
        }
        
        $html = wp_remote_retrieve_body($response);
        
        // 使用DOMDocument解析HTML
        libxml_use_internal_errors(true);
        $dom = new DOMDocument();
        $dom->loadHTML($html);
        libxml_clear_errors();
        
        $xpath = new DOMXPath($dom);
        $items = array();
        
        // 根据配置的选择器提取内容
        $title_selector = $selectors['title'] ?? 'h1, h2, .title';
        $content_selector = $selectors['content'] ?? '.content, .post-content, article';
        $link_selector = $selectors['link'] ?? 'a';
        
        $title_nodes = $xpath->query($this->css_to_xpath($title_selector));
        $content_nodes = $xpath->query($this->css_to_xpath($content_selector));
        
        $count = min($title_nodes->length, $content_nodes->length);
        
        for ($i = 0; $i < $count; $i++) {
            $title = trim($title_nodes->item($i)->textContent);
            $content = trim($content_nodes->item($i)->textContent);
            
            if (!empty($title) && !empty($content) && strlen($content) > 100) {
                $items[] = array(
                    'title' => $title,
                    'content' => $content,
                    'url' => $source->url,
                    'published_date' => current_time('mysql'),
                    'author' => '',
                    'categories' => array()
                );
            }
        }
        
        return $items;
    }
    
    /**
     * 从API收集内容
     */
    private function collect_from_api($source) {
        $settings = json_decode($source->settings, true) ?: array();
        $headers = $settings['headers'] ?? array();
        $auth = $settings['auth'] ?? array();
        
        $args = array(
            'timeout' => 30,
            'headers' => $headers
        );
        
        // 添加认证信息
        if (!empty($auth['type'])) {
            switch ($auth['type']) {
                case 'bearer':
                    $args['headers']['Authorization'] = 'Bearer ' . $auth['token'];
                    break;
                case 'api_key':
                    $args['headers'][$auth['header']] = $auth['key'];
                    break;
            }
        }
        
        $response = wp_remote_get($source->url, $args);
        
        if (is_wp_error($response)) {
            throw new Exception('API请求失败: ' . $response->get_error_message());
        }
        
        $data = json_decode(wp_remote_retrieve_body($response), true);
        
        if (!$data) {
            throw new Exception('无效的API响应');
        }
        
        // 根据API响应格式解析数据
        $items = array();
        $data_path = $settings['data_path'] ?? 'data';
        $content_data = $this->get_nested_value($data, $data_path);
        
        if (is_array($content_data)) {
            foreach ($content_data as $item) {
                $items[] = array(
                    'title' => $this->get_nested_value($item, $settings['title_field'] ?? 'title'),
                    'content' => $this->get_nested_value($item, $settings['content_field'] ?? 'content'),
                    'url' => $this->get_nested_value($item, $settings['url_field'] ?? 'url'),
                    'published_date' => $this->get_nested_value($item, $settings['date_field'] ?? 'date'),
                    'author' => $this->get_nested_value($item, $settings['author_field'] ?? 'author'),
                    'categories' => $this->get_nested_value($item, $settings['category_field'] ?? 'categories')
                );
            }
        }
        
        return $items;
    }
    
    /**
     * 检查内容是否已经收集过
     */
    private function is_content_already_collected($url) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'ai_optimizer_collected_content';
        
        $count = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM $table_name WHERE original_url = %s",
            $url
        ));
        
        return $count > 0;
    }
    
    /**
     * 检查内容质量
     */
    private function check_content_quality($content) {
        $min_length = AI_Optimizer_Settings::get('min_content_length', 200);
        $blacklist_words = AI_Optimizer_Settings::get('content_blacklist', array());
        
        // 长度检查
        if (strlen(strip_tags($content['content'])) < $min_length) {
            return false;
        }
        
        // 黑名单词汇检查
        foreach ($blacklist_words as $word) {
            if (stripos($content['title'] . ' ' . $content['content'], $word) !== false) {
                return false;
            }
        }
        
        // 重复内容检查
        if ($this->check_duplicate_content($content['content'])) {
            return false;
        }
        
        return true;
    }
    
    /**
     * 检查重复内容
     */
    private function check_duplicate_content($content) {
        global $wpdb;
        
        $content_hash = md5(strip_tags($content));
        
        // 检查已发布的文章
        $existing_post = $wpdb->get_var($wpdb->prepare(
            "SELECT ID FROM {$wpdb->posts} WHERE MD5(post_content) = %s AND post_status = 'publish'",
            $content_hash
        ));
        
        if ($existing_post) {
            return true;
        }
        
        // 检查已收集的内容
        $table_name = $wpdb->prefix . 'ai_optimizer_collected_content';
        $existing_content = $wpdb->get_var($wpdb->prepare(
            "SELECT id FROM $table_name WHERE MD5(original_content) = %s",
            $content_hash
        ));
        
        return $existing_content !== null;
    }
    
    /**
     * 保存收集到的内容
     */
    private function save_collected_content($source_id, $content) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'ai_optimizer_collected_content';
        
        $data = array(
            'source_id' => $source_id,
            'original_title' => sanitize_text_field($content['title']),
            'original_content' => wp_kses_post($content['content']),
            'original_url' => esc_url_raw($content['url']),
            'category' => sanitize_text_field($content['categories'][0] ?? ''),
            'language' => $this->detect_language($content['content']),
            'collected_at' => current_time('mysql')
        );
        
        $result = $wpdb->insert($table_name, $data);
        
        if ($result !== false) {
            AI_Optimizer_Utils::log("内容已保存", 'info', array(
                'content_id' => $wpdb->insert_id,
                'title' => $content['title']
            ));
        }
    }
    
    /**
     * 处理待重写的内容
     */
    private function process_pending_rewrites() {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'ai_optimizer_collected_content';
        $batch_size = AI_Optimizer_Settings::get('rewrite_batch_size', 5);
        
        $pending_content = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM $table_name 
             WHERE rewrite_status = 'pending' 
             ORDER BY collected_at ASC 
             LIMIT %d",
            $batch_size
        ));
        
        foreach ($pending_content as $content) {
            $this->rewrite_content($content);
        }
    }
    
    /**
     * 重写内容
     */
    private function rewrite_content($content) {
        global $wpdb;
        
        // 更新状态为处理中
        $wpdb->update(
            $wpdb->prefix . 'ai_optimizer_collected_content',
            array('rewrite_status' => 'processing'),
            array('id' => $content->id)
        );
        
        try {
            $rewrite_prompt = $this->build_rewrite_prompt($content);
            
            $response = $this->api_handler->chat_completion(array(
                'messages' => array(
                    array(
                        'role' => 'user',
                        'content' => $rewrite_prompt
                    )
                ),
                'model' => AI_Optimizer_Settings::get('content_rewrite_model', 'Qwen/Qwen2.5-72B-Instruct'),
                'temperature' => 0.7,
                'max_tokens' => 3000
            ));
            
            if ($response['success']) {
                $rewritten_content = $this->parse_rewritten_content($response['data']['choices'][0]['message']['content']);
                
                // 保存重写结果
                $wpdb->update(
                    $wpdb->prefix . 'ai_optimizer_collected_content',
                    array(
                        'rewritten_title' => $rewritten_content['title'],
                        'rewritten_content' => $rewritten_content['content'],
                        'keywords' => $rewritten_content['keywords'],
                        'rewrite_status' => 'completed',
                        'rewritten_at' => current_time('mysql'),
                        'quality_score' => $this->calculate_quality_score($rewritten_content),
                        'seo_score' => $this->calculate_seo_score($rewritten_content)
                    ),
                    array('id' => $content->id)
                );
                
                AI_Optimizer_Utils::log("内容重写完成", 'info', array(
                    'content_id' => $content->id,
                    'original_title' => $content->original_title,
                    'rewritten_title' => $rewritten_content['title']
                ));
                
            } else {
                throw new Exception($response['message']);
            }
            
        } catch (Exception $e) {
            // 更新状态为失败
            $wpdb->update(
                $wpdb->prefix . 'ai_optimizer_collected_content',
                array('rewrite_status' => 'failed'),
                array('id' => $content->id)
            );
            
            AI_Optimizer_Utils::log("内容重写失败", 'error', array(
                'content_id' => $content->id,
                'error' => $e->getMessage()
            ));
        }
    }
    
    /**
     * 构建重写提示
     */
    private function build_rewrite_prompt($content) {
        $target_keywords = AI_Optimizer_Settings::get('target_keywords', '');
        $writing_style = AI_Optimizer_Settings::get('rewriting_style', 'professional');
        $language = $content->language ?: 'zh';
        
        $prompt = "请将以下内容进行完全重写，要求：\n\n";
        $prompt .= "1. 保持原意不变，但用全新的表达方式\n";
        $prompt .= "2. 提高内容的可读性和吸引力\n";
        $prompt .= "3. 优化SEO，适当融入关键词：$target_keywords\n";
        $prompt .= "4. 语言风格：$writing_style\n";
        $prompt .= "5. 输出语言：" . ($language === 'zh' ? '中文' : '英文') . "\n\n";
        
        $prompt .= "原标题：{$content->original_title}\n\n";
        $prompt .= "原内容：\n{$content->original_content}\n\n";
        
        $prompt .= "请按以下格式输出：\n";
        $prompt .= "---标题---\n[重写后的标题]\n\n";
        $prompt .= "---内容---\n[重写后的内容]\n\n";
        $prompt .= "---关键词---\n[提取的关键词，用逗号分隔]\n";
        
        return $prompt;
    }
    
    /**
     * 解析重写内容
     */
    private function parse_rewritten_content($ai_response) {
        $title = '';
        $content = '';
        $keywords = '';
        
        // 提取标题
        if (preg_match('/---标题---\s*\n(.*?)\n\n/s', $ai_response, $matches)) {
            $title = trim($matches[1]);
        }
        
        // 提取内容
        if (preg_match('/---内容---\s*\n(.*?)\n\n---关键词---/s', $ai_response, $matches)) {
            $content = trim($matches[1]);
        }
        
        // 提取关键词
        if (preg_match('/---关键词---\s*\n(.*?)$/s', $ai_response, $matches)) {
            $keywords = trim($matches[1]);
        }
        
        return array(
            'title' => $title ?: '重写标题',
            'content' => $content ?: $ai_response,
            'keywords' => $keywords
        );
    }
    
    /**
     * 计算内容质量评分
     */
    private function calculate_quality_score($content) {
        $score = 0;
        
        // 长度评分 (30分)
        $content_length = strlen(strip_tags($content['content']));
        if ($content_length > 1000) {
            $score += 30;
        } elseif ($content_length > 500) {
            $score += 20;
        } elseif ($content_length > 200) {
            $score += 10;
        }
        
        // 结构评分 (25分)
        $paragraphs = count(explode("\n", trim($content['content'])));
        if ($paragraphs >= 3) {
            $score += 25;
        } elseif ($paragraphs >= 2) {
            $score += 15;
        }
        
        // 关键词评分 (25分)
        $keywords = explode(',', $content['keywords']);
        $keyword_count = count(array_filter($keywords));
        if ($keyword_count >= 5) {
            $score += 25;
        } elseif ($keyword_count >= 3) {
            $score += 15;
        } elseif ($keyword_count >= 1) {
            $score += 5;
        }
        
        // 原创性评分 (20分)
        $score += 20; // AI重写的内容默认认为是原创的
        
        return min($score, 100);
    }
    
    /**
     * 计算SEO评分
     */
    private function calculate_seo_score($content) {
        $score = 0;
        
        // 标题长度 (20分)
        $title_length = strlen($content['title']);
        if ($title_length >= 30 && $title_length <= 60) {
            $score += 20;
        } elseif ($title_length >= 20 && $title_length <= 80) {
            $score += 10;
        }
        
        // 内容长度 (30分)
        $content_length = strlen(strip_tags($content['content']));
        if ($content_length >= 800) {
            $score += 30;
        } elseif ($content_length >= 400) {
            $score += 20;
        } elseif ($content_length >= 200) {
            $score += 10;
        }
        
        // 关键词密度 (25分)
        $target_keywords = AI_Optimizer_Settings::get('target_keywords', '');
        if (!empty($target_keywords)) {
            $keywords = explode(',', $target_keywords);
            $text = strtolower($content['title'] . ' ' . $content['content']);
            
            foreach ($keywords as $keyword) {
                $keyword = trim(strtolower($keyword));
                if (!empty($keyword) && strpos($text, $keyword) !== false) {
                    $score += 5;
                }
            }
        }
        
        // 内容结构 (25分)
        if (strpos($content['content'], "\n") !== false) {
            $score += 15; // 有段落分隔
        }
        if (strlen($content['keywords']) > 0) {
            $score += 10; // 有关键词
        }
        
        return min($score, 100);
    }
    
    /**
     * 自动发布内容
     */
    private function auto_publish_content() {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'ai_optimizer_collected_content';
        $min_quality_score = AI_Optimizer_Settings::get('min_auto_publish_quality', 80);
        $max_auto_publish = AI_Optimizer_Settings::get('max_auto_publish_per_day', 5);
        
        // 获取今天已自动发布的数量
        $today_published = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM $table_name 
             WHERE publish_status = 'published' 
             AND DATE(published_at) = %s",
            current_time('Y-m-d')
        ));
        
        if ($today_published >= $max_auto_publish) {
            return;
        }
        
        // 获取符合条件的内容
        $content_to_publish = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM $table_name 
             WHERE rewrite_status = 'completed' 
             AND publish_status = 'draft' 
             AND quality_score >= %d 
             ORDER BY quality_score DESC, seo_score DESC 
             LIMIT %d",
            $min_quality_score,
            $max_auto_publish - $today_published
        ));
        
        foreach ($content_to_publish as $content) {
            $this->publish_content($content);
        }
    }
    
    /**
     * 发布内容为WordPress文章
     */
    private function publish_content($content) {
        $post_data = array(
            'post_title' => $content->rewritten_title,
            'post_content' => $content->rewritten_content,
            'post_status' => AI_Optimizer_Settings::get('auto_publish_status', 'draft'),
            'post_author' => AI_Optimizer_Settings::get('default_author_id', 1),
            'post_category' => $this->get_category_id($content->category),
            'tags_input' => $content->keywords,
            'meta_input' => array(
                'ai_optimizer_source' => 'content_collector',
                'ai_optimizer_original_url' => $content->original_url,
                'ai_optimizer_quality_score' => $content->quality_score,
                'ai_optimizer_seo_score' => $content->seo_score
            )
        );
        
        $post_id = wp_insert_post($post_data);
        
        if (!is_wp_error($post_id)) {
            // 更新收集内容记录
            global $wpdb;
            $wpdb->update(
                $wpdb->prefix . 'ai_optimizer_collected_content',
                array(
                    'post_id' => $post_id,
                    'publish_status' => 'published',
                    'published_at' => current_time('mysql')
                ),
                array('id' => $content->id)
            );
            
            AI_Optimizer_Utils::log("内容发布成功", 'info', array(
                'content_id' => $content->id,
                'post_id' => $post_id,
                'title' => $content->rewritten_title
            ));
        } else {
            AI_Optimizer_Utils::log("内容发布失败", 'error', array(
                'content_id' => $content->id,
                'error' => $post_id->get_error_message()
            ));
        }
    }
    
    /**
     * 工具方法
     */
    private function extract_categories($item) {
        $categories = array();
        if ($item->get_categories()) {
            foreach ($item->get_categories() as $category) {
                $categories[] = $category->get_term();
            }
        }
        return $categories;
    }
    
    private function css_to_xpath($css_selector) {
        // 简单的CSS到XPath转换
        $xpath = str_replace(array('.', '#'), array('[@class="', '[@id="'], $css_selector);
        $xpath = str_replace('"', '"]', $xpath);
        return '//' . $xpath;
    }
    
    private function get_nested_value($array, $path) {
        $keys = explode('.', $path);
        $value = $array;
        
        foreach ($keys as $key) {
            if (isset($value[$key])) {
                $value = $value[$key];
            } else {
                return null;
            }
        }
        
        return $value;
    }
    
    private function detect_language($text) {
        // 简单的语言检测
        if (preg_match('/[\x{4e00}-\x{9fff}]/u', $text)) {
            return 'zh';
        }
        return 'en';
    }
    
    private function get_category_id($category_name) {
        if (empty($category_name)) {
            return array();
        }
        
        $category = get_category_by_slug(sanitize_title($category_name));
        if ($category) {
            return array($category->term_id);
        }
        
        // 创建新分类
        $category_id = wp_create_category($category_name);
        return $category_id ? array($category_id) : array();
    }
    
    private function update_source_status($source_id, $status, $collected_count = 0) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'ai_optimizer_content_sources';
        
        $update_data = array(
            'status' => $status,
            'last_collected' => current_time('mysql'),
            'updated_at' => current_time('mysql')
        );
        
        if ($collected_count > 0) {
            $update_data['total_collected'] = $wpdb->get_var($wpdb->prepare(
                "SELECT total_collected FROM $table_name WHERE id = %d",
                $source_id
            )) + $collected_count;
        }
        
        $wpdb->update($table_name, $update_data, array('id' => $source_id));
    }
    
    /**
     * AJAX处理方法
     */
    public function handle_add_source() {
        if (!AI_Optimizer_Security::verify_nonce($_POST['nonce'] ?? '')) {
            wp_die(__('安全检查失败', 'ai-website-optimizer'));
        }
        
        if (!AI_Optimizer_Security::check_permission('manage_settings')) {
            wp_die(__('权限不足', 'ai-website-optimizer'));
        }
        
        $name = sanitize_text_field($_POST['name'] ?? '');
        $type = sanitize_text_field($_POST['type'] ?? 'rss');
        $url = esc_url_raw($_POST['url'] ?? '');
        $settings = $_POST['settings'] ?? array();
        
        if (empty($name) || empty($url)) {
            wp_send_json_error('名称和URL不能为空');
        }
        
        global $wpdb;
        $table_name = $wpdb->prefix . 'ai_optimizer_content_sources';
        
        $result = $wpdb->insert($table_name, array(
            'name' => $name,
            'type' => $type,
            'url' => $url,
            'settings' => wp_json_encode($settings),
            'created_at' => current_time('mysql'),
            'updated_at' => current_time('mysql')
        ));
        
        if ($result !== false) {
            wp_send_json_success(array(
                'message' => '内容源添加成功',
                'id' => $wpdb->insert_id
            ));
        } else {
            wp_send_json_error('添加失败');
        }
    }
    
    public function handle_remove_source() {
        if (!AI_Optimizer_Security::verify_nonce($_POST['nonce'] ?? '')) {
            wp_die(__('安全检查失败', 'ai-website-optimizer'));
        }
        
        if (!AI_Optimizer_Security::check_permission('manage_settings')) {
            wp_die(__('权限不足', 'ai-website-optimizer'));
        }
        
        $source_id = intval($_POST['source_id'] ?? 0);
        
        if ($source_id <= 0) {
            wp_send_json_error('无效的源ID');
        }
        
        global $wpdb;
        $table_name = $wpdb->prefix . 'ai_optimizer_content_sources';
        
        $result = $wpdb->delete($table_name, array('id' => $source_id));
        
        if ($result !== false) {
            wp_send_json_success('内容源删除成功');
        } else {
            wp_send_json_error('删除失败');
        }
    }
    
    public function handle_test_source() {
        if (!AI_Optimizer_Security::verify_nonce($_POST['nonce'] ?? '')) {
            wp_die(__('安全检查失败', 'ai-website-optimizer'));
        }
        
        $source_id = intval($_POST['source_id'] ?? 0);
        
        global $wpdb;
        $table_name = $wpdb->prefix . 'ai_optimizer_content_sources';
        
        $source = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table_name WHERE id = %d",
            $source_id
        ));
        
        if (!$source) {
            wp_send_json_error('源不存在');
        }
        
        try {
            $items = $this->collect_from_source($source);
            wp_send_json_success(array(
                'message' => '测试成功',
                'items_found' => count($items)
            ));
        } catch (Exception $e) {
            wp_send_json_error('测试失败: ' . $e->getMessage());
        }
    }
    
    public function handle_manual_collection() {
        if (!AI_Optimizer_Security::verify_nonce($_POST['nonce'] ?? '')) {
            wp_die(__('安全检查失败', 'ai-website-optimizer'));
        }
        
        if (!AI_Optimizer_Security::check_permission('edit_posts')) {
            wp_die(__('权限不足', 'ai-website-optimizer'));
        }
        
        $this->run_content_collection();
        wp_send_json_success('手动收集完成');
    }
    
    public function handle_publish_content() {
        if (!AI_Optimizer_Security::verify_nonce($_POST['nonce'] ?? '')) {
            wp_die(__('安全检查失败', 'ai-website-optimizer'));
        }
        
        if (!AI_Optimizer_Security::check_permission('publish_posts')) {
            wp_die(__('权限不足', 'ai-website-optimizer'));
        }
        
        $content_id = intval($_POST['content_id'] ?? 0);
        
        global $wpdb;
        $table_name = $wpdb->prefix . 'ai_optimizer_collected_content';
        
        $content = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table_name WHERE id = %d",
            $content_id
        ));
        
        if (!$content) {
            wp_send_json_error('内容不存在');
        }
        
        if ($content->rewrite_status !== 'completed') {
            wp_send_json_error('内容尚未重写完成');
        }
        
        $this->publish_content($content);
        wp_send_json_success('内容发布成功');
    }
}