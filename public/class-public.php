<?php
/**
 * Public-facing functionality of the plugin
 */

if (!defined('ABSPATH')) {
    exit;
}

class AI_Optimizer_Public {
    
    private static $instance = null;
    private $monitor;
    
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
        // Only initialize if frontend monitoring is enabled
        if (AI_Optimizer_Settings::get('frontend_monitoring', false)) {
            add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
            add_action('wp_footer', array($this, 'render_performance_tracker'));
            add_action('wp_ajax_ai_optimizer_frontend', array($this, 'handle_frontend_data'));
            add_action('wp_ajax_nopriv_ai_optimizer_frontend', array($this, 'handle_frontend_data'));
        }
        
        // SEO optimization hooks
        add_action('wp_head', array($this, 'add_seo_optimizations'));
        add_filter('the_content', array($this, 'optimize_content_output'));
        add_filter('wp_title', array($this, 'optimize_title'), 10, 3);
        add_filter('document_title_parts', array($this, 'optimize_document_title'));
        
        // Performance optimization hooks
        add_action('wp_head', array($this, 'add_performance_optimizations'));
        add_filter('script_loader_tag', array($this, 'add_async_defer_attributes'), 10, 2);
        add_filter('style_loader_tag', array($this, 'optimize_css_loading'), 10, 2);
        
        // Content enhancement hooks
        add_filter('the_content', array($this, 'enhance_content_with_ai'));
        add_action('save_post', array($this, 'analyze_new_content'));
        
