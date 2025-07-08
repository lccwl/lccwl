<?php
/**
 * SEO优化完全修复版 - 解决所有功能问题
 */

if (!defined('ABSPATH')) {
    exit;
}

// 获取当前设置
$competitors = get_option('ai_opt_competitors', array());
$analysis_scope = get_option('ai_opt_analysis_scope', array('content', 'meta', 'structure', 'competitors'));
$optimization_strategy = get_option('ai_opt_optimization_strategy', array());
$auto_optimization_enabled = get_option('ai_opt_auto_optimization', false);
?>

<div class="ai-optimizer-wrap">
    <div class="ai-optimizer-header">
        <h1>🎯 AI智能SEO优化分析</h1>
        <p>基于先进AI技术的网站SEO深度分析与自动化优化</p>
    </div>
    
    <!-- AI分析控制面板 -->
    <div class="ai-optimizer-card">
        <h2>🤖 AI分析控制面板</h2>
        
        <!-- AI模型选择区域 -->
        <div class="ai-model-section">
            <h3>🧠 AI模型配置</h3>
            <div class="model-grid">
                <div class="model-preset">
                    <label>选择预设模型:</label>
                    <select id="ai_model_preset" class="ai-optimizer-select">
                        <option value="">-- 选择预设模型 --</option>
                        <option value="qwen/qwq-32b-preview">Qwen QwQ-32B (推荐)</option>
                        <option value="deepseek-ai/deepseek-v2.5">DeepSeek V2.5</option>
                        <option value="meta-llama/llama-3.1-405b-instruct">Llama 3.1 405B</option>
                        <option value="anthropic/claude-3-5-sonnet-20241022">Claude 3.5 Sonnet</option>
                        <option value="google/gemini-2.0-flash-thinking-exp-1219">Gemini 2.0 Flash</option>
                        <option value="custom">自定义模型</option>
                    </select>
                </div>
                
                <div class="model-custom" id="custom_model_section" style="display:none;">
                    <label>自定义AI模型:</label>
                    <input type="text" id="ai_model_custom" class="ai-optimizer-input" placeholder="输入模型名称，如: your-provider/model-name">
                </div>
                
                <div class="model-api">
                    <label>API配置:</label>
                    <select id="api_provider" class="ai-optimizer-select">
                        <option value="siliconflow">Siliconflow API</option>
                        <option value="openai">OpenAI API</option>
                        <option value="anthropic">Anthropic API</option>
                        <option value="custom">自定义API</option>
                    </select>
                </div>
                
                <div class="api-custom" id="custom_api_section" style="display:none;">
                    <label>自定义API端点:</label>
                    <input type="url" id="api_endpoint_custom" class="ai-optimizer-input" placeholder="https://api.example.com/v1/chat/completions">
                    <label>API密钥:</label>
                    <input type="password" id="api_key_custom" class="ai-optimizer-input" placeholder="输入API密钥">
                </div>
            </div>
        </div>
        
        <!-- 分析范围选择 -->
        <div class="analysis-scope-section">
            <h3>📋 分析范围配置</h3>
            <div class="scope-grid">
                <div class="scope-category">
                    <h4>基础分析</h4>
                    <label class="scope-checkbox">
                        <input type="checkbox" name="analysis_scope[]" value="content" <?php checked(in_array('content', $analysis_scope)); ?>>
                        <span class="checkmark"></span>
                        内容质量分析
                    </label>
                    <label class="scope-checkbox">
                        <input type="checkbox" name="analysis_scope[]" value="meta" <?php checked(in_array('meta', $analysis_scope)); ?>>
                        <span class="checkmark"></span>
                        元标签优化
                    </label>
                    <label class="scope-checkbox">
                        <input type="checkbox" name="analysis_scope[]" value="structure" <?php checked(in_array('structure', $analysis_scope)); ?>>
                        <span class="checkmark"></span>
                        网站结构分析
                    </label>
                    <label class="scope-checkbox">
                        <input type="checkbox" name="analysis_scope[]" value="keywords" <?php checked(in_array('keywords', $analysis_scope)); ?>>
                        <span class="checkmark"></span>
                        关键词优化
                    </label>
                </div>
                
                <div class="scope-category">
                    <h4>深度分析</h4>
                    <label class="scope-checkbox">
                        <input type="checkbox" name="analysis_scope[]" value="performance" <?php checked(in_array('performance', $analysis_scope)); ?>>
                        <span class="checkmark"></span>
                        页面性能分析
                    </label>
                    <label class="scope-checkbox">
                        <input type="checkbox" name="analysis_scope[]" value="mobile" <?php checked(in_array('mobile', $analysis_scope)); ?>>
                        <span class="checkmark"></span>
                        移动端适配
                    </label>
                    <label class="scope-checkbox">
                        <input type="checkbox" name="analysis_scope[]" value="schema" <?php checked(in_array('schema', $analysis_scope)); ?>>
                        <span class="checkmark"></span>
                        结构化数据
                    </label>
                    <label class="scope-checkbox">
                        <input type="checkbox" name="analysis_scope[]" value="competitors" <?php checked(in_array('competitors', $analysis_scope)); ?>>
                        <span class="checkmark"></span>
                        竞争对手分析
                    </label>
                </div>
            </div>
            
            <div class="scope-actions">
                <button type="button" id="select_all_scope" class="button">全选</button>
                <button type="button" id="clear_all_scope" class="button">清空</button>
                <button type="button" id="save_scope_settings" class="button button-primary">保存选择</button>
            </div>
        </div>
        
        <!-- 竞争对手分析配置 -->
        <div class="competitors-section" id="competitors_section">
            <h3>🏆 竞争对手分析配置</h3>
            <div class="competitors-manager">
                <div class="add-competitor">
                    <input type="url" id="new_competitor_url" class="ai-optimizer-input" placeholder="输入竞争对手网站URL，如: https://example.com">
                    <input type="text" id="new_competitor_name" class="ai-optimizer-input" placeholder="竞争对手名称（可选）">
                    <button type="button" id="add_competitor" class="button button-primary">添加竞争对手</button>
                </div>
                
                <div class="competitors-list" id="competitors_list">
                    <?php if (!empty($competitors)): ?>
                        <?php foreach ($competitors as $index => $competitor): ?>
                        <div class="competitor-item" data-index="<?php echo $index; ?>">
                            <div class="competitor-info">
                                <strong><?php echo esc_html($competitor['name'] ?: parse_url($competitor['url'], PHP_URL_HOST)); ?></strong>
                                <span class="competitor-url"><?php echo esc_url($competitor['url']); ?></span>
                            </div>
                            <button type="button" class="remove-competitor button button-small">删除</button>
                        </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="no-competitors">暂无竞争对手配置</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <!-- 优化策略配置 -->
        <div class="optimization-strategy-section">
            <h3>⚙️ 优化策略配置</h3>
            <div class="strategy-grid">
                <div class="strategy-category">
                    <h4>自动优化项目</h4>
                    <label class="strategy-checkbox">
                        <input type="checkbox" name="optimization_strategy[]" value="title_optimization" <?php checked(in_array('title_optimization', $optimization_strategy)); ?>>
                        <span class="checkmark"></span>
                        标题自动优化
                    </label>
                    <label class="strategy-checkbox">
                        <input type="checkbox" name="optimization_strategy[]" value="meta_description" <?php checked(in_array('meta_description', $optimization_strategy)); ?>>
                        <span class="checkmark"></span>
                        描述自动生成
                    </label>
                    <label class="strategy-checkbox">
                        <input type="checkbox" name="optimization_strategy[]" value="alt_text" <?php checked(in_array('alt_text', $optimization_strategy)); ?>>
                        <span class="checkmark"></span>
                        图片Alt文本
                    </label>
                    <label class="strategy-checkbox">
                        <input type="checkbox" name="optimization_strategy[]" value="internal_linking" <?php checked(in_array('internal_linking', $optimization_strategy)); ?>>
                        <span class="checkmark"></span>
                        内部链接优化
                    </label>
                </div>
                
                <div class="strategy-category">
                    <h4>高级优化</h4>
                    <label class="strategy-checkbox">
                        <input type="checkbox" name="optimization_strategy[]" value="schema_markup" <?php checked(in_array('schema_markup', $optimization_strategy)); ?>>
                        <span class="checkmark"></span>
                        结构化数据生成
                    </label>
                    <label class="strategy-checkbox">
                        <input type="checkbox" name="optimization_strategy[]" value="sitemap_update" <?php checked(in_array('sitemap_update', $optimization_strategy)); ?>>
                        <span class="checkmark"></span>
                        站点地图更新
                    </label>
                    <label class="strategy-checkbox">
                        <input type="checkbox" name="optimization_strategy[]" value="robots_optimization" <?php checked(in_array('robots_optimization', $optimization_strategy)); ?>>
                        <span class="checkmark"></span>
                        Robots.txt优化
                    </label>
                    <label class="strategy-checkbox">
                        <input type="checkbox" name="optimization_strategy[]" value="performance_optimization" <?php checked(in_array('performance_optimization', $optimization_strategy)); ?>>
                        <span class="checkmark"></span>
                        性能优化建议
                    </label>
                </div>
            </div>
            
            <div class="automation-toggle">
                <label class="automation-switch">
                    <input type="checkbox" id="auto_optimization_enabled" <?php checked($auto_optimization_enabled); ?>>
                    <span class="slider"></span>
                    <span class="automation-label">启用自动化优化执行</span>
                </label>
                <p class="automation-description">开启后，系统将自动应用AI优化建议。建议先进行测试分析。</p>
            </div>
        </div>
    </div>
    
    <!-- 分析执行区域 -->
    <div class="ai-optimizer-card">
        <h2>🚀 执行AI深度分析</h2>
        <div class="analysis-controls">
            <button type="button" id="start_seo_analysis" class="button button-primary button-hero">
                <span class="dashicons dashicons-analytics"></span>
                开始AI深度分析
            </button>
            <button type="button" id="stop_analysis" class="button button-secondary" style="display:none;">
                <span class="dashicons dashicons-dismiss"></span>
                停止分析
            </button>
        </div>
        
        <!-- 分析进度和日志 -->
        <div class="analysis-progress" id="analysis_progress" style="display:none;">
            <div class="progress-header">
                <h3>📊 分析进度</h3>
                <div class="progress-status">
                    <span id="progress_text">准备开始分析...</span>
                    <span id="progress_percentage">0%</span>
                </div>
            </div>
            <div class="progress-bar-container">
                <div class="progress-bar" id="progress_bar"></div>
            </div>
            
            <div class="analysis-logs" id="analysis_logs">
                <div class="logs-header">
                    <h4>📋 实时分析日志</h4>
                    <div class="logs-controls">
                        <button type="button" id="clear_logs" class="button button-small">清空日志</button>
                        <button type="button" id="export_logs" class="button button-small">导出日志</button>
                    </div>
                </div>
                <div class="logs-container" id="logs_container">
                    <div class="log-placeholder">分析日志将在此显示...</div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- 分析结果展示 -->
    <div class="ai-optimizer-card" id="analysis_results" style="display:none;">
        <h2>📈 分析结果与优化建议</h2>
        
        <div class="results-summary" id="results_summary">
            <!-- 结果摘要 -->
        </div>
        
        <div class="results-tabs">
            <div class="tab-nav">
                <button type="button" class="tab-button active" data-tab="overview">概览</button>
                <button type="button" class="tab-button" data-tab="suggestions">优化建议</button>
                <button type="button" class="tab-button" data-tab="competitors">竞争对手</button>
                <button type="button" class="tab-button" data-tab="technical">技术分析</button>
            </div>
            
            <div class="tab-content">
                <div class="tab-panel active" id="tab_overview">
                    <div id="overview_content">
                        <!-- 概览内容 -->
                    </div>
                </div>
                
                <div class="tab-panel" id="tab_suggestions">
                    <div id="suggestions_content">
                        <!-- 建议内容 -->
                    </div>
                </div>
                
                <div class="tab-panel" id="tab_competitors">
                    <div id="competitors_content">
                        <!-- 竞争对手内容 -->
                    </div>
                </div>
                
                <div class="tab-panel" id="tab_technical">
                    <div id="technical_content">
                        <!-- 技术分析内容 -->
                    </div>
                </div>
            </div>
        </div>
        
        <div class="results-actions">
            <button type="button" id="apply_selected_optimizations" class="button button-primary">应用选中的优化</button>
            <button type="button" id="schedule_optimization" class="button">定时执行优化</button>
            <button type="button" id="export_results" class="button">导出分析报告</button>
        </div>
    </div>
