<?php
/**
 * Content Collector class for automated content gathering and AI rewriting
 */

if (!defined('ABSPATH')) {
    exit;
}

class AI_Optimizer_Content_Collector {
    
    private $api_handler;
    private $sources;
    
    public function __construct() {
        $this->api_handler = new AI_Optimizer_API_Handler();
        $this->sources = $this->get_content_sources();
    }
    
    /**
     * Collect content from all configured sources
     */
    public function collect_all_content() {
        AI_Optimizer_Utils::log('Starting content collection from all sources', 'info');
        
        $collected_items = array();
        
        foreach ($this->sources as $source) {
            $items = $this->collect_from_source($source);
            $collected_items = array_merge($collected_items, $items);
            
            // Add delay between sources to be respectful
            sleep(2);
        }
        
        // Process collected items
        $processed_items = array();
        foreach ($collected_items as $item) {
            $processed = $this->process_content_item($item);
            if ($processed) {
                $processed_items[] = $processed;
            }
            
            // Rate limiting
            sleep(1);
        }
        
        AI_Optimizer_Utils::log('Content collection completed', 'info', array(
            'sources_checked' => count($this->sources),
            'items_collected' => count($collected_items),
            'items_processed' => count($processed_items)
        ));
        
        return $processed_items;
    }
    
    /**
     * Collect content from a specific source
     */
    public function collect_from_source($source) {
        $source_type = $this->detect_source_type($source);
        
        switch ($source_type) {
            case 'rss':
                return $this->collect_from_rss($source);
            case 'website':
                return $this->collect_from_website($source);
            case 'api':
                return $this->collect_from_api($source);
            default:
                AI_Optimizer_Utils::log('Unknown source type', 'warning', array('source' => $source));
                return array();
        }
    }
    
    /**
     * Collect from RSS feed
     */
    private function collect_from_rss($rss_url) {
        $items = array();
        
        AI_Optimizer_Utils::log('Collecting from RSS feed', 'info', array('url' => $rss_url));
        
        $rss = fetch_feed($rss_url);
        
        if (is_wp_error($rss)) {
            AI_Optimizer_Utils::log('RSS feed error', 'error', array(
                'url' => $rss_url,
                'error' => $rss->get_error_message()
            ));
            return array();
        }
        
        $maxitems = $rss->get_item_quantity(20);
        $rss_items = $rss->get_items(0, $maxitems);
        
        foreach ($rss_items as $item) {
            $collected_item = array(
                'title' => $item->get_title(),
                'content' => $item->get_content(),
                'excerpt' => $item->get_description(),
                'url' => $item->get_link(),
                'published' => $item->get_date('Y-m-d H:i:s'),
                'author' => $item->get_author() ? $item->get_author()->get_name() : '',
                'source_url' => $rss_url,
                'source_type' => 'rss',
                'categories' => $this->extract_categories($item)
            );
            
            // Check if content is new
            if (!$this->content_exists($collected_item)) {
                $items[] = $collected_item;
            }
        }
        
        return $items;
    }
    
    /**
     * Collect from website by scraping
     */
    private function collect_from_website($website_url) {
        $items = array();
        
        AI_Optimizer_Utils::log('Collecting from website', 'info', array('url' => $website_url));
        
        $response = wp_remote_get($website_url, array('timeout' => 30));
        
        if (is_wp_error($response)) {
            AI_Optimizer_Utils::log('Website scraping error', 'error', array(
                'url' => $website_url,
                'error' => $response->get_error_message()
            ));
            return array();
        }
        
        $html = wp_remote_retrieve_body($response);
        $dom = new DOMDocument();
        @$dom->loadHTML($html);
        
        // Extract articles using common patterns
        $articles = $this->extract_articles_from_dom($dom, $website_url);
        
        foreach ($articles as $article) {
            if (!$this->content_exists($article)) {
                $items[] = $article;
            }
        }
        
        return $items;
    }
    