        $this->monitor = new AI_Optimizer_Monitor();
    }
    
    /**
     * Enqueue public-facing scripts and styles
     */
    public function enqueue_scripts() {
        wp_enqueue_script(
            'ai-optimizer-frontend',
            AI_OPTIMIZER_PLUGIN_URL . 'public/assets/js/frontend.js',
            array('jquery'),
            AI_OPTIMIZER_VERSION,
            true
        );
        
        wp_localize_script('ai-optimizer-frontend', 'aiOptimizerPublic', array(
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('ai_optimizer_frontend_nonce'),
            'settings' => array(
                'trackingEnabled' => AI_Optimizer_Settings::get('frontend_monitoring', false),
                'performanceTracking' => true,
                'errorTracking' => true,
                'userBehaviorTracking' => false
            )
        ));
        
        wp_enqueue_style(
            'ai-optimizer-frontend',
            AI_OPTIMIZER_PLUGIN_URL . 'public/assets/css/frontend.css',
            array(),
            AI_OPTIMIZER_VERSION
        );
    }
    
    /**
     * Render performance tracking code
     */
    public function render_performance_tracker() {
        if (!AI_Optimizer_Settings::get('frontend_monitoring', false)) {
            return;
        }
        ?>
        <script type="text/javascript">
        (function() {
            // Performance tracking
            if (window.performance && window.performance.timing) {
                window.addEventListener('load', function() {
                    setTimeout(function() {
                        var timing = window.performance.timing;
                        var data = {
                            action: 'ai_optimizer_frontend',
                            type: 'performance',
                            data: {
                                loadTime: timing.loadEventEnd - timing.navigationStart,
                                domContentLoaded: timing.domContentLoadedEventEnd - timing.navigationStart,
                                firstPaint: window.performance.getEntriesByType ? 
                                    (window.performance.getEntriesByType('paint')[0] ? 
                                     window.performance.getEntriesByType('paint')[0].startTime : 0) : 0,
                                url: window.location.href,
                                userAgent: navigator.userAgent,
                                timestamp: new Date().toISOString()
                            },
                            nonce: aiOptimizerPublic.nonce
                        };
                        
                        // Send data via AJAX
                        jQuery.post(aiOptimizerPublic.ajaxUrl, data);
                    }, 1000);
                });
            }
            
            // Error tracking
            window.addEventListener('error', function(e) {
                var data = {
                    action: 'ai_optimizer_frontend',
                    type: 'javascript_error',
                    data: {
                        message: e.message,
                        filename: e.filename,
                        lineno: e.lineno,
                        colno: e.colno,
                        stack: e.error ? e.error.stack : '',
                        url: window.location.href,
                        timestamp: new Date().toISOString()
                    },
                    nonce: aiOptimizerPublic.nonce
                };
                
                jQuery.post(aiOptimizerPublic.ajaxUrl, data);
            });
            
            // Resource error tracking
            window.addEventListener('error', function(e) {
                if (e.target !== window) {
                    var data = {
                        action: 'ai_optimizer_frontend',
                        type: 'resource_error',
                        data: {
                            element: e.target.tagName,
                            source: e.target.src || e.target.href,
                            url: window.location.href,
                            timestamp: new Date().toISOString()
                        },
                        nonce: aiOptimizerPublic.nonce
                    };
                    
                    jQuery.post(aiOptimizerPublic.ajaxUrl, data);
                }
            }, true);
        })();
        </script>
        <?php
    }
    
    /**
     * Handle frontend data collection
     */
    public function handle_frontend_data() {
        if (!wp_verify_nonce($_POST['nonce'], 'ai_optimizer_frontend_nonce')) {
            wp_die(__('Security check failed', 'ai-website-optimizer'));
        }
        
        $type = sanitize_text_field($_POST['type'] ?? '');
        $data = $_POST['data'] ?? array();
        
        // Sanitize the data array
        $sanitized_data = $this->sanitize_frontend_data($data);
        
        switch ($type) {
            case 'performance':
                $this->store_performance_data($sanitized_data);
                break;
            case 'javascript_error':
                $this->store_error_data($sanitized_data);
                break;
            case 'resource_error':
                $this->store_resource_error_data($sanitized_data);
                break;
        }
        
        wp_send_json_success();
    }
    
    /**
     * Add SEO optimizations to head
     */
    public function add_seo_optimizations() {
        global $post;
        
        if (!is_singular()) {
            return;
        }
        
        // Get AI-generated meta description if available
        $ai_meta_description = get_post_meta($post->ID, 'ai_optimizer_meta_description', true);
        
        if ($ai_meta_description) {
            echo '<meta name="description" content="' . esc_attr($ai_meta_description) . '">' . "\n";
        }
        
        // Add structured data for better SEO
        $this->add_structured_data();
        
        // Add Open Graph tags
        $this->add_open_graph_tags();
        
        // Add Twitter Card tags
        $this->add_twitter_card_tags();
        
        // Add canonical URL
        $canonical_url = get_permalink($post->ID);
        echo '<link rel="canonical" href="' . esc_url($canonical_url) . '">' . "\n";
        
        // Add prev/next for paginated content
        $this->add_pagination_links();
    }
    
    /**
     * Add performance optimizations
     */
    public function add_performance_optimizations() {
        // Add DNS prefetch for external domains
        $external_domains = array(
            '//fonts.googleapis.com',
            '//fonts.gstatic.com',
            '//cdn.jsdelivr.net',
            '//cdnjs.cloudflare.com'
        );
        
        foreach ($external_domains as $domain) {
            echo '<link rel="dns-prefetch" href="' . esc_attr($domain) . '">' . "\n";
        }
        
        // Add preload for critical resources
        echo '<link rel="preload" as="style" href="' . get_stylesheet_uri() . '">' . "\n";
        
        // Add viewport meta tag if not present
        if (!has_action('wp_head', 'wp_site_icon')) {
            echo '<meta name="viewport" content="width=device-width, initial-scale=1">' . "\n";
        }
    }
    
    /**
     * Optimize content output
     */
    public function optimize_content_output($content) {
        if (!is_singular() || !in_the_loop() || !is_main_query()) {
            return $content;
        }
        
        // Apply AI content enhancements if available
        global $post;
        $enhanced_content = get_post_meta($post->ID, 'ai_optimizer_enhanced_content', true);
        
        if ($enhanced_content && AI_Optimizer_Settings::get('seo_auto_optimize', false)) {
            return $enhanced_content;
        }
        
        // Optimize images in content
        $content = $this->optimize_content_images($content);
        
        // Add internal linking suggestions
        $content = $this->add_internal_links($content);
        
        return $content;
    }
    
    /**
     * Optimize page title
     */
    public function optimize_title($title, $sep, $seplocation) {
        global $post;
        
        if (is_singular() && $post) {
            $ai_title = get_post_meta($post->ID, 'ai_optimizer_title', true);
            
            if ($ai_title && AI_Optimizer_Settings::get('seo_auto_optimize', false)) {
                return $ai_title;
            }
        }
        
        return $title;
    }
    
    /**
     * Optimize document title parts
     */
    public function optimize_document_title($title_parts) {
        global $post;
        
        if (is_singular() && $post) {
            $ai_title = get_post_meta($post->ID, 'ai_optimizer_title', true);
            
            if ($ai_title && AI_Optimizer_Settings::get('seo_auto_optimize', false)) {
                $title_parts['title'] = $ai_title;
            }
        }
        
        return $title_parts;
    }
    
    /**
     * Add async/defer attributes to scripts
     */
    public function add_async_defer_attributes($tag, $handle) {
        // Scripts that should be deferred
        $defer_scripts = array(
            'jquery',
            'ai-optimizer-frontend'
        );
        
        // Scripts that should be loaded async
        $async_scripts = array(
            'google-analytics',
            'gtag'
        );
        
        if (in_array($handle, $defer_scripts)) {
            return str_replace('<script ', '<script defer ', $tag);
        }
        
        if (in_array($handle, $async_scripts)) {
            return str_replace('<script ', '<script async ', $tag);
        }
        
        return $tag;
    }
    
    /**
     * Optimize CSS loading
     */
    public function optimize_css_loading($tag, $handle) {
        // Non-critical CSS that can be loaded asynchronously
        $async_styles = array(
            'dashicons',
            'admin-bar'
        );
        
        if (in_array($handle, $async_styles) && !is_admin()) {
            return str_replace("rel='stylesheet'", "rel='preload' as='style' onload=\"this.onload=null;this.rel='stylesheet'\"", $tag);
        }
        
        return $tag;
    }
    
    /**
     * Enhance content with AI
     */
    public function enhance_content_with_ai($content) {
        if (!is_singular() || is_admin()) {
            return $content;
        }
        
        global $post;
        
        // Check if content has been recently enhanced
        $last_enhanced = get_post_meta($post->ID, 'ai_optimizer_last_enhanced', true);
        
        if ($last_enhanced && (time() - strtotime($last_enhanced)) < DAY_IN_SECONDS) {
            return $content;
        }
        
        // Only enhance if auto-optimization is enabled
        if (!AI_Optimizer_Settings::get('seo_auto_optimize', false)) {
            return $content;
        }
        
        // Schedule content enhancement in background
        wp_schedule_single_event(time() + 60, 'ai_optimizer_enhance_content', array($post->ID));
        
        return $content;
    }
    
    /**
     * Analyze new content when posts are saved
     */
    public function analyze_new_content($post_id) {
        // Skip for autosaves and revisions
        if (wp_is_post_autosave($post_id) || wp_is_post_revision($post_id)) {
            return;
        }
        
        $post = get_post($post_id);
        
        // Only analyze published posts and pages
        if (!$post || $post->post_status !== 'publish' || !in_array($post->post_type, array('post', 'page'))) {
            return;
        }
        
        // Schedule SEO analysis
        wp_schedule_single_event(time() + 120, 'ai_optimizer_analyze_post_seo', array($post_id));
        
        AI_Optimizer_Utils::log('Scheduled SEO analysis for new content', 'info', array(
            'post_id' => $post_id,
            'post_title' => $post->post_title
        ));
    }
    
    /**
     * Private helper methods
     */
    private function sanitize_frontend_data($data) {
        $sanitized = array();
        
        foreach ($data as $key => $value) {
            if (is_string($value)) {
                $sanitized[$key] = sanitize_text_field($value);
            } elseif (is_numeric($value)) {
                $sanitized[$key] = floatval($value);
            } elseif (is_array($value)) {
                $sanitized[$key] = $this->sanitize_frontend_data($value);
            } else {
                $sanitized[$key] = $value;
            }
        }
        
        return $sanitized;
    }
    
    private function store_performance_data($data) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'ai_optimizer_frontend_performance';
        
        // Create table if it doesn't exist
        $this->create_frontend_performance_table();
        
        $wpdb->insert(
            $table_name,
            array(
                'url' => $data['url'] ?? '',
                'load_time' => $data['loadTime'] ?? 0,
                'dom_content_loaded' => $data['domContentLoaded'] ?? 0,
                'first_paint' => $data['firstPaint'] ?? 0,
                'user_agent' => $data['userAgent'] ?? '',
                'ip_address' => AI_Optimizer_Utils::get_client_ip(),
                'created_at' => current_time('mysql')
            ),
            array('%s', '%f', '%f', '%f', '%s', '%s', '%s')
        );
    }
    
    private function store_error_data($data) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'ai_optimizer_frontend_errors';
        
        // Create table if it doesn't exist
        $this->create_frontend_errors_table();
        
        $wpdb->insert(
            $table_name,
            array(
                'url' => $data['url'] ?? '',
                'error_message' => $data['message'] ?? '',
                'filename' => $data['filename'] ?? '',
                'line_number' => $data['lineno'] ?? 0,
                'column_number' => $data['colno'] ?? 0,
                'stack_trace' => $data['stack'] ?? '',
                'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
                'ip_address' => AI_Optimizer_Utils::get_client_ip(),
                'created_at' => current_time('mysql')
            ),
            array('%s', '%s', '%s', '%d', '%d', '%s', '%s', '%s', '%s')
        );
        
        // Log critical errors
        AI_Optimizer_Utils::log('Frontend JavaScript error detected', 'error', $data);
    }
    
    private function store_resource_error_data($data) {
        AI_Optimizer_Utils::log('Frontend resource error detected', 'warning', $data);
    }
    
    private function add_structured_data() {
        global $post;
        
        if (!is_singular()) {
            return;
        }
        
        $schema = array(
            '@context' => 'https://schema.org',
            '@type' => 'Article',
            'headline' => get_the_title($post->ID),
            'author' => array(
                '@type' => 'Person',
                'name' => get_the_author_meta('display_name', $post->post_author)
            ),
            'datePublished' => get_the_date('c', $post->ID),
            'dateModified' => get_the_modified_date('c', $post->ID),
            'url' => get_permalink($post->ID)
        );
        
        // Add featured image if available
        if (has_post_thumbnail($post->ID)) {
            $image = wp_get_attachment_image_src(get_post_thumbnail_id($post->ID), 'full');
            if ($image) {
                $schema['image'] = $image[0];
            }
        }
        
        echo '<script type="application/ld+json">' . json_encode($schema) . '</script>' . "\n";
    }
    
    private function add_open_graph_tags() {
        global $post;
        
        if (!is_singular()) {
            return;
        }
        
        echo '<meta property="og:type" content="article">' . "\n";
        echo '<meta property="og:title" content="' . esc_attr(get_the_title($post->ID)) . '">' . "\n";
        echo '<meta property="og:url" content="' . esc_url(get_permalink($post->ID)) . '">' . "\n";
        
        $excerpt = get_the_excerpt($post->ID);
        if ($excerpt) {
            echo '<meta property="og:description" content="' . esc_attr($excerpt) . '">' . "\n";
        }
        
        if (has_post_thumbnail($post->ID)) {
            $image = wp_get_attachment_image_src(get_post_thumbnail_id($post->ID), 'large');
            if ($image) {
                echo '<meta property="og:image" content="' . esc_url($image[0]) . '">' . "\n";
            }
        }
    }
    
    private function add_twitter_card_tags() {
        global $post;
        
        if (!is_singular()) {
            return;
        }
        
        echo '<meta name="twitter:card" content="summary_large_image">' . "\n";
        echo '<meta name="twitter:title" content="' . esc_attr(get_the_title($post->ID)) . '">' . "\n";
        
        $excerpt = get_the_excerpt($post->ID);
        if ($excerpt) {
            echo '<meta name="twitter:description" content="' . esc_attr($excerpt) . '">' . "\n";
        }
        
        if (has_post_thumbnail($post->ID)) {
            $image = wp_get_attachment_image_src(get_post_thumbnail_id($post->ID), 'large');
            if ($image) {
                echo '<meta name="twitter:image" content="' . esc_url($image[0]) . '">' . "\n";
            }
        }
    }
    
    private function add_pagination_links() {
        global $paged;
        
        if (is_singular() && $paged) {
            $prev_page = $paged - 1;
            $next_page = $paged + 1;
            
            if ($prev_page > 0) {
                echo '<link rel="prev" href="' . esc_url(get_pagenum_link($prev_page)) . '">' . "\n";
            }
            
            echo '<link rel="next" href="' . esc_url(get_pagenum_link($next_page)) . '">' . "\n";
        }
    }
    
    private function optimize_content_images($content) {
        // Add loading="lazy" to images
        $content = preg_replace('/<img(?![^>]*loading=)([^>]+)>/i', '<img loading="lazy"$1>', $content);
        
        return $content;
    }
    
    private function add_internal_links($content) {
        // Get target keywords for internal linking
        $target_keywords = AI_Optimizer_Settings::get('seo_target_keywords', '');
        
        if (empty($target_keywords)) {
            return $content;
        }
        
        $keywords_array = array_filter(array_map('trim', explode("\n", $target_keywords)));
        
        foreach ($keywords_array as $keyword) {
            // Find related posts for this keyword
            $related_posts = get_posts(array(
                'post_type' => 'post',
                'posts_per_page' => 1,
                's' => $keyword,
                'exclude' => array(get_the_ID())
            ));
            
            if (!empty($related_posts)) {
                $related_post = $related_posts[0];
                $link = '<a href="' . get_permalink($related_post->ID) . '">' . $keyword . '</a>';
                
                // Replace first occurrence of keyword with link
                $content = preg_replace('/\b' . preg_quote($keyword, '/') . '\b/i', $link, $content, 1);
            }
        }
        
        return $content;
    }
    
    private function create_frontend_performance_table() {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'ai_optimizer_frontend_performance';
        
        if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") === $table_name) {
            return;
        }
        
        $charset_collate = $wpdb->get_charset_collate();
        
        $sql = "CREATE TABLE $table_name (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            url varchar(500) NOT NULL,
            load_time float NOT NULL,
            dom_content_loaded float NOT NULL,
            first_paint float NOT NULL,
            user_agent text,
            ip_address varchar(45),
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY url (url(255)),
            KEY created_at (created_at)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }
    
    private function create_frontend_errors_table() {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'ai_optimizer_frontend_errors';
        
        if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") === $table_name) {
            return;
        }
        
        $charset_collate = $wpdb->get_charset_collate();
        
        $sql = "CREATE TABLE $table_name (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            url varchar(500) NOT NULL,
            error_message text NOT NULL,
            filename varchar(500),
            line_number int(11),
            column_number int(11),
            stack_trace longtext,
            user_agent text,
            ip_address varchar(45),
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY url (url(255)),
            KEY created_at (created_at)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }
}