</div>

<style>
/* SEO优化页面样式 */
.ai-optimizer-wrap {
    max-width: 1200px;
    margin: 20px auto;
}

.ai-optimizer-header {
    text-align: center;
    margin-bottom: 30px;
    padding: 30px;
    background: linear-gradient(135deg, #165DFF 0%, #7E22CE 100%);
    color: white;
    border-radius: 15px;
}

.ai-optimizer-header h1 {
    margin: 0 0 10px 0;
    font-size: 2.5em;
}

.ai-optimizer-card {
    background: #fff;
    border: 1px solid #ccd0d4;
    border-radius: 12px;
    padding: 25px;
    margin-bottom: 25px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.05);
}

.ai-optimizer-card h2 {
    margin: 0 0 20px 0;
    color: #165DFF;
    border-bottom: 2px solid #f0f0f0;
    padding-bottom: 10px;
}

/* 模型选择区域 */
.ai-model-section {
    margin-bottom: 30px;
}

.model-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 20px;
    margin-top: 15px;
}

.ai-optimizer-select, .ai-optimizer-input {
    width: 100%;
    padding: 10px;
    border: 2px solid #ddd;
    border-radius: 8px;
    font-size: 14px;
    transition: border-color 0.3s ease;
}

.ai-optimizer-select:focus, .ai-optimizer-input:focus {
    border-color: #165DFF;
    outline: none;
    box-shadow: 0 0 0 3px rgba(22, 93, 255, 0.1);
}