    /**
     * Collect from API endpoints
     */
    private function collect_from_api($api_config) {
        $items = array();
        
        // Parse API configuration
        $config = json_decode($api_config, true);
        if (!$config) {
            return array();
        }
        
        AI_Optimizer_Utils::log('Collecting from API', 'info', array('endpoint' => $config['endpoint']));
        
        $response = wp_remote_get($config['endpoint'], array(
            'headers' => $config['headers'] ?? array(),
            'timeout' => 30
        ));
        
        if (is_wp_error($response)) {
            AI_Optimizer_Utils::log('API collection error', 'error', array(
                'endpoint' => $config['endpoint'],
                'error' => $response->get_error_message()
            ));
            return array();
        }
        
        $data = json_decode(wp_remote_retrieve_body($response), true);
        
        if ($data && isset($config['data_path'])) {
            $items_data = $this->extract_data_by_path($data, $config['data_path']);
            
            foreach ($items_data as $item_data) {
                $mapped_item = $this->map_api_data($item_data, $config['mapping'] ?? array());
                if ($mapped_item && !$this->content_exists($mapped_item)) {
                    $items[] = $mapped_item;
                }
            }
        }
        
        return $items;
    }
    
    /**
     * Process content item with AI
     */
    private function process_content_item($item) {
        // Check content quality
        if (!$this->is_content_quality_acceptable($item)) {
            return false;
        }
        
        // Rewrite content to avoid copyright issues
        $rewritten_content = $this->rewrite_content_with_ai($item);
        
        if (!$rewritten_content) {
            AI_Optimizer_Utils::log('Failed to rewrite content', 'warning', array(
                'title' => $item['title']
            ));
            return false;
        }
        
        // Enhance with SEO optimization
        $seo_optimized = $this->optimize_content_for_seo($rewritten_content);
        
        // Generate categories and tags
        $categories_tags = $this->generate_categories_and_tags($seo_optimized);
        
        return array_merge($seo_optimized, $categories_tags, array(
            'original_source' => $item['source_url'],
            'collection_date' => current_time('mysql'),
            'processing_status' => 'ready_to_publish'
        ));
    }
    
    /**
     * Rewrite content using AI
     */
    private function rewrite_content_with_ai($item) {
        $original_content = $item['content'];
        $title = $item['title'];
        
        $rewrite_prompt = "Rewrite this article to be original and engaging while preserving the key information:\n\n";
        $rewrite_prompt .= "Original Title: {$title}\n";
        $rewrite_prompt .= "Original Content: " . wp_trim_words($original_content, 300) . "\n\n";
        $rewrite_prompt .= "Requirements:\n";
        $rewrite_prompt .= "- Create completely original content\n";
        $rewrite_prompt .= "- Maintain factual accuracy\n";
        $rewrite_prompt .= "- Improve readability and engagement\n";
        $rewrite_prompt .= "- Optimize for SEO\n";
        $rewrite_prompt .= "- Keep the same general topic and intent\n\n";
        $rewrite_prompt .= "Provide: New Title, New Content (minimum 300 words), Meta Description";
        
        $rewritten = $this->api_handler->chat_completion(
            $rewrite_prompt,
            'Qwen/QwQ-32B',
            'You are a professional content writer specializing in creating original, SEO-optimized articles.'
        );
        
        if (!$rewritten) {
            return false;
        }
        
        // Parse the AI response
        $parsed = $this->parse_rewritten_content($rewritten, $item);
        
        return $parsed;
    }
    
    /**
     * Optimize content for SEO
     */
    private function optimize_content_for_seo($content) {
        $target_keywords = AI_Optimizer_Settings::get('seo_target_keywords', '');
        $keywords_array = array_filter(array_map('trim', explode("\n", $target_keywords)));
        
        if (empty($keywords_array)) {
            return $content;
        }
        
        $optimization_prompt = "Optimize this content for SEO using these target keywords: " . implode(', ', $keywords_array) . "\n\n";
        $optimization_prompt .= "Content: {$content['content']}\n\n";
        $optimization_prompt .= "Requirements:\n";
        $optimization_prompt .= "- Naturally integrate target keywords\n";
        $optimization_prompt .= "- Maintain keyword density between 1-3%\n";
        $optimization_prompt .= "- Improve content structure with headings\n";
        $optimization_prompt .= "- Add internal linking suggestions\n";
        $optimization_prompt .= "- Ensure readability remains high\n\n";
        $optimization_prompt .= "Return the optimized content with suggested improvements.";
        
        $optimized = $this->api_handler->chat_completion(
            $optimization_prompt,
            'Qwen/QwQ-32B',
            'You are an SEO expert specializing in content optimization.'
        );
        
        if ($optimized) {
            $content['content'] = $optimized;
            $content['seo_optimized'] = true;
        }
        
        return $content;
    }
    
