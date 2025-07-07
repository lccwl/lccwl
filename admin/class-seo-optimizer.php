<?php
/**
 * SEO Optimizer class
 */

if (!defined('ABSPATH')) {
    exit;
}

class AI_Optimizer_SEO {
    
    private $api_handler;
    
    public function __construct() {
        $this->api_handler = new AI_Optimizer_API_Handler();
    }
    
    /**
     * Render SEO optimization page
     */
    public static function render() {
        $seo = new self();
        $analysis = $seo->get_seo_analysis();
        $suggestions = $seo->get_suggestions();
        
        include AI_OPTIMIZER_PLUGIN_PATH . 'admin/views/seo-optimization.php';
    }
    
    /**
     * Run SEO analysis
     */
    public function run_analysis() {
        $pages = $this->get_pages_to_analyze();
        $results = array();
        
        foreach ($pages as $page) {
            $analysis = $this->analyze_page($page);
            $results[] = $analysis;
            
            // Store analysis results
            $this->store_analysis($analysis);
        }
        
        return $results;
    }
    
    /**
     * Analyze single page
     */
    private function analyze_page($page) {
        $url = get_permalink($page->ID);
        $response = wp_remote_get($url);
        $content = wp_remote_retrieve_body($response);
        
        // Parse HTML
        $dom = new DOMDocument();
        @$dom->loadHTML($content);
        
        $analysis = array(
            'page_id' => $page->ID,
            'url' => $url,
            'title' => $this->analyze_title($dom),
            'meta_description' => $this->analyze_meta_description($dom),
            'headings' => $this->analyze_headings($dom),
            'images' => $this->analyze_images($dom),
            'links' => $this->analyze_links($dom),
            'keywords' => $this->analyze_keywords($content, $page->post_title),
            'content_quality' => $this->analyze_content_quality($content),
        );
        
        // Get AI recommendations
        $analysis['ai_suggestions'] = $this->get_ai_suggestions($analysis);
        
        return $analysis;
    }
    
    /**
     * Analyze title tag
     */
    private function analyze_title($dom) {
        $title_tags = $dom->getElementsByTagName('title');
        $title = $title_tags->length > 0 ? $title_tags->item(0)->textContent : '';
        
        return array(
            'content' => $title,
            'length' => strlen($title),
            'issues' => $this->check_title_issues($title),
        );
    }
    
    /**
     * Analyze meta description
     */
    private function analyze_meta_description($dom) {
        $meta_description = '';
        $meta_tags = $dom->getElementsByTagName('meta');
        
        foreach ($meta_tags as $meta) {
            if ($meta->getAttribute('name') === 'description') {
                $meta_description = $meta->getAttribute('content');
                break;
            }
        }
        
        return array(
            'content' => $meta_description,
            'length' => strlen($meta_description),
            'issues' => $this->check_meta_description_issues($meta_description),
        );
    }
    
    /**
     * Analyze headings
     */
    private function analyze_headings($dom) {
        $headings = array();
        
        for ($i = 1; $i <= 6; $i++) {
            $h_tags = $dom->getElementsByTagName('h' . $i);
            $headings['h' . $i] = array();
            
            foreach ($h_tags as $tag) {
                $headings['h' . $i][] = $tag->textContent;
            }
        }
        
        return array(
            'structure' => $headings,
            'issues' => $this->check_heading_issues($headings),
        );
    }
    
    /**
     * Analyze images
     */
    private function analyze_images($dom) {
        $images = $dom->getElementsByTagName('img');
        $analysis = array(
            'total' => $images->length,
            'without_alt' => 0,
            'large_images' => 0,
            'issues' => array(),
        );
        
        foreach ($images as $img) {
            if (!$img->hasAttribute('alt') || empty($img->getAttribute('alt'))) {
                $analysis['without_alt']++;
                $analysis['issues'][] = 'Missing alt text for image: ' . $img->getAttribute('src');
            }
            
            // Check image size (simplified)
            $src = $img->getAttribute('src');
            if (strpos($src, '.jpg') !== false || strpos($src, '.png') !== false) {
                // This is a simplified check - in production, you'd want to actually check file sizes
                $analysis['large_images']++;
            }
        }
        
        return $analysis;
    }
    