/* 分析范围区域 */
.analysis-scope-section {
    margin-bottom: 30px;
}

.scope-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
    margin: 15px 0;
}

.scope-category h4 {
    margin: 0 0 15px 0;
    color: #333;
    font-size: 16px;
    border-bottom: 1px solid #eee;
    padding-bottom: 5px;
}

/* 自定义复选框样式 */
.scope-checkbox, .strategy-checkbox {
    display: flex;
    align-items: center;
    margin-bottom: 10px;
    cursor: pointer;
    user-select: none;
    padding: 5px;
    border-radius: 5px;
    transition: background-color 0.2s ease;
}

.scope-checkbox:hover, .strategy-checkbox:hover {
    background-color: #f8f9fa;
}

.scope-checkbox input[type="checkbox"], .strategy-checkbox input[type="checkbox"] {
    margin-right: 10px;
    transform: scale(1.2);
}

.checkmark {
    margin-left: 5px;
}

.scope-actions {
    margin-top: 15px;
    padding-top: 15px;
    border-top: 1px solid #eee;
}

.scope-actions .button {
    margin-right: 10px;
}

/* 竞争对手分析区域 */
.competitors-section {
    margin-bottom: 30px;
}

.add-competitor {
    display: grid;
    grid-template-columns: 2fr 1fr auto;
    gap: 10px;
    margin-bottom: 20px;
    align-items: end;
}