    /**
     * Generate categories and tags using AI
     */
    private function generate_categories_and_tags($content) {
        $categorization_prompt = "Analyze this content and suggest appropriate WordPress categories and tags:\n\n";
        $categorization_prompt .= "Title: {$content['title']}\n";
        $categorization_prompt .= "Content: " . wp_trim_words($content['content'], 200) . "\n\n";
        $categorization_prompt .= "Provide:\n";
        $categorization_prompt .= "1. Primary Category (1-2 categories)\n";
        $categorization_prompt .= "2. Tags (5-10 relevant tags)\n";
        $categorization_prompt .= "3. Content type classification\n\n";
        $categorization_prompt .= "Format as JSON: {\"categories\": [\"cat1\", \"cat2\"], \"tags\": [\"tag1\", \"tag2\"], \"content_type\": \"type\"}";
        
        $categorization = $this->api_handler->chat_completion(
            $categorization_prompt,
            'Qwen/QwQ-32B',
            'You are a content categorization specialist.'
        );
        
        $parsed_categorization = json_decode($categorization, true);
        
        if ($parsed_categorization) {
            return array(
                'suggested_categories' => $parsed_categorization['categories'] ?? array(),
                'suggested_tags' => $parsed_categorization['tags'] ?? array(),
                'content_type' => $parsed_categorization['content_type'] ?? 'article'
            );
        }
        
        return array(
            'suggested_categories' => array(),
            'suggested_tags' => array(),
            'content_type' => 'article'
        );
    }
    
    /**
     * Auto-publish processed content
     */
    public function auto_publish_content($content_items) {
        if (!AI_Optimizer_Settings::get('content_auto_publish', false)) {
            return false;
        }
        
        $published_count = 0;
        
        foreach ($content_items as $item) {
            $post_id = $this->create_wordpress_post($item);
            
            if ($post_id) {
                $published_count++;
                
                // Store collection metadata
                $this->store_collection_metadata($post_id, $item);
                
                AI_Optimizer_Utils::log('Auto-published content', 'info', array(
                    'post_id' => $post_id,
                    'title' => $item['title'],
                    'source' => $item['original_source']
                ));
                
                // Rate limiting between posts
                sleep(5);
            }
        }
        
        return $published_count;
    }
    
    /**
     * Create WordPress post from content item
     */
    private function create_wordpress_post($item) {
        $default_categories = AI_Optimizer_Settings::get('content_categories', array());
        
        $post_data = array(
            'post_title' => $item['title'],
            'post_content' => $item['content'],
            'post_excerpt' => $item['meta_description'] ?? '',
            'post_status' => 'draft', // Start as draft for review
            'post_type' => 'post',
            'post_category' => $default_categories,
            'meta_input' => array(
                'ai_optimizer_source_url' => $item['original_source'],
                'ai_optimizer_collection_date' => $item['collection_date'],
                'ai_optimizer_processed_by_ai' => true
            )
        );
        
        $post_id = wp_insert_post($post_data);
        
        if ($post_id && !is_wp_error($post_id)) {
            // Add suggested tags
            if (!empty($item['suggested_tags'])) {
                wp_set_post_tags($post_id, $item['suggested_tags']);
            }
            
            // Add suggested categories
            if (!empty($item['suggested_categories'])) {
                $category_ids = array();
                foreach ($item['suggested_categories'] as $cat_name) {
                    $cat = get_category_by_slug(sanitize_title($cat_name));
                    if (!$cat) {
                        $cat_id = wp_create_category($cat_name);
                        if ($cat_id) {
                            $category_ids[] = $cat_id;
                        }
                    } else {
                        $category_ids[] = $cat->term_id;
                    }
                }
                
                if (!empty($category_ids)) {
                    wp_set_post_categories($post_id, $category_ids, true);
                }
            }
            
            return $post_id;
        }
        
        return false;
    }
    
