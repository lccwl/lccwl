<?php
/**
 * 前端公共类
 */

if (!defined('ABSPATH')) {
    exit;
}

class AI_Optimizer_Public {
    
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
        // 只在启用前端监控时加载
        if (get_option('ai_optimizer_frontend_monitoring', false)) {
            add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
            add_action('wp_footer', array($this, 'add_monitoring_script'));
        }
        
        // 性能优化钩子
        add_action('wp_head', array($this, 'add_optimization_meta'), 1);
        add_filter('wp_head', array($this, 'optimize_head'));
        
        // SEO优化钩子
        if (get_option('ai_optimizer_seo_auto_optimize', false)) {
            add_filter('wp_title', array($this, 'optimize_title'));
            add_action('wp_head', array($this, 'add_seo_meta'));
        }
    }
    
    /**
     * 加载前端脚本
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
            'trackingEnabled' => get_option('ai_optimizer_frontend_monitoring', false),
        ));
    }
    
    /**
     * 添加监控脚本
     */
    public function add_monitoring_script() {
        ?>
        <script type="text/javascript">
        // 性能监控
        if (window.performance && window.performance.timing) {
            window.addEventListener('load', function() {
                setTimeout(function() {
                    var timing = window.performance.timing;
                    var navigation = window.performance.navigation;
                    
                    var performanceData = {
                        url: window.location.href,
                        pageLoadTime: timing.loadEventEnd - timing.navigationStart,
                        domContentLoaded: timing.domContentLoadedEventEnd - timing.navigationStart,
                        firstContentfulPaint: 0,
                        largestContentfulPaint: 0,
                        cumulativeLayoutShift: 0,
                        firstInputDelay: 0,
                        viewportWidth: window.innerWidth,
                        viewportHeight: window.innerHeight,
                        connectionType: navigator.connection ? navigator.connection.effectiveType : 'unknown'
                    };
                    
                    // 获取Core Web Vitals
                    if ('PerformanceObserver' in window) {
                        // FCP
                        try {
                            new PerformanceObserver(function(list) {
                                for (var entry of list.getEntries()) {
                                    if (entry.name === 'first-contentful-paint') {
                                        performanceData.firstContentfulPaint = entry.startTime;
                                    }
                                }
                            }).observe({entryTypes: ['paint']});
                        } catch(e) {}
                        
                        // LCP
                        try {
                            new PerformanceObserver(function(list) {
                                var entries = list.getEntries();
                                var lastEntry = entries[entries.length - 1];
                                performanceData.largestContentfulPaint = lastEntry.startTime;
                            }).observe({entryTypes: ['largest-contentful-paint']});
                        } catch(e) {}
                        
                        // CLS
                        try {
                            var clsValue = 0;
                            new PerformanceObserver(function(list) {
                                for (var entry of list.getEntries()) {
                                    if (!entry.hadRecentInput) {
                                        clsValue += entry.value;
                                        performanceData.cumulativeLayoutShift = clsValue;
                                    }
                                }
                            }).observe({entryTypes: ['layout-shift']});
                        } catch(e) {}
                        
                        // FID
                        try {
                            new PerformanceObserver(function(list) {
                                for (var entry of list.getEntries()) {
                                    performanceData.firstInputDelay = entry.processingStart - entry.startTime;
                                    break;
                                }
                            }).observe({entryTypes: ['first-input']});
                        } catch(e) {}
                    }
                    
                    // 发送数据
                    if (window.aiOptimizerPublic && window.aiOptimizerPublic.trackingEnabled) {
                        jQuery.post(window.aiOptimizerPublic.ajaxUrl, {
                            action: 'ai_optimizer_track',
                            type: 'performance',
                            data: performanceData,
                            nonce: window.aiOptimizerPublic.nonce
                        });
                    }
                }, 2000);
            });
        }
        
        // 错误监控
        window.addEventListener('error', function(event) {
            if (window.aiOptimizerPublic && window.aiOptimizerPublic.trackingEnabled) {
                var errorData = {
                    url: window.location.href,
                    message: event.message,
                    filename: event.filename,
                    lineno: event.lineno,
                    colno: event.colno,
                    stack: event.error ? event.error.stack : '',
                    browserInfo: {
                        userAgent: navigator.userAgent,
                        language: navigator.language,
                        platform: navigator.platform,
                        cookieEnabled: navigator.cookieEnabled
                    }
                };
                
                jQuery.post(window.aiOptimizerPublic.ajaxUrl, {
                    action: 'ai_optimizer_track',
                    type: 'error',
                    data: errorData,
                    nonce: window.aiOptimizerPublic.nonce
                });
            }
        });
        
        // Promise错误监控
        window.addEventListener('unhandledrejection', function(event) {
            if (window.aiOptimizerPublic && window.aiOptimizerPublic.trackingEnabled) {
                var errorData = {
                    url: window.location.href,
                    message: 'Unhandled Promise Rejection: ' + event.reason,
                    filename: '',
                    lineno: 0,
                    colno: 0,
                    stack: event.reason ? event.reason.stack : '',
                    browserInfo: {
                        userAgent: navigator.userAgent,
                        language: navigator.language,
                        platform: navigator.platform,
                        cookieEnabled: navigator.cookieEnabled
                    }
                };
                
                jQuery.post(window.aiOptimizerPublic.ajaxUrl, {
                    action: 'ai_optimizer_track',
                    type: 'error',
                    data: errorData,
                    nonce: window.aiOptimizerPublic.nonce
                });
            }
        });
        </script>
        <?php
    }
    
    /**
     * 添加优化元标签
     */
    public function add_optimization_meta() {
        // DNS预取
        echo '<link rel="dns-prefetch" href="//fonts.googleapis.com">' . "\n";
        echo '<link rel="dns-prefetch" href="//ajax.googleapis.com">' . "\n";
        echo '<link rel="dns-prefetch" href="//cdnjs.cloudflare.com">' . "\n";
        
        // 预连接
        echo '<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>' . "\n";
        
        // 资源提示
        if (is_home() || is_front_page()) {
            echo '<link rel="preload" href="' . get_stylesheet_uri() . '" as="style">' . "\n";
        }
    }
    
    /**
     * 优化头部
     */
    public function optimize_head() {
        // 移除不必要的链接
        remove_action('wp_head', 'wp_generator');
        remove_action('wp_head', 'wlwmanifest_link');
        remove_action('wp_head', 'rsd_link');
        remove_action('wp_head', 'wp_shortlink_wp_head');
        
        // 移除emoji脚本（如果禁用）
        if (get_option('ai_optimizer_disable_emojis', false)) {
            remove_action('wp_head', 'print_emoji_detection_script', 7);
            remove_action('wp_print_styles', 'print_emoji_styles');
            remove_action('admin_print_scripts', 'print_emoji_detection_script');
            remove_action('admin_print_styles', 'print_emoji_styles');
        }
    }
    
    /**
     * 优化标题
     */
    public function optimize_title($title) {
        if (is_home() || is_front_page()) {
            $optimized_title = get_option('ai_optimizer_home_title');
            if (!empty($optimized_title)) {
                return $optimized_title;
            }
        }
        
        return $title;
    }
    
    /**
     * 添加SEO元标签
     */
    public function add_seo_meta() {
        global $post;
        
        // Meta描述
        $meta_description = '';
        
        if (is_home() || is_front_page()) {
            $meta_description = get_option('ai_optimizer_home_description');
        } elseif (is_single() || is_page()) {
            $meta_description = get_post_meta($post->ID, 'ai_optimizer_meta_description', true);
            if (empty($meta_description)) {
                $meta_description = wp_trim_words(strip_tags($post->post_content), 25);
            }
        } elseif (is_category()) {
            $category = get_queried_object();
            $meta_description = $category->description;
        }
        
        if (!empty($meta_description)) {
            echo '<meta name="description" content="' . esc_attr($meta_description) . '">' . "\n";
        }
        
        // Meta关键词
        if (is_single() || is_page()) {
            $meta_keywords = get_post_meta($post->ID, 'ai_optimizer_meta_keywords', true);
            if (!empty($meta_keywords)) {
                echo '<meta name="keywords" content="' . esc_attr($meta_keywords) . '">' . "\n";
            }
        }
        
        // Open Graph标签
        if (is_single() || is_page()) {
            echo '<meta property="og:title" content="' . esc_attr(get_the_title()) . '">' . "\n";
            echo '<meta property="og:description" content="' . esc_attr($meta_description) . '">' . "\n";
            echo '<meta property="og:url" content="' . esc_attr(get_permalink()) . '">' . "\n";
            echo '<meta property="og:type" content="article">' . "\n";
            
            if (has_post_thumbnail()) {
                $image = wp_get_attachment_image_src(get_post_thumbnail_id(), 'large');
                echo '<meta property="og:image" content="' . esc_attr($image[0]) . '">' . "\n";
            }
        }
        
        // Twitter卡片
        echo '<meta name="twitter:card" content="summary_large_image">' . "\n";
        
        // 结构化数据
        $this->add_structured_data();
    }
    
    /**
     * 添加结构化数据
     */
    private function add_structured_data() {
        global $post;
        
        if (is_single() && get_post_type() === 'post') {
            $schema = array(
                '@context' => 'https://schema.org',
                '@type' => 'Article',
                'headline' => get_the_title(),
                'datePublished' => get_the_date('c'),
                'dateModified' => get_the_modified_date('c'),
                'author' => array(
                    '@type' => 'Person',
                    'name' => get_the_author()
                ),
                'publisher' => array(
                    '@type' => 'Organization',
                    'name' => get_bloginfo('name'),
                    'logo' => array(
                        '@type' => 'ImageObject',
                        'url' => get_site_icon_url()
                    )
                )
            );
            
            if (has_post_thumbnail()) {
                $image = wp_get_attachment_image_src(get_post_thumbnail_id(), 'large');
                $schema['image'] = array(
                    '@type' => 'ImageObject',
                    'url' => $image[0],
                    'width' => $image[1],
                    'height' => $image[2]
                );
            }
            
            echo '<script type="application/ld+json">' . wp_json_encode($schema) . '</script>' . "\n";
        }
        
        if (is_home() || is_front_page()) {
            $schema = array(
                '@context' => 'https://schema.org',
                '@type' => 'WebSite',
                'name' => get_bloginfo('name'),
                'description' => get_bloginfo('description'),
                'url' => home_url(),
                'potentialAction' => array(
                    '@type' => 'SearchAction',
                    'target' => home_url('/?s={search_term_string}'),
                    'query-input' => 'required name=search_term_string'
                )
            );
            
            echo '<script type="application/ld+json">' . wp_json_encode($schema) . '</script>' . "\n";
        }
    }
    
    /**
     * 优化图片延迟加载
     */
    public function optimize_images($content) {
        if (!get_option('ai_optimizer_lazy_load_images', false)) {
            return $content;
        }
        
        // 添加lazy loading属性
        $content = preg_replace('/<img(.*?)src=/i', '<img$1loading="lazy" src=', $content);
        
        return $content;
    }
    
    /**
     * 压缩HTML输出
     */
    public function compress_html($html) {
        if (!get_option('ai_optimizer_compress_html', false)) {
            return $html;
        }
        
        // 移除HTML注释（保留条件注释）
        $html = preg_replace('/<!--(?!\s*(?:\[if [^\]]+]|<!|>))(?:(?!-->).)*-->/s', '', $html);
        
        // 移除多余空白
        $html = preg_replace('/\s+/', ' ', $html);
        $html = preg_replace('/>\s+</', '><', $html);
        
        return trim($html);
    }
    
    /**
     * 获取页面性能评分
     */
    public function get_page_performance_score() {
        global $wpdb;
        
        $current_url = (is_ssl() ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
        
        $table_name = $wpdb->prefix . 'ai_optimizer_frontend_performance';
        
        $avg_metrics = $wpdb->get_row($wpdb->prepare(
            "SELECT 
                AVG(page_load_time) as avg_load_time,
                AVG(first_contentful_paint) as avg_fcp,
                AVG(largest_contentful_paint) as avg_lcp,
                AVG(cumulative_layout_shift) as avg_cls,
                AVG(first_input_delay) as avg_fid
             FROM $table_name 
             WHERE url = %s 
             AND created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)",
            $current_url
        ));
        
        if (!$avg_metrics) {
            return null;
        }
        
        // 计算性能评分
        $score = 100;
        
        // 加载时间评分 (权重: 30%)
        if ($avg_metrics->avg_load_time > 4000) {
            $score -= 30;
        } elseif ($avg_metrics->avg_load_time > 2500) {
            $score -= 15;
        } elseif ($avg_metrics->avg_load_time > 1500) {
            $score -= 5;
        }
        
        // LCP评分 (权重: 25%)
        if ($avg_metrics->avg_lcp > 4000) {
            $score -= 25;
        } elseif ($avg_metrics->avg_lcp > 2500) {
            $score -= 12;
        }
        
        // FCP评分 (权重: 20%)
        if ($avg_metrics->avg_fcp > 3000) {
            $score -= 20;
        } elseif ($avg_metrics->avg_fcp > 1800) {
            $score -= 10;
        }
        
        // CLS评分 (权重: 15%)
        if ($avg_metrics->avg_cls > 0.25) {
            $score -= 15;
        } elseif ($avg_metrics->avg_cls > 0.1) {
            $score -= 8;
        }
        
        // FID评分 (权重: 10%)
        if ($avg_metrics->avg_fid > 300) {
            $score -= 10;
        } elseif ($avg_metrics->avg_fid > 100) {
            $score -= 5;
        }
        
        return max(0, $score);
    }
    
    /**
     * 添加性能提示
     */
    public function add_performance_hints() {
        if (!current_user_can('manage_options') || !get_option('ai_optimizer_show_performance_hints', false)) {
            return;
        }
        
        $score = $this->get_page_performance_score();
        
        if ($score !== null && $score < 80) {
            echo '<div style="position: fixed; top: 32px; right: 20px; background: #ff6b6b; color: white; padding: 10px; border-radius: 5px; z-index: 999999; font-size: 12px;">';
            echo '页面性能评分: ' . round($score) . '/100';
            echo '</div>';
        }
    }
}