.competitor-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 15px;
    background: #f8f9fa;
    border-radius: 8px;
    margin-bottom: 10px;
    border: 1px solid #e9ecef;
}

.competitor-info {
    flex: 1;
}

.competitor-info strong {
    display: block;
    color: #333;
    margin-bottom: 5px;
}

.competitor-url {
    color: #666;
    font-size: 13px;
}

.no-competitors {
    text-align: center;
    color: #666;
    padding: 30px;
    font-style: italic;
}

/* 优化策略区域 */
.optimization-strategy-section {
    margin-bottom: 30px;
}

.strategy-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 20px;
    margin: 15px 0;
}

.strategy-category h4 {
    margin: 0 0 15px 0;
    color: #333;
    font-size: 16px;
    border-bottom: 1px solid #eee;
    padding-bottom: 5px;
}

/* 自动化开关 */
.automation-toggle {
    margin-top: 25px;
    padding: 20px;
    background: #f8f9fa;
    border-radius: 8px;
    border: 1px solid #e9ecef;
}

.automation-switch {
    display: flex;
    align-items: center;
    cursor: pointer;
}

.automation-switch input[type="checkbox"] {
    display: none;
}

.slider {
    position: relative;
    width: 60px;
    height: 30px;
    background: #ccc;
    border-radius: 30px;
    transition: background 0.3s;
    margin-right: 15px;
}

.slider:before {
    content: "";
    position: absolute;
    width: 26px;
    height: 26px;
    border-radius: 50%;
    background: white;
    top: 2px;
    left: 2px;
    transition: transform 0.3s;
}

.automation-switch input:checked + .slider {
    background: #165DFF;
}

.automation-switch input:checked + .slider:before {
    transform: translateX(30px);
}

.automation-label {
    font-weight: bold;
    color: #333;
}

.automation-description {
    margin: 10px 0 0 0;
    color: #666;
    font-size: 13px;
}

/* 分析控制区域 */
.analysis-controls {
    text-align: center;
    margin-bottom: 30px;
}

.button-hero {
    padding: 15px 30px;
    font-size: 16px;
    border-radius: 8px;
}

/* 进度条和日志 */
.analysis-progress {
    margin-top: 30px;
}

.progress-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 15px;
}

.progress-status {
    display: flex;
    align-items: center;
    gap: 15px;
}

.progress-bar-container {
    width: 100%;
    height: 20px;
    background: #f0f0f0;
    border-radius: 10px;
    overflow: hidden;
    margin-bottom: 20px;
}

.progress-bar {
    height: 100%;
    background: linear-gradient(90deg, #165DFF, #00F5D4);
    width: 0;
    transition: width 0.5s ease;
    border-radius: 10px;
}

.analysis-logs {
    background: #f8f9fa;
    border-radius: 8px;
    border: 1px solid #e9ecef;
}

.logs-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 15px;
    border-bottom: 1px solid #e9ecef;
}

.logs-header h4 {
    margin: 0;
}

.logs-controls .button {
    margin-left: 5px;
}

.logs-container {
    padding: 15px;
    max-height: 300px;
    overflow-y: auto;
    font-family: 'Courier New', monospace;
    font-size: 13px;
    line-height: 1.5;
}

.log-placeholder {
    color: #666;
    text-align: center;
    font-style: italic;
}

/* 结果展示区域 */
.results-tabs {
    margin-top: 20px;
}

.tab-nav {
    display: flex;
    border-bottom: 2px solid #f0f0f0;
    margin-bottom: 20px;
}