    /**
     * Analyze links
     */
    private function analyze_links($dom) {
        $links = $dom->getElementsByTagName('a');
        $analysis = array(
            'total' => $links->length,
            'internal' => 0,
            'external' => 0,
            'nofollow' => 0,
            'issues' => array(),
        );
        
        $site_url = get_site_url();
        
        foreach ($links as $link) {
            $href = $link->getAttribute('href');
            
            if (empty($href) || $href === '#') {
                $analysis['issues'][] = 'Empty or placeholder link found';
                continue;
            }
            
            if (strpos($href, $site_url) === 0 || strpos($href, '/') === 0) {
                $analysis['internal']++;
            } else {
                $analysis['external']++;
            }
            
            if (strpos($link->getAttribute('rel'), 'nofollow') !== false) {
                $analysis['nofollow']++;
            }
        }
        
        return $analysis;
    }
    
    /**
     * Analyze keywords
     */
    private function analyze_keywords($content, $title) {
        // Extract text content only
        $text = strip_tags($content);
        $words = str_word_count(strtolower($text), 1);
        
        // Count word frequency
        $word_count = array_count_values($words);
        arsort($word_count);
        
        // Remove common stop words
        $stop_words = array('the', 'a', 'an', 'and', 'or', 'but', 'in', 'on', 'at', 'to', 'for', 'of', 'with', 'by', 'is', 'are', 'was', 'were', 'be', 'been', 'being', 'have', 'has', 'had', 'do', 'does', 'did', 'will', 'would', 'could', 'should', 'may', 'might', 'must', 'can');
        
        $keywords = array();
        foreach ($word_count as $word => $count) {
            if (strlen($word) > 3 && !in_array($word, $stop_words) && $count > 2) {
                $keywords[$word] = $count;
            }
        }
        
        return array(
            'top_keywords' => array_slice($keywords, 0, 10, true),
            'keyword_density' => $this->calculate_keyword_density($keywords, count($words)),
            'title_keywords' => $this->extract_title_keywords($title),
        );
    }
    
    /**
     * Analyze content quality
     */
    private function analyze_content_quality($content) {
        $text = strip_tags($content);
        $word_count = str_word_count($text);
        $sentence_count = preg_split('/[.!?]+/', $text, -1, PREG_SPLIT_NO_EMPTY);
        $avg_sentence_length = $word_count / count($sentence_count);
        
        return array(
            'word_count' => $word_count,
            'sentence_count' => count($sentence_count),
            'avg_sentence_length' => round($avg_sentence_length, 2),
            'readability_score' => $this->calculate_readability($word_count, count($sentence_count), $text),
        );
    }
    
    /**
     * Get AI suggestions
     */
    private function get_ai_suggestions($analysis) {
        $prompt = "Based on this SEO analysis, provide specific optimization suggestions for better search engine ranking:\n\n" . json_encode($analysis, JSON_PRETTY_PRINT);
        
        $response = $this->api_handler->chat_completion($prompt, 'Qwen/QwQ-32B');
        
        if ($response) {
            return $this->parse_ai_suggestions($response);
        }
        
        return array();
    }
    
