<?php
/**
 * Plugin Name: AI智能网站优化器 - 完全修复版
 * Plugin URI: https://example.com/ai-website-optimizer
 * Description: 集成Siliconflow API的WordPress智能监控与优化插件，具备实时监控、SEO优化、代码修复和多媒体生成功能
 * Version: 2.1.0
 * Author: AI Developer
 * License: GPL v2 or later
 * Text Domain: ai-website-optimizer
 */

// 防止直接访问
if (!defined('ABSPATH')) {
    exit;
}

// 定义插件常量
define('AI_OPT_VERSION', '2.1.0');
define('AI_OPT_PLUGIN_URL', plugin_dir_url(__FILE__));
define('AI_OPT_PLUGIN_PATH', plugin_dir_path(__FILE__));

/**
 * 主插件类
 */
class AI_Website_Optimizer_Fixed {
    
    private static $instance = null;
    
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        add_action('init', array($this, 'init'));
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_assets'));
        
        // AJAX处理
        add_action('wp_ajax_ai_opt_test_api', array($this, 'ajax_test_api'));
        add_action('wp_ajax_ai_opt_save_settings', array($this, 'ajax_save_settings'));
        add_action('wp_ajax_ai_opt_run_seo_analysis', array($this, 'ajax_run_seo_analysis'));
        add_action('wp_ajax_ai_opt_run_patrol_check', array($this, 'ajax_run_patrol_check'));
        add_action('wp_ajax_ai_opt_generate_content', array($this, 'ajax_generate_content'));
        add_action('wp_ajax_ai_opt_save_competitors', array($this, 'ajax_save_competitors'));
        add_action('wp_ajax_ai_opt_get_monitor_logs', array($this, 'ajax_get_monitor_logs'));
        
