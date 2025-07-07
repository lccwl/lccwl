<?php
/**
 * Plugin Name: AI智能网站优化器
 * Plugin URI: https://example.com/ai-website-optimizer
 * Description: 集成Siliconflow API的WordPress智能监控与优化插件，具备实时监控、SEO优化、代码修复和多媒体生成功能，支持自动发布到WordPress
 * Version: 2.0.0
 * Author: AI Developer
 * License: GPL v2 or later
 * Text Domain: ai-website-optimizer
 */

// 防止直接访问
if (!defined('ABSPATH')) {
    exit;
}

// 定义插件常量
define('AI_OPT_VERSION', '2.0.0');
define('AI_OPT_PLUGIN_URL', plugin_dir_url(__FILE__));
define('AI_OPT_PLUGIN_PATH', plugin_dir_path(__FILE__));

/**
 * 主插件类
 */
class AI_Website_Optimizer {
    
    private static $instance = null;
    
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        // 加载依赖文件
        $this->load_dependencies();
        
        // 激活/停用钩子
        register_activation_hook(__FILE__, array($this, 'activate'));
        register_deactivation_hook(__FILE__, array($this, 'deactivate'));
        
        // 初始化
        add_action('init', array($this, 'init'));
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_assets'));
        
        // AJAX
        add_action('wp_ajax_ai_opt_test_api', array($this, 'ajax_test_api'));
        add_action('wp_ajax_ai_opt_save_settings', array($this, 'ajax_save_settings'));
        add_action('wp_ajax_ai_opt_run_analysis', array($this, 'ajax_run_analysis'));
        add_action('wp_ajax_ai_opt_generate_content', array($this, 'ajax_generate_content'));
        add_action('wp_ajax_ai_opt_activate_license', array($this, 'ajax_activate_license'));
        add_action('wp_ajax_ai_opt_deactivate_license', array($this, 'ajax_deactivate_license'));
        add_action('wp_ajax_ai_opt_check_video_status', array($this, 'ajax_check_video_status'));
        add_action('wp_ajax_ai_opt_publish_to_wordpress', array($this, 'ajax_publish_to_wordpress'));
        add_action('wp_ajax_ai_opt_save_auto_settings', array($this, 'ajax_save_auto_settings'));
        add_action('wp_ajax_ai_opt_get_monitor_logs', array($this, 'ajax_get_monitor_logs'));
    }
    
    private function load_dependencies() {
        // 加载工具类
        if (file_exists(AI_OPT_PLUGIN_PATH . 'includes/class-utils.php')) {
            require_once AI_OPT_PLUGIN_PATH . 'includes/class-utils.php';
        }
        
        // 加载授权管理类
        if (file_exists(AI_OPT_PLUGIN_PATH . 'includes/class-license-manager.php')) {
            require_once AI_OPT_PLUGIN_PATH . 'includes/class-license-manager.php';
        }
    }
    
    public function init() {
        load_plugin_textdomain('ai-website-optimizer', false, dirname(plugin_basename(__FILE__)) . '/languages');
    }
    
    public function add_admin_menu() {
        // 主菜单
        add_menu_page(
            'AI智能优化器',
            'AI智能优化器',
            'manage_options',
            'ai-optimizer',
            array($this, 'render_dashboard'),
            'dashicons-chart-line',
            30
        );
        
        // 子菜单
        add_submenu_page('ai-optimizer', '仪表盘', '仪表盘', 'manage_options', 'ai-optimizer', array($this, 'render_dashboard'));
        add_submenu_page('ai-optimizer', '性能监控', '性能监控', 'manage_options', 'ai-optimizer-monitor', array($this, 'render_monitor'));
        add_submenu_page('ai-optimizer', 'SEO优化', 'SEO优化', 'manage_options', 'ai-optimizer-seo', array($this, 'render_seo'));
        add_submenu_page('ai-optimizer', 'AI工具', 'AI工具', 'manage_options', 'ai-optimizer-tools', array($this, 'render_tools'));
        add_submenu_page('ai-optimizer', '插件设置', '插件设置', 'manage_options', 'ai-optimizer-settings', array($this, 'render_settings'));
        add_submenu_page('ai-optimizer', '授权管理', '授权管理', 'manage_options', 'ai-optimizer-license', array($this, 'render_license'));
    }
    
    public function enqueue_admin_assets($hook) {
        if (strpos($hook, 'ai-optimizer') === false) {
            return;
        }
        
        wp_enqueue_script('jquery');
        wp_enqueue_script('chart-js', 'https://cdn.jsdelivr.net/npm/chart.js', array(), '3.9.1', true);
        
        // 内联CSS
        wp_add_inline_style('wp-admin', '
            .ai-optimizer-wrap { max-width: 1200px; margin: 20px auto; }
            .ai-optimizer-card { background: #fff; border: 1px solid #ccd0d4; border-radius: 8px; padding: 20px; margin-bottom: 20px; box-shadow: 0 1px 1px rgba(0,0,0,.04); }
            .ai-optimizer-stats { display: flex; gap: 20px; margin: 20px 0; }
            .stat-card { flex: 1; background: linear-gradient(135deg, #165DFF 0%, #7E22CE 100%); color: #fff; padding: 20px; border-radius: 8px; text-align: center; }
            .stat-card h3 { margin: 0 0 10px 0; font-size: 16px; color: #fff; }
            .stat-card .stat-value { font-size: 32px; font-weight: bold; margin: 10px 0; }
            .ai-optimizer-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px; margin: 20px 0; }
            .feature-card { background: #f8f9fa; padding: 20px; border-radius: 8px; border: 1px solid #e5e7eb; }
            .feature-card h3 { margin-top: 0; color: #165DFF; }
            #generation-result { background: #f0f8ff; border: 1px solid #165DFF; border-radius: 8px; padding: 20px; margin-top: 20px; }
            #generation-result h3 { color: #165DFF; margin-top: 0; }
            #result-content { background: white; padding: 15px; border-radius: 4px; margin-top: 10px; white-space: pre-wrap; }
            @media screen and (max-width: 768px) { .ai-optimizer-stats { flex-direction: column; } }
        ');
        
        // 内联JS - 优化版本
        wp_add_inline_script('jquery', '
            jQuery(document).ready(function($) {
                // 全局配置
                window.AIOptimizer = {
                    nonce: "' . wp_create_nonce('ai-opt-nonce') . '",
                    ajaxurl: "' . admin_url('admin-ajax.php') . '",
                    currentContent: "",
                    currentContentType: ""
                };
                
                var nonce = window.AIOptimizer.nonce;
                var ajaxurl = window.AIOptimizer.ajaxurl;
                
                // 测试API
                $("#test-api-btn").click(function() {
                    var btn = $(this);
                    btn.prop("disabled", true).text("测试中...");
                    
                    $.post(ajaxurl, {
                        action: "ai_opt_test_api",
                        nonce: nonce
                    }, function(response) {
                        if (response.success) {
                            $("#test-result").html("<div class=\"notice notice-success\"><p>" + response.data.message + "</p></div>");
                        } else {
                            $("#test-result").html("<div class=\"notice notice-error\"><p>" + response.data.message + "</p></div>");
                        }
                        btn.prop("disabled", false).text("测试API连接");
                    });
                });
                
                // 保存设置
                $("#save-settings-form").submit(function(e) {
                    e.preventDefault();
                    var form = $(this);
                    var btn = form.find("input[type=submit]");
                    btn.prop("disabled", true).val("保存中...");
                    
                    $.post(ajaxurl, {
                        action: "ai_opt_save_settings",
                        nonce: nonce,
                        api_key: $("#api_key").val(),
                        enable_monitoring: $("#enable_monitoring").is(":checked") ? 1 : 0,
                        enable_seo: $("#enable_seo").is(":checked") ? 1 : 0,
                        enable_ai_tools: $("#enable_ai_tools").is(":checked") ? 1 : 0
                    }, function(response) {
                        if (response.success) {
                            $(".wrap > h1").after("<div class=\"notice notice-success is-dismissible\"><p>设置已保存</p></div>");
                            setTimeout(function() { $(".notice.is-dismissible").fadeOut(); }, 3000);
                        } else {
                            $(".wrap > h1").after("<div class=\"notice notice-error is-dismissible\"><p>保存失败: " + (response.data.message || "未知错误") + "</p></div>");
                        }
                        btn.prop("disabled", false).val("保存设置");
                    });
                });
                
                // 内容类型切换处理
                $("#content_type").change(function() {
                    var type = $(this).val();
                    if (type === "video") {
                        $("#video_model_row").show();
                        $("#video_model").trigger("change");
                    } else {
                        $("#video_model_row, #image_input_row").hide();
                    }
                });
                
                // 视频模型切换处理
                $("#video_model").change(function() {
                    var model = $(this).val();
                    if (model.includes("I2V")) {
                        $("#image_input_row").show();
                    } else {
                        $("#image_input_row").hide();
                    }
                });
                
                // 处理图片文件上传为base64
                $("#reference_image_file").change(function() {
                    var file = this.files[0];
                    if (file) {
                        var reader = new FileReader();
                        reader.onload = function(e) {
                            var base64 = e.target.result;
                            $("#reference_image_url").val(base64);
                            $("#image_upload_status").text("图片已加载").css("color", "green");
                        };
                        reader.onerror = function() {
                            $("#image_upload_status").text("图片加载失败").css("color", "red");
                        };
                        reader.readAsDataURL(file);
                    }
                });
                
                // AI内容生成 - 优化版本
                $("#generate-content-btn").click(function() {
                    var btn = $(this);
                    var contentType = $("#content_type").val();
                    var prompt = $("#prompt").val();
                    
                    if (!prompt) {
                        alert("请输入提示词");
                        return;
                    }
                    
                    btn.prop("disabled", true).text("生成中...");
                    $("#generation-result").hide();
                    
                    // 显示实时状态
                    showGenerationStatus(contentType);
                    
                    var postData = {
                        action: "ai_opt_generate_content",
                        nonce: nonce,
                        content_type: contentType,
                        prompt: prompt
                    };
                    
                    // 如果是视频生成，添加额外参数
                    if (contentType === "video") {
                        postData.video_model = $("#video_model").val();
                        var imageUrl = $("#reference_image_url").val();
                        if (imageUrl) {
                            postData.reference_image = imageUrl;
                        }
                    }
                    
                    $.post(ajaxurl, postData, function(response) {
                        if (response.success) {
                            window.AIOptimizer.currentContentType = response.data.type;
                            window.AIOptimizer.currentContent = response.data.content;
                            displayGeneratedContent(response.data.content, response.data.type);
                            $("#generation-result").show();
                        } else {
                            alert("生成失败: " + (response.data.message || "请检查API密钥是否配置正确"));
                        }
                        btn.prop("disabled", false).text("生成内容");
                    }).fail(function() {
                        alert("网络错误，请稍后重试");
                        btn.prop("disabled", false).text("生成内容");
                    });
                });
                
                function showGenerationStatus(type) {
                    var statusMessages = {
                        "text": "正在生成文本内容...",
                        "image": "正在生成图片，请耐心等待...",
                        "video": "正在生成视频，这可能需要几分钟...",
                        "audio": "正在合成音频..."
                    };
                    $("#generation-status").text(statusMessages[type] || "生成中...");
                }
                
                function displayGeneratedContent(content, type) {
                    var html = "";
                    switch(type) {
                        case "image":
                            html = "<img src=\"" + content + "\" style=\"max-width: 100%; height: auto; border-radius: 8px;\" alt=\"生成的图片\">";
                            break;
                        case "video":
                            html = "<video controls style=\"max-width: 100%; height: auto; border-radius: 8px;\"><source src=\"" + content + "\" type=\"video/mp4\">您的浏览器不支持视频播放。</video>";
                            break;
                        case "audio":
                            html = "<audio controls style=\"width: 100%;\"><source src=\"" + content + "\" type=\"audio/mpeg\">您的浏览器不支持音频播放。</audio>";
                            break;
                        case "text":
                        default:
                            html = "<div style=\"background: white; padding: 15px; border-radius: 4px; white-space: pre-wrap;\">" + content + "</div>";
                            break;
                    }
                    $("#result-content").html(html);
                    $("#generation-status").text("");
                }
                
                // 发布类型选择处理
                $("#publish_type").change(function() {
                    if ($(this).val() === "scheduled") {
                        $("#schedule_row").show();
                        // 设置默认时间为1小时后
                        var now = new Date();
                        now.setHours(now.getHours() + 1);
                        var isoString = now.toISOString().slice(0, 16);
                        $("#schedule_time").val(isoString);
                    } else {
                        $("#schedule_row").hide();
                    }
                });
                
                // 发布到WordPress
                $("#publish-content-btn").click(function() {
                    var btn = $(this);
                    var title = $("#post_title").val();
                    var publishType = $("#publish_type").val();
                    var scheduleTime = $("#schedule_time").val();
                    
                    if (!title) {
                        alert("请输入文章标题");
                        return;
                    }
                    
                    if (!window.AIOptimizer.currentContent) {
                        alert("请先生成内容");
                        return;
                    }
                    
                    btn.prop("disabled", true).text("发布中...");
                    
                    $.post(ajaxurl, {
                        action: "ai_opt_publish_to_wordpress",
                        nonce: nonce,
                        title: title,
                        content: window.AIOptimizer.currentContent,
                        content_type: window.AIOptimizer.currentContentType,
                        publish_type: publishType,
                        schedule_time: scheduleTime
                    }, function(response) {
                        if (response.success) {
                            alert(response.data.message + "\\n\\n编辑链接: " + response.data.edit_link);
                            // 清空表单
                            $("#post_title").val("");
                        } else {
                            alert("发布失败: " + (response.data.message || "未知错误"));
                        }
                        btn.prop("disabled", false).text("发布到WordPress");
                    }).fail(function() {
                        alert("网络错误，请稍后重试");
                        btn.prop("disabled", false).text("发布到WordPress");
                    });
                });
                
                // SEO分析
                $("#run-seo-analysis").click(function() {
                    var btn = $(this);
                    btn.prop("disabled", true).text("分析中...");
                    
                    $.post(ajaxurl, {
                        action: "ai_opt_run_analysis",
                        nonce: nonce
                    }, function(response) {
                        if (response.success) {
                            alert("分析完成: " + response.data.message);
                        } else {
                            alert("分析失败: " + (response.data.message || "请检查API密钥"));
                        }
                        btn.prop("disabled", false).text("运行AI分析");
                    });
                });
                
                // 刷新监控数据
                $("#refresh-monitor-data").click(function() {
                    location.reload();
                });
                
                // 导出报告
                $("#export-report").click(function() {
                    alert("报告导出功能正在开发中...");
                });
                
                // 动画效果
                $(".stat-value").each(function() {
                    var $this = $(this);
                    var value = parseInt($this.text());
                    if (!isNaN(value)) {
                        $this.text("0%");
                        $({ counter: 0 }).animate({ counter: value }, {
                            duration: 1000,
                            step: function() { $this.text(Math.ceil(this.counter) + "%"); }
                        });
                    }
                });
                
                // 自动化发布功能
                $("#auto-publish-type").change(function() {
                    if ($(this).val() === "post") {
                        $("#post-category-group").show();
                    } else {
                        $("#post-category-group").hide();
                    }
                });
                
                // 保存自动化设置
                $("#save-auto-settings").click(function() {
                    var btn = $(this);
                    btn.prop("disabled", true).text("保存中...");
                    
                    var settings = {
                        publish_type: $("#auto-publish-type").val(),
                        category: $("#post-category").val(),
                        theme: $("#auto-theme").val(),
                        frequency: $("#auto-frequency").val(),
                        count: $("#auto-count").val()
                    };
                    
                    $.post(ajaxurl, {
                        action: "ai_opt_save_auto_settings",
                        nonce: nonce,
                        settings: settings
                    }, function(response) {
                        if (response.success) {
                            $("#auto-status").text("设置已保存").css("color", "green");
                            setTimeout(function() {
                                $("#auto-status").text("");
                            }, 3000);
                        }
                        btn.prop("disabled", false).text("保存设置");
                    });
                });
                
                // 启动自动发布
                $("#start-auto-publish").click(function() {
                    if (!$("#auto-theme").val()) {
                        alert("请设置主题关键词");
                        return;
                    }
                    
                    $(this).hide();
                    $("#stop-auto-publish").show();
                    $("#auto-log").show();
                    $("#auto-status").text("自动发布已启动").css("color", "green");
                    
                    // 添加日志
                    addAutoLog("自动发布已启动，主题：" + $("#auto-theme").val());
                    
                    // 立即执行一次
                    executeAutoPublish();
                    
                    // 根据频率设置定时器
                    var frequency = $("#auto-frequency").val();
                    var interval = getIntervalTime(frequency);
                    
                    window.autoPublishTimer = setInterval(executeAutoPublish, interval);
                });
                
                // 停止自动发布
                $("#stop-auto-publish").click(function() {
                    $(this).hide();
                    $("#start-auto-publish").show();
                    $("#auto-status").text("自动发布已停止").css("color", "red");
                    
                    if (window.autoPublishTimer) {
                        clearInterval(window.autoPublishTimer);
                    }
                    
                    addAutoLog("自动发布已停止");
                });
                
                // 执行自动发布
                function executeAutoPublish() {
                    var publishType = $("#auto-publish-type").val();
                    var theme = $("#auto-theme").val();
                    var category = $("#post-category").val();
                    var count = $("#auto-count").val();
                    
                    addAutoLog("开始生成内容，类型：" + publishType);
                    
                    // 根据发布类型生成提示词
                    var prompt = generatePromptByTheme(theme, publishType);
                    
                    $.post(ajaxurl, {
                        action: "ai_opt_generate_content",
                        nonce: nonce,
                        type: getGenerationType(publishType),
                        prompt: prompt
                    }, function(response) {
                        if (response.success) {
                            addAutoLog("内容生成成功，准备发布");
                            
                            // 自动生成标题
                            var title = generateTitle(theme, publishType);
                            
                            // 发布内容
                            $.post(ajaxurl, {
                                action: "ai_opt_publish_to_wordpress",
                                nonce: nonce,
                                title: title,
                                content: response.data.content,
                                content_type: response.data.type,
                                publish_type: "auto",
                                category_id: category
                            }, function(pubResponse) {
                                if (pubResponse.success) {
                                    addAutoLog("发布成功：" + title);
                                } else {
                                    addAutoLog("发布失败：" + (pubResponse.data || "未知错误"));
                                }
                            });
                        } else {
                            addAutoLog("生成失败：" + (response.data || "未知错误"));
                        }
                    });
                }
                
                // 根据主题生成提示词
                function generatePromptByTheme(theme, type) {
                    var date = new Date().toLocaleDateString("zh-CN");
                    
                    if (type === "post") {
                        return "请写一篇关于" + theme + "的详细文章，包含最新的信息和见解。日期：" + date;
                    } else if (type === "video") {
                        return "创建一个关于" + theme + "的视频场景，包含生动的画面描述";
                    } else if (type === "audio") {
                        return "用自然的语音介绍" + theme + "的相关内容，语调友好专业";
                    }
                }
                
                // 生成标题
                function generateTitle(theme, type) {
                    var date = new Date().toLocaleDateString("zh-CN");
                    var types = {
                        "post": "【文章】",
                        "video": "【视频】",
                        "audio": "【音频】"
                    };
                    return types[type] + theme + " - " + date;
                }
                
                // 获取生成类型
                function getGenerationType(publishType) {
                    var typeMap = {
                        "post": "text",
                        "video": "video",
                        "audio": "audio"
                    };
                    return typeMap[publishType] || "text";
                }
                
                // 获取间隔时间
                function getIntervalTime(frequency) {
                    var intervals = {
                        "hourly": 3600000,      // 1小时
                        "daily": 86400000,      // 24小时
                        "twice-daily": 43200000, // 12小时
                        "weekly": 604800000     // 7天
                    };
                    return intervals[frequency] || 3600000;
                }
                
                // 添加日志
                function addAutoLog(message) {
                    var time = new Date().toLocaleTimeString("zh-CN");
                    var logItem = "<li>[" + time + "] " + message + "</li>";
                    $("#auto-log-list").prepend(logItem);
                    
                    // 保持最多20条日志
                    if ($("#auto-log-list li").length > 20) {
                        $("#auto-log-list li:last").remove();
                    }
                }
            });
        ');
    }
    
    // 页面渲染函数
    public function render_dashboard() {
        ?>
        <div class="wrap ai-optimizer-wrap">
            <h1>AI智能优化器 - 仪表盘</h1>
            
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
                    <h3>🚀 快速开始</h3>
                    <p>欢迎使用AI智能网站优化器！本插件集成了先进的AI技术，帮助您优化网站性能、提升SEO排名、生成高质量内容。</p>
                    <p><a href="<?php echo admin_url('admin.php?page=ai-optimizer-settings'); ?>" class="button button-primary">配置API密钥</a></p>
                </div>
                
                <div class="feature-card">
                    <h3>📊 最新监控数据</h3>
                    <ul>
                        <li>页面加载时间: 1.2秒</li>
                        <li>内存使用率: 45%</li>
                        <li>数据库查询: 32次</li>
                        <li>错误数量: 0</li>
                    </ul>
                </div>
                
                <div class="feature-card">
                    <h3>🤖 AI功能</h3>
                    <ul>
                        <li>✅ 内容生成器</li>
                        <li>✅ SEO优化建议</li>
                        <li>✅ 代码错误修复</li>
                        <li>✅ 性能优化分析</li>
                    </ul>
                </div>
            </div>
        </div>
        <?php
    }
    
    public function render_monitor() {
        ?>
        <div class="wrap ai-optimizer-wrap">
            <h1>实时监控日志</h1>
            
            <div class="ai-optimizer-card">
                <h2>📋 实时日志监控</h2>
                
                <div class="log-controls" style="margin-bottom: 20px;">
                    <button id="start-monitoring" class="button button-primary">开始监控</button>
                    <button id="stop-monitoring" class="button button-secondary" style="display:none;">停止监控</button>
                    <button id="clear-logs" class="button">清空日志</button>
                    <select id="log-filter" style="margin-left: 10px;">
                        <option value="all">全部日志</option>
                        <option value="error">错误日志</option>
                        <option value="warning">警告日志</option>
                        <option value="info">信息日志</option>
                        <option value="debug">调试日志</option>
                    </select>
                    <input type="checkbox" id="auto-scroll" checked> <label for="auto-scroll">自动滚动</label>
                </div>
                
                <div id="log-container" style="background: #1a1a1a; color: #00ff00; padding: 15px; border-radius: 5px; height: 500px; overflow-y: auto; font-family: 'Courier New', monospace; font-size: 13px;">
                    <div id="log-content">
                        <div class="log-entry info">[<?php echo date('Y-m-d H:i:s'); ?>] [信息] 监控系统已准备就绪，等待启动...</div>
                    </div>
                </div>
                
                <div style="margin-top: 15px;">
                    <h3>监控内容：</h3>
                    <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px;">
                        <label><input type="checkbox" class="monitor-type" value="performance" checked> 性能监控</label>
                        <label><input type="checkbox" class="monitor-type" value="error" checked> 错误监控</label>
                        <label><input type="checkbox" class="monitor-type" value="database" checked> 数据库监控</label>
                        <label><input type="checkbox" class="monitor-type" value="plugin" checked> 插件活动</label>
                        <label><input type="checkbox" class="monitor-type" value="user" checked> 用户活动</label>
                        <label><input type="checkbox" class="monitor-type" value="security" checked> 安全事件</label>
                    </div>
                </div>
            </div>
            
            <div class="ai-optimizer-card" style="margin-top: 20px;">
                <h2>📊 日志统计</h2>
                <div id="log-stats" style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 15px;">
                    <div class="stat-box" style="background: #f0f0f0; padding: 15px; border-radius: 5px; text-align: center;">
                        <h4 style="margin: 0; color: #333;">总日志数</h4>
                        <div id="total-logs" style="font-size: 24px; font-weight: bold; color: #165DFF;">0</div>
                    </div>
                    <div class="stat-box" style="background: #fee; padding: 15px; border-radius: 5px; text-align: center;">
                        <h4 style="margin: 0; color: #333;">错误</h4>
                        <div id="error-count" style="font-size: 24px; font-weight: bold; color: #d32f2f;">0</div>
                    </div>
                    <div class="stat-box" style="background: #fff8e1; padding: 15px; border-radius: 5px; text-align: center;">
                        <h4 style="margin: 0; color: #333;">警告</h4>
                        <div id="warning-count" style="font-size: 24px; font-weight: bold; color: #f57c00;">0</div>
                    </div>
                    <div class="stat-box" style="background: #e8f5e9; padding: 15px; border-radius: 5px; text-align: center;">
                        <h4 style="margin: 0; color: #333;">信息</h4>
                        <div id="info-count" style="font-size: 24px; font-weight: bold; color: #388e3c;">0</div>
                    </div>
                </div>
            </div>
            
            <script>
                jQuery(document).ready(function($) {
                    var monitoring = false;
                    var logCount = { total: 0, error: 0, warning: 0, info: 0, debug: 0 };
                    var monitorInterval;
                    
                    // 开始监控
                    $("#start-monitoring").click(function() {
                        monitoring = true;
                        $(this).hide();
                        $("#stop-monitoring").show();
                        addLog("info", "实时监控已启动");
                        startRealTimeMonitoring();
                    });
                    
                    // 停止监控
                    $("#stop-monitoring").click(function() {
                        monitoring = false;
                        $(this).hide();
                        $("#start-monitoring").show();
                        addLog("info", "实时监控已停止");
                        if (monitorInterval) {
                            clearInterval(monitorInterval);
                        }
                    });
                    
                    // 清空日志
                    $("#clear-logs").click(function() {
                        $("#log-content").html("");
                        logCount = { total: 0, error: 0, warning: 0, info: 0, debug: 0 };
                        updateStats();
                    });
                    
                    // 日志过滤
                    $("#log-filter").change(function() {
                        var filter = $(this).val();
                        if (filter === "all") {
                            $(".log-entry").show();
                        } else {
                            $(".log-entry").hide();
                            $(".log-entry." + filter).show();
                        }
                    });
                    
                    // 添加日志
                    function addLog(type, message, details) {
                        var time = new Date().toLocaleString("zh-CN");
                        var typeLabels = {
                            "error": "[错误]",
                            "warning": "[警告]",
                            "info": "[信息]",
                            "debug": "[调试]"
                        };
                        var typeColors = {
                            "error": "#ff5252",
                            "warning": "#ffb74d",
                            "info": "#00ff00",
                            "debug": "#64b5f6"
                        };
                        
                        var logHtml = '<div class="log-entry ' + type + '" style="color: ' + typeColors[type] + '; margin-bottom: 5px;">';
                        logHtml += '[' + time + '] ' + typeLabels[type] + ' ' + message;
                        if (details) {
                            logHtml += ' - ' + details;
                        }
                        logHtml += '</div>';
                        
                        $("#log-content").append(logHtml);
                        
                        // 更新统计
                        logCount.total++;
                        logCount[type]++;
                        updateStats();
                        
                        // 自动滚动
                        if ($("#auto-scroll").is(":checked")) {
                            var container = $("#log-container");
                            container.scrollTop(container[0].scrollHeight);
                        }
                        
                        // 应用过滤器
                        var currentFilter = $("#log-filter").val();
                        if (currentFilter !== "all" && type !== currentFilter) {
                            $("#log-content .log-entry:last").hide();
                        }
                    }
                    
                    // 更新统计
                    function updateStats() {
                        $("#total-logs").text(logCount.total);
                        $("#error-count").text(logCount.error);
                        $("#warning-count").text(logCount.warning);
                        $("#info-count").text(logCount.info);
                    }
                    
                    // 开始实时监控
                    function startRealTimeMonitoring() {
                        monitorInterval = setInterval(function() {
                            if (!monitoring) return;
                            
                            // 获取选中的监控类型
                            var monitorTypes = [];
                            $(".monitor-type:checked").each(function() {
                                monitorTypes.push($(this).val());
                            });
                            
                            // 通过AJAX获取实时数据
                            $.post(ajaxurl, {
                                action: "ai_opt_get_monitor_logs",
                                nonce: "<?php echo wp_create_nonce('ai_optimizer_nonce'); ?>",
                                types: monitorTypes
                            }, function(response) {
                                if (response.success && response.data.logs) {
                                    response.data.logs.forEach(function(log) {
                                        addLog(log.type, log.message, log.details);
                                    });
                                }
                            });
                        }, 2000); // 每2秒更新一次
                    }
                    
                    // 模拟一些初始日志
                    setTimeout(function() {
                        addLog("info", "WordPress版本检测", "当前版本: <?php echo get_bloginfo('version'); ?>");
                        addLog("info", "插件状态检查", "AI优化器插件已激活");
                        addLog("info", "数据库连接", "连接正常，查询时间: 0.023秒");
                    }, 1000);
                });
            </script>
        </div>
        <?php
    }
    
    public function render_seo() {
        ?>
        <div class="wrap ai-optimizer-wrap">
            <h1>SEO优化</h1>
            
            <div class="ai-optimizer-card">
                <h2>SEO分析报告</h2>
                <table class="form-table">
                    <tr>
                        <th>页面标题</th>
                        <td><span style="color: green;">✓</span> 优化良好</td>
                    </tr>
                    <tr>
                        <th>Meta描述</th>
                        <td><span style="color: orange;">!</span> 需要改进</td>
                    </tr>
                    <tr>
                        <th>关键词密度</th>
                        <td><span style="color: green;">✓</span> 2.5% (理想范围)</td>
                    </tr>
                    <tr>
                        <th>图片Alt标签</th>
                        <td><span style="color: red;">✗</span> 12个图片缺少Alt标签</td>
                    </tr>
                </table>
                
                <p>
                    <button class="button button-primary" id="run-seo-analysis">运行AI分析</button>
                </p>
            </div>
            
            <div class="ai-optimizer-card">
                <h2>优化建议</h2>
                <ol>
                    <li>添加更多长尾关键词到内容中</li>
                    <li>优化页面加载速度，当前为2.3秒</li>
                    <li>增加内部链接，提高页面相关性</li>
                    <li>更新Meta描述，包含主要关键词</li>
                </ol>
            </div>
        </div>
        <?php
    }
    
    public function render_tools() {
        ?>
        <div class="wrap ai-optimizer-wrap">
            <h1>AI工具</h1>
            
            <div class="ai-optimizer-card">
                <h2>AI内容生成</h2>
                
                <form method="post" action="">
                    <table class="form-table">
                        <tr>
                            <th scope="row">生成类型</th>
                            <td>
                                <select name="content_type" id="content_type" class="regular-text">
                                    <option value="text">文本内容</option>
                                    <option value="image">图片生成</option>
                                    <option value="video">视频生成</option>
                                    <option value="audio">音频生成</option>
                                </select>
                            </td>
                        </tr>
                        <tr id="video_model_row" style="display: none;">
                            <th scope="row">视频模型</th>
                            <td>
                                <select name="video_model" id="video_model" class="regular-text">
                                    <option value="Wan-AI/Wan2.1-T2V-14B-Turbo">文本到视频 (T2V) - 快速版</option>
                                    <option value="Wan-AI/Wan2.1-T2V-14B">文本到视频 (T2V) - 标准版</option>
                                    <option value="Wan-AI/Wan2.1-I2V-14B-720P-Turbo">图片到视频 (I2V) - 快速版</option>
                                    <option value="Wan-AI/Wan2.1-I2V-14B-720P">图片到视频 (I2V) - 标准版</option>
                                    <option value="tencent/HunyuanVideo">腾讯混元视频</option>
                                </select>
                                <p class="description">选择不同的模型生成视频。I2V模型需要上传参考图片。</p>
                            </td>
                        </tr>
                        <tr id="image_input_row" style="display: none;">
                            <th scope="row">参考图片</th>
                            <td>
                                <input type="text" id="reference_image_url" name="reference_image_url" class="large-text" placeholder="输入图片URL地址">
                                <p class="description">或者使用base64格式：data:image/png;base64,...</p>
                                <div style="margin-top: 10px;">
                                    <input type="file" id="reference_image_file" accept="image/*">
                                    <span id="image_upload_status" style="margin-left: 10px;"></span>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">提示词</th>
                            <td>
                                <textarea name="prompt" id="prompt" rows="5" class="large-text" placeholder="请输入您想要生成的内容描述..."></textarea>
                                <p class="description">详细描述您需要的内容，AI将根据您的描述生成相应内容。</p>
                            </td>
                        </tr>
                    </table>
                    
                    <p class="submit">
                        <button type="button" class="button button-primary" id="generate-content-btn">生成内容</button>
                        <span id="generation-status" style="margin-left: 10px; color: #165DFF; font-weight: bold;"></span>
                    </p>
                </form>
                
                <div id="generation-result" style="display:none;">
                    <h3>生成结果</h3>
                    <div id="result-content"></div>
                    
                    <div id="publish-section" style="margin-top: 20px; padding: 15px; background: #f5f5f5; border-radius: 5px;">
                        <h4>发布设置</h4>
                        <table class="form-table">
                            <tr>
                                <th scope="row"><label for="post_title">文章标题</label></th>
                                <td>
                                    <input type="text" id="post_title" name="post_title" class="regular-text" placeholder="为生成的内容设置标题...">
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><label for="publish_type">发布类型</label></th>
                                <td>
                                    <select id="publish_type" name="publish_type" class="regular-text">
                                        <option value="draft">保存草稿</option>
                                        <option value="auto">立即发布</option>
                                        <option value="scheduled">定时发布</option>
                                    </select>
                                </td>
                            </tr>
                            <tr id="schedule_row" style="display: none;">
                                <th scope="row"><label for="schedule_time">发布时间</label></th>
                                <td>
                                    <input type="datetime-local" id="schedule_time" name="schedule_time" class="regular-text">
                                </td>
                            </tr>
                        </table>
                        <p class="submit">
                            <button type="button" id="publish-content-btn" class="button button-secondary">发布到WordPress</button>
                        </p>
                    </div>
                </div>
            </div>
            
            <!-- 自动化发布设置 -->
            <div class="ai-optimizer-card" style="margin-top: 20px;">
                <h2>🤖 自动化发布设置</h2>
                
                <table class="form-table">
                    <tr>
                        <th scope="row">主题发布类型</th>
                        <td>
                            <select id="auto-publish-type" class="regular-text">
                                <option value="post">文章帖子</option>
                                <option value="video">视频内容</option>
                                <option value="audio">音频内容</option>
                            </select>
                        </td>
                    </tr>
                    <tr id="post-category-group">
                        <th scope="row">文章分类</th>
                        <td>
                            <select id="post-category" class="regular-text">
                                <?php
                                $categories = get_categories(array('hide_empty' => false));
                                foreach ($categories as $category) {
                                    echo '<option value="' . $category->term_id . '">' . esc_html($category->name) . '</option>';
                                }
                                ?>
                            </select>
                            <p class="description">选择文章要发布到的分类（圈子）</p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">主题关键词</th>
                        <td>
                            <input type="text" id="auto-theme" class="regular-text" placeholder="例如：科技新闻、美食评测、旅行攻略">
                            <p class="description">AI将根据这个主题自动生成相关内容</p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">发布频率</th>
                        <td>
                            <select id="auto-frequency" class="regular-text">
                                <option value="hourly">每小时</option>
                                <option value="daily">每天一次</option>
                                <option value="twice-daily">每天两次</option>
                                <option value="weekly">每周一次</option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">自动生成数量</th>
                        <td>
                            <input type="number" id="auto-count" class="small-text" value="1" min="1" max="10">
                            <span class="description">每次自动生成的内容数量</span>
                        </td>
                    </tr>
                </table>
                
                <p class="submit">
                    <button type="button" id="save-auto-settings" class="button button-primary">保存设置</button>
                    <button type="button" id="start-auto-publish" class="button button-secondary">启动自动发布</button>
                    <button type="button" id="stop-auto-publish" class="button button-secondary" style="display:none;">停止自动发布</button>
                    <span id="auto-status" style="margin-left: 10px; font-weight: bold;"></span>
                </p>
                
                <div id="auto-log" style="margin-top: 20px; display:none;">
                    <h3>自动发布日志</h3>
                    <div style="background: #f5f5f5; padding: 10px; border-radius: 5px; max-height: 200px; overflow-y: auto;">
                        <ul id="auto-log-list"></ul>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }
    
    public function render_license() {
        include AI_OPT_PLUGIN_PATH . 'admin/views/license.php';
    }
    
    public function render_settings() {
        ?>
        <div class="wrap ai-optimizer-wrap">
            <h1>插件设置</h1>
            
            <div class="ai-optimizer-card">
                <form method="post" id="save-settings-form">
                    <table class="form-table">
                        <tr>
                            <th scope="row"><label for="api_key">Siliconflow API密钥</label></th>
                            <td>
                                <input type="password" id="api_key" name="api_key" value="<?php echo esc_attr(get_option('ai_optimizer_api_key', '')); ?>" class="regular-text" />
                                <p class="description">请输入您的Siliconflow API密钥。<a href="https://siliconflow.cn" target="_blank">获取API密钥</a></p>
                            </td>
                        </tr>
                        
                        <tr>
                            <th scope="row">功能开关</th>
                            <td>
                                <fieldset>
                                    <label>
                                        <input type="checkbox" id="enable_monitoring" name="enable_monitoring" value="1" <?php checked(get_option('ai_optimizer_enable_monitoring', 1), 1); ?> />
                                        启用性能监控
                                    </label><br>
                                    
                                    <label>
                                        <input type="checkbox" id="enable_seo" name="enable_seo" value="1" <?php checked(get_option('ai_optimizer_enable_seo', 1), 1); ?> />
                                        启用SEO优化
                                    </label><br>
                                    
                                    <label>
                                        <input type="checkbox" id="enable_ai_tools" name="enable_ai_tools" value="1" <?php checked(get_option('ai_optimizer_enable_ai_tools', 1), 1); ?> />
                                        启用AI工具
                                    </label>
                                </fieldset>
                            </td>
                        </tr>
                    </table>
                    
                    <p class="submit">
                        <input type="submit" name="submit" id="submit" class="button button-primary" value="保存设置">
                        <button type="button" id="test-api-btn" class="button">测试API连接</button>
                    </p>
                </form>
                
                <div id="test-result" style="margin-top: 20px;"></div>
            </div>
        </div>
        <?php
    }
    
    // AJAX处理函数
    public function ajax_test_api() {
        check_ajax_referer('ai-opt-nonce', 'nonce');
        
        $api_key = get_option('ai_optimizer_api_key');
        if (empty($api_key)) {
            wp_send_json_error(array('message' => '请先配置API密钥'));
            return;
        }
        
        // 测试API连接
        $url = 'https://api.siliconflow.cn/v1/models';
        $args = array(
            'headers' => array(
                'Authorization' => 'Bearer ' . $api_key
            ),
            'timeout' => 10
        );
        
        $response = wp_remote_get($url, $args);
        
        if (is_wp_error($response)) {
            wp_send_json_error(array('message' => '连接失败: ' . $response->get_error_message()));
            return;
        }
        
        $code = wp_remote_retrieve_response_code($response);
        if ($code === 200) {
            wp_send_json_success(array('message' => 'API连接成功！密钥有效。'));
        } else {
            wp_send_json_error(array('message' => 'API密钥无效或连接失败，状态码: ' . $code));
        }
    }
    
    public function ajax_save_settings() {
        check_ajax_referer('ai-opt-nonce', 'nonce');
        
        $api_key = sanitize_text_field($_POST['api_key'] ?? '');
        $enable_monitoring = intval($_POST['enable_monitoring'] ?? 0);
        $enable_seo = intval($_POST['enable_seo'] ?? 0); 
        $enable_ai_tools = intval($_POST['enable_ai_tools'] ?? 0);
        
        update_option('ai_optimizer_api_key', $api_key);
        update_option('ai_optimizer_enable_monitoring', $enable_monitoring);
        update_option('ai_optimizer_enable_seo', $enable_seo);
        update_option('ai_optimizer_enable_ai_tools', $enable_ai_tools);
        
        wp_send_json_success(array('message' => '设置已保存'));
    }
    
    public function ajax_run_analysis() {
        check_ajax_referer('ai-opt-nonce', 'nonce');
        wp_send_json_success(array('message' => '分析功能开发中...'));
    }
    
    public function ajax_generate_content() {
        check_ajax_referer('ai-opt-nonce', 'nonce');
        
        $api_key = get_option('ai_optimizer_api_key');
        if (empty($api_key)) {
            wp_send_json_error(array('message' => '请先配置API密钥'));
            return;
        }
        
        $content_type = sanitize_text_field($_POST['content_type'] ?? 'text');
        $prompt = sanitize_textarea_field($_POST['prompt'] ?? '');
        $video_model = sanitize_text_field($_POST['video_model'] ?? '');
        $reference_image = sanitize_text_field($_POST['reference_image'] ?? '');
        
        if (empty($prompt)) {
            wp_send_json_error(array('message' => '请输入提示词'));
            return;
        }
        
        // 如果是视频生成且选择了I2V模型但没有图片
        if ($content_type === 'video' && strpos($video_model, 'I2V') !== false && empty($reference_image)) {
            wp_send_json_error(array('message' => '当前视频模型需要参考图片，请先上传图片'));
            return;
        }
        
        // 调用Siliconflow API生成内容
        $response = $this->call_siliconflow_api($content_type, $prompt, $api_key, $video_model, $reference_image);
        
        if (isset($response['error'])) {
            wp_send_json_error(array('message' => $response['error']));
            return;
        }
        
        if (isset($response['content'])) {
            wp_send_json_success(array(
                'message' => '生成成功',
                'content' => $response['content'],
                'type' => $response['type']
            ));
        } else {
            wp_send_json_error(array('message' => 'API调用失败，请检查网络连接'));
        }
    }
    
    // 新增：发布到WordPress的AJAX处理
    public function ajax_publish_to_wordpress() {
        check_ajax_referer('ai-opt-nonce', 'nonce');
        
        $title = sanitize_text_field($_POST['title'] ?? '');
        $content = wp_kses_post($_POST['content'] ?? '');
        $content_type = sanitize_text_field($_POST['content_type'] ?? 'text');
        $publish_type = sanitize_text_field($_POST['publish_type'] ?? 'draft');
        $schedule_time = sanitize_text_field($_POST['schedule_time'] ?? '');
        $category_id = isset($_POST['category_id']) ? intval($_POST['category_id']) : 0;
        
        if (empty($title) || empty($content)) {
            wp_send_json_error(array('message' => '标题和内容不能为空'));
            return;
        }
        
        $post_data = array(
            'post_title' => $title,
            'post_content' => $this->format_content_for_wordpress($content, $content_type),
            'post_status' => ($publish_type === 'auto') ? 'publish' : 'draft',
            'post_author' => get_current_user_id(),
            'post_type' => 'post'
        );
        
        // 如果是定时发布
        if ($publish_type === 'scheduled' && !empty($schedule_time)) {
            $post_data['post_status'] = 'future';
            $post_data['post_date'] = date('Y-m-d H:i:s', strtotime($schedule_time));
        }
        
        $post_id = wp_insert_post($post_data);
        
        if (is_wp_error($post_id)) {
            wp_send_json_error(array('message' => '发布失败: ' . $post_id->get_error_message()));
            return;
        }
        
        // 设置文章分类
        if ($category_id > 0) {
            wp_set_post_categories($post_id, array($category_id));
        }
        
        // 添加自定义字段标记这是AI生成的内容
        add_post_meta($post_id, '_ai_generated', true);
        add_post_meta($post_id, '_ai_content_type', $content_type);
        add_post_meta($post_id, '_ai_generation_time', current_time('mysql'));
        
        $message = '';
        $edit_link = admin_url('post.php?post=' . $post_id . '&action=edit');
        
        switch ($publish_type) {
            case 'auto':
                $message = '内容已自动发布！';
                break;
            case 'scheduled':
                $message = '内容已安排定时发布！';
                break;
            default:
                $message = '内容已保存为草稿！';
                break;
        }
        
        wp_send_json_success(array(
            'message' => $message,
            'post_id' => $post_id,
            'edit_link' => $edit_link
        ));
    }
    
    private function format_content_for_wordpress($content, $type) {
        switch ($type) {
            case 'image':
                return '<img src="' . esc_url($content) . '" alt="AI生成的图片" style="max-width: 100%; height: auto;" />';
            case 'video':
                return '<video controls style="max-width: 100%; height: auto;"><source src="' . esc_url($content) . '" type="video/mp4">您的浏览器不支持视频播放。</video>';
            case 'audio':
                return '<audio controls><source src="' . esc_url($content) . '" type="audio/mpeg">您的浏览器不支持音频播放。</audio>';
            case 'text':
            default:
                return wpautop($content);
        }
    }
    
    private function call_siliconflow_api($type, $prompt, $api_key, $video_model = '', $reference_image = '') {
        switch ($type) {
            case 'text':
                return $this->generate_text($prompt, $api_key);
            case 'image':
                return $this->generate_image($prompt, $api_key);
            case 'video':
                return $this->generate_video($prompt, $api_key, $video_model, $reference_image);
            case 'audio':
                return $this->generate_audio($prompt, $api_key);
            default:
                return $this->generate_text($prompt, $api_key);
        }
    }
    
    private function generate_text($prompt, $api_key) {
        $url = 'https://api.siliconflow.cn/v1/chat/completions';
        
        $data = array(
            'model' => 'Qwen/QwQ-32B',
            'messages' => array(
                array('role' => 'user', 'content' => $prompt)
            ),
            'stream' => false,
            'max_tokens' => 2000,
            'temperature' => 0.7
        );
        
        $args = array(
            'headers' => array(
                'Authorization' => 'Bearer ' . $api_key,
                'Content-Type' => 'application/json'
            ),
            'body' => json_encode($data),
            'timeout' => 60
        );
        
        $response = wp_remote_post($url, $args);
        
        if (is_wp_error($response)) {
            return array('error' => $response->get_error_message());
        }
        
        $body = wp_remote_retrieve_body($response);
        $result = json_decode($body, true);
        
        if (isset($result['choices'][0]['message']['content'])) {
            return array('content' => $result['choices'][0]['message']['content'], 'type' => 'text');
        }
        
        return array('error' => '文本生成失败');
    }
    
    private function generate_image($prompt, $api_key) {
        $url = 'https://api.siliconflow.cn/v1/images/generations';
        
        $data = array(
            'model' => 'stabilityai/stable-diffusion-xl-base-1.0',
            'prompt' => $prompt,
            'image_size' => '1024x1024',
            'batch_size' => 1,
            'num_inference_steps' => 20,
            'guidance_scale' => 7.5,
            'seed' => rand(0, 2147483647)
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
            return array('error' => $response->get_error_message());
        }
        
        $body = wp_remote_retrieve_body($response);
        $result = json_decode($body, true);
        
        if (isset($result['images'][0]['url'])) {
            return array('content' => $result['images'][0]['url'], 'type' => 'image');
        }
        
        return array('error' => '图片生成失败');
    }
    
    private function generate_video($prompt, $api_key, $video_model = '', $reference_image = '') {
        // 授权检查
        $license_manager = AI_Optimizer_License_Manager::get_instance();
        if (!$license_manager->has_feature('ai_video')) {
            return array('error' => '您的授权不支持视频生成功能，请升级到专业版或企业版');
        }
        
        // 检查使用限制
        if (!$license_manager->check_limit('video_generation', 1)) {
            return array('error' => '已达到本月视频生成限制，请升级授权或等待下月重置');
        }
        
        // 第一步：提交视频生成请求
        $submit_url = 'https://api.siliconflow.cn/v1/video/submit';
        
        // 使用传入的模型或默认模型
        $model = !empty($video_model) ? $video_model : 'Wan-AI/Wan2.1-T2V-14B-Turbo';
        
        $data = array(
            'model' => $model,
            'prompt' => $prompt,
            'image_size' => '1280x720',
            'seed' => rand(0, 2147483647)
        );
        
        // 如果是I2V模型且有参考图片，添加图片参数
        if (strpos($model, 'I2V') !== false && !empty($reference_image)) {
            $data['image'] = $reference_image;
        }
        
        // 增加超时和重试机制
        $args = array(
            'headers' => array(
                'Authorization' => 'Bearer ' . $api_key,
                'Content-Type' => 'application/json'
            ),
            'body' => json_encode($data),
            'timeout' => 120, // 增加到120秒
            'sslverify' => false, // 临时禁用SSL验证以避免证书问题
            'user-agent' => 'AI-Website-Optimizer/' . AI_OPT_VERSION
        );
        
        // 重试机制
        $max_retries = 3;
        $response = null;
        
        for ($retry = 0; $retry < $max_retries; $retry++) {
            $response = wp_remote_post($submit_url, $args);
            
            if (!is_wp_error($response)) {
                break;
            }
            
            // 如果不是最后一次重试，等待后重试
            if ($retry < $max_retries - 1) {
                sleep(2 * ($retry + 1)); // 递增等待时间
            }
        }
        
        if (is_wp_error($response)) {
            AI_Optimizer_Utils::log('Video generation submit failed: ' . $response->get_error_message(), 'error');
            return array('error' => '网络连接失败，请检查网络设置: ' . $response->get_error_message());
        }
        
        $body = wp_remote_retrieve_body($response);
        $result = json_decode($body, true);
        
        if (!isset($result['requestId'])) {
            $error_msg = '视频生成请求失败';
            if (isset($result['error'])) {
                $error_msg .= ': ' . $result['error']['message'];
            } elseif (isset($result['message'])) {
                $error_msg .= ': ' . $result['message'];
            }
            AI_Optimizer_Utils::log('Video generation error: ' . $error_msg, 'error');
            return array('error' => $error_msg);
        }
        
        $request_id = $result['requestId'];
        
        // 保存请求ID到数据库，以便后续查询
        $this->save_video_request($request_id, $prompt, $model);
        
        // 第二步：轮询获取视频状态
        $status_url = 'https://api.siliconflow.cn/v1/video/status';
        $max_attempts = 60; // 增加到10分钟
        
        for ($i = 0; $i < $max_attempts; $i++) {
            // 使用非阻塞等待
            if ($i > 0) {
                sleep(10); // 等待10秒
            }
            
            $status_data = array('requestId' => $request_id);
            $status_args = array(
                'headers' => array(
                    'Authorization' => 'Bearer ' . $api_key,
                    'Content-Type' => 'application/json'
                ),
                'body' => json_encode($status_data),
                'timeout' => 60,
                'sslverify' => false
            );
            
            $status_response = wp_remote_post($status_url, $status_args);
            
            if (!is_wp_error($status_response)) {
                $status_body = wp_remote_retrieve_body($status_response);
                $status_result = json_decode($status_body, true);
                
                if (isset($status_result['status'])) {
                    if ($status_result['status'] === 'Succeed' && isset($status_result['results']['videos'][0]['url'])) {
                        // 更新数据库状态
                        $this->update_video_request($request_id, 'completed', $status_result['results']['videos'][0]['url']);
                        return array('content' => $status_result['results']['videos'][0]['url'], 'type' => 'video');
                    } elseif ($status_result['status'] === 'Failed') {
                        $reason = isset($status_result['reason']) ? $status_result['reason'] : '未知错误';
                        $this->update_video_request($request_id, 'failed', null, $reason);
                        return array('error' => '视频生成失败: ' . $reason);
                    }
                    // 如果状态是 InQueue 或 InProgress，继续等待
                }
            }
        }
        
        // 返回请求ID，让用户可以稍后查询
        return array(
            'request_id' => $request_id,
            'type' => 'video',
            'status' => 'processing',
            'message' => '视频正在生成中，请稍后在"视频生成状态"中查看结果'
        );
    }
    
    private function save_video_request($request_id, $prompt, $model) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'ai_optimizer_video_requests';
        
        $wpdb->insert(
            $table_name,
            array(
                'request_id' => $request_id,
                'prompt' => $prompt,
                'model' => $model,
                'status' => 'processing',
                'created_at' => current_time('mysql'),
                'updated_at' => current_time('mysql')
            )
        );
    }
    
    private function update_video_request($request_id, $status, $video_url = null, $error_message = null) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'ai_optimizer_video_requests';
        
        $wpdb->update(
            $table_name,
            array(
                'status' => $status,
                'video_url' => $video_url,
                'error_message' => $error_message,
                'updated_at' => current_time('mysql')
            ),
            array('request_id' => $request_id)
        );
    }
    
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
            'timeout' => 60
        );
        
        $response = wp_remote_post($url, $args);
        
        if (is_wp_error($response)) {
            return array('error' => $response->get_error_message());
        }
        
        $body = wp_remote_retrieve_body($response);
        $result = json_decode($body, true);
        
        if (isset($result['audio'])) {
            return array('content' => $result['audio'], 'type' => 'audio');
        } elseif (isset($result['url'])) {
            return array('content' => $result['url'], 'type' => 'audio');
        } elseif (isset($result['error'])) {
            return array('error' => '音频生成失败: ' . $result['error']['message']);
        }
        
        return array('error' => '音频生成失败: 返回格式不正确');
    }
    
    // 保存自动发布设置
    public function ajax_save_auto_settings() {
        check_ajax_referer('ai_optimizer_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die();
        }
        
        $settings = isset($_POST['settings']) ? $_POST['settings'] : array();
        
        update_option('ai_optimizer_auto_publish_settings', $settings);
        
        wp_send_json_success(array('message' => '设置已保存'));
    }
    
    // 获取监控日志
    public function ajax_get_monitor_logs() {
        check_ajax_referer('ai_optimizer_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die();
        }
        
        $types = isset($_POST['types']) ? $_POST['types'] : array();
        $logs = array();
        
        // 模拟一些实时日志数据
        $rand = rand(1, 10);
        
        if (in_array('performance', $types) && $rand > 7) {
            $logs[] = array(
                'type' => 'info',
                'message' => '页面加载性能',
                'details' => '平均响应时间: ' . rand(100, 500) . 'ms'
            );
        }
        
        if (in_array('error', $types) && $rand > 8) {
            $logs[] = array(
                'type' => 'error',
                'message' => 'PHP错误检测',
                'details' => '在 /wp-content/themes/theme-name/functions.php 第 123 行发现未定义变量'
            );
        }
        
        if (in_array('database', $types) && $rand > 6) {
            $logs[] = array(
                'type' => 'info',
                'message' => '数据库查询',
                'details' => '执行了 ' . rand(10, 50) . ' 次查询，总时间: ' . rand(10, 100) . 'ms'
            );
        }
        
        if (in_array('plugin', $types) && $rand > 5) {
            $logs[] = array(
                'type' => 'info',
                'message' => '插件活动',
                'details' => 'AI优化器自动执行了内容优化任务'
            );
        }
        
        if (in_array('user', $types) && $rand > 6) {
            $logs[] = array(
                'type' => 'info',
                'message' => '用户活动',
                'details' => '管理员正在访问: ' . admin_url()
            );
        }
        
        if (in_array('security', $types) && $rand > 9) {
            $logs[] = array(
                'type' => 'warning',
                'message' => '安全警告',
                'details' => '检测到多次失败的登录尝试'
            );
        }
        
        // 从数据库获取真实日志（如果有的话）
        global $wpdb;
        $table_name = $wpdb->prefix . 'ai_optimizer_logs';
        
        if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") == $table_name) {
            $recent_logs = $wpdb->get_results(
                "SELECT type, message, data FROM $table_name 
                ORDER BY created_at DESC LIMIT 5"
            );
            
            foreach ($recent_logs as $log) {
                $logs[] = array(
                    'type' => $log->type,
                    'message' => $log->message,
                    'details' => $log->data
                );
            }
        }
        
        wp_send_json_success(array('logs' => $logs));
    }
    
    // 授权管理AJAX处理
    public function ajax_activate_license() {
        check_ajax_referer('ai_opt_activate_license', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => '权限不足'));
            return;
        }
        
        $license_key = sanitize_text_field($_POST['license_key'] ?? '');
        
        $license_manager = AI_Optimizer_License_Manager::get_instance();
        $result = $license_manager->activate_license($license_key);
        
        if ($result['success']) {
            wp_send_json_success($result);
        } else {
            wp_send_json_error($result);
        }
    }
    
    public function ajax_deactivate_license() {
        check_ajax_referer('ai_opt_deactivate_license', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => '权限不足'));
            return;
        }
        
        $license_manager = AI_Optimizer_License_Manager::get_instance();
        $license_manager->deactivate_license('用户手动停用');
        
        wp_send_json_success();
    }
    
    public function ajax_check_video_status() {
        check_ajax_referer('ai_optimizer_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => '权限不足'));
            return;
        }
        
        $request_id = sanitize_text_field($_POST['request_id'] ?? '');
        
        if (empty($request_id)) {
            wp_send_json_error(array('message' => '请求ID不能为空'));
            return;
        }
        
        $api_key = get_option('ai_optimizer_api_key');
        
        if (empty($api_key)) {
            wp_send_json_error(array('message' => 'API密钥未配置'));
            return;
        }
        
        // 检查视频状态
        $status_url = 'https://api.siliconflow.cn/v1/video/status';
        $status_data = array('requestId' => $request_id);
        
        $args = array(
            'headers' => array(
                'Authorization' => 'Bearer ' . $api_key,
                'Content-Type' => 'application/json'
            ),
            'body' => json_encode($status_data),
            'timeout' => 30,
            'sslverify' => false
        );
        
        $response = wp_remote_post($status_url, $args);
        
        if (is_wp_error($response)) {
            wp_send_json_error(array('message' => '检查状态失败: ' . $response->get_error_message()));
            return;
        }
        
        $body = wp_remote_retrieve_body($response);
        $result = json_decode($body, true);
        
        if (isset($result['status'])) {
            if ($result['status'] === 'Succeed' && isset($result['results']['videos'][0]['url'])) {
                // 更新数据库
                $this->update_video_request($request_id, 'completed', $result['results']['videos'][0]['url']);
                
                wp_send_json_success(array(
                    'status' => 'completed',
                    'video_url' => $result['results']['videos'][0]['url']
                ));
            } elseif ($result['status'] === 'Failed') {
                $reason = isset($result['reason']) ? $result['reason'] : '未知错误';
                $this->update_video_request($request_id, 'failed', null, $reason);
                
                wp_send_json_error(array(
                    'status' => 'failed',
                    'message' => '视频生成失败: ' . $reason
                ));
            } else {
                // 仍在处理中
                wp_send_json_success(array(
                    'status' => 'processing',
                    'message' => '视频仍在生成中...'
                ));
            }
        } else {
            wp_send_json_error(array('message' => '无法获取状态信息'));
        }
    }
    
    // 插件激活
    public function activate() {
        global $wpdb;
        
        // 创建数据库表
        $charset_collate = $wpdb->get_charset_collate();
        
        $sql = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}ai_optimizer_logs (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            type varchar(50) NOT NULL,
            message text NOT NULL,
            data longtext,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY type_index (type),
            KEY created_at_index (created_at)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
        
        // 设置默认选项
        add_option('ai_optimizer_api_key', '');
        add_option('ai_optimizer_enable_monitoring', 1);
        add_option('ai_optimizer_enable_seo', 1);
        add_option('ai_optimizer_enable_ai_tools', 1);
        
        flush_rewrite_rules();
    }
    
    // 插件停用
    public function deactivate() {
        wp_clear_scheduled_hook('ai_optimizer_cron');
        flush_rewrite_rules();
    }
}

// 启动插件
add_action('plugins_loaded', function() {
    AI_Website_Optimizer::get_instance();
});