    /**
     * Check if content already exists
     */
    private function content_exists($item) {
        global $wpdb;
        
        // Check by title similarity
        $existing_post = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT ID FROM {$wpdb->posts} 
                WHERE post_title = %s 
                AND post_status IN ('publish', 'draft', 'pending') 
                LIMIT 1",
                $item['title']
            )
        );
        
        if ($existing_post) {
            return true;
        }
        
        // Check by URL if available
        if (isset($item['url'])) {
            $existing_meta = $wpdb->get_row(
                $wpdb->prepare(
                    "SELECT post_id FROM {$wpdb->postmeta} 
                    WHERE meta_key = 'ai_optimizer_source_url' 
                    AND meta_value = %s 
                    LIMIT 1",
                    $item['url']
                )
            );
            
            if ($existing_meta) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Helper methods
     */
    private function get_content_sources() {
        $sources_text = AI_Optimizer_Settings::get('content_sources', '');
        $sources = array_filter(array_map('trim', explode("\n", $sources_text)));
        
        return $sources;
    }
    
    private function detect_source_type($source) {
        if (strpos($source, '.xml') !== false || strpos($source, 'rss') !== false || strpos($source, 'feed') !== false) {
            return 'rss';
        }
        
        if (strpos($source, '{') === 0) {
            return 'api';
        }
        
        return 'website';
    }
    
    private function extract_categories($rss_item) {
        $categories = array();
        $item_categories = $rss_item->get_categories();
        
        if ($item_categories) {
            foreach ($item_categories as $category) {
                $categories[] = $category->get_label();
            }
        }
        
        return $categories;
    }
    
    private function extract_articles_from_dom($dom, $base_url) {
        $articles = array();
        $xpath = new DOMXPath($dom);
        
        // Common article selectors
        $article_selectors = array(
            '//article',
            '//*[contains(@class, "post")]',
            '//*[contains(@class, "article")]',
            '//*[contains(@class, "entry")]'
        );
        
        foreach ($article_selectors as $selector) {
            $nodes = $xpath->query($selector);
            
            foreach ($nodes as $node) {
                $article = $this->extract_article_from_node($node, $base_url);
                if ($article && !empty($article['title']) && !empty($article['content'])) {
                    $articles[] = $article;
                }
            }
            
            if (!empty($articles)) {
                break; // Found articles with first selector
            }
        }
        
        return array_slice($articles, 0, 10); // Limit to 10 articles per page
    }
    
    private function extract_article_from_node($node, $base_url) {
        $xpath = new DOMXPath($node->ownerDocument);
        
        // Extract title
        $title_nodes = $xpath->query('.//h1 | .//h2 | .//h3 | .//*[contains(@class, "title")]', $node);
        $title = $title_nodes->length > 0 ? trim($title_nodes->item(0)->textContent) : '';
        
        // Extract content
        $content_nodes = $xpath->query('.//*[contains(@class, "content") or contains(@class, "body") or contains(@class, "text")]', $node);
        $content = '';
        
        if ($content_nodes->length > 0) {
            $content = trim($content_nodes->item(0)->textContent);
        } else {
            // Fallback to all text content
            $content = trim($node->textContent);
        }
        
        // Extract link
        $link_nodes = $xpath->query('.//a[@href]', $node);
        $link = '';
        
        if ($link_nodes->length > 0) {
            $href = $link_nodes->item(0)->getAttribute('href');
            $link = $this->resolve_url($href, $base_url);
        }
        
        return array(
            'title' => $title,
            'content' => $content,
            'excerpt' => wp_trim_words($content, 50),
            'url' => $link,
            'published' => current_time('mysql'),
            'author' => '',
            'source_url' => $base_url,
            'source_type' => 'website'
        );
    }
    
    private function resolve_url($href, $base_url) {
        if (strpos($href, 'http') === 0) {
            return $href;
        }
        
        $parsed_base = parse_url($base_url);
        $base_scheme_host = $parsed_base['scheme'] . '://' . $parsed_base['host'];
        
        if (strpos($href, '/') === 0) {
            return $base_scheme_host . $href;
        }
        
        return $base_scheme_host . '/' . ltrim($href, '/');
    }
    
    private function extract_data_by_path($data, $path) {
        $keys = explode('.', $path);
        $current = $data;
        
        foreach ($keys as $key) {
            if (is_array($current) && isset($current[$key])) {
                $current = $current[$key];
            } else {
                return array();
            }
        }
        
        return is_array($current) ? $current : array($current);
    }
    
    private function map_api_data($item_data, $mapping) {
        $mapped = array();
        
        foreach ($mapping as $field => $path) {
            $value = $this->get_nested_value($item_data, $path);
            $mapped[$field] = $value;
        }
        
        return $mapped;
    }
    
    private function get_nested_value($data, $path) {
        $keys = explode('.', $path);
        $current = $data;
        
        foreach ($keys as $key) {
            if (is_array($current) && isset($current[$key])) {
                $current = $current[$key];
            } else {
                return '';
            }
        }
        
        return $current;
    }
    
    private function is_content_quality_acceptable($item) {
        // Basic quality checks
        $title_length = strlen($item['title']);
        $content_length = strlen($item['content']);
        
        if ($title_length < 10 || $title_length > 200) {
            return false;
        }
        
        if ($content_length < 100) {
            return false;
        }
        
        // Check for spam patterns
        $spam_patterns = array(
            '/\b(?:buy now|click here|free money|guaranteed)\b/i',
            '/\b(?:viagra|casino|poker|loan)\b/i'
        );
        
        $full_text = $item['title'] . ' ' . $item['content'];
        
        foreach ($spam_patterns as $pattern) {
            if (preg_match($pattern, $full_text)) {
                return false;
            }
        }
        
        return true;
    }
    
    private function parse_rewritten_content($rewritten_text, $original_item) {
        // Try to extract structured content from AI response
        $lines = explode("\n", $rewritten_text);
        
        $new_title = '';
        $new_content = '';
        $meta_description = '';
        
        $current_section = '';
        
        foreach ($lines as $line) {
            $line = trim($line);
            
            if (stripos($line, 'title:') === 0) {
                $new_title = trim(substr($line, 6));
                $current_section = 'title';
            } elseif (stripos($line, 'content:') === 0) {
                $new_content = trim(substr($line, 8));
                $current_section = 'content';
            } elseif (stripos($line, 'meta description:') === 0) {
                $meta_description = trim(substr($line, 17));
                $current_section = 'meta';
            } elseif (!empty($line) && $current_section) {
                if ($current_section === 'content') {
                    $new_content .= "\n" . $line;
                } elseif ($current_section === 'meta') {
                    $meta_description .= " " . $line;
                }
            }
        }
        
        // Fallback if structured parsing fails
        if (empty($new_title)) {
            $new_title = $original_item['title'];
        }
        
        if (empty($new_content)) {
            $new_content = $rewritten_text;
        }
        
        return array(
            'title' => $new_title,
            'content' => $new_content,
            'meta_description' => $meta_description,
            'excerpt' => wp_trim_words($new_content, 50)
        );
    }
    
    private function store_collection_metadata($post_id, $item) {
        update_post_meta($post_id, 'ai_optimizer_original_source', $item['original_source']);
        update_post_meta($post_id, 'ai_optimizer_collection_method', $item['source_type']);
        update_post_meta($post_id, 'ai_optimizer_processed_date', $item['collection_date']);
        update_post_meta($post_id, 'ai_optimizer_ai_rewritten', true);
        
        if (isset($item['seo_optimized'])) {
            update_post_meta($post_id, 'ai_optimizer_seo_optimized', $item['seo_optimized']);
        }
    }
    
    /**
     * Get collection statistics
     */
    public function get_collection_stats() {
        global $wpdb;
        
        $stats = $wpdb->get_row(
            "SELECT 
                COUNT(*) as total_collected,
                COUNT(CASE WHEN post_status = 'publish' THEN 1 END) as published,
                COUNT(CASE WHEN post_status = 'draft' THEN 1 END) as drafts
            FROM {$wpdb->posts} p
            INNER JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id
            WHERE pm.meta_key = 'ai_optimizer_processed_by_ai'
            AND pm.meta_value = '1'
            AND p.post_date >= DATE_SUB(NOW(), INTERVAL 30 DAY)",
            ARRAY_A
        );
        
        return $stats ?: array(
            'total_collected' => 0,
            'published' => 0,
            'drafts' => 0
        );
    }
}