// Register the enhancement hooks
add_action('ai_optimizer_enhance_content', array('AI_Optimizer_Content_Enhancer', 'enhance_post_content'));
add_action('ai_optimizer_analyze_post_seo', array('AI_Optimizer_SEO_Analyzer', 'analyze_post_seo'));

/**
 * Content Enhancer class for background processing
 */
class AI_Optimizer_Content_Enhancer {
    
    public static function enhance_post_content($post_id) {
        $post = get_post($post_id);
        
        if (!$post) {
            return;
        }
        
        $api_handler = new AI_Optimizer_API_Handler();
        
        $enhancement_prompt = "Enhance this content for better readability and SEO:\n\n";
        $enhancement_prompt .= "Title: {$post->post_title}\n";
        $enhancement_prompt .= "Content: " . wp_trim_words($post->post_content, 500) . "\n\n";
        $enhancement_prompt .= "Requirements:\n";
        $enhancement_prompt .= "- Improve readability and flow\n";
        $enhancement_prompt .= "- Add relevant headings (H2, H3)\n";
        $enhancement_prompt .= "- Optimize for search engines\n";
        $enhancement_prompt .= "- Maintain original meaning\n";
        $enhancement_prompt .= "- Return enhanced HTML content";
        
        $enhanced_content = $api_handler->chat_completion(
            $enhancement_prompt,
            'Qwen/QwQ-32B',
            'You are a content optimization expert specializing in SEO and readability improvements.'
        );
        
        if ($enhanced_content) {
            update_post_meta($post_id, 'ai_optimizer_enhanced_content', $enhanced_content);
            update_post_meta($post_id, 'ai_optimizer_last_enhanced', current_time('mysql'));
            
            AI_Optimizer_Utils::log('Content enhanced with AI', 'info', array(
                'post_id' => $post_id,
                'post_title' => $post->post_title
            ));
        }
    }
}