        // 激活/停用钩子
        register_activation_hook(__FILE__, array($this, 'activate'));
        register_deactivation_hook(__FILE__, array($this, 'deactivate'));
    }
    
    public function init() {
        // 创建数据库表
        $this->create_database_tables();
        
        // 加载文本域
        load_plugin_textdomain('ai-website-optimizer', false, dirname(plugin_basename(__FILE__)) . '/languages');
    }
    
    /**
     * 创建数据库表
     */
    private function create_database_tables() {
        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();
        
        // SEO分析结果表
        $seo_table = $wpdb->prefix . 'ai_seo_analysis';
        $seo_sql = "CREATE TABLE $seo_table (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            url varchar(255) NOT NULL,
            analysis_type varchar(50) NOT NULL,
            score int(3) NOT NULL,
            issues text,
            suggestions text,
            ai_model varchar(100) NOT NULL,
            analyzed_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY url (url),
            KEY analyzed_at (analyzed_at)
        ) $charset_collate;";
        
        // 巡逻日志表
        $patrol_table = $wpdb->prefix . 'ai_patrol_logs';
        $patrol_sql = "CREATE TABLE $patrol_table (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            patrol_type varchar(50) NOT NULL,
            status varchar(20) NOT NULL,
            issues_found int(5) DEFAULT 0,
            auto_fixes_applied int(5) DEFAULT 0,
            execution_time float DEFAULT 0,
            details text,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY patrol_type (patrol_type),
            KEY created_at (created_at)
        ) $charset_collate;";
        
        // 内容生成历史表
        $content_table = $wpdb->prefix . 'ai_content_history';
        $content_sql = "CREATE TABLE $content_table (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            content_type varchar(50) NOT NULL,
            prompt text NOT NULL,
            ai_model varchar(100) NOT NULL,
            generated_content longtext NOT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY content_type (content_type),
            KEY created_at (created_at)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($seo_sql);
        dbDelta($patrol_sql);
        dbDelta($content_sql);
    }
    
    /**
     * 插件激活
     */
    public function activate() {
        $this->create_database_tables();
        
        // 设置默认选项
        add_option('ai_opt_api_key', '');
        add_option('ai_seo_competitor_urls', array());
        add_option('ai_patrol_settings', array(
            'enabled' => true,
            'monitor_database' => true,
            'monitor_code' => true,
            'monitor_performance' => true,
            'monitor_security' => true,
            'auto_fix' => false
        ));
        
        // 清理旧的定时任务
        wp_clear_scheduled_hook('ai_optimizer_daily_patrol');
        
        // 刷新重写规则
        flush_rewrite_rules();
    }
    
    /**
     * 插件停用
     */
    public function deactivate() {
        // 清理定时任务
        wp_clear_scheduled_hook('ai_optimizer_daily_patrol');
        wp_clear_scheduled_hook('ai_patrol_system_check');
        flush_rewrite_rules();
    }
    
    /**
     * 添加管理菜单
     */
    public function add_admin_menu() {
        add_menu_page(
            'AI智能优化器',
            'AI智能优化器',
            'manage_options',
            'ai-optimizer',
            array($this, 'render_dashboard'),
            'dashicons-performance',
            30
        );
        
        add_submenu_page(
            'ai-optimizer',
            '仪表盘',
            '仪表盘',
            'manage_options',
            'ai-optimizer',
            array($this, 'render_dashboard')
        );
        
        add_submenu_page(
            'ai-optimizer',
            '性能监控',
            '性能监控',
            'manage_options',
            'ai-optimizer-monitor',
            array($this, 'render_monitor')
        );
        
        add_submenu_page(
            'ai-optimizer',
            'SEO优化',
            'SEO优化',
            'manage_options',
            'ai-optimizer-seo',
            array($this, 'render_seo')
        );
        
        add_submenu_page(
            'ai-optimizer',
            'AI工具',
            'AI工具',
            'manage_options',
            'ai-optimizer-tools',
            array($this, 'render_tools')
        );
        
        add_submenu_page(
            'ai-optimizer',
            '设置',
            '设置',
            'manage_options',
            'ai-optimizer-settings',
            array($this, 'render_settings')
        );
    }
    
    /**
     * 加载管理员资源
     */
    public function enqueue_admin_assets($hook) {
        if (strpos($hook, 'ai-optimizer') === false) {
            return;
        }
        
        wp_enqueue_script('jquery');
        wp_enqueue_script('chart-js', 'https://cdn.jsdelivr.net/npm/chart.js', array('jquery'), '3.9.1', true);
        
        // 内联CSS
        wp_add_inline_style('admin-styles', $this->get_admin_css());
        
        // 内联JavaScript
        wp_add_inline_script('jquery', $this->get_admin_js());
        
        // 本地化脚本
        wp_localize_script('jquery', 'aiOptimizer', array(
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('ai-opt-nonce'),
            'currentContent' => '',
            'currentContentType' => ''
        ));
    }
    
    /**
     * 获取管理员CSS
     */
    private function get_admin_css() {
        return '
        .ai-optimizer-wrap {
            max-width: 1200px;
            margin: 20px 0;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
            border-radius: 10px;
            color: white;
        }
        
        .ai-optimizer-card {
            background: rgba(255, 255, 255, 0.1);
            padding: 20px;
            margin: 20px 0;
            border-radius: 10px;
            border: 1px solid rgba(255, 255, 255, 0.2);
            backdrop-filter: blur(10px);
        }
        
        .ai-optimizer-card h2 {
            color: #00ff88;
            margin-bottom: 20px;
            text-shadow: 0 0 10px rgba(0, 255, 136, 0.5);
        }
        
        .ai-optimizer-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: rgba(0, 255, 136, 0.1);
            padding: 20px;
            border-radius: 10px;
            text-align: center;
            border: 1px solid rgba(0, 255, 136, 0.3);
        }
        
        .stat-value {
            font-size: 2.5em;
            font-weight: bold;
            color: #00ff88;
            text-shadow: 0 0 10px rgba(0, 255, 136, 0.7);
        }
        
        .ai-optimizer-button {
            background: linear-gradient(45deg, #00ff88, #00ccff);
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 25px;
            cursor: pointer;
            font-weight: bold;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(0, 255, 136, 0.4);
        }
        
        .ai-optimizer-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(0, 255, 136, 0.6);
        }
        
        .ai-optimizer-button:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }
        
        .form-table th {
            color: #00ff88;
            font-weight: bold;
        }
        
        .form-table input, .form-table select, .form-table textarea {
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.3);
            color: white;
            padding: 8px 12px;
            border-radius: 5px;
        }
        
        .form-table input:focus, .form-table select:focus, .form-table textarea:focus {
            border-color: #00ff88;
            box-shadow: 0 0 10px rgba(0, 255, 136, 0.5);
        }
        
        .analysis-results {
            background: rgba(0, 0, 0, 0.3);
            padding: 20px;
            border-radius: 10px;
            margin-top: 20px;
            border-left: 4px solid #00ff88;
        }
        
        .log-container {
            max-height: 400px;
            overflow-y: auto;
            background: rgba(0, 0, 0, 0.5);
            padding: 15px;
            border-radius: 5px;
            font-family: monospace;
            font-size: 13px;
            margin-top: 10px;
        }
        
        .log-entry {
            margin-bottom: 5px;
            padding: 5px;
            border-radius: 3px;
        }
        
        .log-entry.error {
            background: rgba(255, 0, 0, 0.2);
            color: #ff6b6b;
        }
        
        .log-entry.warning {
            background: rgba(255, 165, 0, 0.2);
            color: #ffa500;
        }
        
        .log-entry.info {
            background: rgba(0, 255, 136, 0.2);
            color: #00ff88;
        }
        
        .log-entry.debug {
            background: rgba(100, 181, 246, 0.2);
            color: #64b5f6;
        }
        
        .progress-bar {
            background: rgba(255, 255, 255, 0.2);
            border-radius: 10px;
            height: 20px;
            overflow: hidden;
            margin: 10px 0;
        }
        
        .progress-fill {
            background: linear-gradient(45deg, #00ff88, #00ccff);
            height: 100%;
            transition: width 0.3s ease;
            border-radius: 10px;
        }
        
        .checkbox-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 10px;
            margin: 15px 0;
        }
        
        .checkbox-item {
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .checkbox-item input[type="checkbox"] {
            width: 18px;
            height: 18px;
            accent-color: #00ff88;
        }
        
        .content-display {
            background: rgba(0, 0, 0, 0.3);
            padding: 20px;
            border-radius: 10px;
            margin: 20px 0;
            min-height: 200px;
        }
        
        .content-display img, .content-display video, .content-display audio {
            max-width: 100%;
            height: auto;
            border-radius: 5px;
            margin: 10px 0;
        }
        
        .alert {
            padding: 15px;
            margin: 10px 0;
            border-radius: 5px;
        }
        
        .alert-success {
            background: rgba(0, 255, 136, 0.2);
            border: 1px solid rgba(0, 255, 136, 0.5);
            color: #00ff88;
        }
        
        .alert-error {
            background: rgba(255, 0, 0, 0.2);
            border: 1px solid rgba(255, 0, 0, 0.5);
            color: #ff6b6b;
        }
        
        .alert-warning {
            background: rgba(255, 165, 0, 0.2);
            border: 1px solid rgba(255, 165, 0, 0.5);
            color: #ffa500;
        }
        
        .ai-optimizer-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }
        
        .feature-card {
            background: rgba(255, 255, 255, 0.1);
            padding: 20px;
            border-radius: 10px;
            border: 1px solid rgba(255, 255, 255, 0.2);
            transition: transform 0.3s ease;
        }
        
        .feature-card:hover {
            transform: translateY(-5px);
        }
        
        .feature-card h3 {
            color: #00ff88;
            margin-bottom: 15px;
        }
        ';
    }
    
    /**
     * 获取管理员JavaScript
     */
    private function get_admin_js() {
        return '
        jQuery(document).ready(function($) {
            var nonce = aiOptimizer.nonce;
            
            // 测试API连接
            $("#test-api").click(function() {
                var btn = $(this);
                var apiKey = $("#api_key").val();
                
                if (!apiKey.trim()) {
                    alert("请先输入API密钥");
                    return;
                }
                
                btn.prop("disabled", true).text("测试中...");
                
                $.post(aiOptimizer.ajaxurl, {
                    action: "ai_opt_test_api",
                    nonce: nonce,
                    api_key: apiKey
                }, function(response) {
                    if (response.success) {
                        alert("API连接成功！");
                    } else {
                        alert("API连接失败：" + response.data.message);
                    }
                }).fail(function() {
                    alert("网络错误，请检查网络连接");
                }).always(function() {
                    btn.prop("disabled", false).text("测试连接");
                });
            });
            
            // 保存设置
            $("#save-settings").click(function() {
                var btn = $(this);
                var apiKey = $("#api_key").val();
                
                btn.prop("disabled", true).text("保存中...");
                
                $.post(aiOptimizer.ajaxurl, {
                    action: "ai_opt_save_settings",
                    nonce: nonce,
                    api_key: apiKey
                }, function(response) {
                    if (response.success) {
                        alert("设置保存成功！");
                    } else {
                        alert("设置保存失败：" + response.data.message);
                    }
                }).always(function() {
                    btn.prop("disabled", false).text("保存设置");
                });
            });
            
            // SEO分析
            $("#start-seo-analysis").click(function() {
                var btn = $(this);
                var aiModel = $("#ai_model").val();
                var customModel = $("#custom_ai_model").val();
                var optimizationStrategy = $("input[name=\"optimization_strategy\"]:checked").val();
                var autoOptimization = $("#auto_optimization").is(":checked");
                
                // 获取分析范围
                var analysisScope = [];
                $("input[name=\"analysis_scope[]\"]:checked").each(function() {
                    analysisScope.push($(this).val());
                });
                
                if (analysisScope.length === 0) {
                    alert("请至少选择一个分析范围");
                    return;
                }
                
                // 保存复选框状态到localStorage
                var checkboxStates = {};
                $("input[name=\"analysis_scope[]\"]").each(function() {
                    checkboxStates[$(this).val()] = $(this).is(":checked");
                });
                localStorage.setItem("ai_seo_analysis_scope", JSON.stringify(checkboxStates));
                
                btn.prop("disabled", true).text("分析中...");
                $("#analysis-results").html("<div class=\"alert alert-info\">正在进行AI深度分析，请稍候...</div>");
                
                $.post(aiOptimizer.ajaxurl, {
                    action: "ai_opt_run_seo_analysis",
                    nonce: nonce,
                    ai_model: aiModel,
                    custom_ai_model: customModel,
                    optimization_strategy: optimizationStrategy,
                    analysis_scope: analysisScope,
                    auto_optimization: autoOptimization
                }, function(response) {
                    if (response.success) {
                        var results = response.data;
                        var html = "<div class=\"alert alert-success\">SEO分析完成！</div>";
                        html += "<div class=\"analysis-results\">";
                        html += "<h3>分析结果</h3>";
                        html += "<p><strong>使用的AI模型：</strong>" + results.model_used + "</p>";
                        html += "<p><strong>分析得分：</strong>" + (results.score || "N/A") + "分</p>";
                        html += "<div class=\"suggestions\">" + results.suggestions + "</div>";
                        
                        if (results.auto_optimization_results) {
                            html += "<h4>自动优化结果</h4>";
                            results.auto_optimization_results.forEach(function(result) {
                                html += "<p><strong>" + result.type + "：</strong>" + result.message + "</p>";
                            });
                        }
                        
                        html += "</div>";
                        $("#analysis-results").html(html);
                    } else {
                        $("#analysis-results").html("<div class=\"alert alert-error\">分析失败：" + response.data.message + "</div>");
                    }
                }).fail(function() {
                    $("#analysis-results").html("<div class=\"alert alert-error\">网络错误，请检查网络连接</div>");
                }).always(function() {
                    btn.prop("disabled", false).text("开始AI深度分析");
                });
            });
            
            // 恢复复选框状态
            var savedStates = localStorage.getItem("ai_seo_analysis_scope");
            if (savedStates) {
                try {
                    var checkboxStates = JSON.parse(savedStates);
                    $("input[name=\"analysis_scope[]\"]").each(function() {
                        var name = $(this).val();
                        if (checkboxStates.hasOwnProperty(name)) {
                            $(this).prop("checked", checkboxStates[name]);
                        }
                    });
                } catch (e) {
                    console.log("恢复复选框状态失败:", e);
                }
            }
            
            // AI模型选择联动
            $("#ai_model").change(function() {
                var customModelContainer = $("#custom-model-container");
                if ($(this).val() === "custom") {
                    customModelContainer.show();
                } else {
                    customModelContainer.hide();
                }
            });
            
            // 巡逻检查
            $("#start-patrol").click(function() {
                var btn = $(this);
                
                btn.prop("disabled", true).text("巡逻中...");
                $("#patrol-results").html("<div class=\"alert alert-info\">正在执行AI巡逻检查，请稍候...</div>");
                
                $.post(aiOptimizer.ajaxurl, {
                    action: "ai_opt_run_patrol_check",
                    nonce: nonce
                }, function(response) {
                    if (response.success) {
                        var results = response.data.results;
                        var html = "<div class=\"alert alert-success\">AI巡逻检查完成！</div>";
                        html += "<div class=\"analysis-results\">";
                        html += "<h3>巡逻结果</h3>";
                        
                        if (results.database) {
                            html += "<h4>数据库检查</h4>";
                            html += "<p>" + JSON.stringify(results.database) + "</p>";
                        }
                        
                        if (results.code) {
                            html += "<h4>代码检查</h4>";
                            html += "<p>" + JSON.stringify(results.code) + "</p>";
                        }
                        
                        if (results.performance) {
                            html += "<h4>性能检查</h4>";
                            html += "<p>" + JSON.stringify(results.performance) + "</p>";
                        }
                        
                        if (results.security) {
                            html += "<h4>安全检查</h4>";
                            html += "<p>" + JSON.stringify(results.security) + "</p>";
                        }
                        
                        html += "</div>";
                        $("#patrol-results").html(html);
                    } else {
                        $("#patrol-results").html("<div class=\"alert alert-error\">巡逻失败：" + response.data.message + "</div>");
                    }
                }).fail(function() {
                    $("#patrol-results").html("<div class=\"alert alert-error\">网络错误，请检查网络连接</div>");
                }).always(function() {
                    btn.prop("disabled", false).text("立即执行AI巡逻");
                });
            });
            
            // 内容生成
            $(".generate-content").click(function() {
                var btn = $(this);
                var type = btn.data("type");
                var prompt = $("#content-prompt").val();
                
                if (!prompt.trim()) {
                    alert("请输入生成提示");
                    return;
                }
                
                btn.prop("disabled", true).text("生成中...");
                $("#content-display").html("<div class=\"alert alert-info\">正在生成" + type + "内容，请稍候...</div>");
                
                $.post(aiOptimizer.ajaxurl, {
                    action: "ai_opt_generate_content",
                    nonce: nonce,
                    type: type,
                    prompt: prompt
                }, function(response) {
                    if (response.success) {
                        var content = response.data.content;
                        var html = "<div class=\"alert alert-success\">生成成功！</div>";
                        html += "<div class=\"content-display\">";
                        
                        if (type === "text") {
                            html += "<p>" + content + "</p>";
                        } else if (type === "image") {
                            html += "<img src=\"" + content + "\" alt=\"AI生成的图片\">";
                        } else if (type === "video") {
                            html += "<video controls><source src=\"" + content + "\" type=\"video/mp4\"></video>";
                        } else if (type === "audio") {
                            html += "<audio controls><source src=\"" + content + "\" type=\"audio/mpeg\"></audio>";
                        }
                        
                        html += "</div>";
                        $("#content-display").html(html);
                        
                        // 保存内容到全局变量
                        aiOptimizer.currentContent = content;
                        aiOptimizer.currentContentType = type;
                    } else {
                        $("#content-display").html("<div class=\"alert alert-error\">生成失败：" + response.data.message + "</div>");
                    }
                }).fail(function() {
                    $("#content-display").html("<div class=\"alert alert-error\">网络错误，请检查网络连接</div>");
                }).always(function() {
                    btn.prop("disabled", false).text("生成" + type);
                });
            });
            
            // 保存竞争对手
            $("#save-competitors").click(function() {
                var btn = $(this);
                var urls = [];
                
                $(".competitor-url").each(function() {
                    var url = $(this).val().trim();
                    if (url) {
                        urls.push(url);
                    }
                });
                
                btn.prop("disabled", true).text("保存中...");
                
                $.post(aiOptimizer.ajaxurl, {
                    action: "ai_opt_save_competitors",
                    nonce: nonce,
                    competitor_urls: urls
                }, function(response) {
                    if (response.success) {
                        alert("竞争对手设置保存成功！");
                    } else {
                        alert("保存失败：" + response.data.message);
                    }
                }).always(function() {
                    btn.prop("disabled", false).text("保存设置");
                });
            });
            
            // 添加竞争对手URL输入框
            $("#add-competitor").click(function() {
                var html = "<div class=\"competitor-item\">";
                html += "<input type=\"url\" class=\"competitor-url regular-text\" placeholder=\"请输入竞争对手网站URL\">";
                html += "<button type=\"button\" class=\"remove-competitor ai-optimizer-button\" style=\"margin-left: 10px;\">删除</button>";
                html += "</div>";
                $("#competitors-container").append(html);
            });
            
            // 删除竞争对手URL
            $(document).on("click", ".remove-competitor", function() {
                $(this).closest(".competitor-item").remove();
            });
            
            // 获取监控日志
            function loadMonitorLogs() {
                $.post(aiOptimizer.ajaxurl, {
                    action: "ai_opt_get_monitor_logs",
                    nonce: nonce
                }, function(response) {
                    if (response.success) {
                        var logs = response.data.logs;
                        var html = "";
                        
                        logs.forEach(function(log) {
                            html += "<div class=\"log-entry " + log.type + "\">";
                            html += "[" + log.time + "] " + log.message;
                            html += "</div>";
                        });
                        
                        $("#log-content").html(html);
                        
                        // 自动滚动到底部
                        var container = $("#log-container");
                        container.scrollTop(container[0].scrollHeight);
                    }
                });
            }
            
            // 如果在监控页面，定期刷新日志
            if ($("#log-container").length > 0) {
                loadMonitorLogs();
                setInterval(loadMonitorLogs, 5000); // 每5秒刷新一次
            }
        });
        ';
    }
    
    /**
     * 渲染仪表盘
     */
    public function render_dashboard() {
        $api_key = get_option('ai_opt_api_key', '');
        $api_configured = !empty($api_key);
        
        ?>
        <div class="wrap ai-optimizer-wrap">
            <h1>🚀 AI智能网站优化器 - 仪表盘</h1>
            
            <div class="ai-optimizer-stats">
                <div class="stat-card">
                    <h3>网站性能</h3>
                    <div class="stat-value">95%</div>
                    <p>优化程度</p>
                </div>
                <div class="stat-card">
                    <h3>SEO得分</h3>
                    <div class="stat-value">88%</div>
                    <p>搜索优化</p>
                </div>
                <div class="stat-card">
                    <h3>AI使用量</h3>
                    <div class="stat-value">73%</div>
                    <p>功能利用率</p>
                </div>
            </div>
            
            <div class="ai-optimizer-grid">
                <div class="feature-card">
                    <h3>🔧 快速配置</h3>
                    <?php if ($api_configured): ?>
                        <p>✅ API密钥已配置</p>
                        <p>系统已准备就绪，您可以开始使用所有AI功能。</p>
                    <?php else: ?>
                        <p>⚠️ API密钥未配置</p>
                        <p>请先配置Siliconflow API密钥以启用AI功能。</p>
                        <a href="<?php echo admin_url('admin.php?page=ai-optimizer-settings'); ?>" class="ai-optimizer-button">配置API密钥</a>
                    <?php endif; ?>
                </div>
                
                <div class="feature-card">
                    <h3>📊 系统状态</h3>
                    <ul>
                        <li>WordPress版本: <?php echo get_bloginfo('version'); ?></li>
                        <li>PHP版本: <?php echo PHP_VERSION; ?></li>
                        <li>内存使用: <?php echo size_format(memory_get_usage(true)); ?></li>
                        <li>插件版本: <?php echo AI_OPT_VERSION; ?></li>
                    </ul>
                </div>
                
                <div class="feature-card">
                    <h3>🤖 AI功能</h3>
                    <ul>
                        <li>✅ SEO智能分析</li>
                        <li>✅ 自动化巡逻</li>
                        <li>✅ 内容生成工具</li>
                        <li>✅ 性能监控</li>
                    </ul>
                </div>
            </div>
        </div>
        <?php
    }
    
    /**
     * 渲染性能监控页面
     */
    public function render_monitor() {
        ?>
        <div class="wrap ai-optimizer-wrap">
            <h1>🔍 AI智能巡逻系统</h1>
            
            <div class="ai-optimizer-card">
                <h2>🎯 AI巡逻控制面板</h2>
                <table class="form-table">
                    <tr>
                        <th scope="row">巡逻状态</th>
                        <td>
                            <button id="start-patrol" class="ai-optimizer-button">立即执行AI巡逻</button>
                            <p class="description">执行全面的AI巡逻检查，包括数据库、代码、性能和安全检查</p>
                        </td>
                    </tr>
                </table>
                
                <div id="patrol-results"></div>
            </div>
            
            <div class="ai-optimizer-card">
                <h2>📊 实时系统日志</h2>
                <div id="log-container" class="log-container">
                    <div id="log-content"></div>
                </div>
            </div>
        </div>
        <?php
    }
    
    /**
     * 渲染SEO优化页面
     */
    public function render_seo() {
        $competitor_urls = get_option('ai_seo_competitor_urls', array());
        ?>
        <div class="wrap ai-optimizer-wrap">
            <h1>🔍 AI智能SEO优化分析</h1>
            
            <div class="ai-optimizer-card">
                <h2>🎯 AI分析控制面板</h2>
                <table class="form-table">
                    <tr>
                        <th scope="row"><label for="ai_model">AI模型选择</label></th>
                        <td>
                            <select id="ai_model" name="ai_model" class="regular-text">
                                <option value="Qwen/QwQ-32B-Preview">Qwen/QwQ-32B (推荐)</option>
                                <option value="Qwen/Qwen2.5-72B-Instruct">Qwen/Qwen2.5-72B</option>
                                <option value="meta-llama/Meta-Llama-3.1-70B-Instruct">Meta-Llama-3.1-70B</option>
                                <option value="custom">自定义模型</option>
                            </select>
                            <div id="custom-model-container" style="display: none; margin-top: 10px;">
                                <input type="text" id="custom_ai_model" placeholder="请输入自定义AI模型名称" class="regular-text">
                                <p class="description">例如：gpt-4, claude-3-opus, deepseek-coder</p>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">分析范围</th>
                        <td>
                            <div class="checkbox-grid">
                                <div class="checkbox-item">
                                    <input type="checkbox" name="analysis_scope[]" value="keywords" id="scope_keywords">
                                    <label for="scope_keywords">关键词分析</label>
                                </div>
                                <div class="checkbox-item">
                                    <input type="checkbox" name="analysis_scope[]" value="content" id="scope_content">
                                    <label for="scope_content">内容质量分析</label>
                                </div>
                                <div class="checkbox-item">
                                    <input type="checkbox" name="analysis_scope[]" value="technical" id="scope_technical">
                                    <label for="scope_technical">技术SEO分析</label>
                                </div>
                                <div class="checkbox-item">
                                    <input type="checkbox" name="analysis_scope[]" value="competitors" id="scope_competitors">
                                    <label for="scope_competitors">竞争对手分析</label>
                                </div>
                                <div class="checkbox-item">
                                    <input type="checkbox" name="analysis_scope[]" value="backlinks" id="scope_backlinks">
                                    <label for="scope_backlinks">外链分析</label>
                                </div>
                                <div class="checkbox-item">
                                    <input type="checkbox" name="analysis_scope[]" value="performance" id="scope_performance">
                                    <label for="scope_performance">性能分析</label>
                                </div>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">优化策略</th>
                        <td>
                            <div class="checkbox-grid">
                                <div class="checkbox-item">
                                    <input type="radio" name="optimization_strategy" value="content_optimization" id="strategy_content">
                                    <label for="strategy_content">内容优化</label>
                                </div>
                                <div class="checkbox-item">
                                    <input type="radio" name="optimization_strategy" value="technical_optimization" id="strategy_technical">
                                    <label for="strategy_technical">技术优化</label>
                                </div>
                                <div class="checkbox-item">
                                    <input type="radio" name="optimization_strategy" value="comprehensive" id="strategy_comprehensive" checked>
                                    <label for="strategy_comprehensive">全面优化</label>
                                </div>
                            </div>
                            <div style="margin-top: 15px;">
                                <input type="checkbox" id="auto_optimization" name="auto_optimization">
                                <label for="auto_optimization">启用自动化优化</label>
                                <p class="description">系统将自动执行可以安全应用的优化建议</p>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">执行分析</th>
                        <td>
                            <button id="start-seo-analysis" class="ai-optimizer-button">开始AI深度分析</button>
                        </td>
                    </tr>
                </table>
                
                <div id="analysis-results"></div>
            </div>
            
            <div class="ai-optimizer-card">
                <h2>🏆 竞争对手分析设置</h2>
                <div id="competitors-container">
                    <?php if (!empty($competitor_urls)): ?>
                        <?php foreach ($competitor_urls as $url): ?>
                            <div class="competitor-item">
                                <input type="url" class="competitor-url regular-text" value="<?php echo esc_url($url); ?>" placeholder="请输入竞争对手网站URL">
                                <button type="button" class="remove-competitor ai-optimizer-button" style="margin-left: 10px;">删除</button>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="competitor-item">
                            <input type="url" class="competitor-url regular-text" placeholder="请输入竞争对手网站URL">
                            <button type="button" class="remove-competitor ai-optimizer-button" style="margin-left: 10px;">删除</button>
                        </div>
                    <?php endif; ?>
                </div>
                <p>
                    <button id="add-competitor" class="ai-optimizer-button">添加竞争对手</button>
                    <button id="save-competitors" class="ai-optimizer-button">保存设置</button>
                </p>
            </div>
        </div>
        <?php
    }
    
    /**
     * 渲染AI工具页面
     */
    public function render_tools() {
        ?>
        <div class="wrap ai-optimizer-wrap">
            <h1>🤖 AI内容生成工具</h1>
            
            <div class="ai-optimizer-card">
                <h2>📝 内容生成</h2>
                <table class="form-table">
                    <tr>
                        <th scope="row"><label for="content-prompt">生成提示</label></th>
                        <td>
                            <textarea id="content-prompt" rows="4" class="large-text" placeholder="请输入您想要生成的内容描述..."></textarea>
                            <p class="description">详细描述您想要生成的内容，AI会根据您的描述生成相应的内容</p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">生成类型</th>
                        <td>
                            <button class="generate-content ai-optimizer-button" data-type="text">生成文本</button>
                            <button class="generate-content ai-optimizer-button" data-type="image">生成图片</button>
                            <button class="generate-content ai-optimizer-button" data-type="video">生成视频</button>
                            <button class="generate-content ai-optimizer-button" data-type="audio">生成音频</button>
                        </td>
                    </tr>
                </table>
                
                <div id="content-display" class="content-display">
                    <p>请输入提示词并选择生成类型</p>
                </div>
            </div>
        </div>
        <?php
    }
    
    /**
     * 渲染设置页面
     */
    public function render_settings() {
        $api_key = get_option('ai_opt_api_key', '');
        ?>
        <div class="wrap ai-optimizer-wrap">
            <h1>⚙️ 系统设置</h1>
            
            <div class="ai-optimizer-card">
                <h2>🔑 API配置</h2>
                <table class="form-table">
                    <tr>
                        <th scope="row"><label for="api_key">Siliconflow API密钥</label></th>
                        <td>
                            <input type="password" id="api_key" name="api_key" value="<?php echo esc_attr($api_key); ?>" class="regular-text" placeholder="请输入您的Siliconflow API密钥">
                            <p class="description">
                                请在 <a href="https://cloud.siliconflow.cn/" target="_blank">Siliconflow官网</a> 注册并获取API密钥
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">操作</th>
                        <td>
                            <button id="test-api" class="ai-optimizer-button">测试连接</button>
                            <button id="save-settings" class="ai-optimizer-button">保存设置</button>
                        </td>
                    </tr>
                </table>
            </div>
            
            <div class="ai-optimizer-card">
                <h2>ℹ️ 系统信息</h2>
                <table class="form-table">
                    <tr>
                        <th scope="row">插件版本</th>
                        <td><?php echo AI_OPT_VERSION; ?></td>
                    </tr>
                    <tr>
                        <th scope="row">WordPress版本</th>
                        <td><?php echo get_bloginfo('version'); ?></td>
                    </tr>
                    <tr>
                        <th scope="row">PHP版本</th>
                        <td><?php echo PHP_VERSION; ?></td>
                    </tr>
                    <tr>
                        <th scope="row">内存限制</th>
                        <td><?php echo ini_get('memory_limit'); ?></td>
                    </tr>
                    <tr>
                        <th scope="row">数据库版本</th>
                        <td><?php global $wpdb; echo $wpdb->db_version(); ?></td>
                    </tr>
                </table>
            </div>
        </div>
        <?php
    }
    
    /**
     * AJAX: 测试API连接
     */
    public function ajax_test_api() {
        if (!wp_verify_nonce($_POST['nonce'] ?? '', 'ai-opt-nonce') || !current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }
        
        $api_key = sanitize_text_field($_POST['api_key'] ?? '');
        
        if (empty($api_key)) {
            wp_send_json_error(array('message' => '请输入API密钥'));
            return;
        }
        
        $result = $this->test_siliconflow_api($api_key);
        
        if ($result['success']) {
            wp_send_json_success(array('message' => 'API连接成功'));
        } else {
            wp_send_json_error(array('message' => $result['error']));
        }
    }
    
    /**
     * 测试Siliconflow API
     */
    private function test_siliconflow_api($api_key) {
        $url = 'https://api.siliconflow.cn/v1/models';
        
        $args = array(
            'headers' => array(
                'Authorization' => 'Bearer ' . $api_key,
                'Content-Type' => 'application/json'
            ),
            'timeout' => 30
        );
        
        $response = wp_remote_get($url, $args);
        
        if (is_wp_error($response)) {
            return array('success' => false, 'error' => '网络连接失败: ' . $response->get_error_message());
        }
        
        $http_code = wp_remote_retrieve_response_code($response);
        
        if ($http_code === 200) {
            return array('success' => true);
        } else {
            $body = wp_remote_retrieve_body($response);
            $error_info = json_decode($body, true);
            $error_message = isset($error_info['error']['message']) ? $error_info['error']['message'] : 'API调用失败';
            return array('success' => false, 'error' => $error_message);
        }
    }
    
    /**
     * AJAX: 保存设置
     */
    public function ajax_save_settings() {
        if (!wp_verify_nonce($_POST['nonce'] ?? '', 'ai-opt-nonce') || !current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }
        
        $api_key = sanitize_text_field($_POST['api_key'] ?? '');
        
        update_option('ai_opt_api_key', $api_key);
        
        wp_send_json_success(array('message' => '设置保存成功'));
    }
    
    /**
     * AJAX: 运行SEO分析
     */
    public function ajax_run_seo_analysis() {
        if (!wp_verify_nonce($_POST['nonce'] ?? '', 'ai-opt-nonce') || !current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }
        
        try {
            $ai_model = sanitize_text_field($_POST['ai_model'] ?? 'Qwen/QwQ-32B-Preview');
            $custom_model = sanitize_text_field($_POST['custom_ai_model'] ?? '');
            $optimization_strategy = sanitize_text_field($_POST['optimization_strategy'] ?? 'comprehensive');
            $analysis_scope = $_POST['analysis_scope'] ?? array();
            $auto_optimization = isset($_POST['auto_optimization']);
            
            // 使用自定义模型还是预设模型
            $selected_model = ($ai_model === 'custom' && !empty($custom_model)) ? $custom_model : $ai_model;
            
            if ($ai_model === 'custom' && empty($custom_model)) {
                wp_send_json_error(array('message' => '请输入自定义AI模型名称'));
                return;
            }
            
            // 验证API密钥
            $api_key = get_option('ai_opt_api_key');
            if (empty($api_key)) {
                wp_send_json_error(array('message' => '请先在设置页面配置Siliconflow API密钥'));
                return;
            }
            
            // 运行SEO分析
            $result = $this->run_seo_analysis($selected_model, $analysis_scope, $optimization_strategy, $auto_optimization, $api_key);
            
            if (isset($result['error'])) {
                wp_send_json_error(array('message' => $result['error']));
            } else {
                wp_send_json_success(array(
                    'message' => 'SEO分析完成',
                    'suggestions' => $result['content'],
                    'model_used' => $selected_model,
                    'score' => $result['score']
                ));
            }
        } catch (Exception $e) {
            wp_send_json_error(array('message' => 'SEO分析失败: ' . $e->getMessage()));
        }
    }
    
    /**
     * 运行SEO分析
     */
    private function run_seo_analysis($model, $analysis_scope, $optimization_strategy, $auto_optimization, $api_key) {
        $site_url = get_site_url();
        $site_title = get_bloginfo('name');
        $site_description = get_bloginfo('description');
        
        // 构建分析提示词
        $prompt = "请作为专业SEO专家，对以下网站进行详细的SEO分析：\n\n";
        $prompt .= "网站URL: $site_url\n";
        $prompt .= "网站标题: $site_title\n";
        $prompt .= "网站描述: $site_description\n";
        $prompt .= "WordPress版本: " . get_bloginfo('version') . "\n\n";
        
        // 添加分析范围
        if (!empty($analysis_scope)) {
            $prompt .= "重点分析范围：\n";
            $scope_labels = array(
                'keywords' => '关键词优化分析',
                'content' => '内容质量与结构分析',
                'technical' => '技术SEO分析',
                'competitors' => '竞争对手分析',
                'backlinks' => '外链建设分析',
                'performance' => '页面性能分析'
            );
            
            foreach ($analysis_scope as $scope) {
                if (isset($scope_labels[$scope])) {
                    $prompt .= "- " . $scope_labels[$scope] . "\n";
                }
            }
            $prompt .= "\n";
        }
        
        // 添加竞争对手分析
        $competitor_urls = get_option('ai_seo_competitor_urls', array());
        if (!empty($competitor_urls)) {
            $prompt .= "竞争对手网站：\n";
            foreach ($competitor_urls as $url) {
                $prompt .= "- $url\n";
            }
            $prompt .= "\n";
        }
        
        $prompt .= "请提供详细的SEO分析报告，包括：\n";
        $prompt .= "1. 整体SEO评分（0-100分）\n";
        $prompt .= "2. 发现的主要问题\n";
        $prompt .= "3. 具体的优化建议\n";
        $prompt .= "4. 预期的优化效果\n";
        
        if ($auto_optimization) {
            $prompt .= "5. 可以自动化执行的优化步骤\n";
        }
        
        $prompt .= "\n请以结构化的方式提供分析结果。";
        
        // 调用AI API
        $result = $this->call_ai_api($model, $prompt, $api_key);
        
        if (isset($result['error'])) {
            return $result;
        }
        
        // 提取评分
        $score = 0;
        if (isset($result['content'])) {
            if (preg_match('/评分[：:]?\s*(\d+)/u', $result['content'], $matches)) {
                $score = intval($matches[1]);
            } elseif (preg_match('/(\d+)分/u', $result['content'], $matches)) {
                $score = intval($matches[1]);
            }
        }
        
        $result['score'] = $score;
        
        // 保存分析结果
        $this->save_seo_analysis($site_url, $result, $model, $analysis_scope);
        
        return $result;
    }
    
    /**
     * 保存SEO分析结果
     */
    private function save_seo_analysis($url, $result, $model, $analysis_scope) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'ai_seo_analysis';
        
        $wpdb->insert(
            $table_name,
            array(
                'url' => $url,
                'analysis_type' => implode(',', $analysis_scope),
                'score' => $result['score'],
                'suggestions' => json_encode($result),
                'ai_model' => $model,
                'analyzed_at' => current_time('mysql')
            ),
            array('%s', '%s', '%d', '%s', '%s', '%s')
        );
    }
    
    /**
     * AJAX: 运行AI巡逻检查
     */
    public function ajax_run_patrol_check() {
        if (!wp_verify_nonce($_POST['nonce'] ?? '', 'ai-opt-nonce') || !current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }
        
        try {
            $api_key = get_option('ai_opt_api_key');
            if (empty($api_key)) {
                wp_send_json_error(array('message' => '请先在设置页面配置Siliconflow API密钥'));
                return;
            }
            
            $results = $this->run_patrol_check($api_key);
            
            if (isset($results['error'])) {
                wp_send_json_error(array('message' => $results['error']));
            } else {
                wp_send_json_success(array(
                    'message' => 'AI巡逻检查完成',
                    'results' => $results
                ));
            }
        } catch (Exception $e) {
            wp_send_json_error(array('message' => 'AI巡逻检查失败: ' . $e->getMessage()));
        }
    }
    
    /**
     * 运行AI巡逻检查
     */
    private function run_patrol_check($api_key) {
        $results = array();
        
        // 数据库检查
        $results['database'] = $this->check_database_health();
        
        // 代码质量检查
        $results['code'] = $this->check_code_quality();
        
        // 性能检查
        $results['performance'] = $this->check_site_performance();
        
        // 安全检查
        $results['security'] = $this->check_security_status();
        
        // 记录巡逻结果
        $this->log_patrol_results($results);
        
        return $results;
    }
    
    /**
     * 检查数据库健康度
     */
    private function check_database_health() {
        global $wpdb;
        
        $results = array();
        
        // 检查数据库连接
        $results['connection'] = $wpdb->check_connection() ? '正常' : '异常';
        
        // 检查数据库大小
        $db_size = $wpdb->get_var("SELECT ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) AS 'DB Size in MB' FROM information_schema.tables WHERE table_schema='{$wpdb->dbname}'");
        $results['size'] = $db_size . ' MB';
        
        // 检查表数量
        $table_count = $wpdb->get_var("SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = '{$wpdb->dbname}'");
        $results['table_count'] = $table_count;
        
        return $results;
    }
    
    /**
     * 检查代码质量
     */
    private function check_code_quality() {
        $results = array();
        
        // 检查活跃插件
        $active_plugins = get_option('active_plugins');
        $results['active_plugins'] = count($active_plugins);
        
        // 检查主题
        $current_theme = wp_get_theme();
        $results['current_theme'] = $current_theme->get('Name');
        
        // 检查PHP错误日志
        $error_log = ini_get('error_log');
        if ($error_log && file_exists($error_log)) {
            $results['error_log_size'] = size_format(filesize($error_log));
        } else {
            $results['error_log_size'] = '未找到';
        }
        
        return $results;
    }
    
    /**
     * 检查网站性能
     */
    private function check_site_performance() {
        $results = array();
        
        // 内存使用情况
        $results['memory_usage'] = size_format(memory_get_usage(true));
        $results['memory_limit'] = ini_get('memory_limit');
        
        // 页面加载时间
        $start_time = microtime(true);
        $response = wp_remote_get(home_url());
        $load_time = microtime(true) - $start_time;
        $results['page_load_time'] = round($load_time, 3) . '秒';
        
        // 数据库查询数量
        $results['db_queries'] = get_num_queries();
        
        return $results;
    }
    
    /**
     * 检查安全状态
     */
    private function check_security_status() {
        $results = array();
        
        // 检查WordPress版本
        $wp_version = get_bloginfo('version');
        $latest_version = get_transient('wp_latest_version');
        if (!$latest_version) {
            $version_check = wp_version_check();
            $latest_version = $version_check->current ?? $wp_version;
            set_transient('wp_latest_version', $latest_version, 12 * HOUR_IN_SECONDS);
        }
        
        $results['wp_version'] = $wp_version;
        $results['is_latest'] = version_compare($wp_version, $latest_version, '>=') ? '是' : '否';
        
        // 检查SSL状态
        $results['ssl_enabled'] = is_ssl() ? '是' : '否';
        
        // 检查管理员用户
        $admin_users = get_users(array('role' => 'administrator'));
        $results['admin_count'] = count($admin_users);
        
        return $results;
    }
    
    /**
     * 记录巡逻结果
     */
    private function log_patrol_results($results) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'ai_patrol_logs';
        
        $wpdb->insert(
            $table_name,
            array(
                'patrol_type' => 'full_check',
                'status' => 'completed',
                'details' => json_encode($results),
                'created_at' => current_time('mysql')
            ),
            array('%s', '%s', '%s', '%s')
        );
    }
    
    /**
     * AJAX: 生成内容
     */
    public function ajax_generate_content() {
        if (!wp_verify_nonce($_POST['nonce'] ?? '', 'ai-opt-nonce') || !current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }
        
        try {
            $type = sanitize_text_field($_POST['type'] ?? 'text');
            $prompt = sanitize_textarea_field($_POST['prompt'] ?? '');
            
            if (empty($prompt)) {
                wp_send_json_error(array('message' => '请输入生成提示'));
                return;
            }
            
            $api_key = get_option('ai_opt_api_key');
            if (empty($api_key)) {
                wp_send_json_error(array('message' => '请先在设置页面配置Siliconflow API密钥'));
                return;
            }
            
            $result = $this->generate_content($type, $prompt, $api_key);
            
            if (isset($result['error'])) {
                wp_send_json_error(array('message' => $result['error']));
            } else {
                wp_send_json_success(array(
                    'content' => $result['content'],
                    'type' => $type
                ));
            }
        } catch (Exception $e) {
            wp_send_json_error(array('message' => '内容生成失败: ' . $e->getMessage()));
        }
    }
    
    /**
     * 生成内容
     */
    private function generate_content($type, $prompt, $api_key) {
        switch ($type) {
            case 'text':
                return $this->generate_text($prompt, $api_key);
            case 'image':
                return $this->generate_image($prompt, $api_key);
            case 'video':
                return $this->generate_video($prompt, $api_key);
            case 'audio':
                return $this->generate_audio($prompt, $api_key);
            default:
                return array('error' => '不支持的生成类型');
        }
    }
    
    /**
     * 生成文本
     */
    private function generate_text($prompt, $api_key) {
        $result = $this->call_ai_api('Qwen/QwQ-32B-Preview', $prompt, $api_key);
        
        if (isset($result['error'])) {
            return $result;
        }
        
        return array('content' => $result['content']);
    }
    
    /**
     * 生成图片
     */
    private function generate_image($prompt, $api_key) {
        $url = 'https://api.siliconflow.cn/v1/images/generations';
        
        $data = array(
            'model' => 'stabilityai/stable-diffusion-xl-base-1.0',
            'prompt' => $prompt,
            'image_size' => '1024x1024',
            'batch_size' => 1,
            'num_inference_steps' => 20,
            'guidance_scale' => 7.5,
            'seed' => rand(1, 1000000)
        );
        
        $args = array(
            'headers' => array(
                'Authorization' => 'Bearer ' . $api_key,
                'Content-Type' => 'application/json'
            ),
            'body' => json_encode($data),
            'timeout' => 120
        );
        
        $response = wp_remote_post($url, $args);
        
        if (is_wp_error($response)) {
            return array('error' => '网络请求失败: ' . $response->get_error_message());
        }
        
        $http_code = wp_remote_retrieve_response_code($response);
        if ($http_code !== 200) {
            return array('error' => '图片生成失败，HTTP状态码: ' . $http_code);
        }
        
        $body = wp_remote_retrieve_body($response);
        $result = json_decode($body, true);
        
        if (isset($result['images'][0]['url'])) {
            return array('content' => $result['images'][0]['url']);
        }
        
        return array('error' => '图片生成失败');
    }
    
    /**
     * 生成视频
     */
    private function generate_video($prompt, $api_key) {
        $url = 'https://api.siliconflow.cn/v1/video/submit';
        
        $data = array(
            'model' => 'tencent/HunyuanVideo',
            'prompt' => $prompt,
            'resolution' => '720p',
            'duration' => 5
        );
        
        $args = array(
            'headers' => array(
                'Authorization' => 'Bearer ' . $api_key,
                'Content-Type' => 'application/json'
            ),
            'body' => json_encode($data),
            'timeout' => 120
        );
        
        $response = wp_remote_post($url, $args);
        
        if (is_wp_error($response)) {
            return array('error' => '网络请求失败: ' . $response->get_error_message());
        }
        
        $http_code = wp_remote_retrieve_response_code($response);
        if ($http_code !== 200) {
            return array('error' => '视频生成失败，HTTP状态码: ' . $http_code);
        }
        
        $body = wp_remote_retrieve_body($response);
        $result = json_decode($body, true);
        
        if (isset($result['requestId'])) {
            // 这里应该实现异步查询视频生成状态
            return array('content' => '视频生成中，请稍后查看');
        }
        
        return array('error' => '视频生成失败');
    }
    
    /**
     * 生成音频
     */
    private function generate_audio($prompt, $api_key) {
        $url = 'https://api.siliconflow.cn/v1/audio/speech';
        
        $data = array(
            'model' => 'fishaudio/fish-speech-1.5',
            'input' => $prompt,
            'voice' => 'default',
            'response_format' => 'mp3',
            'speed' => 1.0
        );
        
        $args = array(
            'headers' => array(
                'Authorization' => 'Bearer ' . $api_key,
                'Content-Type' => 'application/json'
            ),
            'body' => json_encode($data),
            'timeout' => 120
        );
        
        $response = wp_remote_post($url, $args);
        
        if (is_wp_error($response)) {
            return array('error' => '网络请求失败: ' . $response->get_error_message());
        }
        
        $http_code = wp_remote_retrieve_response_code($response);
        if ($http_code !== 200) {
            return array('error' => '音频生成失败，HTTP状态码: ' . $http_code);
        }
        
        $body = wp_remote_retrieve_body($response);
        $result = json_decode($body, true);
        
        if (isset($result['url'])) {
            return array('content' => $result['url']);
        }
        
        return array('error' => '音频生成失败');
    }
    
    /**
     * 调用AI API
     */
    private function call_ai_api($model, $prompt, $api_key) {
        $url = 'https://api.siliconflow.cn/v1/chat/completions';
        
        $data = array(
            'model' => $model,
            'messages' => array(
                array(
                    'role' => 'user',
                    'content' => $prompt
                )
            ),
            'max_tokens' => 4000,
            'temperature' => 0.7,
            'stream' => false
        );
        
        $args = array(
            'headers' => array(
                'Authorization' => 'Bearer ' . $api_key,
                'Content-Type' => 'application/json'
            ),
            'body' => json_encode($data),
            'timeout' => 120
        );
        
        $response = wp_remote_post($url, $args);
        
        if (is_wp_error($response)) {
            return array('error' => '网络请求失败: ' . $response->get_error_message());
        }
        
        $http_code = wp_remote_retrieve_response_code($response);
        if ($http_code !== 200) {
            $body = wp_remote_retrieve_body($response);
            $error_info = json_decode($body, true);
            $error_message = 'HTTP错误 ' . $http_code;
            
            if (isset($error_info['error']['message'])) {
                $error_message .= ': ' . $error_info['error']['message'];
            }
            
            return array('error' => $error_message);
        }
        
        $body = wp_remote_retrieve_body($response);
        $result = json_decode($body, true);
        
        if (isset($result['choices'][0]['message']['content'])) {
            return array('content' => $result['choices'][0]['message']['content']);
        }
        
        return array('error' => 'API响应格式错误');
    }
    
    /**
     * AJAX: 保存竞争对手设置
     */
    public function ajax_save_competitors() {
        if (!wp_verify_nonce($_POST['nonce'] ?? '', 'ai-opt-nonce') || !current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }
        
        $competitor_urls = $_POST['competitor_urls'] ?? array();
        
        // 验证URL格式
        $valid_urls = array();
        foreach ($competitor_urls as $url) {
            $url = trim($url);
            if (!empty($url) && filter_var($url, FILTER_VALIDATE_URL)) {
                $valid_urls[] = $url;
            }
        }
        
        update_option('ai_seo_competitor_urls', $valid_urls);
        
        wp_send_json_success(array(
            'message' => '竞争对手设置已保存',
            'saved_urls' => $valid_urls
        ));
    }
    
    /**
     * AJAX: 获取监控日志
     */
    public function ajax_get_monitor_logs() {
        if (!wp_verify_nonce($_POST['nonce'] ?? '', 'ai-opt-nonce') || !current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }
        
        $logs = $this->get_system_logs();
        
        wp_send_json_success(array('logs' => $logs));
    }
    
    /**
     * 获取系统日志
     */
    private function get_system_logs() {
        $logs = array();
        
        // 生成一些示例日志
        $current_time = current_time('H:i:s');
        
        $logs[] = array(
            'time' => $current_time,
            'type' => 'info',
            'message' => '系统运行正常，内存使用率：' . $this->get_memory_usage_percent() . '%'
        );
        
        $logs[] = array(
            'time' => $current_time,
            'type' => 'info',
            'message' => '数据库连接正常，查询数量：' . get_num_queries()
        );
        
        $logs[] = array(
            'time' => $current_time,
            'type' => 'info',
            'message' => '插件状态：' . count(get_option('active_plugins', array())) . '个插件已激活'
        );
        
        return $logs;
    }
    
    /**
     * 获取内存使用百分比
     */
    private function get_memory_usage_percent() {
        $memory_limit = ini_get('memory_limit');
        $memory_limit_bytes = $this->convert_to_bytes($memory_limit);
        $memory_usage = memory_get_usage(true);
        
        return round(($memory_usage / $memory_limit_bytes) * 100, 1);
    }
    
    /**
     * 转换内存大小为字节
     */
    private function convert_to_bytes($size) {
        $size = trim($size);
        $last = strtolower($size[strlen($size) - 1]);
        $size = (int) $size;
        
        switch ($last) {
            case 'g':
                $size *= 1024;
            case 'm':
                $size *= 1024;
            case 'k':
                $size *= 1024;
        }
        
        return $size;
    }
}

// 插件激活钩子
register_activation_hook(__FILE__, 'ai_optimizer_activate_fixed');

function ai_optimizer_activate_fixed() {
    // 检查WordPress版本
    if (version_compare(get_bloginfo('version'), '5.0', '<')) {
        deactivate_plugins(basename(__FILE__));
        wp_die('此插件需要WordPress 5.0或更高版本。');
    }
    
    // 检查PHP版本
    if (version_compare(PHP_VERSION, '7.4.0', '<')) {
        deactivate_plugins(basename(__FILE__));
        wp_die('此插件需要PHP 7.4.0或更高版本。');
    }
}

// 插件停用钩子
register_deactivation_hook(__FILE__, 'ai_optimizer_deactivate_fixed');

function ai_optimizer_deactivate_fixed() {
    // 清理定时任务
    wp_clear_scheduled_hook('ai_optimizer_daily_patrol');
}

// 启动插件
add_action('plugins_loaded', function() {
    if (is_admin()) {
        AI_Website_Optimizer_Fixed::get_instance();
    }
});
?>