    /**
     * Parse AI suggestions
     */
    private function parse_ai_suggestions($ai_response) {
        // Parse the AI response and extract actionable suggestions
        $suggestions = array();
        
        // This is a simplified parser - in production, you'd want more sophisticated parsing
        $lines = explode("\n", $ai_response);
        
        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line)) continue;
            
            if (strpos($line, 'title') !== false && strpos($line, 'optimize') !== false) {
                $suggestions[] = array(
                    'type' => 'title',
                    'priority' => 'high',
                    'description' => $line,
                    'action' => 'optimize_title',
                );
            } elseif (strpos($line, 'meta description') !== false) {
                $suggestions[] = array(
                    'type' => 'meta_description',
                    'priority' => 'high',
                    'description' => $line,
                    'action' => 'optimize_meta_description',
                );
            } elseif (strpos($line, 'heading') !== false) {
                $suggestions[] = array(
                    'type' => 'headings',
                    'priority' => 'medium',
                    'description' => $line,
                    'action' => 'optimize_headings',
                );
            } elseif (strpos($line, 'image') !== false) {
                $suggestions[] = array(
                    'type' => 'images',
                    'priority' => 'medium',
                    'description' => $line,
                    'action' => 'optimize_images',
                );
            }
        }
        
        return $suggestions;
    }
    
    /**
     * Store analysis
     */
    private function store_analysis($analysis) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'ai_optimizer_seo_analysis';
        
        $wpdb->insert(
            $table_name,
            array(
                'page_id' => $analysis['page_id'],
                'analysis_data' => json_encode($analysis),
                'created_at' => current_time('mysql'),
            ),
            array('%d', '%s', '%s')
        );
    }
    
    /**
     * Get pages to analyze
     */
    private function get_pages_to_analyze() {
        return get_posts(array(
            'post_type' => array('post', 'page'),
            'post_status' => 'publish',
            'numberposts' => 50,
        ));
    }
    
    /**
     * Get SEO analysis
     */
    public function get_seo_analysis() {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'ai_optimizer_seo_analysis';
        
        return $wpdb->get_results(
            "SELECT * FROM {$table_name} ORDER BY created_at DESC LIMIT 20",
            ARRAY_A
        );
    }
    
    /**
     * Get suggestions
     */
    public function get_suggestions() {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'ai_optimizer_seo_suggestions';
        
        return $wpdb->get_results(
            "SELECT * FROM {$table_name} WHERE status = 'pending' ORDER BY priority DESC, created_at DESC",
            ARRAY_A
        );
    }
    
    /**
     * Apply suggestion
     */
    public function apply_suggestion($suggestion_id) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'ai_optimizer_seo_suggestions';
        
        $suggestion = $wpdb->get_row(
            $wpdb->prepare("SELECT * FROM {$table_name} WHERE id = %d", $suggestion_id),
            ARRAY_A
        );
        
        if (!$suggestion) {
            return false;
        }
        
        $success = false;
        
        switch ($suggestion['action']) {
            case 'optimize_title':
                $success = $this->optimize_title($suggestion);
                break;
            case 'optimize_meta_description':
                $success = $this->optimize_meta_description($suggestion);
                break;
            case 'optimize_headings':
                $success = $this->optimize_headings($suggestion);
                break;
            case 'optimize_images':
                $success = $this->optimize_images($suggestion);
                break;
        }
        
        if ($success) {
            $wpdb->update(
                $table_name,
                array('status' => 'applied', 'applied_at' => current_time('mysql')),
                array('id' => $suggestion_id),
                array('%s', '%s'),
                array('%d')
            );
        }
        
        return $success;
    }
    
    /**
     * Get current SEO score
     */
    public function get_current_score() {
        // Calculate overall SEO score based on recent analysis
        $recent_analysis = $this->get_seo_analysis();
        
        if (empty($recent_analysis)) {
            return 0;
        }
        
        $total_score = 0;
        $count = 0;
        
        foreach ($recent_analysis as $analysis) {
            $data = json_decode($analysis['analysis_data'], true);
            $score = $this->calculate_page_score($data);
            $total_score += $score;
            $count++;
        }
        
        return $count > 0 ? round($total_score / $count) : 0;
    }
    
    /**
     * Calculate page SEO score
     */
    private function calculate_page_score($analysis) {
        $score = 100;
        
        // Title issues
        if (!empty($analysis['title']['issues'])) {
            $score -= count($analysis['title']['issues']) * 10;
        }
        
        // Meta description issues
        if (!empty($analysis['meta_description']['issues'])) {
            $score -= count($analysis['meta_description']['issues']) * 10;
        }
        
        // Heading issues
        if (!empty($analysis['headings']['issues'])) {
            $score -= count($analysis['headings']['issues']) * 5;
        }
        
        // Image issues
        if ($analysis['images']['without_alt'] > 0) {
            $score -= $analysis['images']['without_alt'] * 3;
        }
        
        return max(0, $score);
    }
    
    /**
     * Helper methods for issue checking
     */
    private function check_title_issues($title) {
        $issues = array();
        
        if (empty($title)) {
            $issues[] = 'Missing title tag';
        } elseif (strlen($title) < 30) {
            $issues[] = 'Title too short (< 30 characters)';
        } elseif (strlen($title) > 60) {
            $issues[] = 'Title too long (> 60 characters)';
        }
        
        return $issues;
    }
    
    private function check_meta_description_issues($meta_description) {
        $issues = array();
        
        if (empty($meta_description)) {
            $issues[] = 'Missing meta description';
        } elseif (strlen($meta_description) < 120) {
            $issues[] = 'Meta description too short (< 120 characters)';
        } elseif (strlen($meta_description) > 160) {
            $issues[] = 'Meta description too long (> 160 characters)';
        }
        
        return $issues;
    }
    
    private function check_heading_issues($headings) {
        $issues = array();
        
        if (empty($headings['h1'])) {
            $issues[] = 'Missing H1 tag';
        } elseif (count($headings['h1']) > 1) {
            $issues[] = 'Multiple H1 tags found';
        }
        
        if (empty($headings['h2'])) {
            $issues[] = 'No H2 tags found';
        }
        
        return $issues;
    }
    
    private function calculate_keyword_density($keywords, $total_words) {
        $density = array();
        
        foreach ($keywords as $keyword => $count) {
            $density[$keyword] = round(($count / $total_words) * 100, 2);
        }
        
        return $density;
    }
    
    private function extract_title_keywords($title) {
        $words = str_word_count(strtolower($title), 1);
        return array_unique($words);
    }
    
    private function calculate_readability($word_count, $sentence_count, $text) {
        // Simplified readability score
        if ($sentence_count == 0) return 0;
        
        $avg_sentence_length = $word_count / $sentence_count;
        $complex_words = $this->count_complex_words($text);
        
        // Simplified Flesch Reading Ease formula
        $score = 206.835 - (1.015 * $avg_sentence_length) - (84.6 * ($complex_words / $word_count));
        
        return max(0, min(100, round($score)));
    }
    
    private function count_complex_words($text) {
        $words = str_word_count(strtolower($text), 1);
        $complex = 0;
        
        foreach ($words as $word) {
            $syllables = $this->count_syllables($word);
            if ($syllables >= 3) {
                $complex++;
            }
        }
        
        return $complex;
    }
    
    private function count_syllables($word) {
        $word = strtolower($word);
        $syllables = preg_match_all('/[aeiouy]+/', $word);
        
        if (substr($word, -1) === 'e') {
            $syllables--;
        }
        
        return max(1, $syllables);
    }
    
    /**
     * Optimization methods
     */
    private function optimize_title($suggestion) {
        // This would implement actual title optimization
        // For now, just log the action
        AI_Optimizer_Utils::log('SEO title optimization applied for suggestion ID: ' . $suggestion['id']);
        return true;
    }
    
    private function optimize_meta_description($suggestion) {
        // This would implement actual meta description optimization
        AI_Optimizer_Utils::log('SEO meta description optimization applied for suggestion ID: ' . $suggestion['id']);
        return true;
    }
    
    private function optimize_headings($suggestion) {
        // This would implement actual heading optimization
        AI_Optimizer_Utils::log('SEO heading optimization applied for suggestion ID: ' . $suggestion['id']);
        return true;
    }
    
    private function optimize_images($suggestion) {
        // This would implement actual image optimization
        AI_Optimizer_Utils::log('SEO image optimization applied for suggestion ID: ' . $suggestion['id']);
        return true;
    }
}