.tab-button {
    background: none;
    border: none;
    padding: 15px 25px;
    cursor: pointer;
    font-size: 14px;
    font-weight: bold;
    color: #666;
    border-bottom: 3px solid transparent;
    transition: all 0.3s ease;
}

.tab-button:hover {
    color: #165DFF;
    background: #f8f9fa;
}

.tab-button.active {
    color: #165DFF;
    border-bottom-color: #165DFF;
}

.tab-panel {
    display: none;
}

.tab-panel.active {
    display: block;
}

.results-actions {
    margin-top: 30px;
    padding-top: 20px;
    border-top: 1px solid #eee;
    text-align: center;
}

.results-actions .button {
    margin: 0 10px;
}

/* 响应式设计 */
@media (max-width: 768px) {
    .model-grid, .scope-grid, .strategy-grid {
        grid-template-columns: 1fr;
    }
    
    .add-competitor {
        grid-template-columns: 1fr;
    }
    
    .progress-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 10px;
    }
    
    .tab-nav {
        flex-direction: column;
    }
    
    .results-actions {
        text-align: left;
    }
    
    .results-actions .button {
        display: block;
        width: 100%;
        margin: 5px 0;
    }
}
</style>

<script>
jQuery(document).ready(function($) {
    const nonce = '<?php echo wp_create_nonce("ai-opt-nonce"); ?>';
    const ajaxurl = '<?php echo admin_url('admin-ajax.php'); ?>';
    
    let analysisRunning = false;
    let analysisInterval;
    
    // 初始化
    initializeSEOOptimizer();
    
    function initializeSEOOptimizer() {
        // 加载保存的设置
        loadSavedSettings();
        
        // 绑定事件
        bindEvents();
        
        // 检查竞争对手分析选项
        toggleCompetitorsSection();
    }
    
    function loadSavedSettings() {
        // 从localStorage加载分析范围设置
        const savedScope = localStorage.getItem('ai_opt_analysis_scope');
        if (savedScope) {
            const scopeArray = JSON.parse(savedScope);
            $('input[name="analysis_scope[]"]').each(function() {
                $(this).prop('checked', scopeArray.includes($(this).val()));
            });
        }
        
        // 从localStorage加载优化策略设置
        const savedStrategy = localStorage.getItem('ai_opt_optimization_strategy');
        if (savedStrategy) {
            const strategyArray = JSON.parse(savedStrategy);
            $('input[name="optimization_strategy[]"]').each(function() {
                $(this).prop('checked', strategyArray.includes($(this).val()));
            });
        }
    }
    
    function bindEvents() {
        // AI模型选择事件
        $('#ai_model_preset').on('change', function() {
            const value = $(this).val();
            if (value === 'custom') {
                $('#custom_model_section').show();
            } else {
                $('#custom_model_section').hide();
            }
        });
        
        // API提供商选择事件
        $('#api_provider').on('change', function() {
            const value = $(this).val();
            if (value === 'custom') {
                $('#custom_api_section').show();
            } else {
                $('#custom_api_section').hide();
            }
        });
        
        // 分析范围事件
        $('input[name="analysis_scope[]"]').on('change', function() {
            toggleCompetitorsSection();
            saveAnalysisScope();
        });
        
        // 优化策略事件
        $('input[name="optimization_strategy[]"]').on('change', function() {
            saveOptimizationStrategy();
        });
        
        // 分析范围全选/清空
        $('#select_all_scope').on('click', function() {
            $('input[name="analysis_scope[]"]').prop('checked', true);
            toggleCompetitorsSection();
            saveAnalysisScope();
        });
        
        $('#clear_all_scope').on('click', function() {
            $('input[name="analysis_scope[]"]').prop('checked', false);
            toggleCompetitorsSection();
            saveAnalysisScope();
        });
        
        // 保存范围设置
        $('#save_scope_settings').on('click', function() {
            saveAnalysisScope();
            showNotification('分析范围设置已保存', 'success');
        });
        
        // 竞争对手管理
        $('#add_competitor').on('click', addCompetitor);
        $(document).on('click', '.remove-competitor', removeCompetitor);
        
        // 开始分析
        $('#start_seo_analysis').on('click', startSEOAnalysis);
        $('#stop_analysis').on('click', stopAnalysis);
        
        // 日志管理
        $('#clear_logs').on('click', clearLogs);
        $('#export_logs').on('click', exportLogs);
        
        // 标签页切换
        $('.tab-button').on('click', function() {
            const tabId = $(this).data('tab');
            switchTab(tabId);
        });
        
        // 自动化开关
        $('#auto_optimization_enabled').on('change', function() {
            const enabled = $(this).is(':checked');
            saveAutoOptimizationSetting(enabled);
        });
    }
    
    function toggleCompetitorsSection() {
        const competitorsChecked = $('input[name="analysis_scope[]"][value="competitors"]').is(':checked');
        if (competitorsChecked) {
            $('#competitors_section').show();
        } else {
            $('#competitors_section').hide();
        }
    }
    
    function saveAnalysisScope() {
        const scope = [];
        $('input[name="analysis_scope[]"]:checked').each(function() {
            scope.push($(this).val());
        });
        
        localStorage.setItem('ai_opt_analysis_scope', JSON.stringify(scope));
        
        // 同时保存到数据库
        $.post(ajaxurl, {
            action: 'ai_opt_save_analysis_scope',
            nonce: nonce,
            scope: scope
        });
    }
    
    function saveOptimizationStrategy() {
        const strategy = [];
        $('input[name="optimization_strategy[]"]:checked').each(function() {
            strategy.push($(this).val());
        });
        
        localStorage.setItem('ai_opt_optimization_strategy', JSON.stringify(strategy));
        
        // 同时保存到数据库
        $.post(ajaxurl, {
            action: 'ai_opt_save_optimization_strategy',
            nonce: nonce,
            strategy: strategy
        });
    }
    
    function saveAutoOptimizationSetting(enabled) {
        $.post(ajaxurl, {
            action: 'ai_opt_save_auto_optimization',
            nonce: nonce,
            enabled: enabled ? 1 : 0
        }, function(response) {
            if (response.success) {
                showNotification('自动化设置已保存', 'success');
            }
        });
    }
    
    function addCompetitor() {
        const url = $('#new_competitor_url').val().trim();
        const name = $('#new_competitor_name').val().trim();
        
        if (!url) {
            showNotification('请输入竞争对手网站URL', 'error');
            return;
        }
        
        // URL验证
        if (!isValidURL(url)) {
            showNotification('请输入有效的网站URL', 'error');
            return;
        }
        
        $.post(ajaxurl, {
            action: 'ai_opt_add_competitor',
            nonce: nonce,
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
        
        $.post(ajaxurl, {
            action: 'ai_opt_remove_competitor',
            nonce: nonce,
            index: index
        }, function(response) {
            if (response.success) {
                refreshCompetitorsList();
                showNotification('竞争对手已删除', 'success');
            }
        });
    }
    
    function refreshCompetitorsList() {
        $.post(ajaxurl, {
            action: 'ai_opt_get_competitors',
            nonce: nonce
        }, function(response) {
            if (response.success) {
                updateCompetitorsList(response.data);
            }
        });
    }
    
    function updateCompetitorsList(competitors) {
        const container = $('#competitors_list');
        
        if (competitors.length === 0) {
            container.html('<div class="no-competitors">暂无竞争对手配置</div>');
            return;
        }
        
        let html = '';
        competitors.forEach((competitor, index) => {
            html += `
                <div class="competitor-item" data-index="${index}">
                    <div class="competitor-info">
                        <strong>${competitor.name || new URL(competitor.url).hostname}</strong>
                        <span class="competitor-url">${competitor.url}</span>
                    </div>
                    <button type="button" class="remove-competitor button button-small">删除</button>
                </div>
            `;
        });
        
        container.html(html);
    }
    
    function startSEOAnalysis() {
        if (analysisRunning) return;
        
        // 获取配置
        const config = getAnalysisConfig();
        
        if (!validateAnalysisConfig(config)) {
            return;
        }
        
        analysisRunning = true;
        
        // 更新UI
        $('#start_seo_analysis').hide();
        $('#stop_analysis').show();
        $('#analysis_progress').show();
        $('#analysis_results').hide();
        
        // 重置进度
        updateProgress(0, '准备开始分析...');
        clearLogs();
        
        // 开始分析
        $.post(ajaxurl, {
            action: 'ai_opt_start_seo_analysis',
            nonce: nonce,
            config: config
        }, function(response) {
            if (response.success) {
                const analysisId = response.data.analysis_id;
                startProgressMonitoring(analysisId);
                addLog('info', '分析已启动', '分析ID: ' + analysisId);
            } else {
                stopAnalysis();
                showNotification('分析启动失败: ' + response.data.message, 'error');
                addLog('error', '分析启动失败', response.data.message);
            }
        }).fail(function(xhr, status, error) {
            stopAnalysis();
            showNotification('网络错误: ' + error, 'error');
            addLog('error', '网络错误', error);
        });
    }
    
    function getAnalysisConfig() {
        const config = {};
        
        // AI模型配置
        const modelPreset = $('#ai_model_preset').val();
        if (modelPreset === 'custom') {
            config.ai_model = $('#ai_model_custom').val();
        } else {
            config.ai_model = modelPreset;
        }
        
        // API配置
        const apiProvider = $('#api_provider').val();
        if (apiProvider === 'custom') {
            config.api_endpoint = $('#api_endpoint_custom').val();
            config.api_key = $('#api_key_custom').val();
        } else {
            config.api_provider = apiProvider;
        }
        
        // 分析范围
        config.analysis_scope = [];
        $('input[name="analysis_scope[]"]:checked').each(function() {
            config.analysis_scope.push($(this).val());
        });
        
        // 优化策略
        config.optimization_strategy = [];
        $('input[name="optimization_strategy[]"]:checked').each(function() {
            config.optimization_strategy.push($(this).val());
        });
        
        // 自动化设置
        config.auto_optimization = $('#auto_optimization_enabled').is(':checked');
        
        return config;
    }
    
    function validateAnalysisConfig(config) {
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
    
    function startProgressMonitoring(analysisId) {
        analysisInterval = setInterval(function() {
            $.post(ajaxurl, {
                action: 'ai_opt_get_analysis_progress',
                nonce: nonce,
                analysis_id: analysisId
            }, function(response) {
                if (response.success) {
                    const data = response.data;
                    updateProgress(data.progress, data.status);
                    
                    // 更新日志
                    if (data.logs && data.logs.length > 0) {
                        data.logs.forEach(log => {
                            addLog(log.type, log.title, log.message);
                        });
                    }
                    
                    // 检查是否完成
                    if (data.completed) {
                        clearInterval(analysisInterval);
                        onAnalysisCompleted(data.results);
                    }
                }
            });
        }, 2000); // 每2秒检查一次
    }
    
    function stopAnalysis() {
        analysisRunning = false;
        
        if (analysisInterval) {
            clearInterval(analysisInterval);
        }
        
        $('#start_seo_analysis').show();
        $('#stop_analysis').hide();
        
        // 发送停止请求
        $.post(ajaxurl, {
            action: 'ai_opt_stop_seo_analysis',
            nonce: nonce
        });
        
        addLog('warning', '分析已停止', '用户手动停止了分析过程');
    }
    
    function updateProgress(percentage, status) {
        $('#progress_bar').css('width', percentage + '%');
        $('#progress_percentage').text(percentage + '%');
        $('#progress_text').text(status);
    }
    
    function addLog(type, title, message) {
        const timestamp = new Date().toLocaleTimeString();
        const logClass = `log-${type}`;
        
        const logHtml = `
            <div class="log-entry ${logClass}">
                <span class="log-time">[${timestamp}]</span>
                <span class="log-title">${title}:</span>
                <span class="log-message">${message}</span>
            </div>
        `;
        
        const container = $('#logs_container');
        
        if (container.find('.log-placeholder').length > 0) {
            container.empty();
        }
        
        container.append(logHtml);
        container.scrollTop(container[0].scrollHeight);
    }
    
    function clearLogs() {
        $('#logs_container').html('<div class="log-placeholder">分析日志将在此显示...</div>');
    }
    
    function exportLogs() {
        const logs = [];
        $('#logs_container .log-entry').each(function() {
            const time = $(this).find('.log-time').text();
            const title = $(this).find('.log-title').text();
            const message = $(this).find('.log-message').text();
            logs.push(`${time} ${title} ${message}`);
        });
        
        const content = logs.join('\n');
        const blob = new Blob([content], { type: 'text/plain' });
        const url = URL.createObjectURL(blob);
        
        const a = document.createElement('a');
        a.href = url;
        a.download = `seo-analysis-logs-${new Date().toISOString().slice(0, 10)}.txt`;
        a.click();
        
        URL.revokeObjectURL(url);
    }
    
    function onAnalysisCompleted(results) {
        analysisRunning = false;
        $('#start_seo_analysis').show();
        $('#stop_analysis').hide();
        
        // 显示结果
        displayAnalysisResults(results);
        $('#analysis_results').show();
        
        addLog('success', '分析完成', '所有分析任务已完成，请查看结果');
        showNotification('SEO分析已完成！', 'success');
    }
    
    function displayAnalysisResults(results) {
        // 更新概览
        $('#overview_content').html(generateOverviewHTML(results.overview));
        
        // 更新建议
        $('#suggestions_content').html(generateSuggestionsHTML(results.suggestions));
        
        // 更新竞争对手分析
        if (results.competitors) {
            $('#competitors_content').html(generateCompetitorsHTML(results.competitors));
        }
        
        // 更新技术分析
        $('#technical_content').html(generateTechnicalHTML(results.technical));
        
        // 更新摘要
        $('#results_summary').html(generateSummaryHTML(results.summary));
    }
    
    function generateOverviewHTML(overview) {
        return `
            <div class="overview-grid">
                <div class="score-card">
                    <h3>总体SEO评分</h3>
                    <div class="score-value">${overview.total_score}/100</div>
                </div>
                <div class="metrics-grid">
                    ${overview.metrics.map(metric => `
                        <div class="metric-item">
                            <div class="metric-label">${metric.label}</div>
                            <div class="metric-value">${metric.value}</div>
                        </div>
                    `).join('')}
                </div>
            </div>
        `;
    }
    
    function generateSuggestionsHTML(suggestions) {
        return `
            <div class="suggestions-list">
                ${suggestions.map(suggestion => `
                    <div class="suggestion-item" data-priority="${suggestion.priority}">
                        <div class="suggestion-header">
                            <h4>${suggestion.title}</h4>
                            <span class="priority-badge priority-${suggestion.priority}">${getPriorityText(suggestion.priority)}</span>
                        </div>
                        <div class="suggestion-content">
                            <p>${suggestion.description}</p>
                            <div class="suggestion-actions">
                                <button type="button" class="apply-suggestion button button-primary" data-suggestion-id="${suggestion.id}">
                                    应用优化
                                </button>
                                <button type="button" class="view-details button" data-suggestion-id="${suggestion.id}">
                                    查看详情
                                </button>
                            </div>
                        </div>
                    </div>
                `).join('')}
            </div>
        `;
    }
    
    function generateCompetitorsHTML(competitors) {
        return `
            <div class="competitors-analysis">
                ${competitors.map(competitor => `
                    <div class="competitor-analysis-item">
                        <h4>${competitor.name}</h4>
                        <div class="competitor-metrics">
                            <div class="metric">
                                <span class="label">SEO评分:</span>
                                <span class="value">${competitor.seo_score}/100</span>
                            </div>
                            <div class="metric">
                                <span class="label">页面速度:</span>
                                <span class="value">${competitor.page_speed}</span>
                            </div>
                            <div class="metric">
                                <span class="label">移动友好度:</span>
                                <span class="value">${competitor.mobile_score}/100</span>
                            </div>
                        </div>
                        <div class="competitor-insights">
                            <h5>关键洞察:</h5>
                            <ul>
                                ${competitor.insights.map(insight => `<li>${insight}</li>`).join('')}
                            </ul>
                        </div>
                    </div>
                `).join('')}
            </div>
        `;
    }
    
    function generateTechnicalHTML(technical) {
        return `
            <div class="technical-analysis">
                <div class="technical-categories">
                    ${Object.keys(technical).map(category => `
                        <div class="technical-category">
                            <h4>${getCategoryTitle(category)}</h4>
                            <div class="technical-items">
                                ${technical[category].map(item => `
                                    <div class="technical-item status-${item.status}">
                                        <div class="item-title">${item.title}</div>
                                        <div class="item-description">${item.description}</div>
                                        ${item.recommendation ? `<div class="item-recommendation">${item.recommendation}</div>` : ''}
                                    </div>
                                `).join('')}
                            </div>
                        </div>
                    `).join('')}
                </div>
            </div>
        `;
    }
    
    function generateSummaryHTML(summary) {
        return `
            <div class="results-summary-content">
                <div class="summary-scores">
                    <div class="summary-score">
                        <div class="score-label">总体评分</div>
                        <div class="score-value">${summary.total_score}/100</div>
                    </div>
                    <div class="summary-score">
                        <div class="score-label">发现问题</div>
                        <div class="score-value">${summary.issues_found}</div>
                    </div>
                    <div class="summary-score">
                        <div class="score-label">优化建议</div>
                        <div class="score-value">${summary.suggestions_count}</div>
                    </div>
                </div>
                <div class="summary-highlights">
                    <h4>关键发现:</h4>
                    <ul>
                        ${summary.highlights.map(highlight => `<li>${highlight}</li>`).join('')}
                    </ul>
                </div>
            </div>
        `;
    }
    
    function switchTab(tabId) {
        $('.tab-button').removeClass('active');
        $('.tab-panel').removeClass('active');
        
        $(`.tab-button[data-tab="${tabId}"]`).addClass('active');
        $(`#tab_${tabId}`).addClass('active');
    }
    
    function showNotification(message, type = 'info') {
        const notification = $(`
            <div class="notice notice-${type} is-dismissible">
                <p>${message}</p>
                <button type="button" class="notice-dismiss">
                    <span class="screen-reader-text">关闭通知</span>
                </button>
            </div>
        `);
        
        $('.ai-optimizer-wrap').prepend(notification);
        
        setTimeout(() => {
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
    
    function getPriorityText(priority) {
        const priorities = {
            'high': '高优先级',
            'medium': '中优先级',
            'low': '低优先级'
        };
        return priorities[priority] || priority;
    }
    
    function getCategoryTitle(category) {
        const titles = {
            'meta': '元数据分析',
            'structure': '网站结构',
            'performance': '性能分析',
            'mobile': '移动端适配',
            'security': '安全检查'
        };
        return titles[category] || category;
    }
});
</script>