/**
 * SEO Analyzer class for background processing
 */
class AI_Optimizer_SEO_Analyzer {
    
    public static function analyze_post_seo($post_id) {
        $seo_optimizer = new AI_Optimizer_SEO();
        
        $post = get_post($post_id);
        if (!$post) {
            return;
        }
        
        // Analyze the post
        $analysis = $seo_optimizer->analyze_page((object) array('ID' => $post_id));
        
        // Generate AI recommendations
        if ($analysis && !empty($analysis['issues'])) {
            $api_handler = new AI_Optimizer_API_Handler();
            
            $recommendation_prompt = "Based on this SEO analysis, provide specific optimization recommendations:\n\n";
            $recommendation_prompt .= "Post: {$post->post_title}\n";
            $recommendation_prompt .= "Issues found: " . json_encode($analysis['issues']) . "\n\n";
            $recommendation_prompt .= "Provide actionable recommendations for improvement.";
            
            $recommendations = $api_handler->chat_completion(
                $recommendation_prompt,
                'Qwen/QwQ-32B',
                'You are an SEO expert providing actionable optimization recommendations.'
            );
            
            if ($recommendations) {
                update_post_meta($post_id, 'ai_optimizer_seo_recommendations', $recommendations);
                
                AI_Optimizer_Utils::log('SEO analysis completed for post', 'info', array(
                    'post_id' => $post_id,
                    'issues_found' => count($analysis['issues'])
                ));
            }
        }
    }
}
