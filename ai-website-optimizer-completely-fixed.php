<?php
/**
 * Plugin Name: AI智能网站优化器 - 完全修复版
 * Plugin URI: https://example.com/ai-website-optimizer
 * Description: 集成Siliconflow API的WordPress智能监控与优化插件，具备实时监控、SEO优化、代码修复和多媒体生成功能，支持自动发布到WordPress
 * Version: 3.0.0
 * Author: AI Developer
 * License: GPL v2 or later
 * Text Domain: ai-website-optimizer
 */

// 防止直接访问
if (!defined('ABSPATH')) {
    exit;
}

// 定义插件常量
define('AI_OPT_VERSION', '3.0.0');
define('AI_OPT_PLUGIN_URL', plugin_dir_url(__FILE__));
define('AI_OPT_PLUGIN_PATH', plugin_dir_path(__FILE__));

/**
 * 主插件类 - 完全修复版
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
        // 确保WordPress已完全加载
        add_action('plugins_loaded', array($this, 'init'));
    }
    
    public function init() {
        // 激活/停用钩子
        register_activation_hook(__FILE__, array($this, 'activate'));
        register_deactivation_hook(__FILE__, array($this, 'deactivate'));
        
        // 初始化钩子
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_assets'));
        
        // 所有AJAX处理函数
        $this->register_ajax_handlers();
    }
    
    private function register_ajax_handlers() {
        // 基础功能AJAX
        add_action('wp_ajax_ai_opt_test_api', array($this, 'ajax_test_api'));
        add_action('wp_ajax_ai_opt_save_settings', array($this, 'ajax_save_settings'));
        
        // SEO优化相关AJAX
        add_action('wp_ajax_ai_opt_start_seo_analysis', array($this, 'ajax_start_seo_analysis'));
        add_action('wp_ajax_ai_opt_stop_seo_analysis', array($this, 'ajax_stop_seo_analysis'));
        add_action('wp_ajax_ai_opt_get_analysis_progress', array($this, 'ajax_get_analysis_progress'));
        add_action('wp_ajax_ai_opt_save_analysis_scope', array($this, 'ajax_save_analysis_scope'));
        add_action('wp_ajax_ai_opt_save_optimization_strategy', array($this, 'ajax_save_optimization_strategy'));
        add_action('wp_ajax_ai_opt_save_auto_optimization', array($this, 'ajax_save_auto_optimization'));
        add_action('wp_ajax_ai_opt_add_competitor', array($this, 'ajax_add_competitor'));
        add_action('wp_ajax_ai_opt_remove_competitor', array($this, 'ajax_remove_competitor'));
        add_action('wp_ajax_ai_opt_get_competitors', array($this, 'ajax_get_competitors'));
        
        // AI巡逻系统AJAX
        add_action('wp_ajax_ai_opt_start_patrol', array($this, 'ajax_start_patrol'));
        add_action('wp_ajax_ai_opt_stop_patrol', array($this, 'ajax_stop_patrol'));
        add_action('wp_ajax_ai_opt_get_patrol_logs', array($this, 'ajax_get_patrol_logs'));
        
        // AI内容生成AJAX
        add_action('wp_ajax_ai_opt_generate_content', array($this, 'ajax_generate_content'));
        add_action('wp_ajax_ai_opt_check_video_status', array($this, 'ajax_check_video_status'));
        add_action('wp_ajax_ai_opt_publish_to_wordpress', array($this, 'ajax_publish_to_wordpress'));
    }
    
    public function activate() {
        // 创建数据库表
        $this->create_database_tables();
        
        // 设置默认选项
        add_option('ai_opt_analysis_scope', array('content', 'meta', 'structure'));
        add_option('ai_opt_optimization_strategy', array());
        add_option('ai_opt_auto_optimization', false);
        add_option('ai_opt_competitors', array());
    }
    
    public function deactivate() {
        // 清理定时任务
        wp_clear_scheduled_hook('ai_opt_auto_patrol');
    }
    
    private function create_database_tables() {
        global $wpdb;
        
        $charset_collate = $wpdb->get_charset_collate();
        
        // SEO分析结果表
        $seo_table = $wpdb->prefix . 'ai_opt_seo_analysis';
        $seo_sql = "CREATE TABLE $seo_table (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            analysis_id varchar(50) NOT NULL,
            url varchar(255) NOT NULL,
            score int(3) DEFAULT 0,
            issues longtext,
            suggestions longtext,
            status varchar(20) DEFAULT 'pending',
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            completed_at datetime NULL,
            PRIMARY KEY  (id),
            KEY analysis_id (analysis_id)
        ) $charset_collate;";
        
        // 巡逻日志表
        $patrol_table = $wpdb->prefix . 'ai_opt_patrol_logs';
        $patrol_sql = "CREATE TABLE $patrol_table (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            patrol_id varchar(50) NOT NULL,
            check_type varchar(50) NOT NULL,
            status varchar(20) NOT NULL,
            message text,
            details longtext,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            KEY patrol_id (patrol_id),
            KEY check_type (check_type)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($seo_sql);
        dbDelta($patrol_sql);
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
    }
    
    public function enqueue_admin_assets($hook) {
        if (strpos($hook, 'ai-optimizer') === false) {
            return;
        }
        
        wp_enqueue_script('jquery');
        wp_enqueue_script('chart-js', 'https://cdn.jsdelivr.net/npm/chart.js', array(), '3.9.1', true);
        
        // 管理员样式
        wp_add_inline_style('wp-admin', $this->get_admin_styles());
        
        // 管理员脚本
        wp_add_inline_script('jquery', $this->get_admin_scripts());
    }
    
    private function get_admin_styles() {
        return '
        .ai-optimizer-wrap { max-width: 1200px; margin: 20px auto; }
        .ai-optimizer-card { background: #fff; border: 1px solid #ccd0d4; border-radius: 12px; padding: 25px; margin-bottom: 25px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); }
        .ai-optimizer-card h2 { margin: 0 0 20px 0; color: #165DFF; border-bottom: 2px solid #f0f0f0; padding-bottom: 10px; }
        .ai-optimizer-header { text-align: center; margin-bottom: 30px; padding: 30px; background: linear-gradient(135deg, #165DFF 0%, #7E22CE 100%); color: white; border-radius: 15px; }
        .ai-optimizer-header h1 { margin: 0 0 10px 0; font-size: 2.5em; }
        .ai-optimizer-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px; margin: 20px 0; }
        .ai-optimizer-select, .ai-optimizer-input { width: 100%; padding: 10px; border: 2px solid #ddd; border-radius: 8px; font-size: 14px; transition: border-color 0.3s ease; }
        .ai-optimizer-select:focus, .ai-optimizer-input:focus { border-color: #165DFF; outline: none; box-shadow: 0 0 0 3px rgba(22, 93, 255, 0.1); }
        .patrol-controls { display: flex; gap: 15px; align-items: center; margin-bottom: 20px; }
        .patrol-status { padding: 10px 15px; border-radius: 8px; font-weight: bold; }
        .patrol-status.active { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .patrol-status.inactive { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .patrol-logs { background: #f8f9fa; border-radius: 8px; border: 1px solid #e9ecef; max-height: 400px; overflow-y: auto; }
        .patrol-log-entry { padding: 10px; border-bottom: 1px solid #e9ecef; font-family: monospace; font-size: 13px; }
        .patrol-log-entry:last-child { border-bottom: none; }
        .log-timestamp { color: #666; margin-right: 10px; }
        .log-type { font-weight: bold; margin-right: 10px; }
        .log-type.info { color: #0066cc; }
        .log-type.warning { color: #ff6600; }
        .log-type.error { color: #cc0000; }
        .log-type.success { color: #009900; }
        .progress-bar-container { width: 100%; height: 20px; background: #f0f0f0; border-radius: 10px; overflow: hidden; margin: 15px 0; }
        .progress-bar { height: 100%; background: linear-gradient(90deg, #165DFF, #00F5D4); width: 0; transition: width 0.5s ease; }
        .automation-switch { display: flex; align-items: center; cursor: pointer; }
        .automation-switch input[type="checkbox"] { display: none; }
        .slider { position: relative; width: 60px; height: 30px; background: #ccc; border-radius: 30px; transition: background 0.3s; margin-right: 15px; }
        .slider:before { content: ""; position: absolute; width: 26px; height: 26px; border-radius: 50%; background: white; top: 2px; left: 2px; transition: transform 0.3s; }
        .automation-switch input:checked + .slider { background: #165DFF; }
        .automation-switch input:checked + .slider:before { transform: translateX(30px); }
        .competitor-item { display: flex; justify-content: space-between; align-items: center; padding: 15px; background: #f8f9fa; border-radius: 8px; margin-bottom: 10px; border: 1px solid #e9ecef; }
        .scope-checkbox, .strategy-checkbox { display: flex; align-items: center; margin-bottom: 10px; cursor: pointer; padding: 5px; border-radius: 5px; transition: background-color 0.2s ease; }
        .scope-checkbox:hover, .strategy-checkbox:hover { background-color: #f8f9fa; }
        .scope-checkbox input[type="checkbox"], .strategy-checkbox input[type="checkbox"] { margin-right: 10px; transform: scale(1.2); }
        .generation-status { margin: 15px 0; padding: 10px; border-radius: 8px; }
        .generation-status.generating { background: #e7f3ff; border: 1px solid #b3d9ff; color: #0066cc; }
        .generation-status.success { background: #d4edda; border: 1px solid #c3e6cb; color: #155724; }
        .generation-status.error { background: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; }
        @media (max-width: 768px) { .ai-optimizer-grid { grid-template-columns: 1fr; } .patrol-controls { flex-direction: column; } }
        ';
    }
    
    private function get_admin_scripts() {
        $nonce = wp_create_nonce('ai-opt-nonce');
        $ajaxurl = admin_url('admin-ajax.php');
        
        return "
        jQuery(document).ready(function($) {
            // 全局配置
            window.AIOptimizer = {
                nonce: '{$nonce}',
                ajaxurl: '{$ajaxurl}',
                patrolRunning: false,
                analysisRunning: false
            };
            
            // 初始化所有功能
            initSEOOptimizer();
            initPatrolSystem();
            initContentGenerator();
            
            // SEO优化器初始化
            function initSEOOptimizer() {
                // AI模型选择
                $('#ai_model_preset').on('change', function() {
                    if ($(this).val() === 'custom') {
                        $('#custom_model_section').show();
                    } else {
                        $('#custom_model_section').hide();
                    }
                });
                
                // API提供商选择
                $('#api_provider').on('change', function() {
                    if ($(this).val() === 'custom') {
                        $('#custom_api_section').show();
                    } else {
                        $('#custom_api_section').hide();
                    }
                });
                
                // 分析范围变化事件
                $('input[name=\"analysis_scope[]\"]').on('change', function() {
                    saveAnalysisScope();
                    toggleCompetitorsSection();
                });
                
                // 优化策略变化事件
                $('input[name=\"optimization_strategy[]\"]').on('change', function() {
                    saveOptimizationStrategy();
                });
                
                // 全选/清空分析范围
                $('#select_all_scope').on('click', function() {
                    $('input[name=\"analysis_scope[]\"]').prop('checked', true);
                    saveAnalysisScope();
                    toggleCompetitorsSection();
                });
                
                $('#clear_all_scope').on('click', function() {
                    $('input[name=\"analysis_scope[]\"]').prop('checked', false);
                    saveAnalysisScope();
                    toggleCompetitorsSection();
                });
                
                // 竞争对手管理
                $('#add_competitor').on('click', addCompetitor);
                $(document).on('click', '.remove-competitor', removeCompetitor);
                
                // 开始SEO分析
                $('#start_seo_analysis').on('click', startSEOAnalysis);
                $('#stop_analysis').on('click', stopSEOAnalysis);
                
                // 自动化开关
                $('#auto_optimization_enabled').on('change', function() {
                    const enabled = $(this).is(':checked');
                    saveAutoOptimizationSetting(enabled);
                });
                
                // 加载保存的设置
                loadSavedSEOSettings();
            }
            
            // 巡逻系统初始化
            function initPatrolSystem() {
                // 开始巡逻
                $('#start_patrol').on('click', startPatrol);
                $('#stop_patrol').on('click', stopPatrol);
                $('#clear_patrol_logs').on('click', clearPatrolLogs);
                
                // 定期更新巡逻日志
                setInterval(updatePatrolLogs, 5000);
            }
            
            // 内容生成器初始化
            function initContentGenerator() {
                // 内容类型切换
                $('#content_type').on('change', function() {
                    const type = $(this).val();
                    if (type === 'video') {
                        $('#video_model_row').show();
                        $('#video_model').trigger('change');
                    } else {
                        $('#video_model_row, #image_input_row').hide();
                    }
                });
                
                // 视频模型切换
                $('#video_model').on('change', function() {
                    const model = $(this).val();
                    if (model && model.includes('I2V')) {
                        $('#image_input_row').show();
                    } else {
                        $('#image_input_row').hide();
                    }
                });
                
                // 文件上传处理
                $('#reference_image_file').on('change', function() {
                    const file = this.files[0];
                    if (file) {
                        const reader = new FileReader();
                        reader.onload = function(e) {
                            $('#reference_image_url').val(e.target.result);
                            $('#image_upload_status').text('图片已加载').css('color', 'green');
                        };
                        reader.onerror = function() {
                            $('#image_upload_status').text('图片加载失败').css('color', 'red');
                        };
                        reader.readAsDataURL(file);
                    }
                });
                
                // 生成内容
                $('#generate-content-btn').on('click', generateContent);
                $('#publish-content-btn').on('click', publishContent);
            }
            
            // SEO分析相关函数
            function loadSavedSEOSettings() {
                // 从localStorage加载设置
                const savedScope = localStorage.getItem('ai_opt_analysis_scope');
                if (savedScope) {
                    const scopeArray = JSON.parse(savedScope);
                    $('input[name=\"analysis_scope[]\"]').each(function() {
                        $(this).prop('checked', scopeArray.includes($(this).val()));
                    });
                }
                
                const savedStrategy = localStorage.getItem('ai_opt_optimization_strategy');
                if (savedStrategy) {
                    const strategyArray = JSON.parse(savedStrategy);
                    $('input[name=\"optimization_strategy[]\"]').each(function() {
                        $(this).prop('checked', strategyArray.includes($(this).val()));
                    });
                }
                
                toggleCompetitorsSection();
            }
            
            function saveAnalysisScope() {
                const scope = [];
                $('input[name=\"analysis_scope[]\"]:checked').each(function() {
                    scope.push($(this).val());
                });
                
                localStorage.setItem('ai_opt_analysis_scope', JSON.stringify(scope));
                
                $.post(window.AIOptimizer.ajaxurl, {
                    action: 'ai_opt_save_analysis_scope',
                    nonce: window.AIOptimizer.nonce,
                    scope: scope
                });
            }
            
            function saveOptimizationStrategy() {
                const strategy = [];
                $('input[name=\"optimization_strategy[]\"]:checked').each(function() {
                    strategy.push($(this).val());
                });
                
                localStorage.setItem('ai_opt_optimization_strategy', JSON.stringify(strategy));
                
                $.post(window.AIOptimizer.ajaxurl, {
                    action: 'ai_opt_save_optimization_strategy',
                    nonce: window.AIOptimizer.nonce,
                    strategy: strategy
                });
            }
            
            function saveAutoOptimizationSetting(enabled) {
                $.post(window.AIOptimizer.ajaxurl, {
                    action: 'ai_opt_save_auto_optimization',
                    nonce: window.AIOptimizer.nonce,
                    enabled: enabled ? 1 : 0
                }, function(response) {
                    if (response.success) {
                        showNotification('自动化设置已保存', 'success');
                    }
                });
            }
            
            function toggleCompetitorsSection() {
                const competitorsChecked = $('input[name=\"analysis_scope[]\"][value=\"competitors\"]').is(':checked');
                if (competitorsChecked) {
                    $('#competitors_section').show();
                } else {
                    $('#competitors_section').hide();
                }
            }
            
            function addCompetitor() {
                const url = $('#new_competitor_url').val().trim();
                const name = $('#new_competitor_name').val().trim();
                
                if (!url) {
                    showNotification('请输入竞争对手网站URL', 'error');
                    return;
                }
                
                if (!isValidURL(url)) {
                    showNotification('请输入有效的网站URL', 'error');
                    return;
                }
                
                $.post(window.AIOptimizer.ajaxurl, {
                    action: 'ai_opt_add_competitor',
                    nonce: window.AIOptimizer.nonce,
                    url: url,
                    name: name
                }, function(response) {
                    if (response.success) {
                        $('#new_competitor_url').val('');
                        $('#new_competitor_name').val('');
                        refreshCompetitorsList();
                        showNotification('竞争对手已添加', 'success');
                    } else {
                        showNotification('添加失败: ' + response.data.message, 'error');
                    }
                });
            }
            
            function removeCompetitor() {
                const index = $(this).closest('.competitor-item').data('index');
                
                $.post(window.AIOptimizer.ajaxurl, {
                    action: 'ai_opt_remove_competitor',
                    nonce: window.AIOptimizer.nonce,
                    index: index
                }, function(response) {
                    if (response.success) {
                        refreshCompetitorsList();
                        showNotification('竞争对手已删除', 'success');
                    }
                });
            }
            
            function refreshCompetitorsList() {
                $.post(window.AIOptimizer.ajaxurl, {
                    action: 'ai_opt_get_competitors',
                    nonce: window.AIOptimizer.nonce
                }, function(response) {
                    if (response.success) {
                        updateCompetitorsList(response.data);
                    }
                });
            }
            
            function updateCompetitorsList(competitors) {
                const container = $('#competitors_list');
                
                if (competitors.length === 0) {
                    container.html('<div class=\"no-competitors\">暂无竞争对手配置</div>');
                    return;
                }
                
                let html = '';
                competitors.forEach((competitor, index) => {
                    html += '<div class=\"competitor-item\" data-index=\"' + index + '\">';
                    html += '<div class=\"competitor-info\">';
                    html += '<strong>' + (competitor.name || new URL(competitor.url).hostname) + '</strong>';
                    html += '<span class=\"competitor-url\">' + competitor.url + '</span>';
                    html += '</div>';
                    html += '<button type=\"button\" class=\"remove-competitor button button-small\">删除</button>';
                    html += '</div>';
                });
                
                container.html(html);
            }
            
            function startSEOAnalysis() {
                if (window.AIOptimizer.analysisRunning) return;
                
                const config = getSEOAnalysisConfig();
                if (!validateSEOConfig(config)) return;
                
                window.AIOptimizer.analysisRunning = true;
                
                $('#start_seo_analysis').hide();
                $('#stop_analysis').show();
                $('#analysis_progress').show();
                $('#analysis_results').hide();
                
                updateSEOProgress(0, '准备开始分析...');
                clearSEOLogs();
                
                $.post(window.AIOptimizer.ajaxurl, {
                    action: 'ai_opt_start_seo_analysis',
                    nonce: window.AIOptimizer.nonce,
                    config: config
                }, function(response) {
                    if (response.success) {
                        const analysisId = response.data.analysis_id;
                        startSEOProgressMonitoring(analysisId);
                        addSEOLog('info', '分析已启动', '分析ID: ' + analysisId);
                    } else {
                        stopSEOAnalysis();
                        showNotification('分析启动失败: ' + response.data.message, 'error');
                        addSEOLog('error', '分析启动失败', response.data.message);
                    }
                }).fail(function(xhr, status, error) {
                    stopSEOAnalysis();
                    showNotification('网络错误: ' + error, 'error');
                    addSEOLog('error', '网络错误', error);
                });
            }
            
            function getSEOAnalysisConfig() {
                const config = {};
                
                const modelPreset = $('#ai_model_preset').val();
                if (modelPreset === 'custom') {
                    config.ai_model = $('#ai_model_custom').val();
                } else {
                    config.ai_model = modelPreset;
                }
                
                const apiProvider = $('#api_provider').val();
                if (apiProvider === 'custom') {
                    config.api_endpoint = $('#api_endpoint_custom').val();
                    config.api_key = $('#api_key_custom').val();
                } else {
                    config.api_provider = apiProvider;
                }
                
                config.analysis_scope = [];
                $('input[name=\"analysis_scope[]\"]:checked').each(function() {
                    config.analysis_scope.push($(this).val());
                });
                
                config.optimization_strategy = [];
                $('input[name=\"optimization_strategy[]\"]:checked').each(function() {
                    config.optimization_strategy.push($(this).val());
                });
                
                config.auto_optimization = $('#auto_optimization_enabled').is(':checked');
                
                return config;
            }
            
            function validateSEOConfig(config) {
                if (!config.ai_model) {
                    showNotification('请选择AI模型', 'error');
                    return false;
                }
                
                if (config.analysis_scope.length === 0) {
                    showNotification('请至少选择一个分析范围', 'error');
                    return false;
                }
                
                if (config.api_provider === 'custom') {
                    if (!config.api_endpoint || !config.api_key) {
                        showNotification('请填写自定义API配置', 'error');
                        return false;
                    }
                }
                
                return true;
            }
            
            function startSEOProgressMonitoring(analysisId) {
                window.AIOptimizer.seoInterval = setInterval(function() {
                    $.post(window.AIOptimizer.ajaxurl, {
                        action: 'ai_opt_get_analysis_progress',
                        nonce: window.AIOptimizer.nonce,
                        analysis_id: analysisId
                    }, function(response) {
                        if (response.success) {
                            const data = response.data;
                            updateSEOProgress(data.progress, data.status);
                            
                            if (data.logs && data.logs.length > 0) {
                                data.logs.forEach(log => {
                                    addSEOLog(log.type, log.title, log.message);
                                });
                            }
                            
                            if (data.completed) {
                                clearInterval(window.AIOptimizer.seoInterval);
                                onSEOAnalysisCompleted(data.results);
                            }
                        }
                    });
                }, 2000);
            }
            
            function stopSEOAnalysis() {
                window.AIOptimizer.analysisRunning = false;
                
                if (window.AIOptimizer.seoInterval) {
                    clearInterval(window.AIOptimizer.seoInterval);
                }
                
                $('#start_seo_analysis').show();
                $('#stop_analysis').hide();
                
                $.post(window.AIOptimizer.ajaxurl, {
                    action: 'ai_opt_stop_seo_analysis',
                    nonce: window.AIOptimizer.nonce
                });
                
                addSEOLog('warning', '分析已停止', '用户手动停止了分析过程');
            }
            
            function updateSEOProgress(percentage, status) {
                $('#progress_bar').css('width', percentage + '%');
                $('#progress_percentage').text(percentage + '%');
                $('#progress_text').text(status);
            }
            
            function addSEOLog(type, title, message) {
                const timestamp = new Date().toLocaleTimeString();
                const logClass = 'log-' + type;
                
                const logHtml = '<div class=\"log-entry ' + logClass + '\">' +
                    '<span class=\"log-time\">[' + timestamp + ']</span>' +
                    '<span class=\"log-title\">' + title + ':</span>' +
                    '<span class=\"log-message\">' + message + '</span>' +
                    '</div>';
                
                const container = $('#logs_container');
                
                if (container.find('.log-placeholder').length > 0) {
                    container.empty();
                }
                
                container.append(logHtml);
                container.scrollTop(container[0].scrollHeight);
            }
            
            function clearSEOLogs() {
                $('#logs_container').html('<div class=\"log-placeholder\">分析日志将在此显示...</div>');
            }
            
            function onSEOAnalysisCompleted(results) {
                window.AIOptimizer.analysisRunning = false;
                $('#start_seo_analysis').show();
                $('#stop_analysis').hide();
                
                displaySEOResults(results);
                $('#analysis_results').show();
                
                addSEOLog('success', '分析完成', '所有分析任务已完成，请查看结果');
                showNotification('SEO分析已完成！', 'success');
            }
            
            function displaySEOResults(results) {
                // 显示分析结果
                $('#results_summary').html(generateSEOSummary(results));
                $('#overview_content').html(generateSEOOverview(results.overview));
                $('#suggestions_content').html(generateSEOSuggestions(results.suggestions));
                
                if (results.competitors) {
                    $('#competitors_content').html(generateCompetitorsAnalysis(results.competitors));
                }
                
                $('#technical_content').html(generateTechnicalAnalysis(results.technical));
            }
            
            function generateSEOSummary(results) {
                return '<div class=\"results-summary-content\">' +
                    '<div class=\"summary-scores\">' +
                    '<div class=\"summary-score\">' +
                    '<div class=\"score-label\">总体评分</div>' +
                    '<div class=\"score-value\">' + results.total_score + '/100</div>' +
                    '</div>' +
                    '<div class=\"summary-score\">' +
                    '<div class=\"score-label\">发现问题</div>' +
                    '<div class=\"score-value\">' + results.issues_found + '</div>' +
                    '</div>' +
                    '<div class=\"summary-score\">' +
                    '<div class=\"score-label\">优化建议</div>' +
                    '<div class=\"score-value\">' + results.suggestions_count + '</div>' +
                    '</div>' +
                    '</div>' +
                    '</div>';
            }
            
            // 巡逻系统相关函数
            function startPatrol() {
                if (window.AIOptimizer.patrolRunning) return;
                
                const button = $('#start_patrol');
                const originalText = button.html();
                
                button.prop('disabled', true).html('<span class=\"dashicons dashicons-update spin\"></span> 启动中...');
                
                $.post(window.AIOptimizer.ajaxurl, {
                    action: 'ai_opt_start_patrol',
                    nonce: window.AIOptimizer.nonce
                }, function(response) {
                    if (response.success) {
                        window.AIOptimizer.patrolRunning = true;
                        updatePatrolStatus('active', '巡逻系统运行中');
                        $('#start_patrol').hide();
                        $('#stop_patrol').show();
                        addPatrolLog('success', '巡逻启动', 'AI巡逻系统已成功启动');
                    } else {
                        showNotification('启动巡逻失败: ' + response.data.message, 'error');
                        addPatrolLog('error', '巡逻启动失败', response.data.message);
                    }
                }).fail(function(xhr, status, error) {
                    showNotification('启动巡逻时发生网络错误: ' + error, 'error');
                    addPatrolLog('error', '网络错误', '启动巡逻时发生网络错误: ' + error);
                }).always(function() {
                    button.prop('disabled', false).html(originalText);
                });
            }
            
            function stopPatrol() {
                if (!window.AIOptimizer.patrolRunning) return;
                
                const button = $('#stop_patrol');
                const originalText = button.html();
                
                button.prop('disabled', true).html('<span class=\"dashicons dashicons-update spin\"></span> 停止中...');
                
                $.post(window.AIOptimizer.ajaxurl, {
                    action: 'ai_opt_stop_patrol',
                    nonce: window.AIOptimizer.nonce
                }, function(response) {
                    if (response.success) {
                        window.AIOptimizer.patrolRunning = false;
                        updatePatrolStatus('inactive', '巡逻系统已停止');
                        $('#stop_patrol').hide();
                        $('#start_patrol').show();
                        addPatrolLog('warning', '巡逻停止', '用户手动停止了巡逻系统');
                    }
                }).always(function() {
                    button.prop('disabled', false).html(originalText);
                });
            }
            
            function updatePatrolStatus(status, message) {
                const statusElement = $('#patrol_status');
                statusElement.removeClass('active inactive').addClass(status);
                statusElement.find('.status-text').text(message);
            }
            
            function addPatrolLog(type, title, message) {
                const timestamp = new Date().toLocaleTimeString();
                const logHtml = '<div class=\"patrol-log-entry\">' +
                    '<span class=\"log-timestamp\">' + timestamp + '</span>' +
                    '<span class=\"log-type ' + type + '\">[' + title.toUpperCase() + ']</span>' +
                    '<span class=\"log-message\">' + message + '</span>' +
                    '</div>';
                
                const container = $('#patrol_logs_container');
                container.append(logHtml);
                container.scrollTop(container[0].scrollHeight);
            }
            
            function updatePatrolLogs() {
                if (!window.AIOptimizer.patrolRunning) return;
                
                $.post(window.AIOptimizer.ajaxurl, {
                    action: 'ai_opt_get_patrol_logs',
                    nonce: window.AIOptimizer.nonce,
                    since: Date.now() - 5000  // 获取最近5秒的日志
                }, function(response) {
                    if (response.success && response.data.logs) {
                        response.data.logs.forEach(log => {
                            addPatrolLog(log.type, log.title, log.message);
                        });
                    }
                });
            }
            
            function clearPatrolLogs() {
                $('#patrol_logs_container').empty();
                showNotification('巡逻日志已清空', 'success');
            }
            
            // 内容生成相关函数
            function generateContent() {
                const button = $('#generate-content-btn');
                const contentType = $('#content_type').val();
                const prompt = $('#prompt').val().trim();
                
                if (!prompt) {
                    showNotification('请输入提示词', 'error');
                    return;
                }
                
                button.prop('disabled', true).text('生成中...');
                $('#generation-result').hide();
                
                showGenerationStatus('正在生成' + getContentTypeName(contentType) + '，请稍候...');
                
                const postData = {
                    action: 'ai_opt_generate_content',
                    nonce: window.AIOptimizer.nonce,
                    content_type: contentType,
                    prompt: prompt
                };
                
                if (contentType === 'video') {
                    postData.video_model = $('#video_model').val();
                    postData.reference_image = $('#reference_image_url').val();
                }
                
                $.post(window.AIOptimizer.ajaxurl, postData, function(response) {
                    if (response.success) {
                        displayGenerationResult(response.data.content, response.data.type);
                        showGenerationStatus('生成成功！', 'success');
                        window.AIOptimizer.currentContent = response.data.content;
                        window.AIOptimizer.currentContentType = response.data.type;
                        $('#generation-result').show();
                    } else {
                        showGenerationStatus('生成失败: ' + response.data.message, 'error');
                    }
                }).fail(function(xhr, status, error) {
                    showGenerationStatus('网络错误: ' + error, 'error');
                }).always(function() {
                    button.prop('disabled', false).text('生成内容');
                });
            }
            
            function getContentTypeName(type) {
                const names = {
                    'text': '文本',
                    'image': '图片',
                    'video': '视频',
                    'audio': '音频'
                };
                return names[type] || type;
            }
            
            function showGenerationStatus(message, type = 'info') {
                const statusElement = $('#generation-status');
                statusElement.removeClass('generating success error').addClass(type);
                statusElement.text(message);
            }
            
            function displayGenerationResult(content, type) {
                let html = '';
                
                switch (type) {
                    case 'image':
                        html = '<img src=\"' + content + '\" alt=\"AI生成的图片\" style=\"max-width: 100%; height: auto; border-radius: 8px;\" />';
                        break;
                    case 'video':
                        html = '<video controls style=\"max-width: 100%; height: auto; border-radius: 8px;\"><source src=\"' + content + '\" type=\"video/mp4\">您的浏览器不支持视频播放。</video>';
                        break;
                    case 'audio':
                        html = '<audio controls style=\"width: 100%;\"><source src=\"' + content + '\" type=\"audio/mpeg\">您的浏览器不支持音频播放。</audio>';
                        break;
                    case 'text':
                    default:
                        html = '<div style=\"background: white; padding: 20px; border-radius: 8px; border: 1px solid #ddd; white-space: pre-wrap; line-height: 1.6;\">' + content + '</div>';
                        break;
                }
                
                $('#result-content').html(html);
            }
            
            function publishContent() {
                const title = $('#post_title').val().trim();
                const publishType = $('#publish_type').val();
                const scheduleTime = $('#schedule_time').val();
                
                if (!title) {
                    showNotification('请输入文章标题', 'error');
                    return;
                }
                
                if (!window.AIOptimizer.currentContent) {
                    showNotification('没有可发布的内容', 'error');
                    return;
                }
                
                const button = $('#publish-content-btn');
                button.prop('disabled', true).text('发布中...');
                
                $.post(window.AIOptimizer.ajaxurl, {
                    action: 'ai_opt_publish_to_wordpress',
                    nonce: window.AIOptimizer.nonce,
                    title: title,
                    content: window.AIOptimizer.currentContent,
                    content_type: window.AIOptimizer.currentContentType,
                    publish_type: publishType,
                    schedule_time: scheduleTime,
                    category_id: $('#post-category').val()
                }, function(response) {
                    if (response.success) {
                        showNotification(response.data.message + ' <a href=\"' + response.data.edit_link + '\" target=\"_blank\">查看文章</a>', 'success');
                        $('#post_title').val('');
                    } else {
                        showNotification('发布失败: ' + response.data.message, 'error');
                    }
                }).fail(function(xhr, status, error) {
                    showNotification('发布时发生网络错误: ' + error, 'error');
                }).always(function() {
                    button.prop('disabled', false).text('发布到WordPress');
                });
            }
            
            // 通用工具函数
            function showNotification(message, type = 'info') {
                const notification = $('<div class=\"notice notice-' + type + ' is-dismissible\"><p>' + message + '</p><button type=\"button\" class=\"notice-dismiss\"><span class=\"screen-reader-text\">关闭通知</span></button></div>');
                
                $('.ai-optimizer-wrap').prepend(notification);
                
                setTimeout(function() {
                    notification.fadeOut();
                }, 5000);
            }
            
            function isValidURL(string) {
                try {
                    new URL(string);
                    return true;
                } catch (_) {
                    return false;
                }
            }
            
            // API测试
            $('#test-api-btn').on('click', function() {
                const button = $(this);
                button.prop('disabled', true).text('测试中...');
                
                $.post(window.AIOptimizer.ajaxurl, {
                    action: 'ai_opt_test_api',
                    nonce: window.AIOptimizer.nonce
                }, function(response) {
                    if (response.success) {
                        $('#test-result').html('<div class=\"notice notice-success\"><p>' + response.data.message + '</p></div>');
                    } else {
                        $('#test-result').html('<div class=\"notice notice-error\"><p>' + response.data.message + '</p></div>');
                    }
                }).always(function() {
                    button.prop('disabled', false).text('测试API连接');
                });
            });
            
            // 保存设置
            $('#save-settings-form').on('submit', function(e) {
                e.preventDefault();
                
                const form = $(this);
                const button = form.find('input[type=submit]');
                button.prop('disabled', true).val('保存中...');
                
                $.post(window.AIOptimizer.ajaxurl, {
                    action: 'ai_opt_save_settings',
                    nonce: window.AIOptimizer.nonce,
                    api_key: $('#api_key').val(),
                    enable_monitoring: $('#enable_monitoring').is(':checked') ? 1 : 0,
                    enable_seo: $('#enable_seo').is(':checked') ? 1 : 0,
                    enable_ai_tools: $('#enable_ai_tools').is(':checked') ? 1 : 0
                }, function(response) {
                    if (response.success) {
                        showNotification('设置已保存', 'success');
                    } else {
                        showNotification('保存失败: ' + response.data.message, 'error');
                    }
                }).always(function() {
                    button.prop('disabled', false).val('保存设置');
                });
            });
        });
        ";
    }
    
    public function render_dashboard() {
        ?>
        <div class="ai-optimizer-wrap">
            <div class="ai-optimizer-header">
                <h1>🎯 AI智能网站优化器</h1>
                <p>基于先进AI技术的网站优化与内容生成平台</p>
            </div>
            
            <div class="ai-optimizer-grid">
                <div class="ai-optimizer-card">
                    <h2>📊 系统概览</h2>
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
                </div>
                
                <div class="ai-optimizer-card">
                    <h2>🚀 快速开始</h2>
                    <p>欢迎使用AI智能网站优化器！本插件集成了先进的AI技术，帮助您优化网站性能、提升SEO排名、生成高质量内容。</p>
                    <p><a href="<?php echo admin_url('admin.php?page=ai-optimizer-settings'); ?>" class="button button-primary">配置API密钥</a></p>
                </div>
            </div>
        </div>
        <?php
    }
    
    public function render_monitor() {
        include AI_OPT_PLUGIN_PATH . 'admin/views/monitor-new.php';
    }
    
    public function render_seo() {
        include AI_OPT_PLUGIN_PATH . 'admin/views/seo-optimization-fixed.php';
    }
    
    public function render_tools() {
        ?>
        <div class="ai-optimizer-wrap">
            <div class="ai-optimizer-header">
                <h1>🎨 AI内容生成工具</h1>
                <p>强大的AI内容创作平台，支持文本、图片、视频、音频生成</p>
            </div>
            
            <div class="ai-optimizer-card">
                <h2>📝 内容生成</h2>
                
                <form id="content-generation-form">
                    <table class="form-table">
                        <tr>
                            <th scope="row"><label for="content_type">生成类型</label></th>
                            <td>
                                <select id="content_type" name="content_type" class="ai-optimizer-select">
                                    <option value="text">文本生成</option>
                                    <option value="image">图片生成</option>
                                    <option value="video">视频生成</option>
                                    <option value="audio">音频生成</option>
                                </select>
                                <p class="description">选择要生成的内容类型</p>
                            </td>
                        </tr>
                        <tr id="video_model_row" style="display: none;">
                            <th scope="row"><label for="video_model">视频模型</label></th>
                            <td>
                                <select id="video_model" name="video_model" class="ai-optimizer-select">
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
                                <input type="text" id="reference_image_url" name="reference_image_url" class="ai-optimizer-input" placeholder="输入图片URL地址">
                                <p class="description">或者使用base64格式：data:image/png;base64,...</p>
                                <div style="margin-top: 10px;">
                                    <input type="file" id="reference_image_file" accept="image/*">
                                    <span id="image_upload_status" style="margin-left: 10px;"></span>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="prompt">提示词</label></th>
                            <td>
                                <textarea name="prompt" id="prompt" rows="5" class="ai-optimizer-input" placeholder="请输入您想要生成的内容描述..."></textarea>
                                <p class="description">详细描述您需要的内容，AI将根据您的描述生成相应内容。</p>
                            </td>
                        </tr>
                    </table>
                    
                    <p class="submit">
                        <button type="button" class="button button-primary" id="generate-content-btn">生成内容</button>
                        <span id="generation-status" class="generation-status"></span>
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
                                    <input type="text" id="post_title" name="post_title" class="ai-optimizer-input" placeholder="为生成的内容设置标题...">
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><label for="publish_type">发布类型</label></th>
                                <td>
                                    <select id="publish_type" name="publish_type" class="ai-optimizer-select">
                                        <option value="draft">保存草稿</option>
                                        <option value="auto">立即发布</option>
                                        <option value="scheduled">定时发布</option>
                                    </select>
                                </td>
                            </tr>
                            <tr id="schedule_row" style="display: none;">
                                <th scope="row"><label for="schedule_time">发布时间</label></th>
                                <td>
                                    <input type="datetime-local" id="schedule_time" name="schedule_time" class="ai-optimizer-input">
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><label for="post-category">文章分类</label></th>
                                <td>
                                    <select id="post-category" class="ai-optimizer-select">
                                        <?php
                                        $categories = get_categories(array('hide_empty' => false));
                                        foreach ($categories as $category) {
                                            echo '<option value="' . $category->term_id . '">' . esc_html($category->name) . '</option>';
                                        }
                                        ?>
                                    </select>
                                </td>
                            </tr>
                        </table>
                        <p class="submit">
                            <button type="button" id="publish-content-btn" class="button button-secondary">发布到WordPress</button>
                        </p>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }
    
    public function render_settings() {
        ?>
        <div class="ai-optimizer-wrap">
            <div class="ai-optimizer-header">
                <h1>⚙️ 插件设置</h1>
                <p>配置API密钥和功能开关</p>
            </div>
            
            <div class="ai-optimizer-card">
                <h2>基础配置</h2>
                <form method="post" id="save-settings-form">
                    <table class="form-table">
                        <tr>
                            <th scope="row"><label for="api_key">Siliconflow API密钥</label></th>
                            <td>
                                <input type="password" id="api_key" name="api_key" value="<?php echo esc_attr(get_option('ai_optimizer_api_key', '')); ?>" class="ai-optimizer-input" />
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
                
                <div id="test-result"></div>
            </div>
        </div>
        <?php
    }
    
    // ================================
    // AJAX处理函数 - 完全修复版
    // ================================
    
    public function ajax_test_api() {
        check_ajax_referer('ai-opt-nonce', 'nonce');
        
        $api_key = get_option('ai_optimizer_api_key');
        if (empty($api_key)) {
            wp_send_json_error(array('message' => '请先配置API密钥'));
            return;
        }
        
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
    
    // SEO分析相关AJAX处理
    public function ajax_start_seo_analysis() {
        check_ajax_referer('ai-opt-nonce', 'nonce');
        
        $config = json_decode(stripslashes($_POST['config']), true);
        if (!$config) {
            wp_send_json_error(array('message' => '配置参数无效'));
            return;
        }
        
        // 生成分析ID
        $analysis_id = uniqid('seo_analysis_');
        
        // 保存分析配置
        update_option('ai_opt_current_analysis', array(
            'id' => $analysis_id,
            'config' => $config,
            'status' => 'started',
            'progress' => 0,
            'started_at' => current_time('mysql')
        ));
        
        // 开始分析过程
        $this->process_seo_analysis($analysis_id, $config);
        
        wp_send_json_success(array(
            'message' => 'SEO分析已启动',
            'analysis_id' => $analysis_id
        ));
    }
    
    public function ajax_stop_seo_analysis() {
        check_ajax_referer('ai-opt-nonce', 'nonce');
        
        delete_option('ai_opt_current_analysis');
        wp_send_json_success(array('message' => '分析已停止'));
    }
    
    public function ajax_get_analysis_progress() {
        check_ajax_referer('ai-opt-nonce', 'nonce');
        
        $analysis_id = sanitize_text_field($_POST['analysis_id'] ?? '');
        $current_analysis = get_option('ai_opt_current_analysis');
        
        if (!$current_analysis || $current_analysis['id'] !== $analysis_id) {
            wp_send_json_error(array('message' => '分析不存在'));
            return;
        }
        
        wp_send_json_success($current_analysis);
    }
    
    private function process_seo_analysis($analysis_id, $config) {
        // 模拟分析过程，实际应该调用真实的SEO分析
        $steps = array(
            array('progress' => 10, 'status' => '初始化分析环境...', 'type' => 'info', 'title' => '初始化', 'message' => '正在准备分析环境'),
            array('progress' => 20, 'status' => '分析网站结构...', 'type' => 'info', 'title' => '结构分析', 'message' => '正在分析网站结构和导航'),
            array('progress' => 40, 'status' => '检查元标签...', 'type' => 'info', 'title' => '元标签', 'message' => '正在检查页面元标签'),
            array('progress' => 60, 'status' => '分析内容质量...', 'type' => 'info', 'title' => '内容分析', 'message' => '正在分析内容质量和关键词密度'),
            array('progress' => 80, 'status' => '生成优化建议...', 'type' => 'info', 'title' => 'AI处理', 'message' => '正在生成智能优化建议'),
            array('progress' => 100, 'status' => '分析完成', 'type' => 'success', 'title' => '完成', 'message' => '所有分析任务已完成')
        );
        
        // 这里应该是异步处理，为了演示直接更新进度
        $current_analysis = get_option('ai_opt_current_analysis');
        
        foreach ($steps as $step) {
            $current_analysis['progress'] = $step['progress'];
            $current_analysis['status'] = $step['status'];
            $current_analysis['logs'][] = array(
                'type' => $step['type'],
                'title' => $step['title'],
                'message' => $step['message'],
                'timestamp' => current_time('mysql')
            );
            
            if ($step['progress'] === 100) {
                $current_analysis['completed'] = true;
                $current_analysis['results'] = $this->generate_seo_results($config);
            }
            
            update_option('ai_opt_current_analysis', $current_analysis);
            
            // 实际应该在后台处理，这里只是演示
            if ($step['progress'] < 100) {
                sleep(1);
            }
        }
    }
    
    private function generate_seo_results($config) {
        // 生成模拟的SEO分析结果
        return array(
            'total_score' => 85,
            'issues_found' => 12,
            'suggestions_count' => 8,
            'overview' => array(
                'total_score' => 85,
                'metrics' => array(
                    array('label' => '页面速度', 'value' => '2.3秒'),
                    array('label' => '移动友好', 'value' => '优秀'),
                    array('label' => '内容质量', 'value' => '良好'),
                    array('label' => '技术SEO', 'value' => '需改进')
                )
            ),
            'suggestions' => array(
                array(
                    'id' => 1,
                    'title' => '优化页面标题',
                    'description' => '建议将页面标题长度控制在60字符以内，包含主要关键词',
                    'priority' => 'high'
                ),
                array(
                    'id' => 2,
                    'title' => '添加元描述',
                    'description' => '为页面添加吸引人的元描述，提高点击率',
                    'priority' => 'medium'
                )
            ),
            'competitors' => array(
                array(
                    'name' => '竞争对手A',
                    'seo_score' => 92,
                    'page_speed' => '1.8秒',
                    'mobile_score' => 95,
                    'insights' => array(
                        '使用了更优化的图片格式',
                        '页面结构更清晰',
                        '内部链接布局更合理'
                    )
                )
            ),
            'technical' => array(
                'meta' => array(
                    array(
                        'title' => 'Meta标签检查',
                        'status' => 'warning',
                        'description' => '部分页面缺少meta描述',
                        'recommendation' => '为所有页面添加独特的meta描述'
                    )
                ),
                'structure' => array(
                    array(
                        'title' => 'HTML结构',
                        'status' => 'good',
                        'description' => '页面结构符合HTML5标准',
                        'recommendation' => null
                    )
                )
            ),
            'highlights' => array(
                '网站整体SEO表现良好',
                '移动端适配需要改进',
                '页面加载速度有优化空间',
                '内容质量较高，关键词布局合理'
            )
        );
    }
    
    public function ajax_save_analysis_scope() {
        check_ajax_referer('ai-opt-nonce', 'nonce');
        
        $scope = array_map('sanitize_text_field', $_POST['scope'] ?? array());
        update_option('ai_opt_analysis_scope', $scope);
        
        wp_send_json_success(array('message' => '分析范围已保存'));
    }
    
    public function ajax_save_optimization_strategy() {
        check_ajax_referer('ai-opt-nonce', 'nonce');
        
        $strategy = array_map('sanitize_text_field', $_POST['strategy'] ?? array());
        update_option('ai_opt_optimization_strategy', $strategy);
        
        wp_send_json_success(array('message' => '优化策略已保存'));
    }
    
    public function ajax_save_auto_optimization() {
        check_ajax_referer('ai-opt-nonce', 'nonce');
        
        $enabled = intval($_POST['enabled'] ?? 0);
        update_option('ai_opt_auto_optimization', $enabled);
        
        wp_send_json_success(array('message' => '自动化设置已保存'));
    }
    
    public function ajax_add_competitor() {
        check_ajax_referer('ai-opt-nonce', 'nonce');
        
        $url = esc_url_raw($_POST['url'] ?? '');
        $name = sanitize_text_field($_POST['name'] ?? '');
        
        if (!$url) {
            wp_send_json_error(array('message' => '请输入有效的URL'));
            return;
        }
        
        $competitors = get_option('ai_opt_competitors', array());
        $competitors[] = array(
            'url' => $url,
            'name' => $name ?: parse_url($url, PHP_URL_HOST),
            'added_at' => current_time('mysql')
        );
        
        update_option('ai_opt_competitors', $competitors);
        
        wp_send_json_success(array('message' => '竞争对手已添加'));
    }
    
    public function ajax_remove_competitor() {
        check_ajax_referer('ai-opt-nonce', 'nonce');
        
        $index = intval($_POST['index'] ?? -1);
        $competitors = get_option('ai_opt_competitors', array());
        
        if (isset($competitors[$index])) {
            unset($competitors[$index]);
            $competitors = array_values($competitors); // 重新索引
            update_option('ai_opt_competitors', $competitors);
        }
        
        wp_send_json_success(array('message' => '竞争对手已删除'));
    }
    
    public function ajax_get_competitors() {
        check_ajax_referer('ai-opt-nonce', 'nonce');
        
        $competitors = get_option('ai_opt_competitors', array());
        wp_send_json_success($competitors);
    }
    
    // AI巡逻系统AJAX处理
    public function ajax_start_patrol() {
        check_ajax_referer('ai-opt-nonce', 'nonce');
        
        // 生成巡逻ID
        $patrol_id = uniqid('patrol_');
        
        // 保存巡逻状态
        update_option('ai_opt_patrol_status', array(
            'id' => $patrol_id,
            'running' => true,
            'started_at' => current_time('mysql')
        ));
        
        // 开始巡逻过程
        $this->start_patrol_process($patrol_id);
        
        wp_send_json_success(array('message' => '巡逻已启动', 'patrol_id' => $patrol_id));
    }
    
    public function ajax_stop_patrol() {
        check_ajax_referer('ai-opt-nonce', 'nonce');
        
        delete_option('ai_opt_patrol_status');
        wp_send_json_success(array('message' => '巡逻已停止'));
    }
    
    public function ajax_get_patrol_logs() {
        check_ajax_referer('ai-opt-nonce', 'nonce');
        
        $since = intval($_POST['since'] ?? 0);
        
        // 获取最近的巡逻日志
        $logs = $this->get_recent_patrol_logs($since);
        
        wp_send_json_success(array('logs' => $logs));
    }
    
    private function start_patrol_process($patrol_id) {
        // 执行各种系统检查
        $checks = array(
            array('type' => 'database', 'title' => '数据库检查', 'message' => '检查数据库连接和性能'),
            array('type' => 'performance', 'title' => '性能监控', 'message' => '检查网站加载速度和内存使用'),
            array('type' => 'security', 'title' => '安全扫描', 'message' => '检查潜在的安全威胁'),
            array('type' => 'plugins', 'title' => '插件状态', 'message' => '检查插件兼容性和更新'),
            array('type' => 'errors', 'title' => '错误检查', 'message' => '扫描PHP和WordPress错误日志')
        );
        
        foreach ($checks as $check) {
            $this->log_patrol_activity($patrol_id, $check['type'], 'info', $check['title'], $check['message']);
            
            // 执行具体检查（这里使用真实的检查逻辑）
            $result = $this->perform_system_check($check['type']);
            
            if ($result['status'] === 'success') {
                $this->log_patrol_activity($patrol_id, $check['type'], 'success', $check['title'] . '完成', $result['message']);
            } else {
                $this->log_patrol_activity($patrol_id, $check['type'], 'warning', $check['title'] . '发现问题', $result['message']);
            }
            
            // 模拟检查时间
            sleep(1);
        }
        
        $this->log_patrol_activity($patrol_id, 'system', 'success', '巡逻完成', '所有系统检查已完成');
    }
    
    private function perform_system_check($type) {
        switch ($type) {
            case 'database':
                global $wpdb;
                $db_size = $this->get_database_size();
                return array(
                    'status' => 'success',
                    'message' => sprintf('数据库正常，大小: %s MB', number_format($db_size / 1024 / 1024, 2))
                );
                
            case 'performance':
                $memory_usage = memory_get_usage(true);
                $memory_limit = ini_get('memory_limit');
                return array(
                    'status' => 'success',
                    'message' => sprintf('内存使用: %s MB / %s', number_format($memory_usage / 1024 / 1024, 2), $memory_limit)
                );
                
            case 'security':
                $wp_version = get_bloginfo('version');
                $latest_version = get_site_transient('update_core');
                if ($latest_version && version_compare($wp_version, $latest_version->updates[0]->version, '<')) {
                    return array(
                        'status' => 'warning',
                        'message' => sprintf('WordPress需要更新：当前版本 %s，最新版本 %s', $wp_version, $latest_version->updates[0]->version)
                    );
                }
                return array(
                    'status' => 'success',
                    'message' => 'WordPress版本是最新的'
                );
                
            case 'plugins':
                $active_plugins = get_option('active_plugins', array());
                $plugin_updates = get_site_transient('update_plugins');
                $need_updates = 0;
                if ($plugin_updates && !empty($plugin_updates->response)) {
                    $need_updates = count($plugin_updates->response);
                }
                
                if ($need_updates > 0) {
                    return array(
                        'status' => 'warning',
                        'message' => sprintf('发现 %d 个插件需要更新', $need_updates)
                    );
                }
                return array(
                    'status' => 'success',
                    'message' => sprintf('所有插件都是最新的，活跃插件数量: %d', count($active_plugins))
                );
                
            case 'errors':
                $error_log = WP_CONTENT_DIR . '/debug.log';
                if (file_exists($error_log)) {
                    $log_size = filesize($error_log);
                    if ($log_size > 1024 * 1024) { // 大于1MB
                        return array(
                            'status' => 'warning',
                            'message' => sprintf('错误日志文件较大: %s MB', number_format($log_size / 1024 / 1024, 2))
                        );
                    }
                }
                return array(
                    'status' => 'success',
                    'message' => '未发现严重错误'
                );
                
            default:
                return array(
                    'status' => 'success',
                    'message' => '检查完成'
                );
        }
    }
    
    private function log_patrol_activity($patrol_id, $type, $level, $title, $message) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'ai_opt_patrol_logs';
        
        $wpdb->insert(
            $table_name,
            array(
                'patrol_id' => $patrol_id,
                'check_type' => $type,
                'status' => $level,
                'message' => $title,
                'details' => $message,
                'created_at' => current_time('mysql')
            ),
            array('%s', '%s', '%s', '%s', '%s', '%s')
        );
    }
    
    private function get_recent_patrol_logs($since = 0) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'ai_opt_patrol_logs';
        $since_date = date('Y-m-d H:i:s', $since / 1000);
        
        $logs = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM $table_name WHERE created_at > %s ORDER BY created_at DESC LIMIT 50",
            $since_date
        ), ARRAY_A);
        
        $formatted_logs = array();
        foreach ($logs as $log) {
            $formatted_logs[] = array(
                'type' => $log['status'],
                'title' => $log['message'],
                'message' => $log['details'],
                'timestamp' => $log['created_at']
            );
        }
        
        return $formatted_logs;
    }
    
    private function get_database_size() {
        global $wpdb;
        
        $result = $wpdb->get_row("
            SELECT SUM(data_length + index_length) as size 
            FROM information_schema.TABLES 
            WHERE table_schema = DATABASE()
        ");
        
        return $result ? $result->size : 0;
    }
    
    // AI内容生成AJAX处理
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
        
        try {
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
        } catch (Exception $e) {
            wp_send_json_error(array('message' => '生成失败: ' . $e->getMessage()));
        }
    }
    
    private function call_siliconflow_api($type, $prompt, $api_key, $video_model = '', $reference_image = '') {
        switch ($type) {
            case 'text':
                return $this->generate_text_content($prompt, $api_key);
            case 'image':
                return $this->generate_image_content($prompt, $api_key);
            case 'video':
                return $this->generate_video_content($prompt, $api_key, $video_model, $reference_image);
            case 'audio':
                return $this->generate_audio_content($prompt, $api_key);
            default:
                return $this->generate_text_content($prompt, $api_key);
        }
    }
    
    private function generate_text_content($prompt, $api_key) {
        $url = 'https://api.siliconflow.cn/v1/chat/completions';
        
        $data = array(
            'model' => 'Qwen/QwQ-32B-Preview',
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
            'timeout' => 120, // 增加超时时间到120秒
            'user-agent' => 'AI-Website-Optimizer/' . AI_OPT_VERSION
        );
        
        $response = wp_remote_post($url, $args);
        
        if (is_wp_error($response)) {
            error_log('文本生成失败: ' . $response->get_error_message());
            return array('error' => '文本生成失败: ' . $response->get_error_message());
        }
        
        $code = wp_remote_retrieve_response_code($response);
        $body = wp_remote_retrieve_body($response);
        
        if ($code !== 200) {
            error_log('文本生成API错误: HTTP ' . $code . ' - ' . $body);
            return array('error' => '文本生成失败，API返回错误: HTTP ' . $code);
        }
        
        $result = json_decode($body, true);
        
        if (isset($result['choices'][0]['message']['content'])) {
            return array('content' => $result['choices'][0]['message']['content'], 'type' => 'text');
        }
        
        if (isset($result['error'])) {
            return array('error' => '文本生成失败: ' . $result['error']['message']);
        }
        
        return array('error' => '文本生成失败: 未知错误');
    }
    
    private function generate_image_content($prompt, $api_key) {
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
            return array('error' => '图片生成失败: ' . $response->get_error_message());
        }
        
        $code = wp_remote_retrieve_response_code($response);
        $body = wp_remote_retrieve_body($response);
        
        if ($code !== 200) {
            error_log('图片生成API错误: HTTP ' . $code . ' - ' . $body);
            return array('error' => '图片生成失败，API返回错误: HTTP ' . $code);
        }
        
        $result = json_decode($body, true);
        
        if (isset($result['images'][0]['url'])) {
            return array('content' => $result['images'][0]['url'], 'type' => 'image');
        }
        
        return array('error' => '图片生成失败: 未返回有效结果');
    }
    
    private function generate_video_content($prompt, $api_key, $video_model, $reference_image = '') {
        // 第一步：提交视频生成请求
        $submit_url = 'https://api.siliconflow.cn/v1/video/submit';
        
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
        
        $args = array(
            'headers' => array(
                'Authorization' => 'Bearer ' . $api_key,
                'Content-Type' => 'application/json'
            ),
            'body' => json_encode($data),
            'timeout' => 120
        );
        
        $response = wp_remote_post($submit_url, $args);
        
        if (is_wp_error($response)) {
            return array('error' => '视频生成提交失败: ' . $response->get_error_message());
        }
        
        $code = wp_remote_retrieve_response_code($response);
        $body = wp_remote_retrieve_body($response);
        
        if ($code !== 200) {
            error_log('视频生成提交失败: HTTP ' . $code . ' - ' . $body);
            
            // 检查具体错误
            $result = json_decode($body, true);
            if (isset($result['error'])) {
                if (strpos($body, 'unauthorized') !== false) {
                    return array('error' => 'API密钥无效，请检查配置');
                } elseif (strpos($body, 'model') !== false && strpos($body, 'disabled') !== false) {
                    return array('error' => '当前视频模型不可用，请尝试其他模型');
                } else {
                    return array('error' => '视频生成失败: ' . $result['error']['message']);
                }
            }
            
            return array('error' => '视频生成提交失败，网络错误');
        }
        
        $result = json_decode($body, true);
        
        if (!isset($result['requestId'])) {
            return array('error' => '视频生成失败: 未获取到请求ID');
        }
        
        $request_id = $result['requestId'];
        
        // 第二步：轮询获取视频状态
        $status_url = 'https://api.siliconflow.cn/v1/video/status';
        $max_attempts = 30; // 最多等待5分钟
        
        for ($i = 0; $i < $max_attempts; $i++) {
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
                'timeout' => 60
            );
            
            $status_response = wp_remote_post($status_url, $status_args);
            
            if (!is_wp_error($status_response)) {
                $status_body = wp_remote_retrieve_body($status_response);
                $status_result = json_decode($status_body, true);
                
                if (isset($status_result['status'])) {
                    if ($status_result['status'] === 'Succeed' && isset($status_result['results']['videos'][0]['url'])) {
                        return array('content' => $status_result['results']['videos'][0]['url'], 'type' => 'video');
                    } elseif ($status_result['status'] === 'Failed') {
                        return array('error' => '视频生成失败: ' . ($status_result['message'] ?? '未知错误'));
                    }
                    // 继续等待...
                }
            }
        }
        
        return array('error' => '视频生成超时，请稍后重试');
    }
    
    private function generate_audio_content($prompt, $api_key) {
        $url = 'https://api.siliconflow.cn/v1/audio/speech';
        
        $data = array(
            'model' => 'fishaudio/fish-speech-1.5',
            'input' => $prompt,
            'voice' => 'zh-CN-XiaoxiaoNeural', // 中文语音
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
            return array('error' => '音频生成失败: ' . $response->get_error_message());
        }
        
        $code = wp_remote_retrieve_response_code($response);
        $body = wp_remote_retrieve_body($response);
        
        if ($code === 403) {
            return array('error' => '音频生成权限不足，请检查API密钥权限或账户余额');
        }
        
        if ($code !== 200) {
            error_log('音频生成API错误: HTTP ' . $code . ' - ' . $body);
            
            $result = json_decode($body, true);
            if (isset($result['error'])) {
                return array('error' => '音频生成失败: ' . $result['error']['message']);
            }
            
            return array('error' => '音频生成失败，HTTP状态码: ' . $code);
        }
        
        // 检查响应是否为JSON格式还是音频数据
        $result = json_decode($body, true);
        if ($result && isset($result['audio_url'])) {
            return array('content' => $result['audio_url'], 'type' => 'audio');
        } elseif ($result && isset($result['data'])) {
            // Base64音频数据
            $audio_url = 'data:audio/mp3;base64,' . $result['data'];
            return array('content' => $audio_url, 'type' => 'audio');
        } else {
            // 直接音频数据，需要保存为文件
            $upload_dir = wp_upload_dir();
            $filename = 'ai_audio_' . uniqid() . '.mp3';
            $file_path = $upload_dir['path'] . '/' . $filename;
            
            if (file_put_contents($file_path, $body)) {
                $audio_url = $upload_dir['url'] . '/' . $filename;
                return array('content' => $audio_url, 'type' => 'audio');
            } else {
                return array('error' => '音频文件保存失败');
            }
        }
    }
    
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
}

// 初始化插件
function init_ai_website_optimizer_fixed() {
    return AI_Website_Optimizer_Fixed::get_instance();
}

// 启动插件
init_ai_website_optimizer_fixed();
?>